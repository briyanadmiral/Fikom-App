<?php

namespace App\Services;

use App\Models\KlasifikasiSurat;
use App\Models\TugasHeader;
use App\Models\TugasPenerima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Import helper functions (global) agar bisa dipanggil langsung
use function logStatusChange;
use function sanitize_html_limited;
use function sanitize_input;
use function sanitize_notification;
use function validate_integer_id;
use function validate_status;

class SuratTugasService
{
    protected SuratTugasNotificationService $notificationService;

    protected ?NomorSuratService $nomorService;

    public function __construct(SuratTugasNotificationService $notificationService, ?NomorSuratService $nomorService = null)
    {
        $this->notificationService = $notificationService;
        $this->nomorService = $nomorService ?? app(NomorSuratService::class);
    }

    /**
     * Create new surat tugas
     * Asumsi: data sudah melalui FormRequest (trim, cast, rules).
     */
    public function createTugas(array $validatedData, string $mode): TugasHeader
    {
        // Whitelist mode
        if (! in_array($mode, ['submit', 'draft'], true)) {
            throw new \InvalidArgumentException('Mode tidak valid. Gunakan "submit" atau "draft".');
        }

        return DB::transaction(function () use ($validatedData, $mode) {

            $status = $mode === 'submit' ? 'pending' : 'draft';
            $nextApprover = null;

            // Ambil penandatangan dari *_id (prioritas), fallback ke legacy key bila ada
            $penandaIn = validate_integer_id($validatedData['penandatangan_id'] ?? ($validatedData['penandatangan'] ?? null));
            if ($status === 'pending') {
                $nextApprover = $penandaIn ?: null;
            }

            // Nomor surat: pakai input jika ada, kalau kosong → reserve otomatis
            $nomor = trim((string) ($validatedData['nomor'] ?? ''));
            $suffix = null;
            $parentTugasId = null;
            $nomorUrutInt = null;

            // ✅ MODE TURUNAN: Gunakan suffix dari parent jika is_turunan = true
            if (! empty($validatedData['is_turunan']) && ! empty($validatedData['parent_tugas_id'])) {
                $parentId = (int) $validatedData['parent_tugas_id'];
                $suffixData = $this->nomorService->reserveSuffix($parentId);

                $nomor = $suffixData['nomor'];
                $suffix = $suffixData['suffix'];
                $parentTugasId = $suffixData['parent_id'];
                $nomorUrutInt = $suffixData['nomor_urut_int'];

                Log::info('Mode Turunan: Created suffix nomor', [
                    'parent_id' => $parentId,
                    'suffix' => $suffix,
                    'nomor' => $nomor,
                ]);
            } elseif ($nomor === '') {
                // Normal mode: reserve nomor baru
                $klasifikasiId = (int) ($validatedData['klasifikasi_surat_id'] ?? 0);
                $klasifikasi = $klasifikasiId ? KlasifikasiSurat::find($klasifikasiId) : null;
                $kodeKlas = $klasifikasi ? $klasifikasi->kode : 'B.10.1';

                $bulanR = $this->ensureBulanRomawi($validatedData['bulan'] ?? 'I');
                $tahun = (int) ($validatedData['tahun'] ?? date('Y'));
                $unit = 'TG';

                $res = $this->nomorService->reserve($unit, $kodeKlas, $bulanR, $tahun);
                $nomor = $res['nomor'];

                // Extract nomor_urut_int untuk sorting
                $parts = explode('/', $nomor);
                $nomorUrutInt = (int) preg_replace('/\D/', '', $parts[0] ?? '0');
            }

            $segmen = $this->resolveSegmenPenerima($validatedData['status_penerima'] ?? null);
            $tanggalSurat = $validatedData['tanggal_surat'] ?? now()->format('Y-m-d');
            $tugasHeaderModel = new TugasHeader;
            $table = $tugasHeaderModel->getTable();

            // Build data aman
            $data = [
                'nomor' => $nomor,
                'nomor_status' => 'reserved',
                'bulan' => $this->ensureBulanRomawi($validatedData['bulan'] ?? 'I'),
                'tahun' => (int) ($validatedData['tahun'] ?? date('Y')),
                'semester' => $validatedData['semester'] ?? null,
                'nama_umum' => sanitize_input($validatedData['nama_umum'] ?? '', 255),
                'klasifikasi_surat_id' => (int) ($validatedData['klasifikasi_surat_id'] ?? 0),
                'tanggal_surat' => $tanggalSurat,
                'status_surat' => validate_status($status, ['draft', 'pending', 'disetujui', 'ditolak']) ?? 'draft',
                'dibuat_oleh' => Auth::id(),
                'jenis_tugas' => sanitize_input($validatedData['jenis_tugas'] ?? '', 100),
                'tugas' => sanitize_input($validatedData['tugas'] ?? '', 255),
                'detail_tugas' => sanitize_input($validatedData['detail_tugas'] ?? null, 65000),

                'status_penerima' => $segmen,
                'redaksi_pembuka' => sanitize_html_limited($validatedData['redaksi_pembuka'] ?? null),
                'penutup' => sanitize_html_limited($validatedData['penutup'] ?? null),
                'waktu_mulai' => $validatedData['waktu_mulai'] ?? null,
                'waktu_selesai' => $validatedData['waktu_selesai'] ?? null,
                'tempat' => sanitize_input($validatedData['tempat'] ?? '', 255),
                'next_approver' => $nextApprover ? (int) $nextApprover : null,
                'tembusan' => sanitize_input($validatedData['tembusan'] ?? '', 500),
                'submitted_at' => $status === 'pending' ? now() : null,
                // ✅ NOMOR TURUNAN (Suffix Letter)
                'suffix' => $suffix,
                'parent_tugas_id' => $parentTugasId,
                'nomor_urut_int' => $nomorUrutInt,
            ];

            // ✅ FIX: Set semua FK yang dibutuhkan
            // Kolom baru dengan _id
            $this->putIfColumnExists($data, $table, 'pembuat_id', validate_integer_id($validatedData['pembuat_id'] ?? ($validatedData['nama_pembuat'] ?? null)));
            $this->putIfColumnExists($data, $table, 'asal_surat_id', validate_integer_id($validatedData['asal_surat_id'] ?? ($validatedData['asal_surat'] ?? null)));
            $this->putIfColumnExists($data, $table, 'penandatangan_id', $penandaIn);

            // ✅ FIX: Set kolom legacy nama_pembuat, asal_surat, penandatangan
            // CRITICAL: Kolom ini harus diisi jika masih ada di DB dan NOT NULL
            if (Schema::hasColumn($table, 'nama_pembuat')) {
                // Prioritas: ambil ID dari pembuat_id
                $pembuatId = validate_integer_id($validatedData['pembuat_id'] ?? ($validatedData['nama_pembuat'] ?? Auth::id()));
                $data['nama_pembuat'] = $pembuatId; // ✅ FIXED: Set with ID
            }

            if (Schema::hasColumn($table, 'asal_surat')) {
                $asalId = validate_integer_id($validatedData['asal_surat_id'] ?? null) ?: validate_integer_id($validatedData['asal_surat'] ?? null) ?: validate_integer_id($validatedData['pembuat_id'] ?? null) ?: Auth::id(); // fallback terakhir yang aman

                $data['asal_surat'] = (int) $asalId;
            }

            if (Schema::hasColumn($table, 'penandatangan')) {
                $data['penandatangan'] = $penandaIn; // ✅ FIXED: Set with ID
            }

            $tugas = TugasHeader::create($data);

            // Sinkron penerima (internal/eksternal)
            $this->syncPenerima($tugas, $validatedData['penerima_internal'] ?? [], $validatedData['penerima_eksternal'] ?? []);

            if ($status === 'pending') {
                // Kirim notifikasi (bila ada teks custom, bungkus sanitize_notification())
                $this->notificationService->notifyApprovalRequest($tugas);
            }

            // Audit trail
            logStatusChange(DB::connection(), (int) $tugas->id, null, $status);

            return $tugas;
        });
    }

    /**
     * Update existing surat tugas
     */
    public function updateTugas(TugasHeader $tugas, array $validatedData, string $mode): TugasHeader
    {
        if (! in_array($mode, ['submit', 'draft'], true)) {
            throw new \InvalidArgumentException('Mode tidak valid.');
        }

        return DB::transaction(function () use ($tugas, $validatedData, $mode) {
            // Tidak boleh update bila nomor sudah terkunci
            if ($tugas->nomor_status === 'locked') {
                throw new \RuntimeException('Surat sudah terkunci (locked) dan tidak dapat diubah.');
            }

            $oldStatus = $tugas->status_surat;
            $newStatus = $oldStatus;
            $nextApprover = $tugas->next_approver;

            // Submit dari draft → pending
            if ($mode === 'submit' && $oldStatus === 'draft') {
                $newStatus = 'pending';
                $nextApprover = validate_integer_id($validatedData['penandatangan_id'] ?? ($validatedData['penandatangan'] ?? null));
            }

            // Nomor otomatis bila transisi draft→pending dan belum ada nomor
            $nomor = trim((string) ($validatedData['nomor'] ?? ''));
            if ($nomor === '' && $oldStatus === 'draft' && $newStatus === 'pending') {
                $klasifikasiId = (int) ($validatedData['klasifikasi_surat_id'] ?? $tugas->klasifikasi_surat_id);
                $klasifikasi = $klasifikasiId ? KlasifikasiSurat::find($klasifikasiId) : null;
                $kodeKlas = $klasifikasi ? $klasifikasi->kode : 'B.10.1';

                $bulanR = $this->ensureBulanRomawi($validatedData['bulan'] ?? ($tugas->bulan ?? 'I'));
                $tahun = (int) ($validatedData['tahun'] ?? ($tugas->tahun ?? date('Y')));
                $unit = 'TG';

                $res = $this->nomorService->reserve($unit, $kodeKlas, $bulanR, $tahun);
                $nomor = $res['nomor'];
            }

            $segmen = $this->resolveSegmenPenerima($validatedData['status_penerima'] ?? $tugas->status_penerima);
            $tanggalSurat = $validatedData['tanggal_surat'] ?? ($tugas->tanggal_surat ?? now()->format('Y-m-d'));
            $table = $tugas->getTable();

            $data = [
                'nomor' => $nomor !== '' ? $nomor : $tugas->nomor,
                'bulan' => $this->ensureBulanRomawi($validatedData['bulan'] ?? $tugas->bulan),
                'tahun' => (int) ($validatedData['tahun'] ?? $tugas->tahun),
                'semester' => $validatedData['semester'] ?? $tugas->semester,
                'nama_umum' => sanitize_input($validatedData['nama_umum'] ?? $tugas->nama_umum, 255),
                'klasifikasi_surat_id' => (int) ($validatedData['klasifikasi_surat_id'] ?? $tugas->klasifikasi_surat_id),
                'tanggal_surat' => $tanggalSurat,
                'status_surat' => validate_status($newStatus, ['draft', 'pending', 'disetujui', 'ditolak']) ?? $tugas->status_surat,

                'jenis_tugas' => sanitize_input($validatedData['jenis_tugas'] ?? $tugas->jenis_tugas, 100),
                'tugas' => sanitize_input($validatedData['tugas'] ?? $tugas->tugas, 255),
                'detail_tugas' => sanitize_input($validatedData['detail_tugas'] ?? $tugas->detail_tugas, 65000),

                'status_penerima' => $segmen,
                'redaksi_pembuka' => sanitize_html_limited($validatedData['redaksi_pembuka'] ?? $tugas->redaksi_pembuka),
                'penutup' => sanitize_html_limited($validatedData['penutup'] ?? $tugas->penutup),

                'next_approver' => $nextApprover,
                'waktu_mulai' => $validatedData['waktu_mulai'] ?? $tugas->waktu_mulai,
                'waktu_selesai' => $validatedData['waktu_selesai'] ?? $tugas->waktu_selesai,
                'tempat' => sanitize_input($validatedData['tempat'] ?? $tugas->tempat, 255),
                'tembusan' => sanitize_input($validatedData['tembusan'] ?? $tugas->tembusan, 500),
                'submitted_at' => $oldStatus === 'draft' && $newStatus === 'pending' ? now() : $tugas->submitted_at,
            ];

            // FK aman (prioritaskan *_id bila ada; jika tidak ada, jangan overwrite legacy dengan angka)
            $this->putIfColumnExists($data, $table, 'pembuat_id', validate_integer_id($validatedData['pembuat_id'] ?? ($tugas->pembuat_id ?? null)));
            $this->putIfColumnExists($data, $table, 'asal_surat_id', validate_integer_id($validatedData['asal_surat_id'] ?? ($tugas->asal_surat_id ?? null)));

            if (array_key_exists('penandatangan_id', $validatedData)) {
                $this->putIfColumnExists($data, $table, 'penandatangan_id', validate_integer_id($validatedData['penandatangan_id']));
            }

            if (! Schema::hasColumn($table, 'penandatangan_id') && Schema::hasColumn($table, 'penandatangan')) {
                if (array_key_exists('penandatangan', $validatedData)) {
                    $data['penandatangan'] = (int) validate_integer_id($validatedData['penandatangan']) ?? $tugas->penandatangan;
                }
            }

            $tugas->update($data);

            // Sinkron penerima (HANYA jika ada data penerima di input, agar tidak terhapus saat submit)
            if (array_key_exists('penerima_internal', $validatedData) || array_key_exists('penerima_eksternal', $validatedData)) {
                $this->syncPenerima($tugas, $validatedData['penerima_internal'] ?? [], $validatedData['penerima_eksternal'] ?? []);
            }

            if ($oldStatus === 'draft' && $newStatus === 'pending') {
                $this->notificationService->notifyApprovalRequest($tugas);
            }

            if ($oldStatus !== $newStatus) {
                logStatusChange(DB::connection(), (int) $tugas->id, $oldStatus, $newStatus);
            }

            return $tugas;
        });
    }

    /**
     * Approve surat tugas
     */
    public function approveTugas(TugasHeader $tugas, array $validatedData): TugasHeader
    {
        return DB::transaction(function () use ($tugas, $validatedData) {
            $table = $tugas->getTable();

            $update = [
                'tanggal_surat' => $tugas->tanggal_surat ?? now()->toDateString(),
                'status_surat' => 'disetujui',
                'signed_at' => now(),
                'next_approver' => null,
                'nomor_status' => 'locked', // 🔒 kunci nomor saat approve
                'dikunci_pada' => now(),
            ];

            // ✅ FIX: Only update size values if explicitly provided
            // This preserves values already saved by controller
            if (array_key_exists('ttd_w_mm', $validatedData)) {
                $update['ttd_w_mm'] = (int) $validatedData['ttd_w_mm'];
            }
            if (array_key_exists('cap_w_mm', $validatedData)) {
                $update['cap_w_mm'] = (int) $validatedData['cap_w_mm'];
            }
            if (array_key_exists('cap_opacity', $validatedData)) {
                $update['cap_opacity'] = (float) $validatedData['cap_opacity'];
            }

            // Simpan penandatangan ke kolom yang tersedia
            if (Schema::hasColumn($table, 'penandatangan_id')) {
                $update['penandatangan_id'] = Auth::id();
            } elseif (Schema::hasColumn($table, 'penandatangan')) {
                $update['penandatangan'] = Auth::id();
            }

            $tugas->update($update);

            $this->notificationService->notifyApproved($tugas);

            // Audit trail
            logStatusChange(DB::connection(), (int) $tugas->id, 'pending', 'disetujui');

            return $tugas;
        });
    }

    /**
     * Reject surat tugas
     */
    public function rejectTugas(TugasHeader $tugas, string $alasan): TugasHeader
    {
        return DB::transaction(function () use ($tugas, $alasan) {
            $oldStatus = $tugas->status_surat;
            
            // Clean reason
            $alasan = sanitize_input($alasan);

            $tugas->update([
                'status_surat' => 'ditolak',
                'next_approver' => null,
                'alasan_penolakan' => $alasan,
            ]);

            // Notify
            $this->notificationService->notifyRejected($tugas, $alasan);

            // Audit trail (AuditService handles logReject, but here we use logStatusChange helper for legacy support + AuditService in Observer)
            // Note: Observer should handle AuditService logging if configured.
            // But let's check TugasHeaderObserver again. It logs status change but doesn't explicitly call logReject with reason for TugasHeader yet (it did for KeputusanHeader).
            // Let's add manual logStatusChange for now as per existing pattern.
            logStatusChange(DB::connection(), (int) $tugas->id, $oldStatus, 'ditolak');

            return $tugas;
        });
    }

    /**
     * Sinkron penerima (internal dan eksternal)
     * - internal: array of user_id
     * - eksternal: array of ['nama','jabatan','instansi']
     */
    private function syncPenerima(TugasHeader $tugas, array $internalIds, array $eksternalData): void
    {
        // Bersihkan dulu penerima lama
        $tugas->penerima()->delete();

        // Internal (FK aman)
        foreach ($internalIds as $uid) {
            $userId = validate_integer_id($uid);
            if ($userId) {
                TugasPenerima::create([
                    'tugas_id' => $tugas->id,
                    'pengguna_id' => $userId,
                ]);
            }
        }

        // Eksternal (sanitasi teks)
        foreach ($eksternalData as $p) {
            $nama = sanitize_input($p['nama'] ?? '', 100);
            if ($nama) {
                TugasPenerima::create([
                    'tugas_id' => $tugas->id,
                    'pengguna_id' => null,
                    'nama_penerima' => $nama,
                    'jabatan_penerima' => sanitize_input($p['jabatan'] ?? null, 100),
                    'instansi' => sanitize_input($p['instansi'] ?? null, 150),
                ]);
            }
        }
    }

    /**
     * Normalisasi segmen penerima (whitelist)
     */
    private function resolveSegmenPenerima(?string $rawInput): ?string
    {
        if (! $rawInput) {
            return null;
        }

        $allowed = ['dosen', 'tendik', 'mahasiswa'];
        $raw = mb_strtolower(trim($rawInput));

        foreach ($allowed as $segment) {
            if (Str::contains($raw, $segment)) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * Pastikan bulan romawi valid dari input angka/romawi
     */
    /**
     * Pastikan bulan romawi valid dari input angka/romawi
     */
    private function ensureBulanRomawi($value): string
    {
        $value = strtoupper(trim((string) $value));
        $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        if (in_array($value, $romans, true)) {
            return $value;
        }

        $n = validate_integer_id($value);
        if ($n && $n >= 1 && $n <= 12) {
            return $this->nomorService->toRoman($n);
        }

        return 'I'; // Default fallback
    }

    /**
     * Helper: set $data[$column] hanya jika kolom ada di tabel & value tidak null
     */
    private function putIfColumnExists(array &$data, string $table, string $column, $value): void
    {
        if (! is_null($value) && Schema::hasColumn($table, $column)) {
            $data[$column] = $value;
        }
    }
}
