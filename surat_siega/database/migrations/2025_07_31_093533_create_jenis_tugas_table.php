<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJenisTugasTable extends Migration
{
    public function up()
    {
        Schema::create('jenis_tugas', function (Blueprint $table) {
            $table->id();                                    // L10
            $table->string('nama')->unique();                // L11
            $table->timestamps();                            // L12
        });
    }

    public function down()
    {
        Schema::dropIfExists('jenis_tugas');               // L19
    }
}
