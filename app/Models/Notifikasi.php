<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Notifikasi - Model untuk notifikasi pengguna.
 */
class Notifikasi extends Model
{
    use SoftDeletes;

    protected $table = 'notifikasi';

    public $timestamps = false; // Pakai 'dibuat_pada' bukan created_at/updated_at

    protected $fillable = ['pengguna_id', 'tipe', 'referensi_id', 'pesan', 'dibaca', 'dibuat_pada'];

    protected $guarded = ['id', 'deleted_at'];

    protected $casts = [
        'pengguna_id' => 'integer',
        'referensi_id' => 'integer',
        'dibaca' => 'boolean',
        'dibuat_pada' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['dibuat_pada', 'deleted_at'];

    // ==================== RELASI =========================

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * Accessor untuk tipe dengan sanitasi.
     */
    protected function tipe(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 50));
    }

    /**
     * Accessor untuk pesan dengan sanitasi.
     */
    protected function pesan(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_notification($value, 500));
    }

    // ==================== SCOPES =========================

    public function scopeUnread($query)
    {
        return $query->where('dibaca', false);
    }

    public function scopeRead($query)
    {
        return $query->where('dibaca', true);
    }

    /**
     * Scope by user dengan validasi ID.
     */
    public function scopeByUser($query, $userId)
    {
        $userId = validate_integer_id($userId);

        if ($userId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('pengguna_id', $userId);
    }

    /**
     * Scope by tipe dengan sanitasi.
     */
    public function scopeByTipe($query, string $tipe)
    {
        $tipe = sanitize_input($tipe, 50);

        if ($tipe === null || $tipe === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('tipe', $tipe);
    }

    /**
     * Scope recent notifications.
     */
    public function scopeRecent($query, int $days = 7)
    {
        $days = max(1, min(365, $days));

        return $query->where('dibuat_pada', '>=', now()->subDays($days));
    }

    /**
     * Scope order by latest.
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('dibuat_pada');
    }

    /**
     * Scope by referensi with validation.
     */
    public function scopeByReferensi($query, int $referensiId)
    {
        $referensiId = validate_integer_id($referensiId);

        if ($referensiId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('referensi_id', $referensiId);
    }

    // ==================== BUSINESS LOGIC =========================

    /**
     * Mark as read.
     */
    public function markAsRead(): bool
    {
        if ($this->dibaca) {
            return true; // Already read
        }

        return $this->update(['dibaca' => true]);
    }

    /**
     * Mark as unread.
     */
    public function markAsUnread(): bool
    {
        if (! $this->dibaca) {
            return true; // Already unread
        }

        return $this->update(['dibaca' => false]);
    }

    /**
     * Get pesan yang aman untuk display.
     */
    public function getPesanSafe(): string
    {
        return $this->pesan; // Already sanitized via accessor
    }

    /**
     * Get time ago (human readable).
     */
    public function getTimeAgo(): string
    {
        if ($this->dibuat_pada === null) {
            return '-';
        }

        return $this->dibuat_pada->diffForHumans();
    }

    /**
     * Check if notification is old.
     */
    public function isOld(int $days = 30): bool
    {
        if ($this->dibuat_pada === null) {
            return true;
        }

        $days = max(1, $days);

        return $this->dibuat_pada->lt(now()->subDays($days));
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return ! $this->dibaca;
    }

    /**
     * Get badge class for UI.
     */
    public function getBadgeClass(): string
    {
        return $this->dibaca ? 'badge-secondary' : 'badge-primary';
    }

    public function getIcon(): string
    {
        return match ($this->tipe) {
            'surat_tugas' => 'bi-file-earmark-text',
            'surat_keputusan' => 'bi-file-earmark-check',
            'approval' => 'bi-check-circle',
            'rejection' => 'bi-x-circle',
            'info' => 'bi-info-circle',
            default => 'bi-bell',
        };
    }

    /**
     * Dynamic Link Accessor.
     */
    public function getLinkAttribute(): ?string
    {
        if (! $this->referensi_id) {
            return null;
        }

        return match ($this->tipe) {
            'surat_tugas', 'st' => route('surat_tugas.show', $this->referensi_id),
            'surat_keputusan', 'sk' => route('surat_keputusan.show', $this->referensi_id),
            'approval_st' => route('surat_tugas.approve.form', $this->referensi_id),
            'approval_sk' => route('surat_keputusan.approveForm', $this->referensi_id),
            default => null,
        };
    }

    // ==================== STATIC METHODS =========================

    /**
     * Mark all as read for user.
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        $userId = validate_integer_id($userId);

        if ($userId === null) {
            return 0;
        }

        return self::where('pengguna_id', $userId)
            ->where('dibaca', false)
            ->update(['dibaca' => true]);
    }

    /**
     * Delete old notifications (with soft delete awareness).
     */
    public static function deleteOldNotifications(int $days = 90): int
    {
        $days = max(30, $days);

        return self::where('dibuat_pada', '<', now()->subDays($days))
            ->where('dibaca', true)
            ->delete();
    }

    /**
     * Force delete old soft-deleted notifications.
     */
    public static function forceDeleteOldNotifications(int $days = 180): int
    {
        $days = max(90, $days);

        return self::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($days))
            ->forceDelete();
    }

    /**
     * Get unread count for user.
     */
    public static function getUnreadCount(int $userId): int
    {
        $userId = validate_integer_id($userId);

        if ($userId === null) {
            return 0;
        }

        return self::where('pengguna_id', $userId)->where('dibaca', false)->count();
    }

    /**
     * Create notification with validation.
     */
    public static function createNotification(array $data): ?self
    {
        // Validate required fields
        if (empty($data['pengguna_id']) || empty($data['tipe']) || empty($data['pesan'])) {
            return null;
        }

        // Validate user ID
        $userId = validate_integer_id($data['pengguna_id']);
        if ($userId === null) {
            return null;
        }

        // Sanitize data
        $data['pengguna_id'] = $userId;
        $data['tipe'] = sanitize_input($data['tipe'], 50);
        $data['pesan'] = sanitize_notification($data['pesan'], 500);
        $data['dibaca'] = false;
        $data['dibuat_pada'] = now();

        // Validate referensi_id if present
        if (isset($data['referensi_id'])) {
            $data['referensi_id'] = validate_integer_id($data['referensi_id']);
        }

        return self::create($data);
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // Auto-set dibuat_pada on create
        static::creating(function ($model) {
            if (empty($model->dibuat_pada)) {
                $model->dibuat_pada = now();
            }
        });

        // Validate before saving
        static::saving(function ($model) {
            if (empty($model->pengguna_id)) {
                throw new \InvalidArgumentException('Pengguna ID wajib diisi');
            }

            if (empty($model->tipe)) {
                throw new \InvalidArgumentException('Tipe notifikasi wajib diisi');
            }

            if (empty($model->pesan)) {
                throw new \InvalidArgumentException('Pesan notifikasi wajib diisi');
            }
        });
    }
}
