<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers\Admin;

use Devniox\AiAutomation\Models\KnowledgeItem;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class KnowledgeController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = KnowledgeItem::query();
        TenantResolver::applyScope($query, $scope);
        $items = $query->latest('id')->paginate((int) $request->integer('per_page', 25))->withQueryString();

        if (! $request->expectsJson()) {
            return view('devniox-ai::admin.page', ['section' => 'knowledge', 'items' => $items]);
        }

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $item = KnowledgeItem::query()->create(array_merge($scope, $validated, [
            'is_active' => $validated['is_active'] ?? true,
        ]));

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function update(Request $request, int $knowledge): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = KnowledgeItem::query()->whereKey($knowledge);
        TenantResolver::applyScope($query, $scope);

        $item = $query->firstOrFail();
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $item->update($validated);

        return response()->json(['success' => true, 'data' => $item->refresh()]);
    }

    public function destroy(Request $request, int $knowledge): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = KnowledgeItem::query()->whereKey($knowledge);
        TenantResolver::applyScope($query, $scope);
        $query->firstOrFail()->delete();

        return response()->json(['success' => true]);
    }
}
