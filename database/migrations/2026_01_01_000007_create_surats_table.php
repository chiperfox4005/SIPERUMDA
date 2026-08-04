<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 100);
            $table->string('perihal', 150);
            $table->string('jenis', 50);
            $table->string('file');
            $table->foreignId('agenda_id')->nullable()->constrained('agendas')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('surats'); }
};