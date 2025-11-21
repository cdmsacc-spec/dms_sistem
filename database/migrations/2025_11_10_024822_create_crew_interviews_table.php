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
            $table->foreignId('id_crew')->constrained('crews')->cascadeOnDelete();
            $table->longText('crewing')->nullable();
            $table->longText('user_operation')->nullable();
            $table->longText('summary')->nullable();
            $table->longText('keterangan')->nullable();
            $table->date('tanggal');
            $table->string('file')->nullable();
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
