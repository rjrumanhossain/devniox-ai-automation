<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\Models\Faq;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

    public function test_chatbot_uses_scoped_faq_before_ai_and_persists_conversation(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        config()->set('devniox-ai.ai.api_key', null);

        Faq::query()->create([
            'business_key' => 'shop-1',
            'question' => 'delivery',
            'answer' => 'Delivery takes 2 days.',
            'keywords' => ['shipping'],
            'is_active' => true,
        ]);

        $response = $this->post('/devniox-ai/chat', [
            'business_key' => 'shop-1',
            'message' => 'Tell me about shipping',
            'email' => 'buyer@example.com',
        ]);

        $response->assertOk();
        $this->assertSame('faq', $response->json('source'));
        $this->assertSame('Delivery takes 2 days.', $response->json('message'));
        $this->assertTrue(Schema::hasTable('devniox_messages'));
        $this->assertDatabaseCount('devniox_messages', 2);
        $this->assertDatabaseHas('devniox_leads', ['email' => 'buyer@example.com']);
    }

    public function test_chatbot_returns_handoff_when_no_local_or_ai_answer_exists(): void
    {
        config()->set('devniox-ai.ai.api_key', null);
        config()->set('devniox-ai.handoff.whatsapp_number', '+15551234567');

        $response = $this->post('/devniox-ai/chat', [
            'message' => 'very specific unknown question',
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('needs_handoff'));
        $this->assertSame('handoff', $response->json('source'));
        $this->assertStringStartsWith('https://wa.me/15551234567', $response->json('handoff.whatsapp_url'));
    }

    public function test_chatbot_can_answer_from_commerce_catalog_without_ai(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        config()->set('devniox-ai.ai.api_key', null);
        config()->set('devniox-ai.ai.default_language', 'bn');
        config()->set('app.url', 'https://example.test');

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->decimal('new_price', 10, 2)->nullable();
            $table->decimal('old_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        \DB::table('products')->insert([
            'name' => 'Blue Hoodie',
            'slug' => 'blue-hoodie',
            'new_price' => 1250,
            'old_price' => 1500,
            'stock' => 8,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('products')->insert([
            'name' => 'Blue Wall Rack',
            'slug' => 'blue-wall-rack',
            'new_price' => 950,
            'old_price' => null,
            'stock' => 3,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/devniox-ai/chat', [
            'message' => 'Blue Hoodie price stock',
        ]);

        $response->assertOk();
        $this->assertSame('commerce', $response->json('source'));
        $this->assertStringContainsString('Blue Hoodie', $response->json('message'));
        $this->assertStringContainsString('৳1,250', $response->json('message'));
        $this->assertStringContainsString('https://example.test/product/blue-hoodie', $response->json('message'));
    }

    public function test_widget_turns_urls_into_clickable_links(): void
    {
        $response = $this->get('/devniox-ai/widget.js');

        $response->assertOk();
        $response->assertSee('appendTextWithLinks', false);
        $response->assertSee("document.createElement('a')", false);
        $response->assertSee("anchor.target = '_blank'", false);
    }
}
