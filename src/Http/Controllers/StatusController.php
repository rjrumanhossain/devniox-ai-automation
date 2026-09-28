<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class StatusController extends Controller
{
    public function index(): JsonResponse
    {
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
                'whatsapp' => ! empty(config('devniox-ai.whatsapp.token')) && ! empty(config('devniox-ai.whatsapp.phone_id')),
                'messenger' => ! empty(config('devniox-ai.messenger.page_access_token')),
            ],
        ]);
    }
}
