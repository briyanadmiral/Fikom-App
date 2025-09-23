<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;  // Import SoftDeletes
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; // Gunakan trait SoftDeletes

    protected $table = 'pengguna';

    // Laravel akan otomatis kelola created_at dan updated_at
    public $timestamps = true;

    /**
     * Kolom yang dapat diisi massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'sandi_hash',
        'nama_lengkap',
        'npp',
        'jabatan',     // Tambahan kolom baru
        'peran_id',
        'status',      // Tambahan kolom baru
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'sandi_hash',
        'remember_token',
    ];

    /**
     * Casting atribut.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity' => 'datetime',
        // 'dibuat_pada' sudah tidak diperlukan, Laravel kelola created_at & updated_at
    ];

    /**
     * Ambil password untuk autentikasi.
     */
    public function getAuthPassword()
    {
        return $this->sandi_hash;
    }

    /**
     * Relasi ke model Peran.
     */
    public function peran()
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    /**
     * Relasi ke model Notifikasi.
     */
    public function notifikasi()
    {
        return $this->hasMany(\App\Models\Notifikasi::class, 'pengguna_id');
    }

    // START PATCH: Relasi TTD
public function signature()
{
    return $this->hasOne(\App\Models\UserSignature::class, 'pengguna_id');
}
// END PATCH


public function isDekan(): bool
{
    return (int) $this->peran_id === 2; // tabel `peran`: id=2 -> dekan
}

public function isWakilDekan(): bool
{
    return (int) $this->peran_id === 3; // tabel `peran`: id=3 -> wakil_dekan
}

public function canApproveSurat(): bool
{
    return $this->isDekan() || $this->isWakilDekan();
}
}
