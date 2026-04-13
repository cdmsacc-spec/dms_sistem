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
        // ── Tabel utama template ───────────────────────────────────────────────
        Schema::create('reminder_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->foreignId('id_author')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
 
        // ── Jadwal (hari & jam) dari template ─────────────────────────────────
        Schema::create('reminder_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reminder_template')
                ->constrained('reminder_templates')
                ->cascadeOnDelete();
            $table->unsignedInteger('reminder_hari');
            $table->time('reminder_jam');
            $table->timestamps();
        });
 
        // ── Penerima dari template ─────────────────────────────────────────────
        Schema::create('to_reminder_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reminder_template')
                ->constrained('reminder_templates')
                ->cascadeOnDelete();
            $table->string('nama');
            $table->string('send_to');
            $table->string('type'); // 'wa' | 'email'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('to_reminder_template_items');
        Schema::dropIfExists('reminder_template_items');
        Schema::dropIfExists('reminder_templates');
    }
};
