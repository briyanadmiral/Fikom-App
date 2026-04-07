<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name', 'Arsip Surat FIKOM'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="FIKOM UNIKA">
    <meta name="description" content="Sistem Informasi Surat Fakultas Ilmu Komputer">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    {{-- CSS Vendors --}}
    {{-- Font Awesome 6 (CDN dengan fallback) --}}
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer"
          onerror="this.onerror=null;this.href='{{ asset('vendor/fontawesome-free/css/all.min.css') }}';">
    
    {{-- AdminLTE 3 Theme --}}
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    {{-- Custom Styles dari Child Views --}}
    @stack('styles')
    
    {{-- Custom Global Styles --}}
    <style>
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-overlay.active {
            display: flex;
        }
        .loading-spinner {
            color: #fff;
            font-size: 2rem;
        }
        
        /* Fix AdminLTE Sidebar pada Mobile */
        @media (max-width: 767.98px) {
            .content-wrapper {
                margin-left: 0 !important;
            }
        }
        
        /* Accessibility Improvements */
        .skip-to-content {
            position: absolute;
            top: -40px;
            left: 0;
            background: #007bff;
            color: white;
            padding: 8px;
            text-decoration: none;
            z-index: 100;
        }
        .skip-to-content:focus {
            top: 0;
        }

        /* === TEMA GLASSMORPHISM (GREY UI/UX) OVERRIDES UNTUK ADMINLTE === */
        body {
            background: #e4e7ec !important;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%) !important;
            background-attachment: fixed !important;
            color: #333333 !important;
        }
        .wrapper, .content-wrapper, .main-footer {
            background-color: transparent !important;
            border: none !important;
        }
        /* Navbar (Header) */
        .main-header {
            background: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.7) !important;
        }
        /* Sidebar */
        .main-sidebar {
            background: rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.7) !important;
        }
        .sidebar a, .brand-text {
            color: #3a4252 !important;
        }
        .sidebar a:hover {
            color: #000 !important;
            background: rgba(255, 255, 255, 0.6) !important;
        }
        .brand-link {
            border-bottom: 1px solid rgba(255, 255, 255, 0.7) !important;
        }
        /* Cards */
        .card {
            background: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07) !important;
        }
        .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.7) !important;
            color: #3a4252 !important;
        }
        /* Table */
        table, .table {
            background: transparent !important;
        }
        table th, table td, .table th, .table td {
            background: transparent !important;
            color: #333333 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.5) !important;
        }
        table thead th, .table thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.7) !important;
        }
        /* Buttons & Badges & Info Boxes (Glassmorphism) */
        .btn-primary, .btn-info, .btn-success, .btn-warning, .btn-danger, .btn-default,
        .bg-primary, .bg-info, .bg-success, .bg-warning, .bg-danger, .info-box, .small-box {
            background: rgba(255, 255, 255, 0.5) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            color: #3a4252 !important;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05) !important;
            transition: all 0.3s ease !important;
        }
        .btn-primary:hover, .btn-info:hover, .btn-success:hover, .btn-warning:hover, .btn-danger:hover, .btn-default:hover {
            background: rgba(255, 255, 255, 0.8) !important;
            color: #000 !important;
            transform: translateY(-2px) !important;
            border-color: #8a9ccc !important;
        }
        .small-box .icon > i {
            color: rgba(58, 66, 82, 0.3) !important;
        }
        /* Overrides form controls */
        .form-control {
            background: rgba(255, 255, 255, 0.5) !important;
            backdrop-filter: blur(5px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            color: #333 !important;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.8) !important;
            border-color: #8a9ccc !important;
            box-shadow: 0 0 0 0.2rem rgba(138, 156, 204, 0.25) !important;
        }
        .text-white { color: #3a4252 !important; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    {{-- Skip to Content (Accessibility) --}}
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    {{-- Loading Overlay --}}
    <div class="loading-overlay" id="globalLoadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p class="mt-2">Memproses...</p>
        </div>
    </div>

    <div class="wrapper">
        {{-- Navbar --}}
        @include('layouts.navbar')
        
        {{-- Sidebar --}}
        @include('layouts.sidebar')
        
        {{-- Content Wrapper --}}
        <div class="content-wrapper pt-2">
            {{-- Content Header --}}
            <section class="content-header">
                <div class="container-fluid">
                    @yield('content_header')
                </div>
            </section>
            
            {{-- Main Content --}}
            <section class="content" id="main-content">
                @yield('content')
            </section>
        </div>
        
        {{-- Footer --}}
        <footer class="main-footer small">
            <div class="float-right d-none d-sm-inline">
                <strong>Version:</strong> 1.0.0 | <strong>Environment:</strong> {{ config('app.env') }}
            </div>
            <strong>&copy; {{ date('Y') }} <a href="{{ url('/') }}">{{ config('app.name') }}</a>.</strong> 
            All rights reserved.
        </footer>
        
        {{-- Control Sidebar (Optional) --}}
        <aside class="control-sidebar control-sidebar-dark">
            {{-- Control sidebar content goes here --}}
        </aside>
    </div>

    {{-- ========================================
         JAVASCRIPT SECTION
    ========================================= --}}
    
    {{-- jQuery 3.7.1 (CDN dengan Fallback) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous"></script>
    <script>
        // Fallback ke jQuery lokal jika CDN gagal
        window.jQuery || document.write('<script src="{{ asset('vendor/jquery/jquery.min.js') }}"><\/script>');
    </script>
    
    {{-- Bootstrap 4.6.2 Bundle (includes Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" 
            crossorigin="anonymous"
            onerror="this.onerror=null;this.src='{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}';">
    </script>
    
    {{-- AdminLTE 3 App --}}
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    
    {{-- SweetAlert2 11.x --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Anti-Injection Security Script --}}
    <script src="{{ asset('js/anti-injection.js') }}"></script>
    
    {{-- Custom Scripts dari Child Views --}}
    @stack('scripts')
    
    {{-- ========================================
         GLOBAL JAVASCRIPT INITIALIZATION
    ========================================= --}}
    <script>
        (function($) {
            'use strict';
            
            // ===== CSRF Token Setup untuk AJAX =====
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                error: function(xhr, status, error) {
                    // Handle Session Expired (419)
                    if (xhr.status === 419) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Sesi Habis',
                            text: 'Sesi Anda telah berakhir. Halaman akan di-refresh.',
                            showConfirmButton: true,
                            allowOutsideClick: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                    // Handle Unauthorized (401)
                    else if (xhr.status === 401) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tidak Terautentikasi',
                            text: 'Silakan login kembali.',
                            showConfirmButton: true
                        }).then(() => {
                            window.location.href = '{{ route("login") }}';
                        });
                    }
                    // Handle Forbidden (403)
                    else if (xhr.status === 403) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Akses Ditolak',
                            text: 'Anda tidak memiliki izin untuk mengakses resource ini.',
                            showConfirmButton: true
                        });
                    }
                    // Handle Server Error (500)
                    else if (xhr.status >= 500) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Server',
                            text: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
                            showConfirmButton: true
                        });
                    }
                }
            });
            
            // ===== Global Loading Overlay Helper =====
            window.showLoading = function(message = 'Memproses...') {
                $('#globalLoadingOverlay').addClass('active').find('p').text(message);
            };
            
            window.hideLoading = function() {
                $('#globalLoadingOverlay').removeClass('active');
            };

            // Fix Back-Forward Cache (BFCache) issue where overlay stays active
            window.addEventListener('pageshow', function(event) {
                if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
                    hideLoading();
                }
            });
            
            // ===== Form Submission Loading =====
            $('form[data-loading="true"]').on('submit', function() {
                showLoading('Mengirim data...');
            });
            
            // ===== Tooltip & Popover Initialization =====
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
            
            // ===== Confirm Delete dengan SweetAlert =====
            $(document).on('click', '.btn-delete, [data-confirm="delete"]', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const itemName = $(this).data('item-name') || 'data ini';
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus ${itemName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        showLoading('Menghapus data...');
                        form.submit();
                    }
                });
            });

            // ===== Generic Confirm Action =====
            $(document).on('click', '[data-confirm-message]', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const message = $(this).data('confirm-message');
                const title = $(this).data('confirm-title') || 'Konfirmasi';
                const icon = $(this).data('confirm-icon') || 'question';
                const confirmText = $(this).data('confirm-text') || 'Ya, Lanjutkan';
                const confirmColor = $(this).data('confirm-color') || '#3085d6';

                Swal.fire({
                    title: title,
                    text: message,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: confirmColor,
                    cancelButtonColor: '#d33',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        if(form.length > 0) {
                            showLoading('Memproses...');
                            form.submit();
                        }
                    }
                });
            });
            
            // ===== Prevent Double Submit =====
            $('form').on('submit', function() {
                const $submitBtn = $(this).find('button[type="submit"], input[type="submit"]');
                $submitBtn.prop('disabled', true);
                
                // Re-enable setelah 5 detik (safety net)
                setTimeout(() => {
                    $submitBtn.prop('disabled', false);
                }, 5000);
            });
            
            // ===== Auto-dismiss Alert =====
            $('.alert[data-auto-dismiss]').each(function() {
                const timeout = $(this).data('auto-dismiss') || 5000;
                const $alert = $(this);
                setTimeout(() => {
                    $alert.fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, timeout);
            });
            
            // ===== Number Input Formatter =====
            $('input[data-format="number"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                $(this).val(value);
            });
            
            $('input[data-format="currency"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = new Intl.NumberFormat('id-ID').format(value);
                $(this).val(value);
            });
            
            // ===== Print Helper =====
            window.printElement = function(elementId) {
                const printContents = document.getElementById(elementId).innerHTML;
                const originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload(); // Reload to restore event listeners
            };
            
        })(jQuery);
        
        // ===== DOMContentLoaded Events =====
        document.addEventListener('DOMContentLoaded', function() {
            
            // ===== Success Notification =====
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: {!! json_encode(session('success')) !!},
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            @endif
            
            // ===== Error Notification =====
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: {!! json_encode(session('error')) !!},
                    showConfirmButton: true,
                    confirmButtonText: 'Tutup'
                });
            @endif
            
            // ===== Warning Notification =====
            @if (session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: {!! json_encode(session('warning')) !!},
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            @endif
            
            // ===== Info Notification =====
            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: {!! json_encode(session('info')) !!},
                    timer: 3500,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                });
            @endif
            
            // ===== Validation Errors =====
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '<ul style="text-align: left; padding-left: 20px;">' +
                        @foreach($errors->all() as $error)
                            '<li>{!! addslashes($error) !!}</li>' +
                        @endforeach
                        '</ul>',
                    showConfirmButton: true,
                    confirmButtonText: 'Perbaiki',
                    width: '600px'
                });
            @endif
            
        });
        
        // ===== Service Worker Registration (Optional - untuk PWA) =====
        @if(config('app.env') === 'production')
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful');
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
        @endif
        
    </script>
    
    {{-- Environment Indicator (Only in Development) --}}
    @if(config('app.env') !== 'production')
    <script>
        console.log('%c🚀 DEVELOPMENT MODE', 'background: #222; color: #bada55; font-size: 20px; padding: 10px;');
        console.log('%c⚠️ Debug Mode: ON', 'background: #ff6347; color: white; font-size: 14px; padding: 5px;');
    </script>
    @endif
    
</body>
</html>
