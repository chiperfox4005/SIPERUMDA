<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Surat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_surat',
        'jenis_surat',
        'tanggal_surat',
        'perihal',
        'isi_surat',
        'tujuan',
        'penerima_nama',
        'penerima_nip',
        'penerima_jabatan',
        'status',
        'dibuat_oleh',
        'disetujui_oleh',
        'tanggal_disetujui',
        'catatan_penolakan',
        'file_path',
        'penandatangan_id',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_disetujui' => 'datetime',
    ];

    // Relasi ke User (pembuat)
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'nip');
    }

    // Relasi ke User (yang approve)
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'nip');
    }

    // Relasi ke Signatory (pejabat penandatangan)
    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'penandatangan_id');
    }

    // Helper untuk label jenis surat
    public function getJenisLabelAttribute(): string
    {
        return match($this->jenis_surat) {
            'tugas' => 'Surat Tugas',
            'dinas' => 'Surat Perintah Dinas',
            'izin' => 'Surat Izin',
            'undangan' => 'Surat Undangan',
            'sk' => 'Surat Keputusan',
            default => ucfirst($this->jenis_surat),
        };
    }

    // Helper untuk badge status
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'draft' => ['class' => 'bg-secondary', 'label' => 'Draft'],
            'submitted' => ['class' => 'bg-warning text-dark', 'label' => 'Menunggu Persetujuan'],
            'approved' => ['class' => 'bg-success', 'label' => 'Disetujui'],
            'rejected' => ['class' => 'bg-danger', 'label' => 'Ditolak'],
            default => ['class' => 'bg-secondary', 'label' => ucfirst($this->status)],
        };
    }
}