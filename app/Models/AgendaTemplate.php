<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'icon', 'description',
        'form_schema', 'pdf_layout',
        'requires_room', 'requires_letter',
        'letter_template', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'form_schema' => 'array',
        'pdf_layout' => 'array',
        'requires_room' => 'boolean',
        'requires_letter' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function agendas()
    {
        return $this->hasMany(Agenda::class);
    }

    public static function getActiveTemplates()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}