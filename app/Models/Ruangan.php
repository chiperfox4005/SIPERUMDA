<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_ruangan',
        'lokasi',
        'kapasitas',
        'fasilitas',
        'foto',
        'status',
    ];

    protected $casts = [
        'fasilitas' => 'array',
    ];

    public function peminjamanRuangans(): HasMany
    {
        return $this->hasMany(PeminjamanRuangan::class);
    }

    public function isTersedia($tanggal, $waktuMulai, $waktuSelesai)
    {
        return !PeminjamanRuangan::where('ruangan_id', $this->id)
            ->where('tanggal_pemakaian', $tanggal)
            ->where('status_persetujuan', 'disetujui')
            ->where(function ($query) use ($waktuMulai, $waktuSelesai) {
                $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('waktu_mulai', '<=', $waktuMulai)
                      ->where('waktu_selesai', '>', $waktuMulai);
                })->orWhere(function ($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('waktu_mulai', '<', $waktuSelesai)
                      ->where('waktu_selesai', '>=', $waktuSelesai);
                })->orWhere(function ($q) use ($waktuMulai, $waktuSelesai) {
                    $q->where('waktu_mulai', '>=', $waktuMulai)
                      ->where('waktu_selesai', '<=', $waktuSelesai);
                });
            })->exists();
    }
}