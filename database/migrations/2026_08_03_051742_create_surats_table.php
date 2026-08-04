<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->nullable()->unique(); // Akan di-generate otomatis
            $table->string('jenis_surat'); // 'tugas', 'dinas', 'izin', 'undangan', 'sk'
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->text('isi_surat'); // Konten surat (bisa HTML)
            $table->string('tujuan')->nullable(); // Kepada siapa
            $table->string('penerima_nama')->nullable(); // Nama penerima
            $table->string('penerima_nip')->nullable();
            $table->string('penerima_jabatan')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->string('dibuat_oleh'); // NIP pembuat
            $table->string('disetujui_oleh')->nullable(); // NIP yang approve
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->string('file_path')->nullable(); // Path PDF final
            $table->string('penandatangan_id')->nullable(); // ID pejabat yang tanda tangan (relasi ke signatories)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};