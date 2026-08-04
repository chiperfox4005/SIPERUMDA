<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('signatories')) {
            Schema::create('signatories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('position');
                $table->string('npp')->unique();
                $table->string('ttd_image_path')->nullable(); // Path gambar tanda tangan
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('signatories');
    }
};