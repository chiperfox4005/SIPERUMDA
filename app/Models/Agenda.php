<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agenda extends Model
{
    use SoftDeletes;

    protected $table = 'agendas';

    protected $fillable = [
        'template_id',
        'nomor_surat',
        'judul',
        'hari',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_mulai',
        'jam_selesai',
        'tempat',
        'acara',
        'pimpinan_rapat',
        'peserta',
        'inisiator',
        'notulen',
        'catatan',
        'lampiran',
        'membutuhkan_ruangan',
        'ruangan_id',
        'created_by',
        'status',
        'peminjaman_ruangan_id',
        'pdf_path',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'peserta' => 'array',
        'catatan' => 'array',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'approved_at' => 'datetime',
        'membutuhkan_ruangan' => 'boolean',
        // PERBAIKAN: Paksa menjadi string agar cocok dengan tipe data NIP di tabel users
        'created_by' => 'string',
        'approved_by' => 'string',
    ];

    // --- RELASI ---

    public function template(): BelongsTo
    {
        return $this->belongsTo(AgendaTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        // Relasi ke tabel users berdasarkan NIP (created_by)
        return $this->belongsTo(User::class, 'created_by', 'nip'); 
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function peminjamanRuangan(): HasOne
    {
        return $this->hasOne(PeminjamanRuangan::class, 'agenda_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'nip');
    }
}