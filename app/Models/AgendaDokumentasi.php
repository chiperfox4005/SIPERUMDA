<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaDokumentasi extends Model
{
    protected $table = 'agenda_dokumentasis';

    protected $fillable = [
        'agenda_id',
        'risalah_rapat',
        'daftar_hadir',
        'foto_kegiatan',
        'lampiran_lainnya',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'nip');
    }
}