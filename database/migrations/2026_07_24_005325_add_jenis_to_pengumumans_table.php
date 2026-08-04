<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // Menambahkan kolom 'jenis' setelah kolom 'judul'
            $table->string('jenis')->nullable()->after('judul');
        });
    }

    public function down()
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};