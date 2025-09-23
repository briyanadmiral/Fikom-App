<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TugasHeader extends Model
{
    protected $table = 'tugas_header';

    // Guarded kosong agar mass assignment fleksibel (controller sudah validasi)
    protected $guarded = [];

    protected $casts = [
        'tanggal_asli'        => 'datetime',
        'tanggal_surat'         => 'date',
        'waktu_mulai'         => 'datetime',
        'waktu_selesai'       => 'datetime',
        'submitted_at'        => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'dikunci_pada'        => 'datetime',
        // Kolom baru dari migration:
        'kode_surat'          => 'string',
        'bulan'               => 'string',
        'ttd_config' => 'array',
        'cap_config' => 'array',

        // jika ingin otomatis jadi array: uncomment berikut
        // 'tembusan'            => 'array',
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

    public function changeStatus(string $newStatus, ?int $nextApprover = null)
    {
        $old = $this->status_surat;
        $this->update([
            'status_surat'  => $newStatus,
            'next_approver' => $nextApprover,
        ]);
        logStatusChange(null, $this->id, $old, $newStatus);
    }

    /**
     * (Opsional) Jika Anda ingin memecah tembusan menjadi array:
     */
    public function getTembusanArrayAttribute(): array
    {
        return $this->tembusan
            ? array_filter(explode(',', (string)$this->tembusan))
            : [];
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
