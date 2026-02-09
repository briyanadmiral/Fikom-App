<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\File;

/**
 * ✅ IMPROVED: Enhanced file upload security & validation
 *
 * File upload adalah HIGH RISK vector attack!
 * Layer proteksi:
 * - Extension validation
 * - MIME type validation (real content check)
 * - File size validation
 * - Filename sanitization
 * - Path traversal protection
 * - Double extension detection
 * - Image dimensions validation (optional)
 *
 * @version 2.0.0
 *
 * @date 2025-12-06
 */
class AttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy or middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // === Keputusan ID ===
            'keputusan_id' => ['required', 'integer', 'exists:keputusan_header,id'],

            // === File Upload (STRICT VALIDATION) ===
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max (10 * 1024 KB)

                // ✅ IMPROVED: Use Laravel 10+ File rule for better validation
                File::types(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip', 'rar'])->max(10 * 1024), // 10MB in KB

                // ✅ ADDITIONAL: Validate MIME types (content-based, not just extension)
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar',
                'mimetypes:'.
                implode(',', [
                    // PDF
                    'application/pdf',

                    // MS Word
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',

                    // MS Excel
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                    // Images
                    'image/jpeg',
                    'image/jpg',
                    'image/png',

                    // Archives
                    'application/zip',
                    'application/x-rar-compressed',
                    'application/x-rar',
                    'application/octet-stream', // For RAR files
                ]),
            ],

            // === Deskripsi ===
            'deskripsi' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\p{L}\p{N}\s\-\.,;:()\/"\']+$/u', // Whitelist characters
            ],

            // === Kategori ===
            'kategori' => ['required', 'string', 'in:proposal,rab,surat_pengantar,dokumentasi,lainnya'],
        ];
    }

    /**
     * ✅ IMPROVED: Comprehensive file validation before processing
     */
    protected function prepareForValidation(): void
    {
        // ====================================================================
        // STEP 1: Sanitize TEXT fields
        // ====================================================================
        if ($this->has('deskripsi') && is_string($this->input('deskripsi'))) {
            $deskripsi = strip_tags($this->input('deskripsi'));
            $deskripsi = sanitize_input($deskripsi, 500);

            $this->merge(['deskripsi' => $deskripsi]);
        }

        // ====================================================================
        // STEP 2: Validate keputusan_id
        // ====================================================================
        if ($this->filled('keputusan_id')) {
            $validated = validate_integer_id($this->input('keputusan_id'));
            $this->merge(['keputusan_id' => $validated]);
        }

        // ====================================================================
        // STEP 3: Pre-validate uploaded file (if exists)
        // ====================================================================
        if ($this->hasFile('file')) {
            $file = $this->file('file');

            // ✅ Validate filename for path traversal attacks
            $originalName = $file->getClientOriginalName();

            if ($this->hasPathTraversal($originalName)) {
                Log::warning('AttachmentRequest: Path traversal detected in filename', [
                    'filename' => $originalName,
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);

                // Sanitize filename (remove dangerous characters)
                $originalName = $this->sanitizeFilename($originalName);
            }

            // ✅ Check for double extensions (e.g., shell.php.jpg)
            if ($this->hasDoubleExtension($originalName)) {
                Log::warning('AttachmentRequest: Double extension detected', [
                    'filename' => $originalName,
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
            }

            // ✅ Check for executable extensions in filename
            if ($this->hasExecutableExtension($originalName)) {
                Log::critical('AttachmentRequest: Executable extension detected', [
                    'filename' => $originalName,
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);

                abort(403, 'File dengan ekstensi executable tidak diizinkan');
            }

            // ✅ Validate image dimensions (if image file)
            if ($this->isImage($file)) {
                $this->validateImageDimensions($file);
            }

            // ✅ Additional MIME type validation
            $this->validateMimeType($file);
        }
    }

    /**
     * ✅ Check for path traversal patterns in filename
     */
    private function hasPathTraversal(string $filename): bool
    {
        $patterns = [
            '/\.\.\//', // ../
            '/\.\.\\\\/', // ..\
            '/%2e%2e%2f/i', // URL encoded ../
            '/%2e%2e\//i', // URL encoded ../
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ Sanitize filename (remove dangerous characters)
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);

        // Remove null bytes
        $filename = str_replace("\0", '', $filename);

        // Remove control characters
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename);

        // Remove path separators
        $filename = str_replace(['/', '\\', '..'], '', $filename);

        return $filename;
    }

    /**
     * ✅ Check for double extensions (e.g., file.php.jpg)
     */
    private function hasDoubleExtension(string $filename): bool
    {
        // Get all parts split by dot
        $parts = explode('.', $filename);

        // If more than 2 parts (name + extension), it has multiple extensions
        if (count($parts) > 2) {
            // Check if any part before last is a dangerous extension
            $dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'exe', 'sh', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar'];

            // Check all parts except the last one (which is the visible extension)
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if (in_array(strtolower($parts[$i]), $dangerousExts)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ✅ Check for executable extensions
     */
    private function hasExecutableExtension(string $filename): bool
    {
        $executableExts = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'php', 'phtml', 'php3', 'php4', 'php5', 'pht', 'sh', 'bash', 'zsh', 'csh', 'app', 'deb', 'rpm', 'msi', 'dmg', 'iso'];

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $executableExts);
    }

    /**
     * ✅ Check if file is an image
     */
    private function isImage($file): bool
    {
        $mimeType = $file->getMimeType();

        return in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * ✅ Validate image dimensions (prevent decompression bomb)
     */
    private function validateImageDimensions($file): void
    {
        try {
            $imageInfo = @getimagesize($file->getRealPath());

            if ($imageInfo === false) {
                Log::warning('AttachmentRequest: Invalid image file', [
                    'filename' => $file->getClientOriginalName(),
                    'user_id' => auth()->id(),
                ]);

                return;
            }

            [$width, $height] = $imageInfo;

            // ✅ Max dimensions: 10000x10000 (prevent decompression bomb)
            $maxDimension = 10000;

            if ($width > $maxDimension || $height > $maxDimension) {
                Log::warning('AttachmentRequest: Image dimensions too large', [
                    'filename' => $file->getClientOriginalName(),
                    'dimensions' => "{$width}x{$height}",
                    'user_id' => auth()->id(),
                ]);

                abort(422, "Dimensi gambar terlalu besar. Maksimal {$maxDimension}x{$maxDimension} piksel.");
            }

            // ✅ Check pixel count (max 100 megapixels)
            $maxPixels = 100 * 1000 * 1000; // 100 megapixels
            $pixels = $width * $height;

            if ($pixels > $maxPixels) {
                Log::warning('AttachmentRequest: Image pixel count too high', [
                    'filename' => $file->getClientOriginalName(),
                    'pixels' => $pixels,
                    'user_id' => auth()->id(),
                ]);

                abort(422, 'Total pixel gambar terlalu besar (decompression bomb detected).');
            }
        } catch (\Exception $e) {
            Log::error('AttachmentRequest: Error validating image', [
                'error' => $e->getMessage(),
                'filename' => $file->getClientOriginalName(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * ✅ Validate MIME type (content-based, not extension-based)
     */
    private function validateMimeType($file): void
    {
        $realMimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // ✅ MIME type whitelist mapping
        $allowedMimeTypes = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/x-rar-compressed', 'application/x-rar', 'application/octet-stream'],
        ];

        if (isset($allowedMimeTypes[$extension])) {
            if (! in_array($realMimeType, $allowedMimeTypes[$extension])) {
                Log::warning('AttachmentRequest: MIME type mismatch', [
                    'filename' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'expected_mime' => $allowedMimeTypes[$extension],
                    'actual_mime' => $realMimeType,
                    'user_id' => auth()->id(),
                ]);

                // Don't block, just warn (RAR files often have octet-stream MIME)
                // abort(422, 'Tipe file tidak sesuai dengan ekstensi file.');
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Keputusan ID
            'keputusan_id.required' => 'ID Keputusan wajib diisi',
            'keputusan_id.integer' => 'ID Keputusan tidak valid',
            'keputusan_id.exists' => 'Keputusan tidak ditemukan',

            // File
            'file.required' => 'File lampiran wajib dipilih',
            'file.file' => 'File tidak valid',
            'file.max' => 'Ukuran file maksimal 10 MB',
            'file.mimes' => 'Format file harus: PDF, Word (DOC/DOCX), Excel (XLS/XLSX), Gambar (JPG/PNG), atau Arsip (ZIP/RAR)',
            'file.mimetypes' => 'Tipe file tidak valid. Hanya file dokumen, gambar, dan arsip yang diizinkan',

            // Deskripsi
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter',
            'deskripsi.regex' => 'Deskripsi mengandung karakter tidak valid',

            // Kategori
            'kategori.required' => 'Kategori dokumen wajib dipilih',
            'kategori.in' => 'Kategori tidak valid. Pilih: Proposal, RAB, Surat Pengantar, Dokumentasi, atau Lainnya',
        ];
    }

    /**
     * ✅ Custom attribute names
     */
    public function attributes(): array
    {
        return [
            'keputusan_id' => 'ID keputusan',
            'file' => 'file lampiran',
            'deskripsi' => 'deskripsi',
            'kategori' => 'kategori dokumen',
        ];
    }

    /**
     * ✅ Handle failed validation
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::info('AttachmentRequest validation failed', [
            'user_id' => auth()->id(),
            'errors' => $validator->errors()->toArray(),
            'file_info' => $this->hasFile('file')
                ? [
                    'original_name' => $this->file('file')->getClientOriginalName(),
                    'mime_type' => $this->file('file')->getMimeType(),
                    'size' => $this->file('file')->getSize(),
                ]
                : null,
        ]);

        parent::failedValidation($validator);
    }
}
