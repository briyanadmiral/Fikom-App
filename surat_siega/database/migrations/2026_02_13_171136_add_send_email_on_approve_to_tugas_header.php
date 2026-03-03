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
            $table->boolean('send_email_on_approve')->default(true)->after('next_approver')
                  ->comment('Apakah kirim email ke penerima setelah surat disetujui');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $table) {
            $table->dropColumn('send_email_on_approve');
        });
    }
};
