<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tugasId = $this->route('tuga')->id; // Nama parameter route adalah 'tuga', bukan 'tugas'

        // Pindahkan rules dari method update() di controller
        return [
            'klasifikasi_surat_id' => 'required|exists:klasifikasi_surat,id',
            'nama_umum' => 'required|string|max:255',
            'jenis_tugas' => 'required|string',
            'tugas' => 'required|string',
            'detail_tugas' => 'nullable|string|max:65000',
            'redaksi_pembuka' => 'nullable|string|max:2000',
            'penutup' => 'nullable|string|max:1000',

            'penerima_internal' => 'nullable|array',
            'penerima_internal.*' => 'exists:pengguna,id',
            'penerima_eksternal' => 'nullable|array',
            'penerima_eksternal.*.nama' => 'required_with:penerima_eksternal|string|max:255',
            'penerima_eksternal.*.jabatan' => 'required_with:penerima_eksternal|string|max:255',

            'penandatangan' => 'required|exists:pengguna,id',
            'status_penerima' => 'nullable',
            'waktu_mulai' => 'nullable|date',
            'waktu_selesai' => 'nullable|date|after_or_equal:waktu_mulai',
            'tempat' => 'nullable|string|max:255',
            'tahun' => 'required|integer|digits:4',
            'bulan' => 'required|string|max:10',
            'semester' => 'required|string|in:Ganjil,Genap',

            'nama_pembuat' => 'required|string',
            'asal_surat' => 'required|exists:pengguna,id',

            'nomor' => ['required', 'string', Rule::unique('tugas_header')->ignore($tugasId)],
            'no_surat_manual' => 'nullable|string|max:255',
            'tembusan'           => 'nullable',
            'tembusan_formatted' => 'nullable|string|max:10000',
        ];
    }

    protected function prepareForValidation(): void
    {
        // ✅ Lakukan hal yang sama untuk normalisasi tembusan
        if ($this->has('tembusan')) {
            $this->merge([
                'tembusan' => $this->normalizeTembusanLines($this->input('tembusan')),
            ]);
        }
    }
    
    // Salin juga private method normalizeTembusanLines() ke sini
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