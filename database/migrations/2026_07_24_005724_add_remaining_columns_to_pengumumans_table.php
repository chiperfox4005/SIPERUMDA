<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // 1. target_audience (menyimpan 'semua_pegawai' atau 'bagian_tertentu')
            if (!Schema::hasColumn('pengumumans', 'target_audience')) {
                $table->string('target_audience')->nullable()->after('tanggal_selesai');
            }

            // 2. target_ids (menyimpan JSON daftar ID bagian/sub bagian yang dituju)
            if (!Schema::hasColumn('pengumumans', 'target_ids')) {
                $table->json('target_ids')->nullable()->after('target_audience');
            }

            // 3. prioritas (menyimpan 'umum', 'penting', atau 'mendesak')
            if (!Schema::hasColumn('pengumumans', 'prioritas')) {
                $table->string('prioritas')->default('umum')->after('target_ids');
            }

            // 4. status (menyimpan 'draft' atau 'publish')
            if (!Schema::hasColumn('pengumumans', 'status')) {
                $table->string('status')->default('draft')->after('prioritas');
            }
            
            // 5. created_by (menyimpan NIP pembuat)
            if (!Schema::hasColumn('pengumumans', 'created_by')) {
                $table->string('created_by')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            $table->dropColumn([
                'target_audience', 
                'target_ids', 
                'prioritas', 
                'status', 
                'created_by'
            ]);
        });
    }
};