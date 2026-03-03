<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_signatures', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $t->string('ttd_path'); // storage/app/private/ttd/{userId}.png
            $t->unsignedSmallInteger('default_width_mm')->default(35);
            $t->unsignedSmallInteger('default_height_mm')->default(15);
            $t->timestamps();
            $t->unique('pengguna_id'); // 1 user = 1 TTD
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_signatures');
    }
};
