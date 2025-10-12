<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KeputusanHeader extends Model
{
    protected $table = 'keputusan_header';

    protected $fillable = [
        'nomor',
        'tahun',
        'semester',
        'bulan',
        'tentang',
        
        // ✅ HANYA tanggal_surat (HAPUS tanggal_asli)
        'tanggal_surat',
        
        'dibuat_oleh',
        'penandatangan',
        'status_surat',
        'menimbang',
        'mengingat',
        'menetapkan',
        'memutuskan',
        'tembusan',
        'penerima_eksternal',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'published_by',
        'published_at',
        'signed_at',
        'ttd_w_mm',
        'cap_w_mm',
        'cap_opacity',
        'ttd_config',
        'cap_config',
    ];

    protected $casts = [
        // ✅ HAPUS 'tanggal_asli'
        'tanggal_surat' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'published_at' => 'datetime',
        'signed_at' => 'datetime',
        'menimbang' => 'array',
        'mengingat' => 'array',
        'menetapkan' => 'array',
        'penerima_eksternal' => 'array',
        'tembusan' => 'string',
        'ttd_config' => 'array',
        'cap_config' => 'array',
    ];

    // ===== Relasi User =====
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

    // ===== Relasi penerima internal =====
    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'keputusan_penerima', 'keputusan_id', 'pengguna_id')
            ->withPivot(['read_at', 'dibaca'])
            ->withTimestamps();
    }
}
