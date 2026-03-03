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
        Schema::table('sub_tugas', function (Blueprint $table) {
            $table->foreignId('klasifikasi_surat_id')
                ->after('jenis_tugas_id')
                ->nullable()
                ->constrained('klasifikasi_surat')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_tugas', function (Blueprint $table) {
            $table->dropForeign(['klasifikasi_surat_id']);
            $table->dropColumn('klasifikasi_surat_id');
        });
    }
};
