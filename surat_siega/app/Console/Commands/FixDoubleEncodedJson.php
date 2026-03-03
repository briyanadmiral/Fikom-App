<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDoubleEncodedJson extends Command
{
    protected $signature = 'data:fix-double-json {--apply : Commit perubahan ke DB (default: dry-run)}';

    protected $description = 'Perbaiki JSON yang ter-encode dua kali pada tabel keputusan_*';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $targets = [
            ['table' => 'keputusan_header', 'columns' => ['metadata_json', 'lampiran_json', 'isi_json']],
            ['table' => 'keputusan_versi',  'columns' => ['payload_json']],
        ];

        foreach ($targets as $t) {
            $table = $t['table'];
            $columns = $t['columns'];

            if (! DB::getSchemaBuilder()->hasTable($table)) {
                $this->warn("Lewati: tabel {$table} tidak ada.");

                continue;
            }

            $rows = DB::table($table)->select('id', ...$columns)->get();
            $fixed = 0;
            $total = $rows->count();

            foreach ($rows as $row) {
                $updates = [];
                foreach ($columns as $col) {
                    $val = $row->{$col};
                    if ($val === null || $val === '') {
                        continue;
                    }

                    $decoded = json_decode($val, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // bisa jadi string biasa, biarkan
                        continue;
                    }

                    // jika hasil decode adalah string yang terlihat JSON lagi → decode sekali lagi
                    if (is_string($decoded)) {
                        $try = json_decode($decoded, true);
                        if (json_last_error() === JSON_ERROR_NONE && (is_array($try) || is_object($try))) {
                            $updates[$col] = json_encode($try, JSON_UNESCAPED_UNICODE);
                        }
                    }
                }

                if (! empty($updates)) {
                    $fixed++;
                    if ($apply) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            }

            $this->info(sprintf(
                '[%s] kandidat: %d, %s: %d',
                $table, $total, $apply ? 'diperbaiki' : 'terdeteksi',
                $fixed
            ));
        }

        if (! $apply) {
            $this->line('Dry-run selesai. Jalankan lagi dengan --apply untuk commit perubahan.');
        }

        return self::SUCCESS;
    }
}
