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
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jenis_dokumen')->nullable()->constrained('jenis_dokumens')->nullOnDelete();
            $table->foreignId('id_kapal')->nullable()->constrained('kapals')->nullOnDelete();
            $table->foreignId('id_author')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('keterangan')->nullable();
            $table->text('penerbit');
            $table->longText('tempat_penerbitan');
            $table->text('status');
            $table->boolean('is_expiration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumens');
    }
};
