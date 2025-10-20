<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TugasHeader extends Model
{
    use SoftDeletes;

    protected $table = 'tugas_header';

    /**
     * CRITICAL: $fillable untuk Mass Assignment Protection
     * - tembusan_formatted DIHAPUS (dibangkitkan dari method getTembusanFormatted)
     */
    protected $fillable = ['nomor', 'nomor_status', 'kode_surat', 'bulan', 'tahun', 'tanggal_surat', 'tanggal_asli', 'status_surat', 'next_approver', 'submitted_at', 'signed_at', 'signed_pdf_path', 'dibuat_oleh', 'nama_pembuat', 'asal_surat', 'klasifikasi_surat_id', 'semester', 'no_surat_manual', 'nama_umum', 'jenis_tugas', 'tugas', 'detail_tugas', 'detail_tugas_id', 'status_penerima', 'redaksi_pembuka', 'penutup', 'tembusan', 'waktu_mulai', 'waktu_selesai', 'tempat', 'penandatangan', 'ttd_config', 'cap_config', 'ttd_w_mm', 'cap_w_mm', 'cap_opacity', 'dikunci_pada'];

    /**
     * CRITICAL: $guarded untuk extra protection
     */
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * CRITICAL: Type casting untuk security & data integrity
     */
    protected $casts = [
        'tanggal_asli' => 'datetime',
        'tanggal_surat' => 'datetime',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signed_at' => 'datetime',
        'dikunci_pada' => 'datetime',
        'deleted_at' => 'datetime',

        'dibuat_oleh' => 'integer',
        'nama_pembuat' => 'integer',
        'asal_surat' => 'integer',
        'penandatangan' => 'integer',
        'next_approver' => 'integer',
        'klasifikasi_surat_id' => 'integer',
        'detail_tugas_id' => 'integer',
        'tahun' => 'integer',
        'ttd_w_mm' => 'integer',
        'cap_w_mm' => 'integer',

        'cap_opacity' => 'float',

        'kode_surat' => 'string',
        'bulan' => 'string',
        'nomor' => 'string',
        'nomor_status' => 'string',
        'status_surat' => 'string',

        'ttd_config' => 'array',
        'cap_config' => 'array',
    ];

    /**
     * SECURITY: Hide sensitive fields dari JSON output
     */
    protected $hidden = ['ttd_config', 'cap_config', 'signed_pdf_path'];

    // ==================== RELASI =========================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nama_pembuat');
    }

    public function penandatanganUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    public function asalSurat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asal_surat');
    }

    public function nextApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'next_approver');
    }

    public function penerima(): HasMany
    {
        return $this->hasMany(TugasPenerima::class, 'tugas_id');
    }

    public function log(): HasMany
    {
        return $this->hasMany(TugasLog::class, 'tugas_id');
    }

    // ✅ ALIAS untuk kompatibilitas dengan controller/view baru
    public function logs(): HasMany
    {
        return $this->hasMany(TugasLog::class, 'tugas_id');
    }

    public function tugasDetail(): BelongsTo
    {
        return $this->belongsTo(TugasDetail::class, 'detail_tugas_id');
    }

    // ✅ ALIAS: dipakai sebagai detailMaster di controller/view
    public function detailMaster(): BelongsTo
    {
        return $this->belongsTo(TugasDetail::class, 'detail_tugas_id');
    }

    public function klasifikasiSurat(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'klasifikasi_surat_id');
    }

    // ✅ ALIAS: dipakai oleh show() sebagai 'klasifikasi'
    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'klasifikasi_surat_id');
    }

    public function pembuatUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // ==================== SCOPES =========================

    public function scopeDraft($query)
    {
        return $query->where('status_surat', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status_surat', 'pending');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status_surat', 'disetujui');
    }

    /**
     * ✅ GOOD: Validasi integer dengan helper
     */
    public function scopeByUser($query, $userId)
    {
        $userId = validate_integer_id($userId);

        if ($userId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('dibuat_oleh', $userId);
    }

    /**
     * ✅ GOOD: Scope untuk approval
     */
    public function scopeNeedsApprovalBy($query, $userId)
    {
        $userId = validate_integer_id($userId);

        if ($userId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('next_approver', $userId)->where('status_surat', 'pending');
    }

    public function scopeWithFullRelations($query)
    {
        return $query->with([
            'penerima.pengguna',
            'pembuat',
            'penandatanganUser',
            'nextApprover',
            // tambahan agar hierarki tampil lengkap di show/detail
            'tugasDetail.subTugas.jenisTugas',
            'klasifikasiSurat',
        ]);
    }

    /**
     * ✅ ADDED: Scope by tahun
     */
    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * ✅ ADDED: Scope by bulan
     */
    public function scopeByBulan($query, string $bulan)
    {
        $bulan = sanitize_input($bulan, 10);
        return $query->where('bulan', $bulan);
    }

    /**
     * ✅ ADDED: Scope search
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
                ->orWhere('nama_umum', 'LIKE', "%{$keyword}%")
                ->orWhere('tugas', 'LIKE', "%{$keyword}%");
        });
    }

    // ==================== ACCESSORS =========================

    /**
     * ✅ GOOD: Tembusan array dengan sanitasi
     */
    protected function tembusanArray(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->tembusan)) {
                    return [];
                }

                $items = explode("\n", (string) $this->tembusan);

                $cleaned = array_map(function ($item) {
                    return sanitize_output(trim($item));
                }, $items);

                return array_values(array_filter($cleaned));
            },
        );
    }

    protected function tanggalUtama(): Attribute
    {
        return Attribute::make(get: fn() => $this->tanggal_surat ?: $this->tanggal_asli);
    }

    protected function isSigned(): Attribute
    {
        return Attribute::make(get: fn() => $this->status_surat === 'disetujui' && !is_null($this->signed_at));
    }

    protected function nomorSafe(): Attribute
    {
        return Attribute::make(get: fn() => sanitize_output($this->nomor));
    }

    // ==================== MUTATORS =========================

    /**
     * ✅ GOOD: Mutator dengan sanitasi
     */
    protected function nomor(): Attribute
    {
        return Attribute::make(set: fn($value) => sanitize_input($value, 100));
    }

    protected function tembusan(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (empty($value)) {
                    return null;
                }

                if (is_array($value)) {
                    $value = implode("\n", $value);
                }

                $lines = explode("\n", (string) $value);

                $cleaned = array_map(function ($line) {
                    return sanitize_input(trim($line), 500);
                }, $lines);

                $cleaned = array_filter($cleaned);
                return implode("\n", $cleaned);
            },
        );
    }

    protected function namaUmum(): Attribute
    {
        return Attribute::make(get: fn($value) => sanitize_output($value), set: fn($value) => sanitize_input($value, 255));
    }

    protected function tempat(): Attribute
    {
        return Attribute::make(get: fn($value) => sanitize_output($value), set: fn($value) => sanitize_input($value, 255));
    }

    protected function redaksiPembuka(): Attribute
    {
        return Attribute::make(get: fn($value) => $value, set: fn($value) => sanitize_html_limited($value));
    }

    protected function penutup(): Attribute
    {
        return Attribute::make(get: fn($value) => $value, set: fn($value) => sanitize_html_limited($value));
    }

    /**
     * ✅ ADDED: Mutator untuk tugas
     */
    protected function tugas(): Attribute
    {
        return Attribute::make(get: fn($value) => sanitize_output($value), set: fn($value) => sanitize_input($value, 500));
    }

    /**
     * ✅ ADDED: Mutator untuk jenis_tugas
     */
    protected function jenisTugas(): Attribute
    {
        return Attribute::make(get: fn($value) => sanitize_output($value), set: fn($value) => sanitize_input($value, 100));
    }

    /**
     * ✅ ADDED: Mutator untuk detail_tugas (HTML terbatas)
     * agar aman saat dirender dengan {!! !!} di view.
     */
    protected function detailTugas(): Attribute
    {
        return Attribute::make(get: fn($value) => $value, set: fn($value) => sanitize_html_limited($value));
    }

    // ==================== BUSINESS LOGIC =========================

    public function shouldShowSignatures(): bool
    {
        return $this->is_signed === true;
    }

    /**
     * ✅ GOOD: State machine validation
     */
    public function changeStatus(string $newStatus, ?int $nextApprover = null): bool
    {
        $newStatus = validate_status($newStatus, ['draft', 'pending', 'disetujui', 'ditolak']);

        if ($newStatus === null) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $validTransitions = [
            'draft' => ['pending'],
            'pending' => ['disetujui', 'ditolak', 'draft'],
            'disetujui' => [],
            'ditolak' => ['draft'],
        ];

        $currentStatus = $this->status_surat;

        if (!isset($validTransitions[$currentStatus]) || !in_array($newStatus, $validTransitions[$currentStatus], true)) {
            throw new \InvalidArgumentException("Invalid status transition from {$currentStatus} to {$newStatus}");
        }

        if ($nextApprover !== null) {
            $nextApprover = validate_integer_id($nextApprover);

            if ($nextApprover === null) {
                throw new \InvalidArgumentException('Invalid next_approver ID');
            }
        }

        $old = $this->status_surat;

        try {
            $this->update([
                'status_surat' => $newStatus,
                'next_approver' => $nextApprover,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to change status', [
                'tugas_id' => $this->id,
                'old_status' => $old,
                'new_status' => $newStatus,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            return false;
        }
    }

    public function canBeEdited(): bool
    {
        if ($this->nomor_status === 'locked') {
            return false;
        }

        if ($this->status_surat === 'disetujui') {
            return false;
        }

        return true;
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    public function canBeDeleted(): bool
    {
        return $this->status_surat === 'draft';
    }

    public function lockNomor(): bool
    {
        if ($this->nomor_status === 'locked') {
            return true;
        }

        return $this->update([
            'nomor_status' => 'locked',
            'dikunci_pada' => now(),
        ]);
    }

    public function getNomorForDisplay(): string
    {
        return sanitize_output($this->nomor ?? '[Belum Ada Nomor]');
    }

    public function getTembusanFormatted(): string
    {
        $items = $this->tembusan_array;

        if (empty($items)) {
            return '-';
        }

        $formatted = '<ol>';
        foreach ($items as $item) {
            $formatted .= '<li>' . $item . '</li>';
        }
        $formatted .= '</ol>';

        return $formatted;
    }

    public function validateBeforeSave(): void
    {
        if (in_array($this->status_surat, ['pending', 'disetujui'], true) && empty($this->nomor)) {
            throw new \InvalidArgumentException('Nomor surat wajib diisi untuk status ' . $this->status_surat);
        }

        if ($this->status_surat === 'pending' && empty($this->penandatangan)) {
            throw new \InvalidArgumentException('Penandatangan wajib diisi untuk pengajuan surat');
        }
    }

    /**
     * ✅ ADDED: Get status badge color
     */
    public function getStatusBadgeColor(): string
    {
        return match ($this->status_surat) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * ✅ ADDED: Get formatted tanggal for display
     */
    public function getTanggalFormatted(): string
    {
        $tanggal = $this->tanggal_utama;

        if (!$tanggal) {
            return '-';
        }

        return $tanggal->format('d F Y');
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

            if (empty($model->status_surat)) {
                $model->status_surat = 'draft';
            }
        });

        // ✅ ADDED: Validate before saving
        static::saving(function ($model) {
            $model->validateBeforeSave();
        });

        // ✅ ADDED: Prevent deletion of approved documents
        static::deleting(function ($model) {
            if ($model->status_surat === 'disetujui') {
                throw new \RuntimeException('Surat tugas yang sudah disetujui tidak dapat dihapus');
            }
        });
    }
}
