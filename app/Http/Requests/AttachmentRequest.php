<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'keputusan_id' => 'required|exists:keputusan_header,id',
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar',
            ],
            'deskripsi' => 'nullable|string|max:500',
            'kategori' => 'required|in:proposal,rab,surat_pengantar,dokumentasi,lainnya',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File lampiran wajib dipilih.',
            'file.file' => 'File tidak valid.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
            'file.mimes' => 'Format file harus: PDF, Word, Excel, Gambar (JPG/PNG), atau ZIP/RAR.',
            'kategori.required' => 'Kategori dokumen wajib dipilih.',
            'kategori.in' => 'Kategori tidak valid.',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Sanitize deskripsi
        if ($this->has('deskripsi')) {
            $this->merge([
                'deskripsi' => sanitize_input($this->input('deskripsi'), 500),
            ]);
        }
    }
}
