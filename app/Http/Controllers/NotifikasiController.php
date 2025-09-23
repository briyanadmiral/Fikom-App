<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;   // Model notifikasi Anda
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Tampilkan daftar notifikasi untuk user yang sedang login.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil notifikasi milik user yang belum dibaca
        $notifs = Notifikasi::where('pengguna_id', $user->id)
            ->orderByDesc('dibuat_pada')
            ->get();

        return view('notifikasi.index', compact('notifs'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Carilah notifikasi dengan ID yang diberikan, 
        // pastikan milik user yang sedang login
        $notif = Notifikasi::where('id', $id)
            ->where('pengguna_id', $user->id)
            ->first();

        if (!$notif) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Notifikasi tidak ditemukan.');
        }

        // Tandai sebagai dibaca
        $notif->update(['dibaca' => true]);

        return redirect()->route('notifikasi.index')
            ->with('success', 'Notifikasi telah ditandai dibaca.');
    }
}
