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
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->timestamp('tanggal_arsip')->nullable();
            $table->unsignedBigInteger('arsipkan_oleh')->nullable();

            $table->foreign('arsipkan_oleh')->references('id')->on('pengguna')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->dropForeign(['arsipkan_oleh']);
            $table->dropColumn(['tanggal_arsip', 'arsipkan_oleh']);
        });
    }
};
