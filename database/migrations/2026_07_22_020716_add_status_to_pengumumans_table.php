<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            // Tambahkan kolom status jika belum ada (tanpa after)
            if (!Schema::hasColumn('pengumumans', 'status')) {
                $table->enum('status', ['draft', 'publish'])->default('draft');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengumumans', function (Blueprint $table) {
            if (Schema::hasColumn('pengumumans', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};