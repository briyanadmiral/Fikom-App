<?php

namespace App\Http\Controllers;

use App\Models\MenimbangLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk mengelola library poin Menimbang (SK)
 */
class MenimbangLibraryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of menimbang items
     */
    public function index(Request $request)
    {
        $query = MenimbangLibrary::with('creator')
            ->active();

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->byKategori($request->input('kategori'));
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Order by
        $orderBy = $request->input('order_by', 'usage_count');
        $orderDir = $request->input('order_dir', 'desc');
        
        if ($orderBy === 'usage_count') {
            $query->popular();
        } else {
            $query->orderBy($orderBy, $orderDir);
        }

        $items = $query->paginate(20);
        $categories = MenimbangLibrary::getCategories();

        return view('menimbang_library.index', compact('items', 'categories'));
    }

    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        return view('menimbang_library.create');
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string|max:10000',
            'kategori' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
        ]);

        $validated['dibuat_oleh'] = Auth::id();
        $validated['is_active'] = true;

        $item = MenimbangLibrary::create($validated);

        return redirect()
            ->route('menimbang_library.index')
            ->with('success', 'Poin menimbang berhasil ditambahkan.');
    }

    /**
     * Show the form for editing
     */
    public function edit(MenimbangLibrary $menimbangLibrary)
    {
        return view('menimbang_library.edit', ['item' => $menimbangLibrary]);
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, MenimbangLibrary $menimbangLibrary)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string|max:10000',
            'kategori' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $menimbangLibrary->update($validated);

        return redirect()
            ->route('menimbang_library.index')
            ->with('success', 'Poin menimbang berhasil diperbarui.');
    }

    /**
     * Remove the specified item
     */
    public function destroy(MenimbangLibrary $menimbangLibrary)
    {
        $menimbangLibrary->delete();

        return redirect()
            ->route('menimbang_library.index')
            ->with('success', 'Poin menimbang berhasil dihapus.');
    }

    /**
     * AJAX: Search for autocomplete
     */
    public function search(Request $request)
    {
        $keyword = $request->input('q', '');
        $kategori = $request->input('kategori');
        $limit = min((int) $request->input('limit', 10), 50);

        $query = MenimbangLibrary::active()
            ->search($keyword)
            ->popular()
            ->limit($limit);

        if (!empty($kategori)) {
            $query->byKategori($kategori);
        }

        $items = $query->get(['id', 'judul', 'isi', 'kategori', 'usage_count']);

        return response()->json(['data' => $items]);
    }

    /**
     * AJAX: Increment usage when item is inserted to SK
     */
    public function incrementUsage(MenimbangLibrary $menimbangLibrary)
    {
        $menimbangLibrary->incrementUsage();

        return response()->json(['success' => true]);
    }
}
