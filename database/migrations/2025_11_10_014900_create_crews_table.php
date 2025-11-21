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
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->text('nama_crew');
            $table->string('avatar')->nullable();
            $table->text('posisi_dilamar')->nullable()->default(null);
            $table->longText('tempat_lahir')->nullable()->default(null);
            $table->date('tanggal_lahir')->nullable()->default(null);
            $table->text('jenis_kelamin')->nullable()->default(null);
            $table->text('golongan_darah')->nullable()->default(null);
            $table->text('status_identitas')->nullable()->default(null);
            $table->text('agama')->nullable()->default(null);
            $table->string('no_hp')->nullable()->default(null);
            $table->string('no_hp_rumah')->nullable()->default(null);
            $table->string('email')->nullable()->default(null);
            $table->text('kebangsaan')->nullable()->default(null);
            $table->text('suku')->nullable()->default(null);
            $table->text('alamat_ktp')->nullable()->default(null);
            $table->text('alamat_sekarang')->nullable()->default(null);
            $table->text('status_rumah')->nullable()->default(null);
            $table->integer('tinggi_badan')->nullable()->default(null);
            $table->integer('berat_badan')->nullable()->default(null);
            $table->text('ukuran_waerpack')->nullable()->default(null);
            $table->text('ukuran_sepatu')->nullable()->default(null);
            $table->text('status')->default('draft');
            $table->index('status');
            $table->index('nama_crew');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crews');
    }
};
