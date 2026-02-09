<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $t) {
            $t->string('mode')->default('image'); // 'image' | 'composed'
            $t->string('judul_atas')->nullable();
            $t->string('subjudul')->nullable();
            $t->string('alamat')->nullable();
            $t->string('telepon')->nullable();
            $t->string('fax')->nullable();
            $t->string('email')->nullable();
            $t->string('website')->nullable();
            $t->string('logo_kiri_path')->nullable();
            $t->string('logo_kanan_path')->nullable();
            $t->boolean('tampilkan_logo_kiri')->default(false);
            $t->boolean('tampilkan_logo_kanan')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('master_kop_surat', function (Blueprint $t) {
            $t->dropColumn([
                'mode', 'judul_atas', 'subjudul', 'alamat', 'telepon', 'fax', 'email', 'website',
                'logo_kiri_path', 'logo_kanan_path', 'tampilkan_logo_kiri', 'tampilkan_logo_kanan',
            ]);
        });
    }
};
