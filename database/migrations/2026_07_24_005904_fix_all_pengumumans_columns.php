<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // Daftar semua kolom yang dibutuhkan oleh Pengumuman
            $columnsToAdd = [
                'jenis' => fn($t) => $t->string('jenis')->nullable(),
                'tanggal_mulai' => fn($t) => $t->date('tanggal_mulai')->nullable(),
                'tanggal_selesai' => fn($t) => $t->date('tanggal_selesai')->nullable(),
                'target_audience' => fn($t) => $t->string('target_audience')->nullable(),
                'target_ids' => fn($t) => $t->json('target_ids')->nullable(),
                'prioritas' => fn($t) => $t->string('prioritas')->default('umum'),
                'status' => fn($t) => $t->string('status')->default('draft'),
                'created_by' => fn($t) => $t->string('created_by')->nullable(),
                'tanggal_publish' => fn($t) => $t->date('tanggal_publish')->nullable(), // <-- PENYEBAB ERROR
                'tanggal_berakhir' => fn($t) => $t->date('tanggal_berakhir')->nullable(),
            ];

            // Tambahkan hanya jika kolom belum ada
            foreach ($columnsToAdd as $column => $definition) {
                if (!Schema::hasColumn('pengumumans', $column)) {
                    $definition($table);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            $table->dropColumn([
                'jenis', 'tanggal_mulai', 'tanggal_selesai', 'target_audience', 
                'target_ids', 'prioritas', 'status', 'created_by', 
                'tanggal_publish', 'tanggal_berakhir'
            ]);
        });
    }
};