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
            $table->foreignId('id_kontrak')->constrained('crew_kontraks')->cascadeOnDelete();
            $table->integer('nilai');
            $table->text('aprraiser');
            $table->longText('keterangan');
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
