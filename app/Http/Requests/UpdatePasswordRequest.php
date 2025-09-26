<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // pastikan password saat ini valid
            'current_password' => ['required', 'current_password'],
            // password baru minimal 8, beda dengan current, dan pakai konfirmasi
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'Password saat ini',
            'new_password' => 'Password baru',
            'new_password_confirmation' => 'Konfirmasi password baru',
        ];
    }
}
