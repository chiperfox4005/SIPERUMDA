<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable(); // Bisa NIP atau ID
            $table->string('user_name')->nullable();
            $table->string('action'); // created, updated, deleted
            $table->string('model_type'); // Nama Model, misal: App\Models\Pengumuman
            $table->unsignedBigInteger('model_id'); // ID data yang diubah
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};