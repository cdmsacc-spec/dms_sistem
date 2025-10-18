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
        Schema::create('crew_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained('crew_applicants')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('nama_sertifikat');
            $table->string('nomor_sertifikat');
            $table->longText('tempat_dikeluarkan');
            $table->date('tanggal_dikeluarkan');
            $table->date('tanggal_expired');
            $table->string('status');
            $table->string('file_path');
            $table->index('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_certificates');
    }
};
