<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

/**
 * ✅ REFACTORED: Menggunakan global helpers untuk sanitasi
 */
class StoreTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // === ID utama yang dipakai ===
            'pembuat_id' => ['required', 'integer', 'exists:pengguna,id'],
            'asal_surat_id' => ['required', 'integer', 'exists:pengguna,id'],
            'penandatangan_id' => ['required', 'integer', 'exists:pengguna,id'],

            'klasifikasi_surat_id' => ['required', 'integer', 'exists:klasifikasi_surat,id'],
            'nama_umum' => ['required', 'string', 'max:255'],

            'tanggal_surat' => ['required', 'date'],
            'jenis_tugas' => ['required', 'string', 'max:100'],
            'tugas' => ['required', 'string', 'max:500'],
            'detail_tugas' => ['nullable', 'string', 'max:65000'],
            'redaksi_pembuka' => ['nullable', 'string', 'max:2000'],
            'penutup' => ['nullable', 'string', 'max:1000'],

            'penerima_internal' => ['sometimes', 'array'],
            'penerima_internal.*' => ['integer', 'exists:pengguna,id'],

            'penerima_eksternal' => ['sometimes', 'array'],
            'penerima_eksternal.*.nama' => ['required_with:penerima_eksternal', 'string', 'max:255'],
            'penerima_eksternal.*.jabatan' => ['required_with:penerima_eksternal', 'string', 'max:255'],
            'penerima_eksternal.*.instansi' => ['nullable', 'string', 'max:255'],

            'status_penerima' => ['sometimes', 'nullable', 'string', 'in:dosen,tendik,mahasiswa'],

            'tahun' => ['required', 'integer', 'digits:4', 'min:2000', 'max:2100'],
            'semester' => ['required', 'string', 'in:Ganjil,Genap'],

            'bulan' => ['required', 'string', 'regex:/^(I{1,3}|IV|V|VI|VII|VIII|IX|X|XI|XII|1[0-2]|[1-9])$/i'],

            'nomor' => ['nullable', 'string', 'max:100', Rule::unique('tugas_header', 'nomor')->whereNull('deleted_at')],
            'no_surat_manual' => ['nullable', 'string', 'max:100', Rule::unique('tugas_header', 'nomor')->whereNull('deleted_at')],

            'nama_pembuat' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],
            'asal_surat' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],
            'penandatangan' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],

            'waktu_mulai' => ['nullable', 'date'],
            'waktu_selesai' => ['nullable', 'date', 'after_or_equal:waktu_mulai'],

            'tempat' => ['required', 'string', 'max:255'],

            'tembusan' => ['nullable'],
            'tembusan_formatted' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // 1. Normalisasi string 'null' → null
        foreach (['pembuat_id', 'asal_surat_id', 'penandatangan_id'] as $k) {
            if ($this->input($k) === 'null') {
                $this->merge([$k => null]);
            }
        }

        // 2. Pastikan ID utama terisi dengan fallback
        $this->merge([
            'pembuat_id' => $this->input('pembuat_id') ?: $this->input('nama_pembuat'),
            'asal_surat_id' => $this->input('asal_surat_id') ?? $this->input('asal_surat'),
            'penandatangan_id' => $this->input('penandatangan_id') ?: $this->input('penandatangan'),
        ]);

        // 3. Duplikasi ke key tanpa _id
        $this->merge([
            'nama_pembuat' => $this->input('pembuat_id'),
            'asal_surat' => $this->input('asal_surat_id'),
            'penandatangan' => $this->input('penandatangan_id'),
        ]);

        // 4. Sanitasi text fields dengan global helper
        $textFields = [
            'nama_umum' => 255,
            'jenis_tugas' => 100,
            'tugas' => 500,
            'redaksi_pembuka' => 2000,
            'penutup' => 1000,
            'tempat' => 255,
            'bulan' => 50,
            'status_penerima' => 50,
            'nomor' => 100,
            'no_surat_manual' => 100,
        ];

        foreach ($textFields as $field => $maxLength) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([
                    $field => sanitize_input($this->input($field), $maxLength),
                ]);
            }
        }

        // 5. Normalisasi status_penerima atau set null
        if ($this->filled('status_penerima')) {
            $normalized = mb_strtolower(trim((string) $this->input('status_penerima')));
            if (!in_array($normalized, ['dosen', 'tendik', 'mahasiswa'], true)) {
                $normalized = null;
            }
            $this->merge(['status_penerima' => $normalized]);
        } else {
            $this->merge(['status_penerima' => null]);
        }

        // 6. Sanitasi detail_tugas dengan global helper
        if ($this->has('detail_tugas') && !empty($this->input('detail_tugas'))) {
            $this->merge([
                'detail_tugas' => sanitize_html_limited($this->input('detail_tugas')),
            ]);
        }

        // 7. Sanitasi penerima eksternal dengan helper
        if ($this->has('penerima_eksternal') && is_array($this->input('penerima_eksternal'))) {
            $sanitized = [];
            foreach ($this->input('penerima_eksternal') as $penerima) {
                $sanitized[] = [
                    'nama' => isset($penerima['nama'])
                        ? sanitize_input($penerima['nama'], 255)
                        : null,
                    'jabatan' => isset($penerima['jabatan'])
                        ? sanitize_input($penerima['jabatan'], 255)
                        : null,
                    'instansi' => isset($penerima['instansi'])
                        ? sanitize_input($penerima['instansi'], 255)
                        : null,
                ];
            }
            $this->merge(['penerima_eksternal' => $sanitized]);
        }

        // 8. Normalisasi tanggal_surat
        if ($this->has('tanggal_surat')) {
            $raw = $this->input('tanggal_surat');
            try {
                $date = \Carbon\Carbon::parse($raw)->format('Y-m-d');
            } catch (\Exception $e) {
                // ✅ IMPROVEMENT: Log date parsing errors
                Log::warning('Invalid date format in StoreTugasRequest', [
                    'field' => 'tanggal_surat',
                    'value' => substr($raw, 0, 50),
                    'user_id' => auth()->id(),
                ]);
                $date = now()->format('Y-m-d');
            }
            $this->merge(['tanggal_surat' => $date]);
        } else {
            $this->merge(['tanggal_surat' => now()->format('Y-m-d')]);
        }

        // 9. Normalisasi waktu dengan fallback null
        foreach (['waktu_mulai', 'waktu_selesai'] as $k) {
            if ($this->filled($k)) {
                try {
                    $parsed = \Carbon\Carbon::parse($this->input($k));
                    $this->merge([$k => $parsed->format('Y-m-d H:i:00')]);
                } catch (\Exception $e) {
                    // ✅ IMPROVEMENT: Log time parsing errors
                    Log::warning('Invalid time format in StoreTugasRequest', [
                        'field' => $k,
                        'value' => substr($this->input($k), 0, 50),
                        'user_id' => auth()->id(),
                    ]);
                    $this->merge([$k => null]);
                }
            } else {
                $this->merge([$k => null]);
            }
        }

        // 10. Validasi ID dengan helper
        $idFields = ['pembuat_id', 'asal_surat_id', 'penandatangan_id', 'klasifikasi_surat_id', 'nama_pembuat', 'asal_surat', 'penandatangan'];

        foreach ($idFields as $idField) {
            if ($this->filled($idField)) {
                $validated = validate_integer_id($this->input($idField));
                $this->merge([$idField => $validated]);
            }
        }

        // Tahun dengan filter_var
        if ($this->filled('tahun')) {
            $tahun = filter_var($this->input('tahun'), FILTER_VALIDATE_INT);
            if ($tahun !== false && $tahun >= 2000 && $tahun <= 2100) {
                $this->merge(['tahun' => $tahun]);
            }
        }

        // 11. Validasi array penerima_internal
        if (is_array($this->input('penerima_internal'))) {
            $validated = collect($this->input('penerima_internal'))
                ->map(fn($v) => validate_integer_id($v))
                ->filter(fn($v) => $v !== null)
                ->values()
                ->all();

            $this->merge(['penerima_internal' => $validated]);
        }

        // 12. Normalisasi tembusan dengan helper
        if ($this->has('tembusan')) {
            $this->merge([
                'tembusan' => $this->normalizeTembusanLines($this->input('tembusan')),
            ]);
        }
    }

    /**
     * Normalisasi tembusan dengan global helper
     */
    private function normalizeTembusanLines($raw): string
    {
        $items = [];

        if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            }
        }

        if (is_array($raw)) {
            foreach ($raw as $v) {
                if (is_array($v) && isset($v['value'])) {
                    $items[] = sanitize_input((string) $v['value'], 200);
                } elseif (is_string($v)) {
                    $items[] = sanitize_input($v, 200);
                }
            }
        } elseif (is_string($raw)) {
            $parts = preg_split("/[\n,]+/u", $raw) ?: [];
            foreach ($parts as $p) {
                $items[] = sanitize_input($p, 200);
            }
        }

        $norm = collect($items)
            ->map(fn($s) => trim((string) $s))
            ->filter()
            ->unique(fn($s) => mb_strtolower($s))
            ->values()
            ->all();

        return implode("\n", $norm);
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'asal_surat_id.required' => 'Asal surat harus diisi',
            'asal_surat_id.exists' => 'Asal surat tidak valid',
            'status_penerima.in' => 'Status penerima harus salah satu: Dosen, Tendik, atau Mahasiswa',
            'tahun.min' => 'Tahun tidak valid (minimum 2000)',
            'tahun.max' => 'Tahun tidak valid (maksimum 2100)',
            'waktu_mulai.date' => 'Format waktu mulai tidak valid',
            'waktu_selesai.date' => 'Format waktu selesai tidak valid',
            'waktu_selesai.after_or_equal' => 'Waktu selesai harus setelah atau sama dengan waktu mulai',
            'nomor.unique' => 'Nomor surat sudah digunakan',
            'no_surat_manual.unique' => 'Nomor surat manual sudah digunakan',
            'bulan.regex' => 'Bulan harus 1-12 atau I-XII',
        ];
    }
}
