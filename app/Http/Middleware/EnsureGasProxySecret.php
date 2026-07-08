<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGasProxySecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedSecret = (string) config('services.gas_proxy.secret', '');

        if ($expectedSecret === '' || app()->environment('local')) {
            return $next($request);
        }

        $providedSecret = (string) $request->header('X-GAS-PROXY-SECRET', '');

        if (hash_equals($expectedSecret, $providedSecret)) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        abort(403);
    }
}
