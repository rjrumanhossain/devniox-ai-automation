<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers\Admin;

use Devniox\AiAutomation\Services\SettingsService;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
}
