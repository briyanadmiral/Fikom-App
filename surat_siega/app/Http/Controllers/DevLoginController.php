<?php

namespace App\Http\Controllers;

use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Dev-only auth bypass for AI screenshot agents and local testing.
 * Active ONLY when APP_ENV=local|testing; returns 404 otherwise.
 *
 * URL contract (call from Playwright/Puppeteer/curl):
 *   GET /dev-login                → role picker page
 *   GET /dev-login/role/{role}    → login as first active user with that role
 *                                   roles: admin_tu, dekan, wakil_dekan,
 *                                          kaprodi, dosen, tendik
 *   GET /dev-login/user/{id}      → login as specific user by pengguna.id
 *   GET /dev-login/logout         → logout and return to picker
 */
class DevLoginController extends Controller
{
    private function ensureLocalEnvironment(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            abort(404);
        }

        if (app()->environment('testing')) {
            return;
        }

        $ip = request()->ip();
        if (! in_array($ip, ['127.0.0.1', '::1'], true)) {
            abort(403, 'Dev-login hanya boleh diakses dari localhost.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureLocalEnvironment();

        $roles = Peran::orderBy('id')->get();
        $usersByRole = User::active()
            ->with('peran')
            ->orderBy('peran_id')
            ->orderBy('nama_lengkap')
            ->get()
            ->groupBy('peran_id');

        return response()->view('dev-login.picker', [
            'roles' => $roles,
            'usersByRole' => $usersByRole,
            'currentUser' => Auth::user(),
        ]);
    }

    public function loginByRole(string $role, Request $request)
    {
        $this->ensureLocalEnvironment();

        $peran = Peran::where('nama', $role)->first();
        if (! $peran) {
            abort(404, "Role '{$role}' tidak ditemukan. Tersedia: admin_tu, dekan, wakil_dekan, kaprodi, dosen, tendik.");
        }

        $user = User::with('peran')
            ->where('peran_id', $peran->id)
            ->where('status', 'aktif')
            ->orderBy('id')
            ->first();

        if (! $user) {
            abort(404, "Tidak ada user aktif untuk role '{$role}'.");
        }

        return $this->performLogin($user, $request, "dev-login by role: {$role}");
    }

    public function loginByUser(int $id, Request $request)
    {
        $this->ensureLocalEnvironment();

        $user = User::with('peran')->find($id);
        if (! $user) {
            abort(404, "User dengan id {$id} tidak ditemukan.");
        }
        if (! $user->isActive()) {
            abort(403, "User dengan id {$id} berstatus tidak_aktif.");
        }

        return $this->performLogin($user, $request, "dev-login by user id: {$id}");
    }

    public function logout(Request $request)
    {
        $this->ensureLocalEnvironment();

        $email = Auth::user()?->email;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hapus session PHP native untuk kompatibilitas Fikom-App root
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();

        Log::info('DevLogin logout', ['email' => $email, 'ip' => $request->ip()]);

        return redirect()->route('dev.login.index')
            ->with('success', 'Logout berhasil. Pilih role untuk login lagi.');
    }

    public function loginManual(Request $request)
    {
        $this->ensureLocalEnvironment();

        $request->validate([
            'npp_nim' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = trim($request->input('npp_nim'));
        $password = trim($request->input('password'));

        // Format otomatis jika input berupa angka saja agar cocok dengan penyimpanan database (formatNpp)
        $formattedNpp = null;
        $digits = preg_replace('/\D+/', '', $identifier);
        if ($digits !== '') {
            if (strlen($digits) === 11) {
                $formattedNpp = substr($digits, 0, 3).'.'.substr($digits, 3, 1).'.'.substr($digits, 4, 4).'.'.substr($digits, 8, 3);
            } else {
                $formattedNpp = implode('.', str_split($digits, 3));
            }
        }

        $user = User::with('peran')
            ->where(function ($q) use ($identifier, $formattedNpp) {
                $q->where('npp', $identifier)
                  ->orWhere('nim', $identifier)
                  ->orWhere('email', $identifier);
                if ($formattedNpp) {
                    $q->orWhere('npp', $formattedNpp);
                }
            })
            ->first();

        if ($user && Hash::check($password, $user->sandi_hash)) {
            if (!$user->isActive()) {
                return redirect()->back()->with('error', 'Akun Anda tidak aktif.');
            }
            return $this->performLogin($user, $request, "dev-login manual credentials");
        }

        return redirect()->back()->with('error', 'NPP/NIM atau password salah. Silakan coba lagi.');
    }

    /**
     * Sets session keys consumed by CheckSessionRole middleware
     * (user_id, user_role, user_role_id, user_name, entered_from_dashboard,
     * entry_time) and logs the user in. Mirrors ExternalEntryController::entry.
     */
    private function performLogin(User $user, Request $request, string $reason)
    {
        $user->loadMissing('peran');

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        session([
            'user_id' => $user->id,
            'user_role' => $user->peran->nama ?? 'unknown',
            'user_role_id' => $user->peran_id,
            'user_name' => $user->nama_lengkap,
            'entered_from_dashboard' => true,
            'entry_time' => now(),
            'is_dev_login' => true,
        ]);

        Auth::login($user);

        // Set session PHP native untuk kompatibilitas Fikom-App root
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['logged_in'] = true;
        
        $superadmin_emails = ['briyanadmiral@gmail.com', 'magang.si@unika.ac.id'];
        if (in_array($user->email, $superadmin_emails)) {
            $_SESSION['role'] = 'superadmin';
        } else {
            $_SESSION['role'] = ((int)$user->peran_id === 7) ? 'mahasiswa' : 'dosen';
        }
        
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->nama_lengkap;
        $_SESSION['user_picture'] = 'assets/img/default-avatar.png';

        Log::info('DevLogin success', [
            'reason' => $reason,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->peran->nama ?? null,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('home')
            ->with('success', "Dev-login: masuk sebagai {$user->nama_lengkap} ({$user->peran->nama}).");
    }
}
