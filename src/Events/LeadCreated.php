<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Events;

class LeadCreated
{
    public function __construct(public array $payload)
    {
    }
}
