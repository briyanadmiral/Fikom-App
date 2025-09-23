<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Arsip Surat SIEGA'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- AdminLTE CSS & FontAwesome -->
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

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper pt-2">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                @yield('content_header')
            </div>
        </section>
        <!-- Main content -->
        <section class="content">
            @yield('content')
        </section>
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer small">
        <div class="float-right d-none d-sm-inline">v1.0</div>
        <strong>&copy; {{ date('Y') }} {{ config('app.name') }}</strong>
    </footer>
</div>

<!-- AdminLTE JS, jQuery, Bootstrap -->
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
</body>
</html>
