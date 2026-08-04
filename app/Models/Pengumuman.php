<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pengumuman extends Model
{
    protected $table = 'pengumumans';

    // PENTING: Gunakan 'dibuat_oleh' sesuai dengan nama kolom di database Anda
    protected $fillable = [
        'judul',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_audience',
        'target_ids',
        'isi',
        'lampiran',
        'prioritas',
        'status',
        'dibuat_oleh',        // <-- Ganti dari 'created_by' menjadi 'dibuat_oleh'
        'tanggal_publish',
        'tanggal_berakhir',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_publish' => 'date',
        'tanggal_berakhir' => 'date',
        'target_ids' => 'array',
    ];

    public function creator(): BelongsTo
    {
        // Relasi ke tabel users berdasarkan kolom 'dibuat_oleh' dan 'nip'
        return $this->belongsTo(User::class, 'dibuat_oleh', 'nip');
    }

    /**
     * Cek apakah pengumuman sudah melewati tanggal berakhir
     * Method ini dibutuhkan oleh view pengumuman/index.blade.php
     */
    public function isExpired(): bool
    {
        if (is_null($this->tanggal_berakhir)) {
            return false; // Jika tidak ada tanggal berakhir, dianggap tidak expired
        }
        return Carbon::parse($this->tanggal_berakhir)->isPast();
    }

    /**
     * Scope untuk mendapatkan pengumuman yang aktif (belum expired)
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'publish')
            ->where('tanggal_publish', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_berakhir')
                  ->orWhere('tanggal_berakhir', '>=', now());
            });
    }

    /**
     * Cek apakah pengumuman masih aktif (belum expired)
     */
    public function isActive(): bool
    {
        if ($this->status !== 'publish') {
            return false;
        }
        if (Carbon::parse($this->tanggal_publish)->isFuture()) {
            return false;
        }
        if (!is_null($this->tanggal_berakhir) && Carbon::parse($this->tanggal_berakhir)->isPast()) {
            return false;
        }
        return true;
    }

    /**
     * Get badge warna berdasarkan prioritas
     */
    public function getPrioritasBadgeAttribute(): string
    {
        return match($this->prioritas) {
            'mendesak' => 'danger',
            'penting' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get badge warna berdasarkan status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'publish' => 'success',
            'draft' => 'secondary',
            default => 'info',
        };
    }
}