<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('surats', function (Blueprint $table) {
        $table->foreignId('signatory_id')->nullable()->after('disetujui_oleh')->constrained('signatories')->nullOnDelete();
    });
}

public function down()
{
    Schema::table('surats', function (Blueprint $table) {
        $table->dropForeign(['signatory_id']);
        $table->dropColumn('signatory_id');
    });
}
};
