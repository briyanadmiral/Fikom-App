<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipientImport extends Model
{
    protected $table = 'recipient_imports';

    protected $fillable = [
        'user_id',
        'original_filename',
        'file_path',
        'status',
        'total_rows',
        'success_count',
        'error_count',
        'errors',
    ];

    protected $casts = [
        'errors' => 'array',
        'total_rows' => 'integer',
        'success_count' => 'integer',
        'error_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'secondary',
        };
    }
}
