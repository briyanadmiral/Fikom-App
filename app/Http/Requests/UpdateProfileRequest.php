<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User harus login (middleware auth sudah menjaga), tapi aman kita cek lagi
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi input: email lower-case & trim, NPP hanya digit lalu format sederhana
        $email = strtolower(trim((string) $this->input('email')));
        $nppRaw = $this->input('npp');

        // Ambil angka saja
        $digits = is_string($nppRaw) ? preg_replace('/\D+/', '', $nppRaw) : null;

        // Format NPP: jika 11 digit -> 3.1.4.3, selain itu kelompok per 3 digit (fallback)
        $npp = null;
        if ($digits !== null && $digits !== '') {
            if (strlen($digits) === 11) {
                $npp = substr($digits, 0, 3).'.'.substr($digits, 3, 1).'.'.substr($digits, 4, 4).'.'.substr($digits, 8, 3);
            } else {
                $npp = implode('.', str_split($digits, 3));
            }
        }

        $this->merge([
            'email' => $email,
            'npp'   => $npp,
        ]);
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                // Tabel Anda: 'pengguna' (bukan 'users'), abaikan soft-deleted & record sendiri
                Rule::unique('pengguna', 'email')
                    ->ignore($userId)
                    ->where(fn($q) => $q->whereNull('deleted_at')),
            ],
            'npp' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('pengguna', 'npp')
                    ->ignore($userId)
                    ->where(fn($q) => $q->whereNull('deleted_at')),
            ],
            'jabatan' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan.',
            'npp.unique'   => 'NPP sudah digunakan.',
        ];
    }
}
