<?php

namespace App\Http\Middleware;

use App\Support\DashboardAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $dashboardAuth = app(DashboardAuth::class);

        if (! $dashboardAuth->ensureActiveSession($request)) {
            return response()->json($dashboardAuth->sessionFailurePayload(), 401);
        }

        return $next($request);
    }
}
