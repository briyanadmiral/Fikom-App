<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL is often safer/easier for ENUM modification in MySQL
        DB::statement("ALTER TABLE tugas_header MODIFY COLUMN status_surat ENUM('draft', 'pending', 'disetujui', 'ditolak', 'arsip') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous state (assuming ditolak was allowed, but arsip was not)
        DB::statement("ALTER TABLE tugas_header MODIFY COLUMN status_surat ENUM('draft', 'pending', 'disetujui', 'ditolak') NOT NULL DEFAULT 'draft'");
    }
};
