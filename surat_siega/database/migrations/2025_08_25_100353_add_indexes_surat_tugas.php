<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Helper: cek apakah index dengan nama tertentu sudah ada */
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$db, $table, $index]
        );

        return (bool) $row;
    }

    /** Helper: buat index kalau belum ada (pakai raw agar aman dari duplikasi) */
    private function createIndexIfMissing(string $table, string $index, string $columns): void
    {
        if (! $this->indexExists($table, $index)) {
            DB::statement("CREATE INDEX `$index` ON `$table` ($columns)");
        }
    }

    /** Helper: drop index kalau ada */
    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            DB::statement("DROP INDEX `$index` ON `$table`");
        }
    }

    public function up(): void
    {
        // ==== tugas_header ====
        $this->createIndexIfMissing('tugas_header', 'idx_tugas_status', 'status_surat');
        $this->createIndexIfMissing('tugas_header', 'idx_tugas_dibuat_oleh', 'dibuat_oleh');
        $this->createIndexIfMissing('tugas_header', 'idx_tugas_next_approver', 'next_approver');
        $this->createIndexIfMissing('tugas_header', 'idx_tugas_penandatangan', 'penandatangan');
        $this->createIndexIfMissing('tugas_header', 'idx_tugas_created_status', 'created_at, status_surat');

        // ==== tugas_penerima ====
        $this->createIndexIfMissing('tugas_penerima', 'idx_penerima_tugas', 'tugas_id');
        $this->createIndexIfMissing('tugas_penerima', 'idx_penerima_pengguna', 'pengguna_id');

        // ==== notifikasi (sesuaikan dengan skema kamu) ====
        // Di DB kamu kolomnya: pengguna_id, tipe, referensi_id, dibaca
        if (Schema::hasTable('notifikasi')) {
            // Index untuk filter baca/belum
            $this->createIndexIfMissing('notifikasi', 'idx_notif_dibaca', 'dibaca');

            // Index komposit untuk lookup notifikasi dari suatu entitas
            $this->createIndexIfMissing('notifikasi', 'idx_notif_tipe_ref', 'tipe, referensi_id');

            // Tidak menambah index pengguna_id karena sudah ada dari FK (notifikasi_pengguna_id_foreign).
            // Kalau kamu tetap ingin menambah, pakai baris di bawah ini:
            // $this->createIndexIfMissing('notifikasi', 'idx_notif_pengguna', 'pengguna_id');
        }
    }

    public function down(): void
    {
        // ==== tugas_header ====
        $this->dropIndexIfExists('tugas_header', 'idx_tugas_status');
        $this->dropIndexIfExists('tugas_header', 'idx_tugas_dibuat_oleh');
        $this->dropIndexIfExists('tugas_header', 'idx_tugas_next_approver');
        $this->dropIndexIfExists('tugas_header', 'idx_tugas_penandatangan');
        $this->dropIndexIfExists('tugas_header', 'idx_tugas_created_status');

        // ==== tugas_penerima ====
        $this->dropIndexIfExists('tugas_penerima', 'idx_penerima_tugas');
        $this->dropIndexIfExists('tugas_penerima', 'idx_penerima_pengguna');

        // ==== notifikasi ====
        $this->dropIndexIfExists('notifikasi', 'idx_notif_dibaca');
        $this->dropIndexIfExists('notifikasi', 'idx_notif_tipe_ref');
        // $this->dropIndexIfExists('notifikasi', 'idx_notif_pengguna'); // kalau kamu buat yang ini
    }
};
