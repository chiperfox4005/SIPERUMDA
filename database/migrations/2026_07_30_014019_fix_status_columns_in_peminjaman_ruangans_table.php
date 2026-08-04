<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            // 1. Hapus kolom lama yang menyebabkan error truncation
            $table->dropColumn(['status_peminjaman', 'status_persetujuan']);
        });

        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            // 2. Tambahkan kembali dengan tipe string yang cukup panjang (20 karakter)
            // Sesuaikan urutan 'after' dengan struktur tabel Anda saat ini
            $table->string('status_peminjaman', 20)->default('menunggu')->after('jumlah_peserta');
            $table->string('status_persetujuan', 20)->default('menunggu')->after('status_peminjaman');
        });
    }

    public function down()
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            $table->dropColumn(['status_peminjaman', 'status_persetujuan']);
            
            // Kembalikan ke enum awal sesuai migrasi pertama Anda (jasa jaga-jaga)
            $table->enum('status_peminjaman', ['pending', 'approved', 'rejected'])->default('pending')->after('jumlah_peserta');
            $table->enum('status_persetujuan', ['pending', 'approved', 'rejected'])->default('pending')->after('status_peminjaman');
        });
    }
};