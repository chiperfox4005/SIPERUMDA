<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_submissions';

    protected $fillable = [
        'template_id',
        'user_id',
        'nomor_surat',
        'data_json',
        'status',
        'approved_by',
        'approved_at',
        'pdf_path',
        'qr_code_path',
        'rejection_reason',
        'signatory_id', // ✅ PASTIKAN INI ADA
    ];

    protected $casts = [
        'data_json' => 'array', // ✅ Otomatis handle JSON encode/decode
        'approved_at' => 'datetime',
    ];

    // 1. Relasi ke User (Pembuat)
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'nip');
    }

    // 2. Relasi ke Template Surat
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    // 3. Relasi ke Pejabat Penandatangan (✅ INI YANG HILANG & MENYEBABKAN ERROR)
    public function signatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'signatory_id');
    }
}