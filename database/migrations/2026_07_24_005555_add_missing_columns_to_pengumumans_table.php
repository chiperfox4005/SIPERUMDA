<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // Tambahkan 'jenis' jika belum ada
            if (!Schema::hasColumn('pengumumans', 'jenis')) {
                $table->string('jenis')->nullable()->after('judul');
            }
            
            // Tambahkan 'tanggal_mulai' jika belum ada
            if (!Schema::hasColumn('pengumumans', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('jenis');
            }
            
            // Tambahkan 'tanggal_selesai' jika belum ada
            if (!Schema::hasColumn('pengumumans', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
        });
    }

    public function down()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }
};