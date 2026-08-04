<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Undangan Rapat"
            $table->string('code')->unique(); // Contoh: "UNDANG-RAPAT"
            $table->string('blade_view_path'); // Contoh: "templates.surat.undangan-rapat"
            $table->json('form_schema'); // Definisi field form dalam format JSON
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_templates');
    }
};