<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_kop_surat')) {
            Schema::table('master_kop_surat', function (Blueprint $t) {
                $t->unsignedSmallInteger('cap_default_width_mm')->default(30)->after('cap_path');
                $t->unsignedTinyInteger('cap_opacity')->default(85)->after('cap_default_width_mm'); // 0-100
                $t->integer('cap_offset_x_mm')->default(0)->after('cap_opacity');
                $t->integer('cap_offset_y_mm')->default(0)->after('cap_offset_x_mm');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('master_kop_surat')) {
            Schema::table('master_kop_surat', function (Blueprint $t) {
                $t->dropColumn(['cap_default_width_mm', 'cap_opacity', 'cap_offset_x_mm', 'cap_offset_y_mm']);
            });
        }
    }
};
