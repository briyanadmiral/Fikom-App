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
        Schema::table('tugas_penerima', function (Blueprint $table) {
            // 1. Buat pengguna_id bisa NULL, karena penerima bisa jadi bukan dari tabel pengguna
            $table->unsignedBigInteger('pengguna_id')->nullable()->change();

            // 2. Tambah kolom baru untuk menyimpan nama dan jabatan secara eksplisit
            //    Ini akan diisi baik untuk pengguna internal maupun eksternal
            $table->string('nama_penerima', 255)->after('pengguna_id');
            $table->string('jabatan_penerima', 255)->nullable()->after('nama_penerima');

            // 3. Kolom 'posisi' lama bisa kita hapus karena sudah digantikan 'jabatan_penerima'
            $table->dropColumn('posisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_penerima', function (Blueprint $table) {
            $table->unsignedBigInteger('pengguna_id')->nullable(false)->change();
            $table->dropColumn('nama_penerima');
            $table->dropColumn('jabatan_penerima');
            $table->string('posisi')->nullable();
        });
    }
};
