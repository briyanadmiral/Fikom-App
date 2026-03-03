<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keputusan_header', function (Blueprint $table) {
            $table->dropColumn('tanggal_asli');
        });
    }

    public function down(): void
    {
        Schema::table('keputusan_header', function (Blueprint $table) {
            $table->date('tanggal_asli')->nullable()->after('tentang');
        });
    }
};
