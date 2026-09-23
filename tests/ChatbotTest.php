<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

class ChatbotTest extends TestCase
{
    public function test_chatbot_route_accepts_messages_and_returns_standard_payload(): void
    {
        $response = $this->post('/devniox-ai/chat', [
            'message' => 'Hello, I need help with my order',
            'customer_name' => 'Jane',
            'phone' => '+123456789',
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
        $this->assertNotEmpty($response->json('conversation_id'));
    }

    public function test_chatbot_rejects_empty_message(): void
    {
        $response = $this->post('/devniox-ai/chat', [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
    }
}
