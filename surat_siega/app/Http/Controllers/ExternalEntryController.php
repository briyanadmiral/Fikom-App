<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $userId = $request->query('user_id');
        $token = $request->query('token');

        if (! $userId) {
            abort(403, 'Parameter user_id diperlukan.');
        }

        if (! is_numeric($userId)) {
            abort(403, 'Parameter user_id tidak valid.');
        }

        $sharedSecret = config('services.entry_shared_secret');
        if ($sharedSecret) {
            $expectedToken = hash_hmac('sha256', $userId . date('Y-m-d'), $sharedSecret);

            if (! $token || ! hash_equals($expectedToken, $token)) {
                Log::warning('ExternalEntry: Invalid token attempt', [
                    'user_id' => $userId,
                    'ip' => $request->ip(),
                ]);
                abort(403, 'Token tidak valid atau sudah expired.');
            }
        }

        $user = User::with('peran')->find((int) $userId);

        if (! $user) {
            abort(403, 'User dengan ID tersebut tidak ditemukan.');
        }

        if (! $user->isActive()) {
            abort(403, 'User tidak aktif. Hubungi administrator.');
        }

        session([
            'user_id' => $user->id,
            'user_role' => $user->peran->nama ?? 'unknown',
            'user_role_id' => $user->peran_id,
            'user_name' => $user->nama_lengkap,
            'entered_from_dashboard' => true,
            'entry_time' => now(),
        ]);

        Auth::login($user);

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
        $userName = Auth::user()?->nama_lengkap ?? 'User';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('http://localhost/fikomapp/index.php')->with('success', 'Anda telah berhasil logout.');
    }
}
