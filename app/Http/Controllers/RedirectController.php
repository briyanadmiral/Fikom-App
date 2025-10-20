<?php

namespace App\Http\Controllers;

class RedirectController extends Controller
{
    public function toApproveListSt()
    {
        return redirect()->route('surat_tugas.approveList');
    }

    public function toApproveListSk()
    {
        return redirect()->route('surat_keputusan.approveList');
    }

    public function legacySt(string $any = null)
    {
        // ✅ FIXED: Sanitize path parameter
        $safePath = $this->sanitizePath($any);
        $to = '/surat_tugas' . ($safePath ? "/{$safePath}" : '');

        // ✅ FIXED: Validate query string
        $qs = $this->getSafeQueryString();

        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    }

    public function legacySk(string $any = null)
    {
        // ✅ FIXED: Sanitize path parameter
        $safePath = $this->sanitizePath($any);
        $to = '/surat_keputusan' . ($safePath ? "/{$safePath}" : '');

        // ✅ FIXED: Validate query string
        $qs = $this->getSafeQueryString();

        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    }

    /**
     * ✅ Sanitize path to prevent directory traversal
     */
    private function sanitizePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        // Remove directory traversal attempts
        $path = str_replace(['..', '\\', "\0"], '', $path);

        // Allow only alphanumeric, dash, underscore, slash
        $path = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $path);

        // Remove leading/trailing slashes
        $path = trim($path, '/');

        return $path === '' ? null : $path;
    }

    /**
     * ✅ Get validated query string
     */
    private function getSafeQueryString(): ?string
    {
        $qs = request()->getQueryString();

        if (!$qs) {
            return null;
        }

        // Limit query string length
        if (strlen($qs) > 500) {
            return null;
        }

        // Basic XSS prevention in query string
        $qs = strip_tags($qs);

        return $qs;
    }
}
