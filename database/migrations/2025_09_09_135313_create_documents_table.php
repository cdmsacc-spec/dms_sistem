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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kapal_id')->constrained('nama_kapals')->noActionOnDelete();
            $table->foreignId('jenis_dokumen_id')->nullable()->constrained('jenis_documents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('keterangan')->nullable()->default(null);
            $table->string('penerbit');
            $table->string('tempat_penerbitan');
            $table->string('status');
            $table->timestamps();
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
