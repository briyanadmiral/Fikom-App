<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MenimbangLibrary - Library poin "Menimbang" untuk SK
 *
 * Menyimpan poin-poin menimbang yang sering digunakan
 * agar dapat dengan mudah di-insert ke SK baru.
 */
class MenimbangLibrary extends Model
{
    use SoftDeletes;

    protected $table = 'menimbang_library';

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'tags',
        'dibuat_oleh',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
            get: fn (?string $value) => sanitize_output($value),
            set: fn (?string $value) => sanitize_input($value, 200)
        );
    }

    protected function isi(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => sanitize_output($value),
            set: fn (?string $value) => sanitize_input($value, 10000)
        );
    }

    protected function kategori(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => sanitize_output($value),
            set: fn (?string $value) => sanitize_input($value, 50)
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
     * Scope search by judul/isi
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
                ->orWhere('isi', 'LIKE', "%{$escaped}%");
        });
    }

    /**
     * Scope untuk full-text search (lebih efisien untuk data besar)
     */
    public function scopeFulltextSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);

        return $query->whereRaw(
            'MATCH(judul, isi) AGAINST(? IN NATURAL LANGUAGE MODE)',
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
        $isi = $this->isi;

        // Pastikan diawali huruf kecil jika akan digabung
        if (! empty($isi) && ctype_upper($isi[0])) {
            $isi = mb_strtolower(mb_substr($isi, 0, 1)).mb_substr($isi, 1);
        }

        return $isi;
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->judul)) {
                throw new \InvalidArgumentException('Judul menimbang wajib diisi');
            }
            if (empty($model->isi)) {
                throw new \InvalidArgumentException('Isi menimbang wajib diisi');
            }
        });
    }
}
