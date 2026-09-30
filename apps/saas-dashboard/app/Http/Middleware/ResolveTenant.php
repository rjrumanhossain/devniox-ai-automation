<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = Str::lower($request->getHost());
        $domain = Str::lower((string) config('tenancy.domain'));

        if ($domain !== '' && Str::endsWith($host, '.'.$domain)) {
            $username = Str::before($host, '.'.$domain);

            if ($username !== '' && ! in_array($username, config('tenancy.reserved_usernames', []), true)) {
                $tenant = Business::query()->where('slug', $username)->first();

                abort_unless($tenant, 404);

                app()->instance('currentTenant', $tenant);
            }
        }

        return $next($request);
    }
}
