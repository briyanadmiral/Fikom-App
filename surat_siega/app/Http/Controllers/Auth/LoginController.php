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
     * Security enhanced dengan sanitization
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $loginInput = trim($credentials['email']);

        if (empty($loginInput)) {
            return back()
                ->withErrors(['email' => 'Email atau NPP wajib diisi.'])
                ->withInput($request->only('email'));
        }

        $key = 'login_attempts_'.$request->ip();
        $maxAttempts = 5;
        $decayMinutes = 15;

        if (cache()->has($key) && cache()->get($key) >= $maxAttempts) {
            return back()
                ->withErrors(['email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'])
                ->withInput($request->only('email'));
        }

        // Format NPP jika berupa 11 digit angka
        $nppFormatted = null;
        $digits = preg_replace('/\D+/', '', $loginInput);
        if (strlen($digits) === 11) {
            $nppFormatted = substr($digits, 0, 3).'.'.substr($digits, 3, 1).'.'.substr($digits, 4, 4).'.'.substr($digits, 8, 3);
        }

        $user = User::with('peran')
            ->where(function ($query) use ($loginInput, $nppFormatted) {
                $query->where('email', strtolower($loginInput))
                      ->orWhere('npp', $loginInput);
                if ($nppFormatted) {
                    $query->orWhere('npp', $nppFormatted);
                }
            })
            ->first();

        if (! $user) {
            $attempts = cache()->get($key, 0) + 1;
            cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

            return back()
                ->withErrors(['email' => 'Email/NPP atau password salah.'])
                ->withInput($request->only('email'));
        }

        if (! $user->getAuthPassword()) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'));
        }

        if (! Hash::check($credentials['password'], $user->getAuthPassword())) {
            $attempts = cache()->get($key, 0) + 1;
            cache()->put($key, $attempts, now()->addMinutes($decayMinutes));

            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->withInput($request->only('email'));
        }

        if (! $user->isActive()) {
            return back()
                ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi administrator.'])
                ->withInput($request->only('email'));
        }

        cache()->forget($key);

        Auth::login($user, $request->filled('remember'));

        $request->session()->regenerate();

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
