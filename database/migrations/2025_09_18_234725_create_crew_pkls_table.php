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
        Schema::create('crew_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('crew_applicants')->cascadeOnDelete();
            $table->string('nomor_document');
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaans')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatans')->nullOnDelete();
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah_operasionals')->nullOnDelete();
            $table->foreignId('kapal_id')->nullable()->constrained('nama_kapals')->nullOnDelete();
            $table->string('gaji');
            $table->string('berangkat_dari');
            $table->string('file_path')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('kategory');
            $table->boolean('isNearExpiry')->default(false);
            $table->boolean('kontrak_lanjutan')->default(false);
            $table->string('status_kontrak')->default('Waiting Approval');
            $table->timestamps();
            $table->index('start_date');
            $table->index('kategory');
            $table->index('status_kontrak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_pkls');
    }
};
