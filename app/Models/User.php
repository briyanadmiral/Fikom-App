<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi secara massal.
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
            'sandi_hash' => 'hashed',
            'last_activity' => 'datetime',
            'peran_id' => 'integer',
            'email_verified_at' => 'datetime',
        ];
    }

    // ==================== AUTHENTICATION ====================

    /**
     * Memberitahu sistem otentikasi Laravel nama kolom password yang digunakan.
     *
     * @return string
     */
    public function getAuthPasswordName(): string
    {
        return 'sandi_hash';
    }

    // ==================== RELATIONSHIPS ====================

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
     * Surat tugas yang dibuat oleh user ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tugasDibuat(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'dibuat_oleh');
    }

    /**
     * Surat tugas yang ditandatangani oleh user ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tugasDitandatangani(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'penandatangan');
    }

    /**
     * Surat tugas yang menunggu approval dari user ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tugasMenungguApproval(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'next_approver');
    }

    // ==================== ROLE CHECKER METHODS ====================

    /**
     * Memeriksa apakah pengguna adalah Admin TU (Role 1).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->peran_id === 1;
    }

    /**
     * Memeriksa apakah pengguna memiliki peran 'Dekan' (Role 2).
     *
     * @return bool
     */
    public function isDekan(): bool
    {
        return $this->peran_id === 2;
    }

    /**
     * Memeriksa apakah pengguna memiliki peran 'Wakil Dekan' (Role 3).
     *
     * @return bool
     */
    public function isWakilDekan(): bool
    {
        return $this->peran_id === 3;
    }

    /**
     * Memeriksa apakah pengguna memiliki wewenang untuk menyetujui surat.
     * (Dekan atau Wakil Dekan)
     *
     * @return bool
     */
    public function canApproveSurat(): bool
    {
        return $this->isDekan() || $this->isWakilDekan();
    }

    /**
     * Alias untuk canApproveSurat() - untuk konsistensi naming.
     *
     * @return bool
     */
    public function isApprover(): bool
    {
        return $this->canApproveSurat();
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Get nama peran user dalam format string.
     *
     * @return string
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->peran_id) {
            1 => 'Admin TU',
            2 => 'Dekan',
            3 => 'Wakil Dekan',
            default => 'Unknown'
        };
    }

    /**
     * Cek apakah user sedang aktif (status = 'active').
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Update last activity timestamp.
     *
     * @return void
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Get notifikasi yang belum dibaca.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function unreadNotifications()
    {
        return $this->notifikasi()
            ->where('dibaca', false)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get count notifikasi yang belum dibaca.
     *
     * @return int
     */
    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->notifikasi()
            ->where('dibaca', false)
            ->count();
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope untuk filter user berdasarkan role.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|array  $roleId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRole($query, $roleId)
    {
        if (is_array($roleId)) {
            return $query->whereIn('peran_id', $roleId);
        }
        return $query->where('peran_id', $roleId);
    }

    /**
     * Scope untuk filter hanya approvers (Dekan dan WD).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApprovers($query)
    {
        return $query->whereIn('peran_id', [2, 3]);
    }

    /**
     * Scope untuk filter user yang aktif.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
