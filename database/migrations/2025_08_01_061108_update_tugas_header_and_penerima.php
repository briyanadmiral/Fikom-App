<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->string('tugas')->after('jenis_tugas');
            $table->foreignId('detail_tugas_id')
                ->nullable()
                ->after('tugas')
                ->constrained('tugas_detail')
                ->onDelete('set null');
            $table->text('redaksi_pembuka')->nullable()->after('tempat');
            $table->boolean('ijin_tidak_presensi')
                ->default(false)
                ->after('redaksi_pembuka');
        });

        Schema::table('tugas_penerima', function (Blueprint $table) {
            $table->string('posisi')->nullable()->after('pengguna_id');
        });
    }

    public function down()
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->dropColumn(['tugas', 'detail_tugas_id', 'redaksi_pembuka', 'ijin_tidak_presensi']);
        });
        Schema::table('tugas_penerima', function (Blueprint $table) {
            $table->dropColumn('posisi');
        });
    }
};
