<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKeputusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        // ✅ FIX: Hapus unique check untuk edit juga
        'nomor' => 'required|string|max:100',
        
        'tanggal_surat' => 'required|date',
        'tentang' => 'required|string|max:500',
        'penandatangan' => 'nullable|exists:pengguna,id',
        
        'menimbang' => 'nullable|array',
        'menimbang.*' => 'nullable|string',
        
        'mengingat' => 'nullable|array',
        'mengingat.*' => 'nullable|string',
        
        'menetapkan' => 'nullable|array',
        'menetapkan.*.judul' => 'nullable|string|max:50',
        'menetapkan.*.isi' => 'nullable|string',
        
        'tembusan' => 'nullable|string',
        'penerima_internal' => 'nullable|array',
        'penerima_internal.*' => 'exists:pengguna,id',
        'penerima_eksternal' => 'nullable|array',
    ];
}


    protected function prepareForValidation(): void
    {
        // ✅ Sama seperti StoreKeputusanRequest
        // (copy dari StoreKeputusanRequest::prepareForValidation)
        
        $tembusan = $this->input('tembusan');
        if (is_array($tembusan)) {
            $tembusan = implode(', ', array_filter(array_map('trim', $tembusan)));
        }

        $menimbang = array_values(array_filter(
            array_map('trim', (array) $this->input('menimbang', [])),
            fn ($v) => $v !== ''
        ));
        $mengingat = array_values(array_filter(
            array_map('trim', (array) $this->input('mengingat', [])),
            fn ($v) => $v !== ''
        ));

        $menetapkan = (array) $this->input('menetapkan', []);
        $menetapkan = array_values(array_filter(array_map(function ($d) {
            $judul = trim($d['judul'] ?? '');
            $isi = $d['isi'] ?? ($d['konten'] ?? '');
            return ($judul === '' && trim(strip_tags($isi)) === '')
                ? null
                : ['judul' => $judul, 'isi' => $isi];
        }, $menetapkan)));

        $eksternalRaw = (array) $this->input('penerima_eksternal', []);
        $penerimaEksternal = array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $val = trim((string)($item['value'] ?? $item['name'] ?? $item['text'] ?? ''));
            } else {
                $val = trim((string)$item);
            }
            return $val === '' ? null : $val;
        }, $eksternalRaw)));

        $penerimaInternal = (array) $this->input('penerima_internal', []);
        $penerimaInternal = array_values(array_unique(array_map('intval', $penerimaInternal)));

        $this->merge([
            'tembusan' => $tembusan,
            'menimbang' => $menimbang,
            'mengingat' => $mengingat,
            'menetapkan' => $menetapkan,
            'penerima_internal' => $penerimaInternal,
            'penerima_eksternal' => $penerimaEksternal,
        ]);
    }

    public function messages(): array
    {
        return [
            'tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid.',
            'tentang.required' => 'Judul keputusan (Tentang) wajib diisi.',
            'penandatangan.exists' => 'Penandatangan tidak ditemukan atau tidak aktif.',
            'penerima_internal.*.exists' => 'Salah satu penerima internal tidak ditemukan.',
        ];
    }
}
