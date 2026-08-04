<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusPeminjamanRuanganNotification extends Notification
{
    use Queueable;

    public $peminjaman;
    public $status;
    public $pesan;

    public function __construct($peminjaman, $status, $pesan)
    {
        $this->peminjaman = $peminjaman;
        $this->status = $status;
        $this->pesan = $pesan;
    }

    public function via($notifiable)
    {
        // Simpan ke database notifikasi Laravel
        return ['database']; 
    }

    public function toDatabase($notifiable)
    {
        return [
            'peminjaman_id' => $this->peminjaman->id,
            'ruangan' => $this->peminjaman->ruangan->nama_ruangan ?? 'Ruangan',
            'tanggal' => $this->peminjaman->tanggal_pemakaian,
            'status' => $this->status,
            'pesan' => $this->pesan,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    // Opsional: Jika Anda ingin notifikasi juga dikirim via Email
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Status Peminjaman Ruangan: ' . ucfirst(str_replace('_', ' ', $this->status)))
            ->line($this->pesan)
            ->line('Ruangan: ' . ($this->peminjaman->ruangan->nama_ruangan ?? '-'))
            ->line('Tanggal: ' . $this->peminjaman->tanggal_pemakaian)
            ->action('Lihat Detail', url('/peminjaman-ruangan/' . $this->peminjaman->id));
    }
}