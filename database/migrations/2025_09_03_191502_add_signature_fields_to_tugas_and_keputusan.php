<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Surat Tugas
        Schema::table('tugas_header', function (Blueprint $t) {
            $t->json('ttd_config')->nullable()->after('penandatangan');
            $t->json('cap_config')->nullable()->after('ttd_config');
            $t->timestamp('signed_at')->nullable()->after('submitted_at');
            $t->string('signed_pdf_path')->nullable()->after('file_path');
        });

        // Surat Keputusan (opsional)
        if (Schema::hasTable('keputusan_header')) {
            Schema::table('keputusan_header', function (Blueprint $t) {
                $t->json('ttd_config')->nullable()->after('penandatangan');
                $t->json('cap_config')->nullable()->after('ttd_config');
                $t->timestamp('signed_at')->nullable()->after('tanggal_surat');
                $t->string('signed_pdf_path')->nullable()->after('memutuskan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tugas_header', function (Blueprint $t) {
            $t->dropColumn(['ttd_config', 'cap_config', 'signed_at', 'signed_pdf_path']);
        });

        if (Schema::hasTable('keputusan_header')) {
            Schema::table('keputusan_header', function (Blueprint $t) {
                $t->dropColumn(['ttd_config', 'cap_config', 'signed_at', 'signed_pdf_path']);
            });
        }
    }
};
