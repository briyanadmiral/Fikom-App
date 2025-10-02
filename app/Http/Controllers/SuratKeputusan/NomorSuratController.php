<?php

namespace App\Http\Controllers\SuratKeputusan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NomorSuratService;

class NomorSuratController extends Controller
{
    public function reserve(Request $request, NomorSuratService $service)
    {
        $data = $request->validate([
            'unit'             => 'required|string|max:50',             // contoh: TG / FKOM / B.10.1 (sesuai skema unit-mu)
            'kode_klasifikasi' => 'required|string|max:50',             // contoh: B.10.1 / SK (sesuaikan organisasi)
            'bulan_romawi'     => 'required|string|in:I,II,III,IV,V,VI,VII,VIII,IX,X,XI,XII',
            'tahun'            => 'required|integer|min:2000|max:2100',
        ]);

        $res = $service->reserve(
            $data['unit'],
            $data['kode_klasifikasi'],
            $data['bulan_romawi'],
            (int)$data['tahun']
        );

        return response()->json($res);
    }
}
