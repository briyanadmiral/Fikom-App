<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nama tabel model.
     */
    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi mass-assignment.
     */
    protected $fillable = ['email', 'sandi_hash', 'nama_lengkap', 'npp', 'jabatan', 'peran_id', 'status', 'last_activity', 'foto_path'];

    /**
     * Guarded protection.
     */
    protected $guarded = ['id', 'remember_token', 'email_verified_at', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     */
    protected $hidden = [
        'sandi_hash',
        'password',
        'remember_token',
    ];

    /**
     * Cast tipe data untuk atribut.
     */
    protected function casts(): array
    {
        return [
            'sandi_hash' => 'hashed',
            'last_activity' => 'datetime',
            'peran_id' => 'integer',
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    /**
     * Pastikan guard menggunakan kolom sandi_hash.
     */
    public function getAuthPasswordName(): string
    {
        return 'sandi_hash';
    }

    /**
     * Kompatibilitas penuh dengan Laravel guard.
     */
    public function getAuthPassword()
    {
        return $this->sandi_hash;
    }

    /**
     * Accessor agar $user->password tersedia.
     */
    public function getPasswordAttribute(): ?string
    {
        return $this->sandi_hash;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function peran(): BelongsTo
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'pengguna_id');
    }

    public function signature(): HasOne
    {
        return $this->hasOne(UserSignature::class, 'pengguna_id');
    }

    public function tugasDibuat(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'dibuat_oleh');
    }

    public function tugasDitandatangani(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'penandatangan');
    }

    public function tugasMenungguApproval(): HasMany
    {
        return $this->hasMany(TugasHeader::class, 'next_approver');
    }

    /**
     * Surat keputusan yang dibuat.
     */
    public function keputusanDibuat(): HasMany
    {
        return $this->hasMany(KeputusanHeader::class, 'dibuat_oleh');
    }

    /**
     * Surat keputusan yang ditandatangani.
     */
    public function keputusanDitandatangani(): HasMany
    {
        return $this->hasMany(KeputusanHeader::class, 'penandatangan');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE CHECKERS
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return (int) $this->peran_id === 1;
    }

    public function isDekan(): bool
    {
        return (int) $this->peran_id === 2;
    }

    public function isWakilDekan(): bool
    {
        return (int) $this->peran_id === 3;
    }

    public function canApproveSurat(): bool
    {
        return $this->isDekan() || $this->isWakilDekan();
    }

    public function isApprover(): bool
    {
        return $this->canApproveSurat();
    }

    /**
     * Check if user can create surat.
     */
    public function canCreateSurat(): bool
    {
        return $this->isActive() && in_array($this->peran_id, [1, 2, 3], true);
    }

    /*
    |--------------------------------------------------------------------------
    | UTILITY / ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Accessor untuk backward compatibility.
     * Jika ada code lama yang akses $user->nama, redirect ke nama_lengkap.
     */
    public function getNamaAttribute(): ?string
    {
        return $this->nama_lengkap;
    }

    /**
     * AdminLTE compatibility: Accessor for 'name' attribute.
     */
    public function getNameAttribute(): ?string
    {
        return $this->nama_lengkap;
    }

    /**
     * Nama peran dari relasi jika tersedia.
     */
    public function getRoleNameAttribute(): string
    {
        if ($this->peran) {
            return $this->peran->nama;
        }

        return match ((int) $this->peran_id) {
            1 => 'Admin TU',
            2 => 'Dekan',
            3 => 'Wakil Dekan',
            default => 'Unknown',
        };
    }

    public function isActive(): bool
    {
        return mb_strtolower((string) $this->status) === 'aktif';
    }

    public function updateLastActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    /**
     * Get unread notifications relationship
     */
    public function unreadNotifications()
    {
        return $this->notifikasi()->where('dibaca', false)->orderByDesc('dibuat_pada');
    }

    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->notifikasi()->where('dibaca', false)->count();
    }

    /**
     * Inisial untuk avatar.
     */
    public function getInitialsAttribute(): string
    {
        $name = (string) ($this->nama_lengkap ?? '');

        if (function_exists('get_initials')) {
            return get_initials($name);
        }

        $parts = preg_split('/\s+/', trim(preg_replace('/[^a-zA-Z\s]/', '', $name))) ?: [];
        $init = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $init .= strtoupper(substr($p, 0, 1));
        }

        return $init ?: 'U';
    }

    /**
     * Warna avatar deterministik.
     */
    public function getAvatarColorAttribute(): string
    {
        $name = (string) ($this->nama_lengkap ?? '');

        if (function_exists('generate_color_from_string')) {
            return generate_color_from_string($name);
        }

        return '#cccccc';
    }

    /**
     * Accessor untuk mendapatkan URL foto profile.
     * Fallback ke UI Avatars jika tidak ada foto.
     */
    public function getFotoUrlAttribute(): string
    {
        // Jika ada foto_path dan file exists
        if ($this->foto_path && Storage::disk('public')->exists($this->foto_path)) {
            return asset('storage/'.$this->foto_path);
        }

        // Fallback ke UI Avatars dengan warna dari avatar_color
        $color = ltrim($this->avatar_color, '#');

        return 'https://ui-avatars.com/api/?name='.urlencode($this->nama_lengkap ?? 'U')
             .'&background='.$color.'&color=fff&size=128';
    }

    /**
     * Get display name (safe).
     */
    public function getDisplayNameAttribute(): string
    {
        return sanitize_output($this->nama_lengkap ?? 'Unknown User');
    }

    /**
     * Get formatted NPP.
     */
    public function getFormattedNppAttribute(): ?string
    {
        if (empty($this->npp)) {
            return null;
        }

        return sanitize_output($this->npp);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeByRole($query, $roleId)
    {
        if (is_array($roleId)) {
            // Validate each ID
            $validIds = array_filter(array_map('validate_integer_id', $roleId));

            return $query->whereIn('peran_id', $validIds);
        }

        $roleId = validate_integer_id($roleId);
        if ($roleId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('peran_id', $roleId);
    }

    public function scopeApprovers($query)
    {
        return $query->whereIn('peran_id', [2, 3]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope pencarian aman dengan LIKE escape.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $needle = sanitize_input($term, 100);

        if ($needle === '' || $needle === null) {
            return $query;
        }

        // Escape LIKE wildcards
        $escaped = str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($needle));

        return $query->where(function ($q) use ($escaped) {
            $q->whereRaw('LOWER(nama_lengkap) LIKE ? ESCAPE "\\"', ['%'.$escaped.'%'])
                ->orWhereRaw('LOWER(email) LIKE ? ESCAPE "\\"', ['%'.$escaped.'%'])
                ->orWhereRaw('LOWER(npp) LIKE ? ESCAPE "\\"', ['%'.$escaped.'%']);
        });
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        $status = sanitize_input($status, 20);

        return $query->where('status', $status);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Mutator email dengan sanitasi.
     */
    public function setEmailAttribute($value): void
    {
        $val = is_string($value) ? strtolower(trim($value)) : null;

        if (function_exists('sanitize_email')) {
            $val = sanitize_email($val);
        }

        $this->attributes['email'] = $val ?? '';
    }

    public function setNamaLengkapAttribute($value): void
    {
        if (function_exists('sanitize_input')) {
            $this->attributes['nama_lengkap'] = sanitize_input((string) $value, 255) ?? '';

            return;
        }

        $clean = strip_tags((string) $value);
        $clean = trim($clean);
        $this->attributes['nama_lengkap'] = mb_substr($clean, 0, 255);
    }

    public function setJabatanAttribute($value): void
    {
        if (function_exists('sanitize_input')) {
            $this->attributes['jabatan'] = sanitize_input((string) $value, 255) ?? null;

            return;
        }

        $clean = strip_tags((string) $value);
        $clean = trim($clean);
        $this->attributes['jabatan'] = $clean !== '' ? mb_substr($clean, 0, 255) : null;
    }

    public function setNppAttribute($value): void
    {
        if (function_exists('sanitize_alphanumeric')) {
            $this->attributes['npp'] = sanitize_alphanumeric((string) $value, '/\-\.') ?? null;

            return;
        }

        $clean = preg_replace('/[^a-zA-Z0-9\/\-\.]/', '', (string) $value);
        $this->attributes['npp'] = $clean !== '' ? $clean : null;
    }

    /**
     * Status normalization.
     */
    public function setStatusAttribute($value): void
    {
        $v = mb_strtolower(trim((string) $value));

        // Mapping ke value ENUM di database
        $map = [
            // ON → aktif
            'aktif' => 'aktif',
            'active' => 'aktif',
            'ya' => 'aktif',
            'y' => 'aktif',
            '1' => 'aktif',

            // OFF → tidak_aktif
            'tidak_aktif' => 'tidak_aktif',
            'nonaktif' => 'tidak_aktif',
            'non-aktif' => 'tidak_aktif',
            'inactive' => 'tidak_aktif',
            'no' => 'tidak_aktif',
            'n' => 'tidak_aktif',
            '0' => 'tidak_aktif',
        ];

        // Default: aktif (biar nggak nabrak ENUM)
        $this->attributes['status'] = $map[$v] ?? 'aktif';
    }

    public function setPeranIdAttribute($value): void
    {
        $validated = validate_integer_id($value);
        $this->attributes['peran_id'] = $validated;
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL EVENTS
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        // Validate before saving
        static::saving(function ($model) {
            if (empty($model->email)) {
                throw new \InvalidArgumentException('Email wajib diisi');
            }

            if (empty($model->nama_lengkap)) {
                throw new \InvalidArgumentException('Nama lengkap wajib diisi');
            }

            if (empty($model->peran_id)) {
                throw new \InvalidArgumentException('Peran wajib dipilih');
            }

            // Validate email format
            if (! filter_var($model->email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Format email tidak valid');
            }
        });

        // Prevent deletion of admin
        static::deleting(function ($model) {
            if ($model->isAdmin()) {
                throw new \RuntimeException('User admin tidak dapat dihapus');
            }
        });
    }
}
