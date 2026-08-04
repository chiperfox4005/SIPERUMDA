<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'nip',
        'nama_lengkap',
        'password',
        'bagian_id',
        'sub_bagian_id',
        'jabatan_id',
        'foto_profil',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function bagian(): BelongsTo { return $this->belongsTo(Bagian::class); }
    public function subBagian(): BelongsTo { return $this->belongsTo(SubBagian::class); }
    public function jabatan(): BelongsTo { return $this->belongsTo(Jabatan::class); }

    public function getAuthIdentifierName()
    {
        return 'nip'; // Memberitahu Laravel bahwa 'nip' adalah kolom untuk login
    }
}