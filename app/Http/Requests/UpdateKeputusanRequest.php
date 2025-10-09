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

    public function rules(): array
    {
        $id = $this->route('surat_keputusan')?->id ?? $this->route('keputusan')?->id;

        return [
            'nomor'              => ['nullable', 'string', 'max:100', Rule::unique('keputusan_header', 'nomor')->ignore($id)],
            'tanggal_asli'       => ['required', 'date'],
            'tentang'            => ['required', 'string', 'max:500'],
            'penandatangan'      => ['nullable', 'integer', 'exists:pengguna,id'],

            'menimbang'          => ['nullable', 'array'],
            'menimbang.*'        => ['nullable', 'string', 'max:500'],
            'mengingat'          => ['nullable', 'array'],
            'mengingat.*'        => ['nullable', 'string', 'max:500'],

            'menetapkan'         => ['nullable', 'array'],
            'menetapkan.*.judul' => ['nullable', 'string', 'max:50'],
            'menetapkan.*.isi'   => ['nullable', 'string'],

            'tembusan'           => ['nullable', 'string'],
            'tembusan_formatted' => ['nullable', 'string'],

            // === PENERIMA (AKTIF) ===
            'penerima_internal'      => ['nullable', 'array'],
            'penerima_internal.*'    => ['integer', 'exists:pengguna,id'],
            'penerima_eksternal'     => ['nullable', 'array'],
            'penerima_eksternal.*'   => ['nullable'], // dinormalisasi di prepare
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi tembusan (array → CSV/string)
        $tembusan = $this->input('tembusan');
        if (is_array($tembusan)) {
            $tembusan = implode(', ', array_filter(array_map('trim', $tembusan)));
        }

        // Rapikan daftar
        $menimbang = $this->input('menimbang');
        if (is_array($menimbang)) {
            $menimbang = array_values(array_filter(array_map('trim', $menimbang), fn($v) => $v !== ''));
        }

        $mengingat = $this->input('mengingat');
        if (is_array($mengingat)) {
            $mengingat = array_values(array_filter(array_map('trim', $mengingat), fn($v) => $v !== ''));
        }

        // Samakan key diktum agar pakai 'isi'
        $menetapkan = (array) $this->input('menetapkan', []);
        $menetapkan = array_values(array_filter(array_map(function ($d) {
            $judul = trim($d['judul'] ?? '');
            $isi   = $d['isi'] ?? ($d['konten'] ?? '');
            return ($judul === '' && trim(strip_tags($isi)) === '')
                ? null
                : ['judul' => $judul, 'isi' => $isi];
        }, $menetapkan)));

        // === Normalisasi penerima eksternal: array string bersih ===
        $eksternalRaw = (array) $this->input('penerima_eksternal', []);
        $penerimaEksternal = array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                $val = trim((string)($item['value'] ?? $item['name'] ?? $item['text'] ?? ''));
            } else {
                $val = trim((string)$item);
            }
            return $val === '' ? null : $val;
        }, $eksternalRaw)));

        // Internal
        $penerimaInternal = (array) $this->input('penerima_internal', []);
        $penerimaInternal = array_values(array_unique(array_map('intval', $penerimaInternal)));

        $this->merge([
            'tembusan'           => $tembusan,
            'menimbang'          => $menimbang,
            'mengingat'          => $mengingat,
            'menetapkan'         => $menetapkan,
            'penerima_internal'  => $penerimaInternal,
            'penerima_eksternal' => $penerimaEksternal,
        ]);
    }

    public function messages(): array
    {
        return [
            'penandatangan.exists'      => 'Penandatangan tidak ditemukan atau tidak aktif.',
            'penerima_internal.*.exists'=> 'Salah satu penerima internal tidak ditemukan.',
        ];
    }
}
