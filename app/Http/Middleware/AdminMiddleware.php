<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator PO CAN Travel.');
        }

        return $next($request);
    }
}
