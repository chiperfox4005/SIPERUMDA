<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubBagian extends Model
{
    use HasFactory;

    protected $fillable = [
        'bagian_id',
        'nama_sub_bagian',
    ];

    public function bagian(): BelongsTo
    {
        return $this->belongsTo(Bagian::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}