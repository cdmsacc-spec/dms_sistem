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
        Schema::create('crew_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkl_id')->constrained('crew_pkls')->cascadeOnDelete();
            $table->integer('nilai');
            $table->text('appraiser');
            $table->longText('keterangan')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_appraisals');
    }
};
