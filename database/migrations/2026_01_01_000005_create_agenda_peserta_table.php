<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agenda_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status_kehadiran', ['belum_merespons', 'hadir', 'tidak_hadir'])->default('belum_merespons');
            $table->timestamps();
            $table->unique(['agenda_id', 'user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('agenda_peserta'); }
};