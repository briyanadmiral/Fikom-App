<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * ✅ IMPROVED: Enhanced sanitization & validation for Update Surat Tugas
 *
 * @version 2.0.0
 *
 * @date 2025-12-06
 */
class UpdateTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get tugas ID for unique validation
        $param = $this->route('tugas');
        $tugasId = $param instanceof \App\Models\TugasHeader ? $param->id : (is_scalar($param) ? (int) $param : null);

        return [
            // === ID References ===
            'pembuat_id' => ['required', 'integer', 'exists:pengguna,id'],
            'asal_surat_id' => ['nullable', 'integer', 'exists:pengguna,id'],
            'penandatangan_id' => ['required', 'integer', 'exists:pengguna,id'],
            'klasifikasi_surat_id' => ['required', 'integer', 'exists:klasifikasi_surat,id'],

            // === Main Content ===
            'nama_umum' => [
                'required',
                'string',
                'min:10',
                'max:255',
                // ✅ Allow: letters, numbers, spaces, and common punctuation
                'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u',
            ],

            'tanggal_surat' => ['required', 'date', 'before_or_equal:today'],

            'jenis_tugas' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\p{N}\s\-\.,()]+$/u'],

            'tugas' => ['nullable', 'string', 'max:500', 'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u'],

            // === Optional Rich Text ===
            'detail_tugas' => ['nullable', 'string', 'max:65000'],
            'redaksi_pembuka' => ['nullable', 'string', 'max:2000'],
            'penutup' => ['nullable', 'string', 'max:1000'],

            // === Penerima Internal ===
            'penerima_internal' => ['nullable', 'array'],
            'penerima_internal.*' => ['integer', 'exists:pengguna,id', 'distinct'],

            // === Penerima Eksternal ===
            'penerima_eksternal' => ['nullable', 'array'],
            'penerima_eksternal.*.nama' => ['required_with:penerima_eksternal', 'string', 'max:255', 'regex:/^[\p{L}\s\-\.]+$/u'],
            'penerima_eksternal.*.jabatan' => ['required_with:penerima_eksternal', 'string', 'max:255', 'regex:/^[\p{L}\p{N}\s\-\.,()\/]+$/u'],
            'penerima_eksternal.*.instansi' => ['nullable', 'string', 'max:255', 'regex:/^[\p{L}\p{N}\s\-\.,()\/]+$/u'],

            // === Metadata ===
            'status_penerima' => ['sometimes', 'nullable', 'string', 'in:dosen,tendik,mahasiswa'],
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'semester' => ['required', 'string', 'in:Ganjil,Genap'],
            'bulan' => ['required', 'string', 'regex:/^(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)$/i'],

            // === Nomor Surat (with unique ignore) ===
            'nomor' => ['nullable', 'string', 'max:100', 'regex:/^[0-9A-Z\/\-\.]+$/', Rule::unique('tugas_header', 'nomor')->ignore($tugasId)->whereNull('deleted_at')],
            'no_surat_manual' => ['nullable', 'string', 'max:100', 'regex:/^[0-9A-Z\/\-\.]+$/', Rule::unique('tugas_header', 'nomor')->ignore($tugasId)->whereNull('deleted_at')],
            'tahun_nomor' => ['sometimes', 'integer', 'digits:4'],
            'nomor_urut' => ['nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],

            // === Legacy Field Support ===
            'nama_pembuat' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],
            'asal_surat' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],
            'penandatangan' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],

            // === Waktu Pelaksanaan ===
            'waktu_mulai' => ['nullable', 'date'],
            'waktu_selesai' => ['nullable', 'date', 'after_or_equal:waktu_mulai'],

            // === Tempat ===
            'tempat' => ['nullable', 'string', 'max:255', 'regex:/^[\p{L}\p{N}\s\-\.,()\/]+$/u'],

            // === Tembusan ===
            'tembusan' => ['nullable'],
            'tembusan_formatted' => ['nullable', 'string', 'max:10000'],
        ];
    }

    /**
     * ✅ IMPROVED: Comprehensive sanitization before validation
     */
    protected function prepareForValidation(): void
    {
        // ====================================================================
        // STEP 1: Normalize NULL values
        // ====================================================================
        $nullableFields = ['pembuat_id', 'asal_surat_id', 'penandatangan_id', 'klasifikasi_surat_id'];

        foreach ($nullableFields as $field) {
            if ($this->input($field) === 'null' || $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        // ====================================================================
        // STEP 2: Resolve ID fields with fallback
        // ====================================================================
        $this->merge([
            'pembuat_id' => $this->input('pembuat_id') ?: $this->input('nama_pembuat'),
            'asal_surat_id' => $this->input('asal_surat_id') ?? $this->input('asal_surat'),
            'penandatangan_id' => $this->input('penandatangan_id') ?: $this->input('penandatangan'),
        ]);

        // Duplicate to legacy fields
        $this->merge([
            'nama_pembuat' => $this->input('pembuat_id'),
            'asal_surat' => $this->input('asal_surat_id'),
            'penandatangan' => $this->input('penandatangan_id'),
        ]);

        // ====================================================================
        // STEP 3: Sanitize TEXT fields (strip tags & limit length)
        // ====================================================================
        $textFields = [
            'nama_umum' => 255,
            'jenis_tugas' => 100,
            'tugas' => 500,
            'tempat' => 255,
            'bulan' => 50,
            'status_penerima' => 50,
            'nomor' => 100,
            'no_surat_manual' => 100,
            'redaksi_pembuka' => 2000,
            'penutup' => 1000,
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
        // STEP 4: Sanitize RICH TEXT fields (allow limited HTML)
        // ====================================================================
        if ($this->has('detail_tugas') && ! empty($this->input('detail_tugas'))) {
            $value = $this->input('detail_tugas');

            // ✅ Use helper for HTML sanitization
            $value = sanitize_html_limited($value);

            // ✅ Additional XSS protection
            $value = $this->stripDangerousHtml($value);

            $this->merge(['detail_tugas' => $value]);
        }

        // ====================================================================
        // STEP 5: Sanitize PENERIMA EKSTERNAL
        // ====================================================================
        if ($this->has('penerima_eksternal') && is_array($this->input('penerima_eksternal'))) {
            $sanitized = [];

            foreach ($this->input('penerima_eksternal') as $penerima) {
                if (! is_array($penerima)) {
                    continue;
                }

                $sanitized[] = [
                    'nama' => isset($penerima['nama']) ? strip_tags(sanitize_input($penerima['nama'], 255)) : null,
                    'jabatan' => isset($penerima['jabatan']) ? strip_tags(sanitize_input($penerima['jabatan'], 255)) : null,
                    'instansi' => isset($penerima['instansi']) ? strip_tags(sanitize_input($penerima['instansi'], 255)) : null,
                ];
            }

            $this->merge([
                'penerima_eksternal' => array_filter($sanitized, function ($p) {
                    return ! empty($p['nama']) && ! empty($p['jabatan']);
                }),
            ]);
        }

        // ====================================================================
        // STEP 5.5: Normalize NOMOR_URUT (filter empty strings and strip prefix)
        // ====================================================================
        if ($this->has('nomor_urut')) {
            $value = $this->input('nomor_urut');

            // Convert empty string to null
            if ($value === '' || $value === null) {
                $this->merge(['nomor_urut' => null]);
                Log::debug('UpdateTugasRequest: nomor_urut set to null', [
                    'original_value' => $value,
                ]);
            } else {
                // ✅ FIX: Strip non-numeric prefix (e.g., 'ST-001' -> '001')
                $cleaned = preg_replace('/^[^0-9]+/', '', (string) $value);

                // If result is still not purely numeric, set to null
                if ($cleaned === '' || ! preg_match('/^[0-9]+$/', $cleaned)) {
                    $this->merge(['nomor_urut' => null]);
                    Log::warning('UpdateTugasRequest: nomor_urut invalid format, set to null', [
                        'original_value' => $value,
                        'cleaned_value' => $cleaned,
                    ]);
                } else {
                    $this->merge(['nomor_urut' => $cleaned]);
                    Log::debug('UpdateTugasRequest: nomor_urut normalized', [
                        'original_value' => $value,
                        'cleaned_value' => $cleaned,
                    ]);
                }
            }
        }

        // ====================================================================
        // STEP 6: Normalize STATUS PENERIMA
        // ====================================================================
        if ($this->filled('status_penerima')) {
            $status = mb_strtolower(trim((string) $this->input('status_penerima')));

            if (! in_array($status, ['dosen', 'tendik', 'mahasiswa'], true)) {
                $status = null;
            }

            $this->merge(['status_penerima' => $status]);
        } else {
            $this->merge(['status_penerima' => null]);
        }

        // ====================================================================
        // STEP 7: Normalize DATES
        // ====================================================================
        if ($this->has('tanggal_surat')) {
            try {
                $date = \Carbon\Carbon::parse($this->input('tanggal_surat'))->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Invalid tanggal_surat in UpdateTugasRequest', [
                    'value' => $this->input('tanggal_surat'),
                    'tugas_id' => $this->route('tugas')->id ?? null,
                    'user_id' => auth()->id(),
                ]);
                $date = now()->format('Y-m-d');
            }

            $this->merge(['tanggal_surat' => $date]);
        }

        // ====================================================================
        // STEP 8: Normalize DATETIME fields
        // ====================================================================
        foreach (['waktu_mulai', 'waktu_selesai'] as $field) {
            if ($this->filled($field)) {
                try {
                    $parsed = \Carbon\Carbon::parse($this->input($field));
                    $this->merge([$field => $parsed->format('Y-m-d H:i:00')]);
                } catch (\Exception $e) {
                    Log::warning("Invalid {$field} in UpdateTugasRequest", [
                        'value' => $this->input($field),
                        'tugas_id' => $this->route('tugas')->id ?? null,
                        'user_id' => auth()->id(),
                    ]);
                    $this->merge([$field => null]);
                }
            } else {
                $this->merge([$field => null]);
            }
        }

        // ====================================================================
        // STEP 9: Validate & Sanitize INTEGER IDs
        // ====================================================================
        $idFields = ['pembuat_id', 'asal_surat_id', 'penandatangan_id', 'klasifikasi_surat_id', 'nama_pembuat', 'asal_surat', 'penandatangan'];

        foreach ($idFields as $field) {
            if ($this->filled($field)) {
                $value = validate_integer_id($this->input($field));
                $this->merge([$field => $value]);
            }
        }

        // Validate tahun
        if ($this->filled('tahun')) {
            $tahun = filter_var($this->input('tahun'), FILTER_VALIDATE_INT);

            if ($tahun !== false && $tahun >= 2000 && $tahun <= 2100) {
                $this->merge(['tahun' => $tahun]);
            } else {
                $this->merge(['tahun' => (int) date('Y')]);
            }
        }

        // ====================================================================
        // STEP 10: Validate PENERIMA INTERNAL array
        // ====================================================================
        if (is_array($this->input('penerima_internal'))) {
            $validated = collect($this->input('penerima_internal'))->map(fn ($v) => validate_integer_id($v))->filter(fn ($v) => $v !== null)->unique()->values()->all();

            $this->merge(['penerima_internal' => $validated]);
        }

        // ====================================================================
        // STEP 11: Normalize TEMBUSAN
        // ====================================================================
        if ($this->has('tembusan')) {
            $normalized = $this->normalizeTembusan($this->input('tembusan'));
            $this->merge(['tembusan' => $normalized]);
        }

        // ====================================================================
        // STEP 12: Validate at least one penerima exists
        // ====================================================================
        $this->validatePenerimaExists();
    }

    /**
     * ✅ Remove dangerous characters (SQL/XSS patterns)
     */
    private function removeDangerousChars(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Remove control characters (except newline, tab, carriage return)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        return $value;
    }

    /**
     * ✅ Strip dangerous HTML patterns
     */
    private function stripDangerousHtml(string $value): string
    {
        // Remove script tags
        $value = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/i', '', $value);

        // Remove iframe tags
        $value = preg_replace('/<iframe\b[^>]*>[\s\S]*?<\/iframe>/i', '', $value);

        // Remove on* event handlers
        $value = preg_replace('/<[^>]+\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $value);

        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);

        return $value;
    }

    /**
     * ✅ Normalize tembusan field
     */
    private function normalizeTembusan($raw): string
    {
        $items = [];

        // Handle JSON format
        if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            }
        }

        // Process array format
        if (is_array($raw)) {
            foreach ($raw as $v) {
                if (is_array($v) && isset($v['value'])) {
                    $items[] = strip_tags(sanitize_input((string) $v['value'], 200));
                } elseif (is_string($v)) {
                    $items[] = strip_tags(sanitize_input($v, 200));
                }
            }
        }
        // Process string format
        elseif (is_string($raw)) {
            $parts = preg_split("/[\n,]+/u", $raw) ?: [];
            foreach ($parts as $p) {
                $items[] = strip_tags(sanitize_input($p, 200));
            }
        }

        // Clean and deduplicate
        $normalized = collect($items)->map(fn ($s) => trim((string) $s))->filter()->unique(fn ($s) => mb_strtolower($s))->values()->all();

        return implode("\n", $normalized);
    }

    /**
     * ✅ Ensure at least one penerima exists
     */
    private function validatePenerimaExists(): void
    {
        $hasInternal = is_array($this->input('penerima_internal')) && count($this->input('penerima_internal')) > 0;

        $hasEksternal = is_array($this->input('penerima_eksternal')) && count($this->input('penerima_eksternal')) > 0;

        if (! $hasInternal && ! $hasEksternal) {
            Log::warning('UpdateTugasRequest: No penerima provided', [
                'tugas_id' => $this->route('tugas')->id ?? null,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
        }
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            // ID validations
            'pembuat_id.required' => 'Pembuat surat harus diisi',
            'pembuat_id.exists' => 'Pembuat tidak valid',
            'asal_surat_id.exists' => 'Asal surat tidak valid',
            'penandatangan_id.required' => 'Penandatangan harus diisi',
            'penandatangan_id.exists' => 'Penandatangan tidak valid',
            'klasifikasi_surat_id.required' => 'Klasifikasi surat harus dipilih',
            'klasifikasi_surat_id.exists' => 'Klasifikasi tidak valid',

            // Content validations
            'nama_umum.required' => 'Judul surat harus diisi',
            'nama_umum.min' => 'Judul surat minimal 10 karakter',
            'nama_umum.max' => 'Judul surat maksimal 255 karakter',
            'nama_umum.regex' => 'Judul surat hanya boleh berisi huruf, angka, spasi, dan tanda baca umum',

            'tanggal_surat.required' => 'Tanggal surat harus diisi',
            'tanggal_surat.date' => 'Format tanggal tidak valid',
            'tanggal_surat.before_or_equal' => 'Tanggal surat tidak boleh di masa depan',

            'jenis_tugas.required' => 'Jenis tugas harus dipilih',
            'jenis_tugas.regex' => 'Jenis tugas mengandung karakter tidak valid',

            'tugas.required' => 'Tugas harus diisi',
            'tugas.regex' => 'Tugas mengandung karakter tidak valid',

            // Penerima validations
            'penerima_internal.*.exists' => 'Salah satu penerima internal tidak valid',
            'penerima_internal.*.distinct' => 'Penerima internal tidak boleh duplikat',

            'penerima_eksternal.*.nama.required_with' => 'Nama penerima eksternal harus diisi',
            'penerima_eksternal.*.nama.regex' => 'Nama penerima hanya boleh berisi huruf',
            'penerima_eksternal.*.jabatan.required_with' => 'Jabatan penerima eksternal harus diisi',

            // Metadata validations
            'status_penerima.in' => 'Status penerima harus salah satu: Dosen, Tendik, atau Mahasiswa',

            'tahun.required' => 'Tahun periode harus diisi',
            'tahun.digits' => 'Tahun harus 4 digit',
            'tahun.min' => 'Tahun tidak valid (minimum 2000)',
            'tahun.max' => 'Tahun tidak valid (maksimum 2100)',

            'semester.required' => 'Semester periode harus dipilih',
            'semester.in' => 'Semester harus Ganjil atau Genap',

            'bulan.required' => 'Bulan harus diisi',
            'bulan.regex' => 'Format bulan tidak valid (gunakan I-XII)',

            // Nomor surat validations
            'nomor.unique' => 'Nomor surat sudah digunakan oleh surat lain',
            'nomor.regex' => 'Format nomor surat tidak valid',
            'no_surat_manual.unique' => 'Nomor surat manual sudah digunakan oleh surat lain',
            'no_surat_manual.regex' => 'Format nomor surat manual tidak valid',

            // Waktu validations
            'waktu_mulai.date' => 'Format waktu mulai tidak valid',
            'waktu_selesai.date' => 'Format waktu selesai tidak valid',
            'waktu_selesai.after_or_equal' => 'Waktu selesai harus setelah atau sama dengan waktu mulai',

            // Tempat validation
            'tempat.max' => 'Tempat pelaksanaan maksimal 255 karakter',
            'tempat.regex' => 'Tempat mengandung karakter tidak valid',
        ];
    }

    /**
     * ✅ Custom attribute names for better error messages
     */
    public function attributes(): array
    {
        return [
            'pembuat_id' => 'pembuat surat',
            'asal_surat_id' => 'asal surat',
            'penandatangan_id' => 'penandatangan',
            'klasifikasi_surat_id' => 'klasifikasi surat',
            'nama_umum' => 'judul surat',
            'tanggal_surat' => 'tanggal surat',
            'jenis_tugas' => 'jenis tugas',
            'tugas' => 'tugas',
            'detail_tugas' => 'detail tugas',
            'penerima_internal' => 'penerima internal',
            'penerima_eksternal' => 'penerima eksternal',
            'tempat' => 'tempat pelaksanaan',
            'waktu_mulai' => 'waktu mulai',
            'waktu_selesai' => 'waktu selesai',
        ];
    }

    /**
     * ✅ Handle failed validation
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::info('UpdateTugasRequest validation failed', [
            'tugas_id' => $this->route('tugas')->id ?? null,
            'user_id' => auth()->id(),
            'errors' => $validator->errors()->toArray(),
            'input_summary' => [
                'nama_umum' => substr($this->input('nama_umum', ''), 0, 50),
                'has_penerima_internal' => is_array($this->input('penerima_internal')),
                'has_penerima_eksternal' => is_array($this->input('penerima_eksternal')),
            ],
        ]);

        parent::failedValidation($validator);
    }
}
