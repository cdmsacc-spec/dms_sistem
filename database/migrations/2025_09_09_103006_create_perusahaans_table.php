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
        Schema::create('perusahaans', function (Blueprint $table) {
            $table->id();
            $table->text('nama_perusahaan');
            $table->text('kode_perusahaan');
            $table->longText('alamat');
            $table->string('file_path')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('email')->nullable()->default(null);
            $table->string('telepon')->nullable()->default(null);
            $table->string('npwp')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perusahaans');
    }
};
