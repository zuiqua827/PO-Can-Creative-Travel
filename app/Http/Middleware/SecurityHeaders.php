<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach production HTTP security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking via iframes
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Control referrer information leakage
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict sensitive browser permissions
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Robust, Vite/Tailwind-compatible Content Security Policy
        $csp = "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; "
            ."style-src 'self' 'unsafe-inline' https:; "
            ."font-src 'self' data: https:; "
            ."img-src 'self' data: https: blob:; "
            ."connect-src 'self' ws: wss: https:; "
            ."frame-ancestors 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
