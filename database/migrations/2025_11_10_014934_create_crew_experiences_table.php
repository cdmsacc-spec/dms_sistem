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
        Schema::create('crew_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_crew')->constrained('crews')->cascadeOnDelete();
            $table->text('nama_kapal');
            $table->text('tipe_kapal');
            $table->text('nama_perusahaan');
            $table->text('posisi');
            $table->string('gt_kw');
            $table->text('bendera');
            $table->text('masa_kerja');
            $table->date('periode_awal');
            $table->date('periode_akhir')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_experiences');
    }
};
