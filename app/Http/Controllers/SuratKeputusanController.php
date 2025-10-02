<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeputusanRequest;
use App\Http\Requests\UpdateKeputusanRequest;
use App\Models\KeputusanHeader;
use App\Models\MasterKopSurat;
use App\Services\NomorSuratService;
use App\Services\SkNotifikasiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class SuratKeputusanController extends Controller
{
    /* ==================== Helpers umum ==================== */

    /** Angka → Romawi (1..12) */
    private function toRoman($number)
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $ret = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $ret .= $roman;
                    break;
                }
            }
        }
        return $ret;
    }

    /** Bangun HTML kolom `memutuskan` dari array `menetapkan` */
    private function buildMemutuskanHtml(?array $menetapkan): string
    {
        $menetapkan = $menetapkan ?? [];
        if (empty($menetapkan)) return '';
        $parts = [];
        foreach ($menetapkan as $d) {
            $judul = strtoupper(trim($d['judul'] ?? ''));
            $isi   = $d['isi'] ?? '';
            if ($judul === '' && trim(strip_tags($isi)) === '') continue;
            $parts[] = '<p><strong>' . e($judul) . ':</strong> ' . $isi . '</p>';
        }
        return implode("\n", $parts);
    }

    /** Dependency untuk form (penandatangan, user tembusan, dll) */
    private function getFormDependencies(): array
    {
        $admins  = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat = \App\Models\User::whereIn('peran_id', [2, 3])->orderBy('nama_lengkap')->get();
        $users   = \App\Models\User::where('peran_id', '!=', 1)->orderBy('nama_lengkap')->get();
        $klasifikasi = \App\Models\KlasifikasiSurat::orderBy('kode')->get();
        return compact('admins', 'pejabat', 'users', 'klasifikasi');
    }

    /** Apakah PDF harus menampilkan TTD/Cap */
    private function shouldShowSignatures(KeputusanHeader $sk): bool
    {
        return ($sk->status_surat === 'disetujui') && !empty($sk->signed_at);
    }

    /* ==================== Daftar / List ==================== */

    public function index()
    {
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima'])
            ->orderByDesc('created_at')->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        $mode = 'list';
        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    public function approveList()
    {
        $list = KeputusanHeader::with(['pembuat', 'penerima'])
            ->where('status_surat', 'pending')
            ->where('penandatangan', Auth::id())
            ->orderByDesc('created_at')->get();

        $stats = ['draft' => 0, 'pending' => $list->count(), 'disetujui' => 0];
        $mode  = 'approve-list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    public function mine()
    {
        $userId = Auth::id();
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima'])
            ->whereHas('penerima', function ($query) use ($userId) {
                $query->where('pengguna_id', $userId);
            })
            ->orderByDesc('created_at')->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_keputusan.keputusan_saya', compact('list', 'stats'));
    }

    /* ==================== Create / Edit ==================== */

    public function create()
    {
        $this->authorize('create', KeputusanHeader::class);
        $deps = $this->getFormDependencies();
        return view('surat_keputusan.create', $deps);
    }

    public function store(StoreKeputusanRequest $request)
    {
        $this->authorize('create', KeputusanHeader::class);

        return DB::transaction(function () use ($request) {
            $data    = $request->validated();
            $modeRaw = $request->input('mode');
            $status  = $this->mapModeToStatus($modeRaw);

            $data['status_surat'] = $status;
            unset($data['mode']);

            // Wajib penandatangan jika diajukan
            if ($status === 'pending' && empty($data['penandatangan'])) {
                return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])->withInput();
            }

            // Sanitasi diktum
            if (array_key_exists('menetapkan', $data) && !empty($data['menetapkan'])) {
                foreach ($data['menetapkan'] as &$d) {
                    $rawIsi  = $d['isi'] ?? ($d['konten'] ?? '');
                    $d['isi'] = Purifier::clean($rawIsi);
                    unset($d['konten']);
                }
                // Hapus baris ini: $data['memutuskan'] = $this->buildMemutuskanHtml($data['menetapkan']);
            }

            // Normalisasi tembusan
            if (array_key_exists('tembusan', $data)) {
                if (is_string($data['tembusan']) && strtolower(trim($data['tembusan'])) === 'null') {
                    $data['tembusan'] = null;
                } elseif (is_array($data['tembusan'])) {
                    $data['tembusan'] = implode(', ', array_filter(array_map('trim', $data['tembusan'])));
                }
            }

            // Ambil penerima_internal
            $recipients = $data['penerima_internal'] ?? [];
            unset($data['penerima_internal']);

            // Kolom wajib
            // $data['memutuskan']  = $this->buildMemutuskanHtml($data['menetapkan'] ?? []);
            $data['dibuat_oleh'] = Auth::id();

            // Simpan header
            $sk = KeputusanHeader::create($data);

            // Simpan penerima internal
            if (!empty($recipients)) {
                $ids = array_values(array_unique(array_map('intval', (array) $recipients)));
                $now = now();
                $rows = [];
                foreach ($ids as $pid) {
                    $rows[] = [
                        'keputusan_id' => $sk->id,
                        'pengguna_id'  => $pid,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                        'dibaca'       => 0,
                    ];
                }
                if ($rows) DB::table('keputusan_penerima')->insert($rows);
            }

            // Redirect + notifikasi
            if ($status === 'draft') {
                return redirect()->route('surat_keputusan.index')->with('success', 'Draft SK disimpan.');
            }

            app(SkNotifikasiService::class)->notifyApprovalRequest($sk);
            return redirect()->route('surat_keputusan.edit', $sk)->with('success', 'SK diajukan ke penandatangan.');
        });
    }

    public function edit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('update', $surat_keputusan);
        $deps = $this->getFormDependencies();
        return view('surat_keputusan.edit', array_merge($deps, ['sk' => $surat_keputusan]));
    }

    public function update(UpdateKeputusanRequest $request, KeputusanHeader $surat_keputusan)
{
    $this->authorize('update', $surat_keputusan);

    // Simpan status awal: apakah sebelumnya pending?
    $wasPending = $surat_keputusan->status_surat === 'pending';

    return DB::transaction(function () use ($request, $surat_keputusan, $wasPending) {
        $data    = $request->validated();
        $modeRaw = $request->input('mode');     // boleh null (Simpan biasa)
        $status  = $this->mapModeToStatus($modeRaw);

        // Wajib penandatangan kalau diajukan
        if ($status === 'pending' && empty($data['penandatangan']) && empty($surat_keputusan->penandatangan)) {
            return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])->withInput();
        }

        // Hanya set status kalau ada 'mode' dari UI
        if (in_array($modeRaw, ['draft', 'pending', 'terkirim'])) {
            $data['status_surat'] = $status;
        }
        unset($data['mode']);

        // Sanitasi diktum
        if (array_key_exists('menetapkan', $data) && !empty($data['menetapkan'])) {
            foreach ($data['menetapkan'] as &$d) {
                $rawIsi  = $d['isi'] ?? ($d['konten'] ?? '');
                $d['isi'] = Purifier::clean($rawIsi);
                unset($d['konten']);
            }
            $data['memutuskan'] = $this->buildMemutuskanHtml($data['menetapkan']);
        }

        // Normalisasi tembusan
        if (array_key_exists('tembusan', $data)) {
            if (is_string($data['tembusan']) && strtolower(trim($data['tembusan'])) === 'null') {
                $data['tembusan'] = null;
            } elseif (is_array($data['tembusan'])) {
                $data['tembusan'] = implode(', ', array_filter(array_map('trim', $data['tembusan'])));
            }
        }

        // Sinkron penerima internal
        $recipients = null;
        if (array_key_exists('penerima_internal', $data)) {
            $recipients = array_values(array_unique(array_map('intval', (array) $data['penerima_internal'])));
            unset($data['penerima_internal']);
        }

        $surat_keputusan->update($data);

        if (is_array($recipients)) {
            DB::table('keputusan_penerima')->where('keputusan_id', $surat_keputusan->id)->delete();
            if (!empty($recipients)) {
                $now = now();
                DB::table('keputusan_penerima')->insert(array_map(fn($pid) => [
                    'keputusan_id' => $surat_keputusan->id,
                    'pengguna_id'  => $pid,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                    'dibaca'       => 0,
                ], $recipients));
            }
        }

        // Jika dokumen tetap "pending" dan sebelumnya juga "pending" → beri tahu penandatangan bahwa ada revisi
        $freshStatus   = $surat_keputusan->fresh()->status_surat;
        $stillPending  = ($modeRaw === null && $freshStatus === 'pending') || ($modeRaw !== null && $status === 'pending');

        if ($wasPending && $stillPending && $surat_keputusan->penandatangan) {
            // ... (logika notifikasi revisi tidak perlu diubah, sudah benar)
            if (method_exists(app(SkNotifikasiService::class), 'notifyRevised')) {
                app(SkNotifikasiService::class)->notifyRevised($surat_keputusan, auth()->user());
            } else {
                DB::table('notifikasi')->insert([
                    'pengguna_id'  => (int) $surat_keputusan->penandatangan,
                    'tipe'         => 'surat_keputusan',
                    'referensi_id' => (int) $surat_keputusan->id,
                    'pesan'        => 'SK ' . ($surat_keputusan->nomor ?? '(tanpa nomor)') .
                        ' telah direvisi oleh ' . auth()->user()->nama_lengkap . '.',
                    'dibaca'       => 0,
                    'dibuat_pada'  => now(),
                ]);
            }
        }

        // --- AWAL PERBAIKAN LOGIKA REDIRECT ---

        // 1. Handle redirect untuk tombol-tombol dengan 'mode' eksplisit
        if ($modeRaw === 'draft') {
            return redirect()->route('surat_keputusan.index')->with('success', 'Draft SK disimpan.');
        }

        if ($modeRaw === 'pending' || $modeRaw === 'terkirim') {
            app(SkNotifikasiService::class)->notifyApprovalRequest($surat_keputusan);
            return redirect()->route('surat_keputusan.edit', $surat_keputusan)
                ->with('success', 'Perubahan disimpan & SK diajukan.');
        }

        if ($modeRaw === 'revisi_dan_setujui') {
            return redirect()->route('surat_keputusan.approveForm', $surat_keputusan)
                ->with('success', 'Perubahan berhasil disimpan. Silakan lanjutkan persetujuan.');
        }

        // 2. Handle redirect untuk 'Simpan Perubahan' (mode = null) berdasarkan peran
        $user = Auth::user();
        $message = 'Perubahan berhasil disimpan.';

        // Jika yang menyimpan adalah Penandatangan (peran 2/3), kembalikan ke daftar approve.
        if (in_array((int)$user->peran_id, [2, 3], true)) {
            return redirect()->route('surat_keputusan.approveList')->with('success', $message);
        }

        // Default untuk peran lain (seperti Admin TU), kembalikan ke daftar utama.
        return redirect()->route('surat_keputusan.index')->with('success', $message);
        
        // --- AKHIR PERBAIKAN LOGIKA REDIRECT ---
    });
}

    /* ==================== Workflow ==================== */

    public function submit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('submit', $surat_keputusan);
        $surat_keputusan->update(['status_surat' => 'pending']);
        app(SkNotifikasiService::class)->notifyApprovalRequest($surat_keputusan);
        return back()->with('success', 'Dikirim untuk persetujuan.');
    }

    public function approveForm(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $assets = $this->getSigningAssets($surat_keputusan);
        $preview = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm'      => $surat_keputusan->ttd_w_mm ?? $assets['ttdW'],
            'cap_w_mm'      => $surat_keputusan->cap_w_mm ?? $assets['capW'],
            'cap_opacity'   => $surat_keputusan->cap_opacity ?? $assets['capOpacity'],
        ];

        return view('surat_keputusan.approve', [
            'sk'          => $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima']),
            'kop'         => $assets['kop'],
            'preview'     => $preview,
            'ttdW'        => $preview['ttd_w_mm'],
            'capW'        => $preview['cap_w_mm'],
            'capOpacity'  => $preview['cap_opacity'],
            'ttdImageB64' => $assets['ttdImageB64'],
            'capImageB64' => $assets['capImageB64'],
            'showSigns'   => true,
        ]);
    }

    public function approvePreview(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $assets     = $this->getSigningAssets($surat_keputusan);
        $ttdW       = (int) $request->input('ttd_w_mm', $surat_keputusan->ttd_w_mm ?? $assets['ttdW']);
        $capW       = (int) $request->input('cap_w_mm', $surat_keputusan->cap_w_mm ?? $assets['capW']);
        $capOpacity = (float) $request->input('cap_opacity', $surat_keputusan->cap_opacity ?? $assets['capOpacity']);

        return view('surat_keputusan.partials.approve-preview', [
            'sk'          => $surat_keputusan,
            'kop'         => $assets['kop'],
            'showSigns'   => true,
            'ttdImageB64' => $assets['ttdImageB64'],
            'capImageB64' => $assets['capImageB64'],
            'ttdW'        => $ttdW,
            'capW'        => $capW,
            'capOpacity'  => $capOpacity,
        ]);
    }

    public function approve(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $validated = $request->validate([
            'ttd_w_mm'        => 'required|integer|min:30|max:60',
            'cap_w_mm'        => 'required|integer|min:25|max:45',
            'cap_opacity'     => 'required|numeric|min:0.7|max:1.0',
            'kode_klasifikasi' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $surat_keputusan->ttd_w_mm    = $validated['ttd_w_mm'];
            $surat_keputusan->cap_w_mm    = $validated['cap_w_mm'];
            $surat_keputusan->cap_opacity = $validated['cap_opacity'];

            if (empty($surat_keputusan->tanggal_surat)) {
                $surat_keputusan->tanggal_surat = now()->toDateString();
            }

            if (empty($surat_keputusan->nomor)) {
                $tahun = (int) now()->format('Y');
                $bulanR = $this->toRoman((int) now()->format('n'));
                $kodeUnit = 'SK';
                $kodeKlasifikasi = $validated['kode_klasifikasi'] ?? 'B.10.1';
                $res = app(NomorSuratService::class)->reserve($kodeUnit, $kodeKlasifikasi, $bulanR, $tahun);
                $surat_keputusan->nomor = $res['nomor'];
            }

            $surat_keputusan->status_surat  = 'disetujui';
            $surat_keputusan->approved_by   = Auth::id();
            $surat_keputusan->approved_at   = now();
            $surat_keputusan->penandatangan = Auth::id();
            $surat_keputusan->signed_at     = now();
            $surat_keputusan->save();

            $pdfBytes = $this->renderSkPdfWithSign($surat_keputusan);
            $pdfPath  = "private/surat_keputusan/signed/{$surat_keputusan->id}_" . md5((string)($surat_keputusan->nomor ?? '')) . ".pdf";
            Storage::disk('local')->put($pdfPath, $pdfBytes);

            $surat_keputusan->signed_pdf_path = $pdfPath;
            $surat_keputusan->save();

            app(SkNotifikasiService::class)->notifyApproved($surat_keputusan);

            DB::commit();
            
            // [PERUBAIKAN] Arahkan ke daftar approve, bukan back()
            return redirect()->route('surat_keputusan.approveList')
                ->with('success', 'SK ' . $surat_keputusan->nomor . ' berhasil disetujui.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Gagal approve SK #' . $surat_keputusan->id, ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyetujui SK.');
        }
    }

    public function reject(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('reject', $surat_keputusan);

        $note = trim((string) $request->input('note'));

        $surat_keputusan->update([
            'status_surat' => 'ditolak',
            'rejected_by'  => Auth::id(),
            'rejected_at'  => now(),
        ]);

        // Notifikasi ke pembuat
        if (method_exists(app(SkNotifikasiService::class), 'notifyRejected')) {
            app(SkNotifikasiService::class)->notifyRejected($surat_keputusan, $note);
        } else {
            DB::table('notifikasi')->insert([
                'pengguna_id'  => $surat_keputusan->dibuat_oleh,
                'tipe'         => 'surat_keputusan',
                'referensi_id' => $surat_keputusan->id,
                'pesan'        => 'Surat Keputusan ' . ($surat_keputusan->nomor ?: '(tanpa nomor)') .
                    ' ditolak.' . ($note ? ' Catatan: ' . $note : ''),
                'dibaca'       => 0,
                'dibuat_pada'  => now(),
            ]);
        }

        return back()->with('success', 'SK ditolak dan dikembalikan ke pembuat' . ($note ? ' (catatan dikirim).' : '.'));
    }

    /** Tarik kembali ke Draft untuk direvisi (oleh peran 1/Admin, misalnya) */
    public function reopen(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('reopen', $surat_keputusan);

        DB::transaction(function () use ($surat_keputusan) {
            $surat_keputusan->update([
                'status_surat'     => 'draft',
                'approved_by'      => null,
                'approved_at'      => null,
                'rejected_by'      => null,
                'rejected_at'      => null,
                'signed_at'        => null,
                'signed_pdf_path'  => null,
            ]);

            // (Opsional) kabari penandatangan kalau ada
            if ($surat_keputusan->penandatangan) {
                DB::table('notifikasi')->insert([
                    'pengguna_id'  => (int) $surat_keputusan->penandatangan,
                    'tipe'         => 'surat_keputusan',
                    'referensi_id' => (int) $surat_keputusan->id,
                    'pesan'        => 'SK ' . ($surat_keputusan->nomor ?? '(tanpa nomor)') .
                        ' ditarik ke Draft oleh ' . auth()->user()->nama_lengkap . '.',
                    'dibaca'       => 0,
                    'dibuat_pada'  => now(),
                ]);
            }
        });

        return back()->with('success', 'SK ditarik ke Draft untuk direvisi.');
    }


    public function publish(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('publish', $surat_keputusan);
        $surat_keputusan->update([
            'status_surat' => 'terbit',
            'published_by' => Auth::id(),
            'published_at' => now(),
        ]);
        return back()->with('success', 'SK diterbitkan.');
    }

    public function archive(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('archive', $surat_keputusan);
        $surat_keputusan->update(['status_surat' => 'arsip']);
        return back()->with('success', 'SK diarsipkan.');
    }

    /* ==================== PDF & Preview ==================== */

    public function downloadPdf(KeputusanHeader $surat_keputusan)
    {
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima']);
        $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string) ($surat_keputusan->nomor ?? 'TanpaNomor'));

        if ($this->shouldShowSignatures($surat_keputusan)) {
            $bytes = $this->renderSkPdfWithSign($surat_keputusan);
            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="SuratKeputusan_' . $safeNomor . '.pdf"',
            ]);
        }

        $bytes = $this->renderSkPdfDraft($surat_keputusan);
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SuratKeputusan_DRAFT_' . $safeNomor . '.pdf"',
        ]);
    }

    public function preview(KeputusanHeader $surat_keputusan, Request $request)
    {
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima']);
        $assets    = $this->getSigningAssets($surat_keputusan);
        $showSigns = $this->shouldShowSignatures($surat_keputusan);

        return response()->view('surat_keputusan.preview', array_merge(
            ['sk' => $surat_keputusan, 'showSigns' => $showSigns],
            $assets
        ))->header('X-Frame-Options', 'ALLOWALL');
    }

    /* ==================== Private: assets & render ==================== */

    private function getSigningAssets(KeputusanHeader $sk): array
    {
        $ttdImageB64 = null;
        $pen = $sk->penandatanganUser;
        if ($pen && $pen->signature && !empty($pen->signature->ttd_path)) {
            $ttdImageB64 = $this->b64FromStorage($pen->signature->ttd_path);
        }

        $capImageB64 = null;
        $kop = MasterKopSurat::query()->first();
        if ($kop && !empty($kop->cap_path)) {
            $capImageB64 = $this->b64FromStorage($kop->cap_path);
        }

        $ttdW       = $sk->ttd_w_mm ?? ($pen?->signature?->default_width_mm ?? 42);
        $capW       = $sk->cap_w_mm ?? 35;
        $capOpacity = $sk->cap_opacity ?? 0.95;

        return compact('ttdImageB64', 'capImageB64', 'ttdW', 'capW', 'capOpacity', 'kop');
    }

    private function b64FromStorage($pathPublicOrLocal)
    {
        if (!$pathPublicOrLocal) return null;

        if (Storage::disk('local')->exists($pathPublicOrLocal)) {
            $raw = Storage::disk('local')->get($pathPublicOrLocal);
            return 'data:image/png;base64,' . base64_encode($raw);
        }

        $pub = ltrim(preg_replace('#^public/#', '', $pathPublicOrLocal), '/');
        if (Storage::exists('public/' . $pub)) {
            $raw = Storage::get('public/' . $pub);
            return 'data:image/png;base64,' . base64_encode($raw);
        }

        return null;
    }

    private function renderSkPdfWithSign(KeputusanHeader $sk): string
    {
        $assets = $this->getSigningAssets($sk);

        $html = view('surat_keputusan.surat_pdf', array_merge(
            ['sk' => $sk, 'showSigns' => true],
            $assets
        ))->render();

        return Pdf::loadHTML($html)->setPaper('A4', 'portrait')->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'dpi'                  => 96,
            'chroot'               => public_path(),
        ])->output();
    }

    private function renderSkPdfDraft(KeputusanHeader $sk): string
    {
        $kop = MasterKopSurat::query()->first();

        $html = view('surat_keputusan.surat_pdf', [
            'sk' => $sk,
            'kop' => $kop,
            'showSigns' => false,
            'ttdImageB64' => null,
            'capImageB64' => null,
            'ttdW' => null,
            'capW' => null,
            'capOpacity' => null,
        ])->render();

        return Pdf::loadHTML($html)->setPaper('A4', 'portrait')->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'dpi'                  => 96,
            'chroot'               => public_path(),
        ])->output();
    }

    /** Map nilai tombol UI ke status_surat di DB */
    private function mapModeToStatus(?string $mode): string
    {
        return match ($mode) {
            'terkirim' => 'pending',               // tombol "Simpan & Ajukan"
            'draft'    => 'draft',
            'pending', 'disetujui', 'ditolak', 'terbit', 'arsip' => $mode,
            default    => 'draft',
        };
    }
}
