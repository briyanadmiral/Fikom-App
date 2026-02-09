<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExternalEntryController extends Controller
{
    /**
     * Entry point dari Dashboard Menu eksternal.
     *
     * URL yang akan dipanggil teman Anda:
     * https://your-project.com/entry?user_id=123
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function entry(Request $request)
    {
        // 1️⃣ Ambil parameter dari Dashboard Menu
        $userId = $request->query('user_id');

        // 2️⃣ Validasi parameter (hanya perlu user_id)
        if (! $userId) {
            abort(403, 'Parameter user_id diperlukan.');
        }

        // 3️⃣ Validasi user_id harus integer
        if (! is_numeric($userId)) {
            abort(403, 'Parameter user_id tidak valid.');
        }

        // 4️⃣ Cari user di database Anda (dengan eager load peran)
        $user = User::with('peran')->find((int) $userId);

        if (! $user) {
            abort(403, 'User dengan ID tersebut tidak ditemukan.');
        }

        // 5️⃣ Validasi user aktif
        if (! $user->isActive()) {
            abort(403, 'User tidak aktif. Hubungi administrator.');
        }

        // 6️⃣ Set session Laravel (gunakan peran dari database)
        session([
            'user_id' => $user->id,
            'user_role' => $user->peran->nama ?? 'unknown', // Ambil dari database
            'user_role_id' => $user->peran_id, // ✅ TAMBAH: Simpan peran_id
            'user_name' => $user->nama_lengkap,
            'entered_from_dashboard' => true,
            'entry_time' => now(),
        ]);

        // 7️⃣ Login user Laravel (agar Auth::user() langsung tersedia)
        Auth::login($user);

        // 8️⃣ Redirect ke home project Anda
        return redirect()->route('home')
            ->with('success', "Selamat datang, {$user->nama_lengkap}!");
    }

    /**
     * Exit point - kembali ke Dashboard Menu eksternal.
     *
     * Dipanggil saat user klik tombol "Kembali ke Dashboard Menu"
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exit(Request $request)
    {
        // 1️⃣ Simpan nama user sebelum logout (untuk pesan)
        $userName = Auth::user()?->nama_lengkap ?? 'User';

        // 2️⃣ Logout Laravel
        Auth::logout();

        // 3️⃣ Hapus semua session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4️⃣ Redirect ke halaman login
        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}
