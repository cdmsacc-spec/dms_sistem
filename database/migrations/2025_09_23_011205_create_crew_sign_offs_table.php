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
        Schema::create('crew_sign_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained('crew_applicants')->cascadeOnDelete();
            $table->date('tanggal');
            $table->longText('keterangan')->nullable()->default(null);
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_sign_offs');
    }
};
