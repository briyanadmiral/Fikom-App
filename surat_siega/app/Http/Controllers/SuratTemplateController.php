<?php

namespace App\Http\Controllers;

use App\Models\JenisTugas;
use App\Models\SubTugas;
use App\Models\SuratTemplate;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SuratTemplateController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $query = SuratTemplate::with(['jenisTugas', 'subTugas', 'creator'])
            ->active()
            ->orderBy('nama');

        // Filter by jenis tugas
        if ($request->filled('jenis_tugas_id')) {
            $query->byJenis($request->jenis_tugas_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $templates = $query->paginate(15);
        $jenisTugasList = JenisTugas::orderBy('nama')->get();

        return view('surat_templates.index', compact('templates', 'jenisTugasList'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        $jenisTugasList = JenisTugas::orderBy('nama')->get();
        $subTugasList = SubTugas::with('jenisTugas')->orderBy('nama')->get();
        $placeholders = SuratTemplate::getPlaceholders();

        return view('surat_templates.create', compact('jenisTugasList', 'subTugasList', 'placeholders'));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'jenis_tugas_id' => 'nullable|exists:jenis_tugas,id',
            'sub_tugas_id' => 'nullable|exists:sub_tugas,id',
            'detail_tugas' => 'required|string',
            'tembusan' => 'nullable|string',
        ]);

        $validated['dibuat_oleh'] = Auth::id();
        $validated['is_active'] = true;

        $template = SuratTemplate::create($validated);

        $this->auditService->logCreate($template);

        return redirect()
            ->route('surat_templates.index')
            ->with('success', 'Template berhasil dibuat.');
    }

    /**
     * Display the specified template
     */
    public function show(SuratTemplate $surat_template)
    {
        $surat_template->load(['jenisTugas', 'creator']);
        $preview = $surat_template->preview();

        return view('surat_templates.show', compact('surat_template', 'preview'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(SuratTemplate $surat_template)
    {
        $jenisTugasList = JenisTugas::orderBy('nama')->get();
        $subTugasList = SubTugas::with('jenisTugas')->orderBy('nama')->get();
        $placeholders = SuratTemplate::getPlaceholders();

        return view('surat_templates.edit', compact('surat_template', 'jenisTugasList', 'subTugasList', 'placeholders'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, SuratTemplate $surat_template)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:500',
            'jenis_tugas_id' => 'nullable|exists:jenis_tugas,id',
            'sub_tugas_id' => 'nullable|exists:sub_tugas,id',
            'detail_tugas' => 'required|string',
            'tembusan' => 'nullable|string',
        ]);

        $original = $surat_template->getOriginal();
        $surat_template->update($validated);

        $this->auditService->logUpdate($surat_template, $original);

        return redirect()
            ->route('surat_templates.index')
            ->with('success', 'Template berhasil diperbarui.');
    }

    /**
     * Remove the specified template (soft delete)
     */
    public function destroy(SuratTemplate $surat_template)
    {
        $this->auditService->logDelete($surat_template);

        $surat_template->delete();

        return redirect()
            ->route('surat_templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    /**
     * Preview template with sample data
     */
    public function preview(SuratTemplate $surat_template)
    {
        $preview = $surat_template->preview();

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }

    /**
     * Duplicate template
     */
    public function duplicate(SuratTemplate $surat_template)
    {
        $newTemplate = $surat_template->duplicate(Auth::id());

        $this->auditService->logCreate($newTemplate);

        return redirect()
            ->route('surat_templates.edit', $newTemplate)
            ->with('success', 'Template berhasil diduplikasi. Silakan edit sesuai kebutuhan.');
    }

    /**
     * AJAX: Get template data for form population
     */
    public function getTemplate($id)
    {
        $template = SuratTemplate::findOrFail($id);

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'nama' => $template->nama,
                'detail_tugas' => $template->detail_tugas,
                'tembusan' => $template->tembusan,
                'jenis_tugas_id' => $template->jenis_tugas_id,
            ],
            'placeholders' => $template->getUsedPlaceholders(),
        ]);
    }
}
