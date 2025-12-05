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

    private function toRoman(int $number): string
    {
        if ($number <= 0 || $number > 3999) {
            return '';
        }
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];
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

    /**
     * Konversi nilai kolom `bulan` (bisa romawi / angka) jadi label yang enak dibaca.
     * Contoh:
     *  - "I"   -> "Januari (I)"
     *  - "03"  -> "Maret (03)"
     *  - "XI"  -> "November (XI)"
     */
    private function getBulanLabel(string $bulan): string
    {
        $romanMap = [
            'I' => 'Januari',
            'II' => 'Februari',
            'III' => 'Maret',
            'IV' => 'April',
            'V' => 'Mei',
            'VI' => 'Juni',
            'VII' => 'Juli',
            'VIII' => 'Agustus',
            'IX' => 'September',
            'X' => 'Oktober',
            'XI' => 'November',
            'XII' => 'Desember',
        ];

        $upper = strtoupper(trim($bulan));

        // Kalau cocok romawi
        if (isset($romanMap[$upper])) {
            return $romanMap[$upper] . ' (' . $upper . ')';
        }

        // Kalau angka, coba mapping ke nama bulan
        $int = (int) $bulan;
        if ($int >= 1 && $int <= 12) {
            $nama = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ][$int];

            return $nama . ' (' . $bulan . ')';
        }

        // fallback: kembalikan apa adanya
        return $bulan;
    }

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
            $bulanList[$bulan] = $this->getBulanLabel($bulan);
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

        $taskMaster = JenisTugas::with('subtugas.detail')->orderBy('nama')->get();
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

        // ✅ STEP 1: Validasi input filter
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,pending,disetujui,ditolak',
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'bulan' => 'nullable|string|max:10',
            'penandatangan' => 'nullable|integer|exists:pengguna,id',
            'pembuat' => 'nullable|integer|exists:pengguna,id',
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
            'sort' => 'nullable|in:created_at,tanggal_surat,nomor',
            'order' => 'nullable|in:asc,desc',
        ]);

        // ✅ STEP 2: Base query dengan eager loading penting
        $query = TugasHeader::withFullRelations();

        // ✅ STEP 3: Apply advance filters (scope di model)
        $query->applyFilters($validated);

        // ✅ STEP 4: Sorting
        $sortBy = $validated['sort'] ?? 'created_at';
        $sortOrder = $validated['order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // ✅ STEP 5: Eksekusi query
        $list = $query->get();

        // ✅ STEP 6: Hitung statistik per status
        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
            'ditolak' => $list->where('status_surat', 'ditolak')->count(),
        ];

        // ✅ STEP 7: Data dropdown filter
        $filterData = $this->getFilterDropdownData();

        // Bisa dipakai di view untuk bedakan mode list (all / approve / lainnya)
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
        $bulanRomawi = $this->toRoman($bulanInt);
        $autoNomor = sprintf('/TG/UNIKA/%s/%s', $bulanRomawi, $tahun);
        $tanggalHariIni = now()->format('Y-m-d');
        return view('surat_tugas.create', compact('admins', 'pejabat', 'users', 'taskMaster', 'autoNomor', 'tahun', 'semester', 'klasifikasi', 'bulanRomawi', 'tanggalHariIni'))->with('tugas', null);
    }

    public function store(StoreTugasRequest $request)
    {
        $mode = $this->resolveMode($request);
        $validated = $request->validated();
        if (!isset($validated['tanggal_surat'])) {
            $validated['tanggal_surat'] = now()->format('Y-m-d');
        }

        try {
            $tugas = $this->tugasService->createTugas($validated, $mode);
            $message = $tugas->status_surat === 'pending' ? 'Surat tugas berhasil diajukan!' : 'Surat tugas disimpan sebagai draft!';
            return redirect()->route('surat_tugas.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan Surat Tugas', [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => Auth::id(),
            ]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan surat tugas.');
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
            'tugas' => $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna']),
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

        $assets = $this->getSigningAssets($tugas);
        $preview = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $ttdWMm !== false ? $ttdWMm : $assets['ttdW'],
            'cap_w_mm' => $capWMm !== false ? $capWMm : $assets['capW'],
            'cap_opacity' => $capOpacity !== false ? $capOpacity : $assets['capOpacity'],
        ];

        return view('surat_tugas.partials.approve-preview', [
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
            'ttd_w_mm' => 'required|integer|min:30|max:60',
            'cap_w_mm' => 'required|integer|min:25|max:45',
            'cap_opacity' => 'required|numeric|min:0.7|max:1.0',
        ]);

        try {
            $tugas = $this->tugasService->approveTugas($tugas, $validated);
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
            return back()->with('error', 'Terjadi kesalahan saat menyetujui surat.');
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
            return back()->with('error', 'Gagal menghapus surat tugas.');
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
        $kop = MasterKopSurat::query()->first();
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
                'isRemoteEnabled' => false,
                'dpi' => 96,
                'chroot' => public_path(),
            ])
            ->output();
    }

    public function show(TugasHeader $tugas)
    {
        // Load relasi yang dibutuhkan
        $tugas->load(['pembuat:id,nama_lengkap,email', 'penandatanganUser:id,nama_lengkap,email,peran_id,jabatan', 'penandatanganUser.peran:id,nama', 'klasifikasi:id,kode,deskripsi', 'penerima.pengguna:id,nama_lengkap']);

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
        $tugas->load(['penerima.pengguna']);
        $tanggalHariIni = now()->format('Y-m-d');

        $nomorParts = explode('/', $tugas->nomor);
        $baseNomor = '/' . implode('/', array_slice($nomorParts, 1));

        if ($peranId === 1) {
            if (!($tugas->dibuat_oleh === $user->id && in_array($tugas->status_surat, ['draft', 'ditolak'], true))) {
                abort(403, 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3], true)) {
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

    public function highlight(TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'asalSurat', 'penerima.pengguna']);
        $penerimaList = $tugas->penerima->pluck('pengguna.nama_lengkap')->all();
        $showSigns = $this->shouldShowSignatures($tugas);
        return response()->view('surat_tugas.highlight', compact('tugas', 'penerimaList', 'showSigns'))->header('X-Frame-Options', 'SAMEORIGIN');
    }

    public function downloadPdf(TugasHeader $tugas)
    {
        $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran', 'tugasDetail.subTugas']);
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

    /** ✅ FIXED: Show approve form dengan semua data yang dibutuhkan */
    public function showApproveForm(TugasHeader $tugas)
    {
        $tugas->load(['penerima.pengguna.peran', 'klasifikasiSurat', 'penandatanganUser.peran', 'pembuat', 'creator', 'tugasDetail.subTugas.jenisTugas']);
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
        $tugas->load(['pembuat', 'penandatanganUser', 'penerima.pengguna.peran', 'tugasDetail.subTugas']);
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

        // load relasi apa pun nama yang dipakai di model (tugasDetail / detailMaster)
        $tugas->loadMissing('penerima.pengguna', 'tugasDetail.subTugas.detail', 'detailMaster.subTugas.detail');

        // ambil "master detail" melalui salah satu relasi yang tersedia
        $detailSource = $tugas->tugasDetail ?: $tugas->detailMaster;
        // field-field yang harus diisi untuk sub_tugas tsb
        $detailFields = optional(optional($detailSource)->subTugas)->detail ?? collect();

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
}
