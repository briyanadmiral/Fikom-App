<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TugasLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tugas_log'; // ✅ FIXED: Sesuai dengan tabel di database

    public $timestamps = false; // ✅ FIXED: Tabel hanya punya created_at, tidak ada updated_at

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'tugas_id',
        'user_id',
        'status_lama',    // ✅ FIXED: sesuai kolom di tabel tugas_log
        'status_baru',    // ✅ FIXED: sesuai kolom di tabel tugas_log
        'ip_address',     // ✅ ADDED: ada di tabel tugas_log
        'user_agent',     // ✅ ADDED: ada di tabel tugas_log
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
