<?php

// database/migrations/2025_06_02_053925_create_tugas_log_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas_header')->cascadeOnDelete();
            $table->string('status_lama')->nullable();
            $table->string('status_baru')->nullable();
            $table->foreignId('user_id')->constrained('pengguna');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_log');
    }
};
