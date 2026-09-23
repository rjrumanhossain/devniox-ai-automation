<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Events;

class AiResponseGenerated
{
    public function __construct(public array $payload)
    {
    }
}
