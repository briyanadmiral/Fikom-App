<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MasterKopSuratController extends Controller
{
    /**
     * Cek admin (owner/peran_id = 1) secara aman
     */
    private function ensureAdmin(): void
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->id === 1 || (int) ($user->peran_id ?? 0) === 1);
        abort_unless($isAdmin, 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $kop = MasterKopSurat::firstOrCreate([]);
        return view('pengaturan.kop_surat', compact('kop'));
    }

    public function update(Request $r)
    {
        $this->ensureAdmin();

        // Validasi ketat + normalisasi
        $data = $r->validate([
            'mode_type' => ['required', 'in:custom,upload'],
            'text_align' => ['nullable', 'in:left,right,center'],

            // Kontrol styling
            'logo_size' => ['nullable', 'integer', 'min:30', 'max:200'],
            'font_size_title' => ['nullable', 'integer', 'min:10', 'max:30'],
            'font_size_text' => ['nullable', 'integer', 'min:8', 'max:20'],
            // #RRGGBB
            'text_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'header_padding' => ['nullable', 'integer', 'min:0', 'max:50'],
            'background_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],

            // Data teks (disanitasi lagi di bawah)
            'nama_fakultas' => ['nullable', 'string', 'max:255'],
            'alamat_lengkap' => ['nullable', 'string', 'max:500'],
            'telepon_lengkap' => ['nullable', 'string', 'max:255'],
            'email_website' => ['nullable', 'string', 'max:255'],

            // Upload files (ukuran dalam KB)
            'logo_kanan' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'background_header' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'background' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'cap' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],

            // boolean switch
            'tampilkan_logo_kanan' => ['sometimes', 'boolean'],
        ]);

        // Ambil/buat baris
        $kop = MasterKopSurat::firstOrCreate([]);

        // Jika mode upload, kosongkan field custom text agar tidak ambigu
        if (($data['mode_type'] ?? 'custom') === 'upload') {
            $data['nama_fakultas'] = null;
            $data['alamat_lengkap'] = null;
            $data['telepon_lengkap'] = null;
            $data['email_website'] = null;
        }

        // Default text_align
        if (!isset($data['text_align'])) {
            $data['text_align'] = 'right';
        }

        // Normalisasi boolean tampilkan_logo_kanan (simpan 0/1 bila kolom ada)
        if (array_key_exists('tampilkan_logo_kanan', $data)) {
            $data['tampilkan_logo_kanan'] = (bool) $data['tampilkan_logo_kanan'];
        }

        // Sanitasi teks sederhana (strip_tags + trim + batasi panjang)
        $sanitize = function (?string $v, int $max = 255): ?string {
            if ($v === null || $v === '') {
                return $v;
            }
            $clean = strip_tags($v);
            $clean = trim($clean);
            $clean = mb_substr($clean, 0, $max);
            return $clean === '' ? null : $clean;
        };
        foreach (['nama_fakultas' => 255, 'alamat_lengkap' => 500, 'telepon_lengkap' => 255, 'email_website' => 255] as $key => $limit) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $sanitize($data[$key], $limit);
            }
        }

        // ------- Upload berkas (hapus lama jika ada) -------
        // Kolom adaptif: dukung background_header_path jika tabel memilikinya
        $hasBgHeaderCol = Schema::hasColumn('master_kop_surat', 'background_header_path');

        $fileTargets = [
            'logo_kanan' => 'logo_kanan_path',
            'background_header' => $hasBgHeaderCol ? 'background_header_path' : 'background_path',
            'background' => 'background_path',
            'cap' => 'cap_path',
        ];

        foreach ($fileTargets as $inputName => $columnName) {
            if ($r->hasFile($inputName)) {
                Log::info("Processing file: {$inputName}");

                // ✅ FIXED: Validate old file path
                if (!empty($kop->$columnName)) {
                    $oldPath = validate_file_path($kop->$columnName);

                    if ($oldPath !== null) {
                        try {
                            if (Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->delete($oldPath);
                                Log::info("Deleted old file: {$oldPath}");
                            }
                        } catch (\Throwable $e) {
                            Log::warning("Gagal hapus file lama {$columnName}: " . sanitize_log_message($e->getMessage()));
                        }
                    }
                }

                // Upload file baru
                $file = $r->file($inputName);
                $path = $file->store('kop', 'public'); // Storage aman, path relatif
                $data[$columnName] = $path;

                Log::info("File uploaded: {$inputName} -> {$path}");
            }
        }

        // Pastikan field file input tidak ikut ter-mass assign
        unset($data['logo_kanan'], $data['background_header'], $data['background'], $data['cap']);

        // Set updated_by jika kolom ada
        if (Schema::hasColumn('master_kop_surat', 'updated_by')) {
            $data['updated_by'] = auth()->id();
        }

        try {
            $kop->update($data);
            Log::info('Kop surat updated', ['user_id' => auth()->id()]);
            return back()->with('success', 'Pengaturan kop surat berhasil diperbarui.');
        } catch (\Throwable $e) {
            // ✅ FIXED: Sanitize error message in log
            Log::error('Kop surat update failed', [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);
            return back()
                ->withErrors(['update' => 'Gagal memperbarui pengaturan.'])
                ->withInput();
        }
    }

    // METHOD BARU: Delete Image
    public function deleteImage($type)
    {
        $this->ensureAdmin();

        $kop = MasterKopSurat::first();
        if (!$kop) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // Map type ke column name (adaptif untuk background_header_path)
        $hasBgHeaderCol = Schema::hasColumn('master_kop_surat', 'background_header_path');
        $columnMap = [
            'logo' => 'logo_kanan_path',
            'background' => 'background_path',
            'bg_header' => $hasBgHeaderCol ? 'background_header_path' : 'background_path',
            'cap' => 'cap_path',
        ];

        if (!isset($columnMap[$type])) {
            return response()->json(['success' => false, 'message' => 'Tipe gambar tidak valid'], 400);
        }

        $columnName = $columnMap[$type];
        $filePath = $kop->$columnName;

        if (!$filePath) {
            return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan'], 404);
        }

        // ✅ FIXED: Validate file path
        $validatedPath = validate_file_path($filePath);

        if ($validatedPath === null) {
            return response()->json(['success' => false, 'message' => 'Path tidak valid'], 400);
        }

        try {
            if (Storage::disk('public')->exists($validatedPath)) {
                Storage::disk('public')->delete($validatedPath);
            }

            $kop->update([$columnName => null]);

            Log::info("Image deleted: {$type}");

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete image: ' . sanitize_log_message($e->getMessage()));
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus gambar.',
                ],
                500,
            );
        }
    }

    public function preview(Request $r)
    {
        // Tidak perlu admin full untuk sekadar preview, tapi bisa dibatasi jika perlu.
        // $this->ensureAdmin();

        $kop = MasterKopSurat::first() ?? new MasterKopSurat();

        // Normalisasi + sanitasi nilai preview (tidak disimpan ke DB)
        $safeInt = fn($v, $min, $max, $def) => is_numeric($v) ? max($min, min((int) $v, $max)) : $def;
        $safeHex = function ($v, $def = '#000000') {
            if (is_string($v) && preg_match('/^#([A-Fa-f0-9]{6})$/', $v)) {
                return $v;
            }
            return $def;
        };
        $sanitize = function (?string $v, int $max = 500): ?string {
            if ($v === null || $v === '') {
                return $v;
            }
            $clean = strip_tags($v);
            $clean = trim($clean);
            $clean = mb_substr($clean, 0, $max);
            return $clean === '' ? null : $clean;
        };

        $kop->text_align = in_array($r->input('text_align'), ['left', 'right', 'center'], true) ? $r->input('text_align') : 'right';
        $kop->logo_size = $safeInt($r->input('logo_size'), 30, 200, (int) ($kop->logo_size ?? 100));
        $kop->font_size_title = $safeInt($r->input('font_size_title'), 10, 30, (int) ($kop->font_size_title ?? 14));
        $kop->font_size_text = $safeInt($r->input('font_size_text'), 8, 20, (int) ($kop->font_size_text ?? 10));
        $kop->text_color = $safeHex($r->input('text_color') ?? ($kop->text_color ?? '#000000'));
        $kop->header_padding = $safeInt($r->input('header_padding'), 0, 50, (int) ($kop->header_padding ?? 15));
        $kop->background_opacity = $safeInt($r->input('background_opacity'), 0, 100, (int) ($kop->background_opacity ?? 100));

        $kop->nama_fakultas = $sanitize($r->input('nama_fakultas') ?? $kop->nama_fakultas, 255);
        $kop->alamat_lengkap = $sanitize($r->input('alamat_lengkap') ?? $kop->alamat_lengkap, 500);
        $kop->telepon_lengkap = $sanitize($r->input('telepon_lengkap') ?? $kop->telepon_lengkap, 255);
        $kop->email_website = $sanitize($r->input('email_website') ?? $kop->email_website, 255);

        // Render partial tanpa menyimpan, context: 'web'
        return view('shared._kop_surat', [
            'kop' => $kop,
            'context' => 'web',
            'showDivider' => true,
        ])->render();
    }
}
