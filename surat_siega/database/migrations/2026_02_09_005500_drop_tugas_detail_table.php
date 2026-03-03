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
        Schema::table('tugas_header', function (Blueprint $table) {
            // Drop foreign key with the specific name found in error
            $table->dropForeign('tugas_header_detail_tugas_id_foreign');
            $table->dropColumn('detail_tugas_id');
        });

        Schema::dropIfExists('tugas_detail');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tugas_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_tugas_id')
                ->constrained('sub_tugas')
                ->onDelete('cascade');
            $table->string('nama');
            $table->timestamps();
        });

        Schema::table('tugas_header', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_tugas_id')->nullable()->after('tugas');
            $table->foreign('detail_tugas_id', 'fk_tugas_header__detail')
                ->references('id')
                ->on('tugas_detail');
        });
    }
};
