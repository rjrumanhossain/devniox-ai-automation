<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function whatsapp(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'received',
            'channel' => 'whatsapp',
            'idempotent' => true,
        ]);
    }

    public function messenger(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'received',
            'channel' => 'messenger',
            'idempotent' => true,
        ]);
    }

    public function website(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'received',
            'channel' => 'website',
            'idempotent' => true,
        ]);
    }
}
