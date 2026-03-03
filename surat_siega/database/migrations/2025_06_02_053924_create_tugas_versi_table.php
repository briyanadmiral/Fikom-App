<?php

// database/migrations/2025_06_02_053924_create_tugas_versi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_versi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained('tugas_header')->cascadeOnDelete();
            $table->integer('versi');
            $table->boolean('is_final')->default(false);
            $table->json('konten_json');
            $table->foreignId('versi_induk')->nullable()->constrained('tugas_versi');
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_versi');
    }
};
