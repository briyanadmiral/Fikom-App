<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTugasRequest;
use App\Http\Requests\UpdateTugasRequest;
use App\Models\TugasHeader;
use App\Models\JenisTugas;
use App\Models\MasterKopSurat;
use App\Services\SuratTugasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TugasController extends Controller
{
    /** 🔧 injeksi service */
    protected SuratTugasService $tugasService;

    public function __construct(SuratTugasService $tugasService)
    {
        $this->tugasService = $tugasService;
    }

    // ------------------ Helpers ------------------

    private function toRoman($number)
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }

    private function getFormDependencies(): array
    {
        $admins = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat = \App\Models\User::whereIn('peran_id', [2, 3])->get();
        $users = \App\Models\User::where('peran_id', '!=', 1)->get();
        $taskMaster = JenisTugas::with('subtugas.detail')->orderBy('nama')->get();
        $klasifikasi = \App\Models\KlasifikasiSurat::orderBy('kode')->get();
        return compact('admins', 'pejabat', 'users', 'taskMaster', 'klasifikasi');
    }

    private function resolveMode(Request $request): string
    {
        $raw = $request->input('action') ?? $request->input('mode');
        if ($raw === 'terkirim') {
            $raw = 'submit';
        }
        $mode = is_array($raw) ? end($raw) : $raw ?? 'draft';
        return in_array($mode, ['draft', 'submit']) ? $mode : 'draft';
    }

    private function shouldShowSignatures(TugasHeader $tugas): bool
    {
        return $tugas->status_surat === 'disetujui' && !empty($tugas->signed_at);
    }

    // ------------------ CRUD / Business ------------------

    public function index()
    {
        return redirect()->route('surat_tugas.index');
    }

    public function mine()
    {
        $user = Auth::user();
        $peranId = $user->peran_id;

        if ($peranId === 1) {
            $list = TugasHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser'])
                ->where('dibuat_oleh', $user->id)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $list = TugasHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser'])
                ->where('status_surat', 'disetujui')
                ->whereHas('penerima', fn($q) => $q->where('pengguna_id', $user->id))
                ->orderByDesc('created_at')
                ->get();
        }

        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_tugas.tugas_saya', compact('list', 'stats'));
    }

    public function all()
    {
        $user = Auth::user();
        if ($user->peran_id !== 1) {
            return redirect()->route('surat_tugas.mine')->with('error', 'Anda tidak berhak melihat semua surat.');
        }

        $list = TugasHeader::with(['pembuat', 'penerima.pengguna'])
            ->orderByDesc('created_at')
            ->get();
        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_tugas.index', compact('list', 'stats'));
    }

    public function create()
    {
        $deps = $this->getFormDependencies();
        extract($deps);

        $tahun = date('Y');
        $semester = date('n') >= 8 || date('n') <= 1 ? 'Ganjil' : 'Genap';
        $bulanRomawi = $this->toRoman(date('n'));
        $autoNomor = sprintf('/TG/UNIKA/%s/%s', $bulanRomawi, $tahun);
        $tanggalHariIni = now()->format('Y-m-d'); // Tambahkan ini

        return view(
            'surat_tugas.create',
            compact(
                'admins',
                'pejabat',
                'users',
                'taskMaster',
                'autoNomor',
                'tahun',
                'semester',
                'klasifikasi',
                'bulanRomawi',
                'tanggalHariIni', // Tambahkan ini
            ),
        )->with('tugas', null);
    }

    public function store(StoreTugasRequest $request)
    {
        $mode = $this->resolveMode($request);
        $validated = $request->validated();

        // ✅ Pastikan tanggal_surat masuk ke validated
        if (!isset($validated['tanggal_surat'])) {
            $validated['tanggal_surat'] = $request->input('tanggal_surat') ?? now()->format('Y-m-d');
        }

        try {
            $tugas = $this->tugasService->createTugas($validated, $mode);
            $message = $tugas->status_surat === 'pending' ? 'Surat tugas berhasil diajukan!' : 'Surat tugas disimpan sebagai draft!';
            return redirect()->route('surat_tugas.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan Surat Tugas', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approveList()
    {
        $this->authorize('viewApproveList', TugasHeader::class);

        $list = TugasHeader::with(['pembuat', 'penerima.pengguna'])
            ->where('status_surat', 'pending')
            ->where('next_approver', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_tugas.index', compact('list', 'stats'));
    }

    /** Halaman peninjauan & approve (khusus next_approver) */
    public function approveForm(Request $request, TugasHeader $tugas)
    {
        abort_if($tugas->status_surat !== 'pending' || (int) $tugas->next_approver !== (int) Auth::id(), 403);

        $assets = $this->getSigningAssets($tugas);
        $previewData = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $tugas->ttd_w_mm ?? $assets['ttdW'],
            'cap_w_mm' => $tugas->cap_w_mm ?? $assets['capW'],
            'cap_opacity' => $tugas->cap_opacity ?? $assets['capOpacity'],
        ];

        return view('surat_tugas.approve', [
            'tugas' => $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna']),
            'kop' => $assets['kop'],
            'preview' => $previewData,
            'showSigns' => true,
        ]);
    }

    /** Partial preview untuk halaman approve (selalu tampil TTD/Cap) */
    public function approvePreview(Request $request, TugasHeader $tugas)
    {
        abort_if($tugas->status_surat !== 'pending' || (int) $tugas->next_approver !== (int) Auth::id(), 403);

        $assets = $this->getSigningAssets($tugas);
        $preview = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => (int) $request->input('ttd_w_mm', $assets['ttdW']),
            'cap_w_mm' => (int) $request->input('cap_w_mm', $assets['capW']),
            'cap_opacity' => (float) $request->input('cap_opacity', $assets['capOpacity']),
        ];

        return view('surat_tugas.partials.approve-preview', [
            'tugas' => $tugas,
            'kop' => $assets['kop'],
            'preview' => $preview,
            'showSigns' => true,
        ]);
    }

    // START: Approve + Digital Sign
    public function approve(Request $request, TugasHeader $tugas)
    {
        $this->authorize('approve', $tugas);
        $validated = $request->validate([
            'ttd_w_mm' => 'required|integer|min:30|max:60',
            'cap_w_mm' => 'required|integer|min:25|max:45',
            'cap_opacity' => 'required|numeric|min:0.7|max:1.0',
        ]);

        try {
            // 🔒 kunci nomor & finalisasi status dilakukan di service
            $tugas = $this->tugasService->approveTugas($tugas, $validated);

            $pdfBytes = $this->renderTugasPdfWithSign($tugas);
            $pdfPath = "private/surat_tugas/signed/{$tugas->id}_" . md5((string) $tugas->nomor) . '.pdf';
            Storage::disk('local')->put($pdfPath, $pdfBytes);

            $tugas->update(['signed_pdf_path' => $pdfPath]);

            return redirect()->route('surat_tugas.approveList')->with('success', 'Surat berhasil disetujui.');
        } catch (\Throwable $e) {
            \Log::error('Gagal approve surat tugas #' . $tugas->id, ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyetujui surat.');
        }
    }

    public function destroy(TugasHeader $tugas)
    {
        $this->authorize('delete', $tugas);
        $tugas->delete();
        return redirect()->route('surat_tugas.mine')->with('success', 'Draft surat tugas berhasil dihapus.');
    }

    // ====== helpers tanda tangan & cap ======

    private function getSigningAssets(TugasHeader $tugas): array
    {
        $ttdImageB64 = null;
        $penandatangan = $tugas->penandatanganUser;
        if ($penandatangan && $penandatangan->signature && !empty($penandatangan->signature->ttd_path)) {
            $ttdImageB64 = $this->b64FromStorage($penandatangan->signature->ttd_path);
        }

        $capImageB64 = null;
        $kop = MasterKopSurat::query()->first();
        if ($kop && !empty($kop->cap_path)) {
            $capImageB64 = $this->b64FromStorage($kop->cap_path);
        }

        $ttdW = $tugas->ttd_w_mm ?? 42;
        $capW = $tugas->cap_w_mm ?? 35;
        $capOpacity = $tugas->cap_opacity ?? 0.95;

        return compact('ttdImageB64', 'capImageB64', 'ttdW', 'capW', 'capOpacity', 'kop');
    }

    private function b64FromStorage($pathPublicOrLocal)
    {
        if (!$pathPublicOrLocal) {
            return null;
        }

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

    private function renderTugasPdfWithSign(TugasHeader $tugas): string
    {
        $signAssets = $this->getSigningAssets($tugas);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();

        $html = view('surat_tugas.surat_pdf', array_merge(['tugas' => $tugas, 'penerimaList' => $penerimaList], $signAssets))->render();

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    private function renderTugasPdfDraft(TugasHeader $tugas): string
    {
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();
        $kop = MasterKopSurat::query()->first();

        $html = view('surat_tugas.surat_pdf', [
            'tugas' => $tugas,
            'penerimaList' => $penerimaList,
            'kop' => $kop,
            'showSigns' => false,
            'isDraft' => true,
            'ttdImageB64' => null,
            'capImageB64' => null,
            'ttdW' => null,
            'capW' => null,
            'capOpacity' => null,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    public function show(Request $request, TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser.peran', 'penerima.pengguna']);
        $assets = $this->getSigningAssets($tugas);

        $showSigns = $this->shouldShowSignatures($tugas);

        $previewData = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $request->input('ttd_w_mm', $assets['ttdW']),
            'cap_w_mm' => $request->input('cap_w_mm', $assets['capW']),
            'cap_opacity' => $request->input('cap_opacity', $assets['capOpacity']),
        ];

        if ($request->input('partial') === 'true') {
            return view('surat_tugas.partials.approve-preview', [
                'tugas' => $tugas,
                'kop' => $assets['kop'],
                'preview' => $previewData,
                'showSigns' => $showSigns,
            ]);
        }

        return view('surat_tugas.show', [
            'tugas' => $tugas,
            'kop' => $assets['kop'],
            'preview' => $previewData,
            'showSigns' => $showSigns,
        ]);
    }

    public function edit(TugasHeader $tugas)
{
    $user = Auth::user();
    $peranId = $user->peran_id;
    $tugas->load(['penerima.pengguna']);
    
    // ✅ FIX: Tambahkan tanggal_surat dari database
    $tanggalHariIni = now()->format('Y-m-d');
    
    $nomorParts = explode('/', $tugas->nomor);
    $baseNomor = '/' . implode('/', array_slice($nomorParts, 1));

    if ($peranId === 1) {
        if (!($tugas->dibuat_oleh === $user->id && in_array($tugas->status_surat, ['draft', 'ditolak']))) {
            return redirect()->route('surat_tugas.index')->with('error', 'Anda tidak berhak mengedit surat ini.');
        }
    } elseif (in_array($peranId, [2, 3])) {
        if (!($tugas->status_surat === 'pending' && $tugas->penandatangan == $user->id)) {
            return redirect()->route('surat_tugas.index')->with('error', 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
        }
    } else {
        return redirect()->route('surat_tugas.index')->with('error', 'Anda tidak berhak mengakses form edit ini.');
    }

    $deps = $this->getFormDependencies();
    extract($deps);

    $data = [
        'nomor' => $tugas->nomor,
        'tanggal_surat' => $tugas->tanggal_surat?->format('Y-m-d') ?? $tanggalHariIni, // ✅ FIX: Tambahkan ini
        'tanggal_asli' => $tugas->tanggal_asli?->format('Y-m-d\TH:i'),
        'nama_pembuat' => $tugas->nama_pembuat,
        'asal_surat' => $tugas->asal_surat,
        'jenis_tugas' => $tugas->jenis_tugas,
        'tugas' => $tugas->tugas,
        'status_penerima' => $tugas->status_penerima,
        'detail_tugas' => $tugas->detail_tugas,
        'waktu_mulai' => $tugas->waktu_mulai?->format('Y-m-d\TH:i'),
        'waktu_selesai' => $tugas->waktu_selesai?->format('Y-m-d\TH:i'),
        'tempat' => $tugas->tempat,
        'penandatangan' => $tugas->penandatangan,
        'penerima_ids' => $tugas->penerima->pluck('pengguna_id')->all(),
        'tahun' => $tugas->tahun,
        'semester' => $tugas->semester,
        'no_bin' => $tugas->no_bin,
        'no_surat_manual' => $tugas->no_surat_manual,
        'nama_umum' => $tugas->nama_umum,
        'tembusan' => $tugas->tembusan,
        'redaksi_pembuka' => $tugas->redaksi_pembuka,
        'penutup' => $tugas->penutup,
        'klasifikasi_surat_id' => $tugas->klasifikasi_surat_id ?? null,
    ];

    return view('surat_tugas.edit', compact('admins', 'pejabat', 'users', 'taskMaster', 'klasifikasi', 'data', 'tugas', 'baseNomor', 'tanggalHariIni'));
}


    // UPDATE
    public function update(UpdateTugasRequest $request, TugasHeader $tugas)
    {
        $mode = $this->resolveMode($request);
        $validated = $request->validated();

        // 🟢 Merge juga tanggal_surat saat update
        $validated['tanggal_surat'] = $request->input('tanggal_surat');

        try {
            $tugas = $this->tugasService->updateTugas($tugas, $validated, $mode);
            $message = $mode === 'submit' ? 'Surat tugas berhasil diajukan ulang!' : 'Perubahan surat tugas disimpan sebagai draft!';
            return redirect()->route('surat_tugas.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Gagal memperbarui Surat Tugas', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function highlight(TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'asalSurat', 'penerima.pengguna']);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->all();
        $showSigns = $this->shouldShowSignatures($tugas);

        return response()->view('surat_tugas.highlight', compact('tugas', 'penerimaList', 'showSigns'))->header('X-Frame-Options', 'ALLOWALL');
    }

    public function downloadPdf(TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran', 'tugasDetail.subTugas']);
        $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string) ($tugas->nomor ?? 'TanpaNomor'));

        if ($this->shouldShowSignatures($tugas)) {
            $bytes = $this->renderTugasPdfWithSign($tugas);
            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="SuratTugas_' . $safeNomor . '.pdf"',
            ]);
        }

        $bytes = $this->renderTugasPdfDraft($tugas);
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SuratTugas_DRAFT_' . $safeNomor . '.pdf"',
        ]);
    }

    public function preview(TugasHeader $tugas, Request $request)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran', 'tugasDetail.subTugas']);

        $signAssets = $this->getSigningAssets($tugas);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();
        $showSigns = $this->shouldShowSignatures($tugas);

        return response()
            ->view('surat_tugas.preview', array_merge(['tugas' => $tugas, 'penerimaList' => $penerimaList, 'showSigns' => $showSigns], $signAssets))
            ->header('X-Frame-Options', 'ALLOWALL');
    }
}
