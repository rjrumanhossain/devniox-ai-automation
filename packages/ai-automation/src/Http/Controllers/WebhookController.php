<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
use Devniox\AiAutomation\Channels\FacebookMessengerChannel;
use Devniox\AiAutomation\Channels\WebsiteChannel;
use Devniox\AiAutomation\Channels\WhatsAppChannel;
use Devniox\AiAutomation\Events\WebhookReceived;
use Devniox\AiAutomation\Models\WebhookEvent;
use Devniox\AiAutomation\Services\ChatbotService;
use Devniox\AiAutomation\Services\SettingsService;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WebhookController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService, protected SettingsService $settings) {}

    public function whatsapp(Request $request): JsonResponse|Response
    {
        if ($request->isMethod('get')) {
            return $this->verifyWhatsApp($request);
        }

        return $this->handleChannel($request, 'whatsapp');
    }

    public function messenger(Request $request): JsonResponse|Response
    {
        if ($request->isMethod('get')) {
            return $this->verifyMessenger($request);
        }

        return $this->handleChannel($request, 'messenger');
    }

    public function website(Request $request): JsonResponse
    {
        return $this->handleChannel($request, 'website');
    }

    protected function handleChannel(Request $request, string $channelName): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('X-Hub-Signature-256')
            ?? $request->header('X-Devniox-Signature');
        $rawBody = $request->getContent();
        $scope = TenantResolver::resolve($request, $payload);
        $channel = $this->channel($channelName, $scope);

        if (! $this->validateWebhook($channel, $payload, $rawBody, $signature)) {
            return response()->json([
                'status' => 'rejected',
                'channel' => $channelName,
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        $parsed = $channel->parseIncomingMessage($payload);
        $event = $this->storeWebhookEvent($scope, $channelName, $payload, $signature, $parsed);

        event(new WebhookReceived([
            'channel' => $channelName,
            'payload' => $payload,
            'parsed' => $parsed,
            'scope' => $scope,
        ]));

        if (trim((string) ($parsed['message'] ?? '')) === '') {
            return response()->json([
                'status' => 'received',
                'channel' => $channelName,
                'idempotent' => true,
                'event_id' => $event?->id,
            ]);
        }

        $result = $this->chatbotService->handle((string) $parsed['message'], array_merge($scope, $parsed, [
            'request' => $request,
            'channel' => $channelName,
            'external_id' => $parsed['customer_id'] ?? null,
        ]));

        $sent = null;
        if (($result['success'] ?? false) && $this->canSendDirectReply($channelName, $channel, $parsed)) {
            try {
                $sent = $channel->sendMessage([
                    'to' => $parsed['customer_id'] ?? $parsed['sender_id'],
                    'message' => $result['message'],
                ]);
            } catch (Throwable $exception) {
                $sent = [
                    'status' => 'failed',
                    'error' => config('app.debug') ? $exception->getMessage() : 'Channel send failed.',
                ];
            }
        }

        return response()->json([
            'status' => 'received',
            'channel' => $channelName,
            'idempotent' => true,
            'event_id' => $event?->id,
            'reply' => $result,
            'sent' => $sent,
        ]);
    }

    protected function validateWebhook(ChannelInterface $channel, array $payload, string $rawBody, ?string $signature): bool
    {
        if (method_exists($channel, 'validateRawWebhook')) {
            return $channel->validateRawWebhook($rawBody, $signature);
        }

        return $channel->validateWebhook($payload, $signature);
    }

    protected function storeWebhookEvent(array $scope, string $channel, array $payload, ?string $signature, array $parsed): ?WebhookEvent
    {
        if (! Schema::hasTable('devniox_webhook_events')) {
            return null;
        }

        $providerEventId = $parsed['provider_message_id'] ?? null;

        if ($providerEventId === null || $providerEventId === '') {
            return WebhookEvent::query()->create(array_merge($scope, [
                'signature' => $signature,
                'channel' => $channel,
                'provider_event_id' => null,
                'payload' => $payload,
                'status' => 'received',
            ]));
        }

        return WebhookEvent::query()->updateOrCreate([
            'provider_event_id' => $providerEventId,
        ], array_merge($scope, [
            'signature' => $signature,
            'channel' => $channel,
            'payload' => $payload,
            'status' => 'received',
        ]));
    }

    protected function canSendDirectReply(string $channelName, ChannelInterface $channel, array $parsed): bool
    {
        if (empty($parsed['customer_id']) && empty($parsed['sender_id'])) {
            return false;
        }

        $status = $channel->getConnectionStatus();

        return $channelName !== 'website' && (bool) ($status['connected'] ?? false);
    }

    protected function channel(string $channelName, array $scope): ChannelInterface
    {
        return match ($channelName) {
            'whatsapp' => new WhatsAppChannel($this->settings->credentials($scope, 'whatsapp')),
            'messenger' => new FacebookMessengerChannel($this->settings->credentials($scope, 'messenger')),
            default => new WebsiteChannel,
        };
    }

    protected function verifyMessenger(Request $request): JsonResponse|Response
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $credentials = $this->settings->credentials($scope, 'messenger');
        $verifyToken = $credentials['verify_token'] ?? config('devniox-ai.messenger.verify_token');

        if (
            $request->query('hub_mode') === 'subscribe'
            && $verifyToken
            && hash_equals((string) $verifyToken, (string) $request->query('hub_verify_token'))
        ) {
            return response((string) $request->query('hub_challenge'), 200, ['Content-Type' => 'text/plain']);
        }

        return response()->json([
            'status' => 'rejected',
            'message' => 'Invalid verify token.',
        ], 403);
    }

    protected function verifyWhatsApp(Request $request): JsonResponse|Response
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $credentials = $this->settings->credentials($scope, 'whatsapp');
        $verifyToken = $credentials['verify_token'] ?? config('devniox-ai.whatsapp.verify_token');

        if (
            $request->query('hub_mode') === 'subscribe'
            && $verifyToken
            && hash_equals((string) $verifyToken, (string) $request->query('hub_verify_token'))
        ) {
            return response((string) $request->query('hub_challenge'), 200, ['Content-Type' => 'text/plain']);
        }

        return response()->json([
            'status' => 'rejected',
            'message' => 'Invalid verify token.',
        ], 403);
    }
}
