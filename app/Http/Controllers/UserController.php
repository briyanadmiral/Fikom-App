<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Peran;
use Illuminate\Http\Request; // <-- DITAMBAHKAN
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar semua pengguna beserta peran.
     * SUDAH TERMASUK FITUR PENCARIAN
     */
    public function index(Request $request) // <-- Diubah untuk menerima Request
    {
        // Memulai query builder dengan relasi dan urutan yang sudah ada
        $query = User::with('peran')->latest();

        // [LOGIKA PENCARIAN]
        // Jika ada input 'search' dari form, tambahkan filter ke query
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_lengkap', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('npp', 'like', "%{$searchTerm}%");
            });
        }

        // Lakukan paginasi SETELAH query difilter.
        // `appends` akan memastikan parameter 'search' tetap ada di link pagination.
        $users = $query->paginate(15)->appends($request->only('search'));

        // Ambil semua peran (untuk modal 'Kelola Peran')
        $roles = Peran::all();

        // Kirim data ke view
        return view('users.index', compact('users', 'roles'));
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
            'npp'   => $this->formatNpp($request->input('npp')),
        ]);

        $validated = $request->validate([
            'email'         => [
                'required',
                'email',
                // unik pada pengguna.email, abaikan baris yang sudah soft-deleted
                Rule::unique('pengguna', 'email')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'nama_lengkap'  => 'required|string|max:100',
            'npp'           => [
                'nullable',
                'string',
                'max:50',
                // unik pada pengguna.npp, abaikan baris soft-deleted
                Rule::unique('pengguna', 'npp')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'jabatan'       => 'nullable|string|max:100',
            'peran_id'      => 'required|exists:peran,id',
            'status'        => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'password'      => 'required|string|min:6|confirmed',
        ]);

        try {
            User::create([
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'nama_lengkap'  => $validated['nama_lengkap'],
                'npp'           => $validated['npp'] ?? null,
                'jabatan'       => $validated['jabatan'] ?? null,
                'peran_id'      => $validated['peran_id'],
                'status'        => $validated['status'],
            ]);

            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Throwable $e) {
            // \Log::error('Gagal tambah user', ['err' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan user.');
        }
    }

    /**
     * Form edit user.
     */
    public function edit($id)
    {
        $user  = User::findOrFail($id);
        $peran = Peran::all();

        return view('users.edit', compact('user', 'peran'));
    }

    /**
     * Simpan perubahan user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Normalisasi input supaya validasi unik konsisten
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'npp'   => $this->formatNpp($request->input('npp')),
        ]);

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                // unik email, abaikan record saat ini, dan abaikan yang soft-deleted
                Rule::unique('pengguna', 'email')
                    ->ignore($user->id)
                    ->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'nama_lengkap'  => 'required|string|max:100',
            'npp'           => [
                'nullable',
                'string',
                'max:50',
                // unik npp, abaikan record saat ini & soft-deleted
                Rule::unique('pengguna', 'npp')
                    ->ignore($user->id)
                    ->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'jabatan'       => 'nullable|string|max:100',
            'peran_id'      => 'required|exists:peran,id',
            'status'        => ['required', Rule::in(['aktif', 'tidak_aktif'])],
            'password'      => 'nullable|string|min:6|confirmed',
        ]);

        try {
            $user->email        = $validated['email'];
            $user->nama_lengkap = $validated['nama_lengkap'];
            $user->npp          = $validated['npp'] ?? null;      // <— ikut diupdate
            $user->jabatan      = $validated['jabatan'] ?? null;
            $user->peran_id     = $validated['peran_id'];
            $user->status       = $validated['status'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Throwable $e) {
            // \Log::error('Gagal update user', ['id' => $user->id, 'err' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui user.');
        }
    }

    /**
     * Hapus user (Soft Delete).
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->check() && auth()->id() == $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        try {
            $user->delete(); // Soft delete
            return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Throwable $e) {
            // \Log::error('Gagal hapus user', ['id' => $user->id, 'err' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus user.');
        }
    }

    /**
 * Format NPP ke pola 3-1-4-3 (contoh: 058.1.2002.255).
 * Fallback: kalau bukan 11 digit, dikelompokkan per 3 digit (xxx.xxx.xxx...).
 */
private function formatNpp(?string $raw): ?string
{
    if ($raw === null) return null;
    $digits = preg_replace('/\D+/', '', $raw); // ambil angka saja
    if ($digits === '') return null;

    if (strlen($digits) === 11) {
        // Pola utama: 3-1-4-3
        return substr($digits, 0, 3) . '.' .
               substr($digits, 3, 1) . '.' .
               substr($digits, 4, 4) . '.' .
               substr($digits, 8, 3);
    }

    // Fallback aman: kelompok per 3 digit (biar tetap terbaca)
    return implode('.', str_split($digits, 3));
}

}
