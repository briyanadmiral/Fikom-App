<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeputusanPenerimaTable extends Migration
{
    public function up()
    {
        Schema::create('keputusan_penerima', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('keputusan_id'); // FK ke keputusan_header
            $table->unsignedBigInteger('pengguna_id'); // FK ke pengguna
            $table->boolean('dibaca')->default(false);

            $table->foreign('keputusan_id')->references('id')->on('keputusan_header')->onDelete('cascade');
            $table->foreign('pengguna_id')->references('id')->on('pengguna')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('keputusan_penerima');
    }
}
