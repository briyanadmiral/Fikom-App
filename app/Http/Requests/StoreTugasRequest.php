<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // === ID yang dipakai ===
            'pembuat_id' => ['required', 'integer', 'exists:pengguna,id'],
            'asal_surat_id' => ['required', 'integer', 'exists:pengguna,id'],
            'penandatangan_id' => ['required', 'integer', 'exists:pengguna,id'],

            'klasifikasi_surat_id' => ['required', 'integer', 'exists:klasifikasi_surat,id'],
            'nama_umum' => ['required', 'string', 'max:255'],

            'tanggal_surat' => ['required', 'date'],
            'jenis_tugas' => ['required', 'string'],
            'tugas' => ['required', 'string'],
            'detail_tugas' => ['nullable', 'string', 'max:65000'],
            'redaksi_pembuka' => ['nullable', 'string', 'max:2000'],
            'penutup' => ['nullable', 'string', 'max:1000'],

            'penerima_internal' => ['sometimes', 'array'],
            'penerima_internal.*' => ['integer', 'exists:pengguna,id'],

            'penerima_eksternal' => ['sometimes', 'array'],
            'penerima_eksternal.*.nama' => ['required_with:penerima_eksternal', 'string', 'max:255'],
            'penerima_eksternal.*.jabatan' => ['required_with:penerima_eksternal', 'string', 'max:255'],
            'penerima_eksternal.*.instansi' => ['nullable', 'string', 'max:255'],

            'status_penerima' => ['nullable', 'string', 'max:50'],

            'tahun' => ['required', 'integer', 'digits:4'],
            'semester' => ['required', 'string', 'in:Ganjil,Genap'],
            'bulan' => ['required', 'string', 'max:10'],

            // Nomor unik saat store (tanpa ignore)
            'nomor' => ['required', 'string', Rule::unique('tugas_header', 'nomor')],
            'no_surat_manual' => ['nullable', 'string', Rule::unique('tugas_header', 'nomor')],

            // Field tanpa _id dibiarkan opsional & bertipe ID juga (akan diisi di prepare)
            'nama_pembuat' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],
            'asal_surat' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],
            'penandatangan' => ['sometimes', 'nullable', 'integer', 'exists:pengguna,id'],

            'waktu_mulai' => ['required', 'date'],
            'waktu_selesai' => ['required', 'date', 'after_or_equal:waktu_mulai'],
            'tempat' => ['required', 'string', 'max:255'],

            'tembusan' => ['nullable'],
            'tembusan_formatted' => ['nullable', 'string', 'max:10000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi string 'null' → null
        foreach (['pembuat_id', 'asal_surat_id', 'penandatangan_id'] as $k) {
            if ($this->input($k) === 'null') {
                $this->merge([$k => null]);
            }
        }

        // Pastikan ID utama terisi (fallback dari field tanpa _id bila ada)
        $this->merge([
            'pembuat_id' => $this->input('pembuat_id') ?: $this->input('nama_pembuat'),
            'asal_surat_id' => $this->input('asal_surat_id') ?: $this->input('asal_surat'),
            'penandatangan_id' => $this->input('penandatangan_id') ?: $this->input('penandatangan'),
        ]);

        // Duplikasi ke key tanpa _id (untuk kompatibilitas service/model)
        $this->merge([
            'nama_pembuat' => $this->input('pembuat_id'),
            'asal_surat' => $this->input('asal_surat_id'),
            'penandatangan' => $this->input('penandatangan_id'),
        ]);

        // ————— Tambahan untuk tanggal_surat —————
        // Jika user tidak memilih tanggal, isi dengan tanggal sekarang (YYYY-MM-DD)
        if ($this->has('tanggal_surat')) {
            $raw = $this->input('tanggal_surat');
            // Format input bisa "MM/DD/YYYY" atau "YYYY-MM-DD", kita normalisasi ke YYYY-MM-DD
            try {
                $date = \Carbon\Carbon::parse($raw)->format('Y-m-d');
            } catch (\Exception $e) {
                $date = now()->format('Y-m-d');
            }
            $this->merge(['tanggal_surat' => $date]);
        } else {
            // Fallback jika memang tidak dikirim
            $this->merge(['tanggal_surat' => now()->format('Y-m-d')]);
        }

        // Normalisasi tembusan
        if ($this->has('tembusan')) {
            $this->merge([
                'tembusan' => $this->normalizeTembusanLines($this->input('tembusan')),
            ]);
        }
    }

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
                    $items[] = (string) $v['value'];
                } elseif (is_string($v)) {
                    $items[] = $v;
                }
            }
        } elseif (is_string($raw)) {
            $parts = preg_split("/[\n,]+/u", $raw) ?: [];
            foreach ($parts as $p) {
                $items[] = $p;
            }
        }

        $norm = collect($items)->map(fn($s) => trim((string) $s))->filter()->unique(fn($s) => mb_strtolower($s))->values()->all();

        return implode("\n", $norm);
    }
}
