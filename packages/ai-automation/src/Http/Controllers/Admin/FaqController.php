<?php

declare(strict_types=1);

namespace Devniox\AiAutomation\Http\Controllers\Admin;

use Devniox\AiAutomation\Models\Faq;
use Devniox\AiAutomation\Support\TenantResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Faq::query();
        TenantResolver::applyScope($query, $scope);
        $items = $query->latest('id')->paginate((int) $request->integer('per_page', 25))->withQueryString();

        if (! $request->expectsJson()) {
            return view('devniox-ai::admin.page', ['section' => 'faqs', 'items' => $items]);
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
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $faq = Faq::query()->create(array_merge($scope, $validated, [
            'is_active' => $validated['is_active'] ?? true,
        ]));

        return response()->json(['success' => true, 'data' => $faq], 201);
    }

    public function update(Request $request, int $faq): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Faq::query()->whereKey($faq);
        TenantResolver::applyScope($query, $scope);

        $record = $query->firstOrFail();
        $validated = $request->validate([
            'question' => ['sometimes', 'string', 'max:255'],
            'answer' => ['sometimes', 'string'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $record->update($validated);

        return response()->json(['success' => true, 'data' => $record->refresh()]);
    }

    public function destroy(Request $request, int $faq): JsonResponse
    {
        $scope = TenantResolver::resolve($request, $request->all());
        $query = Faq::query()->whereKey($faq);
        TenantResolver::applyScope($query, $scope);
        $query->firstOrFail()->delete();

        return response()->json(['success' => true]);
    }
}
