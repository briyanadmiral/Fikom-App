<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('keputusan_header', 'penerima_eksternal')) {
            Schema::table('keputusan_header', function (Blueprint $table) {
                $table->dropColumn('penerima_eksternal');
            });
        }

        if (Schema::hasTable('keputusan_penerima')) {
            Schema::drop('keputusan_penerima');
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('keputusan_header', 'penerima_eksternal')) {
            Schema::table('keputusan_header', function (Blueprint $table) {
                $table->json('penerima_eksternal')->nullable()->after('tembusan_formatted');
            });
        }

        if (! Schema::hasTable('keputusan_penerima')) {
            Schema::create('keputusan_penerima', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('keputusan_id');
                $table->unsignedBigInteger('pengguna_id');
                $table->timestamp('read_at')->nullable();
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('keputusan_id')
                    ->references('id')
                    ->on('keputusan_header')
                    ->onDelete('cascade');

                $table->foreign('pengguna_id')
                    ->references('id')
                    ->on('pengguna')
                    ->onDelete('cascade');
            });
        }
    }
};
