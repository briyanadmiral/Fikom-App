<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKeputusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sudah dibatasi di route pakai gate `can:create, KeputusanHeader`
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor'              => ['nullable', 'string', 'max:100', 'unique:keputusan_header,nomor'],
            'tanggal_asli'       => ['required', 'date'],
            'tentang'            => ['required', 'string', 'max:500'],
            'penandatangan'      => ['nullable', 'integer', 'exists:pengguna,id'],

            // daftar & diktum
            'menimbang'          => ['nullable', 'array'],
            'menimbang.*'        => ['nullable', 'string', 'max:500'],
            'mengingat'          => ['nullable', 'array'],
            'mengingat.*'        => ['nullable', 'string', 'max:500'],

            'menetapkan'         => ['nullable', 'array'],
            'menetapkan.*.judul' => ['nullable', 'string', 'max:50'],
            'menetapkan.*.isi'   => ['nullable', 'string'], // HTML dipurify di layer selanjutnya

            // tembusan dari Tagify (CSV/string bebas; distandardkan di prepare)
            'tembusan'           => ['nullable', 'string'],
            'tembusan_formatted' => ['nullable', 'string'],

            // === PENERIMA (AKTIF) ===
            'penerima_internal'      => ['nullable', 'array'],
            'penerima_internal.*'    => ['integer', 'exists:pengguna,id'],
            'penerima_eksternal'     => ['nullable', 'array'],
            // Tagify bisa kirim array of strings ATAU array of objects {value: "..."}; kita longgarkan:
            'penerima_eksternal.*'   => ['nullable'], // dinormalisasi di prepareForValidation
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi tembusan → string (kalau form mengirim array)
        $tembusan = $this->input('tembusan');
        if (is_array($tembusan)) {
            $tembusan = implode(', ', array_filter(array_map('trim', $tembusan)));
        }

        // Trim isi menimbang/mengingat
        $menimbang = array_values(array_filter(
            array_map('trim', (array) $this->input('menimbang', [])),
            fn ($v) => $v !== ''
        ));
        $mengingat = array_values(array_filter(
            array_map('trim', (array) $this->input('mengingat', [])),
            fn ($v) => $v !== ''
        ));

        // Samakan key diktum agar pakai 'isi'
        $menetapkan = (array) $this->input('menetapkan', []);
        $menetapkan = array_values(array_filter(array_map(function ($d) {
            $judul = trim($d['judul'] ?? '');
            $isi   = $d['isi'] ?? ($d['konten'] ?? ''); // fallback front-end lama
            return ($judul === '' && trim(strip_tags($isi)) === '')
                ? null
                : ['judul' => $judul, 'isi' => $isi];
        }, $menetapkan)));

        // === Normalisasi penerima eksternal: jadikan array string bersih ===
        $eksternalRaw = (array) $this->input('penerima_eksternal', []);
        $penerimaEksternal = array_values(array_filter(array_map(function ($item) {
            if (is_array($item)) {
                // Tagify umum: {value: "..."} / {name: "..."} / {text: "..."}
                $val = trim((string)($item['value'] ?? $item['name'] ?? $item['text'] ?? ''));
            } else {
                $val = trim((string)$item);
            }
            return $val === '' ? null : $val;
        }, $eksternalRaw)));

        // Internal: biarkan array of user_id (sudah divalidasi exists)
        $penerimaInternal = (array) $this->input('penerima_internal', []);
        $penerimaInternal = array_values(array_unique(array_map('intval', $penerimaInternal))); // dedup & int-cast

        $this->merge([
            'tembusan'           => $tembusan,
            'menimbang'          => $menimbang,
            'mengingat'          => $mengingat,
            'menetapkan'         => $menetapkan,
            'penerima_internal'  => $penerimaInternal,
            'penerima_eksternal' => $penerimaEksternal, // akan di-cast ke JSON oleh model
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
