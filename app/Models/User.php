<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nama tabel database yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'sandi_hash',
        'nama_lengkap',
        'npp',
        'jabatan',
        'peran_id',
        'status',
        'last_activity',
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'sandi_hash',
        'remember_token',
    ];

    /**
     * Mendefinisikan cast tipe data untuk atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Otomatis melakukan hashing saat atribut ini diisi
            'sandi_hash' => 'hashed',
            'last_activity' => 'datetime',
            'peran_id' => 'integer',
        ];
    }

    /**
     * Memberitahu sistem otentikasi Laravel nama kolom password yang digunakan.
     *
     * @return string
     */
    public function getAuthPasswordName(): string
    {
        return 'sandi_hash';
    }

    /**
     * Mendefinisikan relasi "belongsTo" ke model Peran.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function peran(): BelongsTo
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    /**
     * Mendefinisikan relasi "hasMany" ke model Notifikasi.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'pengguna_id');
    }

    /**
     * Mendefinisikan relasi "hasOne" ke model UserSignature.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function signature(): HasOne
    {
        return $this->hasOne(UserSignature::class, 'pengguna_id');
    }

    /**
     * Memeriksa apakah pengguna memiliki peran 'Dekan'.
     *
     * @return bool
     */
    public function isDekan(): bool
    {
        return $this->peran_id === 2; // Asumsi ID 2 untuk 'dekan'
    }

    /**
     * Memeriksa apakah pengguna memiliki peran 'Wakil Dekan'.
     *
     * @return bool
     */
    public function isWakilDekan(): bool
    {
        return $this->peran_id === 3; // Asumsi ID 3 untuk 'wakil_dekan'
    }

    /**
     * Memeriksa apakah pengguna memiliki wewenang untuk menyetujui surat.
     *
     * @return bool
     */
    public function canApproveSurat(): bool
    {
        return $this->isDekan() || $this->isWakilDekan();
    }
}