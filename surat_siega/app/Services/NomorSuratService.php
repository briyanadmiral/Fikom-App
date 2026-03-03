<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * NomorSuratService - Security enhanced dengan input sanitization.
 * Thread-safe counter management dengan deadlock handling.
 */
class NomorSuratService
{
    /**
     * Reserve nomor berikutnya untuk scope tertentu (unit+klas+bulan+tahun).
     *
     * @return array{no_urut:string, nomor:string, scope:array}
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function reserve(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): array
    {
        // Sanitasi input dengan helpers
        $unit = sanitize_alphanumeric(trim($unit), '_-.');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));
        $tahun = filter_var($tahun, FILTER_VALIDATE_INT);

        // Validasi input tidak boleh kosong
        if (empty($unit) || empty($klas) || empty($bulan)) {
            throw new \InvalidArgumentException('Unit, kode klasifikasi, dan bulan tidak boleh kosong.');
        }

        // Validasi tahun
        if ($tahun === false || $tahun < 2000 || $tahun > 2100) {
            throw new \InvalidArgumentException('Tahun tidak valid. Harus antara 2000-2100.');
        }

        // Whitelist bulan romawi
        $validMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        if (! in_array($bulan, $validMonths, true)) {
            throw new \InvalidArgumentException("Bulan Romawi tidak valid: {$bulan}");
        }

        // Limit panjang untuk mencegah DB overflow
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
            // Gunakan DB transaction dengan retry logic
            $noUrut = DB::transaction(function () use ($counterTable, $scope) {
                // Lock baris counter untuk scope ini (FOR UPDATE = pessimistic lock)
                $row = DB::table($counterTable)->where($scope)->lockForUpdate()->first();

                if (! $row) {
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

                    if (! $row) {
                        throw new \RuntimeException('Failed to initialize counter row');
                    }
                }

                // Validasi last_number
                $lastNumber = filter_var($row->last_number, FILTER_VALIDATE_INT);
                if ($lastNumber === false || $lastNumber < 0) {
                    Log::error('Invalid last_number in counter', [
                        'row_id' => $row->id,
                        'last_number' => sanitize_log_message($row->last_number),
                    ]);
                    throw new \RuntimeException('Invalid last_number in database');
                }

                $next = $lastNumber + 1;

                // Safety check: Prevent overflow (max 99999)
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
            }, 3); // Retry 3x untuk deadlock handling
        } catch (\Exception $e) {
            Log::error('Failed to reserve nomor surat', [
                'scope' => $scope,
                'error' => sanitize_log_message($e->getMessage()),
                'trace' => sanitize_log_message($e->getTraceAsString()),
            ]);
            throw $e;
        }

        // Format nomor dengan validasi config
        $fmt = config('nomor_surat.format', '{NO}/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}');
        $pad = $this->getZeroPad();

        $noStr = str_pad((string) $noUrut, $pad, '0', STR_PAD_LEFT);

        // Sanitasi replacement values
        $nomor = strtr($fmt, [
            '{NO}' => $noStr,
            '{KLAS}' => $klas, // Already sanitized
            '{UNIT}' => $unit, // Already sanitized
            '{BULAN}' => $bulan, // Already sanitized
            '{TAHUN}' => (string) $tahun,
        ]);

        // Log successful reservation
        Log::info('Nomor surat reserved', [
            'nomor' => sanitize_log_message($nomor),
            'no_urut' => $noUrut,
            'scope' => $scope,
        ]);

        return [
            'no_urut' => $noStr,
            'nomor' => $nomor,
            'nomor_urut_int' => $noUrut,
            'scope' => $scope,
        ];
    }

    /**
     * Get current counter untuk scope tertentu (read-only).
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
     * Reset counter untuk testing/admin purposes.
     * @param bool $skipAuthCheck Set true when calling from CLI/artisan commands
     */
    public function resetCounter(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun, bool $skipAuthCheck = false): bool
    {
        $unit = sanitize_alphanumeric(trim($unit), '_-');
        $klas = sanitize_kode(trim($kodeKlasifikasi));
        $bulan = sanitize_alphanumeric(strtoupper(trim($bulanRomawi)));
        $tahun = filter_var($tahun, FILTER_VALIDATE_INT);

        if (empty($unit) || empty($klas) || empty($bulan) || $tahun === false) {
            return false;
        }

        // Validate admin authorization (skip for CLI/artisan)
        if (! $skipAuthCheck) {
            if (! auth()->check() || ! auth()->user()->isAdmin()) {
                Log::warning('Unauthorized counter reset attempt', [
                    'user_id' => auth()->id(),
                    'unit' => $unit,
                ]);

                return false;
            }
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
            'unit' => sanitize_log_message($unit),
            'kode' => sanitize_log_message($klas),
            'bulan' => sanitize_log_message($bulan),
            'tahun' => $tahun,
            'admin' => auth()->id() ?? 'cli',
        ]);

        return $affected > 0;
    }

    /**
     * Get all counters untuk reporting.
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
     * Check if counter exists.
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
     * Get next available nomor (preview tanpa reserve).
     */
    public function previewNextNomor(string $unit, string $kodeKlasifikasi, string $bulanRomawi, int $tahun): string
    {
        $currentCounter = $this->getCurrentCounter($unit, $kodeKlasifikasi, $bulanRomawi, $tahun);
        $nextNumber = $currentCounter + 1;

        $fmt = config('nomor_surat.format', '{NO}/{KLAS}/{UNIT}/UNIKA/{BULAN}/{TAHUN}');
        $pad = $this->getZeroPad();

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

    // =========================================================
    // NOMOR TURUNAN (SUFFIX LETTER) METHODS
    // =========================================================

    /**
     * Get next available suffix for a parent tugas.
     * MUST be called inside a transaction with lockForUpdate on children
     * to prevent race conditions. Use reserveSuffix() instead for safe usage.
     *
     * @param  int  $parentTugasId  ID surat induk
     * @param  bool $locked         True jika sudah dalam transaction context
     * @return string Next suffix letter (A-Z)
     *
     * @throws \RuntimeException if max suffix (Z) exceeded
     */
    public function getNextSuffix(int $parentTugasId, bool $locked = false): string
    {
        // Get existing suffixes for this parent
        $query = \App\Models\TugasHeader::where('parent_tugas_id', $parentTugasId)
            ->whereNotNull('suffix');

        // Jika dalam transaction, lock rows untuk mencegah race condition
        if ($locked) {
            $query->lockForUpdate();
        }

        $existingSuffixes = $query
            ->pluck('suffix')
            ->map(fn ($s) => strtoupper($s))
            ->toArray();

        // Generate next letter (A-Z)
        $alphabet = range('A', 'Z');

        foreach ($alphabet as $letter) {
            if (! in_array($letter, $existingSuffixes, true)) {
                return $letter;
            }
        }

        throw new \RuntimeException('Maksimum suffix (Z) telah tercapai untuk nomor ini.');
    }

    /**
     * Build nomor turunan dari parent nomor + suffix.
     * Helper method untuk menghindari duplikasi logic.
     *
     * @param  string  $parentNomor  Nomor surat induk (e.g. "002/A.3.1/ST.IKOM/UNIKA/XII/2025")
     * @param  string  $suffix       Suffix letter (e.g. "A")
     * @return string  Nomor turunan (e.g. "002A/A.3.1/ST.IKOM/UNIKA/XII/2025")
     */
    private function buildSuffixNomor(string $parentNomor, string $suffix): string
    {
        $parts = explode('/', $parentNomor);

        if (count($parts) >= 1) {
            $parts[0] = $parts[0] . $suffix;
        }

        return implode('/', $parts);
    }

    /**
     * Extract nomor urut integer dari nomor string.
     * "001/A.4/TG/UNIKA/II/2026" → 1
     */
    private function extractNomorUrutInt(string $nomor): int
    {
        $parts = explode('/', $nomor);

        return (int) preg_replace('/\D/', '', $parts[0] ?? '0');
    }

    /**
     * Preview suffix nomor tanpa reserve (read-only).
     * Aman dipanggil tanpa transaction — hanya untuk display.
     *
     * @param  int          $parentTugasId  ID surat induk
     * @param  string|null  $suffix         Suffix spesifik (opsional, jika sudah diketahui)
     * @return string Preview nomor lengkap dengan suffix
     */
    public function previewSuffixNomor(int $parentTugasId, ?string $suffix = null): string
    {
        $parent = \App\Models\TugasHeader::find($parentTugasId);

        if (! $parent) {
            return '[Parent tidak ditemukan]';
        }

        // Jika suffix tidak diberikan, cari yang berikutnya (read-only, tanpa lock)
        if ($suffix === null) {
            $suffix = $this->getNextSuffix($parentTugasId, false);
        }

        return $this->buildSuffixNomor($parent->nomor, $suffix);
    }

    /**
     * Reserve suffix nomor untuk turunan.
     * Thread-safe: menggunakan DB transaction + lockForUpdate.
     *
     * @param  int  $parentTugasId  ID surat induk
     * @return array{suffix: string, nomor: string, parent_id: int, nomor_urut_int: int}
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function reserveSuffix(int $parentTugasId): array
    {
        $parentTugasId = filter_var($parentTugasId, FILTER_VALIDATE_INT);
        if ($parentTugasId === false || $parentTugasId < 1) {
            throw new \InvalidArgumentException('Parent ID tidak valid.');
        }

        try {
            return DB::transaction(function () use ($parentTugasId) {
                // Lock parent row to prevent concurrent modifications
                $parent = \App\Models\TugasHeader::lockForUpdate()->find($parentTugasId);

                if (! $parent) {
                    throw new \InvalidArgumentException('Parent tugas tidak ditemukan.');
                }

                if ($parent->suffix !== null || $parent->parent_tugas_id !== null) {
                    throw new \InvalidArgumentException('Tidak bisa membuat turunan dari nomor yang sudah turunan.');
                }

                if (empty($parent->nomor) || str_starts_with($parent->nomor, 'DRAFT-')) {
                    throw new \InvalidArgumentException('Parent belum memiliki nomor surat yang valid.');
                }

                // Get next suffix with lock on children rows
                $suffix = $this->getNextSuffix($parentTugasId, true);

                // Build nomor turunan (single call, no double query)
                $nomor = $this->buildSuffixNomor($parent->nomor, $suffix);

                // Extract nomor_urut_int from parent
                $nomorUrutInt = $this->extractNomorUrutInt($parent->nomor);

                Log::info('Suffix nomor reserved', [
                    'parent_id' => $parentTugasId,
                    'parent_nomor' => sanitize_log_message($parent->nomor),
                    'suffix' => $suffix,
                    'new_nomor' => sanitize_log_message($nomor),
                ]);

                return [
                    'suffix' => $suffix,
                    'nomor' => $nomor,
                    'parent_id' => $parentTugasId,
                    'nomor_urut_int' => $nomorUrutInt,
                ];
            }, 3); // Retry 3x untuk deadlock handling
        } catch (\InvalidArgumentException $e) {
            throw $e; // Re-throw validation errors without wrapping
        } catch (\Exception $e) {
            Log::error('Failed to reserve suffix nomor', [
                'parent_id' => $parentTugasId,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            throw $e;
        }
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * Get zero-pad setting with validation.
     */
    private function getZeroPad(): int
    {
        $pad = filter_var(config('nomor_surat.zero_pad', 3), FILTER_VALIDATE_INT);

        if ($pad === false || $pad < 1 || $pad > 10) {
            return 3;
        }

        return $pad;
    }

    /**
     * Convert integer ke Romawi (1-3999)
     */
    public function toRoman(int $number): string
    {
        if ($number <= 0 || $number > 3999) {
            return '';
        }

        $map = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
        ];

        $ret = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $ret .= $roman;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Konversi nilai kolom `bulan` (bisa romawi / angka) jadi label yang enak dibaca.
     * Contoh: "I" -> "Januari (I)", "03" -> "Maret (03)"
     */
    public function getBulanLabel(string $bulan): string
    {
        $romanMap = [
            'I' => 'Januari', 'II' => 'Februari', 'III' => 'Maret', 'IV' => 'April',
            'V' => 'Mei', 'VI' => 'Juni', 'VII' => 'Juli', 'VIII' => 'Agustus',
            'IX' => 'September', 'X' => 'Oktober', 'XI' => 'November', 'XII' => 'Desember',
        ];

        $upper = strtoupper(trim($bulan));
        if (isset($romanMap[$upper])) {
            return $romanMap[$upper].' ('.$upper.')';
        }

        // Kalau angka
        $int = (int) $bulan;
        if ($int >= 1 && $int <= 12) {
            $nama = array_values($romanMap)[$int - 1] ?? $bulan;

            return $nama.' ('.$bulan.')';
        }

        return $bulan;
    }
}
