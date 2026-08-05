<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeminjamanRuanganNotification extends Notification
{
    use Queueable;

    protected $peminjaman;
    protected $status;
    protected $actorName;
    protected $type;
    protected $reason;

    public function __construct($peminjaman, $status, $actorName, $type, $reason = null)
    {
        $this->peminjaman = $peminjaman;
        $this->status = $status;
        $this->actorName = $actorName;
        $this->type = $type; // 'pengajuan', 'persetujuan', 'penolakan'
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $title = 'Peminjaman Ruangan';
        $message = '';
        $icon = 'bi bi-calendar-check';
        $color = 'primary';

        if ($this->type === 'pengajuan') {
            $title = 'Pengajuan Peminjaman Baru';
            $message = "<strong>{$this->actorName}</strong> mengajukan peminjaman <strong>{$this->peminjaman->ruangan->nama_ruangan}</strong> pada " . \Carbon\Carbon::parse($this->peminjaman->tanggal_pemakaian)->locale('id')->isoFormat('D MMMM Y');
            $icon = 'bi bi-calendar-plus';
            $color = 'warning';
        } elseif ($this->type === 'persetujuan') {
            $title = 'Peminjaman Disetujui';
            $message = "Peminjaman <strong>{$this->peminjaman->ruangan->nama_ruangan}</strong> Anda telah disetujui oleh <strong>{$this->actorName}</strong>.";
            $icon = 'bi bi-check-circle-fill';
            $color = 'success';
        } elseif ($this->type === 'penolakan') {
            $title = 'Peminjaman Ditolak';
            $message = "Peminjaman <strong>{$this->peminjaman->ruangan->nama_ruangan}</strong> Anda ditolak oleh <strong>{$this->actorName}</strong>. Alasan: {$this->reason}";
            $icon = 'bi bi-x-circle-fill';
            $color = 'danger';
        }

        return [
            'type' => 'peminjaman_ruangan',
            'title' => $title,
            'message' => $message,
            'url' => route('peminjaman-ruangan.show', $this->peminjaman->id),
            'icon' => $icon,
            'color' => $color
        ];
    }
}