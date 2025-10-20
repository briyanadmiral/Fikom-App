<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int         $id
 * @property int         $sub_tugas_id
 * @property string      $nama
 * @property string|null $deskripsi
 * @property int         $urutan
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property-read SubTugas $subTugas
 */
class TugasDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tugas_detail';

    protected $fillable = ['sub_tugas_id', 'nama', 'deskripsi', 'urutan'];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at']; // ✅ ADDED

    protected $casts = [
        'sub_tugas_id' => 'integer',
        'urutan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'urutan' => 0,
    ];

    // ==================== RELASI =========================

    public function subTugas(): BelongsTo
    {
        return $this->belongsTo(SubTugas::class, 'sub_tugas_id');
    }

    public function tugasHeaders(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'detail_tugas_id');
    }

    // ==================== SCOPES =========================

    /**
     * Filter berdasarkan sub_tugas_id dengan validasi
     */
    public function scopeForSub($query, int $subId)
    {
        $subId = validate_integer_id($subId);

        if ($subId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('sub_tugas_id', $subId);
    }

    /**
     * Pencocokan nama sama persis (case-insensitive)
     */
    public function scopeNameEqualsInsensitive($query, string $name)
    {
        $needle = sanitize_input($name, 255);
        $needle = mb_strtolower(trim($needle));
        return $query->whereRaw('LOWER(nama) = ?', [$needle]);
    }

    /**
     * ✅ SIMPLIFIED: Pencarian LIKE dengan escape wildcard
     */
    public function scopeNameLikeInsensitive($query, string $needle)
    {
        $needle = sanitize_input($needle, 100);
        $needle = str_replace(['%', '_'], ['\%', '\_'], $needle);
        $needle = mb_strtolower($needle);

        return $query->whereRaw('LOWER(nama) LIKE ? ESCAPE "\\"', ['%' . $needle . '%']);
    }

    /**
     * Urutan default yang rapi
     */
    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    /**
     * ✅ ADDED: Search scope
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

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ SIMPLIFIED: Use Attribute cast instead of setter
     */
    protected function nama(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 255));
    }

    /**
     * ✅ SIMPLIFIED: Use Attribute cast
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_html_limited($value));
    }

    // ✅ urutan already casted as integer, no need for setter

    // ==================== BUSINESS LOGIC =========================

    /**
     * ✅ ADDED: Check if can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->tugasHeaders()->count() === 0;
    }

    /**
     * ✅ ADDED: Get display name with sub tugas
     */
    public function getDisplayNameAttribute(): string
    {
        $subNama = $this->subTugas?->nama ?? 'Unknown';
        return "{$subNama} - {$this->nama}";
    }

    /**
     * ✅ ADDED: Check if has tugas headers
     */
    public function hasTugasHeaders(): bool
    {
        return $this->tugasHeaders()->count() > 0;
    }

    // ==================== STATIC METHODS =========================

    /**
     * ✅ ADDED: Get by sub tugas ID
     */
    public static function getBySubTugas(int $subId)
    {
        $subId = validate_integer_id($subId);

        if ($subId === null) {
            return collect();
        }

        return self::where('sub_tugas_id', $subId)->orderBy('urutan')->orderBy('nama')->get();
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // ✅ ADDED: Validate before saving
        static::saving(function ($model) {
            if (empty($model->nama)) {
                throw new \InvalidArgumentException('Nama tugas detail wajib diisi');
            }

            if (empty($model->sub_tugas_id)) {
                throw new \InvalidArgumentException('Sub tugas wajib dipilih');
            }

            // Validate sub_tugas_id
            $subId = validate_integer_id($model->sub_tugas_id);
            if ($subId === null) {
                throw new \InvalidArgumentException('ID sub tugas tidak valid');
            }

            // Auto-set urutan if not set
            if ($model->urutan === null || $model->urutan === 0) {
                $maxUrutan = self::where('sub_tugas_id', $model->sub_tugas_id)->max('urutan');
                $model->urutan = ($maxUrutan ?? 0) + 1;
            }
        });

        // ✅ ADDED: Prevent deletion if has tugas headers
        static::deleting(function ($model) {
            if ($model->tugasHeaders()->count() > 0) {
                throw new \RuntimeException('Detail tugas tidak dapat dihapus karena masih digunakan');
            }
        });
    }
}
