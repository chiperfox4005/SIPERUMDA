<?php

namespace App\Services;

use App\Models\DocumentSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGenerationService
{
    public function generate(DocumentSubmission $submission): string
    {
        $template = $submission->template;
        $data = $submission->data_json;
        
        // Render Blade Template dengan data dinamis
        $html = view($template->blade_view_path, [
            'submission' => $submission,
            'data' => $data,
            'nomorSurat' => $submission->nomor_surat,
            'qrCodeUrl' => $submission->qr_code_path ? Storage::url($submission->qr_code_path) : null,
        ])->render();

        // Konfigurasi DomPDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true); // Agar bisa load gambar TTD/QR dari storage

        // Simpan PDF ke storage
        $filename = "surat_{$submission->nomor_surat}.pdf";
        $path = "surats/{$submission->id}/{$filename}";
        
        Storage::disk('public')->put($path, $pdf->output());
        
        return $path;
    }
}