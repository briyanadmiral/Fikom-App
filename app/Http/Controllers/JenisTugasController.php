<?php

namespace App\Http\Controllers;

use App\Models\JenisTugas;
use Illuminate\Http\Request;

class JenisTugasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $list = JenisTugas::with('subTugas')->orderBy('nama')->get();
        return view('jenis_surat_tugas.index', compact('list'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:jenistugas,nama',
        ]);

        JenisTugas::create($validated);

        return redirect()->route('jenis_surat_tugas.index')->with('success', 'Jenis Surat Tugas berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JenisTugas $jenis_surat_tugas)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:jenistugas,nama,' . $jenis_surat_tugas->id,
        ]);

        $jenis_surat_tugas->update($validated);

        return redirect()->route('jenis_surat_tugas.index')->with('success', 'Jenis Surat Tugas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JenisTugas $jenis_surat_tugas)
    {
        // Check if has sub tugas
        if ($jenis_surat_tugas->subTugas()->count() > 0) {
            return back()->with('error', 'Jenis Surat Tugas tidak dapat dihapus karena memiliki Sub Tugas.');
        }

        $jenis_surat_tugas->delete();

        return back()->with('success', 'Jenis Surat Tugas berhasil dihapus.');
    }
}
