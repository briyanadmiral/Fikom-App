<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix Suffix Numbering Integrity:
 * 
 * 1. Add UNIQUE constraint (parent_tugas_id, suffix) to prevent duplicate derivatives
 * 2. Backfill nomor_urut_int for ALL existing surat (not just turunan)
 *    so scopeOrderByNomor() sorts correctly
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Backfill nomor_urut_int from nomor string
        // Extract leading digits from nomor: "001/A.4/TG/..." → 1
        DB::statement("
            UPDATE tugas_header 
            SET nomor_urut_int = CAST(
                REGEXP_SUBSTR(nomor, '^[0-9]+') AS UNSIGNED
            )
            WHERE nomor_urut_int IS NULL 
              AND nomor IS NOT NULL
              AND nomor NOT LIKE 'DRAFT-%'
              AND nomor NOT LIKE 'ST-DUMMY-%'
        ");

        // Step 2: Add UNIQUE constraint to prevent duplicate suffixes
        // Only applies when parent_tugas_id is NOT NULL (normal surat have NULL parent)
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->unique(
                ['parent_tugas_id', 'suffix'],
                'uq_parent_suffix'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->dropUnique('uq_parent_suffix');
        });

        // Note: We don't reset nomor_urut_int because the data was already correct
    }
};
