<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bagian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_bagian',
        'kode_bagian',
    ];

    public function subBagians(): HasMany
    {
        return $this->hasMany(SubBagian::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}