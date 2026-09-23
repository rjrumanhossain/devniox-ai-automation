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
        ]);
    }
}
