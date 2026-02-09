<?php

// database/migrations/2025_06_02_053926_create_agenda_surat_keluar_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas_header')->cascadeOnDelete();
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->text('dikirim_ke');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_surat_keluar');
    }
};
