<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADDED
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KlasifikasiSurat extends Model
{
    use SoftDeletes; // ✅ ADDED

    protected $table = 'klasifikasi_surat';

    protected $fillable = ['kode', 'deskripsi'];

    // ✅ ADDED: Guarded for extra protection
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'kode' => 'string',
        'deskripsi' => 'string',
        'deleted_at' => 'datetime', // ✅ ADDED
    ];

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ ADDED: Sanitize kode using global helper
     */
    protected function kode(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_kode($value, 50));
    }

    /**
     * ✅ ADDED: Sanitize deskripsi using global helper
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 500));
    }

    // ==================== RELASI =========================

    /**
     * Relasi ke Tugas Header
     */
    public function tugasHeaders(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'klasifikasi_surat_id');
    }

    // ==================== STATIC METHODS =========================

    /**
     * Dapatkan kode berikutnya untuk kombinasi prefix & golongan.
     *
     * Contoh:
     *  - prefix=B, golongan=10 → cari B.10.x lalu hasilkan B.10.(max+1) atau B.10.1 jika belum ada.
     */
    public static function getNextCode(string $prefix, int $golongan): string
    {
        // ✅ IMPROVED: Use global helper + custom prefix validation
        $prefix = self::sanitizePrefix($prefix);
        $golongan = max(0, (int) $golongan);

        if ($prefix === '') {
            throw new \InvalidArgumentException('Prefix tidak valid');
        }

        $pattern = $prefix . '.' . $golongan . '.%';

        $driver = DB::getDriverName();
        $lastCode = null;

        if ($driver === 'mysql') {
            // Optimized path for MySQL/MariaDB
            // ✅ SECURE: Pattern already sanitized
            $lastCode = self::where('kode', 'LIKE', $pattern)->orderByRaw('CAST(SUBSTRING_INDEX(kode, ".", -1) AS UNSIGNED) DESC')->value('kode');
        } else {
            // Portable fallback: ambil semua lalu tentukan max suffix di PHP
            $codes = self::where('kode', 'LIKE', $pattern)->pluck('kode')->all();

            $max = 0;
            foreach ($codes as $code) {
                $parts = explode('.', (string) $code);
                $suffix = (int) ($parts[2] ?? 0);
                if ($suffix > $max) {
                    $max = $suffix;
                }
            }
            if ($max > 0) {
                $lastCode = $prefix . '.' . $golongan . '.' . $max;
            }
        }

        if (!$lastCode) {
            return $prefix . '.' . $golongan . '.1';
        }

        $parts = explode('.', $lastCode);
        $subNumber = (int) ($parts[2] ?? 0);
        $subNumber++;

        return $prefix . '.' . $golongan . '.' . $subNumber;
    }

    /**
     * Ambil semua prefix unik (mis. ['A','B','C']).
     */
    public static function getAvailablePrefixes(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            return self::select(DB::raw('SUBSTRING_INDEX(kode, ".", 1) as prefix'))->distinct()->orderBy('prefix')->pluck('prefix')->toArray();
        }

        // Portable: proses di PHP
        $all = self::select('kode')->pluck('kode')->all();
        $prefixes = [];
        foreach ($all as $k) {
            $first = explode('.', (string) $k)[0] ?? null;
            if ($first !== null && $first !== '') {
                $first = self::sanitizePrefix($first);
                if ($first !== '') {
                    $prefixes[$first] = true;
                }
            }
        }
        $out = array_keys($prefixes);
        sort($out, SORT_STRING);
        return $out;
    }

    /**
     * Ambil semua golongan (angka) unik untuk prefix tertentu.
     */
    public static function getAvailableGolongan(string $prefix): array
    {
        $prefix = self::sanitizePrefix($prefix);

        if ($prefix === '') {
            return [];
        }

        $driver = DB::getDriverName();
        $pattern = $prefix . '.%';

        if ($driver === 'mysql') {
            return self::where('kode', 'LIKE', $pattern)->select(DB::raw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(kode, ".", 2), ".", -1) AS UNSIGNED) as golongan'))->distinct()->orderBy('golongan')->pluck('golongan')->toArray();
        }

        // Portable: proses di PHP
        $codes = self::where('kode', 'LIKE', $pattern)->pluck('kode')->all();

        $gol = [];
        foreach ($codes as $code) {
            $parts = explode('.', (string) $code);
            $g = isset($parts[1]) ? (int) $parts[1] : null;
            if ($g !== null) {
                $gol[$g] = true;
            }
        }
        $out = array_keys($gol);
        sort($out, SORT_NUMERIC);
        return $out;
    }

    // ==================== SCOPES =========================

    /**
     * Scope: Filter by prefix
     */
    public function scopeByPrefix($query, string $prefix)
    {
        $prefix = self::sanitizePrefix($prefix);

        if ($prefix === '') {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        return $query->where('kode', 'LIKE', $prefix . '.%');
    }

    /**
     * Scope: Filter by prefix and golongan
     */
    public function scopeByPrefixGolongan($query, string $prefix, int $golongan)
    {
        $prefix = self::sanitizePrefix($prefix);
        $golongan = max(0, (int) $golongan);

        if ($prefix === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('kode', 'LIKE', $prefix . '.' . $golongan . '.%');
    }

    /**
     * ✅ ADDED: Search scope with sanitization
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $keyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);

        return $query->where(function ($q) use ($keyword) {
            $q->where('kode', 'LIKE', "%{$keyword}%")->orWhere('deskripsi', 'LIKE', "%{$keyword}%");
        });
    }

    // ==================== HELPER METHODS =========================

    /**
     * ✅ ADDED: Check if can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->tugasHeaders()->count() === 0;
    }

    /**
     * ✅ ADDED: Get formatted display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->kode . ' - ' . ($this->deskripsi ?? '');
    }

    // ==================== PRIVATE HELPERS =========================

    /**
     * ✅ IMPROVED: Enhanced prefix sanitization
     *
     * Normalisasi & validasi prefix: huruf saja, uppercase, maksimal 10 char.
     */
    private static function sanitizePrefix(string $prefix): string
    {
        // ✅ Use global helper first
        $prefix = sanitize_input($prefix, 10);

        // Remove non-letters
        $prefix = preg_replace('/[^a-zA-Z]/', '', $prefix) ?? '';
        $prefix = strtoupper($prefix);

        if ($prefix === '') {
            Log::warning('KlasifikasiSurat::sanitizePrefix menghasilkan string kosong', [
                'original_input' => substr($prefix, 0, 50),
            ]);
        }

        return $prefix;
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // ✅ ADDED: Validate before saving
        static::saving(function ($model) {
            if (empty($model->kode)) {
                throw new \InvalidArgumentException('Kode klasifikasi wajib diisi');
            }

            // Validate kode format (e.g., A.10.1)
            if (!preg_match('/^[A-Z]\.\d+\.\d+$/', $model->kode)) {
                throw new \InvalidArgumentException('Format kode tidak valid (harus: HURUF.ANGKA.ANGKA)');
            }
        });

        // ✅ ADDED: Prevent deletion if has tugasHeaders
        static::deleting(function ($model) {
            if ($model->tugasHeaders()->count() > 0) {
                throw new \RuntimeException('Klasifikasi tidak dapat dihapus karena masih digunakan');
            }
        });
    }
}
