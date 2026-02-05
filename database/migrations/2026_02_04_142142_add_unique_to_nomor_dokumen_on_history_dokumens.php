<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('history_dokumens', function (Blueprint $table) {
            $table->unique('nomor_dokumen', 'history_dokumens_nomor_dokumen_unique');
        });
    }

    public function down(): void
    {
        Schema::table('history_dokumens', function (Blueprint $table) {
            $table->dropUnique('history_dokumens_nomor_dokumen_unique');
        });
    }
};
