<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('jenis_tugas_id')->nullable();
            $table->text('detail_tugas');
            $table->text('tembusan')->nullable();
            $table->unsignedBigInteger('dibuat_oleh');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('jenis_tugas_id')
                ->references('id')
                ->on('jenis_tugas')
                ->onDelete('set null');

            $table->foreign('dibuat_oleh')
                ->references('id')
                ->on('pengguna')
                ->onDelete('cascade');

            $table->index('jenis_tugas_id');
            $table->index('dibuat_oleh');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_templates');
    }
};
