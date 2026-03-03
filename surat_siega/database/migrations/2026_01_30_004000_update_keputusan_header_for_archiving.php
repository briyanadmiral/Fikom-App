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
        Schema::table('keputusan_header', function (Blueprint $table) {
            // Check if column exists to allow safe repeated runs or future compatibility
            if (! Schema::hasColumn('keputusan_header', 'tanggal_arsip')) {
                $table->timestamp('tanggal_arsip')->nullable();
            }
            if (! Schema::hasColumn('keputusan_header', 'arsipkan_oleh')) {
                $table->unsignedBigInteger('arsipkan_oleh')->nullable();
                $table->foreign('arsipkan_oleh')->references('id')->on('pengguna')->onDelete('set null');
            }
        });

        // Modify ENUM to include 'ditolak', 'terbit', 'arsip'
        DB::statement("ALTER TABLE keputusan_header MODIFY COLUMN status_surat ENUM('draft', 'pending', 'disetujui', 'ditolak', 'terbit', 'arsip') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keputusan_header', function (Blueprint $table) {
            if (Schema::hasColumn('keputusan_header', 'arsipkan_oleh')) {
                $table->dropForeign(['arsipkan_oleh']);
                $table->dropColumn('arsipkan_oleh');
            }
            if (Schema::hasColumn('keputusan_header', 'tanggal_arsip')) {
                $table->dropColumn('tanggal_arsip');
            }
        });

        // Reverting enum is tricky if data exists, just leaving it as broader set is safer usually,
        // but for exact "down", we would remove the new options. skipping strictly for safety.
    }
};
