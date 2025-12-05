<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeputusanHeader extends Model
{
    use SoftDeletes;

    protected $table = 'keputusan_header';

    // ✅ IMPROVED: Fillable fields sesuai struktur DB
    protected $fillable = [
        'nomor',
        'tanggal_surat',
        'kota_penetapan',
        'tahun',
        'signed_at',
        'tentang',
        'judul_penetapan',
        'menimbang',
        'mengingat',
        'menetapkan',
        'memutuskan',
        'signed_pdf_path',
        'tembusan',
        'tembusan_formatted',
        'penerima_eksternal',
        'status_surat',           // ✅ KEY FIX: Ini yang menyebabkan error mass assignment
        'dibuat_oleh',
        'penandatangan',
        'npp_penandatangan',
        'approved_by',
        'approved_at',
        'tanggal_terbit',         // ✅ KEY FIX: Untuk fitur terbitkan
        'terbitkan_oleh',         // ✅ KEY FIX: Untuk fitur terbitkan
        'tanggal_arsip',          // ✅ KEY FIX: Untuk fitur arsipkan
        'arsipkan_oleh',          // ✅ KEY FIX: Untuk fitur arsipkan
        'rejected_by',
        'rejected_at',
        'published_by',
        'published_at',
        'ttd_config',
        'cap_config',
        'ttd_w_mm',
        'cap_w_mm',
        'cap_opacity',
    ];

    // ✅ REMOVED: Guarded tidak perlu karena fillable sudah lengkap
    // protected $guarded = [...];

    protected $casts = [
        'tanggal_surat' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'published_at' => 'datetime',
        'signed_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_terbit' => 'datetime',    // ✅ ADDED
        'tanggal_arsip' => 'datetime',     // ✅ ADDED
        'menimbang' => 'array',
        'mengingat' => 'array',
        'menetapkan' => 'array',
        'penerima_eksternal' => 'array',
        'tembusan' => 'string',
        'ttd_config' => 'array',
        'cap_config' => 'array',
        'ttd_w_mm' => 'integer',
        'cap_w_mm' => 'integer',
        'cap_opacity' => 'float',
        'tahun' => 'integer',
    ];

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ Sanitize nomor
     */
    protected function nomor(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 100)
        );
    }

    /**
     * ✅ Sanitize tentang (subject)
     */
    protected function tentang(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 500)
        );
    }

    /**
     * ✅ Sanitize tembusan
     */
    protected function tembusan(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: function (?string $value) {
                if (empty($value)) {
                    return null;
                }
                $lines = explode("\n", $value);
                $sanitized = array_map(fn($line) => sanitize_input($line, 255), $lines);
                return implode("\n", array_filter($sanitized));
            }
        );
    }

    /**
     * ✅ Sanitize menimbang array
     */
    protected function menimbang(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode(array_map(fn($item) => sanitize_input($item, 1000), $value));
                }
                return $value;
            }
        );
    }

    /**
     * ✅ Sanitize mengingat array
     */
    protected function mengingat(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode(array_map(fn($item) => sanitize_input($item, 1000), $value));
                }
                return $value;
            }
        );
    }

    /**
     * ✅ Sanitize penerima_eksternal array
     */
    protected function penerimaEksternal(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
            set: function ($value) {
                if (is_array($value)) {
                    return json_encode(array_map(fn($nama) => sanitize_input($nama, 255), $value));
                }
                return $value;
            }
        );
    }

    /**
     * ✅ Sanitize kota_penetapan
     */
    protected function kotaPenetapan(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 100)
        );
    }

    /**
     * ✅ Sanitize judul_penetapan
     */
    protected function judulPenetapan(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 500)
        );
    }

    /**
     * ✅ Sanitize NPP penandatangan
     */
    protected function nppPenandatangan(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: fn(?string $value) => sanitize_input($value, 50)
        );
    }

    // ==================== SCOPES =========================

    /**
     * ✅ Scope by status
     */
    public function scopeByStatus($query, string $status)
    {
        $validStatuses = ['draft', 'pending', 'disetujui', 'ditolak', 'terbit', 'arsip'];

        if (!in_array($status, $validStatuses, true)) {
            return $query;
        }

        return $query->where('status_surat', $status);
    }

    /**
     * ✅ Scope by year
     */
    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * ✅ Scope for signed documents
     */
    public function scopeSigned($query)
    {
        return $query->whereNotNull('signed_at')->where('status_surat', 'disetujui');
    }

    /**
     * ✅ Scope for pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status_surat', 'pending');
    }

    /**
     * ✅ Search scope with sanitization
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $keyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);

        return $query->where(function ($q) use ($keyword) {
            $q->where('nomor', 'LIKE', "%{$keyword}%")
              ->orWhere('tentang', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * ✅ Advanced search scope
     */
    public function scopeAdvancedSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $keyword = str_replace(['%', '_'], ['\\%', '\\_'], $keyword);

        return $query->where(function ($q) use ($keyword) {
            $q->where('nomor', 'LIKE', "%{$keyword}%")
                ->orWhere('tentang', 'LIKE', "%{$keyword}%")
                ->orWhereHas('pembuat', function ($q2) use ($keyword) {
                    $q2->where('nama_lengkap', 'LIKE', "%{$keyword}%");
                });
        });
    }

    /**
     * ✅ Filter by tahun
     */
    public function scopeFilterByTahun($query, ?int $tahun)
    {
        if (empty($tahun)) {
            return $query;
        }

        return $query->where('tahun', $tahun);
    }

    /**
     * ✅ Filter by bulan (dari tanggal_surat)
     */
    public function scopeFilterByBulan($query, ?int $bulan)
    {
        if (empty($bulan) || $bulan < 1 || $bulan > 12) {
            return $query;
        }

        return $query->whereMonth('tanggal_surat', $bulan);
    }

    /**
     * ✅ Filter by penandatangan
     */
    public function scopeFilterByPenandatangan($query, ?int $penandatanganId)
    {
        if (empty($penandatanganId)) {
            return $query;
        }

        $validId = validate_integer_id($penandatanganId);
        if ($validId === null) {
            return $query;
        }

        return $query->where('penandatangan', $validId);
    }

    /**
     * ✅ Filter by tanggal range
     */
    public function scopeFilterByTanggalRange($query, ?string $tanggalDari, ?string $tanggalSampai)
    {
        if (!empty($tanggalDari)) {
            $query->where('tanggal_surat', '>=', $tanggalDari);
        }

        if (!empty($tanggalSampai)) {
            $query->where('tanggal_surat', '<=', $tanggalSampai);
        }

        return $query;
    }

    /**
     * ✅ Filter by pembuat (dibuat_oleh)
     */
    public function scopeFilterByPembuat($query, ?int $pembuatId)
    {
        if (empty($pembuatId)) {
            return $query;
        }

        $validId = validate_integer_id($pembuatId);
        if ($validId === null) {
            return $query;
        }

        return $query->where('dibuat_oleh', $validId);
    }

    /**
     * ✅ Apply all filters at once
     */
    public function scopeApplyFilters($query, array $filters)
    {
        return $query
            ->advancedSearch($filters['search'] ?? null)
            ->filterByTahun($filters['tahun'] ?? null)
            ->filterByBulan($filters['bulan'] ?? null)
            ->filterByPenandatangan($filters['penandatangan'] ?? null)
            ->filterByPembuat($filters['pembuat'] ?? null)
            ->filterByTanggalRange($filters['tanggal_dari'] ?? null, $filters['tanggal_sampai'] ?? null);
    }

    // ==================== RELASI =========================

    /**
     * ✅ Relasi ke lampiran
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(KeputusanAttachment::class, 'keputusan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penandatanganUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Relasi ke user yang menerbitkan SK
     */
    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terbitkan_oleh');
    }

    /**
     * Relasi ke user yang mengarsipkan SK
     */
    public function pengarsip(): BelongsTo
    {
        return $this->belongsTo(User::class, 'arsipkan_oleh');
    }

    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'keputusan_penerima', 'keputusan_id', 'pengguna_id')
            ->withPivot(['read_at', 'dibaca'])
            ->withTimestamps();
    }

    // ==================== HELPER METHODS =========================

    /**
     * Cek apakah SK sudah terbit
     */
    public function isTerbit(): bool
    {
        return in_array($this->status_surat, ['terbit', 'arsip'], true);
    }

    /**
     * Cek apakah SK sudah diarsipkan
     */
    public function isArsip(): bool
    {
        return $this->status_surat === 'arsip';
    }

    /**
     * Cek apakah SK bisa diterbitkan
     */
    public function canBeTerbitkan(): bool
    {
        return $this->status_surat === 'disetujui';
    }

    /**
     * Cek apakah SK bisa diarsipkan
     */
    public function canBeArsipkan(): bool
    {
        return $this->status_surat === 'terbit';
    }

    /**
     * ✅ Check if can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status_surat, ['draft', 'ditolak'], true);
    }

    /**
     * ✅ Check if can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->status_surat === 'draft';
    }

    /**
     * ✅ Check if can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status_surat === 'pending';
    }

    /**
     * ✅ Check if is signed
     */
    public function isSigned(): bool
    {
        return !empty($this->signed_at) && $this->status_surat === 'disetujui';
    }

    /**
     * ✅ Get formatted nomor
     */
    public function getFormattedNomorAttribute(): string
    {
        return $this->nomor ?? '(Belum ada nomor)';
    }

    /**
     * ✅ Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_surat) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            'terbit' => 'info',
            'arsip' => 'dark',
            default => 'secondary',
        };
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // ✅ Auto-set dibuat_oleh on create
        static::creating(function ($model) {
            if (empty($model->dibuat_oleh) && auth()->check()) {
                $model->dibuat_oleh = auth()->id();
            }
        });

        // ✅ Auto-set tahun dari tanggal_surat
        static::creating(function ($model) {
            if (!empty($model->tanggal_surat) && empty($model->tahun)) {
                $model->tahun = \Carbon\Carbon::parse($model->tanggal_surat)->year;
            }
        });

        // ✅ Update tahun saat tanggal_surat diubah
        static::updating(function ($model) {
            if ($model->isDirty('tanggal_surat') && !empty($model->tanggal_surat)) {
                $model->tahun = \Carbon\Carbon::parse($model->tanggal_surat)->year;
            }
        });
    }
}
