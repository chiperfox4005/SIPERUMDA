<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate QR Code yang mengarah ke halaman tracking surat
     */
    public function generate(DocumentSubmission $submission): string
    {
        // URL publik untuk tracking (sesuaikan dengan route tracking Anda nanti)
        $trackingUrl = route('surat.track', ['id' => $submission->id]);
        
        // Generate QR Code sebagai SVG atau PNG (PNG lebih kompatibel dengan DomPDF)
        $qrCodeImage = QrCode::format('png')->size(150)->generate($trackingUrl);
        
        // Simpan ke storage
        $filename = "qr_{$submission->id}.png";
        $path = "qrcodes/{$filename}";
        
        Storage::disk('public')->put($path, $qrCodeImage);
        
        return $path;
    }
}