<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Channels;

use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppChannel implements ChannelInterface
{
    public function sendMessage(array $payload): array
    {
        if (empty(config('devniox-ai.whatsapp.token')) || empty(config('devniox-ai.whatsapp.phone_id'))) {
            throw new RuntimeException('WhatsApp credentials are not configured.');
        }

        $response = Http::asJson()->post(
            config('devniox-ai.whatsapp.api_url').'/'.config('devniox-ai.whatsapp.phone_id').'/messages',
            [
                'messaging_product' => 'whatsapp',
                'to' => $payload['to'],
                'type' => 'text',
                'text' => ['body' => $payload['message']],
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('WhatsApp API error: '.$response->body());
        }

        return [
            'channel' => 'whatsapp',
            'status' => 'sent',
            'provider_message_id' => $response->json('messages.0.id'),
        ];
    }

    public function validateWebhook(array $payload, ?string $signature = null): bool
    {
        $secret = config('devniox-ai.whatsapp.webhook_secret');

        if (empty($secret) || $signature === null) {
            return ! empty($payload);
        }

        return hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), $secret) === $signature;
    }

    public function parseIncomingMessage(array $payload): array
    {
        $entry = $payload['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];
        $messages = $value['messages'][0] ?? [];

        return [
            'channel' => 'whatsapp',
            'customer_id' => $messages['from'] ?? null,
            'sender_id' => $messages['from'] ?? null,
            'message' => $messages['text']['body'] ?? '',
            'provider_message_id' => $messages['id'] ?? null,
            'status' => $messages['status'] ?? 'received',
        ];
    }

    public function getConnectionStatus(): array
    {
        return [
            'channel' => 'whatsapp',
            'connected' => ! empty(config('devniox-ai.whatsapp.token')) && ! empty(config('devniox-ai.whatsapp.phone_id')),
            'status' => ! empty(config('devniox-ai.whatsapp.token')) ? 'configured' : 'not_configured',
        ];
    }
}
