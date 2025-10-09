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
use Illuminate\Support\Facades\Schema;   // <- NEW: untuk cek kolom ada/tidak
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class SuratKeputusanController extends Controller
{
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

    /** Angka → Romawi (1..12) */
    private function toRoman($number)
    {
        $map = ['M'=>1000,'CM'=>900,'D'=>500,'CD'=>400,'C'=>100,'XC'=>90,'L'=>50,'XL'=>40,'X'=>10,'IX'=>9,'V'=>5,'IV'=>4,'I'=>1];
        $ret = '';
        $number = max(0, min(3999, (int) $number)); // guard
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) { $number -= $int; $ret .= $roman; break; }
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

    /** Dependency untuk form (hanya yang relevan) */
    private function getFormDependencies(): array
    {
        $admins  = \App\Models\User::where('peran_id', 1)->pluck('nama_lengkap', 'id');

        $pejabat = \App\Models\User::whereIn('peran_id', [2, 3])
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

        // Jika kolom 'status' tidak ada di tabel users/pengguna, ganti/filter sesuai skema kamu
        $users   = \App\Models\User::when(Schema::hasColumn((new \App\Models\User)->getTable(), 'status'),
                        fn($q) => $q->where('status', 'aktif'))
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

        return DB::transaction(function () use ($request) {
            $data    = $request->validated();
            $modeRaw = $request->input('mode');
            $status  = $this->mapModeToStatus($modeRaw);

            $data['status_surat'] = $status;
            unset($data['mode']);

            // Purify diktum & ringkasan HTML
            if (!empty($data['menetapkan']) && is_array($data['menetapkan'])) {
                foreach ($data['menetapkan'] as &$d) {
                    $raw    = $d['isi'] ?? ($d['konten'] ?? '');
                    $d['isi'] = Purifier::clean($raw);
                    unset($d['konten']);
                }
                $data['memutuskan'] = $this->buildMemutuskanHtml($data['menetapkan']);
            }

            // Normalisasi tembusan
            $data['tembusan'] = $this->normalizeTembusan($request->input('tembusan'));
            unset($data['tembusan_formatted']);

            // Penerima
            $internalIds = collect((array) $request->input('penerima_internal', []))
                ->map(fn($v) => (int) $v)->filter()->unique()->values();

            $eksternal   = collect((array) $request->input('penerima_eksternal', []))
                ->map(function ($it) {
                    if (is_array($it)) {
                        $val = trim($it['value'] ?? $it['text'] ?? $it['name'] ?? '');
                    } else {
                        $val = trim((string) $it);
                    }
                    return $val ?: null;
                })
                ->filter()->unique()->values();

            // Guard pengajuan
            if ($status === 'pending' && empty($data['penandatangan'])) {
                return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])->withInput();
            }
            if ($status === 'pending' && $internalIds->isEmpty() && $eksternal->isEmpty()) {
                return back()->withErrors(['penerima_internal' => 'Minimal satu penerima (internal/eksternal) saat pengajuan.'])->withInput();
            }

            $data['dibuat_oleh'] = Auth::id();

            // Simpan penerima_eksternal hanya jika kolomnya ada, jika tidak — abaikan
            if (Schema::hasColumn('keputusan_header', 'penerima_eksternal')) {
                $data['penerima_eksternal'] = $eksternal->all(); // pastikan cast json di model
            } else {
                unset($data['penerima_eksternal']);
            }

            $sk = KeputusanHeader::create($data);

            // Sinkron penerima internal
            if (method_exists($sk, 'penerima') && $internalIds->isNotEmpty()) {
                $sk->penerima()->sync($internalIds->all());
            }

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
        $surat_keputusan->load(['penerima:id,nama_lengkap']);
        return view('surat_keputusan.edit', array_merge($deps, ['sk' => $surat_keputusan]));
    }

    public function update(UpdateKeputusanRequest $request, KeputusanHeader $surat_keputusan)
    {
        $this->authorize('update', $surat_keputusan);
        $wasPending = $surat_keputusan->status_surat === 'pending';

        return DB::transaction(function () use ($request, $surat_keputusan, $wasPending) {
            $modeRaw = $request->input('mode');    // bisa null
            $status  = $this->mapModeToStatus($modeRaw);

            $data = $request->validated();

            if ($status === 'pending' && empty($data['penandatangan']) && empty($surat_keputusan->penandatangan)) {
                return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi saat pengajuan.'])->withInput();
            }

            // Diktum & ringkasan
            if (!empty($data['menetapkan']) && is_array($data['menetapkan'])) {
                foreach ($data['menetapkan'] as &$d) {
                    $raw    = $d['isi'] ?? ($d['konten'] ?? '');
                    $d['isi'] = Purifier::clean($raw);
                    unset($d['konten']);
                }
                $data['memutuskan'] = $this->buildMemutuskanHtml($data['menetapkan']);
            }

            // Tembusan
            $data['tembusan'] = $this->normalizeTembusan($request->input('tembusan'));
            unset($data['tembusan_formatted']);

            // Penerima
            $internalIds = collect((array) $request->input('penerima_internal', []))
                ->map(fn($v) => (int) $v)->filter()->unique()->values();
            $eksternal = collect((array) $request->input('penerima_eksternal', []))
                ->map(function ($it) {
                    if (is_array($it)) {
                        $val = trim($it['value'] ?? $it['text'] ?? $it['name'] ?? '');
                    } else {
                        $val = trim((string) $it);
                    }
                    return $val ?: null;
                })
                ->filter()->unique()->values();

            if (Schema::hasColumn('keputusan_header', 'penerima_eksternal')) {
                $data['penerima_eksternal'] = $eksternal->all();
            } else {
                unset($data['penerima_eksternal']);
            }

            if (in_array($modeRaw, ['draft', 'pending', 'terkirim'], true)) {
                $data['status_surat'] = $status;
            }
            unset($data['mode']);

            // Update header
            $surat_keputusan->update($data);

            // Sinkron penerima internal
            if (method_exists($surat_keputusan, 'penerima')) {
                $surat_keputusan->penerima()->sync($internalIds->all());
            }

            // Notif revisi jika masih pending
            $freshStatus  = $surat_keputusan->fresh()->status_surat;
            $stillPending = ($modeRaw === null && $freshStatus === 'pending') || ($modeRaw !== null && $status === 'pending');

            if ($wasPending && $stillPending && $surat_keputusan->penandatangan) {
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

            $user = Auth::user();
            $message = 'Perubahan berhasil disimpan.';
            if (in_array((int) $user->peran_id, [2, 3], true)) {
                return redirect()->route('surat_keputusan.approveList')->with('success', $message);
            }
            return redirect()->route('surat_keputusan.index')->with('success', $message);
        });
    }

    /* ==================== Workflow ==================== */

    public function submit(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('submit', $surat_keputusan);

        if (!$surat_keputusan->penandatangan) {
            return back()->withErrors(['penandatangan' => 'Penandatangan wajib diisi sebelum pengajuan.']);
        }

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
            'ttd_w_mm'         => 'required|integer|min:30|max:60',
            'cap_w_mm'         => 'required|integer|min:25|max:45',
            'cap_opacity'      => 'required|numeric|min:0.7|max:1.0',
            'kode_klasifikasi' => 'nullable|string|max:20',
            'unit'             => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $surat_keputusan->ttd_w_mm    = $validated['ttd_w_mm'];
            $surat_keputusan->cap_w_mm    = $validated['cap_w_mm'];
            $surat_keputusan->cap_opacity = $validated['cap_opacity'];

            if (empty($surat_keputusan->tanggal_surat)) {
                $surat_keputusan->tanggal_surat = now()->toDateString();
            }

            $date   = \Carbon\Carbon::parse($surat_keputusan->tanggal_surat);
            $tahun  = (int) $date->format('Y');
            $bulanR = $this->toRoman((int) $date->format('n'));

            if (empty($surat_keputusan->nomor)) {
                $unit = $validated['unit'] ?? 'FIKOM';
                $klas = $validated['kode_klasifikasi'] ?? 'B.10.1';

                $res = app(NomorSuratService::class)->reserve($unit, $klas, $bulanR, $tahun);
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

            return redirect()->route('surat_keputusan.approveList')
                ->with('success', 'SK ' . $surat_keputusan->nomor . ' berhasil disetujui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Gagal approve SK #'.$surat_keputusan->id, ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyetujui SK.');
        }
    }

    public function show(KeputusanHeader $surat_keputusan)
    {
        $this->authorize('view', $surat_keputusan);
        return view('surat_keputusan.show', [
            'sk' => $surat_keputusan->load(['pembuat','penandatanganUser']),
        ]);
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

        if (method_exists(app(SkNotifikasiService::class), 'notifyPublished')) {
            app(SkNotifikasiService::class)->notifyPublished($surat_keputusan);
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
    private function mapModeToStatus(?string $mode): string
    {
        return match ($mode) {
            'terkirim' => 'pending',
            'draft'    => 'draft',
            'pending', 'disetujui', 'ditolak', 'terbit', 'arsip' => $mode,
            default    => 'draft',
        };
    }
}
