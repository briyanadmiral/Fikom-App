<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SubTugas - Model untuk sub tugas.
 */
class SubTugas extends Model
{
    use SoftDeletes;

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
     * Scope by jenis tugas dengan validasi ID.
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
     * Scope search dengan sanitasi.
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
     * Scope order by nama.
     */
    public function scopeOrderByNama($query, string $direction = 'asc')
    {
        $direction = validate_sort_direction($direction);

        return $query->orderBy('nama', $direction);
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * Sanitize nama.
     */
    protected function nama(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 255));
    }

    /**
     * Sanitize deskripsi.
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 1000));
    }

    // ==================== BUSINESS LOGIC =========================

    /**
     * Check if can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return true;
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(): bool
    {
        return $this->update([
            'is_active' => ! $this->is_active,
        ]);
    }

    /**
     * Get display name with jenis tugas.
     */
    public function getDisplayNameAttribute(): string
    {
        $jenisNama = $this->jenisTugas?->nama ?? 'Unknown';

        return "{$jenisNama} - {$this->nama}";
    }

    // ==================== STATIC METHODS =========================

    /**
     * Get by jenis tugas ID dengan validasi.
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
     * Get active sub tugas by jenis.
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

        // Validate before saving
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

        // Prevent deletion check removed since detail is gone
        static::deleting(function ($model) {
            // No constraint check needed for detail
        });
    }
}
