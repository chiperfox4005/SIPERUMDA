<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentApprovedNotification extends Notification
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
            'type' => 'surat', // Agar cocok dengan penghitung notifikasi di app.blade.php
            'title' => 'Surat Disetujui',
            'message' => 'Permohonan surat "' . ($this->submission->template->name ?? 'Surat') . '" dengan nomor ' . $this->submission->nomor_surat . ' telah disetujui. Silakan unduh PDF-nya.',
            'url' => route('surat.show', $this->submission->id),
            'icon' => 'bi bi-file-earmark-check text-success'
        ];
    }
}