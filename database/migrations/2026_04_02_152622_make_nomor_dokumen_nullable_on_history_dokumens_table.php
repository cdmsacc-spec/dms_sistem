<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE history_dokumens ALTER COLUMN nomor_dokumen TYPE TEXT');
    }

    public function down(): void
    {
    }
};
