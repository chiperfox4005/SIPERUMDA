<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Agenda;

// Update status agenda otomatis
Schedule::call(function () {
    // Ubah ke 'berlangsung'
    Agenda::where('tanggal_mulai', '<=', now())
          ->where('tanggal_selesai', '>=', now())
          ->where('status', 'disetujui')
          ->update(['status' => 'berlangsung']);
    
    // Ubah ke 'selesai'
    Agenda::where('tanggal_selesai', '<', now())
          ->whereIn('status', ['disetujui', 'berlangsung'])
          ->update(['status' => 'selesai']);
})->everyFiveMinutes();

// Backup database harian (jika menggunakan spatie/laravel-backup)
// Schedule::command('backup:run')->dailyAt('02:00');