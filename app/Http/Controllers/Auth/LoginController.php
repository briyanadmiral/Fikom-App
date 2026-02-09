<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     * ✅ REFACTORED: Security enhanced dengan sanitization
     */
    public function login(Request $request)
    {
        // ✅ Validasi input dengan sanitasi email
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // ✅ Sanitasi email
        $email = sanitize_email($credentials['email']);

        if (! $email) {
            return back()
                ->withErrors(['email' => 'Format email tidak valid.'])
                ->withInput($request->only('email'));
        }

        // ✅ Rate limiting untuk mencegah brute force
        $key = 'login_attempts_'.$request->ip();
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (cache()->has($key) && cache()->get($key) >= $maxAttempts) {
            return back()
                ->withErrors(['email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'])
                ->withInput($request->only('email'));
        }

        // Cari user berdasarkan email
        $user = User::where('email', $email)->first();

        if (! $user) {
            // ✅ Increment failed attempts
            $attempts = cache()->get($key, 0) + 1;
            cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'));
        }

        // ✅ Validasi password (pakai kolom yang benar / getAuthPassword)
        //    Model User kamu pakai kolom 'sandi_hash' + override getAuthPasswordName()
        //    Jadi aman gunakan getAuthPassword() atau akses langsung $user->sandi_hash.
        if (! Hash::check($credentials['password'], $user->getAuthPassword())) { // ✅ FIX: bukan $user->password
            $attempts = cache()->get($key, 0) + 1;
            cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'));
        }

        // ✅ Validasi status user (konsisten dengan model ->isActive())
        if (! $user->isActive()) { // ✅ FIX: gunakan helper dari model (status === 'active')
            return back()
                ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.'])
                ->withInput($request->only('email'));
        }

        // ✅ Clear failed attempts on success
        cache()->forget($key);

        // Login user secara manual
        Auth::login($user, $request->filled('remember'));

        // Regenerate session untuk mencegah fixation
        $request->session()->regenerate();

        // ✅ Gunakan data yang aman untuk session
        session([
            'peran_id' => validate_integer_id($user->peran_id),
            'peran_nama' => sanitize_output($user->peran->nama ?? 'Unknown'),
            'is_admin' => ($user->peran->nama ?? '') === 'admin_tu',
            'is_dosen' => ($user->peran->nama ?? '') === 'Dosen',
            'last_activity' => now(),
            'user_name' => sanitize_output($user->nama_lengkap),
        ]);

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
