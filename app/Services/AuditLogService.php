<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Record an audit log entry for sensitive operations
     */
    public static function log(string $event, Model $subject, ?string $description = null, array $properties = []): ActivityLog
    {
        $user = auth()->user();
        $safeProperties = self::sanitize($properties);

        // Include tenant_id for tenant filtering
        $tenantId = $user?->tenant_id ?? $subject->tenant_id ?? null;
        if ($tenantId) {
            $safeProperties['tenant_id'] = $tenantId;
        }

        return ActivityLog::create([
            'log_name' => $subject->getTable(),
            'description' => $description ?? "{$event} on " . class_basename($subject),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'causer_type' => $user ? get_class($user) : null,
            'causer_id' => $user?->getKey(),
            'properties' => $safeProperties,
            'event' => $event,
        ]);
    }

    /**
     * Remove sensitive information from audit log properties
     */
    protected static function sanitize(array $data): array
    {
        $sensitiveKeys = [
            'password',
            'remember_token',
            'token',
            'secret',
            'api_key',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitize($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '********';
            }
        }

        return $data;
    }
}
