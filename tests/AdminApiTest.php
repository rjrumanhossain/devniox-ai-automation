<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Tests;

use Devniox\AiAutomation\Models\AiSetting;
use Devniox\AiAutomation\Models\ChannelConnection;
use Devniox\AiAutomation\Models\Faq;
use Devniox\AiAutomation\Services\SettingsService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class AdminApiTest extends TestCase
{
    public function test_admin_can_create_faq_and_chat_uses_it(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        config()->set('devniox-ai.ai.api_key', null);

        $this->postJson('/devniox-ai/admin/faqs', [
            'business_key' => 'shop-2',
            'question' => 'refund',
            'answer' => 'Refunds are available within 7 days.',
            'keywords' => ['return'],
        ])->assertCreated()->assertJsonPath('data.business_key', 'shop-2');

        $this->postJson('/devniox-ai/chat', [
            'business_key' => 'shop-2',
            'message' => 'How do I return an order?',
        ])->assertOk()
            ->assertJsonPath('source', 'faq')
            ->assertJsonPath('message', 'Refunds are available within 7 days.');
    }

    public function test_admin_channel_credentials_are_encrypted_and_not_returned(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->postJson('/devniox-ai/admin/channels', [
            'business_key' => 'shop-3',
            'channel' => 'whatsapp',
            'connection_name' => 'Main WhatsApp',
            'credentials' => [
                'token' => 'secret-token',
                'phone_id' => 'phone-123',
                'webhook_secret' => 'hook-secret',
            ],
        ])->assertOk()
            ->assertJsonPath('data.configured', true)
            ->assertJsonMissing(['token' => 'secret-token']);

        $connection = ChannelConnection::query()->firstOrFail();

        $this->assertStringNotContainsString('secret-token', (string) $connection->credentials_encrypted);
        $this->assertSame('secret-token', json_decode(Crypt::decryptString($connection->credentials_encrypted), true)['token']);
    }

    public function test_channel_connection_can_be_deleted_without_affecting_other_channel_or_business(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $service = app(SettingsService::class);
        $service->putChannel(['business_key' => 'shop-channel-a'], 'messenger', ['page_access_token' => 'a-token']);
        $service->putChannel(['business_key' => 'shop-channel-a'], 'whatsapp', ['token' => 'wa-token']);
        $service->putChannel(['business_key' => 'shop-channel-b'], 'messenger', ['page_access_token' => 'b-token']);

        $this->deleteJson('/devniox-ai/admin/channels', [
            'business_key' => 'shop-channel-a',
            'channel' => 'messenger',
        ])->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertSame([], $service->credentials(['business_key' => 'shop-channel-a'], 'messenger'));
        $this->assertSame('wa-token', $service->credentials(['business_key' => 'shop-channel-a'], 'whatsapp')['token']);
        $this->assertSame('b-token', $service->credentials(['business_key' => 'shop-channel-b'], 'messenger')['page_access_token']);
    }

    public function test_channel_enabled_checkbox_accepts_integer_boolean_values(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->postJson('/devniox-ai/admin/channels', [
            'business_key' => 'shop-channel-toggle',
            'channel' => 'messenger',
            'credentials' => ['page_access_token' => 'test-token'],
            'enabled' => 0,
        ])->assertOk()
            ->assertJsonPath('data.enabled', false);

        $this->assertDatabaseHas('devniox_channel_connections', [
            'business_key' => 'shop-channel-toggle',
            'channel' => 'messenger',
            'enabled' => false,
        ]);

        $this->postJson('/devniox-ai/admin/channels', [
            'business_key' => 'shop-channel-toggle',
            'channel' => 'messenger',
            'credentials' => ['page_access_token' => 'test-token'],
            'enabled' => 1,
        ])->assertOk()
            ->assertJsonPath('data.enabled', true);
    }

    public function test_admin_scopes_faqs_by_business_key(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Faq::query()->create([
            'business_key' => 'shop-a',
            'question' => 'hours',
            'answer' => 'A hours',
            'is_active' => true,
        ]);

        Faq::query()->create([
            'business_key' => 'shop-b',
            'question' => 'hours',
            'answer' => 'B hours',
            'is_active' => true,
        ]);

        $this->getJson('/devniox-ai/admin/faqs?business_key=shop-a')
            ->assertOk()
            ->assertJsonPath('data.data.0.answer', 'A hours')
            ->assertJsonMissing(['answer' => 'B hours']);
    }

    public function test_browser_requests_render_admin_pages_while_api_requests_remain_json(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->get('/devniox-ai/admin')
            ->assertRedirect('/devniox-ai/admin/settings');

        $this->get('/devniox-ai/admin/settings')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('Automation settings')
            ->assertSee('OpenAI API key')
            ->assertSee('Save AI settings');

        $this->get('/devniox-ai/admin/faqs')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('Add FAQ');

        $this->get('/devniox-ai/admin/knowledge')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('Add knowledge');

        $this->get('/devniox-ai/admin/conversations')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertSee('Conversations');

        $this->getJson('/devniox-ai/admin/faqs')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_ai_settings_saved_from_admin_drive_chat_without_environment_api_key(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        config()->set('devniox-ai.ai.api_key', null);
        config()->set('devniox-ai.ai.use_ai_fallback', false);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'AI response from saved settings']]],
            ], 200),
        ]);

        $this->postJson('/devniox-ai/admin/settings', [
            'business_key' => 'shop-ai',
            'api_key' => 'database-ai-key',
            'model' => 'database-model',
            'use_ai_fallback' => true,
            'local_first' => false,
            'system_instructions' => 'Answer briefly.',
        ])->assertOk()
            ->assertJsonPath('data.ai.api_key_configured', true)
            ->assertJsonMissing(['api_key' => 'database-ai-key']);

        $this->postJson('/devniox-ai/chat', [
            'business_key' => 'shop-ai',
            'message' => 'quartz calibration request 91b',
        ])->assertOk()
            ->assertJsonPath('source', 'ai')
            ->assertJsonPath('message', 'AI response from saved settings');

        Http::assertSent(fn ($request) => $request['model'] === 'database-model'
            && $request->hasHeader('Authorization', 'Bearer database-ai-key'));
    }

    public function test_ai_configuration_is_encrypted_and_api_key_is_never_listed(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $scope = ['business_key' => 'shop-settings'];

        app(SettingsService::class)->saveAiConfiguration($scope, [
            'api_key' => 'secret-ai-key',
            'model' => 'gpt-test-model',
            'use_ai_fallback' => true,
            'local_first' => false,
        ]);

        $apiKeySetting = AiSetting::query()->where('key', 'ai.api_key')->firstOrFail();
        $this->assertSame('secret-ai-key', Crypt::decryptString($apiKeySetting->value));

        $listed = app(SettingsService::class)->list($scope);
        $this->assertSame('gpt-test-model', $listed['ai']['model']);
        $this->assertTrue($listed['ai']['api_key_configured']);
        $this->assertTrue($listed['ai']['api_key_saved']);
        $this->assertArrayNotHasKey('api_key', $listed['ai']);
        $this->assertArrayNotHasKey('ai.api_key', $listed['settings']);

        app(SettingsService::class)->saveAiConfiguration($scope, ['clear_api_key' => 1]);
        $this->assertFalse(app(SettingsService::class)->list($scope)['ai']['api_key_configured']);
    }

    public function test_environment_api_key_is_available_without_being_marked_database_saved(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        config()->set('devniox-ai.ai.api_key', 'environment-key');

        $listed = app(SettingsService::class)->list(['business_key' => 'shop-env']);

        $this->assertTrue($listed['ai']['api_key_configured']);
        $this->assertFalse($listed['ai']['api_key_saved']);
        $this->assertArrayNotHasKey('api_key', $listed['ai']);
    }

    public function test_generic_setting_can_be_deleted_only_from_its_business_scope(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $service = app(SettingsService::class);
        $service->putSetting(['business_key' => 'shop-delete-a'], 'legacy-key', 'A value');
        $service->putSetting(['business_key' => 'shop-delete-b'], 'legacy-key', 'B value');

        $this->deleteJson('/devniox-ai/admin/settings', [
            'business_key' => 'shop-delete-a',
            'key' => 'legacy-key',
        ])->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertSame([], $service->list(['business_key' => 'shop-delete-a'])['settings']);
        $this->assertSame('B value', $service->list(['business_key' => 'shop-delete-b'])['settings']['legacy-key']);
    }
}
