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
            if (!Schema::hasColumn('peminjaman_ruangans', 'dokumen_resmi')) {
                $table->string('dokumen_resmi')->nullable()->after('keperluan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            $table->dropColumn('dokumen_resmi');
        });
    }
};