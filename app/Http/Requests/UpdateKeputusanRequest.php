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
        $keputusanId = $this->route('surat_keputusan')->id ?? null;

        return [
            'nomor' => 'nullable|string|max:100|unique:keputusan_header,nomor,' . $keputusanId,
            'tanggal_surat' => 'required|date',
            'kota_penetapan' => 'nullable|string|max:100', // ✅ BARU
            'tahun' => 'nullable|integer|min:2020|max:2100', // ✅ BARU
            'tentang' => 'required|string|max:500',
            'judul_penetapan' => 'nullable|string|max:500', // ✅ BARU
            'penandatangan' => 'nullable|exists:pengguna,id',
            'npp_penandatangan' => 'nullable|string|max:50', // ✅ BARU
            'menimbang' => 'required|array|min:1',
            'menimbang.*' => 'required|string|max:1000',
            'mengingat' => 'required|array|min:1',
            'mengingat.*' => 'required|string|max:1000',
            'menetapkan' => 'required|array|min:1',
            'menetapkan.*.judul' => 'required|string|max:50',
            'menetapkan.*.isi' => 'required|string',
            'penerima_internal' => 'nullable|array',
            'penerima_internal.*' => 'exists:pengguna,id',
            'penerima_eksternal' => 'nullable|array',
            'penerima_eksternal.*' => 'string|max:255',
            'tembusan' => 'nullable|string',
            'mode' => 'nullable|in:draft,pending,terkirim',
        ];
    }

    public function messages(): array
    {
        return [
            'judul_penetapan.max' => 'Judul penetapan maksimal 500 karakter.',
            'kota_penetapan.max' => 'Nama kota maksimal 100 karakter.',
            'tahun.min' => 'Tahun tidak valid (minimal 2020).',
            'tahun.max' => 'Tahun tidak valid (maksimal 2100).',
            'npp_penandatangan.max' => 'NPP maksimal 50 karakter.',
        ];
    }

    // Tambahkan messages() yang sama seperti di Store

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

}
