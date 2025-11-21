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
        Schema::create('kapals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_perusahaan')->nullable()->constrained('perusahaans')->nullOnDelete();
            $table->foreignId('id_jenis_kapal')->nullable()->constrained('jenis_kapals')->nullOnDelete();
            $table->foreignId('id_wilayah')->nullable()->constrained('wilayah_operasionals')->nullOnDelete();
            $table->text('nama_kapal');
            $table->text('status_certified');
            $table->text('tahun_kapal');
            $table->longText('keterangan');
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kapals');
    }
};
