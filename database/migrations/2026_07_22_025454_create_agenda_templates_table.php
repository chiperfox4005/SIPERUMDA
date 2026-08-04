<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agenda_templates')) {
            Schema::create('agenda_templates', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('icon')->default('bi-calendar-event');
                $table->text('description')->nullable();
                $table->json('form_schema')->nullable();
                $table->json('pdf_layout')->nullable();
                $table->boolean('requires_room')->default(false);
                $table->boolean('requires_letter')->default(true);
                $table->string('letter_template')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_templates');
    }
};