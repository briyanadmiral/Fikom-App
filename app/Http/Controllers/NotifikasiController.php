<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Tampilkan daftar notifikasi untuk user yang sedang login.
     */
    /**
     * Tampilkan daftar notifikasi untuk user yang sedang login.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Notifikasi::where('pengguna_id', $user->id);

        // Filter: Belum Dibaca
        if ($request->get('filter') === 'unread') {
            $query->where('dibaca', false);
        }

        // Urutkan dan Pagination
        $notifs = $query->orderByDesc('dibuat_pada')->paginate(20)->withQueryString();

        // Statistik untuk badges
        $stats = [
            'total' => Notifikasi::where('pengguna_id', $user->id)->count(),
            'unread' => Notifikasi::where('pengguna_id', $user->id)->unread()->count(),
            'read' => Notifikasi::where('pengguna_id', $user->id)->read()->count(),
        ];

        return view('notifikasi.index', compact('notifs', 'stats'));
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

        $notifId = validate_integer_id($id);
        if ($notifId === null) {
            return redirect()->back()->with('error', 'ID notifikasi tidak valid.');
        }

        $notif = Notifikasi::where('id', $notifId)->where('pengguna_id', $user->id)->first();

        if ($notif) {
            $notif->update(['dibaca' => true]);
            
            // Redirect ke link terkait jika ada
            if ($notif->link) {
                 return redirect($notif->link);
            }
        }

        return redirect()->back()->with('success', 'Notifikasi telah ditandai dibaca.');
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        Notifikasi::where('pengguna_id', $user->id)
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Hapus notifikasi lama yang sudah dibaca.
     */
    public function prune()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Hapus notifikasi yang sudah dibaca DAN lebih tua dari 30 hari
        $deleted = Notifikasi::where('pengguna_id', $user->id)
            ->where('dibaca', true)
            ->where('dibuat_pada', '<', now()->subDays(30))
            ->delete();

        if ($deleted > 0) {
            return redirect()->route('notifikasi.index')->with('success', "Berhasil membersihkan {$deleted} notifikasi lama.");
        }

        return redirect()->route('notifikasi.index')->with('info', 'Tidak ada notifikasi lama yang perlu dibersihkan.');
    }
}
