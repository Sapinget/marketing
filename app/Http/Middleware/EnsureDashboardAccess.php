<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            return $next($request);
        }

        $expectedSecret = trim((string) config('services.gas_proxy.secret', ''));
        $providedSecret = trim((string) $request->header('X-GAS-PROXY-SECRET', ''));

        if ($expectedSecret !== '' && hash_equals($expectedSecret, $providedSecret)) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
