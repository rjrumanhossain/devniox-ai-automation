<?php

namespace Database\Seeders;

use App\Models\AiProviderCredential;
use App\Models\Business;
use App\Models\BusinessApiKey;
use App\Models\ChannelConnection;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
            ],
        );

        $customer = User::updateOrCreate(
            ['email' => 'rumank@gmail.com'],
            [
                'name' => 'Ruman Customer',
                'password' => Hash::make('12345678'),
                'role' => 'customer',
            ],
        );

        $plans = collect([
            ['name' => 'Starter', 'slug' => 'starter', 'billing_cycle' => 'monthly', 'price' => 1490, 'message_limit' => 3000, 'channel_limit' => 1],
            ['name' => 'Growth', 'slug' => 'growth', 'billing_cycle' => 'monthly', 'price' => 3990, 'message_limit' => 12000, 'channel_limit' => 3],
            ['name' => 'Business', 'slug' => 'business', 'billing_cycle' => 'monthly', 'price' => 8990, 'message_limit' => 40000, 'channel_limit' => 8],
            ['name' => 'Enterprise', 'slug' => 'enterprise-yearly', 'billing_cycle' => 'yearly', 'price' => 89900, 'message_limit' => 600000, 'channel_limit' => 25],
        ])->map(fn (array $plan) => Plan::updateOrCreate(['slug' => $plan['slug']], $plan));

        $businessPlan = $plans->firstWhere('slug', 'business');

        $business = Business::updateOrCreate(
            ['slug' => 'boneekbd'],
            [
                'owner_id' => $customer->id,
                'plan_id' => $businessPlan?->id,
                'name' => 'BoneekBD',
                'status' => 'active',
                'timezone' => 'Asia/Dhaka',
            ],
        );

        BusinessApiKey::updateOrCreate(
            ['business_id' => $business->id, 'key_prefix' => 'dnx_live_boneekbd'],
            [
                'name' => 'Website package key',
                'key_hash' => hash('sha256', 'dnx_live_boneekbd_demo_12345678'),
                'abilities' => ['chat:send', 'chat:receive', 'knowledge:read'],
                'is_active' => true,
            ],
        );

        foreach ([
            ['type' => 'messenger', 'external_id' => 'boneekbd-page', 'display_name' => 'BoneekBD Page', 'status' => 'connected'],
            ['type' => 'whatsapp', 'external_id' => '+8801*********', 'display_name' => 'BoneekBD WhatsApp', 'status' => 'connected'],
            ['type' => 'website_chat', 'external_id' => 'boneekbd.test', 'display_name' => 'BoneekBD Website', 'status' => 'connected'],
            ['type' => 'instagram', 'external_id' => null, 'display_name' => 'Business DM', 'status' => 'draft'],
        ] as $channel) {
            ChannelConnection::updateOrCreate(
                ['business_id' => $business->id, 'type' => $channel['type']],
                [
                    ...$channel,
                    'credential_payload' => ['seeded' => true],
                    'webhook_secret' => Str::random(32),
                    'last_seen_at' => now(),
                ],
            );
        }

        foreach ([
            ['provider' => 'openai', 'mode' => 'own_api_key', 'status' => 'connected'],
            ['provider' => 'claude', 'mode' => 'own_api_key', 'status' => 'draft'],
            ['provider' => 'devniox_managed', 'mode' => 'fallback', 'status' => 'available'],
        ] as $provider) {
            AiProviderCredential::updateOrCreate(
                ['business_id' => $business->id, 'provider' => $provider['provider']],
                [
                    ...$provider,
                    'credential_payload' => ['masked_key' => '****'],
                    'monthly_token_limit' => 500000,
                ],
            );
        }

        $admin->businesses()->firstOrCreate(
            ['slug' => 'devniox-platform'],
            [
                'plan_id' => null,
                'name' => 'Devniox Platform',
                'status' => 'active',
                'timezone' => 'Asia/Dhaka',
            ],
        );
    }
}
