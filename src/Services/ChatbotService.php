<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Services;

use Devniox\AiAutomation\AI\Contracts\AiProviderInterface;
use Devniox\AiAutomation\AI\Providers\OpenAiProvider;
use Illuminate\Support\Str;
use RuntimeException;

class ChatbotService
{
    public function __construct(protected ?AiProviderInterface $aiProvider = null)
    {
        $this->aiProvider ??= new OpenAiProvider(
            config('devniox-ai.ai.api_key'),
            config('devniox-ai.ai.model')
        );
    }

    public function handle(string $message, array $context = []): array
    {
        $normalized = $this->normalizeMessage($message);

        $faqResult = $this->resolveFaq($normalized);
        if ($faqResult !== null) {
            return [
                'success' => true,
                'message' => $faqResult,
                'conversation_id' => $context['conversation_id'] ?? (string) Str::uuid(),
                'message_id' => (string) Str::uuid(),
            ];
        }

        try {
            $response = $this->aiProvider->withSystemInstructions(config('devniox-ai.ai.system_instructions', 'You are a helpful support assistant.'))
                ->withBusinessContext([
                    'business_name' => 'Devniox AI Automation',
                    'business_description' => 'AI-powered customer support platform for ecommerce and service business websites.',
                    'support_hours' => 'Monday to Saturday, 9am to 6pm',
                    'contact_email' => 'support@example.com',
                    'contact_phone' => '+1234567890',
                    'customer_name' => $context['customer_name'] ?? null,
                    'customer_email' => $context['email'] ?? null,
                    'customer_phone' => $context['phone'] ?? null,
                ])
                ->withConversationHistory([])
                ->generateResponse(['message' => $normalized]);

            $content = trim((string) ($response['content'] ?? ''));
            if ($content === '') {
                $content = 'I am sorry, I could not generate a response right now. Please try again in a moment.';
            }

            return [
                'success' => true,
                'message' => $content,
                'conversation_id' => $context['conversation_id'] ?? (string) Str::uuid(),
                'message_id' => (string) Str::uuid(),
            ];
        } catch (RuntimeException $exception) {
            return [
                'success' => false,
                'message' => 'I am unable to answer right now because the AI service is not configured or unavailable. Please try again later or contact support.',
                'conversation_id' => $context['conversation_id'] ?? (string) Str::uuid(),
                'message_id' => (string) Str::uuid(),
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ];
        }
    }

    protected function normalizeMessage(string $message): string
    {
        $message = preg_replace('/\s+/', ' ', trim($message)) ?? $message;

        return mb_substr($message, 0, 1000);
    }

    protected function resolveFaq(string $message): ?string
    {
        $normalized = strtolower($message);

        $faqs = [
            'hello' => 'Hello! How can I help you today?',
            'hi' => 'Hi there! How can I help today?',
            'help' => 'I can help answer product questions, support questions, and guide you to the right next step.',
            'pricing' => 'Please contact our sales or support team for current pricing and package details.',
            'support' => 'You can contact our support team directly or ask me about our services and policies.',
            'hours' => 'Our support hours are Monday to Saturday, 9:00 AM to 6:00 PM.',
            'business hours' => 'Our support hours are Monday to Saturday, 9:00 AM to 6:00 PM.',
            'contact' => 'You can email support@example.com or call +1234567890 for assistance.',
            'where are you' => 'I am your AI support assistant for this business website.',
            'human' => 'I can connect you to a human support representative. Please leave a message and support will follow up.',
        ];

        foreach ($faqs as $keyword => $answer) {
            if (str_contains($normalized, (string) $keyword)) {
                return $answer;
            }
        }

        return null;
    }
}
