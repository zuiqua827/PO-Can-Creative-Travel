<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Keys that must never be stored in audit logs.
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'server_key',
        'client_key',
        'webhook_secret',
        'secret',
        'token',
        'authorization',
        'cvv',
        'card_number',
    ];

    /**
     * Record an audit log event.
     */
    public static function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        array $metadata = []
    ): ?AuditLog {
        try {
            $user = Auth::user();
            $actorName = $user ? "{$user->name} ({$user->role})" : 'System / Guest';
            $sanitizedMetadata = self::sanitize($metadata);

            $entry = AuditLog::create([
                'user_id' => $user?->id,
                'actor_name' => $actorName,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'metadata' => empty($sanitizedMetadata) ? null : $sanitizedMetadata,
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent() ? substr(request()->userAgent(), 0, 255) : null,
                'created_at' => now(),
            ]);

            Log::channel('security')->info("Audit: [{$action}] by {$actorName}", [
                'target_type' => $targetType,
                'target_id' => $targetId,
                'ip' => request()?->ip(),
            ]);

            return $entry;
        } catch (\Throwable $e) {
            Log::channel('security')->error("Failed to write audit log [{$action}]: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Recursively strip sensitive keys from metadata.
     */
    protected static function sanitize(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
                $clean[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $clean[$key] = self::sanitize($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
