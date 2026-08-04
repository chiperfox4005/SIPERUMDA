<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuratNotification extends Notification
{
    use Queueable;

    protected $surat;
    protected $status;
    protected $pesan;

    public function __construct($surat, $status, $pesan)
    {
        $this->surat = $surat;
        $this->status = $status;
        $this->pesan = $pesan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'surat',
            'title' => 'Surat ' . ucfirst($this->status),
            'message' => $this->pesan,
            'url' => route('surat.show', $this->surat->id),
            'icon' => 'bi bi-file-earmark-text',
        ];
    }
}