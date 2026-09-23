<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Channels;

use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;

class WebsiteChannel implements ChannelInterface
{
    public function sendMessage(array $payload): array
    {
        return [
            'channel' => 'website',
            'status' => 'queued',
            'provider_message_id' => $payload['customer_id'] ?? 'web-'.uniqid(),
        ];
    }

    public function validateWebhook(array $payload, ?string $signature = null): bool
    {
        return ! empty($payload) && is_array($payload);
    }

    public function parseIncomingMessage(array $payload): array
    {
        return [
            'channel' => 'website',
            'customer_id' => $payload['customer_id'] ?? null,
            'sender_id' => $payload['visitor_id'] ?? ($payload['customer_id'] ?? 'anonymous'),
            'message' => $payload['message'] ?? '',
            'provider_message_id' => $payload['message_id'] ?? null,
        ];
    }

    public function getConnectionStatus(): array
    {
        return [
            'channel' => 'website',
            'connected' => true,
            'status' => 'configured',
        ];
    }
}
