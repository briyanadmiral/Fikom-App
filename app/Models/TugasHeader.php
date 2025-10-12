<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TugasHeader extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'tugas_header';

    protected $fillable = [
    // identitas & nomor surat
    'nomor',
    'nomor_status',
    'kode_surat',
    'bulan',
    'tahun',
    
    // ✅ TAMBAHKAN INI - tanggal surat
    'tanggal_surat',
    'tanggal_asli',
    
    // status & alur persetujuan
    'status_surat',
    'next_approver',
    'submitted_at',
    'signed_at',
    'signed_pdf_path',
    
    // metadata pembuat
    'dibuat_oleh',
    'nama_pembuat',
    'asal_surat',
    
    // klasifikasi
    'klasifikasi_surat_id',
    'semester',
    'no_surat_manual',
    
    // konten tugas
    'nama_umum',
    'jenis_tugas',
    'tugas',
    'detail_tugas',
    'detail_tugas_id',
    'status_penerima',
    'redaksi_pembuka',
    'penutup',
    'tembusan',
    'tembusan_formatted',
    
    // waktu & tempat
    'waktu_mulai',
    'waktu_selesai',
    'tempat',
    
    // tanda tangan
    'penandatangan',
    'ttd_config',
    'cap_config',
    'ttd_w_mm',
    'cap_w_mm',
    'cap_opacity',
];

protected $guarded = ['id'];

protected $casts = [
    'tanggal_asli'        => 'datetime',
    'tanggal_surat'       => 'datetime', // ✅ Sudah ada, bagus!
    'waktu_mulai'         => 'datetime',
    'waktu_selesai'       => 'datetime',
    'submitted_at'        => 'datetime',
    'created_at'          => 'datetime',
    'updated_at'          => 'datetime',
    'signed_at'           => 'datetime',
    'dikunci_pada'        => 'datetime',
    
    // Kolom baru dari migration:
    'kode_surat'          => 'string',
    'bulan'               => 'string',
    'ttd_config'          => 'array',
    'cap_config'          => 'array',
    
    // jika ingin otomatis jadi array: uncomment berikut
    // 'tembusan'         => 'array', // ❌ Lebih baik jangan, karena di service masih pakai string
];


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

    /**
     * Relasi ke TugasDetail (field detail_tugas_id)
     */
    public function tugasDetail(): BelongsTo
    {
        return $this->belongsTo(TugasDetail::class, 'detail_tugas_id');
    }

    // ==================== HELPER =========================

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

    // Tambahkan scope untuk common queries
    public function scopeByUser($query, $userId)
    {
        return $query->where('dibuat_oleh', $userId);
    }

    public function scopeNeedsApprovalBy($query, $userId)
    {
        return $query->where('next_approver', $userId)
            ->where('status_surat', 'pending');
    }

    public function scopeWithFullRelations($query)
    {
        return $query->with(['penerima.pengguna', 'pembuat', 'penandatanganUser', 'nextApprover']);
    }


    /**
     * Change status with validation and proper error handling
     * 
     * @throws \InvalidArgumentException if status transition is invalid
     */
    public function changeStatus(string $newStatus, ?int $nextApprover = null): bool
    {
        // Validate status transition
        $validTransitions = [
            'draft' => ['pending'],
            'pending' => ['disetujui', 'draft'],
            'disetujui' => [], // Final state
        ];

        $currentStatus = $this->status_surat;

        if (
            !isset($validTransitions[$currentStatus]) ||
            !in_array($newStatus, $validTransitions[$currentStatus])
        ) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$currentStatus} to {$newStatus}"
            );
        }

        $old = $this->status_surat;

        try {
            $this->update([
                'status_surat' => $newStatus,
                'next_approver' => $nextApprover,
            ]);

            logStatusChange(null, $this->id, $old, $newStatus);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to change status', [
                'tugas_id' => $this->id,
                'old_status' => $old,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * (Opsional) Jika Anda ingin memecah tembusan menjadi array:
     */
    public function getTembusanArrayAttribute(): array
    {
        if (empty($this->tembusan)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', (string)$this->tembusan))
        ));
    }

    /**
     * Tanggal utama untuk display/sortir:
     * Prioritas tanggal_surat; fallback ke tanggal_asli.
     */
    public function getTanggalUtamaAttribute()
    {
        return $this->tanggal_surat ?: $this->tanggal_asli;
    }

    public function getIsSignedAttribute(): bool
    {
        // Hanya dianggap "sudah tertandatangani" bila benar2 sudah disetujui & ada waktu tanda tangan.
        return $this->status_surat === 'disetujui'
            && !is_null($this->signed_at);
    }

    public function shouldShowSignatures(): bool
    {
        // Satu pintu untuk semua view/partial
        return $this->is_signed === true;
    }
}
