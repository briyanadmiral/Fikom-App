<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model; // ✅ ADDED
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * ✅ REFACTORED: Security enhanced dengan path validation
 * ✅ ADDED: SoftDeletes untuk data integrity
 */
class UserSignature extends Model
{
    use SoftDeletes; // ✅ ADDED

    protected $table = 'user_signatures';

    protected $fillable = ['pengguna_id', 'ttd_path', 'default_width_mm', 'default_height_mm'];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at', // ✅ ADDED
    ];

    protected $casts = [
        'pengguna_id' => 'integer',
        'default_width_mm' => 'integer',
        'default_height_mm' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // ✅ ADDED
    ];

    /**
     * SECURITY: Hide path dari JSON
     */
    protected $hidden = ['ttd_path'];

    // ==================== RELASI =========================

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    // ==================== ACCESSORS & MUTATORS =========================

    /**
     * ✅ GOOD: Accessor untuk ttd_path dengan validasi
     */
    protected function ttdPath(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => $value, set: fn (?string $value) => validate_file_path($value));
    }

    // ==================== SCOPES =========================

    /**
     * ✅ GOOD: Scope by user dengan validasi ID
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
     * ✅ GOOD: Get validated TTD path
     */
    public function getValidatedTtdPath(): ?string
    {
        return validate_file_path($this->ttd_path);
    }

    /**
     * ✅ GOOD: Check if TTD file exists
     */
    public function hasTtdFile(): bool
    {
        $path = $this->getValidatedTtdPath();

        if ($path === null) {
            return false;
        }

        return Storage::disk('local')->exists($path) || Storage::exists('public/'.ltrim($path, '/'));
    }

    /**
     * ✅ GOOD: Get TTD as base64
     */
    public function getTtdBase64(): ?string
    {
        $path = $this->getValidatedTtdPath();

        if ($path === null || ! $this->hasTtdFile()) {
            return null;
        }

        try {
            if (Storage::disk('local')->exists($path)) {
                $content = Storage::disk('local')->get($path);

                return 'data:image/png;base64,'.base64_encode($content);
            }

            $publicPath = 'public/'.ltrim($path, '/');
            if (Storage::exists($publicPath)) {
                $content = Storage::get($publicPath);

                return 'data:image/png;base64,'.base64_encode($content);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get TTD base64', [
                'user_id' => $this->pengguna_id,
                'path' => sanitize_log_message($path),
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }

        return null;
    }

    /**
     * ✅ GOOD: Get default dimensions
     */
    public function getDefaultDimensions(): array
    {
        return [
            'width' => $this->default_width_mm ?? 42,
            'height' => $this->default_height_mm ?? 20,
        ];
    }

    /**
     * ✅ GOOD: Validate dimensions
     */
    public function validateDimensions(int $width, int $height): bool
    {
        $minWidth = 20;
        $maxWidth = 80;
        $minHeight = 10;
        $maxHeight = 50;

        return $width >= $minWidth && $width <= $maxWidth && $height >= $minHeight && $height <= $maxHeight;
    }

    /**
     * ✅ ADDED: Delete TTD file from storage
     */
    public function deleteTtdFile(): bool
    {
        $path = $this->getValidatedTtdPath();

        if ($path === null) {
            return true;
        }

        try {
            if (Storage::disk('local')->exists($path)) {
                return Storage::disk('local')->delete($path);
            }

            $publicPath = 'public/'.ltrim($path, '/');
            if (Storage::exists($publicPath)) {
                return Storage::delete($publicPath);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete TTD file', [
                'user_id' => $this->pengguna_id,
                'path' => sanitize_log_message($path),
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            return false;
        }
    }

    /**
     * ✅ ADDED: Get TTD URL
     */
    public function getTtdUrl(): ?string
    {
        $path = $this->getValidatedTtdPath();

        if ($path === null) {
            return null;
        }

        // Try public storage first
        $publicPath = 'public/'.ltrim($path, '/');
        if (Storage::exists($publicPath)) {
            return Storage::url($publicPath);
        }

        // Return base64 if not in public storage
        return $this->getTtdBase64();
    }

    // ==================== MODEL EVENTS =========================

    protected static function boot()
    {
        parent::boot();

        // ✅ ADDED: Validate before saving
        static::saving(function ($model) {
            if (empty($model->pengguna_id)) {
                throw new \InvalidArgumentException('Pengguna ID wajib diisi');
            }

            $userId = validate_integer_id($model->pengguna_id);
            if ($userId === null) {
                throw new \InvalidArgumentException('Pengguna ID tidak valid');
            }

            // Validate dimensions if provided
            if ($model->default_width_mm !== null || $model->default_height_mm !== null) {
                $width = $model->default_width_mm ?? 42;
                $height = $model->default_height_mm ?? 20;

                if (! $model->validateDimensions($width, $height)) {
                    throw new \InvalidArgumentException('Dimensi signature tidak valid');
                }
            }

            // Validate file path
            if ($model->ttd_path !== null) {
                $validPath = validate_file_path($model->ttd_path);
                if ($validPath === null) {
                    throw new \InvalidArgumentException('Path file signature tidak valid');
                }
            }
        });

        // ✅ ADDED: Delete file on model deletion
        static::deleting(function ($model) {
            // Delete physical file when signature is deleted
            $model->deleteTtdFile();
        });

        // ✅ ADDED: Check for duplicate on create
        static::creating(function ($model) {
            // Check if user already has a signature
            $existing = self::where('pengguna_id', $model->pengguna_id)->whereNull('deleted_at')->first();

            if ($existing) {
                throw new \InvalidArgumentException('User sudah memiliki signature');
            }
        });
    }
}
