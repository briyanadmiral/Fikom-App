<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeputusanRequest;
use App\Http\Requests\UpdateKeputusanRequest;
use App\Models\KeputusanHeader;
use App\Models\MasterKopSurat;
use App\Services\NomorSuratService;
use App\Services\SuratKeputusanService;
use App\Services\SuratKeputusanNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Log;

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
        if ($input === null || $input === '') {
            return null;
        }

        if (is_array($input)) {
            $arr = $input;
        } else {
            $s = trim((string) $input);
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
            // ✅ FIXED: Sanitize each value
            $value = trim(is_array($it) ? $it['value'] ?? ($it['text'] ?? ($it['name'] ?? (string) reset($it))) : (string) $it);
            $names[] = sanitize_input($value, 255);
        }
        $names = array_values(array_unique(array_filter($names)));

        return $names ? implode("\n", $names) : null;
    }

    /** Dependency untuk form (hanya yang relevan) */
    private function getFormDependencies(): array
    {
        // ✅ Admin users with peran_id
        $admins = \App\Models\User::select('id', 'nama_lengkap', 'peran_id')->where('peran_id', 1)->get();

        // ✅ FIXED: Add peran_id to pejabat query
        $pejabat = \App\Models\User::select('id', 'nama_lengkap', 'peran_id')
            ->whereIn('peran_id', [2, 3])
            ->orderBy('nama_lengkap')
            ->get();

        // ✅ Active users with all needed columns
        $users = \App\Models\User::select('id', 'nama_lengkap', 'peran_id', 'email', 'status')->where('status', 'aktif')->orderBy('nama_lengkap')->get();

        // ✅ All klasifikasi surat
        $klasifikasi = \App\Models\KlasifikasiSurat::orderBy('kode')->get();

        return compact('admins', 'pejabat', 'users', 'klasifikasi');
    }

    /** Apakah PDF harus menampilkan TTD/Cap */
    private function shouldShowSignatures(KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'disetujui' && !empty($sk->signed_at);
    }

    /* ==================== Daftar / List ==================== */

    public function index()
    {
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
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
            ->orderByDesc('created_at')
            ->get();

        $stats = ['draft' => 0, 'pending' => $list->count(), 'disetujui' => 0];
        $mode = 'approve-list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    /** Keputusan saya = SK yang mencantumkan saya sebagai penerima */
    public function mine()
    {
        $userId = Auth::id();
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap'])
            ->whereHas('penerima', fn($q) => $q->whereKey($userId))
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_keputusan.keputusan_saya', compact('list', 'stats'));
    }

    /* ==================== Create / Edit ==================== */

    public function create()
    {
        $this->authorize('create', KeputusanHeader::class);
        $deps = $this->getFormDependencies();

        $deps['bulanRomawi'] = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $deps['currentYear'] = now()->year;
        $deps['currentRomawi'] = $deps['bulanRomawi'][now()->month];
        $deps['tembusanPresets'] = ['Yth. Rektor', 'Yth. Wakil Rektor I', 'Yth. Wakil Rektor II', 'Dekan Fakultas Ilmu Komputer', 'BAAK', 'BAUK', 'BAK', 'Kepala Program Studi Sistem Informasi', 'Unit Kepegawaian', 'Arsip'];
        $deps['tanggalHariIni'] = now()->format('Y-m-d');

        return view('surat_keputusan.create', $deps);
    }

    public function store(StoreKeputusanRequest $request)
    {
        $this->authorize('create', KeputusanHeader::class);

        $validatedData = $request->validated();
        $mode = $request->input('mode');
        $status = $mode === 'pending' || $mode === 'terkirim' ? 'pending' : 'draft';

        if ($status === 'pending') {
            if (empty($validatedData['penandatangan'])) {
                return back()
                    ->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])
                    ->withInput();
            }
            if (empty($validatedData['penerima_internal']) && empty($validatedData['penerima_eksternal'])) {
                return back()
                    ->withErrors(['penerima_internal' => 'Minimal satu penerima (internal/eksternal) saat pengajuan.'])
                    ->withInput();
            }
        }

        try {
            $sk = $this->skService->createKeputusan($validatedData, $status);

            $message = $status === 'draft' ? 'Draft SK berhasil disimpan.' : 'SK berhasil diajukan ke penandatangan.';

            return redirect()->route('surat_keputusan.index')->with('success', $message);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()
                    ->withErrors(['nomor' => 'Nomor surat sudah digunakan. Silakan generate nomor baru.'])
                    ->withInput();
            }
            throw $e;
        }
    }

    public function edit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('update', $surat_keputusan);
        $deps = $this->getFormDependencies();
        $surat_keputusan->load(['penerima:id,nama_lengkap']);

        $deps['bulanRomawi'] = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $deps['currentYear'] = now()->year;
        $deps['currentRomawi'] = $deps['bulanRomawi'][now()->month];
        $deps['tembusanPresets'] = ['Yth. Rektor', 'Yth. Wakil Rektor I', 'Yth. Wakil Rektor II', 'Dekan Fakultas Ilmu Komputer', 'BAAK', 'BAUK', 'BAK', 'Kepala Program Studi Sistem Informasi', 'Unit Kepegawaian', 'Arsip'];
        $deps['tanggalHariIni'] = now()->format('Y-m-d');

        return view(
            'surat_keputusan.edit',
            array_merge($deps, [
                'keputusan' => $surat_keputusan,
                'sk' => $surat_keputusan,
                'isEdit' => true,
            ]),
        );
    }

    public function update(UpdateKeputusanRequest $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('update', $surat_keputusan);

        $validatedData = $request->validated();
        $mode = $request->input('mode');

        if ($mode === 'pending' || $mode === 'terkirim') {
            if (!in_array($surat_keputusan->status_surat, ['draft', 'ditolak'])) {
                return back()->withErrors(['mode' => 'SK dengan status ' . $surat_keputusan->status_surat . ' tidak bisa diajukan.']);
            }
            $newStatus = 'pending';
        } else {
            $newStatus = $surat_keputusan->status_surat;
        }

        if ($newStatus === 'pending' && empty($validatedData['penandatangan']) && empty($surat_keputusan->penandatangan)) {
            return back()
                ->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])
                ->withInput();
        }

        try {
            $sk = $this->skService->updateKeputusan($surat_keputusan, $validatedData, $newStatus);

            $message = $newStatus === 'pending' ? 'Perubahan disimpan & SK diajukan kembali.' : 'SK berhasil diperbarui.';

            return redirect()->route('surat_keputusan.index')->with('success', $message);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()
                    ->withErrors(['nomor' => 'Nomor surat sudah digunakan. Silakan generate nomor baru.'])
                    ->withInput();
            }
            throw $e;
        }
    }

    /* ==================== Workflow ==================== */

    public function submit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('submit', $surat_keputusan);

        if (!$surat_keputusan->penandatangan) {
            return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi sebelum pengajuan.']);
        }

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
            'ttd_w_mm' => $surat_keputusan->ttd_w_mm ?? $assets['ttdW'],
            'cap_w_mm' => $surat_keputusan->cap_w_mm ?? $assets['capW'],
            'cap_opacity' => $surat_keputusan->cap_opacity ?? $assets['capOpacity'],
        ];

        return view('surat_keputusan.approve', [
            'sk' => $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']),
            'kop' => $assets['kop'],
            'preview' => $preview,
            'ttdW' => $preview['ttd_w_mm'],
            'capW' => $preview['cap_w_mm'],
            'capOpacity' => $preview['cap_opacity'],
            'ttdImageB64' => $assets['ttdImageB64'],
            'capImageB64' => $assets['capImageB64'],
            'showSigns' => true,
        ]);
    }

    public function approvePreview(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $assets = $this->getSigningAssets($surat_keputusan);
        $ttdW = (int) $request->input('ttd_w_mm', $surat_keputusan->ttd_w_mm ?? $assets['ttdW']);
        $capW = (int) $request->input('cap_w_mm', $surat_keputusan->cap_w_mm ?? $assets['capW']);
        $capOpacity = (float) $request->input('cap_opacity', $surat_keputusan->cap_opacity ?? $assets['capOpacity']);

        return view('surat_keputusan.partials.approve-preview', [
            'sk' => $surat_keputusan,
            'kop' => $assets['kop'],
            'showSigns' => true,
            'ttdImageB64' => $assets['ttdImageB64'],
            'capImageB64' => $assets['capImageB64'],
            'ttdW' => $ttdW,
            'capW' => $capW,
            'capOpacity' => $capOpacity,
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
            $sk = $this->skService->approveAndGenerateNumber($surat_keputusan, $validated);

            $pdfBytes = $this->renderSkPdfWithSign($sk);
            $pdfPath = "private/surat_keputusan/signed/{$sk->id}_" . md5((string) ($sk->nomor ?? '')) . '.pdf';
            Storage::disk('local')->put($pdfPath, $pdfBytes);
            $sk->update(['signed_pdf_path' => $pdfPath]);

            return redirect()
                ->route('surat_keputusan.approveList')
                ->with('success', 'SK ' . $sk->nomor . ' berhasil disetujui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // ✅ FIXED: Sanitize log message
            Log::error('Gagal approve SK #' . $surat_keputusan->id, [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);
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

        $this->skService->rejectKeputusan($surat_keputusan, $note);

        return back()->with('success', 'SK ditolak dan dikembalikan ke pembuat' . ($note ? ' (catatan dikirim).' : '.'));
    }

    public function reopen(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('reopen', $surat_keputusan);

        DB::transaction(function () use ($surat_keputusan) {
            $surat_keputusan->update([
                'status_surat' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'signed_at' => null,
                'signed_pdf_path' => null,
            ]);

            // ✅ FIXED: Use Eloquent model instead of raw DB
            if ($surat_keputusan->penandatangan) {
                \App\Models\Notifikasi::create([
                    'pengguna_id' => (int) $surat_keputusan->penandatangan,
                    'tipe' => 'surat_keputusan',
                    'referensi_id' => (int) $surat_keputusan->id,
                    'pesan' => 'SK ' . ($surat_keputusan->nomor ?? '(tanpa nomor)') . ' ditarik ke Draft oleh ' . auth()->user()->nama_lengkap . '.',
                    'dibaca' => false,
                    'dibuat_pada' => now(),
                ]);
            }
        });

        return back()->with('success', 'SK ditarik ke Draft untuk direvisi.');
    }

    public function terbitkan(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('publish', $surat_keputusan);

        if ($surat_keputusan->status_surat !== 'disetujui') {
            return back()->withErrors(['status' => 'Hanya SK yang sudah disetujui yang bisa diterbitkan.']);
        }

        $surat_keputusan->update([
            'status_surat' => 'terbit',
            'tanggal_terbit' => now(),
        ]);

        return redirect()->route('surat_keputusan.index')->with('success', 'SK berhasil diterbitkan.');
    }

    public function arsipkan(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('archive', $surat_keputusan);

        if ($surat_keputusan->status_surat !== 'terbit') {
            return back()->withErrors(['status' => 'Hanya SK yang sudah terbit yang bisa diarsipkan.']);
        }

        $surat_keputusan->update([
            'status_surat' => 'arsip',
            'tanggal_arsip' => now(),
        ]);

        return redirect()->route('surat_keputusan.index')->with('success', 'SK berhasil diarsipkan.');
    }

    public function destroy(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('delete', $surat_keputusan);

        if ($surat_keputusan->status_surat !== 'draft') {
            return back()->withErrors([
                'status' => 'Hanya SK berstatus draft yang bisa dihapus.',
            ]);
        }

        try {
            DB::transaction(function () use ($surat_keputusan) {
                if (method_exists($surat_keputusan, 'penerima')) {
                    $surat_keputusan->penerima()->detach();
                }

                // ✅ FIXED: Validate file path before deletion
                if ($surat_keputusan->signed_pdf_path) {
                    $validPath = validate_file_path($surat_keputusan->signed_pdf_path);
                    if ($validPath && Storage::disk('local')->exists($validPath)) {
                        Storage::disk('local')->delete($validPath);
                    }
                }

                $surat_keputusan->delete();
            });

            return redirect()->route('surat_keputusan.index')->with('success', 'SK berhasil dihapus.');
        } catch (\Exception $e) {
            // ✅ FIXED: Sanitize log message
            Log::error('Gagal menghapus SK #' . $surat_keputusan->id, [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus SK.');
        }
    }

    /* ==================== PDF & Preview ==================== */

    public function downloadPdf(KeputusanHeader $surat_keputusan)
    {
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']);

        // ✅ FIXED: Use sanitize_alphanumeric() for filename
        $safeNomor = sanitize_alphanumeric($surat_keputusan->nomor, '_-') ?? 'TanpaNomor';

        if ($this->shouldShowSignatures($surat_keputusan)) {
            $bytes = $this->renderSkPdfWithSign($surat_keputusan);
            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="SuratKeputusan_' . $safeNomor . '.pdf"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $bytes = $this->renderSkPdfDraft($surat_keputusan);
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SuratKeputusan_DRAFT_' . $safeNomor . '.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function preview(KeputusanHeader $surat_keputusan, Request $request)
    {
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']);
        $assets = $this->getSigningAssets($surat_keputusan);
        $showSigns = $this->shouldShowSignatures($surat_keputusan);

        return response()
            ->view('surat_keputusan.preview', array_merge(['sk' => $surat_keputusan, 'showSigns' => $showSigns], $assets))
            ->header('X-Frame-Options', 'SAMEORIGIN');
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

        $ttdW = $sk->ttd_w_mm ?? ($pen?->signature?->default_width_mm ?? 42);
        $capW = $sk->cap_w_mm ?? 35;
        $capOpacity = $sk->cap_opacity ?? 0.95;

        return compact('ttdImageB64', 'capImageB64', 'ttdW', 'capW', 'capOpacity', 'kop');
    }

    private function b64FromStorage($pathPublicOrLocal)
    {
        if (!$pathPublicOrLocal) {
            return null;
        }

        // ✅ FIXED: Validate file path
        $validPath = validate_file_path($pathPublicOrLocal);
        if ($validPath === null) {
            return null;
        }

        if (Storage::disk('local')->exists($validPath)) {
            $raw = Storage::disk('local')->get($validPath);
            return 'data:image/png;base64,' . base64_encode($raw);
        }

        $pub = ltrim(preg_replace('#^public/#', '', $validPath), '/');
        if (Storage::exists('public/' . $pub)) {
            $raw = Storage::get('public/' . $pub);
            return 'data:image/png;base64,' . base64_encode($raw);
        }

        return null;
    }

    private function renderSkPdfWithSign(KeputusanHeader $sk): string
    {
        $assets = $this->getSigningAssets($sk);

        $html = view('surat_keputusan.surat_pdf', array_merge(['sk' => $sk, 'showSigns' => true, 'isDraft' => false], $assets))->render();

        return \PDF::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    private function renderSkPdfDraft(KeputusanHeader $sk): string
    {
        $kop = MasterKopSurat::query()->first();

        $html = view('surat_keputusan.surat_pdf', [
            'sk' => $sk,
            'kop' => $kop,
            'showSigns' => false,
            'isDraft' => true,
            'ttdImageB64' => null,
            'capImageB64' => null,
            'ttdW' => null,
            'capW' => null,
            'capOpacity' => null,
        ])->render();

        return \PDF::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    /** Map nilai tombol UI ke status_surat di DB */
    private function mapModeToStatus(?string $mode): ?string
    {
        return match ($mode) {
            'terkirim', 'pending' => 'pending',
            'draft' => 'draft',
            default => null,
        };
    }
}
