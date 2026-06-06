<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

trait LogsAudit
{
    public static function bootLogsAudit(): void
    {
        static::created(function ($model) {
            AuditLog::record('created', $model, [], static::auditAttributes($model), class_basename($model) . ' created');
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if (empty($changes)) {
                return;
            }

            $oldValues = [];
            foreach (array_keys($changes) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
            }

            AuditLog::record('updated', $model, static::sanitize($oldValues), static::sanitize($changes), class_basename($model) . ' updated');
        });

        static::deleted(function ($model) {
            AuditLog::record('deleted', $model, static::auditAttributes($model), [], class_basename($model) . ' deleted');
        });
    }

    protected static function auditAttributes($model): array
    {
        return static::sanitize($model->getAttributes());
    }

    protected static function sanitize(array $attributes): array
    {
        unset($attributes['password'], $attributes['remember_token']);

        return $attributes;
    }
}
