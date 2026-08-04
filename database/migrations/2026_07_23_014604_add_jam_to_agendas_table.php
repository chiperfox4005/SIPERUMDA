<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // Tambah kolom jam_mulai jika belum ada
            if (!Schema::hasColumn('agendas', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->after('tanggal_mulai');
            }
            
            // Tambah kolom jam_selesai jika belum ada
            if (!Schema::hasColumn('agendas', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->after('jam_mulai');
            }

            // Tambah kolom lampiran jika belum ada (untuk jaga-jaga)
            if (!Schema::hasColumn('agendas', 'lampiran')) {
                $table->string('lampiran')->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'lampiran']);
        });
    }
};