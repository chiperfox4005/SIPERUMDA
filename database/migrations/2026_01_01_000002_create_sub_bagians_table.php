<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sub_bagians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bagian_id')->constrained('bagians')->cascadeOnDelete();
            $table->string('nama_sub_bagian', 100);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('sub_bagians'); }
};