<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\SaasDashboardController;
use App\Models\AiProviderCredential;
use App\Models\Business;
use App\Models\ChannelConnection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PortalAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid portal credentials.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($request),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user() ? $this->userPayload($request) : null,
        ]);
    }

    public function overview(Request $request, SaasDashboardController $dashboard): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        $payload = $user->isSuperAdmin()
            ? $dashboard->superAdminOverview()->getData(true)
            : $dashboard->customerOverview()->getData(true);

        if ($user->isSuperAdmin()) {
            $payload['metrics'][0]['value'] = (string) User::where('role', 'customer')->count();
            $payload['clients'] = Business::query()
                ->with(['plan', 'channels', 'aiProviders'])
                ->whereHas('owner', fn ($query) => $query->where('role', 'customer'))
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (Business $business): array => [
                    'name' => $business->name,
                    'plan' => $business->plan?->name ?? 'No plan',
                    'channels' => $business->channels->pluck('type')->map(fn (string $type): string => str($type)->headline()->toString())->all(),
                    'provider' => str($business->aiProviders->first()?->provider ?? 'devniox_managed')->headline()->toString(),
                    'status' => str($business->status)->headline()->toString(),
                    'usage' => 62,
                ])
                ->values()
                ->all();
        } else {
            $business = $user->businesses()->with(['plan', 'apiKeys', 'channels', 'aiProviders'])->first();

            if ($business) {
                $payload['business'] = [
                    'name' => $business->name,
                    'plan' => $business->plan?->name ?? 'No plan',
                    'status' => str($business->status)->headline()->toString(),
                    'api_key_prefix' => $business->apiKeys->first()?->key_prefix ?? 'dnx_live',
                ];
                $payload['channels'] = $business->channels
                    ->map(fn (ChannelConnection $channel): array => [
                        'name' => str($channel->type)->headline()->replace('Website Chat', 'Website Chat')->toString(),
                        'connected' => $channel->status === 'connected' ? 1 : 0,
                        'live' => $channel->status === 'connected' ? 1 : 0,
                        'status' => str($channel->status)->headline()->toString(),
                        'target' => $channel->external_id ?? $channel->display_name ?? 'Not connected',
                    ])
                    ->values()
                    ->all();
                $payload['providers'] = $business->aiProviders
                    ->map(fn (AiProviderCredential $provider): array => [
                        'name' => str($provider->provider)->replace('_', ' ')->headline()->toString(),
                        'mode' => $provider->mode,
                        'status' => str($provider->status)->headline()->toString(),
                    ])
                    ->values()
                    ->all();
            }
        }

        return response()->json($payload);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }

    private function userPayload(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];
    }
}
