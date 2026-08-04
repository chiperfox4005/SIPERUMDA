<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update tabel ruangans
        if (Schema::hasTable('ruangans')) {
            Schema::table('ruangans', function (Blueprint $table) {
                if (!Schema::hasColumn('ruangans', 'kode_ruangan')) {
                    $table->string('kode_ruangan')->nullable()->after('nama_ruangan');
                }
                if (!Schema::hasColumn('ruangans', 'kategori')) {
                    $table->enum('kategori', ['OR.K', 'OR.B', 'Trandis', 'Joglo', 'Perencanaan'])->default('OR.K')->after('kode_ruangan');
                }
                if (!Schema::hasColumn('ruangans', 'memerlukan_surat')) {
                    $table->boolean('memerlukan_surat')->default(false)->after('kategori');
                }
            });
        }

        // Update tabel peminjaman_ruangans
        if (Schema::hasTable('peminjaman_ruangans')) {
            Schema::table('peminjaman_ruangans', function (Blueprint $table) {
                if (!Schema::hasColumn('peminjaman_ruangans', 'agenda_id')) {
                    $table->foreignId('agenda_id')->nullable()->after('id')->constrained('agendas')->onDelete('cascade');
                }
                if (!Schema::hasColumn('peminjaman_ruangans', 'dokumen_resmi')) {
                    $table->string('dokumen_resmi')->nullable()->after('keperluan');
                }
                if (!Schema::hasColumn('peminjaman_ruangans', 'status_peminjaman')) {
                    $table->enum('status_peminjaman', ['diajukan', 'disetujui', 'ditolak', 'selesai'])->default('diajukan')->after('status_persetujuan');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            $table->dropForeign(['agenda_id']);
            $table->dropColumn(['agenda_id', 'dokumen_resmi', 'status_peminjaman']);
        });
        
        Schema::table('ruangans', function (Blueprint $table) {
            $table->dropColumn(['kode_ruangan', 'kategori', 'memerlukan_surat']);
        });
    }
};