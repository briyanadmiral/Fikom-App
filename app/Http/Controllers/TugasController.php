<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTugasRequest;
use App\Http\Requests\UpdateTugasRequest;
use App\Models\JenisTugas;
use App\Models\MasterKopSurat;
use App\Models\TugasHeader;
use App\Services\SuratTugasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /** 🔧 injeksi service */
    protected SuratTugasService $tugasService;

    protected \App\Services\NomorSuratService $nomorService;

    public function __construct(SuratTugasService $tugasService, \App\Services\NomorSuratService $nomorService)
    {
        $this->tugasService = $tugasService;
        $this->nomorService = $nomorService;
    }

    // ------------------ Helpers ------------------

    /**
     * Data dropdown untuk Advance Filter Surat Tugas:
     * - Tahun unik
     * - Bulan unik (dari DB)
     * - Penandatangan (Dekan & WD)
     * - Pembuat (Admin TU)
     */
    private function getFilterDropdownData(): array
    {
        // Tahun unik dari surat tugas
        $tahunList = TugasHeader::selectRaw('DISTINCT tahun')->whereNotNull('tahun')->orderByDesc('tahun')->pluck('tahun');

        // Bulan unik dari surat tugas
        $bulanValues = TugasHeader::selectRaw('DISTINCT bulan')->whereNotNull('bulan')->orderBy('bulan')->pluck('bulan')->filter()->values();

        $bulanList = [];
        foreach ($bulanValues as $bulan) {
            $bulanList[$bulan] = $this->nomorService->getBulanLabel($bulan);
        }

        // Penandatangan: Dekan & WD (peran_id 2 & 3)
        $penandatanganList = \App\Models\User::select('id', 'nama_lengkap', 'jabatan')
            ->whereIn('peran_id', [2, 3])
            ->orderBy('nama_lengkap')
            ->get();

        // Pembuat: Admin TU (peran_id 1)
        $pembuatList = \App\Models\User::select('id', 'nama_lengkap')->where('peran_id', 1)->orderBy('nama_lengkap')->get();

        return [
            'tahun' => $tahunList,
            'bulan' => $bulanList,
            'penandatangan' => $penandatanganList,
            'pembuat' => $pembuatList,
        ];
    }

    private function getFormDependencies(): array
    {
        $admins = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat = \App\Models\User::with('peran')
            ->whereIn('peran_id', [2, 3])
            ->get();
        $users = \App\Models\User::with('peran')->where('peran_id', '!=', 1)->get();

        $taskMaster = JenisTugas::with('subtugas')->orderBy('nama')->get();
        $klasifikasi = \App\Models\KlasifikasiSurat::orderBy('kode')->get();

        return compact('admins', 'pejabat', 'users', 'taskMaster', 'klasifikasi');
    }

    private function resolveMode(Request $request): string
    {
        $raw = $request->input('action') ?? $request->input('mode');
        if ($raw === 'save_and_review') {
            return 'draft';
        }
        if ($raw === 'terkirim') {
            $raw = 'submit';
        }
        $mode = is_array($raw) ? end($raw) : $raw ?? 'draft';

        return validate_status($mode, ['draft', 'submit']) ?? 'draft';
    }

    private function shouldShowSignatures(TugasHeader $tugas): bool
    {
        return in_array($tugas->status_surat, ['disetujui', 'arsip'], true) && !empty($tugas->signed_at);
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
            $list = TugasHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser', 'klasifikasi'])
                ->where('dibuat_oleh', $user->id)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $list = TugasHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser', 'klasifikasi'])
                ->where('status_surat', 'disetujui')
                ->whereHas('penerima', fn($q) => $q->where('pengguna_id', $user->id))
                ->orderByDesc('created_at')
                ->get();
        }

        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
            'ditolak' => $list->where('status_surat', 'ditolak')->count(),
        ];

        return view('surat_tugas.tugas_saya', compact('list', 'stats'));
    }

    public function all(Request $request)
    {
        $user = Auth::user();
        if ($user->peran_id !== 1) {
            abort(403, 'Anda tidak berhak melihat semua surat.');
        }

        $validated = $request->validate([
            'search' => 'nullable|string|max:100',

            'status' => 'nullable|in:draft,pending,disetujui,ditolak,arsip', // Updated for workflow
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'bulan' => 'nullable|string|max:10',
            'penandatangan' => 'nullable|integer|exists:pengguna,id',
            'pembuat' => 'nullable|integer|exists:pengguna,id',
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
            'sort' => 'nullable|in:created_at,tanggal_surat,nomor',
            'order' => 'nullable|in:asc,desc',
        ]);

        $query = TugasHeader::withFullRelations();

        $query->applyFilters($validated);

        $sortBy = $validated['sort'] ?? 'created_at';
        $sortOrder = $validated['order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $list = $query->get();

        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
            'ditolak' => $list->where('status_surat', 'ditolak')->count(),
            'arsip' => $list->where('status_surat', 'arsip')->count(),
        ];

        $filterData = $this->getFilterDropdownData();

        $mode = 'all';

        return view('surat_tugas.index', compact('list', 'stats', 'filterData', 'mode'));
    }

    public function create()
    {
        $deps = $this->getFormDependencies();
        extract($deps);
        $tahun = (int) date('Y');
        $bulanInt = (int) date('n');
        $semester = $bulanInt >= 8 || $bulanInt <= 1 ? 'Ganjil' : 'Genap';
        $bulanRomawi = $this->nomorService->toRoman($bulanInt);
        $autoNomor = sprintf('/TG/UNIKA/%s/%s', $bulanRomawi, $tahun);
        $tanggalHariIni = now()->format('Y-m-d');

        $templates = \App\Models\SuratTemplate::active()->with(['jenisTugas', 'subTugas'])->orderBy('nama')->get();

        $parentableNomors = TugasHeader::whereIn('status_surat', ['pending', 'disetujui'])
            ->onlyMainNomor()
            ->where('tahun', $tahun)
            ->orderByNomor('desc')
            ->limit(100)
            ->get(['id', 'nomor', 'nama_umum', 'status_surat']);

        return view('surat_tugas.create', compact('admins', 'pejabat', 'users', 'taskMaster', 'autoNomor', 'tahun', 'semester', 'klasifikasi', 'bulanRomawi', 'tanggalHariIni', 'templates', 'parentableNomors'))->with('tugas', null);
    }

    public function store(StoreTugasRequest $request)
    {
        $mode = $this->resolveMode($request);
        $validated = $request->validated();

        try {
            $tugas = $this->tugasService->createTugas($validated, $mode);

            $message = $tugas->status_surat === 'pending' ? 'Surat tugas berhasil diajukan!' : 'Surat tugas disimpan sebagai draft!';

            return redirect()->route('surat_tugas.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Failed: Surat Tugas', [
                'errors' => $e->errors(),
                'input' => $request->except(['_token']),
                'user_id' => auth()->id(),
            ]);

            // Tampilkan error detail ke user
            $errorMessages = collect($e->errors())->flatten()->implode(' | ');

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Gagal menyimpan: ' . $errorMessages);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan Surat Tugas', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan surat tugas. Silakan coba lagi.');
        }
    }

    public function approveList()
    {
        $this->authorize('viewApproveList', TugasHeader::class);

        $list = TugasHeader::with(['pembuat', 'penerima.pengguna', 'penandatanganUser', 'klasifikasi'])
            ->where('status_surat', 'pending')
            ->where('next_approver', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft' => 0,
            'pending' => $list->count(),
            'disetujui' => 0,
            'ditolak' => 0,
        ];

        return view('surat_tugas.index', compact('list', 'stats'));
    }

    public function approveForm(Request $request, TugasHeader $tugas)
    {
        $nextApprover = validate_integer_id($tugas->next_approver);
        $currentUserId = validate_integer_id(Auth::id());
        if ($tugas->status_surat !== 'pending' || $nextApprover !== $currentUserId) {
            abort(403, 'Anda tidak berhak menyetujui surat ini.');
        }

        $assets = $this->getSigningAssets($tugas);
        $previewData = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $tugas->ttd_w_mm ?? $assets['ttdW'],
            'cap_w_mm' => $tugas->cap_w_mm ?? $assets['capW'],
            'cap_opacity' => $tugas->cap_opacity ?? $assets['capOpacity'],
        ];

        return view('surat_tugas.approve', [
            'tugas' => $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran']),
            'kop' => $assets['kop'],
            'preview' => $previewData,
            'showSigns' => true,
        ]);
    }

    public function approvePreview(Request $request, TugasHeader $tugas)
    {
        $nextApprover = validate_integer_id($tugas->next_approver);
        $currentUserId = validate_integer_id(Auth::id());
        if ($tugas->status_surat !== 'pending' || $nextApprover !== $currentUserId) {
            abort(403, 'Akses ditolak.');
        }

        $ttdWMm = filter_var($request->input('ttd_w_mm'), FILTER_VALIDATE_INT);
        $capWMm = filter_var($request->input('cap_w_mm'), FILTER_VALIDATE_INT);
        $capOpacity = filter_var($request->input('cap_opacity'), FILTER_VALIDATE_FLOAT);
        // Validasi input offset (bisa negatif)
        $ttdXMm = filter_var($request->input('ttd_x_mm'), FILTER_VALIDATE_INT);
        $ttdYMm = filter_var($request->input('ttd_y_mm'), FILTER_VALIDATE_INT);
        $capXMm = filter_var($request->input('cap_x_mm'), FILTER_VALIDATE_INT);
        $capYMm = filter_var($request->input('cap_y_mm'), FILTER_VALIDATE_INT);

        $assets = $this->getSigningAssets($tugas);

        // Prepare preview settings with overrides
        $preview = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $ttdWMm !== false ? $ttdWMm : $assets['ttdW'],
            'cap_w_mm' => $capWMm !== false ? $capWMm : $assets['capW'],
            'cap_opacity' => $capOpacity !== false ? $capOpacity : $assets['capOpacity'],
            // Offsets
            'ttd_x_mm' => $ttdXMm !== false ? $ttdXMm : 0,
            'ttd_y_mm' => $ttdYMm !== false ? $ttdYMm : 0,
            'cap_x_mm' => $capXMm !== false ? $capXMm : 0,
            'cap_y_mm' => $capYMm !== false ? $capYMm : 0,
        ];

        return view('surat_tugas.partials._approve_preview', [
            'tugas' => $tugas,
            'kop' => $assets['kop'],
            'preview' => $preview,
            'showSigns' => true,
        ]);
    }

    public function approve(Request $request, TugasHeader $tugas)
    {
        $this->authorize('approve', $tugas);
        $validated = $request->validate([
            'ttd_w_mm' => 'required|integer|min:10|max:150',
            'cap_w_mm' => 'required|integer|min:10|max:100',
            'cap_opacity' => 'required|numeric|min:0.5|max:1.0',
            'ttd_x_mm' => 'nullable|integer|min:-100|max:100',
            'ttd_y_mm' => 'nullable|integer|min:-100|max:100',
            'cap_x_mm' => 'nullable|integer|min:-100|max:100',
            'cap_y_mm' => 'nullable|integer|min:-100|max:100',
        ]);

        try {
            // Update config JSON
            $tugas->ttd_config = [
                'w_mm' => $validated['ttd_w_mm'],
                'x' => $validated['ttd_x_mm'] ?? 0,
                'y' => $validated['ttd_y_mm'] ?? 0,
            ];
            $tugas->cap_config = [
                'w_mm' => $validated['cap_w_mm'],
                'x' => $validated['cap_x_mm'] ?? 0,
                'y' => $validated['cap_y_mm'] ?? 0,
            ];

            $tugas->ttd_w_mm = $validated['ttd_w_mm'];
            $tugas->cap_w_mm = $validated['cap_w_mm'];
            $tugas->cap_opacity = $validated['cap_opacity'];
            $tugas->save(); // Ensure config is saved before generating PDF

            // Kita panggil service untuk handle status transition
            $tugas = $this->tugasService->approveTugas($tugas, []);

            // Generate Signed PDF dengan setting terbaru
            $pdfBytes = $this->renderTugasPdfWithSign($tugas);
            $safeNomor = sanitize_alphanumeric($tugas->nomor, '_-') ?? 'NoNomor';
            $pdfPath = sprintf('private/surat_tugas/signed/%d_%s_%s.pdf', $tugas->id, $safeNomor, md5((string) $tugas->nomor));
            Storage::disk('local')->put($pdfPath, $pdfBytes);

            $tugas->update(['signed_pdf_path' => $pdfPath]);

            return redirect()->route('surat_tugas.approveList')->with('success', 'Surat berhasil disetujui dan ditandatangani.');
        } catch (\Throwable $e) {
            \Log::error('Gagal approve surat tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyetujui surat. Silakan coba lagi.');
        }
    }

    public function reject(Request $request, TugasHeader $tugas)
    {
        $this->authorize('reject', $tugas);

        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|min:5|max:500',
        ]);

        try {
            $this->tugasService->rejectTugas($tugas, $validated['alasan_penolakan']);

            return redirect()->route('surat_tugas.approveList')->with('success', 'Surat tugas telah ditolak.');
        } catch (\Throwable $e) {
            \Log::error('Gagal menolak surat tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menolak surat. Silakan coba lagi.');
        }
    }

    public function destroy(TugasHeader $tugas)
    {
        $this->authorize('delete', $tugas);
        try {
            $tugas->delete();

            return redirect()->route('surat_tugas.mine')->with('success', 'Draft surat tugas berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Gagal menghapus surat tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            return back()->with('error', 'Gagal menghapus surat tugas. Silakan coba lagi.');
        }
    }

    // ====== Helpers tanda tangan & cap ======

    private function getSigningAssets(TugasHeader $tugas): array
    {
        $ttdImageB64 = null;
        $penandatangan = $tugas->penandatanganUser;
        if ($penandatangan && $penandatangan->signature && !empty($penandatangan->signature->ttd_path)) {
            $ttdImageB64 = $this->b64FromStorage($penandatangan->signature->ttd_path);
        }

        $capImageB64 = null;
        $kop = MasterKopSurat::getInstance();
        if ($kop && !empty($kop->cap_path)) {
            $capImageB64 = $this->b64FromStorage($kop->cap_path);
        }

        $ttdW = (int) ($tugas->ttd_w_mm ?? 42);
        $capW = (int) ($tugas->cap_w_mm ?? 35);
        $capOpacity = (float) ($tugas->cap_opacity ?? 0.95);

        return compact('ttdImageB64', 'capImageB64', 'ttdW', 'capW', 'capOpacity', 'kop');
    }

    private function b64FromStorage(?string $pathPublicOrLocal): ?string
    {
        if (empty($pathPublicOrLocal)) {
            return null;
        }
        $pathPublicOrLocal = validate_file_path($pathPublicOrLocal);
        if ($pathPublicOrLocal === null) {
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
        $tugas->loadMissing('penerima.pengguna.peran');
        $signAssets = $this->getSigningAssets($tugas);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();

        $html = view(
            'surat_tugas.surat_pdf',
            array_merge(
                [
                    'tugas' => $tugas,
                    'penerimaList' => $penerimaList,
                    'showSigns' => true,
                    'isDraft' => false,
                ],
                $signAssets,
            ),
        )->render();

        return Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    private function renderTugasPdfDraft(TugasHeader $tugas): string
    {
        $tugas->loadMissing('penerima.pengguna.peran');
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();
        $kop = MasterKopSurat::getInstance();

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
                'isRemoteEnabled' => false,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    public function show(TugasHeader $tugas)
    {
        // Load relasi yang dibutuhkan
        $tugas->load(['pembuat:id,nama_lengkap,email', 'penandatanganUser:id,nama_lengkap,email,peran_id,jabatan,npp', 'penandatanganUser.peran:id,nama', 'klasifikasi:id,kode,deskripsi', 'penerima.pengguna:id,nama_lengkap', 'children', 'parent:id,nomor']);

        // Get signing assets (TTD & Cap)
        $assets = $this->getSigningAssets($tugas);

        // Determine if we should show signatures
        $showSigns = $this->shouldShowSignatures($tugas);

        // Prepare preview data
        $preview = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $tugas->ttd_w_mm ?? $assets['ttdW'],
            'cap_w_mm' => $tugas->cap_w_mm ?? $assets['capW'],
            'cap_opacity' => $tugas->cap_opacity ?? $assets['capOpacity'],
        ];

        return view('surat_tugas.show', [
            'tugas' => $tugas,
            'kop' => $assets['kop'],
            'preview' => $preview,
            'showSigns' => $showSigns,
        ]);
    }

    public function edit(TugasHeader $tugas)
    {
        $user = Auth::user();
        $peranId = $user->peran_id;

        $tugas->load([
            'penerima.pengguna',
            'pembuat',
            'klasifikasiSurat',
        ]);
        $tanggalHariIni = now()->format('Y-m-d');

        // Parse nomor surat untuk editing
        $nomorParts = explode('/', $tugas->nomor);
        $baseNomor = '/' . implode('/', array_slice($nomorParts, 1));

        // Authorization check
        if ($peranId == 1) {
            // Admin TU - boleh edit draft/pending/ditolak yang dia buat
            if ($tugas->dibuat_oleh != $user->id || !in_array($tugas->status_surat, ['draft', 'pending', 'ditolak'], true)) {
                abort(403, 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3], true)) {
            // Dekan/WD - boleh edit saat pending dan dia penandatangannya
            if (!($tugas->status_surat === 'pending' && $tugas->penandatangan == $user->id)) {
                abort(403, 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
            }
        } else {
            abort(403, 'Anda tidak berhak mengakses form edit ini.');
        }

        $deps = $this->getFormDependencies();
        extract($deps);

        $data = [
            'nomor' => $tugas->nomor,
            'tanggal_surat' => $tugas->tanggal_surat?->format('Y-m-d') ?? $tanggalHariIni,
            'tanggal_asli' => $tugas->tanggal_asli?->format('Y-m-d\TH:i'),
            'nama_pembuat' => $tugas->pembuat?->nama_lengkap ?? $tugas->dibuat_oleh,
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

        $parentableNomors = collect();
        if ($peranId == 1 && $tugas->status_surat === 'pending') {
            $tahun = $tugas->tahun ?? (int) date('Y');
            $parentableNomors = TugasHeader::whereIn('status_surat', ['pending', 'disetujui'])
                ->onlyMainNomor()
                ->where('tahun', $tahun)
                ->where('id', '!=', $tugas->id) // Exclude surat ini sendiri
                ->orderByNomor('desc')
                ->limit(100)
                ->get(['id', 'nomor', 'nama_umum', 'status_surat']);
        }

        return view('surat_tugas.edit', compact('admins', 'pejabat', 'users', 'taskMaster', 'klasifikasi', 'data', 'tugas', 'baseNomor', 'tanggalHariIni', 'parentableNomors'));
    }

    public function update(UpdateTugasRequest $request, TugasHeader $tugas)
    {
        $action = $request->input('action');
        $mode = $action === 'save_and_review' ? 'draft' : $this->resolveMode($request);

        $validated = $request->validated();
        $validated['tanggal_surat'] = $request->input('tanggal_surat');

        try {
            $tugas = $this->tugasService->updateTugas($tugas, $validated, $mode);

            if ($action === 'save_and_review') {
                return redirect()->route('surat_tugas.approve.form', $tugas->id)->with('success', 'Perubahan berhasil disimpan. Silakan tinjau surat.');
            }

            $message = $mode === 'submit' ? 'Surat tugas berhasil diajukan ulang!' : 'Perubahan surat tugas disimpan sebagai draft!';

            return redirect()->route('surat_tugas.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Gagal memperbarui Surat Tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui surat tugas.');
        }
    }

    public function submit(Request $request, TugasHeader $tugas)
    {
        // 1. Validasi status harus draft
        if ($tugas->status_surat !== 'draft') {
            return back()->with('error', 'Hanya surat dengan status draft yang dapat diajukan.');
        }

        // 2. Validasi kelengkapan data minimal
        // Cek Penandatangan
        if (!$tugas->penandatangan && !$tugas->penandatangan_id) {
            return redirect()->route('surat_tugas.edit', $tugas->id)
                ->with('error', 'Penandatangan belum dipilih. Silakan lengkapi data surat.');
        }

        // Cek Judul / Perihal
        if (empty($tugas->nama_umum)) {
            return redirect()->route('surat_tugas.edit', $tugas->id)
                ->with('error', 'Judul/Perihal surat masih kosong. Silakan lengkapi data surat.');
        }

        // Cek Tanggal Surat
        if (empty($tugas->tanggal_surat)) {
            return redirect()->route('surat_tugas.edit', $tugas->id)
                ->with('error', 'Tanggal surat belum diisi. Silakan lengkapi data surat.');
        }

        // Cek Klasifikasi
        if (empty($tugas->klasifikasi_surat_id)) {
            return redirect()->route('surat_tugas.edit', $tugas->id)
                ->with('error', 'Klasifikasi surat belum dipilih. Silakan lengkapi data surat.');
        }

        // Cek Penerima (Minimal 1)
        if ($tugas->penerima()->count() === 0) {
            return redirect()->route('surat_tugas.edit', $tugas->id)
                ->with('error', 'Belum ada penerima tugas yang dipilih. Silakan tambahkan penerima.');
        }

        try {
            // 3. Capture send_email preference from checkbox
            $sendEmail = (bool) $request->input('send_email', true);

            // 4. Siapkan data untuk update
            $data = [
                'penandatangan_id' => $tugas->penandatangan_id ?? $tugas->penandatangan,
                'send_email' => $sendEmail,
            ];

            // 5. Panggil service dengan mode 'submit'
            $this->tugasService->updateTugas($tugas, $data, 'submit');

            // 6. Simpan preferensi email di model untuk digunakan saat approve
            $tugas->update(['send_email_on_approve' => $sendEmail]);

            $emailNote = $sendEmail
                ? ' Notifikasi email akan dikirim setelah disetujui.'
                : ' Tanpa notifikasi email.';

            return redirect()->route('surat_tugas.index')->with('success', 'Surat tugas berhasil diajukan untuk persetujuan!' . $emailNote);
        } catch (\Exception $e) {
            \Log::error('Gagal submit Surat Tugas (Direct)', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengajukan surat. Silakan coba lagi.');
        }
    }

    public function highlight(TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'asalSurat', 'penerima.pengguna']);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->all();
        $showSigns = $this->shouldShowSignatures($tugas);

        return response()->view('surat_tugas.highlight', compact('tugas', 'penerimaList', 'showSigns'))->header('X-Frame-Options', 'SAMEORIGIN');
    }

    public function downloadPdf(TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran']);
        $safeNomor = sanitize_alphanumeric($tugas->nomor, '_-') ?? 'TanpaNomor';
        try {
            if ($this->shouldShowSignatures($tugas)) {
                $bytes = $this->renderTugasPdfWithSign($tugas);

                return response($bytes, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => sprintf('inline; filename="SuratTugas_%s.pdf"', $safeNomor),
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
            $bytes = $this->renderTugasPdfDraft($tugas);

            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="SuratTugas_DRAFT_%s.pdf"', $safeNomor),
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal generate PDF surat tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            return back()->with('error', 'Gagal mengunduh PDF surat tugas.');
        }
    }

    /**
     * Tampilkan daftar arsip Surat Tugas
     */
    public function arsipList(Request $request)
    {
        $this->authorize('viewAny', TugasHeader::class); // Sama seperti 'all' permissions

        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'bulan' => 'nullable|string|max:10',
            'penandatangan' => 'nullable|integer',
            'pembuat' => 'nullable|integer',
            'order' => 'nullable|in:asc,desc',
            'sort' => 'nullable|in:created_at,tanggal_arsip,nomor',
        ]);

        $query = TugasHeader::withFullRelations()
            ->where('status_surat', 'arsip')
            ->applyFilters($validated)
            ->orderBy($validated['sort'] ?? 'tanggal_arsip', $validated['order'] ?? 'desc');

        $list = $query->get();

        $stats = [
            'arsip' => $list->count(),
        ];

        $filterData = $this->getFilterDropdownData();
        $mode = 'arsip-list';

        return view('surat_tugas.index', compact('list', 'stats', 'filterData', 'mode'));
    }

    /**
     * Arsipkan Surat Tugas
     */
    public function arsipkan(TugasHeader $tugas)
    {
        // Permission check: Bisa via policy 'archive' atau logic di controller
        // Asumsi admin TU (role 1)
        if (Auth::user()->peran_id !== 1) {
            abort(403, 'Hanya Admin yang dapat mengarsipkan surat.');
        }

        if (!$tugas->canBeArsipkan()) {
            return back()->with('error', 'Surat ini tidak dapat diarsipkan (Status harus Disetujui).');
        }

        try {
            \DB::transaction(function () use ($tugas) {
                $tugas->update([
                    'status_surat' => 'arsip',
                    'tanggal_arsip' => now(),
                    'arsipkan_oleh' => Auth::id(),
                ]);
            });

            return redirect()->route('surat_tugas.index')->with('success', 'Surat tugas berhasil diarsipkan.');
        } catch (\Exception $e) {
            \Log::error('Gagal arsipkan surat tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            return back()->with('error', 'Gagal mengarsipkan surat tugas. Silakan coba lagi.');
        }
    }

    /**
     * UNARCHIVE Action
     * Mengembalikan status dari 'arsip' ke 'disetujui'
     */
    public function bukaArsip(TugasHeader $tugas)
    {
        // Hanya Admin TU (role 1) yang boleh buka arsip
        if (Auth::user()->peran_id !== 1) {
            abort(403, 'Akses ditolak. Hanya Admin TU yang dapat membuka arsip.');
        }

        if ($tugas->status_surat !== 'arsip') {
            return back()->with('error', 'Surat tugas ini tidak sedang diarsipkan.');
        }

        try {
            \DB::transaction(function () use ($tugas) {
                $tugas->update([
                    'status_surat' => 'disetujui', // Kembali ke status sebelum arsip
                    'tanggal_arsip' => null, // Reset tanggal arsip
                    'arsipkan_oleh' => null,  // Reset pengarsip
                ]);
            });

            return redirect()->route('surat_tugas.arsipList')->with('success', 'Surat tugas berhasil dikeluarkan dari arsip.');
        } catch (\Exception $e) {
            \Log::error('Gagal membuka arsip surat tugas', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            return back()->with('error', 'Gagal membuka arsip surat tugas. Silakan coba lagi.');
        }
    }

    /** Show approve form dengan semua data yang dibutuhkan */
    public function showApproveForm(TugasHeader $tugas)
    {
        $tugas->load(['penerima.pengguna.peran', 'klasifikasiSurat', 'penandatanganUser.peran', 'pembuat', 'creator']);
        $signAssets = $this->getSigningAssets($tugas);
        $preview = [
            'ttd_image_b64' => $signAssets['ttdImageB64'],
            'cap_image_b64' => $signAssets['capImageB64'],
            'ttd_w_mm' => $tugas->ttd_w_mm ?? $signAssets['ttdW'],
            'cap_w_mm' => $tugas->cap_w_mm ?? $signAssets['capW'],
            'cap_opacity' => $tugas->cap_opacity ?? $signAssets['capOpacity'],
        ];
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();
        $showSigns = $this->shouldShowSignatures($tugas);

        return view('surat_tugas.approve', [
            'tugas' => $tugas,
            'kop' => $signAssets['kop'],
            'preview' => $preview,
            'penerimaList' => $penerimaList,
            'showSigns' => $showSigns,
            'signAssets' => $signAssets,
        ]);
    }

    public function preview(TugasHeader $tugas, Request $request)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran']);
        $signAssets = $this->getSigningAssets($tugas);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->filter()->values()->all();
        $showSigns = $this->shouldShowSignatures($tugas);

        return response()
            ->view(
                'surat_tugas.preview',
                array_merge(
                    [
                        'tugas' => $tugas,
                        'penerimaList' => $penerimaList,
                        'showSigns' => $showSigns,
                    ],
                    $signAssets,
                ),
            )
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    // =====================================================
    // ========== Halaman Tersusun Detail Tugas ============
    // =====================================================

    /** Form input nilai untuk semua field detail (berdasar sub_tugas dari detail master) */
    public function editDetail(TugasHeader $tugas)
    {
        $user = Auth::user();
        $peranId = $user->peran_id;
        $isCreator = $tugas->dibuat_oleh === $user->id;

        // Otorisasi: Admin TU (pembuat) boleh draft/pending/ditolak; Penandatangan boleh saat pending miliknya
        if ($peranId === 1) {
            if (!($isCreator && in_array($tugas->status_surat, ['draft', 'pending', 'ditolak'], true))) {
                abort(403, 'Anda tidak berhak mengubah detail surat ini.');
            }
        } elseif (in_array($peranId, [2, 3], true)) {
            if (!($tugas->status_surat === 'pending' && (int) $tugas->penandatangan === (int) $user->id)) {
                abort(403, 'Anda hanya dapat mengubah detail ketika surat menunggu persetujuan Anda.');
            }
        } else {
            abort(403, 'Anda tidak berhak mengakses halaman ini.');
        }

        $tugas->loadMissing('penerima.pengguna');
        $detailFields = collect();

        // nilai yang sudah tersimpan (JSON: [tugas_detail_id => value])
        $values = [];
        if (!empty($tugas->detail_tugas)) {
            $json = json_decode($tugas->detail_tugas, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $values = $json;
            }
        }

        return view('surat_tugas.detail', compact('tugas', 'detailFields', 'values'));
    }

    /** Simpan nilai detail ke kolom detail_tugas (JSON) */
    public function updateDetail(Request $request, TugasHeader $tugas)
    {
        $user = Auth::user();
        $peranId = $user->peran_id;
        $isCreator = $tugas->dibuat_oleh === $user->id;

        if ($peranId === 1) {
            if (!($isCreator && in_array($tugas->status_surat, ['draft', 'pending', 'ditolak'], true))) {
                abort(403, 'Anda tidak berhak menyimpan detail surat ini.');
            }
        } elseif (in_array($peranId, [2, 3], true)) {
            if (!($tugas->status_surat === 'pending' && (int) $tugas->penandatangan === (int) $user->id)) {
                abort(403, 'Anda hanya dapat menyimpan detail ketika surat menunggu persetujuan Anda.');
            }
        } else {
            abort(403, 'Anda tidak berhak mengakses aksi ini.');
        }

        $validated = $request->validate([
            'details' => ['nullable', 'array'],
            'details.*' => ['nullable', 'string', 'max:1000'],
        ]);

        // simpan aman sebagai JSON (kunci = id dari tabel tugas_detail)
        $tugas->detail_tugas = json_encode($validated['details'] ?? []);
        $tugas->save();

        return redirect()->route('surat_tugas.detail.edit', $tugas->id)->with('success', 'Detail tugas berhasil diperbarui.');
    }

    // =========================================================
    // NOMOR TURUNAN (SUFFIX) — Buat surat turunan dari parent
    // =========================================================

    /**
     * Buat Surat Tugas turunan (suffix) dari parent.
     * Meng-copy semua konten parent, reserve suffix (e.g. "A"),
     * dan redirect ke halaman edit untuk set tanggal_surat.
     */
    public function createTurunan(Request $request, TugasHeader $tugas)
    {
        // Validasi: hanya boleh dari surat utama (bukan turunan)
        if ($tugas->isTurunan()) {
            return back()->with('error', 'Tidak bisa membuat turunan dari surat yang sudah turunan.');
        }

        // Validasi: parent harus punya nomor valid
        if (empty($tugas->nomor) || str_starts_with($tugas->nomor, 'DRAFT-')) {
            return back()->with('error', 'Surat ini belum memiliki nomor yang valid untuk dibuat turunan.');
        }

        // Load relasi yang dibutuhkan
        $tugas->load(['penerima.pengguna', 'klasifikasiSurat']);

        try {
            // Build validated data dari parent (copy semua konten)
            $validatedData = [
                'is_turunan' => true,
                'parent_tugas_id' => $tugas->id,
                // Konten yang di-copy dari parent
                'jenis_tugas' => $tugas->jenis_tugas,
                'tugas' => $tugas->tugas,
                'detail_tugas' => $tugas->detail_tugas,
                'status_penerima' => $tugas->status_penerima,
                'redaksi_pembuka' => $tugas->redaksi_pembuka,
                'penutup' => $tugas->penutup,
                'tembusan' => $tugas->tembusan,
                'waktu_mulai' => $tugas->waktu_mulai?->format('Y-m-d\TH:i'),
                'waktu_selesai' => $tugas->waktu_selesai?->format('Y-m-d\TH:i'),
                'tempat' => $tugas->tempat,
                'nama_umum' => $tugas->nama_umum,
                'klasifikasi_surat_id' => $tugas->klasifikasi_surat_id,
                'semester' => $tugas->semester,
                // Tanggal: default hari ini (admin bisa edit nanti)
                'tanggal_surat' => now()->format('Y-m-d'),
                // Metadata nomor (copy dari parent)
                'bulan' => $tugas->bulan,
                'tahun' => $tugas->tahun,
                // Penandatangan & asal surat (copy dari parent)
                'penandatangan_id' => $tugas->penandatangan,
                'asal_surat' => $tugas->asal_surat,
                // Penerima (copy dari parent)
                'penerima_internal' => $tugas->penerima
                    ->whereNotNull('pengguna_id')
                    ->pluck('pengguna_id')
                    ->toArray(),
                'penerima_eksternal' => $tugas->penerima
                    ->whereNull('pengguna_id')
                    ->map(fn ($p) => [
                        'nama' => $p->nama_penerima,
                        'jabatan' => $p->jabatan_penerima,
                        'instansi' => $p->instansi,
                    ])
                    ->values()
                    ->toArray(),
            ];

            // Create as draft (admin needs to set tanggal_surat lalu submit)
            $turunan = $this->tugasService->createTugas($validatedData, 'draft');

            \Log::info('Surat turunan created', [
                'parent_id' => $tugas->id,
                'parent_nomor' => $tugas->nomor,
                'turunan_id' => $turunan->id,
                'turunan_nomor' => $turunan->nomor,
                'suffix' => $turunan->suffix,
            ]);

            return redirect()
                ->route('surat_tugas.edit', $turunan->id)
                ->with('success', "Surat turunan {$turunan->nomor} berhasil dibuat! Silakan edit tanggal surat dan ajukan.");

        } catch (\Exception $e) {
            \Log::error('Failed to create turunan', [
                'parent_id' => $tugas->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat nomor turunan: ' . $e->getMessage());
        }
    }
}
