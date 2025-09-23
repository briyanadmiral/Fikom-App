<?php

namespace App\Http\Controllers;

use App\Models\Peran;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // List semua peran (JSON, untuk select dropdown dynamic)
    public function index(Request $request)
    {
        // Jika AJAX, return json (untuk refresh dropdown tanpa reload)
        if ($request->ajax()) {
            return response()->json(Peran::orderBy('nama')->get());
        }
        // Jika akses biasa, return view (opsional)
        return view('roles.index', ['roles' => Peran::orderBy('nama')->get()]);
    }

    // Store peran baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:peran,nama',
            'deskripsi' => 'nullable|string|max:255'
        ]);
        $role = Peran::create($validated);
        return response()->json([
            'status' => 'ok',
            'message' => 'Peran berhasil ditambahkan.',
            'role' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Peran::findOrFail($id);
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:peran,nama,' . $role->id,
            'deskripsi' => 'nullable|string|max:255'
        ]);
        $role->update($validated);
        return response()->json([
            'status' => 'ok',
            'message' => 'Peran berhasil diperbarui.',
            'role' => $role
        ]);
    }
    public function destroy($id)
    {
        $role = Peran::findOrFail($id);
        $role->delete();
        return response()->json(['status' => 'ok', 'message' => 'Peran berhasil dihapus.']);
    }
}
