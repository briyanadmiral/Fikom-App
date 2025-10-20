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
        // ✅ FIXED: Sanitize tembusan
        $tembusan = $this->input('tembusan');
        if (is_array($tembusan)) {
            $tembusan = implode(
                ', ',
                array_filter(
                    array_map(function ($item) {
                        return sanitize_input(trim($item), 255);
                    }, $tembusan),
                ),
            );
        } elseif (is_string($tembusan)) {
            $tembusan = sanitize_input($tembusan, 1000);
        }

        // ✅ FIXED: Sanitize menimbang/mengingat
        $menimbang = array_values(
            array_filter(
                array_map(function ($item) {
                    $cleaned = sanitize_input(trim($item), 500);
                    return $cleaned !== '' ? $cleaned : null;
                }, (array) $this->input('menimbang', [])),
                fn($v) => $v !== null,
            ),
        );

        $mengingat = array_values(
            array_filter(
                array_map(function ($item) {
                    $cleaned = sanitize_input(trim($item), 500);
                    return $cleaned !== '' ? $cleaned : null;
                }, (array) $this->input('mengingat', [])),
                fn($v) => $v !== null,
            ),
        );

        // Diktum - No sanitization for HTML content (purified in service)
        $menetapkan = (array) $this->input('menetapkan', []);
        $menetapkan = array_values(
            array_filter(
                array_map(function ($d) {
                    $judul = trim($d['judul'] ?? '');
                    $isi = $d['isi'] ?? ($d['konten'] ?? '');
                    return $judul === '' && trim(strip_tags($isi)) === '' ? null : ['judul' => $judul, 'isi' => $isi];
                }, $menetapkan),
            ),
        );

        // ✅ FIXED: Sanitize penerima eksternal
        $eksternalRaw = (array) $this->input('penerima_eksternal', []);
        $penerimaEksternal = array_values(
            array_filter(
                array_map(function ($item) {
                    if (is_array($item)) {
                        $val = trim((string) ($item['value'] ?? ($item['name'] ?? ($item['text'] ?? ''))));
                    } else {
                        $val = trim((string) $item);
                    }

                    $val = sanitize_input($val, 255);

                    return $val === '' || $val === null ? null : $val;
                }, $eksternalRaw),
            ),
        );

        // ✅ FIXED: Validate internal recipients
        $penerimaInternal = (array) $this->input('penerima_internal', []);
        $penerimaInternal = array_values(
            array_unique(
                array_filter(
                    array_map(function ($id) {
                        $validated = validate_integer_id($id);
                        return $validated !== null ? $validated : null;
                    }, $penerimaInternal),
                ),
            ),
        );

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
