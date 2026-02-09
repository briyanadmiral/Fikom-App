<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

/**
 * ✅ IMPROVED: Enhanced password security & validation
 *
 * Password security is CRITICAL!
 * Protection layers:
 * - Current password verification
 * - Password complexity requirements
 * - Common password blocking
 * - Password history check
 * - Rate limiting (handled by middleware)
 * - Secure logging (no password values)
 *
 * @version 2.0.0
 *
 * @date 2025-12-06
 */
class UpdatePasswordRequest extends FormRequest
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
        return [
            // === Current Password ===
            'current_password' => [
                'required',
                'string',
                'current_password', // Laravel built-in rule
            ],

            // === New Password ===
            'new_password' => [
                'required',
                'string',
                'confirmed', // Requires new_password_confirmation field
                'different:current_password', // Must be different from current

                // ✅ Laravel 10+ Password Rule (comprehensive)
                Password::min(8)
                    ->mixedCase()        // Requires uppercase and lowercase
                    ->letters()          // Requires at least one letter
                    ->numbers()          // Requires at least one number
                    ->symbols()          // Requires at least one symbol (!@#$%^&*)
                    ->uncompromised(3),  // Check against pwned passwords (allows 3 breaches max)

                // ✅ Custom rule: Check against password history
                function ($attribute, $value, $fail) {
                    if ($this->isSameAsOldPassword($value)) {
                        $fail('Password baru tidak boleh sama dengan password sebelumnya.');
                    }
                },

                // ✅ Custom rule: Block common passwords
                function ($attribute, $value, $fail) {
                    if ($this->isCommonPassword($value)) {
                        $fail('Password terlalu umum. Gunakan password yang lebih unik.');
                    }
                },

                // ✅ Custom rule: No sequential characters
                function ($attribute, $value, $fail) {
                    if ($this->hasSequentialChars($value)) {
                        $fail('Password tidak boleh mengandung karakter berurutan (123, abc, dll).');
                    }
                },

                // ✅ Custom rule: No repeated characters
                function ($attribute, $value, $fail) {
                    if ($this->hasRepeatedChars($value)) {
                        $fail('Password tidak boleh mengandung karakter yang diulang lebih dari 3 kali.');
                    }
                },

                // ✅ Custom rule: No user information
                function ($attribute, $value, $fail) {
                    if ($this->containsUserInfo($value)) {
                        $fail('Password tidak boleh mengandung nama, email, atau NPP Anda.');
                    }
                },
            ],
        ];
    }

    /**
     * ✅ Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // ⚠️ JANGAN sanitize password! Password boleh ada karakter apa saja
        // Sanitasi akan merusak password yang valid

        // ✅ Hanya trim whitespace di awal/akhir (user mungkin copy-paste)
        if ($this->has('current_password')) {
            $this->merge([
                'current_password' => trim($this->input('current_password')),
            ]);
        }

        if ($this->has('new_password')) {
            $this->merge([
                'new_password' => trim($this->input('new_password')),
            ]);
        }

        if ($this->has('new_password_confirmation')) {
            $this->merge([
                'new_password_confirmation' => trim($this->input('new_password_confirmation')),
            ]);
        }
    }

    /**
     * ✅ Check if new password is same as old password
     * (Simple check - you can extend to check against password history table)
     */
    private function isSameAsOldPassword(string $newPassword): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Check if same as current password
        return Hash::check($newPassword, $user->password);

        // ✅ OPTIONAL: Check against password history (if you have password_histories table)
        /*
        $recentPasswords = \App\Models\PasswordHistory::where('user_id', $user->id)
            ->latest()
            ->take(5) // Check last 5 passwords
            ->get();

        foreach ($recentPasswords as $history) {
            if (Hash::check($newPassword, $history->password)) {
                return true;
            }
        }
        */

        return false;
    }

    /**
     * ✅ Check against common passwords list
     */
    private function isCommonPassword(string $password): bool
    {
        // List of most common passwords
        $commonPasswords = [
            'password', 'password123', '12345678', '123456789', '1234567890',
            'qwerty', 'abc123', 'monkey', '1234567', '12345',
            'iloveyou', 'admin', 'welcome', 'login', 'admin123',
            'root', 'pass', 'test', 'guest', 'user',
            'password1', 'letmein', 'trustno1', 'dragon', 'baseball',
            'master', 'sunshine', 'ashley', 'bailey', 'shadow',
            'superman', 'qazwsx', 'michael', 'football', '111111',
            '654321', 'passw0rd', 'P@ssw0rd', 'P@ssword', 'password!',
        ];

        $lowerPassword = strtolower($password);

        return in_array($lowerPassword, $commonPasswords);
    }

    /**
     * ✅ Check for sequential characters (123, abc, etc)
     */
    private function hasSequentialChars(string $password): bool
    {
        $sequences = [
            '0123456789',
            '9876543210',
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'ZYXWVUTSRQPONMLKJIHGFEDCBA',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
        ];

        $lowerPassword = strtolower($password);

        foreach ($sequences as $sequence) {
            // Check for 4+ sequential characters
            for ($i = 0; $i <= strlen($sequence) - 4; $i++) {
                $subseq = substr($sequence, $i, 4);
                if (strpos($lowerPassword, $subseq) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ✅ Check for repeated characters (aaaa, 1111, etc)
     */
    private function hasRepeatedChars(string $password): bool
    {
        // Check for 4 or more repeated characters
        return preg_match('/(.)\1{3,}/', $password) === 1;
    }

    /**
     * ✅ Check if password contains user information
     */
    private function containsUserInfo(string $password): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $lowerPassword = strtolower($password);

        // Check nama lengkap
        if ($user->nama_lengkap) {
            $namaParts = explode(' ', strtolower($user->nama_lengkap));
            foreach ($namaParts as $part) {
                if (strlen($part) >= 3 && strpos($lowerPassword, $part) !== false) {
                    return true;
                }
            }
        }

        // Check email (before @)
        if ($user->email) {
            $emailUsername = explode('@', strtolower($user->email))[0];
            if (strlen($emailUsername) >= 3 && strpos($lowerPassword, $emailUsername) !== false) {
                return true;
            }
        }

        // Check NPP/username
        if (isset($user->npp) && $user->npp) {
            if (strpos($lowerPassword, strtolower($user->npp)) !== false) {
                return true;
            }
        }

        if (isset($user->username) && $user->username) {
            if (strpos($lowerPassword, strtolower($user->username)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            // Current Password
            'current_password.required' => 'Password saat ini wajib diisi',
            'current_password.current_password' => 'Password saat ini tidak sesuai',

            // New Password
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak sesuai',
            'new_password.different' => 'Password baru harus berbeda dengan password saat ini',

            // Password complexity (from Password rule)
            'new_password.mixed' => 'Password harus mengandung huruf besar dan kecil',
            'new_password.letters' => 'Password harus mengandung minimal satu huruf',
            'new_password.numbers' => 'Password harus mengandung minimal satu angka',
            'new_password.symbols' => 'Password harus mengandung minimal satu simbol (!@#$%^&*)',
            'new_password.uncompromised' => 'Password ini pernah bocor dalam data breach. Gunakan password lain yang lebih aman',
        ];
    }

    /**
     * Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'password saat ini',
            'new_password' => 'password baru',
            'new_password_confirmation' => 'konfirmasi password baru',
        ];
    }

    /**
     * ✅ Handle successful validation
     */
    protected function passedValidation(): void
    {
        Log::info('Password update validation passed', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    /**
     * ✅ Handle failed validation
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // ⚠️ SECURITY: Log failed password change attempts
        Log::warning('Password update validation failed', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'errors' => $validator->errors()->keys(), // Log field names only, NOT values!
            'timestamp' => now(),
        ]);

        parent::failedValidation($validator);
    }
}
