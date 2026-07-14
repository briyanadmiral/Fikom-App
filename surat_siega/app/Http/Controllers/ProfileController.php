<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the profile completion form.
     */
    public function showCompleteForm()
    {
        $user = Auth::user();

        // If approved, they should not see this form
        if ($user->approval_status === 'approved') {
            return redirect()->route('home');
        }

        // If they already completed profile, redirect to pending page
        if ($user->peran_id === 7) {
            if (!empty($user->nim) && !empty($user->whatsapp)) {
                return redirect()->route('profile.pending');
            }
        } elseif (in_array((int)$user->peran_id, [5, 6], true)) {
            if (!empty($user->npp) && !empty($user->email)) {
                return redirect()->route('profile.pending');
            }
        }

        // Try to auto-format NIM and guess default type
        $suggestedNim = $this->autoFormatNim($user->email);
        $suggestedType = (strpos($user->email, 'unika.ac.id') !== false && strpos($user->email, 'student') === false) ? 'mahasiswa' : 'dosen_tendik';

        return view('profile.complete', compact('user', 'suggestedNim', 'suggestedType'));
    }

    /**
     * Store the completed profile data.
     */
    public function storeCompleteForm(Request $request)
    {
        $user = Auth::user();

        // If approved, do not allow edits
        if ($user->approval_status === 'approved') {
            return redirect()->route('home');
        }

        $request->validate([
            'user_type' => 'required|in:mahasiswa,dosen_tendik',
        ]);

        $userType = $request->input('user_type');

        if ($userType === 'mahasiswa') {
            $validated = $request->validate([
                'nim' => 'required|string|max:50',
                'whatsapp' => 'required|string|max:20',
            ]);

            $user->update([
                'nim' => $validated['nim'],
                'whatsapp' => $validated['whatsapp'],
                'peran_id' => 7, // Mahasiswa
                'approval_status' => 'pending',
            ]);

            $identifier = $validated['nim'];
            $label = "Mahasiswa";
        } else {
            $validated = $request->validate([
                'role_type' => 'required|in:dosen,tendik',
                'npp' => 'required|string|max:50',
                'email' => 'required|email|max:100',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $peranId = ($validated['role_type'] === 'dosen') ? 5 : 6;

            $user->update([
                'npp' => $validated['npp'],
                'email' => $validated['email'],
                'sandi_hash' => Hash::make($validated['password']),
                'peran_id' => $peranId,
                'approval_status' => 'pending',
            ]);

            $identifier = $validated['npp'];
            $label = ($validated['role_type'] === 'dosen') ? "Dosen" : "Tendik";
        }

        // Kirim notifikasi ke semua Admin TU (peran_id = 1) yang aktif
        $admins = User::where('peran_id', 1)->where('status', 'aktif')->get();
        foreach ($admins as $admin) {
            Notifikasi::createNotification([
                'pengguna_id' => $admin->id,
                'tipe' => 'user_registration',
                'referensi_id' => $user->id,
                'pesan' => "{$label} baru {$user->nama_lengkap} ({$identifier}) meminta verifikasi akses.",
            ]);
        }

        return redirect()->route('profile.pending')
            ->with('success', 'Profil berhasil dilengkapi. Silakan tunggu verifikasi.');
    }

    /**
     * Show the pending verification page.
     */
    public function showPendingPage()
    {
        $user = Auth::user();

        if ($user->approval_status === 'approved') {
            return redirect()->route('home');
        }

        if ($user->approval_status === 'rejected') {
            return redirect()->route('profile.rejected');
        }

        // Force profile completion if details are missing
        if ($user->peran_id === 7) {
            if (empty($user->nim) || empty($user->whatsapp)) {
                return redirect()->route('profile.complete');
            }
        } elseif (in_array((int)$user->peran_id, [5, 6], true)) {
            if (empty($user->npp) || empty($user->email)) {
                return redirect()->route('profile.complete');
            }
        } else {
            // Default check (for unassigned users)
            if (empty($user->nim) && empty($user->npp)) {
                return redirect()->route('profile.complete');
            }
        }

        return view('profile.pending', compact('user'));
    }

    /**
     * Show the rejected page.
     */
    public function showRejectedPage()
    {
        $user = Auth::user();

        if ($user->approval_status === 'approved') {
            return redirect()->route('home');
        }

        if ($user->approval_status === 'pending') {
            return redirect()->route('profile.pending');
        }

        return view('profile.rejected', compact('user'));
    }

    /**
     * Helper to auto format NIM from email prefix.
     * Example: 23g40004 -> 23.G4.0004
     */
    private function autoFormatNim(string $email)
    {
        $prefix = explode('@', $email)[0];
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', $prefix);
        if (preg_match('/^(\d{2})([a-zA-Z]\d)(\d{4})$/', $clean, $matches)) {
            return $matches[1] . '.' . strtoupper($matches[2]) . '.' . $matches[3];
        }
        return strtoupper($prefix);
    }
}
