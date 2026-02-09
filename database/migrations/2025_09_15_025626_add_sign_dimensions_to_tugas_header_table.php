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
            // Kolom baru untuk sistem tanda tangan tanpa offset
            $table->unsignedSmallInteger('ttd_w_mm')->nullable()->after('cap_config')->comment('Lebar TTD dalam mm');
            $table->unsignedSmallInteger('cap_w_mm')->nullable()->after('ttd_w_mm')->comment('Lebar Cap dalam mm');
            $table->decimal('cap_opacity', 3, 2)->nullable()->after('cap_w_mm')->comment('Opacity Cap (0.00 - 1.00)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->dropColumn(['ttd_w_mm', 'cap_w_mm', 'cap_opacity']);
        });
    }
};
