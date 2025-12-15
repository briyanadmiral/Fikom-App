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
     * Mass assignment
     */
    protected $fillable = [
        'nomor',
        'nomor_status',
        'kode_surat',
        'bulan',
        'tahun',
        'tanggal_surat',
        'tanggal_asli',
        'status_surat',
        'next_approver',
        'submitted_at',
        'signed_at',
        'signed_pdf_path',
        'dibuat_oleh',
        'nama_pembuat', // masih ada di DB, tapi pelan-pelan dimatikan
        'asal_surat',
        'klasifikasi_surat_id',
        'semester',
        'no_surat_manual',
        'nama_umum',
        'jenis_tugas',
        'tugas',
        'detail_tugas',
        'detail_tugas_id',
        'status_penerima',
        'redaksi_pembuka',
        'penutup',
        'tembusan',
        'waktu_mulai',
        'waktu_selesai',
        'tempat',
        'penandatangan',
        'ttd_config',
        'cap_config',
        'ttd_w_mm',
        'cap_w_mm',
        'cap_opacity',
        'dikunci_pada',
        // Nomor Turunan (Suffix Letter)
        'suffix',
        'parent_tugas_id',
        'nomor_urut_int',
    ];

    /**
     * Extra protection
     */
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Type casting
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
        'parent_tugas_id' => 'integer',
        'nomor_urut_int' => 'integer',

        'cap_opacity' => 'float',

        'kode_surat' => 'string',
        'bulan' => 'string',
        'nomor' => 'string',
        'nomor_status' => 'string',
        'status_surat' => 'string',
        'suffix' => 'string',

        'ttd_config' => 'array',
        'cap_config' => 'array',
    ];

    /**
     * Hide sensitive fields in JSON
     */
    protected $hidden = ['ttd_config', 'cap_config', 'signed_pdf_path'];

    // =========================================================
    // RELATIONS
    // =========================================================

    /**
     * Sumber kebenaran pembuat: kolom dibuat_oleh
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * LEGACY ALIAS:
     * Banyak view lama pakai $tugas->pembuat
     * Sekarang diarahkan ke dibuat_oleh, bukan nama_pembuat.
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * LEGACY ALIAS lagi, tapi tetap ke dibuat_oleh
     */
    public function pembuatUser(): BelongsTo
    {
        return $this->creator();
    }

    public function penandatanganUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    /**
     * Alias supaya di view bisa pakai $tugas->penandatangan
     */
    public function penandatangan(): BelongsTo
    {
        return $this->penandatanganUser();
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

    /**
     * Alias logs() untuk compatibility
     */
    public function logs(): HasMany
    {
        return $this->log();
    }

    public function tugasDetail(): BelongsTo
    {
        return $this->belongsTo(TugasDetail::class, 'detail_tugas_id');
    }

    /**
     * Alias dipakai sebagai detailMaster di controller/view
     */
    public function detailMaster(): BelongsTo
    {
        return $this->tugasDetail();
    }

    public function klasifikasiSurat(): BelongsTo
    {
        return $this->belongsTo(KlasifikasiSurat::class, 'klasifikasi_surat_id');
    }

    /**
     * Alias: show() kadang pakai ->klasifikasi
     */
    public function klasifikasi(): BelongsTo
    {
        return $this->klasifikasiSurat();
    }

    // =========================================================
    // NOMOR TURUNAN (SUFFIX LETTER) RELATIONS
    // =========================================================

    /**
     * Parent surat tugas untuk nomor turunan
     * Contoh: 002A -> parent = 002
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(TugasHeader::class, 'parent_tugas_id');
    }

    /**
     * Children surat tugas (nomor turunan)
     * Contoh: 002 -> children = [002A, 002B]
     */
    public function children(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'parent_tugas_id');
    }

    /**
     * Check if this is a turunan (derivative) surat
     */
    public function isTurunan(): bool
    {
        return !empty($this->suffix) || !empty($this->parent_tugas_id);
    }

    // =========================================================
    // SCOPES
    // =========================================================

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
     * Scope untuk sorting nomor dengan benar (1, 2, 2A, 2B, 3, ... 10, 11)
     * Menghindari leksikal sorting yang salah (1, 10, 11, 2, 2A, ...)
     */
    public function scopeOrderByNomor($query, string $direction = 'asc')
    {
        $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        
        return $query->orderBy('tahun', $dir)
                     ->orderBy('bulan', $dir)
                     ->orderBy('kode_surat', $dir)
                     ->orderBy('nomor_urut_int', $dir)
                     ->orderByRaw("COALESCE(suffix, '') {$dir}");
    }

    /**
     * Scope untuk filter hanya nomor utama (tanpa suffix)
     * Dipakai untuk dropdown parent nomor turunan
     */
    public function scopeOnlyMainNomor($query)
    {
        return $query->whereNull('suffix')
                     ->whereNull('parent_tugas_id');
    }

    /**
     * Filter by pembuat (dibuat_oleh)
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
     * Scope untuk approval queue
     */
    public function scopeNeedsApprovalBy($query, $userId)
    {
        $userId = validate_integer_id($userId);

        if ($userId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('next_approver', $userId)->where('status_surat', 'pending');
    }

    /**
     * Eager load relasi penting
     */
    public function scopeWithFullRelations($query)
    {
        return $query->with([
            // pembuat: alias ke dibuat_oleh
            'pembuat',
            // penerima + user
            'penerima.pengguna',
            // penandatangan dan next approver
            'penandatanganUser',
            'nextApprover',
            // hirarki tugas
            'tugasDetail.subTugas.jenisTugas',
            // klasifikasi
            'klasifikasiSurat',
        ]);
    }

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByBulan($query, string $bulan)
    {
        $bulan = sanitize_input($bulan, 10);
        return $query->where('bulan', $bulan);
    }

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

        /**
     * =========================================================
     * ADVANCE FILTER SCOPE
     * =========================================================
     *
     * Dipakai di TugasController@index untuk apply multiple filters:
     *
     * $filters keys:
     * - search          : string (cari di nomor / nama_umum / tugas)
     * - status          : string (draft/pending/disetujui/ditolak)
     * - tahun           : int
     * - bulan           : string (format sesuai yang kamu simpan di DB)
     * - penandatangan   : user_id penandatangan
     * - pembuat         : user_id pembuat (dibuat_oleh)
     * - tanggal_dari    : date (Y-m-d)
     * - tanggal_sampai  : date (Y-m-d)
     */
    public function scopeApplyFilters($query, array $filters)
    {
        // 🔎 1. Search (pakai scopeSearch yang sudah ada)
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // 📊 2. Status surat (validasi dengan helper kalau ada)
        if (!empty($filters['status'])) {
            // Kalau kamu mau lebih strict, boleh pakai validate_status
            // $status = validate_status($filters['status'], ['draft', 'pending', 'disetujui', 'ditolak']);
            // if ($status !== null) {
            //     $query->where('status_surat', $status);
            // }

            $query->where('status_surat', $filters['status']);
        }

        // 📅 3. Tahun (pakai scopeByTahun)
        if (!empty($filters['tahun'])) {
            $tahun = (int) $filters['tahun'];
            if ($tahun > 0) {
                $query->byTahun($tahun);
            }
        }

        // 📆 4. Bulan (pakai scopeByBulan, DB kamu simpan sebagai string)
        if (!empty($filters['bulan'])) {
            $bulan = (string) $filters['bulan'];
            $query->byBulan($bulan);
        }

        // ✒️ 5. Penandatangan (FK ke pengguna.id)
        if (!empty($filters['penandatangan'])) {
            $penandatanganId = validate_integer_id($filters['penandatangan']);

            if ($penandatanganId !== null) {
                $query->where('penandatangan', $penandatanganId);
            } else {
                // Kalau ID invalid, jangan return data apa-apa
                $query->whereRaw('1 = 0');
            }
        }

        // ✍️ 6. Pembuat (dibuat_oleh) → pakai scopeByUser
        if (!empty($filters['pembuat'])) {
            $pembuatId = validate_integer_id($filters['pembuat']);

            if ($pembuatId !== null) {
                $query->byUser($pembuatId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // 🗓️ 7. Tanggal Dari (tanggal_surat >= X)
        if (!empty($filters['tanggal_dari'])) {
            $query->whereDate('tanggal_surat', '>=', $filters['tanggal_dari']);
        }

        // 🗓️ 8. Tanggal Sampai (tanggal_surat <= Y)
        if (!empty($filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_surat', '<=', $filters['tanggal_sampai']);
        }

        return $query;
    }


    // =========================================================
    // ACCESSORS
    // =========================================================

    /**
     * Tembusan sebagai array yang sudah dibersihkan
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

    // =========================================================
    // MUTATORS
    // =========================================================

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

    protected function tugas(): Attribute
    {
        return Attribute::make(get: fn($value) => sanitize_output($value), set: fn($value) => sanitize_input($value, 500));
    }

    protected function jenisTugas(): Attribute
    {
        return Attribute::make(get: fn($value) => sanitize_output($value), set: fn($value) => sanitize_input($value, 100));
    }

    protected function detailTugas(): Attribute
    {
        return Attribute::make(get: fn($value) => $value, set: fn($value) => sanitize_html_limited($value));
    }

    // =========================================================
    // BUSINESS LOGIC
    // =========================================================

    public function shouldShowSignatures(): bool
    {
        return $this->is_signed === true;
    }

    /**
     * Validasi dan ubah status surat
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

    public function getTanggalFormatted(): string
    {
        $tanggal = $this->tanggal_utama;

        if (!$tanggal) {
            return '-';
        }

        return $tanggal->format('d F Y');
    }

    // =========================================================
    // MODEL EVENTS
    // =========================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->dibuat_oleh) && auth()->check()) {
                $model->dibuat_oleh = auth()->id();
            }

            if (empty($model->status_surat)) {
                $model->status_surat = 'draft';
            }
        });

        static::saving(function ($model) {
            $model->validateBeforeSave();
        });

        static::deleting(function ($model) {
            if ($model->status_surat === 'disetujui') {
                throw new \RuntimeException('Surat tugas yang sudah disetujui tidak dapat dihapus');
            }
        });
    }
}
