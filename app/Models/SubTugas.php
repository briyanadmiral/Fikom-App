<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model; // ✅ ADDED
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ✅ REFACTORED: Security enhanced dengan global helpers
 * ✅ ADDED: SoftDeletes untuk data integrity
 */
class SubTugas extends Model
{
    use SoftDeletes; // ✅ ADDED

    protected $table = 'sub_tugas';

    protected $fillable = ['jenis_tugas_id', 'nama', 'deskripsi', 'is_active'];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'jenis_tugas_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== RELASI =========================

    public function jenisTugas(): BelongsTo
    {
        return $this->belongsTo(JenisTugas::class, 'jenis_tugas_id');
    }

    // ==================== SCOPES =========================

    /**
     * ✅ GOOD: Validasi ID dengan helper
     */
    public function scopeByJenis($query, $jenisId)
    {
        $jenisId = validate_integer_id($jenisId);

        if ($jenisId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('jenis_tugas_id', $jenisId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ✅ ADDED: Scope search dengan sanitasi
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $keyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);

        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'LIKE', "%{$keyword}%")->orWhere('deskripsi', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * ✅ ADDED: Scope order by nama
     */
    public function scopeOrderByNama($query, string $direction = 'asc')
    {
        $direction = validate_sort_direction($direction);

        return $query->orderBy('nama', $direction);
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ GOOD: Sanitasi nama
     */
    protected function nama(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 255));
    }

    /**
     * ✅ GOOD: Sanitasi deskripsi
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 1000));
    }

    // ==================== BUSINESS LOGIC =========================

    // ==================== BUSINESS LOGIC =========================

    /**
     * ✅ GOOD: Check if can be deleted
     * Only check for active usage in other tables if necessary, or just allow hard delete if no constraints.
     * Assuming no relationship to check for now since detail is gone.
     */
    public function canBeDeleted(): bool
    {
        return true;
    }

    /**
     * ✅ ADDED: Toggle active status
     */
    public function toggleActive(): bool
    {
        return $this->update([
            'is_active' => ! $this->is_active,
        ]);
    }

    /**
     * ✅ ADDED: Get display name with jenis tugas
     */
    public function getDisplayNameAttribute(): string
    {
        $jenisNama = $this->jenisTugas?->nama ?? 'Unknown';

        return "{$jenisNama} - {$this->nama}";
    }

    // ==================== STATIC METHODS =========================

    /**
     * ✅ ADDED: Get by jenis tugas ID dengan validasi
     */
    public static function getByJenisTugas(int $jenisId)
    {
        $jenisId = validate_integer_id($jenisId);

        if ($jenisId === null) {
            return collect();
        }

        return self::where('jenis_tugas_id', $jenisId)->orderBy('nama')->get();
    }

    /**
     * ✅ ADDED: Get active sub tugas by jenis
     */
    public static function getActiveByJenisTugas(int $jenisId)
    {
        $jenisId = validate_integer_id($jenisId);

        if ($jenisId === null) {
            return collect();
        }

        return self::where('jenis_tugas_id', $jenisId)->where('is_active', true)->orderBy('nama')->get();
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // ✅ ADDED: Validate before saving
        static::saving(function ($model) {
            if (empty($model->nama)) {
                throw new \InvalidArgumentException('Nama sub tugas wajib diisi');
            }

            if (empty($model->jenis_tugas_id)) {
                throw new \InvalidArgumentException('Jenis tugas wajib dipilih');
            }

            // Validate jenis_tugas_id exists
            $jenisId = validate_integer_id($model->jenis_tugas_id);
            if ($jenisId === null) {
                throw new \InvalidArgumentException('ID jenis tugas tidak valid');
            }

            // Check duplicate nama for same jenis_tugas
            $existing = self::where('jenis_tugas_id', $model->jenis_tugas_id)
                ->where('nama', $model->nama)
                ->where('id', '!=', $model->id ?? 0)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                throw new \InvalidArgumentException("Sub tugas '{$model->nama}' sudah ada untuk jenis tugas ini");
            }
        });

        // ✅ ADDED: Prevent deletion check removed since detail is gone
        static::deleting(function ($model) {
            // No constraint check needed for detail
        });
    }
}
