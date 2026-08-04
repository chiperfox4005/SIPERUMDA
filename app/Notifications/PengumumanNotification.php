<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengumumanNotification extends Notification
{
    use Queueable;

    protected $pengumuman;
    protected $pesan;

    public function __construct($pengumuman, $pesan)
    {
        $this->pengumuman = $pengumuman;
        $this->pesan = $pesan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'pengumuman',
            'title' => 'Pengumuman Baru: ' . ($this->pengumuman->judul ?? ''),
            'message' => $this->pesan,
            'url' => route('pengumuman.show', $this->pengumuman->id),
            'icon' => 'bi bi-megaphone',
        ];
    }
}