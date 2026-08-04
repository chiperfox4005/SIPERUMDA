<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            // Tambahkan kolom hanya jika belum ada
            if (!Schema::hasColumn('agendas', 'template_id')) {
                $table->foreignId('template_id')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'nomor_surat')) {
                $table->string('nomor_surat')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'peserta')) {
                $table->json('peserta')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'inisiator')) {
                $table->string('inisiator')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'notulen')) {
                $table->string('notulen')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'catatan')) {
                $table->json('catatan')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'membutuhkan_ruangan')) {
                $table->boolean('membutuhkan_ruangan')->default(false);
            }
            
            if (!Schema::hasColumn('agendas', 'ruangan_id')) {
                $table->foreignId('ruangan_id')->nullable()->constrained('ruangans');
            }
            
            if (!Schema::hasColumn('agendas', 'peminjaman_ruangan_id')) {
                $table->unsignedBigInteger('peminjaman_ruangan_id')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users');
            }
            
            if (!Schema::hasColumn('agendas', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }
            
            if (!Schema::hasColumn('agendas', 'qr_code')) {
                $table->string('qr_code')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['ruangan_id']);
            $table->dropForeign(['approved_by']);
            
            $table->dropColumn([
                'template_id', 'nomor_surat', 'peserta', 'inisiator', 
                'notulen', 'catatan', 'membutuhkan_ruangan', 'ruangan_id', 
                'peminjaman_ruangan_id', 'approved_by', 'approved_at', 
                'rejection_reason', 'pdf_path', 'qr_code'
            ]);
        });
    }
};