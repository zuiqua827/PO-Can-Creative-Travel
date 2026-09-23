<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    /**
     * Standardized, non-disclosing health check probe for load balancers and monitors.
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => 'ok',
            'cache' => 'ok',
            'storage' => 'ok',
        ];

        $healthy = true;

        // 1. Database Connectivity Check
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $checks['database'] = 'error';
            $healthy = false;
        }

        // 2. Cache Connectivity Check
        try {
            $testKey = '__health_ping_'.time();
            Cache::put($testKey, 'ok', 5);
            if (Cache::get($testKey) !== 'ok') {
                $checks['cache'] = 'error';
                $healthy = false;
            }
        } catch (\Throwable $e) {
            $checks['cache'] = 'error';
            $healthy = false;
        }

        // 3. Storage Accessibility Check
        try {
            if (! Storage::disk('public')->exists('.')) {
                $checks['storage'] = 'warning';
            }
        } catch (\Throwable $e) {
            $checks['storage'] = 'error';
            $healthy = false;
        }

        $statusCode = $healthy ? 200 : 503;

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'app' => 'CAN Travel',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $statusCode);
    }
}
