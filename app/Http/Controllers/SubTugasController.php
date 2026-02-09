<?php

namespace App\Http\Controllers;

use App\Models\JenisTugas;
use App\Models\SubTugas;
use Illuminate\Http\Request;

class SubTugasController extends Controller
{
    /**
     * Menampilkan daftar sub tugas dari jenis tugas tertentu
     */
    public function index(JenisTugas $jenistugas)
    {
        // Eager load sub tugas
        // $jenistugas->load('subTugas.detail'); // Removed detail eager load

        $list = $jenistugas->subTugas()->orderBy('nama')->get();

        return view('sub_tugas.index', compact('jenistugas', 'list'));
    }

    /**
     * Menyimpan sub tugas baru
     */
    public function store(Request $request, JenisTugas $jenistugas)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $jenistugas->subTugas()->create($validated);

        return redirect()->route('sub_tugas.index', $jenistugas->id)->with('success', 'Sub Tugas berhasil ditambahkan.');
    }

    /**
     * Update sub tugas
     */
    public function update(Request $request, JenisTugas $jenistugas, SubTugas $subtugas)
    {
        // ✅ FIXED: Type-safe comparison
        if ((int) $subtugas->jenis_tugas_id !== (int) $jenistugas->id) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $subtugas->update($validated);

        return redirect()->route('sub_tugas.index', $jenistugas->id)->with('success', 'Sub Tugas berhasil diperbarui.');
    }

    /**
     * Hapus sub tugas
     */
    public function destroy(JenisTugas $jenistugas, SubTugas $subtugas)
    {
        // ✅ FIXED: Type-safe comparison
        if ((int) $subtugas->jenis_tugas_id !== (int) $jenistugas->id) {
            abort(404);
        }

        $subtugas->delete();

        return back()->with('success', 'Sub Tugas berhasil dihapus.');
    }
}
