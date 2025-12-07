/**
* ============================================================================
* ANTI-INJECTION PROTECTION FOR ALL FORMS - IMPROVED VERSION
* ============================================================================
*
* Sistem keamanan client-side yang TIDAK MENGGANGGU USER EXPERIENCE
*
* Improvements:
* - ✅ Whitelist approach untuk field tertentu
* - ✅ Sanitasi HANYA saat submit (bukan real-time)
* - ✅ Pattern detection lebih pintar (context-aware)
* - ✅ Visual warning tanpa auto-replace
* - ✅ Configurable per field
*
* @version 2.0.0
* @author SIEGA Security Team (Improved)
* @date 2025-12-06
*/

(function() {
'use strict';

// ============================================================================
// CONFIGURATION
// ============================================================================

const CONFIG = {
// Maximum input length untuk mencegah buffer overflow
maxInputLength: 10000,

// Maximum file size (5MB)
maxFileSize: 5 * 1024 * 1024,

// Allowed file extensions
allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'],

// ❌ NONAKTIFKAN real-time validation (terlalu invasif!)
enableRealTime: false,

// Enable/disable console logging
debugMode: true, // Set false di production

// ✅ WHITELIST: Field yang TIDAK perlu sanitasi ketat
whitelistedFields: [
'nama_umum', // Judul Surat
'redaksi_pembuka', // Redaksi
'penutup', // Penutup
'detail_tugas', // Detail
'tempat', // Tempat
'keterangan', // Keterangan
'catatan', // Catatan
'deskripsi' // Deskripsi
],

// ✅ Field yang butuh validasi email
emailFields: ['email', 'email_pengirim', 'email_penerima'],

// ✅ Field yang harus numeric
numericFields: ['tahun', 'tahun_nomor', 'nomor_urut', 'jumlah', 'harga']
};

// ============================================================================
// IMPROVED DANGEROUS PATTERNS (Lebih Spesifik & Context-Aware)
// ============================================================================

const DANGEROUS_PATTERNS = {
// ✅ SQL Injection patterns - HANYA dalam konteks SQL yang jelas
sql: [
// Detect SQL dengan statement lengkap (lebih aman)
/(\bSELECT\b.*\bFROM\b)/gi,
/(\bINSERT\b.*\bINTO\b)/gi,
/(\bUPDATE\b.*\bSET\b)/gi,
/(\bDELETE\b.*\bFROM\b)/gi,
/(\bDROP\b.*\bTABLE\b)/gi,
/(\bCREATE\b.*\bTABLE\b)/gi,
/(\bALTER\b.*\bTABLE\b)/gi,

// SQL comments
/(--[^\r\n]*)/g,
/(\/\*[\s\S]*?\*\/)/g,

// UNION-based injection
/(\bUNION\b.*\bSELECT\b)/gi,

// OR/AND-based injection (dengan quote)
/(['"])\s*(OR|AND)\s+['"]?\w+['"]?\s*=\s*['"]?\w+/gi,

// Semicolon injection
/;\s*(SELECT|INSERT|UPDATE|DELETE|DROP)/gi
],

// ✅ XSS patterns - Lebih specific
xss: [
/<script\b[^>]*>[\s\S]*?<\ /script>/gi,
        /<iframe\b[^>]*>[\s\S]*?<\ /iframe>/gi,
                /javascript\s*:/gi,
                /<embed\b[^>]*>/gi,
                    /<object\b[^>]*>/gi,

                        // Event handlers dengan context
                        /<[^>]+\s+(on\w+)\s*=\s*["'][^"']*["']/gi,

                            // Data URI dengan javascript
                            /data:text\/html[^,]*,/gi,

                            // Base64 encoded scripts (advanced)
                            /eval\s*\(\s*atob\s*\(/gi
                            ],

                            // Path traversal
                            pathTraversal: [
                            /\.\.\//g,
                            /\.\.\\+/g,
                            /%2e%2e%2f/gi,
                            /%2e%2e\//gi,
                            /\.\.%2f/gi
                            ],

                            // Command injection (lebih spesifik)
                            commandInjection: [
                            /;\s*(wget|curl|bash|sh|cmd|powershell)/gi,
                            /\$\([^)]*\)/g, // $(command)
                            /`[^`]*`/g, // `command`
                            /&&\s*\w+/g, // && command
                            /\|\|\s*\w+/g // || command
                            ]
                            };

                            // ============================================================================
                            // SANITIZATION FUNCTIONS - IMPROVED
                            // ============================================================================

                            /**
                            * Check if input has dangerous patterns
                            * @param {string} value - Input value
                            * @returns {object} Detection result
                            */
                            function detectThreats(value) {
                            if (!value || typeof value !== 'string') {
                            return { hasThreat: false, threats: [] };
                            }

                            const threats = [];

                            // Check SQL injection
                            DANGEROUS_PATTERNS.sql.forEach((pattern, index) => {
                            if (pattern.test(value)) {
                            threats.push({ type: 'SQL Injection', pattern: index });
                            }
                            });

                            // Check XSS
                            DANGEROUS_PATTERNS.xss.forEach((pattern, index) => {
                            if (pattern.test(value)) {
                            threats.push({ type: 'XSS Attack', pattern: index });
                            }
                            });

                            // Check path traversal
                            DANGEROUS_PATTERNS.pathTraversal.forEach((pattern, index) => {
                            if (pattern.test(value)) {
                            threats.push({ type: 'Path Traversal', pattern: index });
                            }
                            });

                            // Check command injection
                            DANGEROUS_PATTERNS.commandInjection.forEach((pattern, index) => {
                            if (pattern.test(value)) {
                            threats.push({ type: 'Command Injection', pattern: index });
                            }
                            });

                            return {
                            hasThreat: threats.length > 0,
                            threats: threats
                            };
                            }

                            /**
                            * ✅ Sanitize text input - SOFT APPROACH
                            * @param {string} value - Input value
                            * @param {object} options - Sanitization options
                            * @returns {string} Sanitized value
                            */
                            function sanitizeText(value, options = {}) {
                            if (!value || typeof value !== 'string') return '';

                            let clean = value.trim();

                            // Apply length limit
                            const maxLength = options.maxLength || CONFIG.maxInputLength;
                            clean = clean.substring(0, maxLength);

                            // ✅ HANYA hapus pattern yang BENAR-BENAR berbahaya
                            DANGEROUS_PATTERNS.sql.forEach(pattern => {
                            clean = clean.replace(pattern, '');
                            });

                            DANGEROUS_PATTERNS.xss.forEach(pattern => {
                            clean = clean.replace(pattern, '');
                            });

                            DANGEROUS_PATTERNS.pathTraversal.forEach(pattern => {
                            clean = clean.replace(pattern, '');
                            });

                            DANGEROUS_PATTERNS.commandInjection.forEach(pattern => {
                            clean = clean.replace(pattern, '');
                            });

                            // Remove null bytes
                            clean = clean.replace(/\0/g, '');

                            // ✅ JANGAN normalize whitespace! Biarkan user bebas
                            // clean = clean.replace(/\s+/g, ' '); // ❌ DIHAPUS!

                            return clean;
                            }

                            /**
                            * Sanitize with whitelist check
                            * @param {HTMLInputElement} input - Input element
                            * @returns {string} Sanitized value
                            */
                            function smartSanitize(input) {
                            const fieldName = input.name || input.id || '';
                            const value = input.value;

                            // ✅ Check if field is whitelisted (skip sanitization)
                            const isWhitelisted = CONFIG.whitelistedFields.some(field =>
                            fieldName.toLowerCase().includes(field.toLowerCase())
                            );

                            if (isWhitelisted) {
                            if (CONFIG.debugMode) {
                            console.log(`✅ Field "${fieldName}" whitelisted - minimal sanitization`);
                            }

                            // Hanya hapus tag HTML berbahaya
                            return value
                            .replace(/<script\b[^>]*>[\s\S]*?<\ /script>/gi, '')
                                    .replace(/<iframe\b[^>]*>[\s\S]*?<\ /iframe>/gi, '')
                                            .replace(/javascript:/gi, '');
                                            }

                                            // Email fields
                                            if (CONFIG.emailFields.includes(fieldName)) {
                                            return sanitizeEmail(value);
                                            }

                                            // Numeric fields
                                            if (CONFIG.numericFields.includes(fieldName)) {
                                            return sanitizeNumber(value);
                                            }

                                            // Default: full sanitization
                                            return sanitizeText(value);
                                            }

                                            /**
                                            * Sanitize email input
                                            * @param {string} email - Email value
                                            * @returns {string} Sanitized email
                                            */
                                            function sanitizeEmail(email) {
                                            if (!email) return '';

                                            let clean = email.trim().toLowerCase();

                                            // Remove dangerous characters
                                            clean = clean.replace(/[<>"';]/g, '');

                                                return clean;
                                                }

                                                /**
                                                * Sanitize numeric input
                                                * @param {string} value - Numeric value
                                                * @returns {string} Sanitized number
                                                */
                                                function sanitizeNumber(value) {
                                                if (!value) return '';

                                                // Only allow digits, dots, and minus
                                                let clean = value.toString().replace(/[^\d.-]/g, '');

                                                return clean;
                                                }

                                                /**
                                                * Validate file upload
                                                * @param {File} file - File object
                                                * @returns {object} Validation result
                                                */
                                                function validateFile(file) {
                                                const result = {
                                                valid: true,
                                                message: ''
                                                };

                                                // Check file size
                                                if (file.size > CONFIG.maxFileSize) {
                                                result.valid = false;
                                                result.message = `File terlalu besar. Maksimal ${CONFIG.maxFileSize /
                                                (1024 * 1024)}MB`;
                                                return result;
                                                }

                                                // Check file extension
                                                const fileName = file.name.toLowerCase();
                                                const extension = fileName.split('.').pop();

                                                if (!CONFIG.allowedExtensions.includes(extension)) {
                                                result.valid = false;
                                                result.message = `Format file tidak diizinkan. Hanya:
                                                ${CONFIG.allowedExtensions.join(', ')}`;
                                                return result;
                                                }

                                                // Check for double extensions (file.php.jpg)
                                                const parts = fileName.split('.');
                                                if (parts.length > 2) {
                                                result.valid = false;
                                                result.message = 'Nama file tidak valid (double extension detected)';
                                                return result;
                                                }

                                                // Check for executable extensions in name
                                                const dangerousExts = ['php', 'exe', 'sh', 'bat', 'cmd', 'com', 'pif',
                                                'scr'];
                                                if (dangerousExts.some(ext => fileName.includes('.' + ext))) {
                                                result.valid = false;
                                                result.message = 'Nama file mengandung ekstensi berbahaya';
                                                return result;
                                                }

                                                return result;
                                                }

                                                //
                                                ============================================================================
                                                // FORM PROTECTION - IMPROVED
                                                //
                                                ============================================================================

                                                /**
                                                * Protect single form
                                                * @param {HTMLFormElement} form - Form element
                                                */
                                                function protectForm(form) {
                                                if (!form || form.dataset.protected === 'true') return;

                                                // Mark as protected
                                                form.dataset.protected = 'true';

                                                // ✅ Add submit handler (HANYA sanitasi saat submit!)
                                                form.addEventListener('submit', function(e) {
                                                if (CONFIG.debugMode) {
                                                console.log('🔒 Form submit intercepted:', form.id || form.name);
                                                }

                                                let hasBlockedThreat = false;
                                                const threats = [];

                                                // Get all text-based inputs
                                                const inputs = form.querySelectorAll('input[type="text"],
                                                input[type="search"], input[type="email"], textarea');

                                                inputs.forEach(input => {
                                                const original = input.value;

                                                // ✅ Detect threats BEFORE sanitizing
                                                const detection = detectThreats(original);

                                                if (detection.hasThreat) {
                                                threats.push({
                                                field: input.name || input.id,
                                                threats: detection.threats
                                                });

                                                // ✅ BLOCK submit jika ada ancaman serius
                                                if (detection.threats.some(t => t.type === 'SQL Injection' || t.type
                                                === 'XSS Attack')) {
                                                hasBlockedThreat = true;
                                                }
                                                }

                                                // Sanitize dengan smart approach
                                                const sanitized = smartSanitize(input);
                                                input.value = sanitized;

                                                if (CONFIG.debugMode && original !== sanitized) {
                                                console.warn('⚠️ Input sanitized:', input.name || input.id, {
                                                original: original.substring(0, 50) + '...',
                                                sanitized: sanitized.substring(0, 50) + '...'
                                                });
                                                }
                                                });

                                                // ✅ BLOCK submit jika ada threat serius
                                                if (hasBlockedThreat) {
                                                e.preventDefault();

                                                // Show SweetAlert jika tersedia
                                                if (typeof Swal !== 'undefined') {
                                                Swal.fire({
                                                icon: 'error',
                                                title: 'Input Berbahaya Terdeteksi',
                                                html: '<p>Sistem mendeteksi pola berbahaya pada input Anda:</p>' +
                                                '<ul style="text-align:left; padding-left: 30px;">' +
                                                    threats.map(t =>
                                                    `<li><strong>${t.field}:</strong> ${t.threats.map(th =>
                                                        th.type).join(', ')}</li>`
                                                    ).join('') +
                                                    '</ul>' +
                                                '<p class="mt-2 text-muted">Mohon periksa kembali input Anda.</p>',
                                                confirmButtonText: 'OK, Saya Mengerti',
                                                width: '600px'
                                                });
                                                } else {
                                                alert('⚠️ Input berbahaya terdeteksi!\n\nField: ' + threats.map(t =>
                                                t.field).join(', '));
                                                }

                                                return false;
                                                }

                                                // Validate file uploads
                                                const fileInputs = form.querySelectorAll('input[type="file"]');
                                                let fileError = false;

                                                fileInputs.forEach(input => {
                                                if (input.files.length > 0) {
                                                for (let i = 0; i < input.files.length; i++) { const
                                                    validation=validateFile(input.files[i]); if (!validation.valid) {
                                                    e.preventDefault(); if (typeof Swal !=='undefined' ) { Swal.fire({
                                                    icon: 'error' , title: 'File Tidak Valid' , text:
                                                    validation.message, confirmButtonText: 'OK' }); } else {
                                                    alert(validation.message); } fileError=true; break; } } } }); if
                                                    (fileError) return false; }); // ❌ REAL-TIME VALIDATION
                                                    DINONAKTIFKAN (terlalu invasif!) // if (CONFIG.enableRealTime) { //
                                                    addRealTimeValidation(form); // } }
                                                    //============================================================================//
                                                    INITIALIZATION
                                                    //============================================================================/**
                                                    * Initialize anti-injection protection */ function init() { if
                                                    (CONFIG.debugMode) { console.log('%c🛡️ Anti-Injection Protection
                                                    v2.0
                                                    Initialized', 'background: #28a745; color: white; font-size: 14px; padding: 5px 10px; border-radius: 3px;'
                                                    ); console.log('Whitelisted fields:', CONFIG.whitelistedFields); }
                                                    // Protect all existing forms
                                                    document.querySelectorAll('form').forEach(protectForm); // Watch for
                                                    dynamically added forms if (typeof MutationObserver !=='undefined' )
                                                    { const observer=new MutationObserver(function(mutations) {
                                                    mutations.forEach(function(mutation) {
                                                    mutation.addedNodes.forEach(function(node) { if
                                                    (node.tagName==='FORM' ) { protectForm(node); } else if
                                                    (node.querySelectorAll) {
                                                    node.querySelectorAll('form').forEach(protectForm); } }); }); });
                                                    observer.observe(document.body, { childList: true, subtree: true });
                                                    } } // Auto-initialize when DOM is ready if
                                                    (document.readyState==='loading' ) {
                                                    document.addEventListener('DOMContentLoaded', init); } else {
                                                    init(); }
                                                    //============================================================================//
                                                    PUBLIC API
                                                    //============================================================================window.AntiInjection={
                                                    sanitizeText, sanitizeEmail, sanitizeNumber, validateFile,
                                                    protectForm, detectThreats, smartSanitize, config: CONFIG,
                                                    version: '2.0.0' }; })();
