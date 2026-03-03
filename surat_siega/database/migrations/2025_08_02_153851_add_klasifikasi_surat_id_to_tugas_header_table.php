<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->foreignId('klasifikasi_surat_id')
                ->nullable()
                ->constrained('klasifikasi_surat')
                ->after('nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            //
        });
    }
};
