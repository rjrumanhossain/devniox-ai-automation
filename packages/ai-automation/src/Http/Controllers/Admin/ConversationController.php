<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers\Admin;

use Devniox\AiAutomation\Models\Conversation;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Conversation::query()->withCount('messages');
        TenantResolver::applyScope($query, $scope);
        $items = $query->latest('id')->paginate((int) $request->integer('per_page', 25))->withQueryString();

        if (! $request->expectsJson()) {
            return view('devniox-ai::admin.page', ['section' => 'conversations', 'items' => $items]);
        }

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function show(Request $request, string $conversation): JsonResponse|View
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Conversation::query()->where('uuid', $conversation)->with('messages');
        TenantResolver::applyScope($query, $scope);

        $record = $query->firstOrFail();

        if (! $request->expectsJson()) {
            return view('devniox-ai::admin.page', ['section' => 'conversation', 'conversation' => $record]);
        }

        return response()->json([
            'success' => true,
            'data' => $record,
        ]);
    }

    public function close(Request $request, string $conversation): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Conversation::query()->where('uuid', $conversation);
        TenantResolver::applyScope($query, $scope);

        $record = $query->firstOrFail();
        $record->update(['status' => 'closed']);

        return response()->json(['success' => true, 'data' => $record->refresh()]);
    }
}
