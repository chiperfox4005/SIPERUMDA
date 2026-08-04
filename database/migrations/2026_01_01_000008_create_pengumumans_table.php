<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('isi');
            $table->string('kategori', 50)->nullable();
            $table->string('lampiran')->nullable();
            $table->dateTime('tanggal_publish');
            $table->dateTime('tanggal_berakhir')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pengumumans'); }
};