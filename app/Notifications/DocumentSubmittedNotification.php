<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentSubmittedNotification extends Notification
{
    use Queueable;

    protected $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function via($notifiable)
    {
        // Kirim ke database notifikasi Laravel
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'surat', // Agar cocok dengan penghitung notifikasi di app.blade.php ($countSurat)
            'title' => 'Pengajuan Surat Baru',
            'message' => ($this->submission->creator->nama_lengkap ?? 'Pegawai') . ' mengajukan permohonan surat "' . ($this->submission->template->name ?? 'Surat') . '". Silakan segera verifikasi.',
            'url' => route('surat.approval'), // Langsung arahkan ke halaman verifikasi
            'icon' => 'bi bi-file-earmark-plus text-primary'
        ];
    }
}