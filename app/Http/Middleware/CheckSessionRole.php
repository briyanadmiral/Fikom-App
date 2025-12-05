<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckSessionRole
{
    /**
     * Handle an incoming request.
     * 
     * Middleware ini menggantikan 'auth' default Laravel.
     * Cek session yang di-set oleh Dashboard Menu eksternal.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1️⃣ Cek apakah sudah ada user yang ter-login dari session Laravel
        if (Auth::check()) {
            // User sudah login, lanjutkan request
            return $next($request);
        }

        // 2️⃣ Cek session dari Dashboard Menu (PHP native session atau Laravel session)
        $userId = session('user_id');
        $userRole = session('user_role');

        // Jika tidak ada session user_id, tolak akses
        if (!$userId) {
            // Redirect atau abort dengan pesan error
            abort(403, 'Akses ditolak. Silakan login melalui Dashboard Menu.');
        }

        // 3️⃣ Cari user di database berdasarkan user_id dari session
        $user = User::find($userId);

        // Validasi: user harus ada dan aktif
        if (!$user || !$user->isActive()) {
            // Hapus session yang invalid
            session()->flush();
            abort(403, 'User tidak ditemukan atau tidak aktif. Silakan login ulang.');
        }

        // 4️⃣ Login user secara programmatic (agar Auth::user() bisa dipakai)
        Auth::login($user);

        // 5️⃣ Simpan info tambahan ke session Laravel (opsional)
        session([
            'entered_from_dashboard' => true,
            'user_role' => $userRole,
            'entry_time' => now(),
        ]);

        // 6️⃣ Lanjutkan request
        return $next($request);
    }
}