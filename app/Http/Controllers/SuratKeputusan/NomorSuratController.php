<?php

namespace App\Http\Controllers\SuratKeputusan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NomorSuratService;

class NomorSuratController extends Controller
{
    /**
     * ✅ ADDED: Authorization middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function reserve(Request $request, NomorSuratService $service)
    {
        // ✅ FIXED: Sanitize and validate doc_type
        $docTypeRaw = $request->input('doc_type', 'ST');
        $docType = strtoupper(sanitize_input($docTypeRaw, 10));

        // ✅ Whitelist validation for doc_type
        $validDocTypes = ['ST', 'SK', 'SP', 'SU']; // Adjust based on your system
        if (!in_array($docType, $validDocTypes, true)) {
            return response()->json(['message' => 'Tipe dokumen tidak valid'], 422);
        }

        // ✅ FIXED: Sanitize unit input
        $unit = $request->input('unit') ?? ($request->input('unit_display') ?? data_get(config('nomor_surat.formats'), "{$docType}.unit"));

        if ($unit) {
            $unit = sanitize_input($unit, 20);
        }

        $data = $request->validate([
            'kode_klasifikasi' => 'required|string|max:50',
            'bulan_romawi' => 'required|string|in:I,II,III,IV,V,VI,VII,VIII,IX,X,XI,XII',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        // ✅ FIXED: Sanitize kode_klasifikasi
        $data['kode_klasifikasi'] = sanitize_input($data['kode_klasifikasi'], 50);

        if (!$unit) {
            return response()->json(['message' => 'Unit belum ditentukan'], 422);
        }

        try {
            $res = $service->reserve($unit, $data['kode_klasifikasi'], $data['bulan_romawi'], (int) $data['tahun']);

            return response()->json($res);
        } catch (\Exception $e) {
            \Log::error('Failed to reserve nomor surat', [
                'error' => sanitize_log_message($e->getMessage()),
                'user_id' => auth()->id(),
                'doc_type' => $docType,
            ]);

            return response()->json(
                [
                    'message' => 'Gagal mereservasi nomor surat',
                ],
                500,
            );
        }
    }
}
