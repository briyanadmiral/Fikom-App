<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKeputusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan aturan validasi yang berlaku untuk request.
     */
    public function rules(): array
    {
        return [
            'nomor'               => ['nullable', 'string', 'max:100', 'unique:keputusan_header,nomor,' . $this->surat_keputusan?->id],
            'tanggal_asli'        => ['required', 'date'],
            'tentang'             => ['required', 'string', 'max:65535'],
            'penandatangan'       => ['nullable', 'integer', 'exists:pengguna,id'],
            'penerima_internal'   => ['nullable', 'array'],
            'penerima_internal.*' => ['integer', 'exists:pengguna,id'],
            'tembusan'            => ['nullable', 'string', 'max:1000'],
            'menimbang'           => ['nullable', 'array'],
            'menimbang.*'         => ['nullable', 'string', 'max:1000'],
            'mengingat'           => ['nullable', 'array'],
            'mengingat.*'         => ['nullable', 'string', 'max:1000'],
            'menetapkan'          => ['nullable', 'array'],
            'menetapkan.*.judul'  => ['required_with:menetapkan.*.isi', 'string', 'max:100'],
            'menetapkan.*.isi'    => ['nullable', 'string'],

            // [PERBAIKAN] Tambahkan 'revisi_dan_setujui' ke daftar yang diizinkan
            'mode' => [
                'nullable',
                'string',
                Rule::in(['draft', 'pending', 'terkirim', 'revisi_dan_setujui']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $rawPenerima = $this->input('penerima_internal', []);
        if (is_string($rawPenerima)) {
            $rawPenerima = array_filter(array_map('trim', explode(',', $rawPenerima)));
        }
        $penerima = array_values(array_unique(array_map(fn($v) => (int)$v, (array)$rawPenerima)));

        $tembusan = $this->input('tembusan');
        if (is_array($tembusan)) {
            $tembusan = implode(', ', array_filter(array_map('trim', $tembusan)));
        }

        $menimbang = $this->input('menimbang');
        if (is_array($menimbang)) {
            $menimbang = array_values(array_filter(array_map('trim', $menimbang), fn($v) => $v !== ''));
        }

        $mengingat = $this->input('mengingat');
        if (is_array($mengingat)) {
            $mengingat = array_values(array_filter(array_map('trim', $mengingat), fn($v) => $v !== ''));
        }

        $menetapkan = (array)$this->input('menetapkan', []);
        $menetapkan = array_values(array_filter(array_map(function ($d) {
            $judul = trim($d['judul'] ?? '');
            $isi   = $d['isi'] ?? ($d['konten'] ?? '');
            return ($judul === '' && trim(strip_tags($isi)) === '') ? null : ['judul' => $judul, 'isi' => $isi];
        }, $menetapkan)));

        $this->merge([
            'penerima_internal' => $penerima,
            'tembusan'          => $tembusan,
            'menimbang'         => $menimbang,
            'mengingat'         => $mengingat,
            'menetapkan'        => $menetapkan,
        ]);
    }
    

    public function messages(): array
    {
        return [
            'penandatangan.exists'        => 'Penandatangan tidak ditemukan atau tidak aktif.',
            'penerima_internal.*.exists'  => 'Salah satu penerima internal tidak valid/aktif.',
        ];
    }

}
