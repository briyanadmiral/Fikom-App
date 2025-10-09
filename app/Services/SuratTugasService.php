<?php

namespace App\Services;

use App\Models\TugasHeader;
use App\Models\TugasPenerima;
use App\Models\JenisTugas;
use App\Models\SubTugas;
use App\Models\TugasDetail;
use App\Models\KlasifikasiSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** tambahkan import */
use App\Services\NomorSuratService;

class SuratTugasService
{
    protected SuratTugasNotificationService $notificationService;

    /** service penomoran opsional (fallback) */
    protected ?NomorSuratService $nomorService;

    public function __construct(
        SuratTugasNotificationService $notificationService,
        ?NomorSuratService $nomorService = null
    ) {
        $this->notificationService = $notificationService;
        $this->nomorService = $nomorService ?? app(NomorSuratService::class);
    }

    public function createTugas(array $validatedData, string $mode): TugasHeader
    {
        return DB::transaction(function () use ($validatedData, $mode) {
            $detailId = $this->resolveDetailTugasId($validatedData['tugas'], $validatedData['jenis_tugas']);
            if (!$detailId) {
                throw new \Exception('Mapping detail tugas tidak ditemukan.');
            }

            $status = $mode === 'submit' ? 'pending' : 'draft';
            $nextApprover = $mode === 'submit' ? $validatedData['penandatangan'] : null;

            // 🔁 Fallback: siapkan nomor otomatis bila kosong
            $nomor = trim((string)($validatedData['nomor'] ?? ''));
            if ($nomor === '') {
                $kodeKlas = optional(KlasifikasiSurat::find($validatedData['klasifikasi_surat_id']))->kode ?? 'B.10.1';
                $bulanR   = $this->ensureBulanRomawi($validatedData['bulan'] ?? 'I');
                $tahun    = (int) ($validatedData['tahun'] ?? date('Y'));
                $unit     = 'TG'; // sesuaikan jika kamu punya mapping unit dari asal_surat

                $res   = $this->nomorService->reserve($unit, $kodeKlas, $bulanR, $tahun);
                $nomor = $res['nomor'];
            }

            // Menentukan segmen penerima
            $segmen = $this->resolveSegmenPenerima($validatedData['status_penerima'] ?? null);

            $tugas = TugasHeader::create([
                'nomor'               => $nomor,
                'nomor_status'        => 'reserved',
                'bulan'               => $validatedData['bulan'],
                'tahun'               => $validatedData['tahun'],
                'semester'            => $validatedData['semester'],
                'nama_umum'           => $validatedData['nama_umum'],
                'klasifikasi_surat_id'=> $validatedData['klasifikasi_surat_id'],
                'status_surat'        => $status,
                'dibuat_oleh'         => Auth::id(),
                'nama_pembuat'        => $validatedData['nama_pembuat'],
                'asal_surat'          => $validatedData['asal_surat'],
                'jenis_tugas'         => $validatedData['jenis_tugas'],
                'tugas'               => $validatedData['tugas'],
                'detail_tugas'        => $validatedData['detail_tugas'] ?? null,
                'detail_tugas_id'     => $detailId,
                'status_penerima'     => $segmen,
                'redaksi_pembuka'     => $validatedData['redaksi_pembuka'] ?? null,
                'penutup'             => $validatedData['penutup'] ?? null,
                'waktu_mulai'         => $validatedData['waktu_mulai'],
                'waktu_selesai'       => $validatedData['waktu_selesai'],
                'tempat'              => $validatedData['tempat'],
                'penandatangan'       => $validatedData['penandatangan'],
                'next_approver'       => $nextApprover,
                'tembusan'            => $validatedData['tembusan'] ?? null,
                'submitted_at'        => ($status === 'pending') ? now() : null,
            ]);

            $this->syncPenerima($tugas, $validatedData['penerima_internal'] ?? [], $validatedData['penerima_eksternal'] ?? []);

            if ($status === 'pending') {
                $this->notificationService->notifyApprovalRequest($tugas);
            }

            return $tugas;
        });
    }

    public function updateTugas(TugasHeader $tugas, array $validatedData, string $mode): TugasHeader
    {
        return DB::transaction(function () use ($tugas, $validatedData, $mode) {
            // 🔒 hard-guard: tidak boleh update kalau sudah locked
            if ($tugas->nomor_status === 'locked') {
                throw new \RuntimeException('Surat sudah terkunci (locked) dan tidak dapat diubah.');
            }

            $oldStatus   = $tugas->status_surat;
            $newStatus   = $oldStatus;
            $nextApprover = $tugas->next_approver;

            if ($mode === 'submit' && $oldStatus === 'draft') {
                $newStatus    = 'pending';
                $nextApprover = $validatedData['penandatangan'] ?? null;
            }

            // 🔁 Fallback nomor saat berubah ke pending & nomor kosong
            $nomor = trim((string)($validatedData['nomor'] ?? ''));
            if ($nomor === '' && $oldStatus === 'draft' && $newStatus === 'pending') {
                $kodeKlas = optional(KlasifikasiSurat::find($validatedData['klasifikasi_surat_id']))->kode ?? 'B.10.1';
                $bulanR   = $this->ensureBulanRomawi($validatedData['bulan'] ?? 'I');
                $tahun    = (int) ($validatedData['tahun'] ?? date('Y'));
                $unit     = 'TG';
                $res   = $this->nomorService->reserve($unit, $kodeKlas, $bulanR, $tahun);
                $nomor = $res['nomor'];
            }

            $segmen = $this->resolveSegmenPenerima($validatedData['status_penerima'] ?? null);

            $tugas->update([
                'nomor'               => $nomor !== '' ? $nomor : $tugas->nomor,
                'bulan'               => $validatedData['bulan'],
                'tahun'               => $validatedData['tahun'],
                'semester'            => $validatedData['semester'],
                'nama_umum'           => $validatedData['nama_umum'],
                'klasifikasi_surat_id'=> $validatedData['klasifikasi_surat_id'],
                'status_surat'        => $newStatus,
                'nama_pembuat'        => $validatedData['nama_pembuat'],
                'asal_surat'          => $validatedData['asal_surat'],
                'jenis_tugas'         => $validatedData['jenis_tugas'],
                'tugas'               => $validatedData['tugas'],
                'detail_tugas'        => $validatedData['detail_tugas'] ?? null,
                'status_penerima'     => $segmen,
                'redaksi_pembuka'     => $validatedData['redaksi_pembuka'] ?? null,
                'penutup'             => $validatedData['penutup'] ?? null,
                'penandatangan'       => $validatedData['penandatangan'] ?? null,
                'next_approver'       => $nextApprover,
                'waktu_mulai'         => $validatedData['waktu_mulai'] ?? null,
                'waktu_selesai'       => $validatedData['waktu_selesai'] ?? null,
                'tempat'              => $validatedData['tempat'] ?? null,
                'tembusan'            => $validatedData['tembusan'] ?? null,
                'submitted_at'        => ($oldStatus === 'draft' && $newStatus === 'pending') ? now() : $tugas->submitted_at,
            ]);

            $this->syncPenerima($tugas, $validatedData['penerima_internal'] ?? [], $validatedData['penerima_eksternal'] ?? []);

            if ($oldStatus === 'draft' && $newStatus === 'pending') {
                $this->notificationService->notifyApprovalRequest($tugas);
            }

            return $tugas;
        });
    }

    public function approveTugas(TugasHeader $tugas, array $validatedData): TugasHeader
    {
        return DB::transaction(function () use ($tugas, $validatedData) {
            $tugas->update([
                'ttd_w_mm'     => $validatedData['ttd_w_mm'],
                'cap_w_mm'     => $validatedData['cap_w_mm'],
                'cap_opacity'  => $validatedData['cap_opacity'],
                'tanggal_surat'=> $tugas->tanggal_surat ?? now()->toDateString(),
                'status_surat' => 'disetujui',
                'penandatangan'=> Auth::id(),
                'signed_at'    => now(),
                'next_approver'=> null,
                'nomor_status' => 'locked',     // 🔒 kunci nomor saat approve
                'dikunci_pada' => now(),
            ]);

            $this->notificationService->notifyApproved($tugas);

            return $tugas;
        });
    }

    private function syncPenerima(TugasHeader $tugas, array $internalIds, array $eksternalData): void
    {
        $tugas->penerima()->delete();

        foreach ($internalIds as $uid) {
            TugasPenerima::create(['tugas_id' => $tugas->id, 'pengguna_id' => $uid]);
        }

        foreach ($eksternalData as $p) {
            if (!empty($p['nama'])) {
                TugasPenerima::create([
                    'tugas_id'         => $tugas->id,
                    'pengguna_id'      => null,
                    'nama_penerima'    => $p['nama'],
                    'jabatan_penerima' => $p['jabatan'] ?? null,
                    'instansi'         => $p['instansi'] ?? null,
                ]);
            }
        }
    }

    /** Helper baru untuk memproses status_penerima */
    private function resolveSegmenPenerima(?string $rawInput): ?string
    {
        if (!$rawInput) return null;

        $raw = mb_strtolower($rawInput);
        foreach (['dosen', 'tendik', 'mahasiswa'] as $opt) {
            if (Str::contains($raw, $opt)) {
                return $opt;
            }
        }
        return null;
    }

    /** pastikan bulan romawi valid dari input angka/romawi */
    private function ensureBulanRomawi($value): string
    {
        $romans = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $upper  = strtoupper(trim((string)$value));
        if (in_array($upper, $romans, true)) return $upper;

        $n = (int) $value;
        if ($n >= 1 && $n <= 12) return $romans[$n-1];
        return 'I';
    }

    /**
     * Pemetaan cerdas dari nama sub_tugas -> detail_tugas_id.
     */
    private function resolveDetailTugasId(?string $tugasNama, ?string $jenisTugas): ?int
    {
        $name = trim((string) $tugasNama);
        if ($name === '') {
            \Log::warning('resolveDetailTugasId: tugasNama kosong');
            return null;
        }

        $jenisId = null;
        if (!empty($jenisTugas)) {
            try {
                $jenisId = optional(JenisTugas::whereRaw('LOWER(nama) = ?', [mb_strtolower($jenisTugas)])->first())->id;
            } catch (\Throwable $e) {
                try {
                    $jenisId = DB::table('jenis_tugas')->whereRaw('LOWER(nama) = ?', [mb_strtolower($jenisTugas)])->value('id');
                } catch (\Throwable $e2) { /* noop */ }
            }
        }

        $sub = null;
        try {
            $q = SubTugas::query()->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)]);
            if ($jenisId) $q->where('jenis_tugas_id', $jenisId);
            $sub = $q->first();
        } catch (\Throwable $e) {
            try {
                $q = DB::table('sub_tugas')->whereRaw('LOWER(nama) = ?', [mb_strtolower($name)]);
                if ($jenisId) $q->where('jenis_tugas_id', $jenisId);
                $row = $q->first();
                if ($row) $sub = (object) ['id' => $row->id, 'jenis_tugas_id' => $row->jenis_tugas_id, 'nama' => $row->nama];
            } catch (\Throwable $e2) { /* noop */ }
        }

        if (!$sub) {
            try {
                $q = SubTugas::query()->where('nama', 'LIKE', '%' . $name . '%');
                if ($jenisId) $q->where('jenis_tugas_id', $jenisId);
                $sub = $q->first();
            } catch (\Throwable $e) {
                try {
                    $q2 = DB::table('sub_tugas')->where('nama', 'LIKE', '%' . $name . '%');
                    if ($jenisId) $q2->where('jenis_tugas_id', $jenisId);
                    $row = $q2->first();
                    if ($row) $sub = (object) ['id' => $row->id, 'jenis_tugas_id' => $row->jenis_tugas_id, 'nama' => $row->nama];
                } catch (\Throwable $e2) { /* noop */ }
            }
        }

        if ($sub && isset($sub->id)) {
            $detail = null;
            $cariKataKunci = [
                'jurnal nasional',
                'artikel jurnal nasional',
                'artikel nasional',
                'reviewer jurnal nasional',
                'review jurnal nasional',
                'review artikel nasional',
                'publikasi nasional'
            ];
            try {
                $dq = TugasDetail::query()->where('sub_tugas_id', $sub->id);
                foreach ($cariKataKunci as $kw) {
                    $try = (clone $dq)->whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($kw) . '%'])->first();
                    if ($try) { $detail = $try; break; }
                }
                if (!$detail) $detail = TugasDetail::where('sub_tugas_id', $sub->id)->orderBy('id')->first();
            } catch (\Throwable $e) {
                try {
                    foreach ($cariKataKunci as $kw) {
                        $row = DB::table('tugas_detail')
                            ->where('sub_tugas_id', $sub->id)
                            ->whereRaw('LOWER(nama) LIKE ?', ['%' . mb_strtolower($kw) . '%'])
                            ->orderBy('id')->first();
                        if ($row) { $detail = (object) ['id' => $row->id]; break; }
                    }
                    if (!$detail) {
                        $row = DB::table('tugas_detail')->where('sub_tugas_id', $sub->id)->orderBy('id')->first();
                        if ($row) $detail = (object) ['id' => $row->id];
                    }
                } catch (\Throwable $e2) { /* noop */ }
            }

            if ($detail && isset($detail->id)) {
                return (int) $detail->id;
            }
        }

        try {
            $lainnya = TugasDetail::whereRaw('LOWER(nama) = ?', ['lainnya'])->value('id');
            if ($lainnya) return (int) $lainnya;
        } catch (\Throwable $e) {
            try {
                $lainnya = DB::table('tugas_detail')->whereRaw('LOWER(nama) = ?', ['lainnya'])->value('id');
                if ($lainnya) return (int) $lainnya;
            } catch (\Throwable $e2) { /* noop */ }
        }

        try {
            $minId = TugasDetail::min('id');
            if ($minId) return (int) $minId;
        } catch (\Throwable $e) {
            try {
                $minId = DB::table('tugas_detail')->min('id');
                if ($minId) return (int) $minId;
            } catch (\Throwable $e2) { /* noop */ }
        }

        \Log::warning('resolveDetailTugasId: gagal memetakan, semua fallback habis', ['tugas' => $name, 'jenis' => $jenisTugas]);
        return null;
    }
}
