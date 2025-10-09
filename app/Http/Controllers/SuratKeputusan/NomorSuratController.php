<?php

namespace App\Http\Controllers\SuratKeputusan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NomorSuratService;

class NomorSuratController extends Controller
{
    public function reserve(Request $request, NomorSuratService $service)
    {
        // dukung 2 bentuk payload
        $docType = strtoupper($request->input('doc_type', 'ST'));

        $unit = $request->input('unit')
            ?? $request->input('unit_display')
            ?? data_get(config("nomor_surat.formats"), "{$docType}.unit");

        $data = $request->validate([
            'kode_klasifikasi' => 'required|string|max:50',
            'bulan_romawi'     => 'required|string|in:I,II,III,IV,V,VI,VII,VIII,IX,X,XI,XII',
            'tahun'            => 'required|integer|min:2000|max:2100',
        ]);

        if (!$unit) {
            return response()->json(['message' => 'Unit belum ditentukan'], 422);
        }

        $res = $service->reserve(
            $unit,
            $data['kode_klasifikasi'],
            $data['bulan_romawi'],
            (int) $data['tahun']
        );

        return response()->json($res);
    }
}
