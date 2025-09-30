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
            $table->foreignId('applicant_id')->constrained('crew_applicants')->cascadeOnDelete();
            $table->text('nama_kapal');
            $table->string('nama_perusahaan');
            $table->string('posisi');
            $table->string('gt_kw');
            $table->string('tipe_kapal');
            $table->string('bendera');
            $table->string('periode_awal');
            $table->string('periode_akhir');
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
