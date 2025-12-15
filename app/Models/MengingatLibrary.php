<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * MengingatLibrary - Library dasar hukum "Mengingat" untuk SK
 * 
 * Menyimpan referensi dasar hukum yang sering digunakan
 * seperti UU, PP, Permen, SK Rektor, dll.
 */
class MengingatLibrary extends Model
{
    use SoftDeletes;

    protected $table = 'mengingat_library';

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'nomor_referensi',
        'tanggal_referensi',
        'dibuat_oleh',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'tanggal_referensi' => 'date',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Kategori dasar hukum yang tersedia
     */
    public const KATEGORI_OPTIONS = [
        'UU' => 'Undang-Undang',
        'PP' => 'Peraturan Pemerintah',
        'Permen' => 'Peraturan Menteri',
        'SK Rektor' => 'SK Rektor',
        'SK Yayasan' => 'SK Yayasan',
        'Peraturan Internal' => 'Peraturan Internal',
        'Lainnya' => 'Lainnya',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke User pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // ==================== ACCESSORS ====================

    protected function judul(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 200)
        );
    }

    protected function isi(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 10000)
        );
    }

    protected function kategori(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 50)
        );
    }

    protected function nomorReferensi(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 100)
        );
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk item aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filter by kategori
     */
    public function scopeByKategori($query, ?string $kategori)
    {
        if (empty($kategori)) {
            return $query;
        }
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope search by judul/isi/nomor
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $escaped = addcslashes($keyword, '%_');

        return $query->where(function ($q) use ($escaped) {
            $q->where('judul', 'LIKE', "%{$escaped}%")
              ->orWhere('isi', 'LIKE', "%{$escaped}%")
              ->orWhere('nomor_referensi', 'LIKE', "%{$escaped}%");
        });
    }

    /**
     * Scope untuk full-text search
     */
    public function scopeFulltextSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        return $query->whereRaw(
            "MATCH(judul, isi, nomor_referensi) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$keyword]
        );
    }

    /**
     * Scope order by usage (paling sering dipakai dulu)
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Scope untuk regulasi (UU, PP, Permen)
     */
    public function scopeRegulasi($query)
    {
        return $query->whereIn('kategori', ['UU', 'PP', 'Permen']);
    }

    /**
     * Scope untuk internal (SK Rektor, Yayasan, Internal)
     */
    public function scopeInternal($query)
    {
        return $query->whereIn('kategori', ['SK Rektor', 'SK Yayasan', 'Peraturan Internal']);
    }

    // ==================== BUSINESS LOGIC ====================

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get available categories
     */
    public static function getCategories(): array
    {
        return self::KATEGORI_OPTIONS;
    }

    /**
     * Get used categories from database
     */
    public static function getUsedCategories(): array
    {
        return self::whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->toArray();
    }

    /**
     * Format isi for display in SK
     */
    public function getFormattedIsi(): string
    {
        return $this->isi;
    }

    /**
     * Get display label with nomor
     */
    public function getDisplayLabel(): string
    {
        $label = $this->judul;
        if (!empty($this->nomor_referensi)) {
            $label .= ' (' . $this->nomor_referensi . ')';
        }
        return $label;
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->judul)) {
                throw new \InvalidArgumentException('Judul mengingat wajib diisi');
            }
            if (empty($model->isi)) {
                throw new \InvalidArgumentException('Isi mengingat wajib diisi');
            }
        });
    }
}
