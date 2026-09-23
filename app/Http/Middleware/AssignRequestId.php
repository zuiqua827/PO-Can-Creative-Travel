<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    /**
     * Handle an incoming request and attach a correlation/request ID to logs and response headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?: (string) Str::uuid();

        // Bind correlation ID to current logging context
        Log::withContext(['request_id' => $requestId]);

        /** @var Response $response */
        $response = $next($request);

        // Expose correlation ID to client for end-to-end traceability
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
