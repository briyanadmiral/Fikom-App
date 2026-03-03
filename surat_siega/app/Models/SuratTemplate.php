<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SuratTemplate - Model untuk menyimpan template Surat Tugas.
 *
 * Placeholder yang didukung:
 * - {{nama_penerima}}  : Nama penerima tugas
 * - {{tanggal}}        : Tanggal surat (formatted)
 * - {{jabatan}}        : Jabatan penandatangan
 * - {{nomor_surat}}    : Nomor surat otomatis
 * - {{tahun}}          : Tahun surat
 * - {{bulan}}          : Bulan surat
 */
class SuratTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'surat_templates';

    protected $fillable = [
        'nama',
        'deskripsi',
        'jenis_tugas_id',
        'sub_tugas_id',
        'detail_tugas',
        'tembusan',
        'dibuat_oleh',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Daftar placeholder yang didukung
     */
    public const PLACEHOLDERS = [
        '{{nama_penerima}}' => 'Nama penerima tugas',
        '{{tanggal}}' => 'Tanggal surat (formatted)',
        '{{jabatan}}' => 'Jabatan penandatangan',
        '{{nomor_surat}}' => 'Nomor surat',
        '{{tahun}}' => 'Tahun surat',
        '{{bulan}}' => 'Bulan surat',
        '{{tempat}}' => 'Tempat pelaksanaan',
        '{{waktu}}' => 'Waktu pelaksanaan',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Relasi ke Jenis Tugas
     */
    public function jenisTugas(): BelongsTo
    {
        return $this->belongsTo(JenisTugas::class, 'jenis_tugas_id');
    }

    /**
     * Relasi ke Sub Tugas
     */
    public function subTugas(): BelongsTo
    {
        return $this->belongsTo(SubTugas::class, 'sub_tugas_id');
    }

    /**
     * Relasi ke User pembuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // ==================== ACCESSORS ====================

    /**
     * Sanitize nama
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => sanitize_output($value),
            set: fn (?string $value) => sanitize_input($value, 100)
        );
    }

    /**
     * Sanitize deskripsi
     */
    protected function deskripsi(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => sanitize_output($value),
            set: fn (?string $value) => sanitize_input($value, 500)
        );
    }

    // ==================== SCOPES ====================

    /**
     * Scope untuk template aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope filter by jenis tugas
     */
    public function scopeByJenis($query, $jenisId)
    {
        if ($jenisId) {
            return $query->where('jenis_tugas_id', $jenisId);
        }

        return $query;
    }

    /**
     * Scope filter by creator
     */
    public function scopeByCreator($query, $userId)
    {
        if ($userId) {
            return $query->where('dibuat_oleh', $userId);
        }

        return $query;
    }

    /**
     * Scope search by nama/deskripsi
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sanitize_input($keyword, 100);
        $escaped = addcslashes($keyword, '%_');

        return $query->where(function ($q) use ($escaped) {
            $q->where('nama', 'LIKE', "%{$escaped}%")
                ->orWhere('deskripsi', 'LIKE', "%{$escaped}%");
        });
    }

    // ==================== BUSINESS LOGIC ====================

    /**
     * Get list of available placeholders
     */
    public static function getPlaceholders(): array
    {
        return self::PLACEHOLDERS;
    }

    /**
     * Deteksi placeholder yang digunakan dalam template
     */
    public function getUsedPlaceholders(): array
    {
        $used = [];
        $content = $this->detail_tugas.' '.($this->tembusan ?? '');

        foreach (array_keys(self::PLACEHOLDERS) as $placeholder) {
            if (str_contains($content, $placeholder)) {
                $used[] = $placeholder;
            }
        }

        return $used;
    }

    /**
     * Apply placeholder replacements ke template
     *
     * @param  array  $data  Associative array dengan key = placeholder (tanpa {{}})
     * @return array ['detail_tugas' => ..., 'tembusan' => ...]
     */
    public function applyPlaceholders(array $data): array
    {
        $detailTugas = $this->detail_tugas;
        $tembusan = $this->tembusan ?? '';

        foreach ($data as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $safeValue = sanitize_output($value);

            $detailTugas = str_replace($placeholder, $safeValue, $detailTugas);
            $tembusan = str_replace($placeholder, $safeValue, $tembusan);
        }

        return [
            'detail_tugas' => $detailTugas,
            'tembusan' => $tembusan,
        ];
    }

    /**
     * Preview template dengan sample data
     */
    public function preview(): array
    {
        $sampleData = [
            'nama_penerima' => 'Dr. Contoh Nama, M.Pd.',
            'tanggal' => now()->translatedFormat('d F Y'),
            'jabatan' => 'Dekan',
            'nomor_surat' => '001/UN.../ST/'.now()->year,
            'tahun' => now()->year,
            'bulan' => now()->translatedFormat('F'),
            'tempat' => 'Ruang Rapat Fakultas',
            'waktu' => '09:00 WIB',
        ];

        return $this->applyPlaceholders($sampleData);
    }

    /**
     * Duplicate template as new draft
     */
    public function duplicate(int $userId): self
    {
        $new = $this->replicate();
        $new->nama = $this->nama.' (Copy)';
        $new->dibuat_oleh = $userId;
        $new->save();

        return $new;
    }

    /**
     * Check if template can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Template bisa dihapus kapan saja (soft delete)
        return true;
    }

    // ==================== MODEL EVENTS ====================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->nama)) {
                throw new \InvalidArgumentException('Nama template wajib diisi');
            }
        });
    }
}
