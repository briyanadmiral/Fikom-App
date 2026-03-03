<?php

namespace App\Http\Controllers;

use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MySignatureController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $this->authorizeRole($user->peran_id); // hanya 2/3
        $sig = $user->signature;

        return view('kop_surat.ttd_saya', compact('sig'));
    }

    public function update(Request $r)
    {
        $user = Auth::user();
        $this->authorizeRole($user->peran_id);

        $data = $r->validate([
            'file' => 'required|image|mimes:png|max:512', // png transparan disarankan, maks 512KB
            'default_width_mm' => 'nullable|integer|min:20|max:80',
            'default_height_mm' => 'nullable|integer|min:10|max:30',
        ]);

        $userId = validate_integer_id($user->id);
        if ($userId === null) {
            abort(500, 'Invalid user ID');
        }

        $file = $r->file('file');

        if ($file->getMimeType() !== 'image/png') {
            return back()->withErrors(['file' => 'File harus berformat PNG.']);
        }

        try {
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false || $imageInfo[2] !== IMAGETYPE_PNG) {
                return back()->withErrors(['file' => 'File bukan PNG yang valid.']);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to validate image: '.sanitize_log_message($e->getMessage()));

            return back()->withErrors(['file' => 'Gagal memvalidasi gambar.']);
        }

        $existingSig = $user->signature;
        if ($existingSig && $existingSig->ttd_path) {
            $oldPath = validate_file_path($existingSig->ttd_path);
            if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                try {
                    Storage::disk('local')->delete($oldPath);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete old signature: '.sanitize_log_message($e->getMessage()));
                }
            }
        }

        // Simpan privat dengan user ID yang sudah divalidasi
        $path = "private/ttd/{$userId}.png";

        try {
            Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));
        } catch (\Throwable $e) {
            Log::error('Failed to save signature: '.sanitize_log_message($e->getMessage()));

            return back()->withErrors(['file' => 'Gagal menyimpan tanda tangan.']);
        }

        UserSignature::updateOrCreate(
            ['pengguna_id' => $userId],
            [
                'ttd_path' => $path,
                'default_width_mm' => $data['default_width_mm'] ?? 35,
                'default_height_mm' => $data['default_height_mm'] ?? 15,
            ],
        );

        return back()->with('ok', 'TTD berhasil diperbarui.');
    }

    private function authorizeRole($peranId)
    {
        if (! in_array((int) $peranId, [2, 3], true)) {
            abort(403, 'Hanya Dekan/Wakil Dekan.');
        }
    }
}
