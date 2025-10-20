<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TugasLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tugas_logs'; // atau nama tabel yang sebenarnya
    
    protected $fillable = [
        'tugas_id',
        'user_id',
        'action', // 'created', 'updated', 'submitted', 'approved', 'rejected'
        'old_status',
        'new_status',
        'notes',
    ];

    protected $casts = [
        'tugas_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
