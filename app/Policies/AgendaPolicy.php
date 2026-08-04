<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool { return true; }
    
    public function view(User $user, Agenda $agenda): bool 
    {
        return $user->hasRole(['IT Administrator', 'Sekretariat', 'Kepegawaian']) 
            || $agenda->created_by === $user->id 
            || $agenda->peserta()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool { return true; } // Pegawai & Sekretariat

    public function update(User $user, Agenda $agenda): bool 
    {
        return $user->hasRole('Sekretariat') || ($agenda->created_by === $user->id && $agenda->status === 'diajukan');
    }

    public function delete(User $user, Agenda $agenda): bool 
    {
        return $user->hasRole(['IT Administrator', 'Sekretariat']) || $agenda->created_by === $user->id;
    }

    public function approve(User $user, Agenda $agenda): bool 
    {
        return $user->hasRole('Sekretariat');
    }

    public function cancel(User $user, Agenda $agenda): bool 
    {
        return $user->hasRole(['IT Administrator', 'Sekretariat']) || $agenda->created_by === $user->id;
    }
}