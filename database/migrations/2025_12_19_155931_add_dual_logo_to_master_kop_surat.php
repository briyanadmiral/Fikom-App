<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk menambahkan dukungan dual logo pada Kop Surat
 * - Menambahkan logo_kiri_path dan tampilkan_logo_kiri
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $table) {
            // Dual Logo Support
            $table->string('logo_kiri_path', 255)->nullable()->after('website');
            $table->boolean('tampilkan_logo_kiri')->default(false)->after('logo_kiri_path');
        });
    }

    public function down(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $table) {
            $table->dropColumn(['logo_kiri_path', 'tampilkan_logo_kiri']);
        });
    }
};
