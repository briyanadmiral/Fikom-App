<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_tugas_id')->nullable()->after('jenis_tugas_id')->comment('FK ke sub_tugas');
            $table->foreign('sub_tugas_id')->references('id')->on('sub_tugas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_templates', function (Blueprint $table) {
            $table->dropForeign(['sub_tugas_id']);
            $table->dropColumn('sub_tugas_id');
        });
    }
};
