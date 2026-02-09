<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kop_surat', function (Blueprint $t) {
            $t->id();
            $t->string('unit')->nullable(); // opsional: fakultas/prodi
            $t->string('header_path')->nullable();
            $t->string('footer_path')->nullable();
            $t->string('cap_path')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kop_surat');
    }
};
