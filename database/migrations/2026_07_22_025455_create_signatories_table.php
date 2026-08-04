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
                $table->string('position');
                $table->string('name');
                $table->string('nip');
                $table->string('signature_image')->nullable();
                $table->boolean('is_active')->default(true);
                $table->date('valid_from');
                $table->date('valid_until')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('signatories');
    }
};