<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * Model untuk Jenis Tugas.
 */
class JenisTugas extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_tugas';

    protected $fillable = ['nama', 'kode', 'deskripsi', 'is_active'];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const CACHE_KEY_ALL = 'jenis_tugas_all';

    const CACHE_KEY_ACTIVE = 'jenis_tugas_active';

    const CACHE_TTL = 3600;

    // ==================== RELASI =========================

    public function subTugas(): HasMany
    {
        return $this->hasMany(SubTugas::class, 'jenis_tugas_id');
    }

    // ==================== SCOPES =========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Escape LIKE wildcards and search.
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $keyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);

        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'LIKE', "%{$keyword}%")
                ->orWhere('kode', 'LIKE', "%{$keyword}%")
                ->orWhere('deskripsi', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * Order by nama.
     */
    public function scopeOrderByNama($query, string $direction = 'asc')
    {
        $direction = validate_sort_direction($direction);

        return $query->orderBy('nama', $direction);
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * Sanitize nama accessor/mutator.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 255));
    }

    /**
     * Sanitize kode accessor/mutator.
     */
    protected function kode(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_kode($value, 50));
    }

    /**
     * Sanitize deskripsi accessor/mutator.
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 1000));
    }

    // ==================== STATIC METHODS =========================

    public static function getAllActiveCached()
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE, self::CACHE_TTL, function () {
            return self::active()->with('subTugas')->orderBy('nama')->get();
        });
    }

    public static function getAllCached()
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            return self::with('subTugas')->orderBy('nama')->get();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget(self::CACHE_KEY_ACTIVE);
    }

    /**
     * Find by kode with sanitization.
     */
    public static function findByKode(string $kode): ?self
    {
        $kode = sanitize_kode($kode);

        if (empty($kode)) {
            return null;
        }

        return self::where('kode', $kode)->first();
    }

    // ==================== BUSINESS LOGIC =========================

    public function canBeDeleted(): bool
    {
        return $this->subTugas()->count() === 0;
    }

    public function toggleActive(): bool
    {
        return $this->update([
            'is_active' => ! $this->is_active,
        ]);
    }

    public function getActiveSubTugas()
    {
        return $this->subTugas()->where('is_active', true)->orderBy('nama')->get();
    }

    // ==================== VALIDATION HELPERS =========================

    public function validateBeforeSave(): void
    {
        if (empty($this->nama)) {
            throw new \InvalidArgumentException('Nama jenis tugas wajib diisi');
        }

        if (empty($this->kode)) {
            throw new \InvalidArgumentException('Kode jenis tugas wajib diisi');
        }

        if (! preg_match('/^[A-Z0-9_-]+$/', $this->kode)) {
            throw new \InvalidArgumentException('Kode harus terdiri dari huruf kapital, angka, dash, atau underscore');
        }

        // Exclude soft deleted records
        $existing = self::where('kode', $this->kode)
            ->where('id', '!=', $this->id ?? 0)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException("Kode '{$this->kode}' sudah digunakan");
        }
    }

    public function save(array $options = []): bool
    {
        $saved = parent::save($options);

        if ($saved) {
            self::clearCache();
        }

        return $saved;
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });

        static::updated(function () {
            self::clearCache();
        });

        // Clear cache on restore
        static::restored(function () {
            self::clearCache();
        });
    }
}
