<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Services;

use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Devniox\AiAutomation\AI\Providers\OpenAiProvider;
use Devniox\AiAutomation\Events\AiResponseGenerated;
use Devniox\AiAutomation\Events\LeadCreated;
use Devniox\AiAutomation\Events\MessageReceived;
use Devniox\AiAutomation\Models\Conversation;
use Devniox\AiAutomation\Models\Faq;
use Devniox\AiAutomation\Models\KnowledgeItem;
use Devniox\AiAutomation\Models\Lead;
use Devniox\AiAutomation\Models\Message;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ChatbotService
{
    protected AiProviderInterface $aiProvider;

    public function __construct(
        ?AiProviderInterface $aiProvider = null,
        protected ?SettingsService $settings = null,
    ) {
        $this->settings ??= app(SettingsService::class);
        $this->aiProvider = $aiProvider ?? new OpenAiProvider(
            config('devniox-ai.ai.api_key'),
            config('devniox-ai.ai.model')
        );
    }

    public function handle(string $message, array $context = []): array
    {
        $normalized = $this->normalizeMessage($message);
        $scope = TenantResolver::resolve($context['request'] ?? null, $context);
        $conversation = $this->resolveConversation($scope, $context);

        event(new MessageReceived([
            'message' => $normalized,
            'scope' => $scope,
            'context' => $this->redactContext($context),
        ]));

        $this->storeMessage($conversation, [
            'channel' => $context['channel'] ?? 'website',
            'direction' => 'incoming',
            'sender_id' => $context['sender_id'] ?? $context['customer_id'] ?? null,
            'provider_message_id' => $context['provider_message_id'] ?? null,
            'content' => $normalized,
            'status' => 'received',
            'metadata' => ['context' => $this->redactContext($context)],
        ]);

        $this->captureLead($scope, $context);

        $answer = $this->resolveLocalAnswer($normalized, $scope);

        if ($answer === null && $this->shouldUseAi($scope)) {
            $answer = $this->resolveAiAnswer($normalized, $scope, $context, $conversation);
        }

        if ($answer === null) {
            $answer = [
                'message' => $this->handoffMessage(),
                'source' => 'handoff',
                'needs_handoff' => true,
            ];
        }

        $handoff = $this->buildHandoff($normalized);
        $messageId = (string) Str::uuid();

        $this->storeMessage($conversation, [
            'channel' => $context['channel'] ?? 'website',
            'direction' => 'outgoing',
            'sender_id' => 'ai',
            'provider_message_id' => $messageId,
            'content' => $answer['message'],
            'status' => 'generated',
            'metadata' => [
                'source' => $answer['source'],
                'needs_handoff' => (bool) ($answer['needs_handoff'] ?? false),
                'handoff' => $handoff,
            ],
        ]);

        event(new AiResponseGenerated([
            'message' => $answer['message'],
            'source' => $answer['source'],
            'scope' => $scope,
            'conversation_id' => $conversation?->uuid,
        ]));

        return [
            'success' => true,
            'message' => $answer['message'],
            'source' => $answer['source'],
            'conversation_id' => $conversation?->uuid ?? ($context['conversation_id'] ?? (string) Str::uuid()),
            'message_id' => $messageId,
            'needs_handoff' => (bool) ($answer['needs_handoff'] ?? false),
            'handoff' => $handoff,
        ];
    }

    protected function normalizeMessage(string $message): string
    {
        $message = preg_replace('/\s+/', ' ', trim($message)) ?? $message;

        return mb_substr($message, 0, 1000);
    }

    protected function resolveConversation(array $scope, array $context): ?Conversation
    {
        if (! $this->tablesReady(['devniox_conversations'])) {
            return null;
        }

        $uuid = $context['conversation_id'] ?? null;
        $query = Conversation::query();
        TenantResolver::applyScope($query, $scope);

        if ($uuid) {
            $conversation = (clone $query)->where('uuid', $uuid)->first();
            if ($conversation) {
                return $conversation;
            }
        }

        $externalId = $context['external_id'] ?? $context['customer_id'] ?? null;

        if ($externalId) {
            $conversation = (clone $query)
                ->where('channel', $context['channel'] ?? 'website')
                ->where('external_id', $externalId)
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        return Conversation::query()->create(array_merge($scope, [
            'uuid' => $uuid ?: (string) Str::uuid(),
            'channel' => $context['channel'] ?? 'website',
            'external_id' => $externalId,
            'customer_id' => $context['customer_id'] ?? null,
            'status' => 'open',
            'metadata' => ['ip' => $context['ip'] ?? null],
        ]));
    }

    protected function storeMessage(?Conversation $conversation, array $data): void
    {
        if (! $conversation || ! $this->tablesReady(['devniox_messages'])) {
            return;
        }

        $conversation->messages()->create($data);
    }

    protected function captureLead(array $scope, array $context): void
    {
        if (! config('devniox-ai.website.lead_capture', true) || ! $this->tablesReady(['devniox_leads'])) {
            return;
        }

        if (empty($context['email']) && empty($context['phone'])) {
            return;
        }

        $query = Lead::query();
        TenantResolver::applyScope($query, $scope);

        $query->updateOrCreate([
            'email' => $context['email'] ?? null,
            'phone' => $context['phone'] ?? null,
        ], array_merge($scope, [
            'source' => $context['channel'] ?? 'website',
            'name' => $context['customer_name'] ?? null,
            'email' => $context['email'] ?? null,
            'phone' => $context['phone'] ?? null,
            'status' => 'new',
            'metadata' => ['customer_id' => $context['customer_id'] ?? null],
        ]));

        event(new LeadCreated([
            'scope' => $scope,
            'email' => $context['email'] ?? null,
            'phone' => $context['phone'] ?? null,
        ]));
    }

    protected function resolveLocalAnswer(string $message, array $scope): ?array
    {
        if (! $this->settings->aiConfiguration($scope)['local_first']) {
            return null;
        }

        $faq = $this->resolveFaq($message, $scope);
        if ($faq !== null) {
            return [
                'message' => $faq,
                'source' => 'faq',
                'needs_handoff' => false,
            ];
        }

        $knowledge = $this->resolveKnowledge($message, $scope);
        if ($knowledge !== null) {
            return [
                'message' => $knowledge,
                'source' => 'knowledge',
                'needs_handoff' => false,
            ];
        }

        $commerce = $this->resolveCommerce($message);
        if ($commerce !== null) {
            return [
                'message' => $commerce,
                'source' => 'commerce',
                'needs_handoff' => false,
            ];
        }

        $fallback = $this->resolveBuiltInFaq($message);
        if ($fallback !== null) {
            return [
                'message' => $fallback,
                'source' => 'built_in_faq',
                'needs_handoff' => false,
            ];
        }

        return null;
    }

    protected function resolveCommerce(string $message): ?string
    {
        if (! config('devniox-ai.commerce.enabled', true)) {
            return null;
        }

        $table = (string) config('devniox-ai.commerce.table', 'products');
        $nameColumn = (string) config('devniox-ai.commerce.name_column', 'name');
        $slugColumn = (string) config('devniox-ai.commerce.slug_column', 'slug');
        $priceColumn = (string) config('devniox-ai.commerce.price_column', 'new_price');

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $nameColumn) || ! Schema::hasColumn($table, $priceColumn)) {
            return null;
        }

        $terms = $this->searchTerms($message);
        if ($terms === []) {
            return null;
        }

        $query = DB::table($table);
        $statusColumn = (string) config('devniox-ai.commerce.status_column', 'status');

        if (Schema::hasColumn($table, $statusColumn)) {
            $query->where($statusColumn, config('devniox-ai.commerce.active_value', 1));
        }

        $query->where(function ($inner) use ($nameColumn, $slugColumn, $table, $terms) {
            foreach ($terms as $term) {
                $inner->orWhere($nameColumn, 'like', '%'.$term.'%');

                if (Schema::hasColumn($table, $slugColumn)) {
                    $inner->orWhere($slugColumn, 'like', '%'.$term.'%');
                }
            }
        });

        $product = $query->latest('id')->limit(30)->get()->sortByDesc(function ($product) use ($nameColumn, $slugColumn, $terms) {
            $haystack = mb_strtolower(trim(((string) ($product->{$nameColumn} ?? '')).' '.((string) ($product->{$slugColumn} ?? ''))));

            return collect($terms)
                ->filter(fn ($term) => str_contains($haystack, $term))
                ->count();
        })->first();

        if (! $product) {
            return null;
        }

        return $this->commerceResponse($product);
    }

    protected function searchTerms(string $message): array
    {
        $stopWords = ['price', 'stock', 'available', 'delivery', 'product', 'koto', 'ase', 'ache', 'ki', 'what', 'is', 'the', 'please', 'tell', 'about'];

        return collect(preg_split('/\s+/', mb_strtolower($message)) ?: [])
            ->map(fn ($term) => trim($term, " \t\n\r\0\x0B?.!,;:'\"()[]{}"))
            ->filter(fn ($term) => mb_strlen($term) >= 3 && ! in_array($term, $stopWords, true))
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    protected function commerceResponse(object $product): string
    {
        $nameColumn = (string) config('devniox-ai.commerce.name_column', 'name');
        $slugColumn = (string) config('devniox-ai.commerce.slug_column', 'slug');
        $priceColumn = (string) config('devniox-ai.commerce.price_column', 'new_price');
        $oldPriceColumn = (string) config('devniox-ai.commerce.old_price_column', 'old_price');
        $stockColumn = (string) config('devniox-ai.commerce.stock_column', 'stock');
        $language = (string) config('devniox-ai.ai.default_language', 'en');

        $name = (string) ($product->{$nameColumn} ?? 'This product');
        $price = $product->{$priceColumn} ?? null;
        $oldPrice = $product->{$oldPriceColumn} ?? null;
        $stock = $product->{$stockColumn} ?? null;
        $url = $this->productUrl($product, $slugColumn);

        if ($language === 'bn') {
            $parts = [$name];
            if ($price !== null) {
                $parts[] = 'বর্তমান দাম '.$this->money($price);
            }
            if ($oldPrice !== null && (float) $oldPrice > (float) $price) {
                $parts[] = 'আগের দাম '.$this->money($oldPrice);
            }
            if ($stock !== null) {
                $parts[] = ((int) $stock > 0) ? 'স্টকে আছে' : 'স্টক আউট';
            }
            if ($url !== null) {
                $parts[] = 'লিংক: '.$url;
            }

            return implode(', ', $parts).'.';
        }

        $parts = [$name];
        if ($price !== null) {
            $parts[] = 'current price '.$this->money($price);
        }
        if ($oldPrice !== null && (float) $oldPrice > (float) $price) {
            $parts[] = 'old price '.$this->money($oldPrice);
        }
        if ($stock !== null) {
            $parts[] = ((int) $stock > 0) ? 'in stock' : 'out of stock';
        }
        if ($url !== null) {
            $parts[] = 'link: '.$url;
        }

        return implode(', ', $parts).'.';
    }

    protected function productUrl(object $product, string $slugColumn): ?string
    {
        $template = (string) config('devniox-ai.commerce.product_url', '/product/{id}');
        if ($template === '') {
            return null;
        }

        $id = rawurlencode((string) ($product->id ?? ''));
        $slug = rawurlencode(trim((string) ($product->{$slugColumn} ?? '')) ?: $id);

        $path = str_replace(
            ['{id}', '{slug}'],
            [$id, $slug],
            $template
        );

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    protected function money(mixed $amount): string
    {
        return '৳'.number_format((float) $amount, 0);
    }

    protected function resolveFaq(string $message, array $scope): ?string
    {
        if (! $this->tablesReady(['devniox_faqs'])) {
            return null;
        }

        $query = Faq::query()->active();
        TenantResolver::applyScope($query, $scope);

        $faqs = $query->latest('id')->limit(100)->get();
        $normalized = mb_strtolower($message);

        foreach ($faqs as $faq) {
            $question = mb_strtolower((string) $faq->question);
            $keywords = array_filter((array) $faq->keywords);

            if ($question !== '' && str_contains($normalized, $question)) {
                return (string) $faq->answer;
            }

            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower((string) $keyword);
                if ($keyword !== '' && str_contains($normalized, $keyword)) {
                    return (string) $faq->answer;
                }
            }
        }

        return null;
    }

    protected function resolveKnowledge(string $message, array $scope): ?string
    {
        if (! $this->tablesReady(['devniox_knowledge_items'])) {
            return null;
        }

        $terms = collect(preg_split('/\s+/', mb_strtolower($message)) ?: [])
            ->map(fn ($term) => trim($term, " \t\n\r\0\x0B?.!,;:'\"()[]{}"))
            ->filter(fn ($term) => mb_strlen($term) >= 3)
            ->unique()
            ->take(6)
            ->values();

        if ($terms->isEmpty()) {
            return null;
        }

        $query = KnowledgeItem::query()->active();
        TenantResolver::applyScope($query, $scope);

        $query->where(function ($inner) use ($terms) {
            foreach ($terms as $term) {
                $inner->orWhere('title', 'like', '%'.$term.'%')
                    ->orWhere('content', 'like', '%'.$term.'%');
            }
        });

        $item = $query->latest('id')->first();
        if (! $item) {
            return null;
        }

        $limit = (int) config('devniox-ai.answering.knowledge_excerpt_length', 900);

        return Str::limit((string) $item->content, $limit);
    }

    protected function resolveBuiltInFaq(string $message): ?string
    {
        $normalized = mb_strtolower($message);

        $faqs = [
            'hello' => 'Hello! How can I help you today?',
            'hi' => 'Hello! How can I help you today?',
            'help' => 'I can answer product, service, order, pricing, and support questions.',
            'support' => 'Please write your question. I will try to help first, then connect you with support if needed.',
            'human' => $this->handoffMessage(),
            'agent' => $this->handoffMessage(),
        ];

        foreach ($faqs as $keyword => $answer) {
            if (str_contains($normalized, $keyword)) {
                return $answer;
            }
        }

        return null;
    }

    protected function shouldUseAi(array $scope): bool
    {
        $settings = $this->settings->aiConfiguration($scope);

        return $settings['use_ai_fallback'] && filled($settings['api_key']);
    }

    protected function resolveAiAnswer(string $message, array $scope, array $context, ?Conversation $conversation): ?array
    {
        try {
            $settings = $this->settings->aiConfiguration($scope);
            $provider = $this->aiProvider instanceof OpenAiProvider
                ? new OpenAiProvider($settings['api_key'], $settings['model'])
                : $this->aiProvider;

            $response = $provider
                ->withSystemInstructions((string) $settings['system_instructions'])
                ->withBusinessContext($this->businessContext($scope, $context))
                ->withConversationHistory($this->conversationHistory($conversation))
                ->generateResponse(['message' => $message]);

            $content = trim((string) ($response['content'] ?? ''));
            if ($content === '') {
                return null;
            }

            return [
                'message' => $content,
                'source' => 'ai',
                'needs_handoff' => false,
            ];
        } catch (RuntimeException|Throwable $exception) {
            return [
                'message' => $this->handoffMessage(),
                'source' => 'ai_unavailable',
                'needs_handoff' => true,
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ];
        }
    }

    protected function businessContext(array $scope, array $context): array
    {
        return [
            'business_key' => $scope['business_key'] ?? null,
            'business_name' => config('devniox-ai.business.name'),
            'business_description' => config('devniox-ai.business.description'),
            'support_hours' => config('devniox-ai.business.support_hours'),
            'contact_email' => config('devniox-ai.business.contact_email'),
            'contact_phone' => config('devniox-ai.business.contact_phone'),
            'customer_name' => $context['customer_name'] ?? null,
        ];
    }

    protected function conversationHistory(?Conversation $conversation): array
    {
        if (! $conversation || ! $this->tablesReady(['devniox_messages'])) {
            return [];
        }

        $limit = (int) config('devniox-ai.answering.max_history_messages', 6);

        return $conversation->messages()
            ->latest('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(fn (Message $message) => [
                'role' => $message->direction === 'outgoing' ? 'assistant' : 'user',
                'content' => Str::limit((string) $message->content, 600),
            ])
            ->values()
            ->all();
    }

    protected function buildHandoff(string $message): array
    {
        if (! config('devniox-ai.handoff.enabled', true)) {
            return [];
        }

        $text = rawurlencode(Str::limit($message, 500, ''));
        $whatsappNumber = preg_replace('/\D+/', '', (string) config('devniox-ai.handoff.whatsapp_number'));

        return array_filter([
            'message' => config('devniox-ai.handoff.message'),
            'whatsapp_url' => $whatsappNumber ? 'https://wa.me/'.$whatsappNumber.'?text='.$text : null,
            'messenger_url' => config('devniox-ai.handoff.messenger_url'),
            'email' => config('devniox-ai.handoff.email'),
            'phone' => config('devniox-ai.handoff.phone'),
        ]);
    }

    protected function handoffMessage(): string
    {
        return (string) config('devniox-ai.handoff.message', 'I can connect you with our support team.');
    }

    protected function tablesReady(array $tables): bool
    {
        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    return false;
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    protected function redactContext(array $context): array
    {
        unset($context['request'], $context['token'], $context['api_key']);

        return $context;
    }
}
