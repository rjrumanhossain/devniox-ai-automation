<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Channels;

use Devniox\AiAutomation\Channels\Contracts\ChannelInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacebookMessengerChannel implements ChannelInterface
{
    public function __construct(protected array $credentials = []) {}

    public function sendMessage(array $payload): array
    {
        if (empty($this->credential('page_access_token'))) {
            throw new RuntimeException('Messenger credentials are not configured.');
        }

        $response = Http::asJson()->post(
            rtrim((string) $this->credential('api_url'), '/').'/me/messages?access_token='.urlencode((string) $this->credential('page_access_token')),
            [
                'recipient' => ['id' => $payload['to']],
                'message' => ['text' => $payload['message']],
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('Messenger API error: '.$response->body());
        }

        return [
            'channel' => 'messenger',
            'status' => 'sent',
            'provider_message_id' => $response->json('message_id'),
        ];
    }

    public function validateWebhook(array $payload, ?string $signature = null): bool
    {
        $verifyToken = $this->credential('verify_token');

        if ($verifyToken && isset($payload['hub_mode'], $payload['hub_verify_token'])) {
            return $payload['hub_verify_token'] === $verifyToken;
        }

        return ! empty($payload);
    }

    public function validateRawWebhook(string $rawBody, ?string $signature = null): bool
    {
        $secret = $this->credential('app_secret');

        if (empty($secret)) {
            return true;
        }

        if ($signature === null) {
            return false;
        }

        $actual = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $actual);
    }

    public function parseIncomingMessage(array $payload): array
    {
        $entry = $payload['entry'][0] ?? [];
        $messaging = $entry['messaging'][0] ?? [];
        $sender = $messaging['sender'] ?? [];
        $message = $messaging['message'] ?? [];

        return [
            'channel' => 'messenger',
            'customer_id' => $sender['id'] ?? null,
            'sender_id' => $sender['id'] ?? null,
            'message' => $message['text'] ?? '',
            'provider_message_id' => $message['mid'] ?? null,
        ];
    }

    public function getConnectionStatus(): array
    {
        return [
            'channel' => 'messenger',
            'connected' => ! empty($this->credential('page_access_token')),
            'status' => ! empty($this->credential('page_access_token')) ? 'configured' : 'not_configured',
        ];
    }

    protected function credential(string $key): mixed
    {
        return $this->credentials[$key] ?? config('devniox-ai.messenger.'.$key);
    }
}
