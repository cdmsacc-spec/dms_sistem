<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jenis_kapal_dokumen', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_jenis_kapal')
                ->constrained('jenis_kapals')
                ->cascadeOnDelete();

            $table->foreignId('id_jenis_dokumen')
                ->constrained('jenis_dokumens')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_kapal_dokumen');
    }
};
