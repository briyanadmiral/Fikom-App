<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeSuratAndBulanToTugasHeader extends Migration
{
    public function up()
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->string('kode_surat')->nullable();
            $table->string('bulan')->nullable();
        });
    }

    public function down()
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->dropColumn(['kode_surat', 'bulan']);
        });
    }
}
