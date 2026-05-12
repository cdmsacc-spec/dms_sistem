<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE dokumens ALTER COLUMN penerbit TYPE TEXT, ALTER COLUMN penerbit DROP NOT NULL');
        DB::statement('ALTER TABLE dokumens ALTER COLUMN tempat_penerbitan TYPE TEXT, ALTER COLUMN tempat_penerbitan DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
