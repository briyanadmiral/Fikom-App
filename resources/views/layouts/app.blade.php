<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Arsip Surat SIEGA'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- Navbar --}}
    @include('layouts.navbar')

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    <div class="content-wrapper pt-2">
        <section class="content-header">
            <div class="container-fluid">
                @yield('content_header')
            </div>
        </section>
        <section class="content">
            @yield('content')
        </section>
    </div>
    <footer class="main-footer small">
        <div class="float-right d-none d-sm-inline">v1.0</div>
        <strong>&copy; {{ date('Y') }} {{ config('app.name') }}</strong>
    </footer>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('js/anti-injection.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- [PERBAIKAN] Blok notifikasi HTML dihapus dari sini dan dipindahkan ke dalam skrip di bawah --}}

@stack('scripts')

{{-- [PERBAIKAN] Tambahkan skrip ini untuk menangani notifikasi secara terpusat menggunakan SweetAlert2 --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 2500,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                showConfirmButton: true // Tampilkan tombol OK untuk pesan error
            });
        @endif
    });
</script>

</body>
</html>