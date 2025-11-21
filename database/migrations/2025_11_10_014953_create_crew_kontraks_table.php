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
        Schema::create('crew_kontraks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_crew')->constrained('crews')->cascadeOnDelete();
            $table->foreignId('id_perusahaan')->nullable()->constrained('perusahaans')->nullOnDelete();
            $table->foreignId('id_jabatan')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('id_kapal')->nullable()->constrained('kapals')->nullOnDelete();
            $table->foreignId('id_wilayah')->nullable()->constrained('wilayah_operasionals')->nullOnDelete();
            $table->string('nomor_dokumen');
            $table->text('gaji')->nullable()->default(null);
            $table->text('berangkat_dari')->nullable()->default(null);
            $table->date('start_date');
            $table->date('end_date')->nullable()->default(null);
            $table->text('kategory');
            $table->boolean('near_expiry')->default(false);
            $table->boolean('kontrak_lanjutan')->default(false);
            $table->text('status_kontrak')->default('waiting approval');
            $table->string('file')->nullable()->default(null);
            $table->index('start_date');
            $table->index('kategory');
            $table->index('status_kontrak');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_kontraks');
    }
};
