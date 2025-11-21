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
        Schema::create('lookups', function (Blueprint $table) {
            $table->id();
            // Grup/tipe lookup: contoh "jenis_kapal", "agama", "status_pegawai"
            $table->string('type', 100)->index();
            // Nilai kunci dan tampilannya
            $table->string('code', 100)->nullable()->index(); // misal: "JKL01"
            $table->string('name', 150); // misal: "Jenis Kapal Penumpang"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookups');
    }
};
