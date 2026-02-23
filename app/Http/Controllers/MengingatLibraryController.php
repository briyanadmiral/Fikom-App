<?php

namespace App\Http\Controllers;

use App\Models\MengingatLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk mengelola library dasar hukum Mengingat (SK)
 */
class MengingatLibraryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of mengingat items
     */
    public function index(Request $request)
    {
        $query = MengingatLibrary::with('creator')
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

        $items = $query->get();
        $categories = MengingatLibrary::getCategories();

        return view('mengingat_library.index', compact('items', 'categories'));
    }

    /**
     * Show the form for creating a new item
     */
    public function create()
    {
        $categories = MengingatLibrary::getCategories();

        return view('mengingat_library.create', compact('categories'));
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
            'nomor_referensi' => 'nullable|string|max:100',
            'tanggal_referensi' => 'nullable|date',
        ]);

        $validated['dibuat_oleh'] = Auth::id();
        $validated['is_active'] = true;

        $item = MengingatLibrary::create($validated);

        return redirect()
            ->route('mengingat_library.index')
            ->with('success', 'Dasar hukum berhasil ditambahkan.');
    }

    /**
     * Show the form for editing
     */
    public function edit(MengingatLibrary $mengingatLibrary)
    {
        $categories = MengingatLibrary::getCategories();

        return view('mengingat_library.edit', [
            'item' => $mengingatLibrary,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, MengingatLibrary $mengingatLibrary)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string|max:10000',
            'kategori' => 'nullable|string|max:50',
            'nomor_referensi' => 'nullable|string|max:100',
            'tanggal_referensi' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $mengingatLibrary->update($validated);

        return redirect()
            ->route('mengingat_library.index')
            ->with('success', 'Dasar hukum berhasil diperbarui.');
    }

    /**
     * Remove the specified item
     */
    public function destroy(MengingatLibrary $mengingatLibrary)
    {
        $mengingatLibrary->delete();

        return redirect()
            ->route('mengingat_library.index')
            ->with('success', 'Dasar hukum berhasil dihapus.');
    }

    /**
     * AJAX: Search for autocomplete
     */
    public function search(Request $request)
    {
        $keyword = $request->input('q', '');
        $kategori = $request->input('kategori');
        $limit = min((int) $request->input('limit', 10), 50);

        $query = MengingatLibrary::active()
            ->search($keyword)
            ->popular()
            ->limit($limit);

        if (! empty($kategori)) {
            $query->byKategori($kategori);
        }

        $items = $query->get([
            'id', 'judul', 'isi', 'kategori',
            'nomor_referensi', 'tanggal_referensi', 'usage_count',
        ]);

        return response()->json(['data' => $items]);
    }

    /**
     * AJAX: Increment usage when item is inserted to SK
     */
    public function incrementUsage(MengingatLibrary $mengingatLibrary)
    {
        $mengingatLibrary->incrementUsage();

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Get categories with counts
     */
    public function categories()
    {
        $categories = MengingatLibrary::active()
            ->selectRaw('kategori, COUNT(*) as count')
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json($categories);
    }
}
