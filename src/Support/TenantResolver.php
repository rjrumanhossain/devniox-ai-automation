<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Support;

class TenantResolver
{
    public static function resolve(): mixed
    {
        return config('devniox-ai.tenant.resolver')
            ? call_user_func(config('devniox-ai.tenant.resolver'))
            : null;
    }
}
