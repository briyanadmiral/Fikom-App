<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeputusanVersiTable extends Migration
{
    public function up()
    {
        Schema::create('keputusan_versi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id'); // FK ke keputusan_header
            $table->integer('versi');
            $table->boolean('is_final')->default(false);
            $table->json('konten_json'); // backup isi menimbang, mengingat, memutuskan
            $table->unsignedBigInteger('versi_induk')->nullable(); // jika ingin versi turunan
            $table->timestamp('dibuat_pada')->useCurrent();

            $table->foreign('header_id')->references('id')->on('keputusan_header')->onDelete('cascade');
            $table->foreign('versi_induk')->references('id')->on('keputusan_versi')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('keputusan_versi');
    }
}
