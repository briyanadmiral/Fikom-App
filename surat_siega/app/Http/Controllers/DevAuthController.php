<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Development-only authentication bypass.
 * ONLY works when APP_ENV=local.
 */
class DevAuthController extends Controller
{
    public function __construct()
    {
        // Hard abort if not local environment
        if (config('app.env') !== 'local') {
            abort(404);
        }
    }

    /**
     * Show dev login form with list of test users.
     */
    public function showDevLogin()
    {
        $users = User::with('peran')->get();

        return view('dev.login', compact('users'));
    }

    /**
     * Login as specific user (bypass password).
     */
    public function loginAs(Request $request)
    {
        $userId = $request->input('user_id');

        $user = User::with('peran')->findOrFail($userId);

        Auth::login($user);

        $request->session()->regenerate();

        session([
            'peran_id' => $user->peran_id,
            'peran_nama' => $user->peran->nama ?? 'Unknown',
            'is_admin' => ($user->peran->nama ?? '') === 'admin_tu',
            'is_dosen' => ($user->peran->nama ?? '') === 'Dosen',
            'last_activity' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Dev login sebagai: ' . $user->nama_lengkap);
    }

    /**
     * Quick login dengan peran_id langsung.
     */
    public function quickLogin(Request $request)
    {
        $peranId = $request->input('peran_id', 1);

        // Cari user dengan peran_id tersebut
        $user = User::where('peran_id', $peranId)
            ->where('approval_status', 'approved')
            ->first();

        if (!$user) {
            return back()->with('error', 'Tidak ada user dengan peran_id ' . $peranId);
        }

        Auth::login($user);

        $request->session()->regenerate();

        session([
            'peran_id' => $user->peran_id,
            'peran_nama' => $user->peran->nama ?? 'Unknown',
            'is_admin' => ($user->peran->nama ?? '') === 'admin_tu',
            'is_dosen' => ($user->peran->nama ?? '') === 'Dosen',
            'last_activity' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Dev login sebagai: ' . $user->nama_lengkap . ' (' . ($user->peran->nama ?? 'Unknown') . ')');
    }
}
