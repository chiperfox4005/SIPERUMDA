<?php

namespace App\Services;

use App\Models\DocumentSubmission;
use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    /**
     * Generate nomor surat unik berdasarkan format: NOMOR/BULAN/TAHUN
     * Contoh: 001/SIPERUMDA/VII/2026
     */
    public function generateNomorSurat(): string
    {
        $tahun = date('Y');
        $bulanRomawi = $this->getRomawiMonth(date('m'));
        
        // Cari nomor urut terakhir di bulan dan tahun ini
        $lastSubmission = DocumentSubmission::whereYear('created_at', $tahun)
            ->whereMonth('created_at', date('m'))
            ->whereNotNull('nomor_surat')
            ->orderBy('nomor_surat', 'desc')
            ->first();

        $lastNumber = 1;
        if ($lastSubmission) {
            // Ekstrak angka dari format "001/SIPERUMDA/VII/2026"
            preg_match('/^(\d+)\//', $lastSubmission->nomor_surat, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }

        $prefix = str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
        return "{$prefix}/SIPERUMDA/{$bulanRomawi}/{$tahun}";
    }

    private function getRomawiMonth($month): string
    {
        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romawi[(int)$month - 1];
    }
}