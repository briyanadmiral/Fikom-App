<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADDED
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeputusanHeader extends Model
{
    use SoftDeletes; // ✅ ADDED

    protected $table = 'keputusan_header';

    // ✅ IMPROVED: More restrictive fillable
    protected $fillable = [
        'nomor',
        'tanggal_surat',
        'kotapenetapan',
        'tahun',
        'signedat',
        'tentang',
        'judulpenetapan',
        'menimbang',
        'mengingat',
        'menetapkan',
        'memutuskan',
        'signedpdfpath',
        'tembusan',
        'penerima_eksternal',
        'statussurat',
        'dibuatoleh',
        'penandatangan',
        'npppenandatangan',
        'approvedby',
        'approvedat',
        'rejectedby',
        'rejectedat',
        'publishedby',
        'publishedat',
        'ttdconfig',
        'capconfig',
        'ttdwmm',
        'capwmm',
        'capopacity',

        // ✅ TAMBAHKAN 4 BARIS INI
        'tanggal_terbit',
        'terbitkan_oleh',
        'tanggal_arsip',
        'arsipkan_oleh',
    ];

    // ✅ ADDED: Guarded fields for extra protection
    protected $guarded = ['id', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'published_by', 'published_at', 'signed_at', 'signed_pdf_path', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'tanggal_surat' => 'date',
        'approvedat' => 'datetime',
        'rejectedat' => 'datetime',
        'publishedat' => 'datetime',
        'signedat' => 'datetime',
        'deletedat' => 'datetime',
        'menimbang' => 'array',
        'mengingat' => 'array',
        'menetapkan' => 'array',
        'penerima_eksternal' => 'array',
        'tembusan' => 'string',
        'ttdconfig' => 'array',
        'capconfig' => 'array',
        'ttdwmm' => 'integer',
        'capwmm' => 'integer',
        'capopacity' => 'float',
        'tahun' => 'integer',

        // ✅ TAMBAHKAN 2 BARIS INI
        'tanggal_terbit' => 'datetime',
        'tanggal_arsip' => 'datetime',
    ];

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ ADDED: Sanitize nomor
     */
    protected function nomor(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 100));
    }

    /**
     * ✅ ADDED: Sanitize tentang (subject)
     */
    protected function tentang(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 500));
    }

    /**
     * ✅ ADDED: Sanitize tembusan
     */
    protected function tembusan(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => sanitize_output($value),
            set: function (?string $value) {
                if (empty($value)) {
                    return null;
                }
                // Split by newlines, sanitize each line
                $lines = explode("\n", $value);
                $sanitized = array_map(fn($line) => sanitize_input($line, 255), $lines);
                return implode("\n", array_filter($sanitized));
            },
        );
    }

    /**
     * ✅ ADDED: Sanitize menimbang array
     */
    protected function menimbang(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
            set: function ($value) {
                if (is_array($value)) {
                    // Sanitize each item
                    return json_encode(array_map(fn($item) => sanitize_input($item, 1000), $value));
                }
                return $value;
            },
        );
    }

    /**
     * ✅ ADDED: Sanitize mengingat array
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
            },
        );
    }

    /**
     * ✅ ADDED: Sanitize penerima_eksternal array
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
            },
        );
    }

    /**
     * ✅ BARU: Sanitize kota_penetapan
     */
    protected function kotaPenetapan(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 100));
    }

    /**
     * ✅ BARU: Sanitize judul_penetapan
     */
    protected function judulPenetapan(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 500));
    }

    /**
     * ✅ BARU: Sanitize NPP penandatangan
     */
    protected function nppPenandatangan(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 50));
    }

    // ==================== SCOPES =========================

    /**
     * ✅ ADDED: Scope by status
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
     * ✅ ADDED: Scope by year
     */
    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * ✅ ADDED: Scope for signed documents
     */
    public function scopeSigned($query)
    {
        return $query->whereNotNull('signed_at')->where('status_surat', 'disetujui');
    }

    /**
     * ✅ ADDED: Scope for pending approval
     */
    public function scopePending($query)
    {
        return $query->where('status_surat', 'pending');
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
            $q->where('nomor', 'LIKE', "%{$keyword}%")->orWhere('tentang', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * ✅ FASE 1.1: Advanced search scope
     * Mendukung pencarian di nomor, tentang, pembuat
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
     * ✅ FASE 1.1: Filter by tahun
     */
    public function scopeFilterByTahun($query, ?int $tahun)
    {
        if (empty($tahun)) {
            return $query;
        }

        return $query->where('tahun', $tahun);
    }

    /**
     * ✅ FASE 1.1: Filter by bulan (dari tanggal_surat)
     */
    public function scopeFilterByBulan($query, ?int $bulan)
    {
        if (empty($bulan) || $bulan < 1 || $bulan > 12) {
            return $query;
        }

        return $query->whereMonth('tanggal_surat', $bulan);
    }

    /**
     * ✅ FASE 1.1: Filter by penandatangan
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
     * ✅ FASE 1.1: Filter by tanggal range
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
     * ✅ FASE 1.1: Filter by pembuat (dibuat_oleh)
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
     * ✅ FASE 1.1: Apply all filters at once
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
     * ✅ FASE 1.2: Relasi ke lampiran
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
     * ✅ ADDED: Check if can be edited
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status_surat, ['draft', 'ditolak'], true);
    }

    /**
     * ✅ ADDED: Check if can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->status_surat === 'draft';
    }

    /**
     * ✅ ADDED: Check if can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status_surat === 'pending';
    }

    /**
     * ✅ ADDED: Check if is signed
     */
    public function isSigned(): bool
    {
        return !empty($this->signed_at) && $this->status_surat === 'disetujui';
    }

    /**
     * ✅ ADDED: Get formatted nomor
     */
    public function getFormattedNomorAttribute(): string
    {
        return $this->nomor ?? '(Belum ada nomor)';
    }

    /**
     * ✅ ADDED: Get status badge color
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

        // ✅ ADDED: Auto-set dibuat_oleh on create
        static::creating(function ($model) {
            if (empty($model->dibuat_oleh) && auth()->check()) {
                $model->dibuat_oleh = auth()->id();
            }
        });
        // ✅ BARU: Auto-set tahun dari tanggal_surat
        static::creating(function ($model) {
            if (!empty($model->tanggal_surat) && empty($model->tahun)) {
                $model->tahun = \Carbon\Carbon::parse($model->tanggal_surat)->year;
            }
        });

        // ✅ BARU: Update tahun saat tanggal_surat diubah
        static::updating(function ($model) {
            if ($model->isDirty('tanggal_surat') && !empty($model->tanggal_surat)) {
                $model->tahun = \Carbon\Carbon::parse($model->tanggal_surat)->year;
            }
        });
    }
}
