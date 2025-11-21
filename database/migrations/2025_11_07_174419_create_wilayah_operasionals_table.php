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
        Schema::create('wilayah_operasionals', function (Blueprint $table) {
            $table->id();
            $table->text('nama_wilayah');
            $table->text('kode_wilayah');
            $table->longText('deskripsi');
            $table->string('ttd_dibuat')->nullable()->default(null);
            $table->string('ttd_diperiksa')->nullable()->default(null);
            $table->string('ttd_diketahui_1')->nullable()->default(null);
            $table->string('ttd_diketahui_2')->nullable()->default(null);
            $table->string('ttd_disetujui_1')->nullable()->default(null);
            $table->string('ttd_disetujui_2')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_operasionals');
    }
};
