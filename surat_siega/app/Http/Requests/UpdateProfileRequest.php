<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Enhanced profile update security & validation.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            // === Nama Lengkap ===
            'nama_lengkap' => [
                'required',
                'string',
                'min:3',
                'max:100',

                'regex:/^[\p{L}\s\.\'\'\-,]+$/u',
            ],

            // === Email ===
            'email' => [
                'required',
                'email:rfc,dns',
                'max:100',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i',
                Rule::unique('pengguna', 'email')->ignore($userId)->whereNull('deleted_at'),


                function ($attribute, $value, $fail) {
                    if ($this->isDisposableEmail($value)) {
                        $fail('Email dari layanan disposable/temporary tidak diizinkan.');
                    }
                },
            ],

            // === NPP ===
            'npp' => [
                'nullable',
                'string',
                'max:50',

                'regex:/^[\d\.]+$/',
                Rule::unique('pengguna', 'npp')->ignore($userId)->whereNull('deleted_at'),


                function ($attribute, $value, $fail) {
                    if ($value && ! $this->isValidNPPFormat($value)) {
                        $fail('Format NPP tidak valid. Contoh: 123.1.4567.890');
                    }
                },
            ],

            // === Jabatan ===
            'jabatan' => [
                'nullable',
                'string',
                'max:100',

                'regex:/^[\p{L}\p{N}\s\-\.,()\/]+$/u',
            ],

            // === Optional: No Telepon ===
            'no_telepon' => [
                'nullable',
                'string',
                'max:20',

                'regex:/^(\+62|62|0)[0-9\s\-\(\)]+$/',
            ],

            // === Optional: Alamat ===
            'alamat' => ['nullable', 'string', 'max:500', 'regex:/^[\p{L}\p{N}\s\-\.,\/()]+$/u'],
        ];
    }

    /**
     * Comprehensive sanitization before validation.
     */
    protected function prepareForValidation(): void
    {
        // ====================================================================
        // STEP 1: Sanitize NAMA LENGKAP
        // ====================================================================
        if ($this->has('nama_lengkap')) {
            $nama = $this->input('nama_lengkap');

            // Strip HTML tags
            $nama = strip_tags($nama);

            // Remove dangerous characters
            $nama = $this->removeDangerousChars($nama);

            // Normalize spaces (multiple spaces → single space)
            $nama = preg_replace('/\s+/', ' ', $nama);

            // Trim and capitalize each word
            $nama = trim($nama);
            $nama = mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8');

            // Apply sanitization helper
            $nama = sanitize_input($nama, 100);

            $this->merge(['nama_lengkap' => $nama]);
        }

        // ====================================================================
        // STEP 2: Normalize EMAIL
        // ====================================================================
        if ($this->has('email')) {
            $email = $this->input('email');

            // Lowercase and trim
            $email = strtolower(trim($email));

            // Remove whitespace
            $email = preg_replace('/\s+/', '', $email);

            // Basic sanitization (remove dangerous chars)
            $email = filter_var($email, FILTER_SANITIZE_EMAIL);

            $this->merge(['email' => $email]);
        }

        // ====================================================================
        // STEP 3: Format NPP
        // ====================================================================
        if ($this->has('npp')) {
            $nppRaw = $this->input('npp');

            if (empty($nppRaw)) {
                $this->merge(['npp' => null]);
            } else {
                // Extract digits only
                $digits = preg_replace('/\D+/', '', (string) $nppRaw);

                // Format NPP based on length
                $npp = $this->formatNPP($digits);

                $this->merge(['npp' => $npp]);
            }
        }

        // ====================================================================
        // STEP 4: Sanitize JABATAN
        // ====================================================================
        if ($this->has('jabatan')) {
            $jabatan = $this->input('jabatan');

            if (! empty($jabatan)) {
                $jabatan = strip_tags($jabatan);
                $jabatan = $this->removeDangerousChars($jabatan);
                $jabatan = trim($jabatan);
                $jabatan = sanitize_input($jabatan, 100);

                $this->merge(['jabatan' => $jabatan]);
            } else {
                $this->merge(['jabatan' => null]);
            }
        }

        // ====================================================================
        // STEP 5: Sanitize NO TELEPON (if exists)
        // ====================================================================
        if ($this->has('no_telepon')) {
            $phone = $this->input('no_telepon');

            if (! empty($phone)) {
                // Remove all non-digit/non-plus chars except spaces and hyphens
                $phone = preg_replace('/[^\d\+\-\s\(\)]/', '', $phone);
                $phone = trim($phone);

                $this->merge(['no_telepon' => $phone]);
            } else {
                $this->merge(['no_telepon' => null]);
            }
        }

        // ====================================================================
        // STEP 6: Sanitize ALAMAT (if exists)
        // ====================================================================
        if ($this->has('alamat')) {
            $alamat = $this->input('alamat');

            if (! empty($alamat)) {
                $alamat = strip_tags($alamat);
                $alamat = $this->removeDangerousChars($alamat);
                $alamat = trim($alamat);
                $alamat = sanitize_input($alamat, 500);

                $this->merge(['alamat' => $alamat]);
            } else {
                $this->merge(['alamat' => null]);
            }
        }

        // ====================================================================
        // STEP 7: Security check - prevent privilege escalation
        // ====================================================================
        // Remove any attempts to modify protected fields
        $protectedFields = ['role', 'is_admin', 'permissions', 'password', 'remember_token'];

        foreach ($protectedFields as $field) {
            if ($this->has($field)) {
                Log::warning('UpdateProfileRequest: Attempt to modify protected field', [
                    'user_id' => auth()->id(),
                    'field' => $field,
                    'ip' => request()->ip(),
                ]);

                // Remove from request
                $this->request->remove($field);
            }
        }
    }

    /**
     * Remove dangerous characters.
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
     * Format NPP based on digit length.
     */
    private function formatNPP(string $digits): ?string
    {
        if (empty($digits)) {
            return null;
        }

        $length = strlen($digits);

        // Standard format: 11 digits → 123.1.4567.890
        if ($length === 11) {
            return substr($digits, 0, 3).'.'.substr($digits, 3, 1).'.'.substr($digits, 4, 4).'.'.substr($digits, 8, 3);
        }

        // Alternative format: 18 digits → 123456.789.123456.789
        if ($length === 18) {
            return substr($digits, 0, 6).'.'.substr($digits, 6, 3).'.'.substr($digits, 9, 6).'.'.substr($digits, 15, 3);
        }

        // Fallback: group by 3 digits
        return implode('.', str_split($digits, 3));
    }

    /**
     * Validate NPP format.
     */
    private function isValidNPPFormat(string $npp): bool
    {
        // Remove dots to get digits
        $digits = str_replace('.', '', $npp);

        // Must be all digits
        if (! ctype_digit($digits)) {
            return false;
        }

        // Common lengths: 11 or 18 digits
        $length = strlen($digits);

        return in_array($length, [11, 18]);
    }

    /**
     * Check if email is from disposable service.
     */
    private function isDisposableEmail(string $email): bool
    {
        // List of common disposable email domains
        $disposableDomains = [
            'tempmail.com',
            'guerrillamail.com',
            '10minutemail.com',
            'throwaway.email',
            'mailinator.com',
            'trashmail.com',
            'temp-mail.org',
            'fakeinbox.com',
            'yopmail.com',
            'maildrop.cc',
            // Add more as needed
        ];

        $domain = strtolower(substr(strrchr($email, '@'), 1));

        return in_array($domain, $disposableDomains);
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            // Nama Lengkap
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nama_lengkap.min' => 'Nama lengkap minimal 3 karakter',
            'nama_lengkap.max' => 'Nama lengkap maksimal 100 karakter',
            'nama_lengkap.regex' => 'Nama lengkap hanya boleh berisi huruf, spasi, titik, koma, dan tanda hubung',

            // Email
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain',
            'email.regex' => 'Format email tidak valid',

            // NPP
            'npp.unique' => 'NPP sudah digunakan oleh pengguna lain',
            'npp.regex' => 'Format NPP tidak valid (hanya angka dan titik)',

            // Jabatan
            'jabatan.max' => 'Jabatan maksimal 100 karakter',
            'jabatan.regex' => 'Jabatan mengandung karakter tidak valid',

            // No Telepon
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter',
            'no_telepon.regex' => 'Format nomor telepon tidak valid. Contoh: 08123456789 atau +6281234567890',

            // Alamat
            'alamat.max' => 'Alamat maksimal 500 karakter',
            'alamat.regex' => 'Alamat mengandung karakter tidak valid',
        ];
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'email' => 'email',
            'npp' => 'NPP',
            'jabatan' => 'jabatan',
            'no_telepon' => 'nomor telepon',
            'alamat' => 'alamat',
        ];
    }

    /**
     * Handle failed validation.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::info('Profile update validation failed', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'errors' => $validator->errors()->keys(),
            'input_summary' => [
                'nama_lengkap' => substr($this->input('nama_lengkap', ''), 0, 20),
                'email' => $this->input('email'),
                'npp' => $this->input('npp'),
            ],
        ]);

        parent::failedValidation($validator);
    }
}
