<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    use HasFactory;

    protected $table = 'signatories';

    // ✅ PASTIKAN SEMUA KOLOM DARI FORM ADA DI SINI
    protected $fillable = [
        'name',
        'position',
        'nip',                // <-- WAJIB ADA (ini yang menyebabkan error)
        'signature_image',    // <-- WAJIB ADA (sesuai dengan name="signature_image" di form)
        'is_active',
        'valid_from',
        'valid_until',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    // Relasi ke pengajuan surat
    public function documentSubmissions()
    {
        return $this->hasMany(DocumentSubmission::class, 'signatory_id');
    }
}