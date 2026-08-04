<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('document_templates')->onDelete('cascade');
            $table->string('user_id'); // Menyimpan NIP user pembuat
            $table->string('nomor_surat')->nullable()->unique(); // Diisi saat approval
            $table->json('data_json'); // Data dinamis yang diisi user
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('submitted');
            $table->string('approved_by')->nullable(); // NIP yang approve
            $table->timestamp('approved_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_submissions');
    }
};