<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Channels\Contracts;

interface ChannelInterface
{
    public function sendMessage(array $payload): array;

    public function validateWebhook(array $payload, ?string $signature = null): bool;

    public function parseIncomingMessage(array $payload): array;

    public function getConnectionStatus(): array;
}
