<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers\Admin;

use Devniox\AiAutomation\Channels\FacebookMessengerChannel;
use Devniox\AiAutomation\Channels\WhatsAppChannel;
use Devniox\AiAutomation\Services\SettingsService;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function __construct(protected SettingsService $settings) {}

    public function index(Request $request): JsonResponse|View
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $data = $this->settings->list($scope);

        if (! $request->expectsJson()) {
            return view('devniox-ai::admin.page', [
                'section' => 'settings',
                'settings' => $data['settings'],
                'channels' => $data['channels'],
                'channelGuides' => $this->channelGuides($request, $scope),
                'ai' => $data['ai'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());

        if ($request->hasAny(['model', 'api_key', 'use_ai_fallback', 'local_first', 'system_instructions', 'clear_api_key'])) {
            $validated = $request->validate([
                'api_key' => ['nullable', 'string', 'max:4096'],
                'clear_api_key' => ['sometimes', 'boolean'],
                'model' => ['required', 'string', 'max:120'],
                'use_ai_fallback' => ['required', 'boolean'],
                'local_first' => ['required', 'boolean'],
                'system_instructions' => ['nullable', 'string', 'max:8000'],
            ]);

            $this->settings->saveAiConfiguration($scope, $validated);
            $ai = $this->settings->aiConfiguration($scope);
            unset($ai['api_key']);

            return response()->json(['success' => true, 'data' => ['ai' => $ai]]);
        }

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'value' => ['nullable'],
        ]);

        $setting = $this->settings->putSetting($scope, $validated['key'], $validated['value'] ?? null);

        return response()->json(['success' => true, 'data' => $setting]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:120'],
        ]);

        return response()->json([
            'success' => true,
            'data' => ['deleted' => $this->settings->deleteSetting($scope, $validated['key'])],
        ]);
    }

    public function channel(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $validated = $request->validate([
            'channel' => ['required', Rule::in(['whatsapp', 'messenger'])],
            'connection_name' => ['nullable', 'string', 'max:120'],
            'credentials' => ['required', 'array'],
            'credentials.token' => ['exclude_unless:channel,whatsapp', 'required', 'string', 'max:4096'],
            'credentials.phone_id' => ['exclude_unless:channel,whatsapp', 'required', 'string', 'max:120'],
            'credentials.webhook_secret' => ['exclude_unless:channel,whatsapp', 'nullable', 'string', 'max:4096'],
            'credentials.verify_token' => ['required', 'string', 'max:255'],
            'credentials.page_access_token' => ['exclude_unless:channel,messenger', 'required', 'string', 'max:4096'],
            'credentials.app_secret' => ['exclude_unless:channel,messenger', 'nullable', 'string', 'max:4096'],
            'credentials.api_url' => ['nullable', 'url', 'max:255'],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        $connection = $this->settings->putChannel(
            $scope,
            $validated['channel'],
            $validated['credentials'],
            $validated['connection_name'] ?? null,
            (bool) ($validated['enabled'] ?? true)
        );

        return response()->json([
            'success' => true,
            'data' => [
                'channel' => $connection->channel,
                'connection_name' => $connection->connection_name,
                'enabled' => $connection->enabled,
                'configured' => true,
                'guide' => $this->channelGuides($request, $scope)[$connection->channel],
            ],
        ]);
    }

    public function testChannel(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $validated = $request->validate([
            'channel' => ['required', Rule::in(['whatsapp', 'messenger'])],
            'test_to' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $channel = $validated['channel'];
        $credentials = $this->settings->credentials($scope, $channel);

        if ($credentials === []) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($channel).' is not connected yet.',
                'data' => ['connected' => false],
            ], 422);
        }

        $health = $this->channelHealth($channel, $credentials);
        $sent = null;

        if (! empty($validated['test_to'])) {
            $adapter = $channel === 'whatsapp'
                ? new WhatsAppChannel($credentials)
                : new FacebookMessengerChannel($credentials);

            $sent = $adapter->sendMessage([
                'to' => $validated['test_to'],
                'message' => $validated['message'] ?? 'Devniox AI Automation test message.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'channel' => $channel,
                'health' => $health,
                'sent' => $sent,
                'guide' => $this->channelGuides($request, $scope)[$channel],
            ],
        ]);
    }

    public function destroyChannel(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $validated = $request->validate([
            'channel' => ['required', Rule::in(['whatsapp', 'messenger'])],
        ]);

        return response()->json([
            'success' => true,
            'data' => ['deleted' => $this->settings->deleteChannel($scope, $validated['channel'])],
        ]);
    }

    protected function channelGuides(Request $request, array $scope): array
    {
        $query = array_filter([
            'business_key' => $scope['business_key'] ?? null,
            'owner_type' => $scope['owner_type'] ?? null,
            'owner_id' => $scope['owner_id'] ?? null,
            'tenant_id' => $scope['tenant_id'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'whatsapp' => [
                'callback_url' => route('devniox-ai.webhooks.whatsapp', $query),
                'verify_token_field' => 'credentials.verify_token',
                'required_credentials' => ['token', 'phone_id', 'verify_token'],
                'recommended_credentials' => ['webhook_secret'],
                'meta_product' => 'WhatsApp Business Cloud API',
            ],
            'messenger' => [
                'callback_url' => route('devniox-ai.webhooks.messenger', $query),
                'verify_token_field' => 'credentials.verify_token',
                'required_credentials' => ['page_access_token', 'verify_token'],
                'recommended_credentials' => ['app_secret'],
                'meta_product' => 'Facebook Messenger',
            ],
        ];
    }

    protected function channelHealth(string $channel, array $credentials): array
    {
        if ($channel === 'whatsapp') {
            $response = Http::withToken((string) ($credentials['token'] ?? ''))->get(
                rtrim((string) ($credentials['api_url'] ?? config('devniox-ai.whatsapp.api_url')), '/').'/'.($credentials['phone_id'] ?? ''),
                ['fields' => 'id,display_phone_number,verified_name']
            );

            return [
                'connected' => $response->successful(),
                'status' => $response->successful() ? 'verified' : 'failed',
                'provider' => $response->successful() ? $response->json() : ['error' => $response->body()],
            ];
        }

        $response = Http::get(
            rtrim((string) ($credentials['api_url'] ?? config('devniox-ai.messenger.api_url')), '/').'/me',
            [
                'fields' => 'id,name',
                'access_token' => $credentials['page_access_token'] ?? '',
            ]
        );

        return [
            'connected' => $response->successful(),
            'status' => $response->successful() ? 'verified' : 'failed',
            'provider' => $response->successful() ? $response->json() : ['error' => $response->body()],
        ];
    }
}
