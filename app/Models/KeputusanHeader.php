<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KeputusanHeader extends Model
{
    use SoftDeletes;

    protected $table = 'keputusan_header';
    protected $guarded = [];

    protected $casts = [
        'tanggal_asli'      => 'date',
        'tanggal_surat'     => 'date',
        'approved_at'       => 'datetime',
        'rejected_at'       => 'datetime',
        'published_at'      => 'datetime',
        'signed_at'         => 'datetime',
        'menimbang'         => 'array',
        'mengingat'         => 'array',
        'menetapkan'        => 'array',
        'penerima_eksternal'=> 'array',   // <-- tambahkan (kolom ada di DB)
        'tembusan'          => 'string',  // disimpan sebagai teks (newline)
        'ttd_config'        => 'array',
        'cap_config'        => 'array',
        // 'memutuskan' tetap string (longtext) -> default cast sudah string
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

    // ===== Relasi penerima internal (pivot: keputusan_penerima) =====
    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'keputusan_penerima', 'keputusan_id', 'pengguna_id')
            ->withPivot(['read_at', 'dibaca'])
            ->withTimestamps();
    }
}
