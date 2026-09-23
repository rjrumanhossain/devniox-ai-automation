<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ChatController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'queued',
            'channel' => 'website',
            'message' => $request->input('message', ''),
        ]);
    }

    public function show(Request $request, string $conversation): JsonResponse
    {
        return response()->json([
            'conversation' => $conversation,
            'messages' => [],
        ]);
    }
}
