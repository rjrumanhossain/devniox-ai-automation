<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\AI\Contracts;

interface AiProviderInterface
{
    public function generateResponse(array $payload): array;

    public function getModel(): string;

    public function withSystemInstructions(string $instructions): self;

    public function withBusinessContext(array $context): self;

    public function withConversationHistory(array $history): self;
}
