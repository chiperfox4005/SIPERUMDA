<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->unique();
            $table->string('nama_lengkap', 150);
            $table->string('password');
            $table->unsignedBigInteger('bagian_id')->nullable();
            $table->unsignedBigInteger('sub_bagian_id')->nullable();
            $table->unsignedBigInteger('jabatan_id')->nullable();
            $table->string('foto_profil')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};