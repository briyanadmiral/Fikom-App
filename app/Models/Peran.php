<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ ADDED
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;

/**
 * ✅ REFACTORED: Security enhanced dengan sanitasi dan caching
 * ✅ ADDED: SoftDeletes untuk data integrity
 */
class Peran extends Model
{
    use SoftDeletes; // ✅ ADDED

    protected $table = 'peran';

    public $timestamps = false;

    protected $fillable = ['nama', 'deskripsi', 'is_active', 'dibuat_pada'];

    protected $guarded = ['id', 'deleted_at']; // ✅ ADDED deleted_at

    protected $casts = [
        'is_active' => 'boolean',
        'dibuat_pada' => 'datetime',
        'deleted_at' => 'datetime', // ✅ ADDED
    ];

    // ✅ ADDED: Dates array for compatibility
    protected $dates = ['dibuat_pada', 'deleted_at'];

    const CACHE_KEY = 'peran_all';
    const CACHE_TTL = 3600;

    // ==================== RELASI =========================

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'peran_id');
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ GOOD: Accessor untuk nama dengan sanitasi
     */
    protected function nama(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 100));
    }

    /**
     * ✅ GOOD: Accessor untuk deskripsi dengan sanitasi
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(get: fn(?string $value) => sanitize_output($value), set: fn(?string $value) => sanitize_input($value, 500));
    }

    // ==================== SCOPES =========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ✅ GOOD: Scope untuk role admin
     */
    public function scopeAdmin($query)
    {
        return $query->where('id', 1);
    }

    /**
     * ✅ GOOD: Scope untuk role approver
     */
    public function scopeApprover($query)
    {
        return $query->whereIn('id', [2, 3]); // Dekan & Wakil Dekan
    }

    /**
     * ✅ ADDED: Scope untuk role staff
     */
    public function scopeStaff($query)
    {
        return $query->where('id', '>', 3); // Non-admin, non-approver
    }

    /**
     * ✅ ADDED: Scope by name dengan sanitasi
     */
    public function scopeByName($query, string $nama)
    {
        $nama = sanitize_input($nama, 100);

        if ($nama === null || $nama === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('nama', $nama);
    }

    // ==================== BUSINESS LOGIC =========================

    /**
     * ✅ GOOD: Check if role is admin
     */
    public function isAdmin(): bool
    {
        return $this->id === 1;
    }

    /**
     * ✅ GOOD: Check if role can approve
     */
    public function canApprove(): bool
    {
        return in_array($this->id, [2, 3], true);
    }

    /**
     * ✅ GOOD: Get role display name (safe)
     */
    public function getDisplayName(): string
    {
        return $this->nama; // Already sanitized via accessor
    }

    /**
     * ✅ GOOD: Check if role can be deleted
     */
    public function canBeDeleted(): bool
    {
        // System roles (1-3) cannot be deleted
        if ($this->id <= 3) {
            return false;
        }

        // Cannot delete if has users
        return $this->users()->count() === 0;
    }

    /**
     * ✅ GOOD: Get users count
     */
    public function getUsersCount(): int
    {
        return $this->users()->count();
    }

    /**
     * ✅ ADDED: Get active users count
     */
    public function getActiveUsersCount(): int
    {
        return $this->users()->where('status', 'aktif')->count();
    }

    /**
     * ✅ ADDED: Check if role is system role
     */
    public function isSystemRole(): bool
    {
        return in_array($this->id, [1, 2, 3], true);
    }

    /**
     * ✅ ADDED: Get badge class for UI
     */
    public function getBadgeClass(): string
    {
        return match ($this->id) {
            1 => 'badge-danger', // Admin
            2 => 'badge-primary', // Dekan
            3 => 'badge-info', // Wakil Dekan
            default => 'badge-secondary',
        };
    }

    // ==================== STATIC METHODS =========================

    /**
     * ✅ GOOD: Get all roles dengan caching
     */
    public static function getAllCached()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::orderBy('id')->get();
        });
    }

    /**
     * ✅ ADDED: Get active roles dengan caching
     */
    public static function getActiveCached()
    {
        return Cache::remember(self::CACHE_KEY . '_active', self::CACHE_TTL, function () {
            return self::active()->orderBy('id')->get();
        });
    }

    /**
     * ✅ GOOD: Clear cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY . '_active');
    }

    /**
     * ✅ GOOD: Get role by name
     */
    public static function findByName(string $nama): ?self
    {
        $nama = sanitize_input($nama, 100);

        if ($nama === null || $nama === '') {
            return null;
        }

        return self::where('nama', $nama)->first();
    }

    /**
     * ✅ ADDED: Get role by ID with validation
     */
    public static function findById(int $id): ?self
    {
        $id = validate_integer_id($id);

        if ($id === null) {
            return null;
        }

        return self::find($id);
    }

    /**
     * ✅ ADDED: Get approver roles
     */
    public static function getApproverRoles()
    {
        return self::whereIn('id', [2, 3])
            ->orderBy('id')
            ->get();
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // Clear cache on changes
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });

        // ✅ ADDED: Clear cache on restore
        static::restored(function () {
            self::clearCache();
        });

        // ✅ ADDED: Prevent deletion of system roles
        static::deleting(function ($model) {
            if ($model->isSystemRole()) {
                throw new \RuntimeException('System role tidak dapat dihapus');
            }

            if ($model->users()->count() > 0) {
                throw new \RuntimeException('Role tidak dapat dihapus karena masih digunakan oleh user');
            }
        });

        // ✅ ADDED: Validate before saving
        static::saving(function ($model) {
            if (empty($model->nama)) {
                throw new \InvalidArgumentException('Nama role wajib diisi');
            }

            // Check for duplicate nama
            $existing = self::where('nama', $model->nama)
                ->where('id', '!=', $model->id ?? 0)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                throw new \InvalidArgumentException("Role '{$model->nama}' sudah ada");
            }
        });

        // ✅ ADDED: Auto-set dibuat_pada on create
        static::creating(function ($model) {
            if (empty($model->dibuat_pada)) {
                $model->dibuat_pada = now();
            }
        });
    }
}
