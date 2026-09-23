<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (! auth()->user()->isAdmin()) {
            Log::warning('Unauthorized admin portal access attempt denied', [
                'user_id' => auth()->id(),
                'email' => auth()->user()->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator CAN Travel.');
        }

        return $next($request);
    }
}
