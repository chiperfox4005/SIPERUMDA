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
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'surat',
            'title' => 'Pengajuan Surat Baru',
            'message' => ($this->submission->creator->nama_lengkap ?? 'Pegawai') . ' mengajukan permohonan surat "' . ($this->submission->template->name ?? 'Surat') . '". Silakan segera verifikasi.',
            'url' => route('surat.approval'),
            'icon' => 'bi bi-file-earmark-plus text-primary'
        ];
    }
}