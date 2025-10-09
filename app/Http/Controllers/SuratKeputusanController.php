<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeputusanRequest;
use App\Http\Requests\UpdateKeputusanRequest;
use App\Models\KeputusanHeader;
use App\Models\MasterKopSurat;
use App\Services\NomorSuratService;
use App\Services\SuratKeputusanService;
use App\Services\SuratKeputusanNotificationService;  // ✅ CHANGED: From SkNotifikasiService
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;   // <- NEW: untuk cek kolom ada/tidak
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class SuratKeputusanController extends Controller
{
    protected SuratKeputusanService $skService;

    public function __construct(SuratKeputusanService $skService)
    {
        $this->skService = $skService;
    }

    /* ==================== Helpers umum ==================== */

    private function normalizeTembusan($input): ?string
    {
        if ($input === null || $input === '') return null;

        if (is_array($input)) {
            $arr = $input;
        } else {
            $s = trim((string)$input);
            if (strlen($s) >= 2 && $s[0] === '"' && substr($s, -1) === '"') {
                $s = substr($s, 1, -1);
            }
            $arr = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $pieces = preg_split('/[,\n;]+/', $s);
                $arr = array_map(fn($x) => ['value' => trim($x)], $pieces);
            }
        }

        $names = [];
        foreach ($arr as $it) {
            $names[] = trim(
                is_array($it) ? ($it['value'] ?? $it['text'] ?? $it['name'] ?? (string)reset($it)) : (string)$it
            );
        }
        $names = array_values(array_unique(array_filter($names)));

        return $names ? implode("\n", $names) : null;
    }

    /** Dependency untuk form (hanya yang relevan) */
    private function getFormDependencies(): array
    {
        $admins  = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');

        $pejabat = \App\Models\User::whereIn('peran_id', [2, 3])
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

        // Jika kolom 'status' tidak ada di tabel users/pengguna, ganti/filter sesuai skema kamu
        $users   = \App\Models\User::when(
            Schema::hasColumn((new \App\Models\User)->getTable(), 'status'),
            fn($q) => $q->where('status', 'aktif')
        )
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

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
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap'])
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
        $list = KeputusanHeader::with(['pembuat', 'penerima:id,nama_lengkap'])
            ->where('status_surat', 'pending')
            ->where('penandatangan', Auth::id())
            ->orderByDesc('created_at')->get();

        $stats = ['draft' => 0, 'pending' => $list->count(), 'disetujui' => 0];
        $mode  = 'approve-list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    /** Keputusan saya = SK yang mencantumkan saya sebagai penerima */
    public function mine()
    {
        $userId = Auth::id();
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap'])
            ->whereHas('penerima', fn($q) => $q->whereKey($userId))   // <- FIX: aman utk nama tabel apapun
            ->orderByDesc('created_at')
            ->get();

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

        $validatedData = $request->validated();
        $mode = $request->input('mode');
        $status = ($mode === 'pending' || $mode === 'terkirim') ? 'pending' : 'draft';

        // Validasi Guard sebelum memanggil service
        if ($status === 'pending') {
            if (empty($validatedData['penandatangan'])) {
                return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])->withInput();
            }
            if (empty($validatedData['penerima_internal']) && empty($validatedData['penerima_eksternal'])) {
                return back()->withErrors(['penerima_internal' => 'Minimal satu penerima (internal/eksternal) saat pengajuan.'])->withInput();
            }
        }

        // ✅ INTI PERUBAHAN: Semua logika dipindahkan ke service.
        $sk = $this->skService->createKeputusan($validatedData, $status);

        // Logika redirect tetap di controller
        if ($status === 'draft') {
            return redirect()->route('surat_keputusan.index')->with('success', 'Draft SK berhasil disimpan.');
        }

        return redirect()->route('surat_keputusan.edit', $sk)->with('success', 'SK berhasil diajukan ke penandatangan.');
    }

    public function edit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('update', $surat_keputusan);
        $deps = $this->getFormDependencies();
        $surat_keputusan->load(['penerima:id,nama_lengkap']);
        return view('surat_keputusan.edit', array_merge($deps, ['sk' => $surat_keputusan]));
    }

    public function update(UpdateKeputusanRequest $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('update', $surat_keputusan);

        $validatedData = $request->validated();
        $mode = $request->input('mode');
        $newStatus = $this->mapModeToStatus($mode); // Bisa null jika mode bukan pengubah status

        if ($newStatus === 'pending' && empty($validatedData['penandatangan']) && empty($surat_keputusan->penandatangan)) {
            return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])->withInput();
        }

        // ✅ INTI PERUBAHAN: Logika update dipindahkan ke service.
        $sk = $this->skService->updateKeputusan($surat_keputusan, $validatedData, $newStatus);

        // Logika redirect tetap di controller
        if ($mode === 'draft') {
            return redirect()->route('surat_keputusan.index')->with('success', 'Draft SK disimpan.');
        }
        if ($mode === 'pending' || $mode === 'terkirim') {
            return redirect()->route('surat_keputusan.edit', $sk)->with('success', 'Perubahan disimpan & SK diajukan kembali.');
        }
        if ($mode === 'revisi_dan_setujui') {
            return redirect()->route('surat_keputusan.approveForm', $sk)->with('success', 'Perubahan berhasil disimpan. Silakan lanjutkan persetujuan.');
        }

        return redirect()->route('surat_keputusan.index')->with('success', 'Perubahan berhasil disimpan.');
    }

    /* ==================== Workflow ==================== */

    public function submit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('submit', $surat_keputusan);

        if (!$surat_keputusan->penandatangan) {
            return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi sebelum pengajuan.']);
        }

        // ✅ Cukup panggil service
        $this->skService->submitForApproval($surat_keputusan);

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
            'sk'          => $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']),
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
            'ttd_w_mm' => 'required|integer|min:30|max:60',
            'cap_w_mm' => 'required|integer|min:25|max:45',
            'cap_opacity' => 'required|numeric|min:0.7|max:1.0',
            'kode_klasifikasi' => 'nullable|string|max:20',
            'unit' => 'nullable|string|max:20',
        ]);

        try {
            // ✅ INTI PERUBAHAN: Panggil service untuk proses approval dan penomoran.
            $sk = $this->skService->approveAndGenerateNumber($surat_keputusan, $validated);

            // Logika PDF dan penyimpanan file tetap di controller karena berhubungan dengan response
            $pdfBytes = $this->renderSkPdfWithSign($sk);
            $pdfPath  = "private/surat_keputusan/signed/{$sk->id}_" . md5((string)($sk->nomor ?? '')) . ".pdf";
            Storage::disk('local')->put($pdfPath, $pdfBytes);
            $sk->update(['signed_pdf_path' => $pdfPath]); // Update path PDF setelah file dibuat

            return redirect()->route('surat_keputusan.approveList')
                ->with('success', 'SK ' . $sk->nomor . ' berhasil disetujui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Gagal approve SK #' . $surat_keputusan->id, ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyetujui SK.');
        }
    }

    public function show(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('view', $surat_keputusan);
        return view('surat_keputusan.show', [
            'sk' => $surat_keputusan->load(['pembuat', 'penandatanganUser']),
        ]);
    }

    public function reject(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('reject', $surat_keputusan);
        $note = trim((string) $request->input('note'));

        // ✅ Cukup panggil service
        $this->skService->rejectKeputusan($surat_keputusan, $note);

        return back()->with('success', 'SK ditolak dan dikembalikan ke pembuat' . ($note ? ' (catatan dikirim).' : '.'));
    }


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

        if (method_exists(app(SuratKeputusanNotificationService::class), 'notifyPublished')) {
            app(SuratKeputusanNotificationService::class)->notifyPublished($surat_keputusan);
        }

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
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']);
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
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']);
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
    private function mapModeToStatus(?string $mode): ?string
    {
        return match ($mode) {
            'terkirim', 'pending' => 'pending',
            'draft'    => 'draft',
            default    => null,
        };
    }
}
