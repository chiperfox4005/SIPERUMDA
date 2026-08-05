<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::logActivity($model, 'created');
        });

        static::updated(function ($model) {
            if ($model->isDirty()) { // Hanya catat jika ada perubahan
                self::logActivity($model, 'updated', $model->getDirty(), $model->getOriginal());
            }
        });

        static::deleted(function ($model) {
            self::logActivity($model, 'deleted', $model->toArray());
        });
    }

    protected static function logActivity($model, $action, $newData = null, $oldData = null)
    {
        $user = Auth::user();
        
        AuditLog::create([
            'user_id' => $user ? ($user->nip ?? $user->id) : 'system',
            'user_name' => $user ? ($user->nama_lengkap ?? $user->name) : 'System',
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => request()->ip(),
        ]);
    }
}