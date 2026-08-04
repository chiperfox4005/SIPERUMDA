<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            // Cek dulu agar tidak error jika kolom sudah ada
            if (!Schema::hasColumn('peminjaman_ruangans', 'ditolak_oleh')) {
                $table->string('ditolak_oleh')->nullable()->after('catatan_penolakan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            $table->dropColumn('ditolak_oleh');
        });
    }
};