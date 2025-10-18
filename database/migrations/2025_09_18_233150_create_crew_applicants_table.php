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
        Schema::create('crew_applicants', function (Blueprint $table) {
            $table->id();
            $table->text('nama_crew');
            $table->text('posisi_dilamar');
            $table->longText('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki Laki', 'Perempuan']);
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O']);
            $table->string('status_identitas');
            $table->text('agama');
            $table->string('no_hp');
            $table->string('no_telp_rumah')->nullable()->default(null);
            $table->string('email');
            $table->text('kebangsaan')->nullable()->default(null);
            $table->text('suku')->nullable()->default(null);
            $table->longText('alamat_ktp')->nullable()->default(null);
            $table->longText('alamat_sekarang')->nullable()->default(null);
            $table->string('status_rumah')->nullable()->default(null);
            $table->string('tinggi_badan');
            $table->string('berat_badan');
            $table->string('ukuran_waerpack')->nullable()->default(null);
            $table->string('ukuran_sepatu')->nullable()->default(null);
            $table->text('nok_nama')->nullable()->default(null);
            $table->string('nok_hubungan')->nullable()->default(null);
            $table->longText('nok_alamat')->nullable()->default(null);
            $table->string('nok_hp')->nullable()->default(null);
            $table->string('foto')->nullable()->default(null);
            $table->string('status_proses');
            $table->timestamps();
            $table->index('status_proses');
            $table->index('nama_crew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_applicants');
    }
};
