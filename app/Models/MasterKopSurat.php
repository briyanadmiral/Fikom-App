<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADDED (optional)
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

/**
 * Model untuk Master Kop Surat
 * SECURITY: Singleton pattern dengan caching untuk performance
 * ✅ REFACTORED: Menggunakan global helpers untuk DRY code
 */
class MasterKopSurat extends Model
{
    use SoftDeletes; // ✅ ADDED (optional untuk consistency)

    protected $table = 'master_kop_surat';

    /**
     * CRITICAL: Mass Assignment Protection
     * Gunakan $guarded untuk protect ID dan timestamps
     */
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at']; // ✅ ADDED deleted_at

    /**
     * Type casting untuk data integrity
     */
    protected $casts = [
        'tampilkan_logo_kanan' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // ✅ ADDED

        // ✅ ADDED: Type cast for numeric fields
        'logo_size' => 'integer',
        'font_size_title' => 'integer',
        'font_size_text' => 'integer',
        'header_padding' => 'integer',
        'background_opacity' => 'integer',
    ];

    /**
     * Hidden attributes untuk JSON serialization
     * SECURITY: Hide file paths dari public API
     */
    protected $hidden = ['logo_path', 'logo_kanan_path', 'cap_path', 'background_path', 'background_header_path'];

    /**
     * Cache key untuk singleton pattern
     */
    const CACHE_KEY = 'master_kop_surat_instance';
    const CACHE_TTL = 3600; // 1 hour

    // ==================== SINGLETON PATTERN =========================

    /**
     * Get the singleton instance with caching
     * PERFORMANCE: Cache untuk mengurangi database query
     *
     * @return self|null
     */
    public static function getInstance(): ?self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::first();
        });
    }

    /**
     * Clear cache untuk force refresh
     * Call ini setelah update data kop surat
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Override save method untuk auto-clear cache
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $saved = parent::save($options);

        if ($saved) {
            self::clearCache();
        }

        return $saved;
    }

    /**
     * Override delete method untuk auto-clear cache
     *
     * @return bool|null
     */
    public function delete(): ?bool
    {
        $deleted = parent::delete();

        if ($deleted) {
            self::clearCache();
        }

        return $deleted;
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * Get default header data dengan fallback values
     * ✅ REFACTORED: Gunakan sanitize_output() helper
     *
     * @return array
     */
    public function getDefaultHeaderData(): array
    {
        return [
            'nama_fakultas' => sanitize_output($this->nama_fakultas ?? 'FAKULTAS ILMU KOMPUTER'),
            'alamat_lengkap' => sanitize_output($this->alamat_lengkap ?? 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234'),
            'telepon_lengkap' => sanitize_output($this->telepon_lengkap ?? 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265'),
            'email_website' => sanitize_output($this->email_website ?? 'e-mail: unika@unika.ac.id http://www.unika.ac.id/'),
        ];
    }

    /**
     * Accessor untuk nama fakultas dengan sanitasi
     * ✅ GOOD: Already using global helpers
     */
    protected function namaFakultas(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 255));
    }

    /**
     * Accessor untuk alamat lengkap dengan sanitasi
     * ✅ GOOD: Already using global helpers
     */
    protected function alamatLengkap(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 500));
    }

    /**
     * Accessor untuk telepon lengkap dengan sanitasi
     * ✅ GOOD: Already using sanitize_phone() helper
     */
    protected function teleponLengkap(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_phone($value));
    }

    /**
     * Accessor untuk email & website dengan sanitasi
     * ✅ GOOD: Already using global helpers
     */
    protected function emailWebsite(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 255));
    }

    /**
     * ✅ ADDED: Accessor untuk text_color dengan validation
     */
    protected function textColor(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value,
            set: function (?string $value) {
                // Validate hex color format
                if ($value && preg_match('/^#([A-Fa-f0-9]{6})$/', $value)) {
                    return strtoupper($value);
                }
                return '#000000'; // Default black
            },
        );
    }

    // ==================== FILE PATH VALIDATION =========================

    /**
     * Get logo path dengan validasi
     * ✅ GOOD: Already using validate_file_path() helper
     *
     * @return string|null
     */
    public function getValidatedLogoPath(): ?string
    {
        return validate_file_path($this->logo_path);
    }

    /**
     * Get logo kanan path dengan validasi
     * ✅ GOOD: Already using validate_file_path() helper
     *
     * @return string|null
     */
    public function getValidatedLogoKananPath(): ?string
    {
        return validate_file_path($this->logo_kanan_path);
    }

    /**
     * Get cap path dengan validasi
     * ✅ GOOD: Already using validate_file_path() helper
     *
     * @return string|null
     */
    public function getValidatedCapPath(): ?string
    {
        return validate_file_path($this->cap_path);
    }

    /**
     * ✅ ADDED: Get background path dengan validasi
     *
     * @return string|null
     */
    public function getValidatedBackgroundPath(): ?string
    {
        return validate_file_path($this->background_path);
    }

    /**
     * ✅ ADDED: Get background header path dengan validasi
     *
     * @return string|null
     */
    public function getValidatedBackgroundHeaderPath(): ?string
    {
        return validate_file_path($this->background_header_path ?? null);
    }

    // ==================== PUBLIC METHODS =========================

    /**
     * Check apakah logo kanan harus ditampilkan
     *
     * @return bool
     */
    public function shouldShowLogoKanan(): bool
    {
        return $this->tampilkan_logo_kanan === true && !empty($this->logo_kanan_path);
    }

    /**
     * Get all header data dengan validation
     * ✅ GOOD: Semua sanitasi menggunakan helpers
     *
     * @return array
     */
    public function getHeaderDataWithValidation(): array
    {
        return [
            'nama_fakultas' => $this->nama_fakultas,
            'alamat_lengkap' => $this->alamat_lengkap,
            'telepon_lengkap' => $this->telepon_lengkap,
            'email_website' => $this->email_website,
            'logo_path' => $this->getValidatedLogoPath(),
            'logo_kanan_path' => $this->getValidatedLogoKananPath(),
            'cap_path' => $this->getValidatedCapPath(),
            'background_path' => $this->getValidatedBackgroundPath(), // ✅ ADDED
            'background_header_path' => $this->getValidatedBackgroundHeaderPath(), // ✅ ADDED
            'tampilkan_logo_kanan' => $this->shouldShowLogoKanan(),
        ];
    }

    /**
     * ✅ GOOD: Get header data dengan format HTML safe
     * Untuk display di view
     *
     * @return array
     */
    public function getHeaderDataForView(): array
    {
        return [
            'nama_fakultas' => $this->nama_fakultas,
            'alamat_lengkap' => nl2br($this->alamat_lengkap),
            'telepon_lengkap' => $this->telepon_lengkap,
            'email_website' => $this->email_website,
            'has_logo' => !empty($this->getValidatedLogoPath()),
            'has_logo_kanan' => $this->shouldShowLogoKanan(),
            'has_cap' => !empty($this->getValidatedCapPath()),
            'has_background' => !empty($this->getValidatedBackgroundPath()), // ✅ ADDED
            'has_background_header' => !empty($this->getValidatedBackgroundHeaderPath()), // ✅ ADDED
        ];
    }

    /**
     * ✅ ADDED: Get styling configuration dengan validation
     *
     * @return array
     */
    public function getStylingConfig(): array
    {
        return [
            'logo_size' => max(30, min(200, (int) ($this->logo_size ?? 100))),
            'font_size_title' => max(10, min(30, (int) ($this->font_size_title ?? 14))),
            'font_size_text' => max(8, min(20, (int) ($this->font_size_text ?? 10))),
            'text_color' => $this->text_color ?? '#000000',
            'header_padding' => max(0, min(50, (int) ($this->header_padding ?? 15))),
            'background_opacity' => max(0, min(100, (int) ($this->background_opacity ?? 100))),
            'text_align' => in_array($this->text_align, ['left', 'center', 'right'], true) ? $this->text_align : 'right',
        ];
    }

    // ==================== BOOT METHOD =========================

    /**
     * Boot model event listeners
     * Auto-clear cache on model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache saat ada perubahan
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });

        static::updated(function () {
            self::clearCache();
        });

        // ✅ ADDED: Clear cache on restore (if using soft deletes)
        static::restored(function () {
            self::clearCache();
        });
    }
}
