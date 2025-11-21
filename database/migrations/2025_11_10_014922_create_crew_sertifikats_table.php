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
        Schema::create('crew_sertifikats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_crew')->constrained('crews')->cascadeOnDelete();
            $table->text('kategory');
            $table->text('nama_sertifikat');
            $table->string('nomor_sertifikat');
            $table->longText('tempat_dikeluarkan');
            $table->date('tanggal_terbit');
            $table->date('tanggal_expired')->nullable()->default(null);
            $table->text('status');
            $table->text('file');
            $table->index('tanggal_terbit');
            $table->index('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_sertifikats');
    }
};
