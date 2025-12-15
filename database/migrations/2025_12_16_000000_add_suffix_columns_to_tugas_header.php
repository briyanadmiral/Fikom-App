<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add Suffix Columns for Nomor Turunan
 * 
 * Enables derivative letter numbering (002A, 002B) for Surat Tugas
 * to handle cases where signing happens after the event.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            // Suffix letter (A, B, C, ... Z) - nullable karena kebanyakan nomor normal
            $table->char('suffix', 1)->nullable()->after('nomor')
                  ->comment('Suffix letter untuk nomor turunan (A-Z)');
            
            // Parent reference untuk relasi turunan
            $table->unsignedBigInteger('parent_tugas_id')->nullable()->after('suffix')
                  ->comment('FK ke tugas_header induk untuk nomor turunan');
            
            // Integer portion for proper sorting (extracted from nomor)
            $table->unsignedSmallInteger('nomor_urut_int')->nullable()->after('parent_tugas_id')
                  ->comment('Nomor urut integer untuk sorting yang benar');
            
            // Foreign key constraint
            $table->foreign('parent_tugas_id')
                  ->references('id')->on('tugas_header')
                  ->onDelete('set null');
            
            // Composite index for correct sorting
            $table->index(
                ['tahun', 'bulan', 'kode_surat', 'nomor_urut_int', 'suffix'], 
                'idx_nomor_sorting'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['parent_tugas_id']);
            
            // Drop index
            $table->dropIndex('idx_nomor_sorting');
            
            // Drop columns
            $table->dropColumn(['suffix', 'parent_tugas_id', 'nomor_urut_int']);
        });
    }
};
