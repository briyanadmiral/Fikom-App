<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SuratQueueHealth extends Command
{
    protected $signature = 'surat:queue-health';

    protected $description = 'Cek kesehatan antrian surat dan job yang gagal';

    public function handle(): int
    {
        $this->info('Memeriksa antrian surat...');

        $queueTableExists = DB::getSchemaBuilder()->hasTable('jobs');
        $failedTableExists = DB::getSchemaBuilder()->hasTable('failed_jobs');

        if (! $queueTableExists) {
            $this->warn('Tabel jobs tidak ditemukan. Pastikan migrasi queue sudah dijalankan.');

            return self::SUCCESS;
        }

        $pendingJobs = DB::table('jobs')->count();
        $this->line('Job menunggu di antrian: '.$pendingJobs);

        if ($failedTableExists) {
            $failedJobs = DB::table('failed_jobs')->count();
            $this->line('Job gagal di failed_jobs: '.$failedJobs);

            if ($failedJobs > 0) {
                $lastFailed = DB::table('failed_jobs')->orderByDesc('id')->first();

                if ($lastFailed) {
                    $this->warn('Job gagal terakhir:');
                    $this->line('ID: '.$lastFailed->id);
                    $this->line('Connection: '.$lastFailed->connection);
                    $this->line('Queue: '.$lastFailed->queue);
                    $this->line('Gagal pada: '.$lastFailed->failed_at);
                }
            }
        } else {
            $this->warn('Tabel failed_jobs tidak ditemukan. Pertimbangkan untuk mengaktifkannya.');
        }

        $this->info('Pemeriksaan antrian selesai.');

        return self::SUCCESS;
    }
}

