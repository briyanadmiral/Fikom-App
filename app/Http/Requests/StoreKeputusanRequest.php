<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'nomor'               => ['nullable','string','max:255'],
            'tanggal_asli'        => ['required','date'],
            'tanggal_surat'       => ['nullable','date','after_or_equal:tanggal_asli'],
            'tentang'             => ['required','string','max:255'],

            // penerima internal (opsional)
            'penerima_internal'   => ['nullable','array'],
            'penerima_internal.*' => [
                'integer','distinct',
                Rule::exists('pengguna','id')->where(fn($q) => $q->where('status','aktif')),
            ],

            // penandatangan (opsional)
            'penandatangan'       => [
                'nullable','integer',
                Rule::exists('pengguna','id')->where(fn($q) => $q->where('status','aktif')),
            ],

            // bagian pertimbangan (wajib)
            'menimbang'           => ['required','array','min:1'],
            'menimbang.*'         => ['required','string','max:500'],

            // dasar hukum (wajib)
            'mengingat'           => ['required','array','min:1'],
            'mengingat.*'         => ['required','string','max:500'],

            // diktum (opsional, tapi kalau ada judul/isi harus berpasangan)
            'menetapkan'          => ['nullable','array'],
            'menetapkan.*.judul'  => ['required_with:menetapkan.*.isi','string','max:50'],
            'menetapkan.*.isi'    => ['required_with:menetapkan.*.judul','string'],

            // tembusan disimpan sebagai string (csv) di DB, tapi boleh kirim array di form
            'tembusan'            => ['nullable', 'string'],

            // mode simpan
            'mode'                => ['nullable','in:draft,pending,terkirim'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi penerima_internal (boleh string "11,13" ataupun array)
        $rawPenerima = $this->input('penerima_internal', []);
        if (is_string($rawPenerima)) {
            $rawPenerima = array_filter(array_map('trim', explode(',', $rawPenerima)));
        }
        $penerima = array_values(array_unique(array_map(fn($v)=> (int)$v, (array)$rawPenerima)));

        // Normalisasi tembusan → string csv (DB varchar)
        $tembusan = $this->input('tembusan');
        if (is_array($tembusan)) {
            $tembusan = implode(', ', array_filter(array_map('trim', $tembusan)));
        }

        // Trim isi menimbang/mengingat
        $menimbang = array_values(array_filter(array_map('trim', (array)$this->input('menimbang', [])), fn($v)=>$v!==''));
        $mengingat = array_values(array_filter(array_map('trim', (array)$this->input('mengingat', [])), fn($v)=>$v!==''));

        // Samakan key diktum agar pakai 'isi'
        $menetapkan = (array)$this->input('menetapkan', []);
        $menetapkan = array_values(array_filter(array_map(function ($d) {
            $judul = trim($d['judul'] ?? '');
            $isi   = $d['isi'] ?? ($d['konten'] ?? ''); // fallback kalau front-end lama kirim 'konten'
            return ($judul === '' && trim(strip_tags($isi)) === '') ? null : ['judul'=>$judul,'isi'=>$isi];
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
