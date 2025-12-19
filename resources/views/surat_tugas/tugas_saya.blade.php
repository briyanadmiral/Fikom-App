@extends('layouts.app')
@section('title', 'Surat Tugas Saya')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <style>
        /* --- Style Asli Anda (Dipertahankan & Disamakan) --- */
        body {
            background: #f7faff;
        }

        .surat-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem 1.3rem 1.8rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem;
        }

        .surat-header .icon {
            background: linear-gradient(135deg, #1498ff 0, #1fc8ff 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #1498ff30;
            font-size: 2rem;
        }

        .surat-header-title {
            font-weight: bold;
            color: #0056b3;
            font-size: 1.85rem;
            margin-bottom: .13rem;
            letter-spacing: -1px;
        }

        .surat-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        /* Statistik Cards */
        .stat-wrapper {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            margin-bottom: 2.1rem;
            flex-wrap: wrap;
        }

        .stat-card {
            width: 170px;
            border-radius: .85rem;
            border: none;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card .card-body {
            text-align: center;
            padding: 1.15rem 1rem;
        }

        .stat-card .icon {
            font-size: 2.3rem;
            margin-bottom: .5rem;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: .83rem;
            margin-bottom: .25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.1;
        }

        /* --- KARTU TABEL --- */
        .card.data-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .card.data-card .card-body {
            padding: 1.5rem;
        }

        /* Data Card Header (Workspace Pill) */
        .data-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e0e6ed;
            border-radius: 1rem 1rem 0 0;
            padding: .85rem 1.25rem;
        }

        .data-card-header-left {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .workspace-pill {
            display: inline-flex;
            align-items: center;
            padding: .45rem 1.1rem;
            border-radius: 999px;
            background: #ffffff;
            font-size: .95rem;
            font-weight: 600;
            color: #004085;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
        }

        .workspace-pill i {
            margin-right: .4rem;
            color: #1498ff;
            font-size: .95rem;
        }

        .data-card-header-right .btn {
            margin-left: .35rem;
        }

        /* Styling Tabel */
        #table-tugas {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
        }

        #table-tugas thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
            white-space: nowrap;
        }

        #table-tugas tbody td {
            vertical-align: middle !important;
            padding: 0.75rem;
            color: #333;
        }

        #table-tugas tbody tr:hover {
            background-color: #f5f9ff;
        }

        /* Kolom alignment */
        #table-tugas th:nth-child(1),
        #table-tugas th:nth-child(4),
        #table-tugas th:nth-child(7),
        #table-tugas th:nth-child(8),
        #table-tugas td:nth-child(1),
        #table-tugas td:nth-child(4),
        #table-tugas td:nth-child(7),
        #table-tugas td:nth-child(8) {
            text-align: center;
        }

        /* Badge Pill */
        .badge-pill {
            padding: 0.45rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        /* Filter Card */
        .filter-card-user {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .filter-card-user .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f1f5f9;
            border-radius: 1rem 1rem 0 0;
            padding: 1rem 1.5rem;
        }

        /* DataTables Cleanup */
        div.dataTables_wrapper div.dataTables_length,
        div.dataTables_wrapper div.dataTables_filter {
            display: none !important;
        }

        div.dataTables_wrapper .row:first-child {
            margin-bottom: 0.75rem;
        }

        div.dataTables_wrapper div.dataTables_info {
            padding-top: 0.8rem;
            font-size: 0.85rem;
            color: #6c757d;
        }

        div.dataTables_wrapper div.dataTables_paginate {
            padding-top: 0.4rem;
        }

        .table-responsive > #table-tugas {
            margin-bottom: 0;
        }

        /* Responsiveness */
        @media (max-width: 767.98px) {
            .surat-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem 1rem;
                gap: .7rem;
            }

            .stat-wrapper {
                gap: .8rem;
                justify-content: center;
            }

            .stat-card {
                width: 100%;
            }

            .data-card-header {
                border-radius: .6rem .6rem 0 0;
                padding: .7rem .9rem;
            }
        }
    </style>
@endpush

@section('content_header')
    <div class="container-fluid px-2">
    <div class="surat-header mt-2 mb-3">
        <span class="icon">
            <i class="fas fa-user-shield text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">Surat Tugas Saya</div>
            <div class="surat-header-desc">
                Daftar semua <b>surat tugas</b> yang ditujukan ke Anda. Lihat detail, download PDF, serta pantau status
                surat di sini.
            </div>
        </div>
    </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-2">

        {{-- 1. STATISTIK STYLE ASLI --}}
        <div class="d-flex justify-content-center w-100 mb-3">
            <div class="stat-wrapper w-100">
                @foreach ([
                    'draft' => ['icon' => 'fa-file-alt', 'label' => 'Draft', 'count' => $stats['draft'] ?? 0, 'color' => 'secondary'],
                    'pending' => ['icon' => 'fa-hourglass-half', 'label' => 'Pending', 'count' => $stats['pending'] ?? 0, 'color' => 'warning'],
                    'disetujui' => ['icon' => 'fa-check-circle', 'label' => 'Disetujui', 'count' => $stats['disetujui'] ?? 0, 'color' => 'success'],
                ] as $status => $info)
                    <div class="stat-card card">
                        <div class="card-body">
                            <div class="icon text-{{ $info['color'] }}">
                                <i class="fas {{ $info['icon'] }}"></i>
                            </div>
                            <div class="label">{{ $info['label'] }}</div>
                            <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 2. FILTER & PENCARIAN (Sederhana) --}}
        <div class="card filter-card-user mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 font-weight-bold text-primary">
                        <i class="fas fa-filter mr-2"></i>Filter & Pencarian
                    </h5>

                    {{-- Tombol Tambah hanya untuk Admin TU --}}
                    @if (auth()->user()->peran_id === 1)
                        <div>
                            <a href="{{ route('surat_tugas.create') }}"
                               class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Buat Surat
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="small font-weight-bold text-muted text-uppercase">Pencarian</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input id="globalSearch" type="text" class="form-control border-left-0"
                                   placeholder="Cari nomor atau perihal...">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <label class="small font-weight-bold text-muted text-uppercase">Status</label>
                        <select id="statusFilter" class="form-control custom-select">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="disetujui">Disetujui</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="d-none d-md-block small">&nbsp;</label>
                        <button id="resetFilters" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-redo mr-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. TABEL UTAMA --}}
        <div class="card data-card shadow-sm">
            {{-- HEADER KARTU: workspace + tombol aksi cepat --}}
            <div class="data-card-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="data-card-header-left">
                    <span class="workspace-pill">
                        <i class="fas fa-briefcase"></i>
                        Ruang Kerja Surat Tugas
                    </span>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-tugas" class="table table-hover" style="width:100%">
                        <thead>
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th>Nomor Surat</th>
                                <th>Perihal</th>
                                <th>Tgl Surat</th>
                                <th>Pembuat</th>
                                <th>Penerima</th>
                                <th>Status</th>

                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($list as $h)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="font-weight-bold">{{ $h->nomor ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($h->nama_umum, 50) }}</td>

                                    {{-- Tanggal --}}
                                    <td class="text-center" data-sort="{{ optional($h->tanggal_surat)->timestamp ?? 0 }}">
                                        @if ($h->tanggal_surat)
                                            {{ $h->tanggal_surat->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>{{ $h->pembuat?->nama_lengkap ?? '-' }}</td>

                                    {{-- Penerima --}}
                                    <td>
                                        @php
                                            $penerima = $h->penerima->pluck('pengguna.nama_lengkap')->filter();
                                            $count = $penerima->count();
                                        @endphp
                                        @if ($count > 0)
                                            {{ $penerima->first() }}
                                            @if ($count > 1)
                                                <span class="badge badge-info ml-1"
                                                      title="Total Penerima">+{{ $count - 1 }}</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                            $colors = [
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                            ];
                                            $color = $colors[$h->status_surat] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-pill badge-{{ $color }}">
                                            {{ ucfirst($h->status_surat) }}
                                        </span>
                                    </td>



                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button"
                                                    data-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">

                                                {{-- Quick View --}}
                                                <a class="dropdown-item text-info quick-view" href="#"
                                                   data-url="{{ route('surat_tugas.preview', $h->id) }}">
                                                    <i class="fas fa-search mr-2"></i> Lihat Cepat
                                                </a>

                                                {{-- Detail --}}
                                                <a class="dropdown-item text-primary"
                                                   href="{{ route('surat_tugas.show', $h->id) }}">
                                                    <i class="fas fa-eye mr-2"></i> Detail
                                                </a>

                                                {{-- Download jika disetujui --}}
                                                @if ($h->status_surat == 'disetujui')
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger"
                                                       href="{{ route('surat_tugas.downloadPdf', $h->id) }}"
                                                       target="_blank">
                                                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
                                                    </a>
                                                @endif

                                                @if (in_array($h->status_surat, ['draft', 'ditolak']))
                                                    @can('update', $h)
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-warning"
                                                           href="{{ route('surat_tugas.edit', $h->id) }}">
                                                            <i class="fas fa-edit mr-2"></i> Edit / Koreksi
                                                        </a>
                                                    @endcan
                                                @endif

                                                {{-- Hapus (Jika Policy Mengizinkan) --}}
                                                @if (in_array($h->status_surat, ['draft', 'ditolak']))
                                                    @can('delete', $h)
                                                        <a class="dropdown-item text-danger btn-delete" href="#"
                                                           data-url="{{ route('surat_tugas.destroy', $h->id) }}">
                                                            <i class="fas fa-trash mr-2"></i> Hapus Draft
                                                        </a>
                                                    @endcan
                                                @endif

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Quick View --}}
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pratinjau Surat Tugas</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <div class="spinner-border text-primary quickview-spinner"
                         style="position:absolute;top:50%;left:50%;"></div>
                    <iframe src="about:blank" style="width:100%;height:100%;border:none;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('surat_tugas.partials._scripts_shared', [
        'mode' => 'user',
        'tableId' => '#table-tugas',
        'searchSelector' => '#globalSearch',
        'statusFilterSelector' => '#statusFilter',
        'resetBtnSelector' => '#resetFilters',
        'orderHeaderText' => 'tgl surat',
        'statusHeaderText' => 'status',
        'nonOrderableHeaders' => ['Aksi'],
        'enableQuickView' => true,
        'quickView' => ['modalId' => '#quickViewModal', 'triggerSelector' => '.quick-view'],
        'enableDelete' => false,
        'i18nUrl' => '/assets/datatables/i18n/id.json',
        'emptyDefaultMsg' => 'Tidak ada surat tugas untuk Anda.',
        'moduleName' => 'Surat Tugas',
    ])
@endpush
