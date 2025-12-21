<?php

namespace App\Traits;

use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log a custom activity.
     */
    protected function logActivity(string $action, string $description, array $metadata = []): void
    {
        UserActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'metadata' => array_merge([
                'model_id' => $this->id ?? null,
                'model_class' => get_class($this),
            ], $metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Automatically log standard Eloquent events.
     */
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity(
                strtolower(class_basename($model)) . '.created',
                class_basename($model) . " '{$model->name}' was created"
            );
        });

        static::updated(function ($model) {
            $model->logActivity(
                strtolower(class_basename($model)) . '.updated',
                class_basename($model) . " '{$model->name}' was updated"
            );
        });

        static::deleted(function ($model) {
            $model->logActivity(
                strtolower(class_basename($model)) . '.deleted',
                class_basename($model) . " '{$model->name}' was deleted"
            );
        });
    }
}
