<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Dashboard internal: jangan diindeks mesin pencari (host publik lewat tunnel/workers.dev) dan jangan umumkan versi PHP.
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        $contentType = (string) $response->headers->get('Content-Type', '');

        // Route yang menetapkan CSP sendiri (mis. halaman cetak ber-nonce) tidak ditimpa.
        if (str_starts_with(strtolower($contentType), 'text/html') && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set(
                'Content-Security-Policy',
                implode('; ', [
                    "default-src 'self'",
                    "base-uri 'self'",
                    "frame-ancestors 'none'",
                    "object-src 'none'",
                    "form-action 'self'",
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                    "style-src 'self' 'unsafe-inline'",
                    "font-src 'self' data:",
                    "img-src 'self' data: blob: https:",
                    "connect-src 'self'",
                ])
            );
        }

        return $response;
    }
}
