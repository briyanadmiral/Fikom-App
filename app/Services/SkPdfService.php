<?php

namespace App\Services;

use App\Models\KeputusanHeader;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * ✅ REFACTORED: Enhanced security dengan file path validation & error handling
 */
class SkPdfService
{
    /**
     * Get signing assets (TTD & Cap) dengan validation
     * ✅ IMPROVED: Added error handling & validation
     */
    private function getSigningAssets(KeputusanHeader $sk): array
    {
        try {
            $ttdImageB64 = null;
            $penandatangan = $sk->penandatanganUser;

            if ($penandatangan && $penandatangan->signature) {
                $ttdPath = $penandatangan->signature->ttd_path;

                // ✅ ADDED: Validate file path
                $validTtdPath = validate_file_path($ttdPath);

                if ($validTtdPath !== null) {
                    $ttdImageB64 = $this->b64FromStorage($validTtdPath);
                }
            }

            $capImageB64 = null;
            $kop = MasterKopSurat::query()->first();

            if ($kop && !empty($kop->cap_path)) {
                // ✅ ADDED: Validate file path
                $validCapPath = validate_file_path($kop->cap_path);

                if ($validCapPath !== null) {
                    $capImageB64 = $this->b64FromPublicOrStorage($validCapPath);
                }
            }

            // ✅ ADDED: Validate dimensions
            $ttdW = $this->validateDimension($sk->ttd_w_mm ?? ($penandatangan?->signature?->default_width_mm ?? 42), 20, 80);

            $capW = $this->validateDimension($sk->cap_w_mm ?? 35, 20, 80);

            // ✅ ADDED: Validate opacity
            $capOpacity = $this->validateOpacity($sk->cap_opacity ?? 0.95);

            return compact('ttdImageB64', 'capImageB64', 'ttdW', 'capW', 'capOpacity', 'kop');
        } catch (\Exception $e) {
            Log::error('Failed to get signing assets', [
                'sk_id' => $sk->id,
                'error' => sanitize_log_message($e->getMessage()),
            ]);

            // Return defaults on error
            return [
                'ttdImageB64' => null,
                'capImageB64' => null,
                'ttdW' => 42,
                'capW' => 35,
                'capOpacity' => 0.95,
                'kop' => null,
            ];
        }
    }

    /**
     * Get base64 dari storage (private disk)
     * ✅ IMPROVED: Added validation & error handling
     */
    private function b64FromStorage(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            // ✅ ADDED: Validate file path
            $validPath = validate_file_path($path);

            if ($validPath === null) {
                Log::warning('Invalid file path for storage', [
                    'path' => sanitize_log_message($path),
                ]);
                return null;
            }

            // ✅ ADDED: Check if file exists
            if (!Storage::disk('local')->exists($validPath)) {
                Log::warning('File not found in storage', [
                    'path' => sanitize_log_message($validPath),
                ]);
                return null;
            }

            // ✅ ADDED: Check file size (max 5MB)
            $fileSize = Storage::disk('local')->size($validPath);
            if ($fileSize > 5 * 1024 * 1024) {
                Log::warning('File too large for base64 encoding', [
                    'path' => sanitize_log_message($validPath),
                    'size' => $fileSize,
                ]);
                return null;
            }

            // ✅ ADDED: Validate mime type
            $mimeType = Storage::disk('local')->mimeType($validPath);
            if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/jpg'], true)) {
                Log::warning('Invalid mime type for image', [
                    'path' => sanitize_log_message($validPath),
                    'mime' => $mimeType,
                ]);
                return null;
            }

            $raw = Storage::disk('local')->get($validPath);

            // ✅ IMPROVED: Dynamic mime type in base64
            $mime = $mimeType === 'image/jpeg' || $mimeType === 'image/jpg' ? 'jpeg' : 'png';
            return "data:image/{$mime};base64," . base64_encode($raw);
        } catch (\Exception $e) {
            Log::error('Failed to get base64 from storage', [
                'path' => sanitize_log_message($path),
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            return null;
        }
    }

    /**
     * Get base64 dari public atau storage
     * ✅ IMPROVED: Added validation & error handling
     */
    private function b64FromPublicOrStorage(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            // ✅ ADDED: Validate file path
            $validPath = validate_file_path($path);

            if ($validPath === null) {
                Log::warning('Invalid file path for public/storage', [
                    'path' => sanitize_log_message($path),
                ]);
                return null;
            }

            // Try public storage first
            $publicPath = 'public/' . ltrim($validPath, '/');

            if (Storage::exists($publicPath)) {
                // ✅ ADDED: Check file size
                $fileSize = Storage::size($publicPath);
                if ($fileSize > 5 * 1024 * 1024) {
                    Log::warning('File too large in public storage', [
                        'path' => sanitize_log_message($publicPath),
                        'size' => $fileSize,
                    ]);
                    return null;
                }

                // ✅ ADDED: Validate mime type
                $mimeType = Storage::mimeType($publicPath);
                if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/jpg'], true)) {
                    return null;
                }

                $raw = Storage::get($publicPath);
                $mime = $mimeType === 'image/jpeg' || $mimeType === 'image/jpg' ? 'jpeg' : 'png';
                return "data:image/{$mime};base64," . base64_encode($raw);
            }

            // Fallback to private storage
            return $this->b64FromStorage($validPath);
        } catch (\Exception $e) {
            Log::error('Failed to get base64 from public/storage', [
                'path' => sanitize_log_message($path),
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            return null;
        }
    }

    /**
     * Render PDF dan simpan ke storage
     * ✅ IMPROVED: Added comprehensive validation & error handling
     */
    public function renderAndStore(KeputusanHeader $sk): string
    {
        try {
            // ✅ ADDED: Validate SK
            if (empty($sk->id)) {
                throw new \InvalidArgumentException('Invalid Surat Keputusan ID');
            }

            // Get signing assets
            $assets = $this->getSigningAssets($sk);

            // ✅ ADDED: Validate view exists
            if (!view()->exists('surat_keputusan.surat_pdf')) {
                throw new \RuntimeException('PDF template view not found');
            }

            // Render HTML
            $html = view('surat_keputusan.surat_pdf', array_merge(['sk' => $sk], $assets, ['showSigns' => true, 'context' => 'pdf']))->render();

            // ✅ ADDED: Validate HTML not empty
            if (empty($html)) {
                throw new \RuntimeException('Generated HTML is empty');
            }

            // Generate PDF
            $bytes = Pdf::loadHTML($html)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'dpi' => 96,
                    'chroot' => public_path(),
                    // ✅ ADDED: Security options
                    'debugPng' => false,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                ])
                ->output();

            // ✅ ADDED: Validate PDF bytes
            if (empty($bytes)) {
                throw new \RuntimeException('PDF generation failed - empty output');
            }

            // ✅ IMPROVED: Sanitize filename components
            $skId = validate_integer_id($sk->id);
            $nomor = sanitize_filename($sk->nomor ?? 'unnamed');
            $hash = substr(md5($nomor), 0, 8);

            // ✅ IMPROVED: Secure path construction
            $filename = "{$skId}_{$hash}.pdf";
            $path = "private/surat_keputusan/signed/{$filename}";

            // ✅ ADDED: Ensure directory exists
            $directory = dirname($path);
            if (!Storage::disk('local')->exists($directory)) {
                Storage::disk('local')->makeDirectory($directory, 0755, true);
            }

            // Store PDF
            Storage::disk('local')->put($path, $bytes);

            // ✅ ADDED: Verify file was saved
            if (!Storage::disk('local')->exists($path)) {
                throw new \RuntimeException('Failed to save PDF to storage');
            }

            // ✅ ADDED: Log success
            Log::info('PDF generated and stored', [
                'sk_id' => $skId,
                'nomor' => sanitize_log_message($nomor),
                'path' => sanitize_log_message($path),
                'size' => Storage::disk('local')->size($path),
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to render and store PDF', [
                'sk_id' => $sk->id ?? null,
                'nomor' => sanitize_log_message($sk->nomor ?? 'unknown'),
                'error' => sanitize_log_message($e->getMessage()),
                'trace' => sanitize_log_message($e->getTraceAsString()),
            ]);

            throw new \RuntimeException('Gagal membuat PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * ✅ ADDED: Validate dimension value
     */
    private function validateDimension($value, int $min, int $max): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false || $value < $min || $value > $max) {
            return ($min + $max) / 2; // Return middle value as default
        }

        return $value;
    }

    /**
     * ✅ ADDED: Validate opacity value
     */
    private function validateOpacity($value): float
    {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($value === false || $value < 0 || $value > 1) {
            return 0.95; // Default
        }

        return $value;
    }

    /**
     * ✅ ADDED: Delete PDF file
     */
    public function deletePdf(string $path): bool
    {
        try {
            $validPath = validate_file_path($path);

            if ($validPath === null) {
                return false;
            }

            if (!Storage::disk('local')->exists($validPath)) {
                return true; // Already deleted
            }

            $deleted = Storage::disk('local')->delete($validPath);

            Log::info('PDF deleted', [
                'path' => sanitize_log_message($validPath),
                'success' => $deleted,
            ]);

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete PDF', [
                'path' => sanitize_log_message($path),
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            return false;
        }
    }

    /**
     * ✅ ADDED: Check if PDF exists
     */
    public function pdfExists(string $path): bool
    {
        $validPath = validate_file_path($path);

        if ($validPath === null) {
            return false;
        }

        return Storage::disk('local')->exists($validPath);
    }
}
