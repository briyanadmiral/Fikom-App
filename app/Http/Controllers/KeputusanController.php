<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\KeputusanHeader;
use App\Models\KeputusanVersi;
use App\Models\KeputusanPenerima;
use App\Models\MasterKopSurat;
use App\Models\User;
use App\Models\UserSignature; // pastikan model & relasi ada (dipakai di modul Surat Tugas)
use Illuminate\Validation\Rule;


class KeputusanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /* =========================================================
     * Helpers (role & util)
     * ========================================================= */
    private function isAdminTU(): bool
    {
        return (int)Auth::user()->peran_id === 1;
    }

    private function isSigner(): bool
    {
        return in_array((int)Auth::user()->peran_id, [2, 3]); // 2=Dekan, 3=Wadek
    }

    private function normalizeArray($arr): array
    {
        $arr = is_array($arr) ? $arr : [];
        $arr = array_map(fn($v) => trim((string)$v), $arr);
        return array_values(array_filter($arr, fn($v) => $v !== ''));
    }

    private function sanitizeHtmlBlock(string $html): string
    {
        $allowed = '<p><br><strong><em><ul><ol><li><u>';
        return strip_tags($html, $allowed);
    }

    /**
     * Susun HTML “MEMUTUSKAN” dari field “menetapkan”.
     * Bisa menerima array string atau array ['judul'=>'KESATU', 'isi'=>'...']
     */
    private function composeMemutuskanHtml(array $menetapkan): string
    {
        $defaultJuduls = ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH', 'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH'];
        $out = [];
        $i = 0;

        foreach ($menetapkan as $item) {
            if (is_array($item)) {
                $judul = strtoupper(trim($item['judul'] ?? ($defaultJuduls[$i] ?? 'KETENTUAN')));
                $isi   = $this->sanitizeHtmlBlock((string)($item['isi'] ?? ''));
            } else {
                $judul = $defaultJuduls[$i] ?? 'KETENTUAN';
                $isi   = $this->sanitizeHtmlBlock((string)$item);
            }
            if ($isi === '') {
                $i++;
                continue;
            }
            $out[] = "<p><strong>{$judul}:</strong> {$isi}</p>";
            $i++;
        }

        return implode("\n", $out);
    }

    /* =========================================================
     * Helpers (TTD/Cap)
     * ========================================================= */
    private function shouldShowSignatures(KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'disetujui' && !empty($sk->signed_at);
    }

    private function getSigningAssets(KeputusanHeader $sk): array
    {
        $kop = MasterKopSurat::first();

        // TTD dari signature user penandatangan
        $sigPath = null;
        if ($sk->penandatanganUser) {
            // coba via relasi (kalau model User punya ->signature)
            $sigPath = optional($sk->penandatanganUser->signature ?? null)->ttd_path;
            // fallback query langsung
            if (!$sigPath) {
                $sigPath = UserSignature::where('pengguna_id', $sk->penandatangan)->value('ttd_path');
            }
        }

        $ttdImageB64 = $this->fileToBase64($sigPath);
        $capImageB64 = $this->fileToBase64($kop?->cap_path);

        // preferensi ukuran/opacity tersimpan di header saat approve
        $ttdW      = (int)($sk->ttd_config['w_mm'] ?? 42);
        $capW      = (int)($sk->cap_config['w_mm'] ?? 35);
        $capOpacity = (float)($sk->cap_config['opacity'] ?? 0.95);

        return compact('kop', 'ttdImageB64', 'capImageB64', 'ttdW', 'capW', 'capOpacity');
    }

    private function fileToBase64(?string $path): ?string
    {
        if (!$path) return null;

        $contents = null;
        $ext = null;

        if (Storage::disk('public')->exists($path)) {
            $contents = Storage::disk('public')->get($path);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        } elseif (Storage::exists($path)) {
            $contents = Storage::get($path);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }

        if (!$contents) return null;

        $mime = $ext === 'png' ? 'image/png'
            : (($ext === 'svg' || $ext === 'svgz') ? 'image/svg+xml' : 'image/jpeg');

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function renderKeputusanPdfWithSign(KeputusanHeader $sk, array $opts = []): string
    {
        $versList     = method_exists($sk, 'versi') ? $sk->versi()->orderBy('versi', 'desc')->get() : collect();
        $penerimaList = $sk->penerima->pluck('pengguna.nama_lengkap')->all();

        $assets   = $this->getSigningAssets($sk);
        $force    = (bool)($opts['forceShow'] ?? false);
        $showSign = $force ? true : $this->shouldShowSignatures($sk);

        $ttdW       = isset($opts['ttdW']) ? (int)$opts['ttdW'] : $assets['ttdW'];
        $capW       = isset($opts['capW']) ? (int)$opts['capW'] : $assets['capW'];
        $capOpacity = isset($opts['capOpacity']) ? (float)$opts['capOpacity'] : $assets['capOpacity'];

        $viewData = array_merge([
            'keputusan'    => $sk,
            'penerimaList' => $penerimaList,
            'versList'     => $versList,
            'kop'          => $assets['kop'] ?? null,
            'context'      => 'pdf',
            'showSigns'    => $showSign,
            'ttdImageB64'  => $assets['ttdImageB64'],
            'capImageB64'  => $assets['capImageB64'],
            'ttdW'         => $ttdW,
            'capW'         => $capW,
            'capOpacity'   => $capOpacity,
        ], $opts['extra'] ?? []);

        $pdf = Pdf::loadView('surat_keputusan.surat_pdf', $viewData)
            ->setPaper('A4', 'portrait');

        return $pdf->output(); // bytes
    }

    /* =========================================================
     * 1. Index → mine
     * ========================================================= */
    public function index()
    {
        $user = Auth::user();

        // Hanya Admin TU yang boleh melihat halaman Input (listing semua SK)
        if ((int)$user->peran_id !== 1) {
            return redirect()->route('surat_keputusan.mine');
        }

        $list = KeputusanHeader::with(['pembuat', 'penerima.pengguna', 'penandatanganUser'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        // pastikan view ini ada: resources/views/surat_keputusan/index.blade.php
        return view('surat_keputusan.index', compact('list', 'stats'));
    }


    /* =========================================================
     * 2. Surat Keputusan Saya
     * ========================================================= */
    public function mine()
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        if ($peranId === 1) {
            $list = KeputusanHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser'])
                ->where('dibuat_oleh', $user->id)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $list = KeputusanHeader::with(['penerima.pengguna', 'pembuat', 'penandatanganUser'])
                ->where('status_surat', 'disetujui')
                ->whereHas('penerima', fn($q) => $q->where('pengguna_id', $user->id))
                ->orderByDesc('created_at')
                ->get();
        }

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_keputusan.keputusan_saya', compact('list', 'stats'));
    }

    /* =========================================================
     * 3. Semua SK (Admin TU)
     * ========================================================= */
    public function all()
    {
        if (!$this->isAdminTU()) {
            return redirect()->route('surat_keputusan.mine')
                ->with('error', 'Anda tidak berhak melihat semua surat.');
        }

        $list = KeputusanHeader::with(['pembuat', 'penerima.pengguna', 'penandatanganUser'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'draft'     => $list->where('status_surat', 'draft')->count(),
            'pending'   => $list->where('status_surat', 'pending')->count(),
            'disetujui' => $list->where('status_surat', 'disetujui')->count(),
        ];

        return view('surat_keputusan.index', compact('list', 'stats'));
    }

    /* =========================================================
     * 4. Form create
     * ========================================================= */
    public function create()
    {
        $admins  = User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat = User::whereIn('peran_id', [2, 3])->get();
        $users   = User::where('peran_id', '!=', 1)->get();

        $tahun = date('Y');
        $max   = KeputusanHeader::whereYear('tanggal_asli', $tahun)
            ->max(DB::raw('CAST(SUBSTRING_INDEX(nomor,"/",1) AS UNSIGNED)')) ?? 0;
        $next  = $max + 1;
        $autoNomor = sprintf('%03d/SK/UNIKA/%s', $next, $tahun);

        return view('surat_keputusan.create', compact('admins', 'pejabat', 'users', 'autoNomor', 'tahun'));
    }

    /* =========================================================
     * 5. Store
     * ========================================================= */
    public function store(Request $request)
    {
        $mode = $request->input('mode'); // draft | terkirim
        $validated = $request->validate([
            'nomor'         => 'required|unique:keputusan_header,nomor',
            'tanggal_asli'  => 'required|date',
            'tentang'       => 'required|string|max:255',
            'menimbang'     => 'required|array|min:1',
            'mengingat'     => 'required|array|min:1',
            'menetapkan'    => 'required|array|min:1',
            'tembusan'      => 'nullable|string|max:255',

            // penandatangan wajib hanya saat mode=terkirim
            'penandatangan' => [Rule::requiredIf($mode === 'terkirim'), 'nullable', 'exists:pengguna,id'],

            // Penerima OPSIONAL (hanya divalidasi jika dikirim)
            'penerima'      => ['sometimes', 'array'],
            'penerima.*'    => ['integer', 'exists:pengguna,id'],

            'mode'          => 'nullable|in:draft,terkirim',
        ]);

        $status = $mode === 'terkirim' ? 'pending' : 'draft';

        // Normalisasi konten
        $menimbang       = $this->normalizeArray($validated['menimbang']);
        $mengingat       = $this->normalizeArray($validated['mengingat']);
        $menetapkan      = is_array($validated['menetapkan']) ? $validated['menetapkan'] : [];
        $memutuskanHtml  = $this->composeMemutuskanHtml($menetapkan);

        // Penerima opsional
        $penerimaIds = collect($request->input('penerima', []))
            ->filter()   // buang null/empty
            ->unique()
            ->values()
            ->all();

        DB::beginTransaction();
        try {
            $sk = KeputusanHeader::create([
                'nomor'         => $validated['nomor'],
                'tanggal_asli'  => $validated['tanggal_asli'],
                'tentang'       => $validated['tentang'],
                'menimbang'     => json_encode($menimbang, JSON_UNESCAPED_UNICODE),
                'mengingat'     => json_encode($mengingat, JSON_UNESCAPED_UNICODE),
                'memutuskan'    => $memutuskanHtml, // simpan HTML siap cetak
                'tembusan'      => $validated['tembusan'] ?? null,
                'status_surat'  => $status,
                'dibuat_oleh'   => Auth::id(),
                'penandatangan' => $validated['penandatangan'] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // versi awal
            $sk->versi()->create([
                'versi'       => 1,
                'konten_json' => json_encode([
                    'menimbang'  => $menimbang,
                    'mengingat'  => $mengingat,
                    'menetapkan' => $menetapkan,
                ], JSON_UNESCAPED_UNICODE),
                'is_final'    => 0,
                'dibuat_pada' => now(),
            ]);

            // penerima opsional
            foreach ($penerimaIds as $pid) {
                $sk->penerima()->create([
                    'pengguna_id' => $pid,
                    'dibaca'      => false,
                ]);
            }

            DB::commit();
            return redirect()->route('surat_keputusan.index')
                ->with('success', 'Surat Keputusan berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan.');
        }
    }


    /* =========================================================
     * 6. Show (akses setara download)
     * ========================================================= */
    public function show($id)
    {
        $user      = Auth::user();
        $peranId   = $user->peran_id;
        $keputusan = KeputusanHeader::with([
            'pembuat',
            'penandatanganUser',
            'penerima.pengguna',
            'versi'
        ])->findOrFail($id);

        // === guard akses (tetap seperti punyamu) ===
        if ($peranId === 1 && $keputusan->dibuat_oleh !== $user->id) {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda tidak berhak melihat detail ini.');
        }
        if (
            in_array($peranId, [2, 3]) &&
            !($keputusan->status_surat === 'pending' && (int)$keputusan->penandatangan === (int)$user->id)
        ) {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda hanya dapat melihat surat yang menunggu persetujuan Anda.');
        }
        if ($peranId === 4) {
            $isPenerima = $keputusan->penerima->contains('pengguna_id', $user->id);
            if (!($keputusan->status_surat === 'disetujui' && $isPenerima)) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda hanya dapat melihat surat yang sudah disetujui untuk Anda.');
            }
            KeputusanPenerima::where('keputusan_id', $id)
                ->where('pengguna_id', $user->id)
                ->update(['dibaca' => 1]);
        }

        $penerimaList = $keputusan->penerima->pluck('pengguna.nama_lengkap')->all();
        $versList     = $keputusan->versi()->orderBy('versi', 'desc')->get();

        // ✅ Ambil aset kop/ttd/cap agar view bisa pakai shared header
        $assets = $this->getSigningAssets($keputusan);

        return view('surat_keputusan.show', [
            'keputusan'    => $keputusan,
            'penerimaList' => $penerimaList,
            'versList'     => $versList,
            'kop'          => $assets['kop'] ?? null,        // dipakai _kop_surat
            // (opsional) kalau view butuh langsung:
            // 'ttdImageB64'  => $assets['ttdImageB64'],
            // 'capImageB64'  => $assets['capImageB64'],
            // 'ttdW'         => $assets['ttdW'],
            // 'capW'         => $assets['capW'],
            // 'capOpacity'   => $assets['capOpacity'],
        ]);
    }


    /* =========================================================
     * 7. Edit
     * ========================================================= */
    public function edit($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        $sk      = KeputusanHeader::with(['penerima.pengguna', 'versi'])->findOrFail($id);

        if ($peranId === 1) {
            if (!($sk->dibuat_oleh === $user->id && $sk->status_surat === 'draft')) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3])) {
            if (!($sk->status_surat === 'pending' && (int)$sk->penandatangan === (int)$user->id)) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
            }
        } else {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda tidak berhak mengakses form edit ini.');
        }

        $admins  = User::where('peran_id', 1)->pluck('nama_lengkap', 'id');
        $pejabat = User::whereIn('peran_id', [2, 3])->get();
        $users   = User::where('peran_id', '!=', 1)->get();

        if (is_string($sk->menimbang))  $sk->menimbang  = json_decode($sk->menimbang, true)  ?? [];
        if (is_string($sk->mengingat))  $sk->mengingat  = json_decode($sk->mengingat, true)  ?? [];

        $lastVers = $sk->versi->sortByDesc('versi')->first();
        $sk->menetapkan = [];
        if ($lastVers && is_string($lastVers->konten_json)) {
            $j = json_decode($lastVers->konten_json, true);
            if (isset($j['menetapkan']) && is_array($j['menetapkan'])) $sk->menetapkan = $j['menetapkan'];
        }
        if (!$sk->menetapkan) $sk->menetapkan = [['judul' => 'KESATU', 'isi' => (string)$sk->memutuskan]];

        return view('surat_keputusan.edit', compact('sk', 'admins', 'pejabat', 'users'));
    }

    /* =========================================================
     * 8. Update
     * ========================================================= */
    public function update(Request $request, $id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;
        $sk      = KeputusanHeader::with(['penerima', 'versi'])->findOrFail($id);

        // === Akses sama seperti versi kamu (tanpa perubahan) ===
        if ($peranId === 1) {
            if (!($sk->dibuat_oleh === $user->id && $sk->status_surat === 'draft')) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda tidak berhak mengedit surat ini.');
            }
        } elseif (in_array($peranId, [2, 3])) {
            if (!($sk->status_surat === 'pending' && (int)$sk->penandatangan === (int)$user->id)) {
                return redirect()->route('surat_keputusan.index')
                    ->with('error', 'Anda hanya dapat merevisi surat yang menunggu persetujuan Anda.');
            }
        } else {
            return redirect()->route('surat_keputusan.index')
                ->with('error', 'Anda tidak berhak melakukan update.');
        }

        $mode = $request->input('mode'); // boleh ada dari form

        // Rules dasar
        $rules = [
            'tanggal_asli'   => 'required|date',
            'tanggal_surat'  => 'nullable|date',
            'tentang'        => 'required|string|max:255',
            'menimbang'      => 'required|array|min:1',
            'mengingat'      => 'required|array|min:1',
            'menetapkan'     => 'required|array|min:1',
            'tembusan'       => 'nullable|string|max:255',

            // penandatangan wajib jika mode=terkirim
            'penandatangan'  => [Rule::requiredIf($mode === 'terkirim'), 'nullable', 'exists:pengguna,id'],

            // Penerima OPSIONAL (hanya divalidasi jika dikirim)
            'penerima'       => ['sometimes', 'array'],
            'penerima.*'     => ['integer', 'exists:pengguna,id'],
        ];

        // Nomor: hanya validasi unique jika benar2 berubah
        if ($request->input('nomor') !== $sk->nomor) {
            $rules['nomor'] = 'required|unique:keputusan_header,nomor,' . $sk->id;
        }

        $validated = $request->validate($rules);

        // Normalisasi konten
        $menimbang       = $this->normalizeArray($validated['menimbang']);
        $mengingat       = $this->normalizeArray($validated['mengingat']);
        $menetapkan      = is_array($validated['menetapkan']) ? $validated['menetapkan'] : [];
        $memutuskanHtml  = $this->composeMemutuskanHtml($menetapkan);

        DB::beginTransaction();
        try {
            // Update header
            $sk->update([
                'nomor'         => $validated['nomor'] ?? $sk->nomor,
                'tanggal_asli'  => $validated['tanggal_asli'],
                'tanggal_surat' => $request->input('tanggal_surat'),
                'tentang'       => $validated['tentang'],
                'menimbang'     => json_encode($menimbang, JSON_UNESCAPED_UNICODE),
                'mengingat'     => json_encode($mengingat, JSON_UNESCAPED_UNICODE),
                'memutuskan'    => $memutuskanHtml,
                'tembusan'      => $validated['tembusan'] ?? null,
                'penandatangan' => $validated['penandatangan'] ?? null,
                'updated_at'    => now(),
            ]);

            // Tambahkan versi baru
            $lastVersiNumber = $sk->versi()->max('versi') ?? 0;
            $sk->versi()->create([
                'versi'       => $lastVersiNumber + 1,
                'konten_json' => json_encode([
                    'menimbang'  => $menimbang,
                    'mengingat'  => $mengingat,
                    'menetapkan' => $menetapkan,
                ], JSON_UNESCAPED_UNICODE),
                'is_final'    => 0,
                'versi_induk' => $lastVersiNumber,
                'dibuat_pada' => now(),
            ]);

            // === Kelola Penerima (OPSIONAL) ===
            if ($request->has('penerima')) {
                $penerimaIds = collect($request->input('penerima', []))
                    ->filter()->unique()->values()->all();

                // reset sesuai input terbaru
                $sk->penerima()->delete();
                foreach ($penerimaIds as $pid) {
                    $sk->penerima()->create([
                        'pengguna_id' => $pid,
                        'dibaca'      => false,
                    ]);
                }
            }
            // jika form tidak kirim 'penerima', penerima lama dipertahankan

            DB::commit();
            return redirect()->route('surat_keputusan.show', $sk->id)
                ->with('success', 'Surat Keputusan berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan saat update.');
        }
    }


    /* =========================================================
     * 9. Approve (Dekan/Wadek) + Arsip PDF final
     * ========================================================= */
    // === REPLACE: APPROVE (Dekan/Wadek) — tanpa render PDF ===
public function approve(Request $request, $id)
{
    $sk = KeputusanHeader::with(['penerima.pengguna', 'versi', 'penandatanganUser'])->findOrFail($id);

    // Ikuti Policy (cross-approval & status pending)
    $this->authorize('approve', $sk);

    DB::beginTransaction();
    try {
        // Simpan konfigurasi tampilan tanda tangan/cap bila ada slider di form approve
        $ttdW       = (int)$request->input('ttd_w_mm');
        $capW       = (int)$request->input('cap_w_mm');
        $capOpacity = (float)$request->input('cap_opacity');

        $ttdCfg = is_array($sk->ttd_config) ? $sk->ttd_config : [];
        $capCfg = is_array($sk->cap_config) ? $sk->cap_config : [];
        if ($ttdW > 0)       $ttdCfg['w_mm']   = $ttdW;
        if ($capW > 0)       $capCfg['w_mm']   = $capW;
        if ($capOpacity > 0) $capCfg['opacity'] = $capOpacity;

        $sk->update([
            'status_surat'  => 'disetujui',
            'tanggal_surat' => $sk->tanggal_surat ?: now()->toDateString(),
            'approved_by'   => Auth::id(),
            'approved_at'   => now(),
            'rejected_by'   => null,
            'rejected_at'   => null,
            'ttd_config'    => $ttdCfg,
            'cap_config'    => $capCfg,
            'updated_at'    => now(),
        ]);

        // Tandai versi terakhir sebagai final
        $lastVers = $sk->versi()->orderByDesc('versi')->first();
        if ($lastVers) $lastVers->update(['is_final' => 1]);

        DB::commit();
        return redirect()->route('surat_keputusan.show', $sk->id)
            ->with('success', 'Surat Keputusan DISETUJUI. Lanjutkan proses TANDA TANGAN.');
    } catch (\Throwable $e) {
        DB::rollBack();
        return redirect()->route('surat_keputusan.mine')
            ->with('error', 'Terjadi kesalahan saat approve.');
    }
}


    /* =========================================================
     * 9b. Form & Preview Approve (UI slider)
     * ========================================================= */
    public function approveForm($id)
    {
        $user = Auth::user();
        $sk = KeputusanHeader::with(['penandatanganUser', 'penerima.pengguna'])->findOrFail($id);

        if (!($this->isSigner() && $sk->status_surat === 'pending' && (int)$sk->penandatangan === (int)$user->id)) {
            return redirect()->route('surat_keputusan.mine')->with('error', 'Tidak berhak membuka halaman approve.');
        }

        $assets = $this->getSigningAssets($sk);

        return view('surat_keputusan.approve', [
            'keputusan'  => $sk,
            'kop'        => $assets['kop'],
            'ttdW'       => $assets['ttdW'],
            'capW'       => $assets['capW'],
            'capOpacity' => $assets['capOpacity'],
        ]);
    }

    public function approvePreview(Request $request, $id)
    {
        $user = Auth::user();
        $sk = KeputusanHeader::with(['penandatanganUser'])->findOrFail($id);
        if (!($this->isSigner() && $sk->status_surat === 'pending' && (int)$sk->penandatangan === (int)$user->id)) {
            abort(403);
        }

        $ttdW       = (int)$request->input('ttd_w_mm', 42);
        $capW       = (int)$request->input('cap_w_mm', 35);
        $capOpacity = (float)$request->input('cap_opacity', 0.95);

        $assets = $this->getSigningAssets($sk);

        // ⚠️ gunakan partial yang benar
        return view('surat_keputusan.partials.approve-preview', [
            'keputusan'    => $sk,
            'kop'          => $assets['kop'],
            'context'      => 'web',
            'showSigns'    => true,
            'ttdImageB64'  => $assets['ttdImageB64'],
            'capImageB64'  => $assets['capImageB64'],
            'ttdW'         => $ttdW,
            'capW'         => $capW,
            'capOpacity'   => $capOpacity,
        ]);
    }

    // Kirim untuk persetujuan (draft -> pending)
public function submit($id)
{
    $sk = KeputusanHeader::findOrFail($id);
    $this->authorize('submit', $sk);

    $sk->update(['status_surat' => 'pending']);
    return back()->with('success', 'Dikirim untuk persetujuan.');
}

// Tolak (hanya saat pending)
public function reject(Request $request, $id)
{
    $sk = KeputusanHeader::findOrFail($id);
    $this->authorize('reject', $sk);

    DB::transaction(function () use ($sk) {
        $sk->update([
            'status_surat' => 'ditolak',
            'rejected_by'  => Auth::id(),
            'rejected_at'  => now(),
        ]);
    });

    return back()->with('success', 'Surat Keputusan DITOLAK.');
}

// Tanda tangan (wajib sudah DISSETUJUI)
public function sign(Request $request, $id)
{
    $sk = KeputusanHeader::with(['penandatanganUser'])->findOrFail($id);
    $this->authorize('sign', $sk);

    try {
        // Render PDF final dengan TTD & Cap TAMPIL
        $bytes = $this->renderKeputusanPdfWithSign($sk, [
            'forceShow'  => true,
            // kalau ada slider di UI: gunakan nilai request agar presisi
            'ttdW'       => $request->input('ttd_w_mm') ?? ($sk->ttd_config['w_mm'] ?? null),
            'capW'       => $request->input('cap_w_mm') ?? ($sk->cap_config['w_mm'] ?? null),
            'capOpacity' => $request->input('cap_opacity') ?? ($sk->cap_config['opacity'] ?? null),
        ]);

        $dir = 'private/surat_keputusan/signed/' . date('Y');
        Storage::makeDirectory($dir);

        $safeNomor = str_replace(['/', '\\'], '-', (string)$sk->nomor);
        $filePath  = "{$dir}/SK_{$sk->id}_{$safeNomor}.pdf";

        Storage::put($filePath, $bytes);

        $sk->update([
            'signed_at'       => now(),
            'signed_pdf_path' => $filePath,
        ]);

        return redirect()->route('surat_keputusan.show', $sk->id)
            ->with('success', 'Ditandatangani & PDF final disimpan.');
    } catch (\Throwable $e) {
        return back()->with('error', 'Gagal membuat PDF tertandatangani.');
    }
}

// Terbitkan (wajib sudah disetujui & punya PDF tertandatangani)
public function publish($id)
{
    $sk = KeputusanHeader::findOrFail($id);
    $this->authorize('publish', $sk);

    if (empty($sk->signed_pdf_path) || !Storage::exists($sk->signed_pdf_path)) {
        return back()->with('error', 'Belum ada PDF tertandatangani. Lakukan TANDA TANGAN dulu.');
    }

    $sk->update([
        'status_surat' => 'terbit',
        'published_by' => Auth::id(),
        'published_at' => now(),
    ]);

    return back()->with('success', 'Surat Keputusan DITERBITKAN.');
}

// Tandai sudah dibaca untuk penerima internal
public function markRead($id)
{
    $row = KeputusanPenerima::where('keputusan_id', $id)
        ->where('pengguna_id', Auth::id())
        ->firstOrFail();

    $row->update([
        'dibaca'  => 1,
        'read_at' => now(),
    ]);

    return back()->with('success', 'Ditandai sudah dibaca.');
}

// Simpan konfigurasi posisi/ukuran tanda tangan & cap
public function saveSignConfig(Request $r, $id)
{
    $sk = KeputusanHeader::findOrFail($id);

    // Boleh oleh approver (dekan/wadek) atau penandatangan
    if (! (Auth::user()->can('approve', $sk) || Auth::user()->can('sign', $sk))) {
        abort(403);
    }

    $data = $r->validate([
        'ttd.w_mm'    => 'nullable|numeric|min:1|max:120',
        'ttd.x_mm'    => 'nullable|numeric|min:0|max:300',
        'ttd.y_mm'    => 'nullable|numeric|min:0|max:300',
        'cap.w_mm'    => 'nullable|numeric|min:1|max:120',
        'cap.x_mm'    => 'nullable|numeric|min:0|max:300',
        'cap.y_mm'    => 'nullable|numeric|min:0|max:300',
        'cap.opacity' => 'nullable|numeric|min:0|max:1',
    ]);

    // Merge supaya tidak menghapus kunci lama
    $ttd = is_array($sk->ttd_config) ? $sk->ttd_config : [];
    $cap = is_array($sk->cap_config) ? $sk->cap_config : [];

    if (isset($data['ttd'])) $ttd = array_merge($ttd, $data['ttd']);
    if (isset($data['cap'])) $cap = array_merge($cap, $data['cap']);

    $sk->update([
        'ttd_config' => $ttd,
        'cap_config' => $cap,
    ]);

    return back()->with('success', 'Posisi TTD & Cap disimpan.');
}

    /* =========================================================
     * 10. Highlight/Preview (iFrame) — ikut aturan akses
     * ========================================================= */
    public function highlight($id)
    {
        $user    = Auth::user();
        $peranId = $user->peran_id;

        $keputusan = KeputusanHeader::with(['pembuat', 'penandatanganUser', 'penerima.pengguna', 'versi'])
            ->findOrFail($id);

        // sama dengan show(): admin TU hanya miliknya
        if ($peranId === 1 && $keputusan->dibuat_oleh !== $user->id) {
            return redirect()->route('surat_keputusan.index')->with('error', 'Anda tidak berhak.');
        }
        // dekan/wadek: hanya pending & miliknya
        if (
            in_array($peranId, [2, 3]) &&
            !($keputusan->status_surat === 'pending' && (int)$keputusan->penandatangan === (int)$user->id)
        ) {
            return redirect()->route('surat_keputusan.index')->with('error', 'Anda tidak berhak.');
        }
        // user umum: disetujui & penerima
        if ($peranId === 4) {
            $isPenerima = $keputusan->penerima->contains('pengguna_id', $user->id);
            if (!($keputusan->status_surat === 'disetujui' && $isPenerima)) {
                return redirect()->route('surat_keputusan.index')->with('error', 'Anda tidak berhak.');
            }
        }

        $penerimaList = $keputusan->penerima->pluck('pengguna.nama_lengkap')->all();
        $versList     = $keputusan->versi()->orderBy('versi', 'desc')->get();

        // === ambil aset kop + TTD/Cap ===
        $assets    = $this->getSigningAssets($keputusan);
        $showSigns = $this->shouldShowSignatures($keputusan);

        return response()
            ->view('surat_keputusan.highlight', [
                'keputusan'    => $keputusan,
                'penerimaList' => $penerimaList,
                'versList'     => $versList,
                'kop'          => $assets['kop'],
                'showSigns'    => $showSigns,
                'ttdImageB64'  => $showSigns ? $assets['ttdImageB64'] : null,
                'capImageB64'  => $showSigns ? $assets['capImageB64'] : null,
            ])
            ->header('X-Frame-Options', 'ALLOWALL');
    }

    /* =========================================================
     * 11. Download PDF — ikut aturan akses + utamakan arsip final
     * ========================================================= */
    public function downloadPdf($id)
    {
        $user      = Auth::user();
        $peranId   = $user->peran_id;
        $keputusan = KeputusanHeader::with(['penerima.pengguna', 'penandatanganUser'])->findOrFail($id);

        // Akses setara show()
        if ($peranId === 1 && $keputusan->dibuat_oleh !== $user->id) {
            return redirect()->route('surat_keputusan.index')->with('error', 'Anda tidak berhak.');
        }
        if (in_array($peranId, [2, 3])) {
            // penandatangan boleh download saat pending (untuk review) atau sesudah disetujui
            $isMine = (int)$keputusan->penandatangan === (int)$user->id;
            if (!($isMine && in_array($keputusan->status_surat, ['pending', 'disetujui']))) {
                return redirect()->route('surat_keputusan.index')->with('error', 'Anda tidak berhak.');
            }
        }
        if ($peranId === 4) {
            $isPenerima = $keputusan->penerima->contains('pengguna_id', $user->id);
            if (!($keputusan->status_surat === 'disetujui' && $isPenerima)) {
                return redirect()->route('surat_keputusan.index')->with('error', 'Anda tidak berhak.');
            }
        }

        // Jika ada arsip final
        if (!empty($keputusan->signed_pdf_path) && Storage::exists($keputusan->signed_pdf_path)) {
            $safeNomor = str_replace(['/', '\\'], '-', $keputusan->nomor);
            $filename  = "SuratKeputusan_{$safeNomor}.pdf";
            $absolute  = Storage::path($keputusan->signed_pdf_path);

            return response()->file($absolute, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'X-Frame-Options'     => 'ALLOWALL',
            ]);
        }

        // Render dinamis (TTD tampil hanya jika sudah disetujui)
        $versList     = method_exists($keputusan, 'versi') ? $keputusan->versi()->orderBy('versi', 'desc')->get() : collect();
        $penerimaList = $keputusan->penerima->pluck('pengguna.nama_lengkap')->all();
        $assets       = $this->getSigningAssets($keputusan);

        $viewData = [
            'keputusan'    => $keputusan,
            'penerimaList' => $penerimaList,
            'versList'     => $versList,
            'kop'          => $assets['kop'],
            'context'      => 'pdf',
            'showSigns'    => $this->shouldShowSignatures($keputusan),
            'ttdImageB64'  => $assets['ttdImageB64'],
            'capImageB64'  => $assets['capImageB64'],
            'ttdW'         => $assets['ttdW'],
            'capW'         => $assets['capW'],
            'capOpacity'   => $assets['capOpacity'],
        ];

        $pdf = Pdf::loadView('surat_keputusan.surat_pdf', $viewData)
            ->setPaper('A4', 'portrait');

        $safeNomor = str_replace(['/', '\\'], '-', $keputusan->nomor);
        $filename  = "SuratKeputusan_{$safeNomor}.pdf";

        return $pdf->stream($filename, [
            'Attachment'      => false,
            'X-Frame-Options' => 'ALLOWALL',
        ]);
    }
}
