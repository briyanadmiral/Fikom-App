<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADDED
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class KeputusanHeader extends Model
{
    use SoftDeletes; // ✅ ADDED

    protected $table = 'keputusan_header';

    // ✅ IMPROVED: More restrictive fillable
    protected $fillable = [
        'nomor',
        'tentang',
        'tanggal_surat',
        'penandatangan',
        'dibuat_oleh',
        'approved_by',
        'approved_at',
        'rejected_by', // ✅ ADD THIS
        'rejected_at', // ✅ ADD THIS
        'published_by',
        'published_at',
        'signed_at',
        'signed_pdf_path',
        'menimbang',
        'mengingat',
        'menetapkan',
        'memutuskan',
        'tembusan',
        'penerima_eksternal',
        'ttd_config',
        'cap_config',
        'ttd_w_mm',
        'cap_w_mm',
        'cap_opacity',
        'tahun',
        'status_surat',
    ];

    // ✅ ADDED: Guarded fields for extra protection
    protected $guarded = ['id', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'published_by', 'published_at', 'signed_at', 'signed_pdf_path', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'tanggal_surat' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'published_at' => 'datetime',
        'signed_at' => 'datetime',
        'deleted_at' => 'datetime', // ✅ ADDED
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

    // ==================== RELASI =========================

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

    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'keputusan_penerima', 'keputusan_id', 'pengguna_id')
            ->withPivot(['read_at', 'dibaca'])
            ->withTimestamps();
    }

    // ==================== HELPER METHODS =========================

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
    }
}
