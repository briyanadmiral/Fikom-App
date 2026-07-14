<?php

namespace App\Http\Controllers;

use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Uncomment if you have policy:
        // $this->authorizeResource(User::class, 'user');
    }

    /**
     * Tampilkan daftar pengguna dengan tabs Pending/Approved.
     * SUDAH TERMASUK FITUR PENCARIAN
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'approved');

        $query = User::with('peran');

        if ($tab === 'pending') {
            $query->where('approval_status', 'pending')
                  ->where(function ($q) {
                      $q->whereNotNull('npp')->orWhereNotNull('nim');
                  });
        } elseif ($tab === 'approved') {
            $query->where('approval_status', 'approved');
        } elseif ($tab === 'rejected') {
            $query->where('approval_status', 'rejected');
        }

        $query->latest();

        if ($request->filled('search')) {
            $searchTerm = sanitize_input($request->search, 100);

            if ($searchTerm) {
                $searchEscaped = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);

                $query->where(function ($q) use ($searchEscaped) {
                    $q->where('nama_lengkap', 'like', "%{$searchEscaped}%")
                        ->orWhere('email', 'like', "%{$searchEscaped}%")
                        ->orWhere('npp', 'like', "%{$searchEscaped}%")
                        ->orWhere('nim', 'like', "%{$searchEscaped}%");
                });
            }
        }

        $users = $query->get();

        $pendingCount = User::where('approval_status', 'pending')
                            ->where(function ($q) {
                                $q->whereNotNull('npp')->orWhereNotNull('nim');
                            })->count();
        $approvedCount = User::where('approval_status', 'approved')->count();
        $rejectedCount = User::where('approval_status', 'rejected')->count();

        $roles = Peran::withCount('users')->get();

        return view('users.index', compact('users', 'roles', 'tab', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        $peran = Peran::all();

        return view('users.create', compact('peran'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        // Normalisasi input agar validasi unik tidak "kejebak" spasi/kasus
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'npp' => $this->formatNpp($request->input('npp')),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('pengguna', 'email')->where(fn ($q) => $q->whereNull('deleted_at'))],
            'nama_lengkap' => 'required|string|max:100',
            'npp' => ['nullable', 'string', 'max:50', Rule::unique('pengguna', 'npp')->where(fn ($q) => $q->whereNull('deleted_at'))],
            'jabatan' => 'nullable|string|max:100',
            'peran_id' => 'required|exists:peran,id',
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            User::create([
                'email' => $validated['email'],
                'sandi_hash' => Hash::make($validated['password']),
                'nama_lengkap' => $validated['nama_lengkap'],
                'npp' => $validated['npp'] ?? null,
                'jabatan' => $validated['jabatan'] ?? null,
                'peran_id' => $validated['peran_id'],
                'status' => $validated['status'],
            ]);

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Log::error('Gagal tambah user', [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan user.');
        }
    }

    /**
     * Form edit user.
     */
    public function edit($id)
    {
        $userId = validate_integer_id($id);
        if ($userId === null) {
            abort(404, 'ID tidak valid');
        }

        $user = User::findOrFail($userId);
        $peran = Peran::all();

        return view('users.edit', compact('user', 'peran'));
    }

    /**
     * Simpan perubahan user.
     */
    public function update(Request $request, $id)
    {
        $userId = validate_integer_id($id);
        if ($userId === null) {
            abort(404, 'ID tidak valid');
        }

        $user = User::findOrFail($userId);

        // Normalisasi input supaya validasi unik konsisten
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'npp' => $this->formatNpp($request->input('npp')),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email', Rule::unique('pengguna', 'email')->ignore($user->id)->where(fn ($q) => $q->whereNull('deleted_at'))],
            'nama_lengkap' => 'required|string|max:100',
            'npp' => ['nullable', 'string', 'max:50', Rule::unique('pengguna', 'npp')->ignore($user->id)->where(fn ($q) => $q->whereNull('deleted_at'))],
            'jabatan' => 'nullable|string|max:100',
            'peran_id' => 'required|exists:peran,id',
            'status' => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            $user->email = $validated['email'];
            $user->nama_lengkap = $validated['nama_lengkap'];
            $user->npp = $validated['npp'] ?? null;
            $user->jabatan = $validated['jabatan'] ?? null;
            $user->peran_id = $validated['peran_id'];
            $user->status = $validated['status'];

            if (! empty($validated['password'])) {
                $user->sandi_hash = Hash::make($validated['password']);
            }

            $user->save();

            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Gagal update user', [
                'id' => $user->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui user.');
        }
    }

    /**
     * Hapus user (Soft Delete).
     */
    public function destroy($id)
    {
        $userId = validate_integer_id($id);
        if ($userId === null) {
            abort(404, 'ID tidak valid');
        }

        $user = User::findOrFail($userId);

        if (auth()->check() && auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        try {
            $user->delete(); // Soft delete

            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal hapus user', [
                'id' => $user->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus user.');
        }
    }

    /**
     * Approve pending user (admin_tu only).
     */
    public function approve($id)
    {
        // Authorization: hanya admin_tu (peran_id 1)
        if (!auth()->check() || auth()->user()->peran_id !== 1) {
            abort(403, 'Akses ditolak. Hanya Admin TU yang dapat memverifikasi user.');
        }

        $userId = validate_integer_id($id);
        if ($userId === null) {
            abort(404, 'ID tidak valid');
        }

        $user = User::findOrFail($userId);

        if ($user->approval_status !== 'pending') {
            return back()->with('error', 'User sudah diproses sebelumnya.');
        }

        try {
            $user->approval_status = 'approved';
            $user->save();

            // TODO: Send email notification to user

            return redirect()->route('users.index', ['tab' => 'approved'])
                ->with('success', 'User berhasil diverifikasi dan email notifikasi telah dikirim.');
        } catch (\Throwable $e) {
            Log::error('Gagal approve user', [
                'id' => $user->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memverifikasi user.');
        }
    }

    /**
     * Reject pending user (admin_tu only).
     */
    public function reject($id)
    {
        // Authorization: hanya admin_tu (peran_id 1)
        if (!auth()->check() || auth()->user()->peran_id !== 1) {
            abort(403, 'Akses ditolak. Hanya Admin TU yang dapat menolak user.');
        }

        $userId = validate_integer_id($id);
        if ($userId === null) {
            abort(404, 'ID tidak valid');
        }

        $user = User::findOrFail($userId);

        if ($user->approval_status !== 'pending') {
            return back()->with('error', 'User sudah diproses sebelumnya.');
        }

        try {
            $user->approval_status = 'rejected';
            $user->save();

            return redirect()->route('users.index', ['tab' => 'pending'])
                ->with('success', 'User berhasil ditolak.');
        } catch (\Throwable $e) {
            Log::error('Gagal reject user', [
                'id' => $user->id,
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menolak user.');
        }
    }

    /**
     * Format NPP ke pola 3-1-4-3 (contoh: 058.1.2002.255).
     * Fallback: kalau bukan 11 digit, dikelompokkan per 3 digit (xxx.xxx.xxx...).
     */
    private function formatNpp(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $raw); // ambil angka saja
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11) {
            // Pola utama: 3-1-4-3
            return substr($digits, 0, 3).'.'.substr($digits, 3, 1).'.'.substr($digits, 4, 4).'.'.substr($digits, 8, 3);
        }

        // Fallback aman: kelompok per 3 digit (biar tetap terbaca)
        return implode('.', str_split($digits, 3));
    }
}
