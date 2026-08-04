<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name', 'code', 'blade_view_path', 'form_schema', 'is_active'
    ];

    protected $casts = [
        'form_schema' => 'array',
        'is_active' => 'boolean',
    ];

    public function submissions()
    {
        return $this->hasMany(DocumentSubmission::class);
    }
}