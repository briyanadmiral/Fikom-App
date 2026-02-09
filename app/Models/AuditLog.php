<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ✅ AuditLog - Model untuk tracking aktivitas user
 *
 * Menyimpan log untuk:
 * - Create/Update/Delete Surat Tugas
 * - Create/Update/Delete Surat Keputusan
 * - Approve/Reject/Publish/Archive actions
 * - Update settings (kop surat, ttd)
 */
class AuditLog extends Model
{
    /**
     * Disable timestamps karena kita hanya punya created_at
     */
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'entity_type',
        'entity_id',
        'entity_name',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Action types yang didukung
     */
    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_APPROVE = 'approve';

    public const ACTION_REJECT = 'reject';

    public const ACTION_SUBMIT = 'submit';

    public const ACTION_PUBLISH = 'publish';

    public const ACTION_UNPUBLISH = 'unpublish';

    public const ACTION_ARCHIVE = 'archive';

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    /**
     * Label untuk action (bahasa Indonesia)
     */
    public const ACTION_LABELS = [
        'create' => 'Membuat',
        'update' => 'Mengubah',
        'delete' => 'Menghapus',
        'approve' => 'Menyetujui',
        'reject' => 'Menolak',
        'submit' => 'Mengajukan',
        'publish' => 'Menerbitkan',
        'unpublish' => 'Membatalkan Terbit',
        'archive' => 'Mengarsipkan',
        'login' => 'Login',
        'logout' => 'Logout',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==================== SCOPES ====================

    /**
     * Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query;
    }

    /**
     * Filter by entity type
     */
    public function scopeByEntityType($query, $type)
    {
        if ($type) {
            return $query->where('entity_type', $type);
        }

        return $query;
    }

    /**
     * Filter by entity
     */
    public function scopeByEntity($query, $type, $id)
    {
        return $query->where('entity_type', $type)
            ->where('entity_id', $id);
    }

    /**
     * Filter by action
     */
    public function scopeByAction($query, $action)
    {
        if ($action) {
            return $query->where('action', $action);
        }

        return $query;
    }

    /**
     * Filter recent (dalam X hari)
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to.' 23:59:59');
        }

        return $query;
    }

    /**
     * Search by entity name or user name
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $escaped = addcslashes($keyword, '%_');

        return $query->where(function ($q) use ($escaped) {
            $q->where('entity_name', 'LIKE', "%{$escaped}%")
                ->orWhere('user_name', 'LIKE', "%{$escaped}%");
        });
    }

    // ==================== ACCESSORS ====================

    /**
     * Get action label (bahasa Indonesia)
     */
    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get entity type label
     */
    public function getEntityTypeLabelAttribute(): string
    {
        return match ($this->entity_type) {
            'TugasHeader' => 'Surat Tugas',
            'KeputusanHeader' => 'Surat Keputusan',
            'User' => 'Pengguna',
            'MasterKopSurat' => 'Kop Surat',
            'UserSignature' => 'Tanda Tangan',
            'SuratTemplate' => 'Template Surat',
            default => $this->entity_type,
        };
    }

    /**
     * Get formatted created_at
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at?->translatedFormat('d M Y H:i') ?? '-';
    }

    /**
     * Get badge class for action
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'create' => 'bg-green-100 text-green-800',
            'update' => 'bg-blue-100 text-blue-800',
            'delete' => 'bg-red-100 text-red-800',
            'approve' => 'bg-emerald-100 text-emerald-800',
            'reject' => 'bg-orange-100 text-orange-800',
            'submit' => 'bg-yellow-100 text-yellow-800',
            'publish' => 'bg-indigo-100 text-indigo-800',
            'archive' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // ==================== STATIC METHODS ====================

    /**
     * Get all action types for dropdown
     */
    public static function getActionOptions(): array
    {
        return self::ACTION_LABELS;
    }

    /**
     * Get all entity types that are logged
     */
    public static function getEntityTypeOptions(): array
    {
        return [
            'TugasHeader' => 'Surat Tugas',
            'KeputusanHeader' => 'Surat Keputusan',
            'User' => 'Pengguna',
            'MasterKopSurat' => 'Kop Surat',
            'UserSignature' => 'Tanda Tangan',
            'SuratTemplate' => 'Template Surat',
        ];
    }

    /**
     * Get browser and OS info from user_agent
     */
    public function getBrowserInfoAttribute(): string
    {
        if (empty($this->user_agent)) {
            return '-';
        }

        $agent = $this->user_agent;
        $browser = 'Unknown Browser';
        $os = 'Unknown OS';

        // Simple detection to avoid heavy dependencies
        if (preg_match('/Firefox\/([0-9.]+)/', $agent, $matches)) {
            $browser = 'Firefox '.intval($matches[1]);
        } elseif (preg_match('/Chrome\/([0-9.]+)/', $agent, $matches)) {
            $browser = 'Chrome '.intval($matches[1]);
        } elseif (preg_match('/Safari\/([0-9.]+)/', $agent, $matches)) {
            $browser = 'Safari '.intval($matches[1]);
        } elseif (preg_match('/Edge\/([0-9.]+)/', $agent, $matches)) {
            $browser = 'Edge '.intval($matches[1]);
        } elseif (strpos($agent, 'MSIE') !== false || strpos($agent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        }

        if (strpos($agent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($agent, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (strpos($agent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($agent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($agent, 'iPhone') !== false || strpos($agent, 'iPad') !== false) {
            $os = 'iOS';
        }

        return "{$browser} on {$os}";
    }

    /**
     * Get route to entity details
     */
    public function getEntityRouteAttribute(): ?string
    {
        if (empty($this->entity_id) || empty($this->entity_type)) {
            return null;
        }

        try {
            return match ($this->entity_type) {
                'TugasHeader' => route('surat_tugas.show', $this->entity_id),
                'KeputusanHeader' => route('surat_keputusan.show', $this->entity_id),
                'User' => route('users.show', $this->entity_id),
                'SuratTemplate' => route('surat_templates.edit', $this->entity_id), // Templates usually edited
                default => null,
            };
        } catch (\Exception $e) {
            return null; // Route might not exist
        }
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }
}
