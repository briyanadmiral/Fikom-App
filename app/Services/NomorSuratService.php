<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NomorSuratService
{
    /**
     * Reserve nomor dengan kunci: (kode_surat, unit, bulan_romawi, tahun).
     * - Thread-safe: retry singkat saat race.
     * - Format default: 001/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}
     *   Bisa diubah via config('surat_sk.nomor_format', '...').
     */
    public function reserve(string $kodeUnit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): array
    {
        $retries = 3;

        while ($retries-- > 0) {
            try {
                return DB::transaction(function () use ($kodeUnit, $kodeKlasifikasi, $bulanRomawi, $tahun) {
                    // lock baris scope counter
                    $row = DB::table('nomor_surat_counters')
                        ->where('kode_surat', $kodeKlasifikasi)
                        ->where('unit', $kodeUnit)
                        ->where('bulan_romawi', $bulanRomawi)
                        ->where('tahun', $tahun)
                        ->lockForUpdate()
                        ->first();

                    if (!$row) {
                        // inisialisasi scope
                        DB::table('nomor_surat_counters')->insert([
                            'kode_surat'   => $kodeKlasifikasi,
                            'unit'         => $kodeUnit,
                            'bulan_romawi' => $bulanRomawi,
                            'tahun'        => $tahun,
                            'last_number'  => 0,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);

                        $row = DB::table('nomor_surat_counters')
                            ->where('kode_surat', $kodeKlasifikasi)
                            ->where('unit', $kodeUnit)
                            ->where('bulan_romawi', $bulanRomawi)
                            ->where('tahun', $tahun)
                            ->lockForUpdate()
                            ->first();
                    }

                    // next number
                    $nextInt = (int) $row->last_number + 1;

                    DB::table('nomor_surat_counters')
                        ->where('id', $row->id)
                        ->update(['last_number' => $nextInt, 'updated_at' => now()]);

                    // padding 3 digit minimal (kalau sudah >=1000, biarkan natural)
                    $noUrut = $nextInt < 1000 ? str_pad((string)$nextInt, 3, '0', STR_PAD_LEFT) : (string)$nextInt;

                    // === FORMAT NOMOR ===
                    // default (punyamu sekarang):
                    // 001/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}
                    $format = config('surat_sk.nomor_format', '{NO}/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}');

                    // contoh kalau ingin format SK seperti: 001/{KLAS}/SK/UNIKA/{UNIT}/{BULAN}/{TAHUN}
                    // set di config: 'surat_sk.nomor_format' => '{NO}/{KLAS}/SK/UNIKA/{UNIT}/{BULAN}/{TAHUN}'

                    $map = [
                        '{NO}'    => $noUrut,
                        '{KLAS}'  => $kodeKlasifikasi,
                        '{UNIT}'  => $kodeUnit,
                        '{BULAN}' => $bulanRomawi,
                        '{TAHUN}' => (string) $tahun,
                    ];

                    $nomor = strtr($format, $map);

                    return ['no_urut' => $noUrut, 'nomor' => $nomor];
                });
            } catch (\Throwable $e) {
                // deadlock / duplicate key -> retry
                if ($retries <= 0) {
                    throw $e;
                }
                // kecilkan jeda agar cepat namun memberi waktu lock release
                usleep(100 * 1000); // 100 ms
            }
        }

        // Fallback (teoretis tak tercapai)
        return ['no_urut' => '000', 'nomor' => '000/ERR'];
    }
}
