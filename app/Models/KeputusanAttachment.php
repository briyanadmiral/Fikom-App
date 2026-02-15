<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class KeputusanAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'keputusan_attachments';

    protected $fillable = ['keputusan_id', 'nama_file', 'nama_file_sistem', 'file_path', 'file_size', 'mime_type', 'extension', 'uploaded_by', 'deskripsi', 'kategori', 'download_count', 'last_downloaded_at'];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'last_downloaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function keputusan(): BelongsTo
    {
        return $this->belongsTo(KeputusanHeader::class, 'keputusan_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get human readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Get file icon based on extension
     */
    public function getFileIconAttribute(): string
    {
        return match ($this->extension) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc', 'docx' => 'fas fa-file-word text-primary',
            'xls', 'xlsx' => 'fas fa-file-excel text-success',
            'jpg', 'jpeg', 'png', 'gif' => 'fas fa-file-image text-info',
            'zip', 'rar' => 'fas fa-file-archive text-warning',
            default => 'fas fa-file text-secondary',
        };
    }

    /**
     * Get kategori label
     */
    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'proposal' => 'Proposal',
            'rab' => 'RAB',
            'surat_pengantar' => 'Surat Pengantar',
            'dokumentasi' => 'Dokumentasi',
            'lainnya' => 'Lainnya',
            default => 'Lainnya',
        };
    }

    // ==================== SCOPES ====================

    /**
     * Filter by kategori
     */
    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Filter by keputusan
     */
    public function scopeForKeputusan($query, int $keputusanId)
    {
        return $query->where('keputusan_id', $keputusanId);
    }

    // ==================== METHODS ====================

    /**
     * Delete physical file from storage
     */
    public function deleteFile(): bool
    {
        if (Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->delete($this->file_path);
        }

        return true;
    }

    /**
     * Check if file exists in storage
     */
    public function fileExists(): bool
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Increment download count
     */
    public function incrementDownload(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    /**
     * Get full storage path
     */
    public function getFullPath(): string
    {
        return Storage::disk('local')->path($this->file_path);
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        // Auto-delete file saat record dihapus permanent
        static::forceDeleted(function ($attachment) {
            $attachment->deleteFile();
        });
    }
}
