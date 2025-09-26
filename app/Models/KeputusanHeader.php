<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\SuratStatus; // enum: DRAFT, PENDING, DISETUJUI, DITOLAK, TERBIT, ARSIP
use App\Models\User;
use App\Models\KeputusanVersi;
use App\Models\KeputusanPenerima;

/**
 * @property int                 $id
 * @property string              $nomor
 * @property \Carbon\Carbon|null $tanggal_asli
 * @property \Carbon\Carbon|null $tanggal_surat
 * @property string|null         $tentang
 * @property array               $menimbang
 * @property array               $mengingat
 * @property string|null         $memutuskan   // HTML siap cetak
 * @property string|null         $tembusan
 * @property SuratStatus|string  $status_surat // draft|pending|disetujui|ditolak|terbit|arsip
 * @property int                 $dibuat_oleh
 * @property int|null            $penandatangan
 * @property int|null            $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property int|null            $rejected_by
 * @property \Carbon\Carbon|null $rejected_at
 * @property int|null            $published_by
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $signed_at
 * @property string|null         $signed_pdf_path
 * @property array|null          $ttd_config   // ex: ['w_mm'=>42,'x_mm'=>0,'y_mm'=>0]
 * @property array|null          $cap_config   // ex: ['w_mm'=>35,'opacity'=>0.95,'x_mm'=>0,'y_mm'=>0]
 */
class KeputusanHeader extends Model
{
    use SoftDeletes;

    protected $table = 'keputusan_header';

    /**
     * Kolom yang boleh di-mass-assign.
     * Catatan:
     * - 'menetapkan' TIDAK disimpan di header (ada di KeputusanVersi.konten_json).
     * - 'memutuskan' disimpan sebagai HTML string (siap cetak/preview).
     */
    protected $fillable = [
        'nomor',
        'tanggal_asli',
        'tanggal_surat',
        'tentang',
        'menimbang',
        'mengingat',
        'memutuskan',
        'tembusan',
        'status_surat',
        'dibuat_oleh',
        'penandatangan',

        // jejak proses
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'published_by',
        'published_at',

        // tanda tangan & file final
        'signed_at',
        'signed_pdf_path',

        // konfigurasi visual
        'ttd_config',
        'cap_config',
    ];

    /**
     * Cast tipe data agar akses di PHP konsisten.
     */
    protected $casts = [
        'menimbang'      => 'array',
        'mengingat'      => 'array',
        // 'memutuskan' -> biarkan default string (HTML)
        'tanggal_asli'   => 'date',
        'tanggal_surat'  => 'date',

        // gunakan enum agar status rapi & aman dari typo
        'status_surat'   => SuratStatus::class,

        // jejak proses
        'approved_at'    => 'datetime',
        'rejected_at'    => 'datetime',
        'published_at'   => 'datetime',
        'signed_at'      => 'datetime',

        // konfigurasi visual
        'ttd_config'     => 'array',
        'cap_config'     => 'array',
    ];

    /**
     * Default attributes (opsional): status awal draft.
     */
    protected $attributes = [
        'status_surat' => 'draft',
    ];

    /* =======================
     *  Relasi
     * ======================= */

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // Alias agar kompatibel: method lama tetap ada
    public function penandatanganUser()
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    // Nama alias yang lebih konsisten dengan penamaan umum
    public function penandaTangan()
    {
        return $this->belongsTo(User::class, 'penandatangan');
    }

    public function versi()
    {
        // Kamu sudah pakai FK 'header_id' — dipertahankan
        return $this->hasMany(KeputusanVersi::class, 'header_id');
    }

    /**
     * Versi terakhir (berdasar kolom 'versi').
     * Jika Laravel < 9, gunakan orderBy di controller.
     */
    public function latestVersi()
    {
        return $this->hasOne(KeputusanVersi::class, 'header_id')->latestOfMany('versi');
    }

    public function penerima()
    {
        // Dipertahankan: tabel penerima pakai 'keputusan_id'
        return $this->hasMany(KeputusanPenerima::class, 'keputusan_id');
    }

    /* =======================
     *  Scopes
     * ======================= */

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('dibuat_oleh', $userId);
    }

    /**
     * Menerima string ('pending') atau enum (SuratStatus::PENDING).
     */
    public function scopeStatus($query, $status)
    {
        $value = $status instanceof SuratStatus ? $status->value : (string)$status;
        return $query->where('status_surat', $value);
    }

    public function scopeApproved($query)
    {
        return $this->scopeStatus($query, SuratStatus::DISETUJUI);
    }

    public function scopePending($query)
    {
        return $this->scopeStatus($query, SuratStatus::PENDING);
    }

    public function scopeForRecipient($query, $userId)
    {
        // Dipertahankan: kolom 'pengguna_id' sesuai kode kamu
        return $query->whereHas('penerima', function ($q) use ($userId) {
            $q->where('pengguna_id', $userId);
        });
    }

    /**
     * Alternatif umum dipakai di list "Surat Saya"
     */
    public function scopeUntukUser($query, $userId)
    {
        return $query->whereHas('penerima', fn($r) => $r->where('pengguna_id', $userId));
    }

    /* =======================
     *  Helpers / Util
     * ======================= */

    /**
     * Cek apakah sudah disetujui & ada timestamp tanda tangan elektronik.
     */
    public function isApproved(): bool
    {
        // jika cast enum aktif
        if ($this->status_surat instanceof SuratStatus) {
            return $this->status_surat === SuratStatus::DISETUJUI && !empty($this->signed_at);
        }
        // fallback jika sementara masih string
        return $this->status_surat === 'disetujui' && !empty($this->signed_at);
    }

    /**
     * Boleh tampilkan TTD & Cap?
     */
    public function shouldShowSigns(): bool
    {
        return $this->isApproved();
    }

    /**
     * Lebar TTD (mm) dengan default aman.
     */
    public function ttdWidthMm(): int
    {
        return (int)($this->ttd_config['w_mm'] ?? 42);
    }

    /**
     * Lebar Cap (mm) dengan default aman.
     */
    public function capWidthMm(): int
    {
        return (int)($this->cap_config['w_mm'] ?? 35);
    }

    /**
     * Opacity Cap (0–1) dengan default aman.
     */
    public function capOpacity(): float
    {
        return (float)($this->cap_config['opacity'] ?? 0.95);
    }

    /**
     * Aturan cross-approval Dekan <-> Wakil Dekan
     * - Tidak boleh self-approve
     * - Dekan approve buatan WD; WD approve buatan Dekan
     */
    public function bisaDiApproveOleh(User $user): bool
    {
        // Wajib pending
        $isPending = $this->status_surat instanceof SuratStatus
            ? $this->status_surat === SuratStatus::PENDING
            : $this->status_surat === 'pending';

        if (!$isPending) return false;

        // Tidak boleh approve surat yang dia buat sendiri
        if ((int)$this->dibuat_oleh === (int)$user->id) return false;

        // Cross-approval berbasis role Spatie
        $pembuatRoles  = $this->pembuat?->getRoleNames()->toArray() ?? [];
        $approverRoles = $user->getRoleNames()->toArray();

        $isDekanMilikWD = in_array('Wakil Dekan', $pembuatRoles, true) && in_array('Dekan', $approverRoles, true);
        $isWDMilikDekan = in_array('Dekan', $pembuatRoles, true) && in_array('Wakil Dekan', $approverRoles, true);

        return $isDekanMilikWD || $isWDMilikDekan;
    }

    /**
     * Siap publish (sudah disetujui dan sudah ada PDF bertanda tangan)
     */
    public function terbitSiapPublish(): bool
    {
        $isApprovedStatus = $this->status_surat instanceof SuratStatus
            ? $this->status_surat === SuratStatus::DISETUJUI
            : $this->status_surat === 'disetujui';

        return $isApprovedStatus && !empty($this->signed_pdf_path);
    }
}
