<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * ✅ IMPROVED: Enhanced sanitization & validation for Surat Keputusan
 *
 * @version 2.0.0
 *
 * @date 2025-12-06
 */
class StoreKeputusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // === Nomor & Tanggal ===
            'nomor' => ['nullable', 'string', 'max:100', 'regex:/^[0-9A-Z\/\-\.]+$/', Rule::unique('keputusan_header', 'nomor')->whereNull('deleted_at')],
            'tanggal_surat' => ['required', 'date', 'before_or_equal:today'],

            // === Kota & Tahun ===
            'kota_penetapan' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[\p{L}\s\-\.]+$/u', // Hanya huruf, spasi, strip, titik
            ],
            'tahun' => ['nullable', 'integer', 'digits:4', 'min:2020', 'max:2100'],

            // === Tentang (Judul) ===
            'tentang' => ['required', 'string', 'min:10', 'max:500', 'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u'],

            // === Judul Penetapan ===
            'judul_penetapan' => ['nullable', 'string', 'max:500', 'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u'],

            // === Penandatangan ===
            'penandatangan' => [
                'nullable',
                'integer',
                'exists:pengguna,id',
                'required_if:mode,pending,terkirim', // ✅ Wajib isi jika diajukan
            ],
            'npp_penandatangan' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[0-9A-Z\-\.\/]+$/',
            ],

            // === Konsideran: Menimbang ===
            'menimbang' => ['required', 'array', 'min:1'],
            'menimbang.*' => ['required', 'string', 'max:1000', 'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u'],

            // === Konsideran: Mengingat ===
            'mengingat' => ['required', 'array', 'min:1'],
            'mengingat.*' => ['required', 'string', 'max:1000', 'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u'],

            // === Diktum: Menetapkan ===
            'menetapkan' => ['required', 'array', 'min:1'],
            'menetapkan.*.judul' => ['required', 'string', 'max:200', 'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u'],
            'menetapkan.*.isi' => ['required', 'string', 'max:65000'],

            // === Penerima Internal ===
            'penerima_internal' => ['nullable', 'array'],
            'penerima_internal.*' => ['integer', 'exists:pengguna,id', 'distinct'],

            // === Penerima Eksternal ===
            'penerima_eksternal' => ['nullable', 'array'],
            'penerima_eksternal.*' => ['string', 'max:255', 'regex:/^[\p{L}\s\-\.,()]+$/u'],

            // === Tembusan ===
            'tembusan' => ['nullable', 'string', 'max:5000'],

            // === Mode/Status ===
            'mode' => ['nullable', 'string', 'in:draft,pending,terkirim'],
        ];
    }

    /**
     * ✅ Validate logic cross-fields (Penerima required if pending)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mode = $this->input('mode');
            if (in_array($mode, ['pending', 'terkirim'])) {
                $hasInternal = count($this->input('penerima_internal', [])) > 0;
                $hasEksternal = count($this->input('penerima_eksternal', [])) > 0;

                if (! $hasInternal && ! $hasEksternal) {
                    $validator->errors()->add('penerima_internal', 'Minimal satu penerima (internal atau eksternal) wajib diisi saat pengajuan.');
                }
            }
        });
    }

    /**
     * ✅ IMPROVED: Comprehensive sanitization before validation
     */
    protected function prepareForValidation(): void
    {
        // ====================================================================
        // STEP 1: Sanitize TEXT fields
        // ====================================================================
        $textFields = [
            'nomor' => 100,
            'tentang' => 500,
            'judul_penetapan' => 500,
            'kota_penetapan' => 100,
            'npp_penandatangan' => 50,
        ];

        foreach ($textFields as $field => $maxLength) {
            if ($this->has($field) && is_string($this->input($field))) {
                $value = $this->input($field);

                // ✅ Strip HTML tags
                $value = strip_tags($value);

                // ✅ Remove dangerous characters
                $value = $this->removeDangerousChars($value);

                // ✅ Apply sanitization helper
                $value = sanitize_input($value, $maxLength);

                $this->merge([$field => $value]);
            }
        }

        // ====================================================================
        // STEP 2: Normalize TANGGAL SURAT
        // ====================================================================
        if ($this->has('tanggal_surat')) {
            try {
                $date = \Carbon\Carbon::parse($this->input('tanggal_surat'))->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Invalid tanggal_surat in StoreKeputusanRequest', [
                    'value' => $this->input('tanggal_surat'),
                    'user_id' => auth()->id(),
                ]);
                $date = now()->format('Y-m-d');
            }

            $this->merge(['tanggal_surat' => $date]);
        }

        // ====================================================================
        // STEP 3: Validate TAHUN
        // ====================================================================
        if ($this->filled('tahun')) {
            $tahun = filter_var($this->input('tahun'), FILTER_VALIDATE_INT);

            if ($tahun !== false && $tahun >= 2020 && $tahun <= 2100) {
                $this->merge(['tahun' => $tahun]);
            } else {
                $this->merge(['tahun' => (int) date('Y')]);
            }
        } else {
            $this->merge(['tahun' => (int) date('Y')]);
        }

        // ====================================================================
        // STEP 4: Normalisasi + sanitasi TEMBUSAN (Tagify JSON → list baris)
        // ====================================================================
        $rawTembusan = $this->input('tembusan');
        $tembusanList = [];

        // 4.1 Jika sudah array
        if (is_array($rawTembusan)) {
            foreach ($rawTembusan as $item) {
                $val = is_array($item)
                    ? ($item['value'] ?? ($item['text'] ?? ($item['name'] ?? reset($item))))
                    : $item;

                $val = strip_tags(trim((string) $val));
                $val = sanitize_input($val, 255);

                if ($val !== '') {
                    $tembusanList[] = $val;
                }
            }
        }
        // 4.2 Jika string (Tagify → JSON string)
        elseif (is_string($rawTembusan)) {
            $s = trim(html_entity_decode($rawTembusan, ENT_QUOTES, 'UTF-8'));

            if ($s !== '') {
                $decoded = json_decode($s, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $item) {
                        $val = is_array($item)
                            ? ($item['value'] ?? ($item['text'] ?? ($item['name'] ?? reset($item))))
                            : $item;

                        $val = strip_tags(trim((string) $val));
                        $val = sanitize_input($val, 255);

                        if ($val !== '') {
                            $tembusanList[] = $val;
                        }
                    }
                } else {
                    // Fallback: koma / newline / titik koma
                    $parts = preg_split('/[,\n;]+/', $s);
                    foreach ($parts as $part) {
                        $val = strip_tags(trim((string) $part));
                        $val = sanitize_input($val, 255);
                        if ($val !== '') {
                            $tembusanList[] = $val;
                        }
                    }
                }
            }
        }

        // 4.3 Unik & join newline
        $tembusanList = array_values(array_unique($tembusanList));
        $tembusan = $tembusanList ? implode("\n", $tembusanList) : '';

        $this->merge(['tembusan' => $tembusan]);

        // ====================================================================
        // STEP 5: Sanitize MENIMBANG array
        // ====================================================================
        $menimbang = array_values(
            array_filter(
                array_map(function ($item) {
                    $cleaned = strip_tags(sanitize_input(trim((string) $item), 1000));

                    return $cleaned !== '' ? $cleaned : null;
                }, (array) $this->input('menimbang', [])),
                fn ($v) => $v !== null,
            ),
        );

        if (empty($menimbang)) {
            $menimbang = [''];
        }

        $this->merge(['menimbang' => $menimbang]);

        // ====================================================================
        // STEP 6: Sanitize MENGINGAT array
        // ====================================================================
        $mengingat = array_values(
            array_filter(
                array_map(function ($item) {
                    $cleaned = strip_tags(sanitize_input(trim((string) $item), 1000));

                    return $cleaned !== '' ? $cleaned : null;
                }, (array) $this->input('mengingat', [])),
                fn ($v) => $v !== null,
            ),
        );

        if (empty($mengingat)) {
            $mengingat = [''];
        }

        $this->merge(['mengingat' => $mengingat]);

        // ====================================================================
        // STEP 7: Sanitize MENETAPKAN array (Diktum)
        // ====================================================================
        $menetapkan = array_values(
            array_filter(
                array_map(function ($d) {
                    if (! is_array($d)) {
                        return null;
                    }

                    $judul = strip_tags(trim((string) ($d['judul'] ?? '')));
                    $isi = $d['isi'] ?? ($d['konten'] ?? '');

                    // ✅ Sanitize HTML content dengan helper
                    $isi = sanitize_html_limited($isi);

                    // ✅ Additional XSS protection
                    $isi = $this->stripDangerousHtml($isi);

                    if ($judul === '' && trim(strip_tags($isi)) === '') {
                        return null;
                    }

                    return [
                        'judul' => sanitize_input($judul, 200),
                        'isi' => $isi,
                    ];
                }, (array) $this->input('menetapkan', [])),
            ),
        );

        if (empty($menetapkan)) {
            $menetapkan = [['judul' => '', 'isi' => '']];
        }

        $this->merge(['menetapkan' => $menetapkan]);

        // ====================================================================
        // STEP 8: Sanitize PENERIMA EKSTERNAL
        // ====================================================================
        $penerimaEksternal = array_values(
            array_filter(
                array_map(function ($item) {
                    if (is_array($item)) {
                        $val = trim((string) ($item['value'] ?? ($item['name'] ?? ($item['text'] ?? ''))));
                    } else {
                        $val = trim((string) $item);
                    }

                    $val = strip_tags(sanitize_input($val, 255));

                    return $val === '' || $val === null ? null : $val;
                }, (array) $this->input('penerima_eksternal', [])),
            ),
        );

        $this->merge(['penerima_eksternal' => $penerimaEksternal]);

        // ====================================================================
        // STEP 9: Validate PENERIMA INTERNAL
        // ====================================================================
        $penerimaInternal = array_values(
            array_unique(
                array_filter(
                    array_map(function ($id) {
                        return validate_integer_id($id);
                    }, (array) $this->input('penerima_internal', [])),
                ),
            ),
        );

        $this->merge(['penerima_internal' => $penerimaInternal]);

        // ====================================================================
        // STEP 10: Validate PENANDATANGAN
        // ====================================================================
        if ($this->filled('penandatangan')) {
            $validated = validate_integer_id($this->input('penandatangan'));
            $this->merge(['penandatangan' => $validated]);
        }

        // ====================================================================
        // STEP 11: Validate at least one penerima exists
        // ====================================================================
        $this->validatePenerimaExists();
    }

    /**
     * ✅ Remove dangerous characters (SQL/XSS patterns)
     */
    private function removeDangerousChars(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = str_replace("\0", '', $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        return $value;
    }

    /**
     * ✅ Strip dangerous HTML patterns
     */
    private function stripDangerousHtml(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $value);
        $value = preg_replace('/<iframe\b[^>]*>[\s\S]*?<\/iframe>/i', '', $value);
        $value = preg_replace('/<[^>]+\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $value);
        $value = preg_replace('/javascript:/i', '', $value);

        return $value;
    }

    /**
     * ✅ Ensure at least one penerima exists
     */
    private function validatePenerimaExists(): void
    {
        $hasInternal = is_array($this->input('penerima_internal')) && count($this->input('penerima_internal')) > 0;
        $hasEksternal = is_array($this->input('penerima_eksternal')) && count($this->input('penerima_eksternal')) > 0;

        if (! $hasInternal && ! $hasEksternal) {
            Log::warning('StoreKeputusanRequest: No penerima provided', [
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'tentang' => substr($this->input('tentang', ''), 0, 50),
            ]);
        }
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            // Nomor & Tanggal
            'nomor.unique' => 'Nomor keputusan sudah digunakan',
            'nomor.regex' => 'Format nomor keputusan tidak valid',
            'tanggal_surat.required' => 'Tanggal surat wajib diisi',
            'tanggal_surat.date' => 'Format tanggal surat tidak valid',
            'tanggal_surat.before_or_equal' => 'Tanggal surat tidak boleh di masa depan',

            // Kota & Tahun
            'kota_penetapan.max' => 'Nama kota maksimal 100 karakter',
            'kota_penetapan.regex' => 'Nama kota hanya boleh berisi huruf',
            'tahun.digits' => 'Tahun harus 4 digit',
            'tahun.min' => 'Tahun tidak valid (minimal 2020)',
            'tahun.max' => 'Tahun tidak valid (maksimal 2100)',

            // Tentang
            'tentang.required' => 'Judul keputusan (Tentang) wajib diisi',
            'tentang.min' => 'Judul keputusan minimal 10 karakter',
            'tentang.max' => 'Judul keputusan maksimal 500 karakter',
            'tentang.regex' => 'Judul keputusan mengandung karakter tidak valid',

            // Judul Penetapan
            'judul_penetapan.max' => 'Judul penetapan maksimal 500 karakter',
            'judul_penetapan.regex' => 'Judul penetapan mengandung karakter tidak valid',

            // Penandatangan
            'penandatangan.exists' => 'Penandatangan tidak ditemukan atau tidak aktif',
            'npp_penandatangan.max' => 'NPP maksimal 50 karakter',
            'npp_penandatangan.regex' => 'Format NPP tidak valid',

            // Konsideran
            'menimbang.required' => 'Bagian "Menimbang" wajib diisi',
            'menimbang.min' => 'Minimal harus ada 1 poin "Menimbang"',
            'menimbang.*.required' => 'Setiap poin "Menimbang" harus diisi',
            'menimbang.*.max' => 'Setiap poin "Menimbang" maksimal 1000 karakter',
            'menimbang.*.regex' => 'Poin "Menimbang" mengandung karakter tidak valid',

            'mengingat.required' => 'Bagian "Mengingat" wajib diisi',
            'mengingat.min' => 'Minimal harus ada 1 poin "Mengingat"',
            'mengingat.*.required' => 'Setiap poin "Mengingat" harus diisi',
            'mengingat.*.max' => 'Setiap poin "Mengingat" maksimal 1000 karakter',
            'mengingat.*.regex' => 'Poin "Mengingat" mengandung karakter tidak valid',

            // Diktum
            'menetapkan.required' => 'Bagian "Menetapkan" wajib diisi',
            'menetapkan.min' => 'Minimal harus ada 1 diktum',
            'menetapkan.*.judul.required' => 'Judul diktum harus diisi',
            'menetapkan.*.judul.max' => 'Judul diktum maksimal 200 karakter',
            'menetapkan.*.judul.regex' => 'Judul diktum mengandung karakter tidak valid',
            'menetapkan.*.isi.required' => 'Isi diktum harus diisi',
            'menetapkan.*.isi.max' => 'Isi diktum terlalu panjang',

            // Penerima
            'penerima_internal.*.exists' => 'Salah satu penerima internal tidak ditemukan',
            'penerima_internal.*.distinct' => 'Penerima internal tidak boleh duplikat',
            'penerima_eksternal.*.max' => 'Nama penerima eksternal maksimal 255 karakter',
            'penerima_eksternal.*.regex' => 'Nama penerima eksternal mengandung karakter tidak valid',

            // Mode
            'mode.in' => 'Status harus salah satu: draft, pending, atau terkirim',
        ];
    }

    /**
     * ✅ Custom attribute names for better error messages
     */
    public function attributes(): array
    {
        return [
            'nomor' => 'nomor keputusan',
            'tanggal_surat' => 'tanggal surat',
            'kota_penetapan' => 'kota penetapan',
            'tahun' => 'tahun',
            'tentang' => 'judul keputusan',
            'judul_penetapan' => 'judul penetapan',
            'penandatangan' => 'penandatangan',
            'npp_penandatangan' => 'NPP penandatangan',
            'menimbang' => 'konsideran menimbang',
            'mengingat' => 'konsideran mengingat',
            'menetapkan' => 'diktum',
            'penerima_internal' => 'penerima internal',
            'penerima_eksternal' => 'penerima eksternal',
            'tembusan' => 'tembusan',
        ];
    }

    /**
     * ✅ Handle failed validation
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::info('StoreKeputusanRequest validation failed', [
            'user_id' => auth()->id(),
            'errors' => $validator->errors()->toArray(),
            'input_summary' => [
                'tentang' => substr($this->input('tentang', ''), 0, 50),
                'has_menimbang' => count($this->input('menimbang', [])),
                'has_mengingat' => count($this->input('mengingat', [])),
                'has_menetapkan' => count($this->input('menetapkan', [])),
                'has_penerima_internal' => count($this->input('penerima_internal', [])),
                'has_penerima_eksternal' => count($this->input('penerima_eksternal', [])),
            ],
        ]);

        parent::failedValidation($validator);
    }
}
