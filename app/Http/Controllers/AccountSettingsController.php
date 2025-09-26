<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;

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
        $user->email        = $data['email'];
        $user->npp          = $data['npp'] ?? null;
        $user->jabatan      = $data['jabatan'] ?? null;
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
}
