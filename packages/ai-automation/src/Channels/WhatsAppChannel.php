<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Channels;

use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppChannel implements ChannelInterface
{
    public function __construct(protected array $credentials = []) {}

    public function sendMessage(array $payload): array
    {
        if (empty($this->credential('token')) || empty($this->credential('phone_id'))) {
            throw new RuntimeException('WhatsApp credentials are not configured.');
        }

        $response = Http::withToken((string) $this->credential('token'))->asJson()->post(
            $this->credential('api_url').'/'.$this->credential('phone_id').'/messages',
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
        $secret = $this->credential('webhook_secret');

        if (empty($secret)) {
            return ! empty($payload);
        }

        if ($signature === null) {
            return false;
        }

        $expected = hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), $secret);
        $actual = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;

        return hash_equals($expected, $actual);
    }

    public function validateRawWebhook(string $rawBody, ?string $signature = null): bool
    {
        $secret = $this->credential('webhook_secret');

        if (empty($secret)) {
            return true;
        }

        if ($signature === null) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);
        $actual = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;

        return hash_equals($expected, $actual);
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
            'connected' => ! empty($this->credential('token')) && ! empty($this->credential('phone_id')),
            'status' => ! empty($this->credential('token')) ? 'configured' : 'not_configured',
        ];
    }

    protected function credential(string $key): mixed
    {
        return $this->credentials[$key] ?? config('devniox-ai.whatsapp.'.$key);
    }
}
