/**
 * ============================================================================
 * ANTI-INJECTION PROTECTION FOR ALL FORMS
 * ============================================================================
 * 
 * Sistem keamanan otomatis untuk mencegah SQL Injection, XSS, dan serangan
 * injection lainnya di client-side.
 * 
 * Features:
 * - Auto-detect semua form di halaman
 * - Sanitasi input sebelum submit
 * - Real-time character filtering
 * - File upload validation
 * - Visual feedback untuk user
 * 
 * @version 1.0.0
 * @author Surat SIEGA Security Team
 * @date 2025-10-20
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
        allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        
        // Enable/disable real-time validation
        enableRealTime: true,
        
        // Enable/disable console logging
        debugMode: false
    };

    // ============================================================================
    // DANGEROUS PATTERNS
    // ============================================================================
    
    const DANGEROUS_PATTERNS = {
        // SQL Injection patterns
        sql: [
            /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|DECLARE)\b)/gi,
            /(--|\#|\/\*|\*\/)/g,
            /('|")(;|\s)*(OR|AND|UNION|SELECT)/gi,
            /(;|\s)*DROP\s+TABLE/gi,
            /(\bOR\b|\bAND\b)\s*['"]?\d+['"]?\s*=\s*['"]?\d+/gi
        ],
        
        // XSS patterns
        xss: [
            /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
            /<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi,
            /javascript:/gi,
            /on\w+\s*=/gi, // onerror=, onload=, onclick=, etc.
            /<embed\b/gi,
            /<object\b/gi,
            /<img[^>]+src\s*=\s*["']?\s*javascript:/gi
        ],
        
        // Path traversal
        pathTraversal: [
            /\.\.\//g,
            /\.\.\\+/g,
            /%2e%2e%2f/gi,
            /%2e%2e\//gi
        ],
        
        // Command injection
        commandInjection: [
            /[;&|`$()]/g,
            /\b(eval|exec|system|shell_exec|passthru)\b/gi
        ]
    };

    // ============================================================================
    // SANITIZATION FUNCTIONS
    // ============================================================================

    /**
     * Sanitize text input
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

        // Remove SQL injection patterns
        DANGEROUS_PATTERNS.sql.forEach(pattern => {
            clean = clean.replace(pattern, '');
        });

        // Remove XSS patterns
        DANGEROUS_PATTERNS.xss.forEach(pattern => {
            clean = clean.replace(pattern, '');
        });

        // Remove path traversal patterns
        DANGEROUS_PATTERNS.pathTraversal.forEach(pattern => {
            clean = clean.replace(pattern, '');
        });

        // Remove command injection patterns
        DANGEROUS_PATTERNS.commandInjection.forEach(pattern => {
            clean = clean.replace(pattern, '');
        });

        // Remove null bytes
        clean = clean.replace(/\0/g, '');

        // Normalize whitespace
        clean = clean.replace(/\s+/g, ' ');

        return clean;
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
        
        // Basic email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(clean)) {
            return '';
        }
        
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
            result.message = `File terlalu besar. Maksimal ${CONFIG.maxFileSize / (1024 * 1024)}MB`;
            return result;
        }

        // Check file extension
        const fileName = file.name.toLowerCase();
        const extension = fileName.split('.').pop();
        
        if (!CONFIG.allowedExtensions.includes(extension)) {
            result.valid = false;
            result.message = `Format file tidak diizinkan. Hanya: ${CONFIG.allowedExtensions.join(', ')}`;
            return result;
        }

        // Check for double extensions (file.php.jpg)
        const parts = fileName.split('.');
        if (parts.length > 2) {
            result.valid = false;
            result.message = 'Nama file tidak valid (double extension)';
            return result;
        }

        return result;
    }

    // ============================================================================
    // FORM PROTECTION
    // ============================================================================

    /**
     * Protect single form
     * @param {HTMLFormElement} form - Form element
     */
    function protectForm(form) {
        if (!form || form.dataset.protected === 'true') return;

        // Mark as protected
        form.dataset.protected = 'true';

        // Add submit handler
        form.addEventListener('submit', function(e) {
            if (CONFIG.debugMode) {
                console.log('Form submit intercepted:', form.id || form.name);
            }

            // Get all inputs
            const textInputs = form.querySelectorAll('input[type="text"], input[type="search"], textarea');
            const emailInputs = form.querySelectorAll('input[type="email"]');
            const numberInputs = form.querySelectorAll('input[type="number"]');
            const fileInputs = form.querySelectorAll('input[type="file"]');

            // Sanitize text inputs
            textInputs.forEach(input => {
                const original = input.value;
                input.value = sanitizeText(original);
                
                if (CONFIG.debugMode && original !== input.value) {
                    console.warn('Input sanitized:', input.name, {original, sanitized: input.value});
                }
            });

            // Sanitize email inputs
            emailInputs.forEach(input => {
                const original = input.value;
                input.value = sanitizeEmail(original);
                
                if (CONFIG.debugMode && original !== input.value) {
                    console.warn('Email sanitized:', input.name, {original, sanitized: input.value});
                }
            });

            // Sanitize number inputs
            numberInputs.forEach(input => {
                const original = input.value;
                input.value = sanitizeNumber(original);
                
                if (CONFIG.debugMode && original !== input.value) {
                    console.warn('Number sanitized:', input.name, {original, sanitized: input.value});
                }
            });

            // Validate file uploads
            let fileError = false;
            fileInputs.forEach(input => {
                if (input.files.length > 0) {
                    for (let i = 0; i < input.files.length; i++) {
                        const validation = validateFile(input.files[i]);
                        if (!validation.valid) {
                            e.preventDefault();
                            alert(validation.message);
                            fileError = true;
                            break;
                        }
                    }
                }
            });

            if (fileError) return false;
        });

        // Add real-time validation if enabled
        if (CONFIG.enableRealTime) {
            addRealTimeValidation(form);
        }
    }

    /**
     * Add real-time validation to form inputs
     * @param {HTMLFormElement} form - Form element
     */
    function addRealTimeValidation(form) {
        const textInputs = form.querySelectorAll('input[type="text"], input[type="search"], textarea');
        
        textInputs.forEach(input => {
            // Create warning indicator
            const warning = document.createElement('small');
            warning.className = 'text-danger d-none';
            warning.style.fontSize = '0.8em';
            warning.textContent = '⚠️ Karakter berbahaya dihapus';
            
            // Insert after input
            if (input.nextSibling) {
                input.parentNode.insertBefore(warning, input.nextSibling);
            } else {
                input.parentNode.appendChild(warning);
            }

            // Add input handler with debounce
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const original = this.value;
                    const sanitized = sanitizeText(original);
                    
                    if (original !== sanitized) {
                        this.value = sanitized;
                        warning.classList.remove('d-none');
                        
                        setTimeout(() => {
                            warning.classList.add('d-none');
                        }, 3000);
                    }
                }, 500);
            });
        });
    }

    // ============================================================================
    // INITIALIZATION
    // ============================================================================

    /**
     * Initialize anti-injection protection
     */
    function init() {
        if (CONFIG.debugMode) {
            console.log('Anti-Injection Protection Initialized');
        }

        // Protect all existing forms
        document.querySelectorAll('form').forEach(protectForm);

        // Watch for dynamically added forms
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.tagName === 'FORM') {
                            protectForm(node);
                        } else if (node.querySelectorAll) {
                            node.querySelectorAll('form').forEach(protectForm);
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ============================================================================
    // PUBLIC API
    // ============================================================================

    window.AntiInjection = {
        sanitizeText,
        sanitizeEmail,
        sanitizeNumber,
        validateFile,
        protectForm,
        config: CONFIG
    };

})();
