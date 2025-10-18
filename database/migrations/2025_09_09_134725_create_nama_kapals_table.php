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
        Schema::create('nama_kapals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaans')->nullOnDelete();
            $table->foreignId('jenis_kapal_id')->constrained('jenis_kapals')->noActionOnDelete();
            $table->string('nama_kapal');
            $table->string('status_certified');
            $table->string('tahun_kapal')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nama_kapals');
    }
};
