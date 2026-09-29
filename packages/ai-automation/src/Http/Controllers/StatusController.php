<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Devniox\AiAutomation\Services\SettingsService;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StatusController extends Controller
{
    public function __construct(protected SettingsService $settings) {}

    public function index(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $whatsappCredentials = $this->settings->credentials($scope, 'whatsapp');
        $messengerCredentials = $this->settings->credentials($scope, 'messenger');

        return response()->json([
            'package' => 'devniox-ai-automation',
            'status' => 'ok',
            'version' => '0.1.0',
            'features' => [
                'website_chat' => (bool) config('devniox-ai.website.enabled', true),
                'ai_fallback' => (bool) config('devniox-ai.ai.use_ai_fallback', true),
                'local_first' => (bool) config('devniox-ai.answering.local_first', true),
                'handoff' => (bool) config('devniox-ai.handoff.enabled', true),
            ],
            'channels' => [
                'whatsapp' => (
                    ! empty($whatsappCredentials['token'])
                    && ! empty($whatsappCredentials['phone_id'])
                ) || (! empty(config('devniox-ai.whatsapp.token')) && ! empty(config('devniox-ai.whatsapp.phone_id'))),
                'messenger' => ! empty($messengerCredentials['page_access_token']) || ! empty(config('devniox-ai.messenger.page_access_token')),
            ],
        ]);
    }
}
