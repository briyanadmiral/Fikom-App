@extends('layouts.app')
@section('title', 'Buat Surat Keputusan')

{{-- ========================================================= --}}
{{-- 🔹 STYLE SECTION (diletakkan di atas agar ter-render di <head>) --}}
{{-- ========================================================= --}}
@push('styles')
    {{-- ============ Library Styles ============ --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --purple-500: #6f42c1;
            --purple-600: #5a33b8;
            --purple-700: #412674;
            --teal-500: #0ab39c;
            --blue-500: #3f8cff;
            --amber-500: #f59f00;
            --green-600: #16a34a;
            --red-500: #ef4444;
            --card-radius: .9rem;
            --input-radius: .55rem;
            --shadow-sm: 0 10px 28px rgba(28, 28, 28, .06);
            --muted: #636e7b;
            --border: #e0e6ed;
            --bg-soft: #f3f6fa;
        }

        /* ====== Page Header ====== */
        .page-header {
            background: var(--bg-soft);
            padding: 1.2rem 1.6rem;
            border-radius: 1.1rem;
            margin-bottom: 1.6rem;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header .icon {
            background: linear-gradient(135deg, var(--purple-500) 0, #9a6ee5 100%);
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 10px #6f42c14d;
            color: #fff;
            font-size: 1.4rem;
        }

        .page-header-title {
            margin: 0;
            font-weight: 700;
            color: var(--purple-700);
            letter-spacing: -.2px;
        }

        .page-header-desc {
            margin: .15rem 0 0;
            color: var(--muted);
            font-size: .98rem;
        }

        /* ====== Cards ====== */
        .card,
        .card-settings {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-sm);
        }

        .card .card-body {
            padding: 1rem 1.1rem;
        }

        .card-settings .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
        }

        /* ====== Form ====== */
        .form-control,
        .custom-select {
            border-radius: var(--input-radius);
        }

        .input-group-text {
            background: #eef1f6;
            border-color: #dfe5ec;
        }

        /* ====== Section Headers ====== */
        .card-h {
            border-bottom: 0;
            color: #fff;
            padding: .85rem 1.1rem;
            border-top-left-radius: var(--card-radius);
            border-top-right-radius: var(--card-radius);
        }

        .card-h--purple {
            background: linear-gradient(135deg, var(--purple-500) 0%, #9a6ee5 100%);
        }

        .card-h--teal {
            background: linear-gradient(135deg, var(--teal-500) 0%, #41d6c3 100%);
        }

        .card-h--blue {
            background: linear-gradient(135deg, var(--blue-500) 0%, #6aa6ff 100%);
        }

        .card-h--amber {
            background: linear-gradient(135deg, var(--amber-500) 0%, #f7b733 100%);
        }

        .card-h--green {
            background: linear-gradient(135deg, var(--green-600) 0%, #34d399 100%);
        }

        .card-h--red {
            background: linear-gradient(135deg, var(--red-500) 0%, #f87171 100%);
        }

        /* ====== QuickNav ====== */
        .text-purple {
            color: var(--purple-500) !important;
        }

        .list-quicknav .list-group-item {
            border: 0;
            border-radius: .6rem;
            padding: .6rem .8rem;
            display: flex;
            align-items: center;
            gap: .55rem;
            transition: .15s ease;
        }

        .list-quicknav .list-group-item:hover {
            background: #f8f7ff;
        }

        .list-quicknav .active {
            background: #f1eaff;
            color: var(--purple-600);
            font-weight: 600;
        }

        a.is-complete {
            background: #eaf9f0 !important;
            color: #146c2e !important;
            border-left: 4px solid #22c55e !important;
        }

        a.has-error {
            background: #fdecec !important;
            color: #b42318 !important;
            border-left: 4px solid #ef4444 !important;
        }

        /* ====== Select2 ====== */
        .select2-container--bootstrap4 .select2-selection {
            min-height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: var(--input-radius);
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background: #f1eaff;
            border: 1px solid #cbb5ff;
            color: var(--purple-600);
            font-weight: 500;
            border-radius: .5rem;
        }

        /* ====== Nomor Builder ====== */
        .nomor-builder {
            display: none;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
        }

        .nomor-builder.show {
            display: block;
        }

        .nomor-builder-preview {
            font-family: "Courier New", monospace;
            background: #e9ecef;
            padding: .25rem .5rem;
            border-radius: 4px;
        }

        /* ====== Dynamic Blocks ====== */
        .diktum-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: .6rem;
            transition: box-shadow .2s ease;
        }

        .diktum-item:focus-within {
            box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
        }

        .menimbang-item .input-group-text,
        .mengingat-item .input-group-text {
            min-width: 34px;
            justify-content: center;
        }

        /* ====== Mobile Action Bar ====== */
        .btn-ghost {
            background: #f6f7fb;
            border: 1px solid #eef0f4;
        }

        .action-bar {
            position: sticky;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 998;
            background: rgba(255, 255, 255, .94);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            border-top: 1px solid #eaeef4;
            padding: .75rem;
        }

        @media (min-width: 992px) {
            .action-bar {
                display: none;
            }
        }

        /* ====== TEMBUSAN ====== */
        .tembusan-wrap {
            border: 1px solid #e9ecef;
            border-radius: .5rem;
            overflow: hidden;
        }

        .tembusan-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 1rem;
            background: linear-gradient(90deg, #5c7cfa 0%, #845ef7 100%);
            color: #fff;
        }

        .tembusan-body {
            padding: 1rem;
            background: #fff;
        }

        .tembusan-preview {
            background: #f8f9ff;
            border: 1px dashed #cdd5ff;
            border-radius: .5rem;
            padding: 1rem;
        }

        .tembusan-preview h6 {
            font-weight: 700;
            color: #3b5bdb;
            margin-bottom: .5rem;
        }

        .tembusan-tools .btn {
            margin-left: .5rem;
        }

        .tagify__tag {
            font-weight: 600;
        }

        .tagify__input {
            min-width: 140px;
        }

        /* ====== Header Box ====== */
        .custom-header-box {
            background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
            color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(44, 62, 80, .13);
            padding: 1.5rem 2rem 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            border-left: 6px solid #3498db;
            margin-top: .5rem;
        }

        .header-icon {
            width: 54px;
            height: 54px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            font-size: 2rem;
            box-shadow: 0 2px 12px 0 rgba(52, 152, 219, .13);
        }

        .header-title {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .header-desc {
            font-size: 1.07rem;
            color: #e9f3fa;
            font-weight: 400;
            margin-left: .1rem;
        }

        @media (max-width: 575.98px) {
            .custom-header-box {
                padding: 1.1rem;
            }

            .header-icon {
                width: 44px;
                height: 44px;
                font-size: 1.2rem;
            }

            .header-title {
                font-size: 1.2rem;
            }

            .header-desc {
                margin-left: 0;
                font-size: .98rem;
            }
        }
    </style>
@endpush

{{-- ========================================================= --}}
{{-- 🔹 HEADER HALAMAN --}}
{{-- ========================================================= --}}
@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-gavel fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Buat Surat Keputusan Baru</div>
                <div class="header-desc mt-2">
                    Isi formulir di bawah untuk membuat surat keputusan baru dengan lengkap. Nomor akan diisikan otomatis
                    jika tersedia.
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- ========================================================= --}}
{{-- 🔹 KONTEN HALAMAN --}}
{{-- ========================================================= --}}
@section('content')
    <div class="container-fluid">
        @can('create', App\Models\KeputusanHeader::class)
            {{-- ⚠️ Error validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <h5><i class="icon fas fa-ban"></i> Gagal Menyimpan!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- ✅ Partial form --}}
            @include('surat_keputusan.partials._form', [
                'mode' => 'create',
                'pejabat' => $pejabat ?? collect(),
                'admins' => $admins ?? collect(),
                'users' => $users ?? collect(),
                'klasifikasi' => $klasifikasi ?? collect(),
                'bulanRomawi' => $bulanRomawi ?? [
                    '',
                    'I',
                    'II',
                    'III',
                    'IV',
                    'V',
                    'VI',
                    'VII',
                    'VIII',
                    'IX',
                    'X',
                    'XI',
                    'XII',
                ],
                'tembusanPresets' => $tembusanPresets ?? [
                    'Yth. Rektor',
                    'Yth. Wakil Rektor I',
                    'Yth. Wakil Rektor II',
                    'Dekan Fakultas Ilmu Komputer',
                    'BAAK',
                    'BAUK',
                    'BAK',
                    'Kepala Program Studi Sistem Informasi',
                    'Unit Kepegawaian',
                    'Arsip',
                ],
                'currentYear' => $currentYear ?? now()->year,
                'currentRomawi' => $currentRomawi ?? null,
                'autoNomor' => $autoNomor ?? '',
            ])
        @else
            {{-- 🚫 Guard akses --}}
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-ban"></i> Akses Ditolak</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Tidak Memiliki Akses</h5>
                        Anda tidak memiliki izin untuk membuat Surat Keputusan baru.
                        Hanya <strong>Admin TU</strong> yang dapat membuat Surat Keputusan.
                    </div>
                    <a href="{{ route('surat_keputusan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endsection
