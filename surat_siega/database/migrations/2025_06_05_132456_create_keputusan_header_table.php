<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeputusanHeaderTable extends Migration
{
    public function up()
    {
        Schema::create('keputusan_header', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->date('tanggal_asli');
            $table->string('tentang'); // judul singkat SK
            $table->json('menimbang'); // array a, b, c...
            $table->json('mengingat'); // array 1, 2, 3...
            $table->longText('memutuskan'); // bisa 1 paragraf atau json jika repeater
            $table->enum('status_surat', ['draft', 'pending', 'disetujui']);
            $table->unsignedBigInteger('dibuat_oleh');
            $table->unsignedBigInteger('penandatangan')->nullable();
            $table->timestamps();

            // Relasi ke pengguna (FK)
            $table->foreign('dibuat_oleh')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('penandatangan')->references('id')->on('pengguna')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('keputusan_header');
    }
}
