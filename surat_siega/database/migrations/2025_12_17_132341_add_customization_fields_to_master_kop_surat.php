<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds full customization fields for Kop Surat
     */
    public function up(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $table) {
            // Font Family
            $table->string('font_family', 50)->default('Arial')->after('text_color');

            // Divider Customization
            $table->string('divider_color', 7)->default('#000000')->after('font_family');
            $table->tinyInteger('divider_width')->unsigned()->default(2)->after('divider_color');
            $table->boolean('show_divider')->default(true)->after('divider_width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $table) {
            $table->dropColumn(['font_family', 'divider_color', 'divider_width', 'show_divider']);
        });
    }
};
