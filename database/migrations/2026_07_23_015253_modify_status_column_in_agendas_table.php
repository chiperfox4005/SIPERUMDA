<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('status', 20)->change(); // Atau sesuaikan dengan enum yang benar
        });
    }

    public function down()
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('status', 5)->change(); // Kembalikan ke kondisi lama
        });
    }
};