<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Storage;

class MasterKopController extends Controller
{
    /**
     * Display list of kop templates
     */
    public function index()
    {
        $kops = MasterKopSurat::orderBy('is_default', 'desc')
            ->orderBy('nama_kop')
            ->get();

        return view('master_kop.index', compact('kops'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('master_kop.create');
    }

    /**
     * Store new kop
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kop' => 'required|string|max:100',
            'unit_code' => 'nullable|string|max:50',
            'nama_universitas' => 'required|string|max:255',
            'nama_fakultas' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'logo_path' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_default' => 'boolean',
        ]);

        $data = $request->only([
            'nama_kop', 'unit_code', 'nama_universitas', 'nama_fakultas',
            'alamat', 'telepon', 'email', 'website'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo_path')) {
            $data['logo_path'] = $request->file('logo_path')->store('kop_logos', 'public');
        }

        // Handle default
        if ($request->boolean('is_default')) {
            MasterKopSurat::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        MasterKopSurat::create($data);

        return redirect()->route('kop.index')->with('success', 'Template kop berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(MasterKopSurat $kop)
    {
        return view('master_kop.edit', compact('kop'));
    }

    /**
     * Update kop
     */
    public function update(Request $request, MasterKopSurat $kop)
    {
        $request->validate([
            'nama_kop' => 'required|string|max:100',
            'unit_code' => 'nullable|string|max:50',
            'nama_universitas' => 'required|string|max:255',
            'nama_fakultas' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'logo_path' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'is_default' => 'boolean',
        ]);

        $data = $request->only([
            'nama_kop', 'unit_code', 'nama_universitas', 'nama_fakultas',
            'alamat', 'telepon', 'email', 'website'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo_path')) {
            // Delete old logo
            if ($kop->logo_path && Storage::disk('public')->exists($kop->logo_path)) {
                Storage::disk('public')->delete($kop->logo_path);
            }
            $data['logo_path'] = $request->file('logo_path')->store('kop_logos', 'public');
        }

        // Handle default
        if ($request->boolean('is_default')) {
            MasterKopSurat::where('is_default', true)
                ->where('id', '!=', $kop->id)
                ->update(['is_default' => false]);
            $data['is_default'] = true;
        } else {
            $data['is_default'] = false;
        }

        $kop->update($data);

        return redirect()->route('kop.index')->with('success', 'Template kop berhasil diperbarui.');
    }

    /**
     * Delete kop
     */
    public function destroy(MasterKopSurat $kop)
    {
        if ($kop->is_default) {
            return redirect()->back()->withErrors(['error' => 'Tidak dapat menghapus kop default.']);
        }

        if ($kop->logo_path && Storage::disk('public')->exists($kop->logo_path)) {
            Storage::disk('public')->delete($kop->logo_path);
        }

        $kop->delete();

        return redirect()->route('kop.index')->with('success', 'Template kop berhasil dihapus.');
    }

    /**
     * Set as default
     */
    public function setDefault(MasterKopSurat $kop)
    {
        MasterKopSurat::where('is_default', true)->update(['is_default' => false]);
        $kop->update(['is_default' => true]);

        return redirect()->route('kop.index')->with('success', 'Kop berhasil dijadikan default.');
    }

    /**
     * Get kop list for AJAX
     */
    public function list()
    {
        $kops = MasterKopSurat::select('id', 'nama_kop', 'unit_code', 'is_default')
            ->orderBy('is_default', 'desc')
            ->orderBy('nama_kop')
            ->get();

        return response()->json($kops);
    }
}
