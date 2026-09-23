<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\Channels\FacebookMessengerChannel;
use Devniox\AiAutomation\Channels\WhatsAppChannel;
use Devniox\AiAutomation\Channels\WebsiteChannel;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ChannelContractTest extends TestCase
{
    public function test_website_channel_parses_messages(): void
    {
        $channel = new WebsiteChannel();

        $parsed = $channel->parseIncomingMessage([
            'customer_id' => 'cust-1',
            'visitor_id' => 'vis-1',
            'message' => 'Hello',
            'message_id' => 'msg-1',
        ]);

        $this->assertSame('website', $parsed['channel']);
        $this->assertSame('vis-1', $parsed['sender_id']);
    }

    public function test_whatsapp_requires_credentials(): void
    {
        config()->set('devniox-ai.whatsapp.token', null);
        config()->set('devniox-ai.whatsapp.phone_id', null);

        $this->expectException(RuntimeException::class);

        (new WhatsAppChannel())->sendMessage(['to' => '123', 'message' => 'Hi']);
    }

    public function test_messenger_channel_sends_message(): void
    {
        config()->set('devniox-ai.messenger.page_access_token', 'token-123');

        Http::fake([
            'https://graph.facebook.com/v18.0/me/messages?access_token=token-123' => Http::response([
                'message_id' => 'mid_123',
            ], 200),
        ]);

        $result = (new FacebookMessengerChannel())->sendMessage(['to' => '456', 'message' => 'Hi']);

        $this->assertSame('sent', $result['status']);
        $this->assertSame('mid_123', $result['provider_message_id']);
    }
}
