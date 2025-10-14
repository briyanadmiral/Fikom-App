<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MasterKopSuratController extends Controller
{
    public function index()
    {
        abort_unless(auth()->id() === 1 || auth()->user()->peran_id === 1, 403);
        
        $kop = MasterKopSurat::firstOrCreate([]);
        return view('pengaturan.kop_surat', compact('kop'));
    }

    public function update(Request $r)
    {
        abort_unless(auth()->id() === 1 || auth()->user()->peran_id === 1, 403);
        
        $data = $r->validate([
            'mode_type' => ['required', 'in:custom,upload'],
            'text_align' => ['nullable', 'in:left,right,center'],
            
            // Kontrol styling
            'logo_size' => ['nullable', 'integer', 'min:30', 'max:200'],
            'font_size_title' => ['nullable', 'integer', 'min:10', 'max:30'],
            'font_size_text' => ['nullable', 'integer', 'min:8', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:7'],
            'header_padding' => ['nullable', 'integer', 'min:0', 'max:50'],
            'background_opacity' => ['nullable', 'integer', 'min:0', 'max:100'],
            
            // Data teks
            'nama_fakultas' => ['nullable','string','max:255'],
            'alamat_lengkap' => ['nullable','string','max:500'],
            'telepon_lengkap' => ['nullable','string','max:255'],
            'email_website' => ['nullable','string','max:255'],
            
            // Upload files
            'logo_kanan' => ['sometimes','file','image','max:1024'],
            'background_header' => ['sometimes','file','image','max:4096'],
            'background' => ['sometimes','file','image','max:4096'],
            'cap' => ['sometimes','file','image','max:1024'],
            
            'tampilkan_logo_kanan' => ['nullable'],
        ]);

        $kop = MasterKopSurat::firstOrCreate([]);

        // Jika mode upload, kosongkan field custom
        if ($data['mode_type'] === 'upload') {
            $data['nama_fakultas'] = null;
            $data['alamat_lengkap'] = null;
            $data['telepon_lengkap'] = null;
            $data['email_website'] = null;
        }

        // Jika tidak ada text_align, set default
        if (!isset($data['text_align'])) {
            $data['text_align'] = 'right';
        }

        // Handle file uploads
        $fileMap = [
            'logo_kanan' => 'logo_kanan_path',
            'background_header' => 'background_path',
            'background' => 'background_path',
            'cap' => 'cap_path',
        ];
        
        foreach ($fileMap as $inputName => $columnName) {
            if ($r->hasFile($inputName)) {
                Log::info("Processing file: {$inputName}");
                
                // Hapus file lama jika ada
                if (!empty($kop->$columnName)) {
                    try {
                        Storage::disk('public')->delete($kop->$columnName);
                        Log::info("Deleted old file: {$kop->$columnName}");
                    } catch (\Exception $e) {
                        Log::warning("Gagal hapus file lama: " . $e->getMessage());
                    }
                }
                
                // Upload file baru
                $file = $r->file($inputName);
                $path = $file->store('kop', 'public');
                $data[$columnName] = $path;
                
                Log::info("File uploaded: {$inputName} -> {$path}");
            }
        }

        // Remove file input dari data array
        unset($data['logo_kanan'], $data['background_header'], $data['background'], $data['cap']);
        
        // Set updated_by jika kolom ada
        if (Schema::hasColumn('master_kop_surat', 'updated_by')) {
            $data['updated_by'] = auth()->id();
        }

        // Update ke database
        $kop->update($data);
        
        Log::info("Kop surat updated", ['data' => $data]);
        
        return back()->with('success', 'Pengaturan kop surat berhasil diperbarui.');
    }
    
    // METHOD BARU: Delete Image
    public function deleteImage($type)
    {
        abort_unless(auth()->id() === 1 || auth()->user()->peran_id === 1, 403);
        
        $kop = MasterKopSurat::first();
        if (!$kop) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        
        // Map type ke column name
        $columnMap = [
            'logo' => 'logo_kanan_path',
            'background' => 'background_path',
            'cap' => 'cap_path',
        ];
        
        if (!isset($columnMap[$type])) {
            return response()->json(['success' => false, 'message' => 'Tipe gambar tidak valid'], 400);
        }
        
        $columnName = $columnMap[$type];
        $filePath = $kop->$columnName;
        
        if (!$filePath) {
            return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan'], 404);
        }
        
        try {
            // Hapus file dari storage
            Storage::disk('public')->delete($filePath);
            
            // Update database
            $kop->update([$columnName => null]);
            
            Log::info("Image deleted: {$type} -> {$filePath}");
            
            return response()->json([
                'success' => true, 
                'message' => 'Gambar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to delete image: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus gambar: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function preview(Request $r)
    {
        $kop = MasterKopSurat::first() ?? new MasterKopSurat();
        
        // Temporary update untuk preview
        $kop->text_align = $r->text_align ?? 'right';
        $kop->logo_size = $r->logo_size ?? 100;
        $kop->font_size_title = $r->font_size_title ?? 14;
        $kop->font_size_text = $r->font_size_text ?? 10;
        $kop->text_color = $r->text_color ?? '#000000';
        $kop->header_padding = $r->header_padding ?? 15;
        $kop->background_opacity = $r->background_opacity ?? 100;
        $kop->nama_fakultas = $r->nama_fakultas ?? $kop->nama_fakultas;
        $kop->alamat_lengkap = $r->alamat_lengkap ?? $kop->alamat_lengkap;
        $kop->telepon_lengkap = $r->telepon_lengkap ?? $kop->telepon_lengkap;
        $kop->email_website = $r->email_website ?? $kop->email_website;
        
        return view('shared._kop_surat', [
            'kop' => $kop,
            'context' => 'web',
            'showDivider' => true
        ])->render();
    }
}
