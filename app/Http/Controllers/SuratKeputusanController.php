<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeputusanRequest;
use App\Http\Requests\UpdateKeputusanRequest;
// ✅ (optional, kalau mau pakai)
use App\Models\KeputusanAttachment;
use App\Models\KeputusanHeader; // ✅ FASE 1.2 - TAMBAHKAN INI
use App\Models\MasterKopSurat;
use App\Models\Notifikasi; // ✅ Tambahkan kalau belum ada
use App\Models\User;
use App\Services\SkPdfService;
use App\Services\SuratKeputusanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SuratKeputusanController extends Controller
{
    protected SuratKeputusanService $skService;

    protected SkPdfService $pdfService;

    public function __construct(SuratKeputusanService $skService, SkPdfService $pdfService)
    {
        $this->skService = $skService;
        $this->pdfService = $pdfService;
    }

    /* ==================== Helpers umum ==================== */

    /** Dependency untuk form (hanya yang relevan) */
    private function getFormDependencies(): array
    {
        // ✅ Admin users (Admin TU) + eager load peran
        $admins = \App\Models\User::with('peran')->select('id', 'nama_lengkap', 'peran_id')->where('peran_id', 1)->orderBy('nama_lengkap')->get();

        // ✅ Pejabat (Dekan & Wakil Dekan) dengan NPP + eager load peran
        $pejabat = \App\Models\User::with('peran')
            ->select('id', 'nama_lengkap', 'peran_id', 'npp')
            ->whereIn('peran_id', [2, 3])
            ->orderBy('peran_id')
            ->orderBy('nama_lengkap')
            ->get();

        // ✅ Active users + eager load peran
        $users = \App\Models\User::with('peran')->select('id', 'nama_lengkap', 'peran_id', 'email', 'status')->where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return compact('admins', 'pejabat', 'users');
    }

    /** Apakah PDF harus menampilkan TTD/Cap */
    private function shouldShowSignatures(KeputusanHeader $sk): bool
    {
        return in_array($sk->status_surat, ['disetujui', 'terbit', 'arsip'], true) && ! empty($sk->signed_at);
    }

    /* ==================== Daftar / List ==================== */

    public function index(Request $request)
    {
        // Validasi input filter
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,pending,disetujui,ditolak,terbit,arsip',
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'bulan' => 'nullable|integer|min:1|max:12',
            'penandatangan' => 'nullable|integer|exists:pengguna,id',
            'pembuat' => 'nullable|integer|exists:pengguna,id',
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date|after_or_equal:tanggal_dari',
            'sort' => 'nullable|in:created_at,tanggal_surat,nomor',
            'order' => 'nullable|in:asc,desc',
        ]);

        // Base query dengan relasi
        $query = KeputusanHeader::with([
            'pembuat:id,nama_lengkap',
            'penandatanganUser:id,nama_lengkap',
            'penerima:id,nama_lengkap',
            'penerbit:id,nama_lengkap', // ✅ TAMBAHAN BARU
            'pengarsip:id,nama_lengkap', // ✅ TAMBAHAN BARU
        ]);
        // Filter by status (default: draft, pending, disetujui, ditolak)
        $statusFilter = $validated['status'] ?? null;
        if ($statusFilter) {
            $query->where('status_surat', $statusFilter);
        } else {
            // Default: hanya tampilkan yang masih dikerjakan
            $query->whereIn('status_surat', ['draft', 'pending', 'disetujui', 'ditolak']);
        }

        // Apply advanced filters
        $query->applyFilters($validated);

        // Sorting
        $sortBy = $validated['sort'] ?? 'created_at';
        $sortOrder = $validated['order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Get results
        $list = $query->get();

        // Statistics
        $stats = [
            'draft' => $list->where('status_surat', 'draft')->count(),
            'pending' => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
            'ditolak' => $list->where('status_surat', 'ditolak')->count(),
        ];

        // Data untuk dropdown filter
        $filterData = $this->getFilterDropdownData();

        $mode = 'list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode', 'filterData'));
    }

    /**
     * ✅ FASE 1.1: Helper untuk data dropdown filter
     */
    private function getFilterDropdownData(): array
    {
        // Tahun unik dari SK yang ada
        $tahunList = KeputusanHeader::selectRaw('DISTINCT tahun')->whereNotNull('tahun')->orderByDesc('tahun')->pluck('tahun');

        // Penandatangan (Dekan & WD)
        $penandatanganList = \App\Models\User::select('id', 'nama_lengkap', 'jabatan')
            ->whereIn('peran_id', [2, 3]) // Dekan & WD
            ->orderBy('nama_lengkap')
            ->get();

        // Pembuat (Admin TU)
        $pembuatList = \App\Models\User::select('id', 'nama_lengkap')
            ->where('peran_id', 1) // Admin TU
            ->orderBy('nama_lengkap')
            ->get();

        // Bulan
        $bulanList = [
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
        ];

        return [
            'tahun' => $tahunList,
            'bulan' => $bulanList,
            'penandatangan' => $penandatanganList,
            'pembuat' => $pembuatList,
        ];
    }

    public function terbitList()
    {
        $list = KeputusanHeader::with([
            'pembuat:id,nama_lengkap',
            'penandatanganUser:id,nama_lengkap',
            'penerima:id,nama_lengkap',
            'penerbit:id,nama_lengkap', // ✅ TAMBAHKAN INI
        ])
            ->where('status_surat', 'terbit')
            ->orderByDesc('tanggal_terbit')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'terbit' => $list->count(),
        ];

        $mode = 'terbit-list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    public function arsipList()
    {
        $list = KeputusanHeader::with([
            'pembuat:id,nama_lengkap',
            'penandatanganUser:id,nama_lengkap',
            'penerima:id,nama_lengkap',
            'penerbit:id,nama_lengkap', // ✅ TAMBAHKAN INI
            'pengarsip:id,nama_lengkap', // ✅ TAMBAHKAN INI
        ])
            ->where('status_surat', 'arsip')
            ->orderByDesc('tanggal_arsip')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'arsip' => $list->count(),
        ];

        $mode = 'arsip-list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    public function approveList()
    {
        $list = KeputusanHeader::with(['pembuat', 'penerima:id,nama_lengkap', 'penandatanganUser:id,nama_lengkap'])
            ->where('status_surat', 'pending')
            ->where('penandatangan', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $stats = ['draft' => 0, 'pending' => $list->count(), 'disetujui' => 0];
        $mode = 'approve-list';

        return view('surat_keputusan.index', compact('list', 'stats', 'mode'));
    }

    /** Keputusan saya = SK yang saya buat atau yang mencantumkan saya sebagai penerima */
    public function mine()
    {
        $userId = Auth::id();
        $list = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap'])
            ->where(function ($query) use ($userId) {
                $query->whereHas('penerima', fn ($q) => $q->whereKey($userId))
                    ->orWhere('dibuat_oleh', $userId);
            })
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
        // Pastikan user punya hak membuat SK
        $this->authorize('create', KeputusanHeader::class);

        // Ambil dependency form (admins, pejabat, users) + peran (sudah eager load di helper)
        $deps = $this->getFormDependencies();

        // Setup bulan romawi dan tahun untuk builder nomor
        $deps['bulanRomawi'] = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $deps['currentYear'] = now()->year;
        $deps['currentRomawi'] = $deps['bulanRomawi'][now()->month];

        // Preset tembusan
        $deps['tembusanPresets'] = ['Yth. Rektor', 'Yth. Wakil Rektor I', 'Yth. Wakil Rektor II', 'Dekan Fakultas Ilmu Komputer', 'BAAK', 'BAUK', 'BAK', 'Kepala Program Studi Sistem Informasi', 'Unit Kepegawaian', 'Arsip'];

        // Tanggal default untuk field tanggal_surat
        $deps['tanggalHariIni'] = now()->format('Y-m-d');

        // View create hanya butuh deps ini, form akan handle mode 'create' otomatis
        return view('surat_keputusan.create', $deps);
    }

    public function store(StoreKeputusanRequest $request)
    {
        $this->authorize('create', KeputusanHeader::class);

        $validatedData = $request->validated();
        $mode = $request->input('mode');
        $status = ($mode === 'pending' || $mode === 'terkirim') ? 'pending' : 'draft';

        // Validasi conditional penandatangan/penerima sudah dihandle oleh StoreKeputusanRequest

        try {
            $sk = $this->skService->createKeputusan($validatedData, $status);

            $message = $status === 'draft' ? 'Surat Keputusan berhasil disimpan.' : 'SK berhasil diajukan ke penandatangan.';

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
        // Hak akses
        $this->authorize('update', $surat_keputusan);

        // Ambil dependency form (admins, pejabat, users) + peran
        $deps = $this->getFormDependencies();

        // Eager load relasi yang dipakai di view
        $surat_keputusan->load([
            'penerima:id,nama_lengkap', // untuk daftar penerima
            'attachments.uploader', // untuk attachments_section (uploader nama dsb)
            'pembuat.peran', // kalau di view butuh peran pembuat
            'penandatanganUser.peran', // kalau di view butuh peran penandatangan
        ]);

        // Setup bulan dan tahun untuk builder nomor
        $deps['bulanRomawi'] = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $deps['currentYear'] = now()->year;
        $deps['currentRomawi'] = $deps['bulanRomawi'][now()->month];

        // Preset tembusan
        $deps['tembusanPresets'] = ['Yth. Rektor', 'Yth. Wakil Rektor I', 'Yth. Wakil Rektor II', 'Dekan Fakultas Ilmu Komputer', 'BAAK', 'BAUK', 'BAK', 'Kepala Program Studi Sistem Informasi', 'Unit Kepegawaian', 'Arsip'];

        // Default tanggal untuk field tanggal_surat kalau belum terisi
        $deps['tanggalHariIni'] = now()->format('Y-m-d');

        // Kirim ke view edit
        return view(
            'surat_keputusan.edit',
            array_merge($deps, [
                'keputusan' => $surat_keputusan, // dipakai di _form.blade.php
                'sk' => $surat_keputusan, // dipakai di edit.blade.php (header dsb)
                'isEdit' => true,
            ]),
        );
    }

    /**
     * ✅ Update SK dengan validasi status dan mode yang benar
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateKeputusanRequest $request, KeputusanHeader $surat_keputusan)
    {
        // ✅ DEBUG: Log request untuk tracking
        \Log::info('SuratKeputusanController update() dipanggil', [
            'user_id' => auth()->id(),
            'user_peran_id' => auth()->user()->peran_id,
            'sk_id' => $surat_keputusan->id,
            'sk_status' => $surat_keputusan->status_surat,
            'sk_dibuat_oleh' => $surat_keputusan->dibuat_oleh,
            'mode' => $request->input('mode'),
        ]);

        // ✅ Authorization check dengan error handling
        try {
            $this->authorize('update', $surat_keputusan);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning('Update SK unauthorized', [
                'user_id' => auth()->id(),
                'user_peran_id' => auth()->user()->peran_id,
                'sk_id' => $surat_keputusan->id,
                'sk_status' => $surat_keputusan->status_surat,
                'sk_dibuat_oleh' => $surat_keputusan->dibuat_oleh,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'authorization' => 'Anda tidak memiliki akses untuk mengubah SK ini. Status: '.$surat_keputusan->status_surat,
                ])
                ->withInput();
        }

        $validatedData = $request->validated();
        $mode = $request->input('mode');

        // ✅ Logika status yang benar
        if ($mode === 'pending' || $mode === 'terkirim') {
            // Hanya draft atau ditolak yang bisa diajukan
            if (! in_array($surat_keputusan->status_surat, ['draft', 'ditolak'], true)) {
                \Log::warning('Update SK gagal: Status tidak valid untuk pengajuan', [
                    'sk_id' => $surat_keputusan->id,
                    'current_status' => $surat_keputusan->status_surat,
                    'mode' => $mode,
                ]);

                return back()
                    ->withErrors(['mode' => "SK dengan status {$surat_keputusan->status_surat} tidak bisa diajukan."])
                    ->withInput();
            }
            $newStatus = 'pending';
        } elseif ($mode === 'draft') {
            $newStatus = 'draft';
        } else {
            $newStatus = $surat_keputusan->status_surat;
        }

        // Validasi penandatangan sudah dihandle oleh UpdateKeputusanRequest

        try {
            // ✅ Update SK melalui service
            $sk = $this->skService->updateKeputusan($surat_keputusan, $validatedData, $newStatus);

            \Log::info('Update SK berhasil', [
                'sk_id' => $sk->id,
                'old_status' => $surat_keputusan->status_surat,
                'new_status' => $sk->status_surat,
                'mode' => $mode,
                'nomor' => $sk->nomor,
            ]);

            $message = $newStatus === 'pending'
                ? 'Perubahan disimpan dan SK diajukan kembali.'
                : 'SK berhasil diperbarui.';

            return redirect()
                ->route('surat_keputusan.index')
                ->with('success', $message);

        } catch (\Illuminate\Database\QueryException $e) {
            // ✅ Duplicate nomor surat
            if ($e->getCode() === '23000' || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                \Log::error('Update SK gagal: Nomor duplikat', [
                    'sk_id' => $surat_keputusan->id,
                    'nomor' => $validatedData['nomor'] ?? null,
                    'error' => $e->getMessage(),
                ]);

                return back()
                    ->withErrors([
                        'nomor' => 'Nomor surat sudah digunakan. Silakan generate nomor baru.',
                    ])
                    ->withInput();
            }

            \Log::error('Update SK gagal: Database error', [
                'sk_id' => $surat_keputusan->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return back()
                ->withErrors([
                    'database' => 'Terjadi kesalahan database. Silakan coba lagi.',
                ])
                ->withInput();

        } catch (\Exception $e) {
            \Log::error('Update SK gagal: Unexpected error', [
                'sk_id' => $surat_keputusan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors([
                    'error' => 'Terjadi kesalahan saat memperbarui SK. Silakan coba lagi.',
                ])
                ->withInput();
        }
    }

    /* ==================== Workflow ==================== */

    public function submit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('submit', $surat_keputusan);

        if (! $surat_keputusan->penandatangan) {
            return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi sebelum pengajuan.']);
        }

        $this->skService->submitForApproval($surat_keputusan);

        return back()->with('success', 'Dikirim untuk persetujuan.');
    }

    public function approveForm(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $assets = $this->pdfService->getSigningAssets($surat_keputusan);
        $preview = [
            'ttd_image_b64' => $assets['ttdImageB64'],
            'cap_image_b64' => $assets['capImageB64'],
            'ttd_w_mm' => $surat_keputusan->ttd_config['w_mm'] ?? ($surat_keputusan->ttd_w_mm ?? $assets['ttdW']),
            'cap_w_mm' => $surat_keputusan->cap_config['w_mm'] ?? ($surat_keputusan->cap_w_mm ?? $assets['capW']),
            'cap_opacity' => $surat_keputusan->cap_config['opacity'] ?? ($surat_keputusan->cap_opacity ?? $assets['capOpacity']),
            // Offsets
            'ttd_x_mm' => $surat_keputusan->ttd_config['x'] ?? 0,
            'ttd_y_mm' => $surat_keputusan->ttd_config['y'] ?? 0,
            'cap_x_mm' => $surat_keputusan->cap_config['x'] ?? 0,
            'cap_y_mm' => $surat_keputusan->cap_config['y'] ?? 0,
        ];

        return view('surat_keputusan.approve', [
            'sk' => $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']),
            'kop' => $assets['kop'],
            'preview' => $preview,
            'ttdW' => $preview['ttd_w_mm'],
            'capW' => $preview['cap_w_mm'],
            'capOpacity' => $preview['cap_opacity'],

            'ttdX' => $preview['ttd_x_mm'],
            'ttdY' => $preview['ttd_y_mm'],
            'capX' => $preview['cap_x_mm'],
            'capY' => $preview['cap_y_mm'],

            'ttdImageB64' => $assets['ttdImageB64'],
            'capImageB64' => $assets['capImageB64'],
            'showSigns' => true,
        ]);
    }

    public function approvePreview(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $assets = $this->pdfService->getSigningAssets($surat_keputusan);
        $ttdW = (int) $request->input('ttd_w_mm', $surat_keputusan->ttd_w_mm ?? $assets['ttdW']);
        $capW = (int) $request->input('cap_w_mm', $surat_keputusan->cap_w_mm ?? $assets['capW']);
        $capOpacity = (float) $request->input('cap_opacity', $surat_keputusan->cap_opacity ?? $assets['capOpacity']);

        // Offsets
        $ttdX = (int) $request->input('ttd_x_mm', $surat_keputusan->ttd_config['x'] ?? 0);
        $ttdY = (int) $request->input('ttd_y_mm', $surat_keputusan->ttd_config['y'] ?? 0);
        $capX = (int) $request->input('cap_x_mm', $surat_keputusan->cap_config['x'] ?? 0);
        $capY = (int) $request->input('cap_y_mm', $surat_keputusan->cap_config['y'] ?? 0);

        return view('surat_keputusan.partials._approve_preview', [
            'sk' => $surat_keputusan,
            'kop' => $assets['kop'],
            'showSigns' => true,
            'ttdImageB64' => $assets['ttdImageB64'],
            'capImageB64' => $assets['capImageB64'],
            'ttdW' => $ttdW,
            'capW' => $capW,
            'capOpacity' => $capOpacity,
            'ttdX' => $ttdX,
            'ttdY' => $ttdY,
            'capX' => $capX,
            'capY' => $capY,
        ]);
    }

    public function approve(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('approve', $surat_keputusan);

        $validated = $request->validate([
            'ttd_w_mm' => 'required|integer|min:10|max:150',
            'cap_w_mm' => 'required|integer|min:10|max:100',
            'cap_opacity' => 'required|numeric|min:0.7|max:1.0',
            'ttd_x_mm' => 'nullable|integer|min:-100|max:100',
            'ttd_y_mm' => 'nullable|integer|min:-100|max:100',
            'cap_x_mm' => 'nullable|integer|min:-100|max:100',
            'cap_y_mm' => 'nullable|integer|min:-100|max:100',
            'kode_klasifikasi' => 'nullable|string|max:20',
            'unit' => 'nullable|string|max:20',
        ]);

        try {
            // Update config JSON sebelum generate PDF
            $surat_keputusan->ttd_config = [
                'w_mm' => $validated['ttd_w_mm'],
                'x' => $validated['ttd_x_mm'] ?? 0,
                'y' => $validated['ttd_y_mm'] ?? 0,
            ];
            $surat_keputusan->cap_config = [
                'w_mm' => $validated['cap_w_mm'],
                'opacity' => $validated['cap_opacity'],
                'x' => $validated['cap_x_mm'] ?? 0,
                'y' => $validated['cap_y_mm'] ?? 0,
            ];

            // Simpan legacy columns juga jika perlu
            $surat_keputusan->ttd_w_mm = $validated['ttd_w_mm'];
            $surat_keputusan->cap_w_mm = $validated['cap_w_mm'];
            $surat_keputusan->cap_opacity = $validated['cap_opacity'];
            $surat_keputusan->save(); // Ensure config is saved

            // Simpan perubahan config ke DB dulu agar persistent?
            // Service 'approveAndGenerateNumber' mungkin melakukan save() sendiri.
            // Kita update instance model di memori, lalu service melakukan update status & nomor.
            // Sebaiknya kita save dulu attribute ini ATAU biarkan service save.
            // Cek implementation service... asumsikan service melakukan $sk->save().
            // Tapi untuk amannya kita set attribute ini pada object $surat_keputusan
            // yang dilempar ke service.

            $sk = $this->skService->approveAndGenerateNumber($surat_keputusan, $validated);

            $pdfBytes = $this->renderSkPdfWithSign($sk);
            $pdfPath = "private/surat_keputusan/signed/{$sk->id}_".md5((string) ($sk->nomor ?? '')).'.pdf';
            Storage::disk('local')->put($pdfPath, $pdfBytes);
            $sk->update(['signed_pdf_path' => $pdfPath]);

            return redirect()
                ->route('surat_keputusan.approveList')
                ->with('success', 'SK '.$sk->nomor.' berhasil disetujui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            // ✅ FIXED: Sanitize log message
            Log::error('Gagal approve SK #'.$surat_keputusan->id, [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyetujui SK.');
        }
    }

    public function show(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('view', $surat_keputusan);

        // ✅ Eager load relasi
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'attachments.uploader']);

        // ✅ TAMBAHKAN INI: Get signing assets (TTD & Cap)
        $assets = $this->pdfService->getSigningAssets($surat_keputusan);

        // ✅ TAMBAHKAN INI: Tentukan apakah harus show TTD/Cap
        $showSigns = $this->shouldShowSignatures($surat_keputusan) || in_array($surat_keputusan->status_surat, ['terbit', 'arsip'], true);

        // ✅ TAMBAHKAN INI: Kirim semua data ke view
        return view(
            'surat_keputusan.show',
            array_merge(
                [
                    'sk' => $surat_keputusan,
                    'showSigns' => $showSigns,
                ],
                $assets,
            ),
        );
    }

    public function reject(Request $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('reject', $surat_keputusan);
        $note = trim((string) $request->input('note'));

        $this->skService->rejectKeputusan($surat_keputusan, $note);

        return back()->with('success', 'SK ditolak dan dikembalikan ke pembuat'.($note ? ' (catatan dikirim).' : '.'));
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
                    'pesan' => 'SK '.($surat_keputusan->nomor ?? '(tanpa nomor)').' ditarik ke Draft oleh '.auth()->user()->nama_lengkap.'.',
                    'dibaca' => false,
                    'dibuat_pada' => now(),
                ]);
            }
        });

        return back()->with('success', 'SK ditarik ke Draft untuk direvisi.');
    }

    /**
     * Terbitkan SK yang sudah disetujui
     */
    public function terbitkan(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('publish', $surat_keputusan);

        if ($surat_keputusan->status_surat !== 'disetujui') {
            return back()->withErrors([
                'status' => 'Hanya SK yang sudah disetujui yang bisa diterbitkan.',
            ]);
        }

        try {
            DB::transaction(function () use ($surat_keputusan) {
                // ✅ Update status dan timestamp
                $surat_keputusan->update([
                    'status_surat' => 'terbit',
                    'tanggal_terbit' => now(),
                    'terbitkan_oleh' => auth()->id(),
                ]);

                // ✅ Kirim notifikasi ke penerima internal (user sistem)
                foreach ($surat_keputusan->penerima as $penerima) {
                    \App\Models\Notifikasi::create([
                        'pengguna_id' => $penerima->id,
                        'tipe' => 'surat_keputusan',
                        'referensi_id' => $surat_keputusan->id,
                        'pesan' => 'SK "'.$surat_keputusan->tentang.'" telah diterbitkan dan berlaku efektif.',
                        'dibaca' => false,
                        'dibuat_pada' => now(),
                    ]);
                }

                // ✅ Kirim email ke penerima eksternal (alamat email di luar sistem)
                if (! empty($surat_keputusan->penerima_eksternal)) {
                    foreach ($surat_keputusan->penerima_eksternal as $emailEksternal) {
                        \App\Jobs\SendSkEmail::dispatch($surat_keputusan->id, $emailEksternal)->delay(now()->addSeconds(5));
                    }
                }
            });

            return redirect()
                ->route('surat_keputusan.terbitList')
                ->with('success', 'SK berhasil diterbitkan pada '.now()->format('d M Y H:i').' WIB.');
        } catch (\Exception $e) {
            \Log::error('Gagal menerbitkan SK: '.$e->getMessage(), [
                'keputusan_id' => $surat_keputusan->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat menerbitkan SK.',
            ]);
        }
    }

    /**
     * Arsipkan SK yang sudah terbit
     */
    public function arsipkan(KeputusanHeader $suratKeputusan)
    {
        $this->authorize('archive', $suratKeputusan);

        // Validasi status
        if ($suratKeputusan->status_surat !== 'terbit') {
            return back()->withErrors([
                'status' => 'Hanya SK yang sudah terbit yang bisa diarsipkan.',
            ]);
        }

        try {
            DB::transaction(function () use ($suratKeputusan) {
                // Update status dan timestamp
                $suratKeputusan->update([
                    'status_surat' => 'arsip',
                    'tanggal_arsip' => now(),
                    'arsipkan_oleh' => auth()->id(),
                ]);

                // ✅ [OPSIONAL] LOG PERUBAHAN STATUS
                if (Schema::hasTable('keputusan_status_logs')) {
                    DB::table('keputusan_status_logs')->insert([
                        'keputusan_id' => $suratKeputusan->id,
                        'status_dari' => 'terbit',
                        'status_ke' => 'arsip',
                        'diubah_oleh' => auth()->id(),
                        'catatan' => 'SK diarsipkan oleh '.auth()->user()->nama_lengkap,
                        'created_at' => now(),
                    ]);
                }
            });

            return redirect()
                ->route('surat_keputusan.arsipList')
                ->with('success', 'SK berhasil diarsipkan pada '.now()->format('d M Y H:i').'.');
        } catch (\Exception $e) {
            Log::error('Gagal mengarsipkan SK: '.$e->getMessage(), [
                'keputusan_id' => $suratKeputusan->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat mengarsipkan SK. Silakan coba lagi.',
            ]);
        }
    }

    /**
     * Action: Buka Arsip (Restore)
     */
    public function bukaArsip(KeputusanHeader $surat_keputusan)
    {
        if (Auth::user()->peran_id !== 1) {
            abort(403, 'Akses ditolak. Hanya Admin TU yang dapat membuka arsip.');
        }

        if ($surat_keputusan->status_surat !== 'arsip') {
            return back()->with('error', 'Surat keputusan ini tidak sedang diarsipkan.');
        }

        try {
            DB::transaction(function () use ($surat_keputusan) {
                // Return to 'terbit' status as that's the pre-archive state
                $surat_keputusan->update([
                    'status_surat' => 'terbit',
                    'tanggal_arsip' => null,
                    'arsipkan_oleh' => null,
                ]);
            });

            return redirect()->route('surat_keputusan.arsipList')->with('success', 'Surat Keputusan berhasil dikeluarkan dari arsip.');
        } catch (\Exception $e) {
            Log::error('Gagal membuka arsip SK', [
                'sk_id' => $surat_keputusan->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuka arsip SK: '.$e->getMessage());
        }
    }

    /**
     * Batal terbitkan SK (rollback dari terbit ke disetujui)
     * Hanya untuk Admin/TU
     */
    public function batalTerbitkan(KeputusanHeader $suratKeputusan)
    {
        $this->authorize('unpublish', $suratKeputusan);

        // Validasi status
        if ($suratKeputusan->status_surat !== 'terbit') {
            return back()->withErrors([
                'status' => 'Hanya SK dengan status terbit yang bisa dibatalkan penerbitannya.',
            ]);
        }

        try {
            DB::transaction(function () use ($suratKeputusan) {
                // Rollback status
                $suratKeputusan->update([
                    'status_surat' => 'disetujui',
                    'tanggal_terbit' => null,
                    'terbitkan_oleh' => null,
                ]);

                // ✅ [OPSIONAL] LOG PERUBAHAN STATUS
                if (Schema::hasTable('keputusan_status_logs')) {
                    DB::table('keputusan_status_logs')->insert([
                        'keputusan_id' => $suratKeputusan->id,
                        'status_dari' => 'terbit',
                        'status_ke' => 'disetujui',
                        'diubah_oleh' => auth()->id(),
                        'catatan' => 'Penerbitan SK dibatalkan oleh '.auth()->user()->nama_lengkap,
                        'created_at' => now(),
                    ]);
                }

                // Notifikasi ke penandatangan
                Notifikasi::create([
                    'pengguna_id' => $suratKeputusan->penandatangan,
                    'tipe' => 'surat_keputusan',
                    'referensi_id' => $suratKeputusan->id,
                    'pesan' => 'Penerbitan SK "'.$suratKeputusan->tentang.'" telah dibatalkan.',
                    'dibaca' => false,
                    'dibuat_pada' => now(),
                ]);
            });

            return redirect()->route('surat_keputusan.index')->with('success', 'Status penerbitan SK berhasil dibatalkan. SK kembali ke status Disetujui.');
        } catch (\Exception $e) {
            Log::error('Gagal membatalkan penerbitan SK: '.$e->getMessage(), [
                'keputusan_id' => $suratKeputusan->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'error' => 'Terjadi kesalahan. Silakan coba lagi.',
            ]);
        }
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
            Log::error('Gagal menghapus SK #'.$surat_keputusan->id, [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus SK.');
        }
    }

    /**
     * Duplikat SK menjadi draft baru
     * Copy header + struktur, reset nomor & status
     */
    public function duplicate(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('view', $surat_keputusan);

        try {
            $newSk = DB::transaction(function () use ($surat_keputusan) {
                // Replicate SK without certain fields
                $new = $surat_keputusan->replicate([
                    'nomor',
                    'status_surat',
                    'signed_at',
                    'signed_pdf_path',
                    'approved_by',
                    'approved_at',
                    'rejected_by',
                    'rejected_at',
                    'tanggal_terbit',
                    'terbitkan_oleh',
                    'tanggal_arsip',
                    'arsipkan_oleh',
                    'published_by',
                    'published_at',
                ]);

                // Reset to draft
                $new->nomor = null;
                $new->status_surat = 'draft';
                $new->dibuat_oleh = Auth::id();
                $new->tanggal_surat = now()->format('Y-m-d');
                $new->tahun = now()->year;
                $new->save();

                // Copy penerima
                foreach ($surat_keputusan->penerima as $penerima) {
                    $new->penerima()->attach($penerima->id);
                }

                return $new;
            });

            return redirect()
                ->route('surat_keputusan.edit', $newSk->id)
                ->with('success', 'SK berhasil diduplikasi sebagai draft baru. Silakan sesuaikan dan tambahkan nomor.');
        } catch (\Exception $e) {
            Log::error('Gagal menduplikat SK #'.$surat_keputusan->id, [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menduplikat SK.');
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
                'Content-Disposition' => 'inline; filename="SuratKeputusan_'.$safeNomor.'.pdf"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        $bytes = $this->renderSkPdfDraft($surat_keputusan);

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="SuratKeputusan_DRAFT_'.$safeNomor.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function preview(KeputusanHeader $surat_keputusan, Request $request)
    {
        $surat_keputusan->load(['pembuat', 'penandatanganUser', 'penerima:id,nama_lengkap']);
        $assets = $this->pdfService->getSigningAssets($surat_keputusan);
        $showSigns = $this->shouldShowSignatures($surat_keputusan);

        return response()
            ->view('surat_keputusan.preview', array_merge(['sk' => $surat_keputusan, 'showSigns' => $showSigns], $assets))
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /* ==================== Private: PDF render helpers ==================== */
    // NOTE: getSigningAssets() dan b64FromStorage() sudah dipindahkan ke SkPdfService
    // Controller menggunakan $this->pdfService->getSigningAssets() sekarang

    private function renderSkPdfWithSign(KeputusanHeader $sk): string
    {
        $assets = $this->pdfService->getSigningAssets($sk);

        $html = view('surat_keputusan.surat_pdf', array_merge(['sk' => $sk, 'showSigns' => true, 'isDraft' => false], $assets))->render();

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

    private function renderSkPdfDraft(KeputusanHeader $sk): string
    {
        $kop = MasterKopSurat::getInstance();

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

    /**
     * Map nilai tombol UI ke status_surat di DB
     */
    private function mapModeToStatus(?string $mode): ?string
    {
        return match ($mode) {
            'terkirim', 'pending' => 'pending',
            'draft' => 'draft',
            default => null,
        };
    }

    /* ==================== FASE 1.2: Lampiran File ==================== */

    /**
     * Upload attachment untuk SK
     * FASE 1.2
     */
    public function uploadAttachment(Request $request, KeputusanHeader $surat_keputusan)
    {
        // Authorization check
        $this->authorize('update', $surat_keputusan);

        try {
            // Validasi input
            $validated = $request->validate(
                [
                    'file' => [
                        'required',
                        'file',
                        'max:10240', // 10MB
                        'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar',
                    ],
                    'kategori' => 'required|in:proposal,rab,surat_pengantar,dokumentasi,lainnya',
                    'deskripsi' => 'nullable|string|max:500',
                ],
                [
                    'file.required' => 'File lampiran wajib dipilih.',
                    'file.max' => 'Ukuran file maksimal 10 MB.',
                    'file.mimes' => 'Format file harus: PDF, Word, Excel, Gambar (JPG/PNG), atau ZIP/RAR.',
                    'kategori.required' => 'Kategori dokumen wajib dipilih.',
                    'kategori.in' => 'Kategori tidak valid.',
                ],
            );

            // Upload file ke storage
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Generate unique filename
            $filename = time().'_'.uniqid().'.'.$extension;

            // Store ke folder: storage/app/public/lampiran_sk/{keputusan_id}/
            $path = $file->storeAs('lampiran_sk/'.$surat_keputusan->id, $filename, 'public');

            // Sanitize deskripsi
            $deskripsi = $validated['deskripsi'] ?? null;
            if ($deskripsi && function_exists('sanitize_input')) {
                $deskripsi = sanitize_input($deskripsi, 500);
            }

            // ✅ FIXED: Sesuaikan nama field dengan model
            $attachment = KeputusanAttachment::create([
                'keputusan_id' => $surat_keputusan->id,
                'kategori' => $validated['kategori'],
                'nama_file' => $originalName, // ✅ FIXED: nama_file_asli → nama_file
                'nama_file_sistem' => $filename,
                'file_path' => $path, // ✅ FIXED: path_file → file_path
                'file_size' => $file->getSize(), // ✅ FIXED: ukuran_file → file_size
                'mime_type' => $file->getMimeType(),
                'extension' => $extension, // ✅ TAMBAHKAN extension
                'deskripsi' => $deskripsi,
                'uploaded_by' => auth()->id(),
                'download_count' => 0, // ✅ Set default
            ]);

            return redirect()
                ->route('surat_keputusan.edit', $surat_keputusan->id)
                ->with('success', 'Lampiran berhasil diunggah: '.$originalName);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('surat_keputusan.edit', $surat_keputusan->id)->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error upload attachment SK', [
                'keputusan_id' => $surat_keputusan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('surat_keputusan.edit', $surat_keputusan->id)
                ->with('error', 'Gagal mengunggah lampiran: '.$e->getMessage());
        }
    }

    /**
     * Download lampiran file
     * FASE 1.2
     */
    public function downloadAttachment(KeputusanHeader $surat_keputusan, KeputusanAttachment $attachment)
    {
        // Authorization check (sudah di-handle middleware, tapi dobel check di sini)
        $this->authorize('view', $surat_keputusan);

        // Validasi: attachment harus milik SK ini
        if ($attachment->keputusan_id !== $surat_keputusan->id) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        // Check file exists
        if (! Storage::disk('public')->exists($attachment->file_path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        try {
            // Increment download count
            $attachment->incrementDownload();

            // Get full path
            $fullPath = Storage::disk('public')->path($attachment->file_path);

            return response()->download($fullPath, $attachment->nama_file);
        } catch (\Exception $e) {
            Log::error('Failed to download attachment', [
                'attachment_id' => $attachment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Gagal mengunduh file.');
        }
    }

    /**
     * Delete lampiran file
     * FASE 1.2
     */
    public function deleteAttachment(KeputusanHeader $surat_keputusan, KeputusanAttachment $attachment)
    {
        // Authorization check (sudah di-handle middleware, tapi dobel check di sini)
        $this->authorize('update', $surat_keputusan);

        // Validasi: attachment harus milik SK ini
        if ($attachment->keputusan_id !== $surat_keputusan->id) {
            abort(404, 'Lampiran tidak ditemukan.');
        }

        // Validasi: hanya draft/ditolak yang bisa hapus lampiran
        if (! in_array($surat_keputusan->status_surat, ['draft', 'ditolak'], true)) {
            return back()->with('error', 'Lampiran hanya bisa dihapus pada SK dengan status draft atau ditolak.');
        }

        try {
            DB::transaction(function () use ($attachment) {
                // Delete physical file menggunakan method model
                $attachment->deleteFile();

                // Delete record (soft delete)
                $attachment->delete();
            });

            Log::info('Attachment deleted', [
                'attachment_id' => $attachment->id,
                'keputusan_id' => $surat_keputusan->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('surat_keputusan.edit', $surat_keputusan->id)->with('success', 'Lampiran berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Failed to delete attachment', [
                'attachment_id' => $attachment->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Gagal menghapus lampiran.');
        }
    }
}
