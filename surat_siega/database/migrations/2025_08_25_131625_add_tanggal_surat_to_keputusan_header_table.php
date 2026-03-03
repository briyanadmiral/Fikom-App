<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keputusan_header', function (Blueprint $t) {
            if (! Schema::hasColumn('keputusan_header', 'tanggal_surat')) {
                $t->date('tanggal_surat')->nullable()->after('nomor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('keputusan_header', function (Blueprint $t) {
            if (Schema::hasColumn('keputusan_header', 'tanggal_surat')) {
                $t->dropColumn('tanggal_surat');
            }
        });
    }
};
