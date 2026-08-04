<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification
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
            'title' => 'Surat Ditolak',
            'message' => 'Permohonan surat "' . ($this->submission->template->name ?? 'Surat') . '" ditolak. Alasan: ' . ($this->submission->rejection_reason ?? '-'),
            'url' => route('surat.show', $this->submission->id),
            'icon' => 'bi bi-x-circle text-danger'
        ];
    }
}