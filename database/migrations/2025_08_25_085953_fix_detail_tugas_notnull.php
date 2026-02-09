<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Jaga-jaga: isi NULL ke fallback otomatis (kalau masih ada)
        $fallbackId = DB::table('tugas_detail')
            ->join('sub_tugas', 'sub_tugas.id', '=', 'tugas_detail.sub_tugas_id')
            ->join('jenis_tugas', 'jenis_tugas.id', '=', 'sub_tugas.jenis_tugas_id')
            ->where('jenis_tugas.nama', 'Lainnya')
            ->where('sub_tugas.nama', 'Lainnya')
            ->where('tugas_detail.nama', 'Lainnya')
            ->value('tugas_detail.id');

        if ($fallbackId) {
            DB::table('tugas_header')->whereNull('detail_tugas_id')->update(['detail_tugas_id' => $fallbackId]);
        }

        // 2) Hapus FK lama (yang ON DELETE SET NULL)
        Schema::table('tugas_header', function (Blueprint $t) {
            // Nama constraint biasanya 'tugas_header_detail_tugas_id_foreign'
            // dropForeign by column aman meski nama berbeda
            $t->dropForeign(['detail_tugas_id']);
        });

        // 3) Ubah kolom jadi NOT NULL
        Schema::table('tugas_header', function (Blueprint $t) {
            $t->unsignedBigInteger('detail_tugas_id')->nullable(false)->change();
        });

        // 4) Tambah FK baru: bukan SET NULL
        Schema::table('tugas_header', function (Blueprint $t) {
            $t->foreign('detail_tugas_id')
                ->references('id')->on('tugas_detail')
                ->onUpdate('cascade')
                ->onDelete('restrict'); // atau ->onDelete('cascade') sesuai kebijakanmu
        });
    }

    public function down(): void
    {
        // Balikkan perubahan
        Schema::table('tugas_header', function (Blueprint $t) {
            $t->dropForeign(['detail_tugas_id']);
        });

        Schema::table('tugas_header', function (Blueprint $t) {
            $t->unsignedBigInteger('detail_tugas_id')->nullable()->change();
        });

        Schema::table('tugas_header', function (Blueprint $t) {
            $t->foreign('detail_tugas_id')
                ->references('id')->on('tugas_detail')
                ->onUpdate('cascade')
                ->onDelete('set null'); // kembalikan seperti semula
        });
    }
};
