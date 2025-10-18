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
        Schema::create('crew_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('crew_applicants')->cascadeOnDelete();
            $table->longText('keterangan')->nullable()->default(null);
            $table->longText('hasil_interviewe1')->nullable()->default(null);
            $table->longText('hasil_interviewe2')->nullable()->default(null);
            $table->longText('hasil_interviewe3')->nullable()->default(null);
            $table->longText('sumary')->nullable()->default(null);
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
        Schema::dropIfExists('crew_interviews');
    }
};
