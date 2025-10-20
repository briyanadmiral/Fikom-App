<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * ====================================================================
 * SECTION 1: DATE & TIME HELPERS
 * ====================================================================
 */

/**
 * Format datetime untuk input HTML5 datetime-local
 * Dengan validasi dan sanitasi input
 */
if (!function_exists('formatDatetimeLocal')) {
    function formatDatetimeLocal($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            // Validasi bahwa value adalah datetime yang valid
            $date = Carbon::parse($value);
            return $date->format('Y-m-d\TH:i');
        } catch (\Exception $e) {
            // Log error untuk debugging tanpa expose ke user
            \Log::warning('formatDatetimeLocal: Invalid date format', [
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }
}

/**
 * ====================================================================
 * SECTION 2: INPUT SANITIZATION HELPERS (Core Security Functions)
 * ====================================================================
 */

if (!function_exists('sanitize_input')) {
    /**
     * Sanitasi input untuk mencegah XSS
     * SECURITY: Strip tags dan limit length
     * 
     * @param string|null $value
     * @param int $maxLength
     * @return string|null
     */
    function sanitize_input(?string $value, int $maxLength = 255): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Strip HTML tags
        $cleaned = strip_tags($value);
        
        // Trim whitespace
        $cleaned = trim($cleaned);
        
        // Limit length
        $cleaned = substr($cleaned, 0, $maxLength);
        
        return $cleaned ?: null;
    }
}

if (!function_exists('sanitize_output')) {
    /**
     * Sanitasi output untuk display
     * SECURITY: Escape HTML entities
     * 
     * @param string|null $value
     * @return string
     */
    function sanitize_output(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('sanitize_search_keyword')) {
    /**
     * Sanitasi search keyword untuk LIKE query
     * SECURITY: Escape wildcard characters
     * 
     * @param string $keyword
     * @return string
     */
    function sanitize_search_keyword(string $keyword): string
    {
        $cleaned = strip_tags($keyword);
        $cleaned = trim($cleaned);
        
        // Escape LIKE wildcards untuk literal search
        $cleaned = str_replace(['%', '_'], ['\\%', '\\_'], $cleaned);
        
        return substr($cleaned, 0, 100);
    }
}

if (!function_exists('sanitize_html_limited')) {
    /**
     * Sanitasi HTML dengan whitelist tags
     * Untuk field yang boleh mengandung HTML terbatas (seperti CKEditor)
     * 
     * @param string|null $input
     * @param string $allowedTags
     * @return string|null
     */
    function sanitize_html_limited(?string $input, string $allowedTags = '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><table><tr><td><th><thead><tbody>'): ?string
    {
        if (empty($input)) {
            return null;
        }

        // Strip tag yang tidak diperbolehkan
        $cleaned = strip_tags($input, $allowedTags);

        // Hapus atribut berbahaya seperti onclick, onerror, onload
        $cleaned = preg_replace('/<([^>]+)\s+on\w+\s*=\s*["\']?[^"\']*["\']?([^>]*)>/i', '<$1$2>', $cleaned);
        
        // Hapus javascript: di dalam href
        $cleaned = preg_replace('/<a\s+([^>]*\s+)?href\s*=\s*["\']?\s*javascript:[^"\']*["\']?([^>]*)>/i', '<a $1$2>', $cleaned);

        // Hapus atribut style yang berisi expression (IE specific XSS)
        $cleaned = preg_replace('/<([^>]+)\s+style\s*=\s*["\']?[^"\']*expression[^"\']*["\']?([^>]*)>/i', '<$1$2>', $cleaned);

        return $cleaned;
    }
}

/**
 * ====================================================================
 * SECTION 3: FILE PATH & SECURITY HELPERS
 * ====================================================================
 */

if (!function_exists('validate_file_path')) {
    /**
     * Validasi file path untuk mencegah path traversal
     * SECURITY: Sanitasi path
     * 
     * @param string|null $path
     * @return string|null
     */
    function validate_file_path(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Hapus path traversal attempts
        $cleaned = str_replace(['../', '..\\'], '', $path);
        
        // Hapus null bytes
        $cleaned = str_replace("\0", '', $cleaned);
        
        // Validasi bahwa path tidak absolute
        if (preg_match('/^[a-z]:/i', $cleaned) || ($cleaned[0] ?? '') === '/') {
            \Log::warning('Suspicious file path detected', ['path' => $path]);
            return null;
        }

        return $cleaned;
    }
}

/**
 * ====================================================================
 * SECTION 4: LOG & NOTIFICATION HELPERS
 * ====================================================================
 */

if (!function_exists('sanitize_log_message')) {
    /**
     * Sanitasi string untuk logging
     * SECURITY: Mencegah log injection
     * 
     * @param string|null $text
     * @return string
     */
    function sanitize_log_message(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Hapus newline dan carriage return untuk mencegah log injection
        $cleaned = str_replace(["\n", "\r"], ' ', $text);
        
        return substr($cleaned, 0, 255);
    }
}

if (!function_exists('sanitize_notification')) {
    /**
     * Sanitasi text untuk notifikasi
     * SECURITY: Strip tags dan limit length
     * 
     * @param string|null $text
     * @param int $maxLength
     * @return string
     */
    function sanitize_notification(?string $text, int $maxLength = 500): string
    {
        if (empty($text)) {
            return '';
        }

        // Strip HTML tags
        $cleaned = strip_tags($text);
        
        // Escape HTML entities
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Limit panjang untuk mencegah notification spam
        return substr(trim($cleaned), 0, $maxLength);
    }
}

/**
 * ====================================================================
 * SECTION 5: VALIDATION HELPERS
 * ====================================================================
 */

if (!function_exists('validate_integer_id')) {
    /**
     * Validasi dan sanitasi integer ID
     * SECURITY: Mencegah type juggling
     * 
     * @param mixed $value
     * @return int|null
     */
    function validate_integer_id($value): ?int
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($validated === false || $validated <= 0) {
            return null;
        }
        
        return $validated;
    }
}

if (!function_exists('validate_status')) {
    /**
     * Validasi status dengan whitelist
     * 
     * @param string|null $status
     * @param array $validStatuses
     * @return string|null
     */
    function validate_status(?string $status, array $validStatuses = ['draft', 'pending', 'disetujui', 'ditolak']): ?string
    {
        if (empty($status)) {
            return null;
        }

        return in_array($status, $validStatuses, true) ? $status : null;
    }
}

if (!function_exists('validate_sort_direction')) {
    /**
     * Validasi sort direction untuk query
     * SECURITY: Whitelist untuk mencegah SQL injection
     * 
     * @param string|null $direction
     * @return string
     */
    function validate_sort_direction(?string $direction): string
    {
        $direction = strtolower(trim($direction ?? ''));
        return in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
    }
}

/**
 * ====================================================================
 * SECTION 6: SPECIFIC DATA TYPE SANITIZATION
 * ====================================================================
 */

if (!function_exists('sanitize_email')) {
    /**
     * Validasi dan sanitasi email
     * 
     * @param string $email Email address
     * @return string Email yang valid atau empty string
     */
    function sanitize_email(?string $email): string
    {
        if (empty($email)) {
            return '';
        }

        // Filter email menggunakan PHP filter
        $cleaned = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        // Validasi format email
        if (filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            return $cleaned;
        }

        return '';
    }
}

if (!function_exists('sanitize_phone')) {
    /**
     * Validasi dan sanitasi nomor telepon
     * 
     * @param string $phone Nomor telepon
     * @return string Nomor telepon yang sudah dibersihkan
     */
    function sanitize_phone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Hapus semua karakter kecuali digit, +, -, (, ), dan spasi
        $cleaned = preg_replace('/[^0-9+\-() ]/', '', $phone);
        
        // Trim dan batasi panjang
        return substr(trim($cleaned), 0, 20);
    }
}

if (!function_exists('sanitize_alphanumeric')) {
    /**
     * Sanitasi string menjadi alphanumeric only
     * 
     * @param string|null $value
     * @param string $allowed Additional allowed characters (e.g., '_-')
     * @return string|null
     */
    function sanitize_alphanumeric(?string $value, string $allowed = ''): ?string
    {
        if (empty($value)) {
            return null;
        }

        $pattern = '/[^a-zA-Z0-9' . preg_quote($allowed, '/') . ']/';
        $cleaned = preg_replace($pattern, '', $value);
        
        return $cleaned ?: null;
    }
}

if (!function_exists('sanitize_kode')) {
    /**
     * Sanitasi kode (uppercase alphanumeric dengan dash/underscore)
     * Untuk field seperti kode_klasifikasi, kode_tugas, etc.
     * 
     * @param string|null $value
     * @param int $maxLength
     * @return string|null
     */
    function sanitize_kode(?string $value, int $maxLength = 50): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Hanya alphanumeric, dash, dan underscore
        $cleaned = preg_replace('/[^a-zA-Z0-9_-]/', '', $value);
        
        // Uppercase dan limit length
        return substr(strtoupper(trim($cleaned)), 0, $maxLength) ?: null;
    }
}

/**
 * ====================================================================
 * SECTION 7: HTML OUTPUT HELPERS (Safe Wrappers)
 * ====================================================================
 */

if (!function_exists('safe_html')) {
    /**
     * Sanitasi string untuk output HTML
     * Wrapper untuk htmlspecialchars dengan setting aman
     * 
     * @param mixed $value Input value
     * @return string Sanitized string
     */
    function safe_html($value): string
    {
        if (is_null($value)) {
            return '';
        }

        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('safe_attr')) {
    /**
     * Sanitasi string untuk output dalam atribut HTML
     * Lebih ketat dari safe_html()
     * 
     * @param mixed $value Input value
     * @return string Sanitized string untuk atribut
     */
    function safe_attr($value): string
    {
        if (is_null($value)) {
            return '';
        }

        // Hapus karakter yang berbahaya dalam atribut HTML
        $cleaned = preg_replace('/["\'\r\n]/', '', (string)$value);
        
        return htmlspecialchars($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

/**
 * ====================================================================
 * SECTION 8: BADGE & UI HELPERS
 * ====================================================================
 */

/**
 * Generate HTML badge peran berdasarkan nama peran
 * AMAN: Output di-escape dengan htmlspecialchars
 * 
 * @param string      $nama  Nama peran (misal: 'admin_tu', 'dekan')
 * @param string|null $label Label/tulisan (default null = sama seperti nama)
 * @return string HTML badge yang sudah di-escape
 */
if (!function_exists('badge_peran')) {
    function badge_peran(string $nama, ?string $label = null): string
    {
        // Sanitasi input nama peran - hanya terima alphanumeric dan underscore
        $nama = preg_replace('/[^a-z0-9_]/i', '', $nama);

        // Otomatis membuat label yang rapi, contoh: 'wakil_dekan' -> 'Wakil Dekan'
        $label = $label ?? ucwords(str_replace('_', ' ', $nama));

        // Whitelist warna untuk peran yang dikenal
        $colors = [
            'admin_tu'    => 'background-color: #ffffba; color: #212529;',
            'dekan'       => 'background-color: #f8d7da; color: #212529;',
            'wakil_dekan' => 'background-color: #ffeadb; color: #212529;',
            'kaprodi'     => 'background-color: #d1ecf1; color: #212529;',
            'dosen'       => 'background-color: #d4edda; color: #212529;',
            'tendik'      => 'background-color: #e2d9f3; color: #212529;',
        ];

        // Warna default jika peran tidak ada dalam daftar
        $style = $colors[$nama] ?? 'background-color: #f8f9fa; color: #212529;';

        // CRITICAL: Escape label untuk mencegah XSS
        $safeLabel = htmlspecialchars($label, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return sprintf(
            '<span class="badge" style="%s">%s</span>',
            $style, // Style sudah hardcoded, aman
            $safeLabel
        );
    }
}

/**
 * Membuat badge untuk peran pengguna (Bootstrap style)
 * AMAN: Output di-escape dengan htmlspecialchars
 * 
 * @param string $peran Nama peran
 * @return string HTML badge
 */
if (!function_exists('badge_peran_bootstrap')) {
    function badge_peran_bootstrap(?string $peran): string
    {
        if (empty($peran)) {
            return '<span class="badge badge-secondary">Unknown</span>';
        }

        // Whitelist warna Bootstrap untuk peran
        $colors = [
            'Admin'    => 'danger',
            'Operator' => 'info',
            'User'     => 'secondary',
            'Dekan'    => 'primary',
            'Dosen'    => 'success',
            'Tendik'   => 'warning',
        ];

        // Default color jika peran tidak dikenal
        $color = $colors[$peran] ?? 'light';

        // Validasi color untuk memastikan hanya Bootstrap classes yang valid
        $validColors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
        if (!in_array($color, $validColors, true)) {
            $color = 'secondary';
        }

        // CRITICAL: Escape peran untuk mencegah XSS
        $safePeran = htmlspecialchars($peran, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return sprintf(
            '<span class="badge badge-%s badge-peran">%s</span>',
            $color, // Sudah divalidasi dengan whitelist
            $safePeran
        );
    }
}

/**
 * ====================================================================
 * SECTION 9: USER-RELATED HELPERS
 * ====================================================================
 */

/**
 * Mengambil inisial dari nama lengkap dengan sanitasi
 * Contoh: "John Doe" menjadi "JD"
 * AMAN: Output di-escape
 * 
 * @param string $name Nama lengkap
 * @return string Inisial (maksimal 2 huruf)
 */
if (!function_exists('get_initials')) {
    function get_initials(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        // Sanitasi input - hapus karakter non-alphanumeric kecuali spasi
        $name = preg_replace('/[^a-zA-Z\s]/', '', $name);
        $name = trim($name);

        if (empty($name)) {
            return '';
        }

        $words = explode(' ', $name);
        $initials = '';
        $max = 2; // Ambil maksimal 2 huruf

        for ($i = 0; $i < count($words) && $i < $max; $i++) {
            if (!empty($words[$i])) {
                $initials .= strtoupper(substr($words[$i], 0, 1));
            }
        }

        return $initials;
    }
}

/**
 * Menghasilkan kode warna hex dari sebuah string (nama)
 * AMAN: Output sudah dalam format hex yang aman
 * 
 * @param string $string Input string
 * @return string Warna hex (contoh: #a3f2c1)
 */
if (!function_exists('generate_color_from_string')) {
    function generate_color_from_string(?string $string): string
    {
        if (empty($string)) {
            return '#cccccc'; // Default gray
        }

        // Generate hash dari string
        $hash = md5($string);
        
        // Ambil 6 karakter pertama untuk RGB
        $color = substr($hash, 0, 6);
        
        // Validasi bahwa hasilnya hex valid
        if (preg_match('/^[a-f0-9]{6}$/i', $color)) {
            return '#' . $color;
        }

        return '#cccccc'; // Fallback
    }
}

/**
 * ====================================================================
 * SECTION 10: LOGGING & AUDIT HELPERS
 * ====================================================================
 */

/**
 * Catat perubahan status surat ke tabel tugas_log
 * AMAN: Menggunakan parameter binding untuk semua query
 * 
 * @param \Illuminate\Database\Connection $db      Koneksi DB Laravel
 * @param int                            $tugasId ID dari tugas_header
 * @param string|null                    $old     Status lama
 * @param string|null                    $new     Status baru
 * @return void
 */
if (!function_exists('logStatusChange')) {
    function logStatusChange($db, int $tugasId, ?string $old, ?string $new): void
    {
        // Validasi tugasId
        if ($tugasId <= 0) {
            \Log::error('logStatusChange: Invalid tugasId', ['tugasId' => $tugasId]);
            return;
        }

        // Whitelist status yang valid
        $validStatuses = ['draft', 'pending', 'disetujui', 'ditolak', null];
        if (!in_array($old, $validStatuses, true) || !in_array($new, $validStatuses, true)) {
            \Log::warning('logStatusChange: Invalid status', [
                'old' => $old,
                'new' => $new
            ]);
        }

        try {
            if ($db instanceof \PDO) {
                // Menggunakan PDO dengan prepared statement
                $stmt = $db->prepare("
                    INSERT INTO tugas_log
                        (tugas_id, status_lama, status_baru, user_id, ip_address, user_agent, created_at)
                    VALUES
                        (?, ?, ?, ?, ?, ?, NOW())
                ");

                // Sanitasi IP address dan User Agent
                $userId = $_SESSION['user_id'] ?? null;
                $ip = filter_var($_SERVER['REMOTE_ADDR'] ?? null, FILTER_VALIDATE_IP);
                $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255); // Limit length

                $stmt->execute([
                    $tugasId,
                    $old,
                    $new,
                    $userId,
                    $ip,
                    $userAgent,
                ]);
            } else {
                // Laravel DB facade - sudah otomatis menggunakan parameter binding
                $userId = Auth::id();
                $ip = request()->ip(); // Laravel sudah validasi IP
                $userAgent = substr((string)request()->userAgent(), 0, 255); // Limit length

                \DB::table('tugas_log')->insert([
                    'tugas_id'    => (int)$tugasId,
                    'status_lama' => $old,
                    'status_baru' => $new,
                    'user_id'     => $userId,
                    'ip_address'  => $ip,
                    'user_agent'  => $userAgent,
                    'created_at'  => now(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('logStatusChange: Failed to log status change', [
                'tugasId' => $tugasId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
