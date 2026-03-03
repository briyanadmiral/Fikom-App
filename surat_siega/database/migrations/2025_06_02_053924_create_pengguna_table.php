<?php

// database/migrations/2025_06_02_053924_create_pengguna_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->unique();
            $table->string('sandi_hash');
            $table->string('nama_lengkap', 100);
            $table->foreignId('peran_id')->constrained('peran')->cascadeOnDelete();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->dateTime('last_activity')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
