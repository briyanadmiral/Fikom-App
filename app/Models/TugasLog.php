<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TugasLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tugas_log';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'tugas_id',
        'user_id',
        'status_lama',
        'status_baru',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'tugas_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(TugasHeader::class, 'tugas_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
