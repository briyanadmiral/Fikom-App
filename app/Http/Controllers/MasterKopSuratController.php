<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterKopSurat;
use App\Services\ImageOptimizerService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\User;

/**
 * Controller untuk pengaturan Kop Surat
 * ✅ UPDATED: Added dual logo, image optimization, presets, export/import
 */
class MasterKopSuratController extends Controller
{
    protected ImageOptimizerService $imageOptimizer;

    public function __construct(ImageOptimizerService $imageOptimizer)
    {
        $this->imageOptimizer = $imageOptimizer;
    }

    /**
     * Cek admin (owner/peran_id = 1) secara aman
     */
    private function ensureAdmin(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        $isAdmin = $user && ($user->id === 1 || (int) ($user->peran_id ?? 0) === 1);
        abort_unless($isAdmin, 403);
    }

    /**
     * Show settings page
     */
    public function index()
    {
        $this->ensureAdmin();
        
        $kop = MasterKopSurat::firstOrNew([]);
        
        // Defaults jika baru
        if (!$kop->exists) {
            $kop->mode_type = 'custom';
            $kop->text_align = 'left';
            $kop->logo_size = 160;
            $kop->font_size_title = 19;
            $kop->font_size_text = 12;
            $kop->header_padding = 5;
            $kop->background_opacity = 100;
            $kop->tampilkan_logo_kanan = true;
            $kop->tampilkan_logo_kiri = false;
        }

        // Get presets from config
        $presets = config('kop_surat_presets.presets', []);
        $paperSizes = config('kop_surat_presets.paper_sizes', []);

        return view('pengaturan.kop_surat.index', compact('kop', 'presets', 'paperSizes'));
    }

    /**
     * Update settings
     */
    public function update(Request $r)
    {
        $this->ensureAdmin();
        
        \Log::info('=== KOP UPDATE START ===');
        \Log::info('Has background file: ' . ($r->hasFile('background') ? 'YES' : 'NO'));
        \Log::info('Mode type: ' . $r->input('mode_type'));
        
        $kop = MasterKopSurat::firstOrNew([]);

        $data = $r->validate([
            'mode_type' => ['required', 'in:custom,upload'],
            'text_align' => ['nullable', 'in:left,right,center'],
            
            // Styling controls
            'logo_size' => ['nullable', 'integer', 'min:30', 'max:300'],
            'font_size_title' => ['nullable', 'integer', 'min:10', 'max:30'],
            'font_size_text' => ['nullable', 'integer', 'min:8', 'max:20'],
            'text_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'header_padding' => ['nullable', 'integer', 'min:0', 'max:250'],
            'background_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],

            // Text content
            'nama_fakultas' => ['nullable', 'string', 'max:255'],
            'alamat_lengkap' => ['nullable', 'string', 'max:500'],
            'telepon_lengkap' => ['nullable', 'string', 'max:255'],
            'email_website' => ['nullable', 'string', 'max:255'],

            // Files - now with separate names
            'logo_kanan' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'logo_kiri' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'background_custom' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'background_upload' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'cap' => ['sometimes', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],

            // Toggles
            'tampilkan_logo_kanan' => ['sometimes', 'boolean'],
            'tampilkan_logo_kiri' => ['sometimes', 'boolean'],
        ]);

        // Merge background inputs - use whichever is provided
        if ($r->hasFile('background_custom')) {
            \Log::info('Has background_custom file: YES');
            // Merge into 'background' key for processing
            $r->files->set('background', $r->file('background_custom'));
        } elseif ($r->hasFile('background_upload')) {
            \Log::info('Has background_upload file: YES');
            $r->files->set('background', $r->file('background_upload'));
        }

        // Normalize boolean values
        $data['tampilkan_logo_kanan'] = isset($data['tampilkan_logo_kanan']) ? (bool)$data['tampilkan_logo_kanan'] : false;
        $data['tampilkan_logo_kiri'] = isset($data['tampilkan_logo_kiri']) ? (bool)$data['tampilkan_logo_kiri'] : false;

        // File handling with optimization
        $fileTargets = [
            'logo_kanan' => ['column' => 'logo_kanan_path', 'method' => 'optimizeLogo'],
            'logo_kiri' => ['column' => 'logo_kiri_path', 'method' => 'optimizeLogo'],
            'cap' => ['column' => 'cap_path', 'method' => 'optimizeStamp'],
        ];

        foreach ($fileTargets as $inputName => $config) {
            if ($r->hasFile($inputName)) {
                $columnName = $config['column'];
                $optimizeMethod = $config['method'];

                \Log::info("Processing file upload: {$inputName}");
                \Log::info("  - Column: {$columnName}");
                \Log::info("  - Method: {$optimizeMethod}");

                // Delete old file
                if (!empty($kop->$columnName)) {
                    $oldPath = $kop->$columnName;
                    \Log::info("  - Old path exists: {$oldPath}");
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                        \Log::info("  - Old file deleted");
                    }
                }

                // Optimize and store new file
                $path = $this->imageOptimizer->$optimizeMethod($r->file($inputName));
                $data[$columnName] = $path;
                
                \Log::info("  - New path saved: {$path}");
            }
        }

        // Handle background separately - check both background_custom and background_upload
        $backgroundFile = null;
        if ($r->hasFile('background_custom')) {
            $backgroundFile = $r->file('background_custom');
            \Log::info("Using background_custom file");
        } elseif ($r->hasFile('background_upload')) {
            $backgroundFile = $r->file('background_upload');
            \Log::info("Using background_upload file");
        }

        if ($backgroundFile) {
            \Log::info("Processing background file upload");
            
            // Delete old file
            if (!empty($kop->background_path)) {
                $oldPath = $kop->background_path;
                \Log::info("  - Old background path exists: {$oldPath}");
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    \Log::info("  - Old background file deleted");
                }
            }

            // Optimize and store new file
            $path = $this->imageOptimizer->optimizeBackground($backgroundFile);
            $data['background_path'] = $path;
            
            \Log::info("  - New background path saved: {$path}");
        }

        // Cleanup file inputs from data
        unset($data['logo_kanan'], $data['logo_kiri'], $data['background_custom'], $data['background_upload'], $data['cap']);

        if (Schema::hasColumn('master_kop_surat', 'updated_by')) {
            $data['updated_by'] = auth()->id();
        }

        \Log::info('Data to be saved:', $data);

        $kop->fill($data);
        $kop->save();

        \Log::info('Kop saved with ID: ' . $kop->id);
        \Log::info('Background path in DB: ' . ($kop->background_path ?? 'NULL'));
        \Log::info('=== KOP UPDATE SUCCESS ===');

        MasterKopSurat::clearCache();

        return back()->with('success', 'Pengaturan kop surat berhasil disimpan.');
    }

    /**
     * Delete uploaded image
     */
    public function deleteImage($type): JsonResponse
    {
        $this->ensureAdmin();
        $kop = MasterKopSurat::firstOrFail();

        $columnMap = [
            'logo' => 'logo_kanan_path',
            'logo_kanan' => 'logo_kanan_path',
            'logo_kiri' => 'logo_kiri_path',
            'background' => 'background_path',
            'cap' => 'cap_path',
        ];

        if (!isset($columnMap[$type])) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $col = $columnMap[$type];
        if ($kop->$col && Storage::disk('public')->exists($kop->$col)) {
            Storage::disk('public')->delete($kop->$col);
        }
        
        $kop->$col = null;
        $kop->save();

        return response()->json(['success' => true]);
    }

    /**
     * Preview kop surat (AJAX)
     */
    public function preview(Request $r)
    {
        \Log::info('=== KOP PREVIEW START ===');
        \Log::info('Request Method: ' . $r->method());
        \Log::info('Request URL: ' . $r->fullUrl());
        \Log::info('Request Headers:', $r->headers->all());
        \Log::info('Has CSRF Token: ' . ($r->header('X-CSRF-TOKEN') ? 'YES' : 'NO'));
        
        try {
            $this->ensureAdmin();
            \Log::info('Admin check passed');
            
            $kop = MasterKopSurat::first() ?? new MasterKopSurat();
            \Log::info('Kop loaded, ID: ' . ($kop->id ?? 'NEW'));
            
            // Fill with request data for preview (exclude file uploads and CSRF token)
            $kop->fill($r->except(['_token', 'logo_kanan', 'logo_kiri', 'background', 'cap']));
            \Log::info('Kop filled with request data');
            
            $html = view('shared._kop_surat', [
                'kop' => $kop,
                'context' => 'web',
                'showDivider' => true,
            ])->render();
            
            \Log::info('View rendered successfully, length: ' . strlen($html));
            \Log::info('=== KOP PREVIEW SUCCESS ===');
            
            return $html;
        } catch (\Exception $e) {
            \Log::error('=== KOP PREVIEW ERROR ===');
            \Log::error('Error Message: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Apply preset configuration
     */
    public function applyPreset(Request $r): JsonResponse
    {
        $this->ensureAdmin();

        $presetKey = $r->input('preset');
        $presets = config('kop_surat_presets.presets', []);

        if (!isset($presets[$presetKey])) {
            return response()->json(['success' => false, 'message' => 'Preset not found'], 404);
        }

        $presetConfig = $presets[$presetKey]['config'];

        return response()->json([
            'success' => true,
            'config' => $presetConfig,
            'name' => $presets[$presetKey]['name'],
        ]);
    }

    /**
     * Export kop surat configuration as JSON
     */
    public function export(): \Symfony\Component\HttpFoundation\Response
    {
        $this->ensureAdmin();

        $kop = MasterKopSurat::first();
        if (!$kop) {
            return back()->with('error', 'Tidak ada konfigurasi kop surat untuk di-export.');
        }

        $exportData = $kop->exportConfig();

        // Include base64 encoded images if they exist
        $imagePaths = [
            'logo_kanan' => $kop->logo_kanan_path,
            'logo_kiri' => $kop->logo_kiri_path,
            'background' => $kop->background_path,
            'cap' => $kop->cap_path,
        ];

        foreach ($imagePaths as $key => $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                $contents = Storage::disk('public')->get($path);
                $mimeType = mime_content_type(Storage::disk('public')->path($path));
                $exportData['images'][$key] = [
                    'filename' => basename($path),
                    'mime_type' => $mimeType,
                    'data' => base64_encode($contents),
                ];
            }
        }

        $filename = 'kop_surat_backup_' . date('Y-m-d_His') . '.json';

        return Response::make(json_encode($exportData, JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import kop surat configuration from JSON
     */
    public function import(Request $r): JsonResponse
    {
        $this->ensureAdmin();

        $r->validate([
            'file' => ['required', 'file', 'mimes:json', 'max:10240'],
        ]);

        try {
            $contents = file_get_contents($r->file('file')->getPathname());
            $importData = json_decode($contents, true);

            if (!$importData || !isset($importData['config'])) {
                return response()->json(['success' => false, 'message' => 'Invalid import file format'], 400);
            }

            $kop = MasterKopSurat::firstOrNew([]);
            
            // Apply configuration
            $kop->fill($importData['config']);

            // Handle imported images
            if (isset($importData['images'])) {
                foreach ($importData['images'] as $key => $imageData) {
                    if (isset($imageData['data']) && isset($imageData['filename'])) {
                        $columnMap = [
                            'logo_kanan' => 'logo_kanan_path',
                            'logo_kiri' => 'logo_kiri_path',
                            'background' => 'background_path',
                            'cap' => 'cap_path',
                        ];

                        if (isset($columnMap[$key])) {
                            $columnName = $columnMap[$key];
                            
                            // Delete old file
                            if ($kop->$columnName && Storage::disk('public')->exists($kop->$columnName)) {
                                Storage::disk('public')->delete($kop->$columnName);
                            }

                            // Save new file
                            $path = 'kop/' . uniqid() . '_' . $imageData['filename'];
                            Storage::disk('public')->put($path, base64_decode($imageData['data']));
                            $kop->$columnName = $path;
                        }
                    }
                }
            }

            $kop->updated_by = auth()->id();
            $kop->save();

            MasterKopSurat::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi berhasil diimport.',
            ]);

        } catch (\Exception $e) {
            Log::error('Import kop surat failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal import: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get list of available presets
     */
    public function getPresets(): JsonResponse
    {
        $this->ensureAdmin();

        $presets = config('kop_surat_presets.presets', []);
        
        $result = [];
        foreach ($presets as $key => $preset) {
            $result[$key] = [
                'name' => $preset['name'],
                'description' => $preset['description'],
            ];
        }

        return response()->json($result);
    }
}
