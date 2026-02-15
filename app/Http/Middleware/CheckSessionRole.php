<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
        // Sudah ada user yang ter-login dari session Laravel
        if (Auth::check()) {
            return $next($request);
        }

        // Cek session dari Dashboard Menu
        $userId = session('user_id');
        $userRole = session('user_role');

        // Jika tidak ada session user_id, redirect ke login
        if (! $userId) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Validasi bahwa userId adalah integer positif
        $userId = filter_var($userId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($userId === false) {
            session()->flush();

            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Cari user di database berdasarkan user_id dari session
        $user = User::find($userId);

        // Validasi: user harus ada dan aktif
        if (! $user || ! $user->isActive()) {
            session()->flush();

            return redirect()->route('login')
                ->with('error', 'User tidak ditemukan atau tidak aktif. Silakan login ulang.');
        }

        // Login user secara programmatic (agar Auth::user() bisa dipakai)
        Auth::login($user);

        // Simpan info tambahan ke session Laravel
        session([
            'entered_from_dashboard' => true,
            'user_role' => $userRole,
            'entry_time' => now(),
        ]);

        return $next($request);
    }
}
