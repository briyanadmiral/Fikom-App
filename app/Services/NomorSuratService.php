<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NomorSuratService
{
    /**
     * Reserve nomor berikutnya untuk scope tertentu (unit+klas+bulan+tahun).
     * Operasi ini concurrency-safe dengan transaksi + FOR UPDATE.
     *
     * @return array{no_urut:string, nomor:string, scope:array}
     */
    public function reserve(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): array
    {
        $unit  = trim($unit);
        $klas  = trim($kodeKlasifikasi);
        $bulan = strtoupper(trim($bulanRomawi));
        $tahun = (int) $tahun;

        // Validasi bulan romawi ringan
        $valid = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        if (!in_array($bulan, $valid, true)) {
            throw new \InvalidArgumentException('Bulan Romawi tidak valid.');
        }

        $scope = [
            'kode_surat'   => $klas,
            'unit'         => $unit,
            'bulan_romawi' => $bulan,
            'tahun'        => $tahun,
        ];

        $counterTable = 'nomor_surat_counters';

        $noUrut = DB::transaction(function () use ($counterTable, $scope) {
            // Lock baris counter untuk scope ini (kalau ada)
            $row = DB::table($counterTable)
                ->where($scope)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                // Inisialisasi scope baru (last_number = 0 → akan di-increment)
                DB::table($counterTable)->insert(array_merge($scope, [
                    'last_number' => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]));

                // Ambil lagi dan lock
                $row = DB::table($counterTable)
                    ->where($scope)
                    ->lockForUpdate()
                    ->first();
            }

            $next = ((int)$row->last_number) + 1;

            DB::table($counterTable)
                ->where('id', $row->id)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            return $next;
        });

        // Format nomor sesuai config
        $fmt   = config('nomor_surat.format', '{NO}/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}');
        $pad   = (int) config('nomor_surat.zero_pad', 3);
        $noStr = str_pad((string)$noUrut, max(1, $pad), '0', STR_PAD_LEFT);

        $nomor = strtr($fmt, [
            '{NO}'    => $noStr,
            '{KLAS}'  => $klas,
            '{UNIT}'  => $unit,
            '{BULAN}' => $bulan,
            '{TAHUN}' => (string) $tahun,
        ]);

        return [
            'no_urut' => $noStr,
            'nomor'   => $nomor,
            'scope'   => $scope,
        ];
    }
}
