<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditsActivity
{
    public static function bootAuditsActivity(): void
    {
        static::created(function (self $model) {
            $model->logActivity('created', null, $model->getAttributes());
        });

        static::updated(function (self $model) {
            $model->logActivity('updated', $model->getOriginal(), $model->getAttributes());
        });

        static::deleted(function (self $model) {
            $model->logActivity('deleted', $model->getAttributes(), null);
        });
    }

    protected function logActivity(string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $this->getAuditModuleName(),
            'record_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    protected function getAuditModuleName(): string
    {
        return str_replace('_', '-', str()->snake(class_basename($this)));
    }
}
