<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TugasPenerima - Model untuk penerima surat tugas.
 */
class TugasPenerima extends Model
{
    use SoftDeletes;

    protected $table = 'tugas_penerima';

    protected $fillable = [
        'tugas_id',
        'pengguna_id',
        'nama_eksternal',
        'email_eksternal',
        'jabatan_eksternal',
        'instansi_eksternal',
        'is_internal',
        'is_read',
        'read_at',
        'dibaca',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'tugas_id' => 'integer',
        'pengguna_id' => 'integer',
        'is_internal' => 'boolean',
        'is_read' => 'boolean',
        'dibaca' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public $timestamps = true;

    // ==================== RELASI =========================

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(TugasHeader::class, 'tugas_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * Accessor nama lengkap dengan sanitasi.
     */
    protected function namaLengkap(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_internal && $this->pengguna) {
                    return sanitize_output($this->pengguna->nama_lengkap ?? $this->pengguna->name);
                }

                return sanitize_output($this->nama_eksternal ?? 'Unknown');
            },
        );
    }

    /**
     * Accessor email dengan validasi.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_internal && $this->pengguna) {
                    return sanitize_email($this->pengguna->email) ?: '';
                }

                return sanitize_email($this->email_eksternal) ?: '';
            },
        );
    }

    /**
     * Mutator untuk nama_eksternal.
     */
    protected function namaEksternal(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 255));
    }

    /**
     * Mutator untuk email_eksternal.
     */
    protected function emailEksternal(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_email($value));
    }

    /**
     * Mutator untuk jabatan_eksternal.
     */
    protected function jabatanEksternal(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 255));
    }

    /**
     * Mutator untuk instansi_eksternal.
     */
    protected function instansiEksternal(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => sanitize_output($value), set: fn (?string $value) => sanitize_input($value, 255));
    }

    // ==================== SCOPES =========================

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeExternal($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope by tugas dengan validasi ID.
     */
    public function scopeByTugas($query, $tugasId)
    {
        $tugasId = validate_integer_id($tugasId);

        if ($tugasId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('tugas_id', $tugasId);
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

    // ==================== BUSINESS LOGIC =========================

    /**
     * Mark as read.
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        return $this->update([
            'is_read' => true,
            'dibaca' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark as unread.
     */
    public function markAsUnread(): bool
    {
        if (! $this->is_read) {
            return true;
        }

        return $this->update([
            'is_read' => false,
            'dibaca' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Check if internal recipient.
     */
    public function isInternal(): bool
    {
        return $this->is_internal === true && $this->pengguna_id !== null;
    }

    /**
     * Check if external recipient.
     */
    public function isExternal(): bool
    {
        return $this->is_internal === false;
    }

    /**
     * Get display name (safe).
     */
    public function getDisplayName(): string
    {
        return $this->nama_lengkap;
    }

    /**
     * Get full info with jabatan & instansi.
     */
    public function getFullInfo(): string
    {
        if ($this->isInternal()) {
            return $this->namaLengkap;
        }

        $info = $this->namaLengkap;

        if ($this->jabatan_eksternal) {
            $info .= ' - '.sanitize_output($this->jabatan_eksternal);
        }

        if ($this->instansi_eksternal) {
            $info .= ' ('.sanitize_output($this->instansi_eksternal).')';
        }

        return $info;
    }

    /**
     * Get badge class for UI.
     */
    public function getBadgeClass(): string
    {
        if ($this->is_read) {
            return 'badge-secondary';
        }

        return 'badge-primary';
    }

    // ==================== STATIC METHODS =========================

    /**
     * Get penerima by tugas.
     */
    public static function getByTugas(int $tugasId)
    {
        $tugasId = validate_integer_id($tugasId);

        if ($tugasId === null) {
            return collect();
        }

        return self::where('tugas_id', $tugasId)->with('pengguna')->get();
    }

    /**
     * Get internal penerima by tugas.
     */
    public static function getInternalByTugas(int $tugasId)
    {
        $tugasId = validate_integer_id($tugasId);

        if ($tugasId === null) {
            return collect();
        }

        return self::where('tugas_id', $tugasId)->where('is_internal', true)->with('pengguna')->get();
    }

    /**
     * Get external penerima by tugas.
     */
    public static function getExternalByTugas(int $tugasId)
    {
        $tugasId = validate_integer_id($tugasId);

        if ($tugasId === null) {
            return collect();
        }

        return self::where('tugas_id', $tugasId)->where('is_internal', false)->get();
    }

    // ==================== MODEL EVENTS =========================
    protected static function boot()
    {
        parent::boot();

        // Validate before saving (lebih lenient)
        static::saving(function ($model) {
            // Validate tugas_id (ini wajib)
            if (empty($model->tugas_id)) {
                throw new \InvalidArgumentException('Tugas ID wajib diisi');
            }

            $tugasId = validate_integer_id($model->tugas_id);
            if ($tugasId === null) {
                throw new \InvalidArgumentException('Tugas ID tidak valid');
            }

            // Validate recipient data (hanya jika is_internal = true)
            if ($model->is_internal === true || $model->is_internal === 1) {
                // Penerima internal: wajib punya pengguna_id
                if (empty($model->pengguna_id)) {
                    throw new \InvalidArgumentException('Pengguna ID wajib diisi untuk penerima internal');
                }

                $userId = validate_integer_id($model->pengguna_id);
                if ($userId === null) {
                    throw new \InvalidArgumentException('Pengguna ID tidak valid');
                }
            }
            // Validasi penerima eksternal dihapus
            // Karena:
            // 1. Penerima eksternal OPTIONAL
            // 2. Validasi sudah ada di FormRequest
            // 3. Tidak semua surat tugas butuh penerima eksternal
        });
    }
}
