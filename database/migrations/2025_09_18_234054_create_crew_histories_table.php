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
        Schema::create('crew_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('crew_applicants')->cascadeOnDelete();
            $table->enum('jenis_proses', ['Sign On', 'Sign Off', 'Mutasi', 'Promosi', 'PHK', 'Interview']);
            $table->foreignId('kapal_awal')->nullable()->constrained('nama_kapals')->nullOnDelete();
            $table->foreignId('kapal_tujuan')->nullable()->constrained('nama_kapals')->nullOnDelete();
            $table->longText('keterangan')->nullable()->default(null);
       
            $table->date('tanggal');
            $table->string('file_path')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_histories');
    }
};
