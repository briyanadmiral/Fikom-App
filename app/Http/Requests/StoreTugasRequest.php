<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asumsikan otorisasi ditangani di controller/route
    }

    public function rules(): array
    {
        // Pindahkan semua rules dari method store() di controller ke sini
        return [
            'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
            'nama_umum'            => 'required|string|max:255',
            'jenis_tugas'          => 'required|string',
            'tugas'                => 'required|string',
            'detail_tugas'         => 'nullable|string|max:65000',
            'redaksi_pembuka'      => 'nullable|string|max:2000',
            'penutup'              => 'nullable|string|max:1000',

            'penerima_internal'   => 'sometimes|array',
            'penerima_internal.*' => 'exists:pengguna,id',

            'penerima_eksternal'              => 'sometimes|array',
            'penerima_eksternal.*.nama'       => 'required_with:penerima_eksternal|string|max:255',
            'penerima_eksternal.*.instansi'   => 'nullable|string|max:255',
            'penerima_eksternal.*.jabatan'    => 'nullable|string|max:255',

            'status_penerima'      => 'nullable|string|max:50',

            'tahun'                => 'required|integer|digits:4',
            'semester'             => 'required|string',
            'bulan'                => 'required|string',
            'nomor'                => 'required|string|max:255',
            'no_surat_manual'      => 'nullable|string|max:255',
            'asal_surat'           => 'required|exists:pengguna,id',
            'nama_pembuat'         => 'required|string',

            'penandatangan'        => 'required|exists:pengguna,id',
            'waktu_mulai'          => 'required|date',
            'waktu_selesai'        => 'required|date|after_or_equal:waktu_mulai',
            'tempat'               => 'required|string|max:255',

            'tembusan'             => 'nullable',
            'tembusan_formatted'   => 'nullable|string|max:10000',
        ];
    }

    protected function prepareForValidation(): void
    {
        // ✅ Pindahkan logika normalisasi tembusan ke sini
        if ($this->has('tembusan')) {
            $this->merge([
                'tembusan' => $this->normalizeTembusanLines($this->input('tembusan')),
            ]);
        }
    }

    private function normalizeTembusanLines($raw): string
    {
        $items = [];
        if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) $raw = $decoded;
        }

        if (is_array($raw)) {
            foreach ($raw as $v) {
                if (is_array($v) && isset($v['value'])) $items[] = (string) $v['value'];
                elseif (is_string($v)) $items[] = $v;
            }
        } elseif (is_string($raw)) {
            $parts = preg_split("/[\n,]+/u", $raw) ?: [];
            foreach ($parts as $p) $items[] = $p;
        }

        $norm = collect($items)->map(fn($s) => trim((string) $s))->filter()
            ->unique(fn($s) => mb_strtolower($s))->values()->all();

        return implode("\n", $norm);
    }
}
