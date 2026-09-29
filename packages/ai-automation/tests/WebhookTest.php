<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\Channels\WhatsAppChannel;
use Devniox\AiAutomation\Services\SettingsService;
use Illuminate\Support\Facades\Http;

class WebhookTest extends TestCase
{
    public function test_webhook_routes_exist(): void
    {
        config()->set('devniox-ai.whatsapp.webhook_secret', null);

        $this->get('/devniox-ai/status')->assertOk();

        $response = $this->post('/devniox-ai/webhooks/whatsapp', [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'from' => '123',
                            'id' => 'msg-1',
                            'text' => ['body' => 'Hi'],
                        ]],
                    ],
                ]],
            ]],
        ]);

        $response->assertStatus(200);
        $this->assertSame('received', $response->json('status'));
    }

    public function test_whatsapp_webhook_validation(): void
    {
        config()->set('devniox-ai.whatsapp.webhook_secret', 'secret');
        $payload = ['hello' => 'world'];
        $signature = hash_hmac('sha256', json_encode($payload), 'secret');

        $this->assertTrue((new WhatsAppChannel)->validateWebhook($payload, $signature));
    }

    public function test_whatsapp_webhook_can_be_verified_by_meta(): void
    {
        config()->set('devniox-ai.whatsapp.verify_token', 'verify-me');

        $this->get('/devniox-ai/webhooks/whatsapp?hub_mode=subscribe&hub_verify_token=verify-me&hub_challenge=abc123')
            ->assertOk()
            ->assertSee('abc123');
    }

    public function test_messenger_webhook_auto_replies_with_saved_channel_credentials(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        app(SettingsService::class)->putChannel(
            ['business_key' => 'shop-page'],
            'messenger',
            ['page_access_token' => 'page-token', 'verify_token' => 'verify-token'],
            'Page',
        );

        Http::fake([
            'https://graph.facebook.com/v18.0/me/messages*' => Http::response([
                'recipient_id' => 'customer-1',
                'message_id' => 'sent-1',
            ]),
        ]);

        $this->postJson('/devniox-ai/webhooks/messenger?business_key=shop-page', [
            'entry' => [[
                'messaging' => [[
                    'sender' => ['id' => 'customer-1'],
                    'message' => ['mid' => 'incoming-1', 'text' => 'hello'],
                ]],
            ]],
        ])->assertOk()
            ->assertJsonPath('reply.source', 'built_in_faq')
            ->assertJsonPath('sent.status', 'sent')
            ->assertJsonPath('sent.provider_message_id', 'sent-1');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/me/messages')
            && $request['recipient']['id'] === 'customer-1'
            && $request['message']['text'] === 'Hello! How can I help you today?');
    }

    public function test_whatsapp_webhook_auto_replies_with_saved_channel_credentials(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        app(SettingsService::class)->putChannel(
            ['business_key' => 'shop-wa'],
            'whatsapp',
            ['token' => 'wa-token', 'phone_id' => 'phone-1', 'verify_token' => 'verify-token'],
            'WhatsApp',
        );

        Http::fake([
            'https://graph.facebook.com/v18.0/phone-1/messages' => Http::response([
                'messages' => [['id' => 'wa-sent-1']],
            ]),
        ]);

        $this->postJson('/devniox-ai/webhooks/whatsapp?business_key=shop-wa', [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'from' => '8801700000000',
                            'id' => 'wa-incoming-1',
                            'text' => ['body' => 'hello'],
                        ]],
                    ],
                ]],
            ]],
        ])->assertOk()
            ->assertJsonPath('reply.source', 'built_in_faq')
            ->assertJsonPath('sent.status', 'sent')
            ->assertJsonPath('sent.provider_message_id', 'wa-sent-1');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/phone-1/messages')
            && $request->hasHeader('Authorization', 'Bearer wa-token')
            && $request['to'] === '8801700000000'
            && $request['text']['body'] === 'Hello! How can I help you today?');
    }
}
