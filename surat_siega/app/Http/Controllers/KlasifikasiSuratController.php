<?php

namespace App\Http\Controllers;

use App\Models\KlasifikasiSurat;
use Illuminate\Http\Request;

class KlasifikasiSuratController extends Controller
{
    /**
     * Menampilkan daftar klasifikasi surat
     */
    public function index(Request $request)
    {
        $query = KlasifikasiSurat::query();

        if ($request->has('search') && $request->search) {
            $search = sanitize_input($request->search, 100); // Limit to 100 chars

            if ($search) {
                $searchEscaped = str_replace(['%', '_'], ['\%', '\_'], $search);

                $query->where(function ($q) use ($searchEscaped) {
                    $q->where('kode', 'LIKE', "%{$searchEscaped}%")->orWhere('deskripsi', 'LIKE', "%{$searchEscaped}%");
                });
            }
        }

        // Filter by prefix (A, B, C, dst)
        if ($request->has('prefix') && $request->prefix) {
            $query->byPrefix($request->prefix);
        }

        $list = $query->withCount('tugasHeaders')->orderBy('kode')->get();

        // Get available prefixes for filter tabs
        $prefixes = KlasifikasiSurat::getAvailablePrefixes();
        $activePrefix = $request->prefix;
        $searchTerm = $request->search;

        return view('klasifikasi_surat.index', compact('list', 'prefixes', 'activePrefix', 'searchTerm'));
    }

    /**
     * AJAX: Get next available code for a prefix + golongan
     */
    public function getNextCode(Request $request)
    {
        $request->validate([
            'prefix' => 'required|string|size:1|regex:/^[A-Z]$/',
            'golongan' => 'required|integer|min:1|max:99',
        ]);

        $prefix = $request->input('prefix');
        $golongan = $request->input('golongan');

        $nextCode = KlasifikasiSurat::getNextCode($prefix, $golongan);

        return response()->json([
            'code' => $nextCode,
            'prefix' => $prefix,
            'golongan' => $golongan,
        ]);
    }

    /**
     * AJAX: Get available golongan for a prefix
     */
    public function getAvailableGolongan(Request $request)
    {
        $prefix = $request->input('prefix');

        if (! $prefix || ! preg_match('/^[A-Z]$/', $prefix)) {
            return response()->json(['golongan' => []]);
        }

        $golongan = KlasifikasiSurat::getAvailableGolongan($prefix);

        return response()->json(['golongan' => $golongan]);
    }

    /**
     * Menyimpan klasifikasi surat baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|size:1|regex:/^[A-Z]$/',
            'golongan' => 'required|integer|min:1|max:99',
            'deskripsi' => 'required|string|max:255',
        ]);

        // Auto-generate kode
        $kode = KlasifikasiSurat::getNextCode($validated['prefix'], $validated['golongan']);

        // Cek apakah kode sudah ada (race condition prevention)
        $attempts = 0;
        while (KlasifikasiSurat::where('kode', $kode)->exists() && $attempts < 5) {
            $kode = KlasifikasiSurat::getNextCode($validated['prefix'], $validated['golongan']);
            $attempts++;
        }

        KlasifikasiSurat::create([
            'kode' => $kode,
            'deskripsi' => $validated['deskripsi'],
        ]);

        return redirect()
            ->route('klasifikasi_surat.index', ['prefix' => $validated['prefix']])
            ->with('success', 'Klasifikasi Surat berhasil ditambahkan dengan kode: '.$kode);
    }

    /**
     * Update klasifikasi surat
     */
    public function update(Request $request, KlasifikasiSurat $klasifikasi_surat)
    {
        $validated = $request->validate([
            'deskripsi' => 'required|string|max:255',
        ]);

        $klasifikasi_surat->update($validated);

        // Get prefix for redirect
        $prefix = substr($klasifikasi_surat->kode, 0, 1);

        return redirect()
            ->route('klasifikasi_surat.index', ['prefix' => $prefix])
            ->with('success', 'Klasifikasi Surat berhasil diperbarui.');
    }

    /**
     * Hapus klasifikasi surat
     */
    public function destroy(KlasifikasiSurat $klasifikasi_surat)
    {
        // Cek apakah klasifikasi ini sedang digunakan
        if ($klasifikasi_surat->tugasHeaders()->count() > 0) {
            return back()->with('error', 'Klasifikasi Surat tidak dapat dihapus karena sedang digunakan.');
        }

        $prefix = substr($klasifikasi_surat->kode, 0, 1);
        $klasifikasi_surat->delete();

        return redirect()
            ->route('klasifikasi_surat.index', ['prefix' => $prefix])
            ->with('success', 'Klasifikasi Surat berhasil dihapus.');
    }
}
