<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Support;

use Illuminate\Http\Request;

class TenantResolver
{
    public static function resolve(?Request $request = null, array $context = []): array
    {
        $resolved = config('devniox-ai.tenant.resolver')
            ? call_user_func(config('devniox-ai.tenant.resolver'), $request, $context)
            : null;

        if (! is_array($resolved)) {
            $resolved = ['tenant_id' => $resolved];
        }

        $businessKey = $context['business_key']
            ?? $request?->input('business_key')
            ?? $request?->header('X-Devniox-Business-Key')
            ?? $request?->getHost();

        return [
            'business_key' => $businessKey,
            'owner_type' => $resolved['owner_type'] ?? $context['owner_type'] ?? null,
            'owner_id' => $resolved['owner_id'] ?? $context['owner_id'] ?? null,
            'tenant_id' => $resolved['tenant_id'] ?? $context['tenant_id'] ?? null,
        ];
    }

    public static function applyScope($query, array $scope)
    {
        foreach (['business_key', 'owner_type', 'owner_id', 'tenant_id'] as $key) {
            if (($scope[$key] ?? null) !== null) {
                $query->where($key, $scope[$key]);
            }
        }

        return $query;
    }
}
