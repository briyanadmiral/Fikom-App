<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ✅ REFACTORED: Security enhanced dengan input sanitization
 * ✅ ADDED: Thread-safe counter management dengan deadlock handling
 */
class NomorSuratService
{
    /**
     * Reserve nomor berikutnya untuk scope tertentu (unit+klas+bulan+tahun).
     * ✅ GOOD: Input validation dengan helpers
     *
     * @return array{no_urut:string, nomor:string, scope:array}
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function reserve(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): array
    {
        // ✅ GOOD: Sanitasi input dengan helpers
        $unit = sanitize_alphanumeric(trim($unit), '_-');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));
        $tahun = filter_var($tahun, FILTER_VALIDATE_INT);

        // ✅ GOOD: Validasi input tidak boleh kosong
        if (empty($unit) || empty($klas) || empty($bulan)) {
            throw new \InvalidArgumentException('Unit, kode klasifikasi, dan bulan tidak boleh kosong.');
        }

        // ✅ GOOD: Validasi tahun
        if ($tahun === false || $tahun < 2000 || $tahun > 2100) {
            throw new \InvalidArgumentException('Tahun tidak valid. Harus antara 2000-2100.');
        }

        // ✅ GOOD: Whitelist bulan romawi
        $validMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        if (!in_array($bulan, $validMonths, true)) {
            throw new \InvalidArgumentException("Bulan Romawi tidak valid: {$bulan}");
        }

        // ✅ GOOD: Limit panjang untuk mencegah DB overflow
        if (strlen($unit) > 20 || strlen($klas) > 20 || strlen($bulan) > 10) {
            throw new \InvalidArgumentException('Input terlalu panjang.');
        }

        $scope = [
            'kode_surat' => $klas,
            'unit' => $unit,
            'bulan_romawi' => $bulan,
            'tahun' => $tahun,
        ];

        $counterTable = 'nomor_surat_counters';

        try {
            // ✅ GOOD: Gunakan DB transaction dengan retry logic
            $noUrut = DB::transaction(function () use ($counterTable, $scope) {
                // Lock baris counter untuk scope ini (FOR UPDATE = pessimistic lock)
                $row = DB::table($counterTable)->where($scope)->lockForUpdate()->first();

                if (!$row) {
                    // Inisialisasi scope baru
                    DB::table($counterTable)->insert(
                        array_merge($scope, [
                            'last_number' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]),
                    );

                    // Ambil lagi dan lock
                    $row = DB::table($counterTable)->where($scope)->lockForUpdate()->first();

                    if (!$row) {
                        throw new \RuntimeException('Failed to initialize counter row');
                    }
                }

                // ✅ GOOD: Validasi last_number
                $lastNumber = filter_var($row->last_number, FILTER_VALIDATE_INT);
                if ($lastNumber === false || $lastNumber < 0) {
                    Log::error('Invalid last_number in counter', [
                        'row_id' => $row->id,
                        'last_number' => sanitize_log_message($row->last_number), // ✅ ADDED
                    ]);
                    throw new \RuntimeException('Invalid last_number in database');
                }

                $next = $lastNumber + 1;

                // ✅ GOOD: Safety check: Prevent overflow (max 99999)
                if ($next > 99999) {
                    throw new \RuntimeException('Nomor surat counter overflow. Max 99999.');
                }

                DB::table($counterTable)
                    ->where('id', $row->id)
                    ->update([
                        'last_number' => $next,
                        'updated_at' => now(),
                    ]);

                return $next;
            }, 3); // ✅ GOOD: Retry 3x untuk deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to reserve nomor surat', [
                'scope' => $scope,
                'error' => sanitize_log_message($e->getMessage()), // ✅ GOOD
                'trace' => sanitize_log_message($e->getTraceAsString()), // ✅ ADDED
            ]);
            throw $e;
        }

        // ✅ GOOD: Format nomor dengan validasi config
        $fmt = config('nomor_surat.format', '{NO}/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}');
        $pad = filter_var(config('nomor_surat.zero_pad', 3), FILTER_VALIDATE_INT);

        if ($pad === false || $pad < 1 || $pad > 10) {
            $pad = 3; // Default fallback
        }

        $noStr = str_pad((string) $noUrut, $pad, '0', STR_PAD_LEFT);

        // ✅ GOOD: Sanitasi replacement values
        $nomor = strtr($fmt, [
            '{NO}' => $noStr,
            '{KLAS}' => $klas, // Already sanitized
            '{UNIT}' => $unit, // Already sanitized
            '{BULAN}' => $bulan, // Already sanitized
            '{TAHUN}' => (string) $tahun,
        ]);

        // ✅ GOOD: Log successful reservation
        Log::info('Nomor surat reserved', [
            'nomor' => sanitize_log_message($nomor),
            'no_urut' => $noUrut,
            'scope' => $scope,
        ]);

        return [
            'no_urut' => $noStr,
            'nomor' => $nomor,
            'scope' => $scope,
        ];
    }

    /**
     * ✅ GOOD: Get current counter untuk scope tertentu (read-only)
     *
     * @param string $unit
     * @param string $kodeKlasifikasi
     * @param string $bulanRomawi
     * @param int $tahun
     * @return int
     */
    public function getCurrentCounter(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): int
    {
        $unit = sanitize_alphanumeric(trim($unit), '_-');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));
        $tahun = filter_var($tahun, FILTER_VALIDATE_INT);

        if (empty($unit) || empty($klas) || empty($bulan) || $tahun === false) {
            return 0;
        }

        $row = DB::table('nomor_surat_counters')
            ->where([
                'kode_surat' => $klas,
                'unit' => $unit,
                'bulan_romawi' => $bulan,
                'tahun' => $tahun,
            ])
            ->first();

        return $row ? (int) $row->last_number : 0;
    }

    /**
     * ✅ GOOD: Reset counter untuk testing/admin purposes
     *
     * @param string $unit
     * @param string $kodeKlasifikasi
     * @param string $bulanRomawi
     * @param int $tahun
     * @return bool
     */
    public function resetCounter(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): bool
    {
        $unit = sanitize_alphanumeric(trim($unit), '_-');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));
        $tahun = filter_var($tahun, FILTER_VALIDATE_INT);

        if (empty($unit) || empty($klas) || empty($bulan) || $tahun === false) {
            return false;
        }

        // ✅ ADDED: Validate admin authorization
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            Log::warning('Unauthorized counter reset attempt', [
                'user_id' => auth()->id(),
                'unit' => $unit,
            ]);
            return false;
        }

        $affected = DB::table('nomor_surat_counters')
            ->where([
                'kode_surat' => $klas,
                'unit' => $unit,
                'bulan_romawi' => $bulan,
                'tahun' => $tahun,
            ])
            ->update([
                'last_number' => 0,
                'updated_at' => now(),
            ]);

        Log::warning('Counter reset', [
            'unit' => sanitize_log_message($unit), // ✅ ADDED
            'kode' => sanitize_log_message($klas), // ✅ ADDED
            'bulan' => sanitize_log_message($bulan), // ✅ ADDED
            'tahun' => $tahun,
            'admin' => auth()->id(),
        ]);

        return $affected > 0;
    }

    /**
     * ✅ ADDED: Get all counters untuk reporting
     *
     * @param int|null $tahun
     * @return \Illuminate\Support\Collection
     */
    public function getAllCounters(?int $tahun = null): \Illuminate\Support\Collection
    {
        $query = DB::table('nomor_surat_counters')->select('kode_surat', 'unit', 'bulan_romawi', 'tahun', 'last_number', 'updated_at')->orderByDesc('tahun')->orderBy('unit')->orderBy('bulan_romawi');

        if ($tahun !== null) {
            $validTahun = filter_var($tahun, FILTER_VALIDATE_INT);
            if ($validTahun !== false) {
                $query->where('tahun', $validTahun);
            }
        }

        return $query->get();
    }

    /**
     * ✅ ADDED: Check if counter exists
     *
     * @param string $unit
     * @param string $kodeKlasifikasi
     * @param string $bulanRomawi
     * @param int $tahun
     * @return bool
     */
    public function counterExists(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): bool
    {
        $unit = sanitize_alphanumeric(trim($unit), '_-');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));
        $tahun = filter_var($tahun, FILTER_VALIDATE_INT);

        if (empty($unit) || empty($klas) || empty($bulan) || $tahun === false) {
            return false;
        }

        return DB::table('nomor_surat_counters')
            ->where([
                'kode_surat' => $klas,
                'unit' => $unit,
                'bulan_romawi' => $bulan,
                'tahun' => $tahun,
            ])
            ->exists();
    }

    /**
     * ✅ ADDED: Get next available nomor (preview tanpa reserve)
     *
     * @param string $unit
     * @param string $kodeKlasifikasi
     * @param string $bulanRomawi
     * @param int $tahun
     * @return string
     */
    public function previewNextNomor(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): string
    {
        $currentCounter = $this->getCurrentCounter($unit, $kodeKlasifikasi, $bulanRomawi, $tahun);
        $nextNumber = $currentCounter + 1;

        $fmt = config('nomor_surat.format', '{NO}/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}');
        $pad = filter_var(config('nomor_surat.zero_pad', 3), FILTER_VALIDATE_INT);

        if ($pad === false || $pad < 1 || $pad > 10) {
            $pad = 3;
        }

        $noStr = str_pad((string) $nextNumber, $pad, '0', STR_PAD_LEFT);

        $unit = sanitize_alphanumeric(trim($unit), '_-');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));

        return strtr($fmt, [
            '{NO}' => $noStr,
            '{KLAS}' => $klas,
            '{UNIT}' => $unit,
            '{BULAN}' => $bulan,
            '{TAHUN}' => (string) $tahun,
        ]);
    }
}
