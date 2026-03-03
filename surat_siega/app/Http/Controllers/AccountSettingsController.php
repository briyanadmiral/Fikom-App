<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AccountSettingsController extends Controller
{
    /**
     * Tampilkan halaman pengaturan akun (profil & password).
     */
    public function edit()
    {
        $user = auth()->user();

        return view('account.settings', compact('user'));
    }

    /**
     * Update profil (nama_lengkap, email, npp, jabatan).
     * Validasi ada di UpdateProfileRequest.
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        // Data tervalidasi sudah diformat (email lower, npp dibersihkan) di FormRequest
        $data = $request->safe()->only(['nama_lengkap', 'email', 'npp', 'jabatan']);

        $user->nama_lengkap = $data['nama_lengkap'];
        $user->email = $data['email'];
        $user->npp = $data['npp'] ?? null;
        $user->jabatan = $data['jabatan'] ?? null;
        $user->save();

        return back()->with('success_profile', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password (butuh current_password).
     * Validasi ada di UpdatePasswordRequest.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();

        $user->password = Hash::make($request->validated()['new_password']);
        $user->save();

        return back()->with('success_password', 'Password berhasil diubah.');
    }

    /**
     * Upload/update foto profile.
     */
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048', // Max 2MB
        ], [
            'foto.required' => 'Silakan pilih file foto.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format yang diizinkan: JPEG, PNG, WebP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        try {
            $user = auth()->user();

            // Hapus foto lama jika ada
            if ($user->foto_path && Storage::disk('public')->exists($user->foto_path)) {
                Storage::disk('public')->delete($user->foto_path);
            }

            // Simpan foto baru dengan nama unik
            $file = $request->file('foto');
            $filename = 'foto_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('profile_photos', $filename, 'public');

            $user->foto_path = $path;
            $user->save();

            return back()->with('success_foto', 'Foto profile berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Gagal upload foto profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat mengupload foto.');
        }
    }

    /**
     * Hapus foto profile (kembali ke default avatar).
     */
    public function deleteFoto()
    {
        try {
            $user = auth()->user();

            if ($user->foto_path && Storage::disk('public')->exists($user->foto_path)) {
                Storage::disk('public')->delete($user->foto_path);
            }

            $user->foto_path = null;
            $user->save();

            return back()->with('success_foto', 'Foto profile berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal hapus foto profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus foto.');
        }
    }
}
