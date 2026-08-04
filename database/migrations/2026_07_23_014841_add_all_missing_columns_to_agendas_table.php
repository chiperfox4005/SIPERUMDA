<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // 1. Waktu & Tempat
            if (!Schema::hasColumn('agendas', 'jam_mulai')) {
                $table->time('jam_mulai')->nullable()->after('tanggal_mulai');
            }
            if (!Schema::hasColumn('agendas', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable()->after('jam_mulai');
            }
            if (!Schema::hasColumn('agendas', 'tempat')) {
                $table->string('tempat')->nullable()->after('jam_selesai');
            }
            
            // 2. Detail Agenda
            if (!Schema::hasColumn('agendas', 'acara')) {
                $table->text('acara')->nullable()->after('tempat');
            }
            if (!Schema::hasColumn('agendas', 'pimpinan_rapat')) {
                $table->string('pimpinan_rapat')->nullable()->after('acara');
            }
            if (!Schema::hasColumn('agendas', 'peserta')) {
                $table->json('peserta')->nullable()->after('pimpinan_rapat');
            }
            
            // 3. Informasi Tambahan
            if (!Schema::hasColumn('agendas', 'inisiator')) {
                $table->string('inisiator')->nullable()->after('peserta');
            }
            if (!Schema::hasColumn('agendas', 'notulen')) {
                $table->string('notulen')->nullable()->after('inisiator');
            }
            if (!Schema::hasColumn('agendas', 'catatan')) {
                $table->json('catatan')->nullable()->after('notulen');
            }
            if (!Schema::hasColumn('agendas', 'lampiran')) {
                $table->string('lampiran')->nullable()->after('catatan');
            }
            
            // 4. Integrasi Ruangan
            if (!Schema::hasColumn('agendas', 'membutuhkan_ruangan')) {
                $table->boolean('membutuhkan_ruangan')->default(false)->after('lampiran');
            }
            if (!Schema::hasColumn('agendas', 'ruangan_id')) {
                $table->unsignedBigInteger('ruangan_id')->nullable()->after('membutuhkan_ruangan');
            }
            if (!Schema::hasColumn('agendas', 'peminjaman_ruangan_id')) {
                $table->unsignedBigInteger('peminjaman_ruangan_id')->nullable()->after('ruangan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $columns = [
                'jam_mulai', 'jam_selesai', 'tempat', 'acara', 'pimpinan_rapat', 
                'peserta', 'inisiator', 'notulen', 'catatan', 'lampiran', 
                'membutuhkan_ruangan', 'ruangan_id', 'peminjaman_ruangan_id'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('agendas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};