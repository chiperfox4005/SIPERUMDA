<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'action', 'model_type', 'model_id', 
        'old_data', 'new_data', 'ip_address'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        // Asumsi relasi ke User berdasarkan NIP atau ID
        return $this->belongsTo(User::class, 'user_id', 'nip')->orWhere('user_id', 'id');
    }
}