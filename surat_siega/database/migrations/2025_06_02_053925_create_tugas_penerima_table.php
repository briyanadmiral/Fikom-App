<?php

// database/migrations/2025_06_02_053925_create_tugas_penerima_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_penerima', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas_header')->cascadeOnDelete();
            $table->foreignId('pengguna_id')->constrained('pengguna')->cascadeOnDelete();
            $table->boolean('dibaca')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_penerima');
    }
};
