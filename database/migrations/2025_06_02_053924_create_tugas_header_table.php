<?php

// database/migrations/2025_06_02_053924_create_tugas_header_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_header', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->date('tanggal_asli');
            $table->enum('status_surat', ['draft', 'pending', 'disetujui']);
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('pengguna');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('dikunci_pada')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('nomor_status', ['reserved', 'locked']);
            $table->foreignId('nama_pembuat')->constrained('pengguna');
            $table->string('no_bin')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('semester')->nullable();
            $table->string('no_surat_manual')->nullable();
            $table->string('nama_umum')->nullable();
            $table->foreignId('asal_surat')->constrained('peran');
            $table->enum('status_penerima', ['dosen', 'tendik', 'mahasiswa'])->nullable();
            $table->string('jenis_tugas')->nullable();
            $table->text('tugas')->nullable();
            $table->date('waktu_mulai')->nullable();
            $table->date('waktu_selesai')->nullable();
            $table->string('tempat')->nullable();
            $table->string('penutup')->nullable();
            $table->text('tembusan')->nullable();
            $table->foreignId('penandatangan')->constrained('pengguna');
            $table->foreignId('next_approver')->nullable()->constrained('pengguna');
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_header');
    }
};
