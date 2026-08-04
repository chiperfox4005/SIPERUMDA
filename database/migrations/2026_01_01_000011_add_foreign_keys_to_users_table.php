<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('bagian_id')->references('id')->on('bagians')->nullOnDelete();
            $table->foreign('sub_bagian_id')->references('id')->on('sub_bagians')->nullOnDelete();
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bagian_id']);
            $table->dropForeign(['sub_bagian_id']);
            $table->dropForeign(['jabatan_id']);
        });
    }
};