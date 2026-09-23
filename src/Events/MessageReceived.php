<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Events;

class MessageReceived
{
    public function __construct(public array $payload)
    {
    }
}
