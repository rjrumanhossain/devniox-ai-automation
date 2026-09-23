<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\Channels\WhatsAppChannel;

class WebhookTest extends TestCase
{
    public function test_webhook_routes_exist(): void
    {
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

        $this->assertTrue((new WhatsAppChannel())->validateWebhook($payload, $signature));
    }
}
