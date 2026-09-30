<?php

namespace App\Actions;

use App\Models\AiProviderCredential;
use App\Models\Business;
use App\Models\BusinessApiKey;
use App\Models\ChannelConnection;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterTenant
{
    public function handle(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $plan = Plan::query()
                ->when($data['plan_slug'] ?? null, fn ($query, string $slug) => $query->where('slug', $slug))
                ->where('is_active', true)
                ->orderBy('price')
                ->first();

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'customer',
            ]);

            $username = $data['username'];
            $tenantDomain = $username.'.'.config('tenancy.domain');

            $business = Business::query()->create([
                'owner_id' => $user->id,
                'plan_id' => $plan?->id,
                'name' => $data['business_name'],
                'slug' => $username,
                'tenant_domain' => $tenantDomain,
                'status' => 'trial',
                'timezone' => 'Asia/Dhaka',
            ]);

            $plainApiKey = 'dnx_live_'.$username.'_'.Str::random(32);

            BusinessApiKey::query()->create([
                'business_id' => $business->id,
                'name' => 'Website package key',
                'key_prefix' => 'dnx_live_'.$username,
                'key_hash' => hash('sha256', $plainApiKey),
                'abilities' => ['chat:send', 'chat:receive', 'knowledge:read'],
                'is_active' => true,
            ]);

            ChannelConnection::query()->create([
                'business_id' => $business->id,
                'type' => 'website_chat',
                'external_id' => $tenantDomain,
                'display_name' => $data['business_name'].' Website',
                'credential_payload' => ['tenant_domain' => $tenantDomain],
                'webhook_secret' => Str::random(40),
                'status' => 'draft',
            ]);

            AiProviderCredential::query()->create([
                'business_id' => $business->id,
                'provider' => 'devniox_managed',
                'mode' => 'fallback',
                'credential_payload' => ['managed' => true],
                'monthly_token_limit' => 100000,
                'status' => 'available',
            ]);

            return [
                'user' => $user,
                'business' => $business->fresh(['plan', 'apiKeys', 'channels', 'aiProviders']),
                'api_key' => $plainApiKey,
            ];
        });
    }
}
