<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AgendaNotification extends Notification
{
    use Queueable;

    protected $agenda;
    protected $status;
    protected $pesan;

    public function __construct($agenda, $status, $pesan)
    {
        $this->agenda = $agenda;
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
            'type' => 'agenda',
            'title' => 'Agenda ' . ucfirst($this->status),
            'message' => $this->pesan,
            'url' => route('agenda.show', $this->agenda->id),
            'icon' => 'bi bi-calendar-event',
        ];
    }
}