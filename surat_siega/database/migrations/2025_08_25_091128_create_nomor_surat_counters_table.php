<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_surat_counters', function (Blueprint $t) {
            $t->id();
            $t->string('kode_surat');      // contoh: TG
            $t->string('unit');            // contoh: UNIKA
            $t->string('bulan_romawi');    // I, II, ..., XII
            $t->integer('tahun');
            $t->unsignedInteger('last_number')->default(0);
            $t->unique(['kode_surat', 'unit', 'bulan_romawi', 'tahun'], 'ux_counter_scope');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_counters');
    }
};
