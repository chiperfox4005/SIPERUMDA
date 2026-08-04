<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruangans', function (Blueprint $table) {
            if (!Schema::hasColumn('ruangans', 'kategori')) {
                $table->enum('kategori', ['OR.K', 'OR.B', 'Trandis', 'Joglo', 'Perencanaan'])
                    ->default('OR.K')
                    ->after('nama_ruangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ruangans', function (Blueprint $table) {
            if (Schema::hasColumn('ruangans', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });
    }
};