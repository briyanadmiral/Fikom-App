<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'pengguna_id',
        'email_on_approval_needed',
        'email_on_approved',
        'email_on_rejected',
        'email_digest_weekly',
    ];

    protected $casts = [
        'email_on_approval_needed' => 'boolean',
        'email_on_approved' => 'boolean',
        'email_on_rejected' => 'boolean',
        'email_digest_weekly' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    /**
     * Get or create preferences for a user
     */
    public static function getForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['pengguna_id' => $userId],
            [
                'email_on_approval_needed' => true,
                'email_on_approved' => true,
                'email_on_rejected' => true,
                'email_digest_weekly' => false,
            ]
        );
    }
}
