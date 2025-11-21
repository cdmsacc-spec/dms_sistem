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
        Schema::create('crew_noks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_crew')->constrained('crews')->cascadeOnDelete();
            $table->text('nama')->nullable()->default(null);
            $table->text('hubungan')->nullable()->default(null);
            $table->text('alamat')->nullable()->default(null);
            $table->text('no_hp')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_noks');
    }
};
