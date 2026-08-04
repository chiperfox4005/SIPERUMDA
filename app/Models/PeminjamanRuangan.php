<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeminjamanRuangan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peminjaman_ruangans';

    protected $fillable = [
        'ruangan_id',
        'agenda_id',
        'user_id',
        'tanggal_pemakaian',
        'waktu_mulai',
        'waktu_selesai',
        'keperluan',
        'jumlah_peserta',
        'lampiran',               // Penting: Agar file bisa disimpan
        'status_persetujuan',
        'status_peminjaman',
        'disetujui_oleh',
        'tanggal_disetujui',
        'catatan_penolakan',
        'catatan_pembatalan',     // Penting: Untuk fitur pembatalan oleh sekretariat
        'ditolak_oleh',
        'tanggal_ditolak',
    ];

    protected $casts = [
        'tanggal_pemakaian' => 'date',
        'tanggal_disetujui' => 'datetime',
        'tanggal_ditolak' => 'datetime',
    ];

    // ===========================
    // RELASI (RELATIONSHIPS)
    // ===========================

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    // user_id berisi NIP, jadi hubungkan ke kolom 'nip' di tabel users
    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'nip');
    }

    // disetujui_oleh juga berisi NIP
    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'nip');
    }

    // ALIAS: Agar view show.blade.php yang memanggil ->approver tidak error
    public function approver(): BelongsTo
    {
        return $this->disetujuiOleh();
    }

    public function ditolakOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditolak_oleh', 'nip');
    }

    // ===========================
    // HELPER METHODS
    // ===========================

    public function isDisetujui(): bool
    {
        return $this->status_persetujuan === 'disetujui';
    }

    public function isDitolak(): bool
    {
        return $this->status_persetujuan === 'ditolak';
    }

    public function isMenunggu(): bool
    {
        return $this->status_persetujuan === 'menunggu';
    }

    public function isDibatalkan(): bool
    {
        return $this->status_persetujuan === 'dibatalkan';
    }

    // ===========================
    // SCOPES (QUERY BUILDER)
    // ===========================

    public function scopeMenunggu($query)
    {
        return $query->where('status_persetujuan', 'menunggu');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status_persetujuan', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status_persetujuan', 'ditolak');
    }

    public function scopeDibatalkan($query)
    {
        return $query->where('status_persetujuan', 'dibatalkan');
    }

    // ===========================
    // KONFLIK JADWAL
    // ===========================

    /**
     * Mengecek apakah ada konflik jadwal dengan peminjaman lain
     */
    public function konflikDengan($ruanganId, $tanggal, $waktuMulai, $waktuSelesai, $excludeId = null)
    {
        $query = PeminjamanRuangan::where('ruangan_id', $ruanganId)
            ->where('tanggal_pemakaian', $tanggal)
            ->whereIn('status_persetujuan', ['disetujui', 'menunggu']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Logika tumpang tindih waktu: (Mulai_A <= Selesai_B) DAN (Selesai_A >= Mulai_B)
        return $query->where(function ($q) use ($waktuMulai, $waktuSelesai) {
            $q->where('waktu_mulai', '<', $waktuSelesai)
              ->where('waktu_selesai', '>', $waktuMulai);
        })->exists();
    }
}