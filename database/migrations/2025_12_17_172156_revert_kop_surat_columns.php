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
        Schema::table('master_kop_surat', function (Blueprint $table) {
            $columns = [
                'font_family',
                'show_divider',
                'divider_color',
                'divider_width',
                'judul_atas',
                'subjudul',
                'logo_kiri_path',
                'tampilkan_logo_kiri',
                'background_header_path',
                'is_default',
                'nama_template'
            ];
            
            // Only drop if they exist to prevent errors
            $columnsToDrop = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('master_kop_surat', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $table) {
            // Restore columns if needed (simplified restoration)
            $table->string('font_family')->nullable()->default('Arial');
            $table->boolean('show_divider')->default(true);
            $table->string('divider_color')->nullable()->default('#000000');
            $table->integer('divider_width')->default(2);
            $table->string('judul_atas')->nullable();
            $table->string('subjudul')->nullable();
            $table->string('logo_kiri_path')->nullable();
            $table->boolean('tampilkan_logo_kiri')->default(false);
            $table->string('background_header_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('nama_template')->nullable();
        });
    }
};
