@extends('layouts.app')
@section('title', 'Daftar Surat Tugas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        body {
            background: #f7faff
        }

        .surat-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem 1.3rem 1.8rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem
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
            font-size: 2rem
        }

        .surat-header-title {
            font-weight: bold;
            color: #0056b3;
            font-size: 1.85rem;
            margin-bottom: .13rem;
            letter-spacing: -1px
        }

        .surat-header-desc {
            color: #636e7b;
            font-size: 1.03rem
        }

        .stat-wrapper {
            display: flex;
            justify-content: flex-start;
            gap: 1.2rem;
            margin-bottom: 2.1rem;
            flex-wrap: wrap
        }

        .stat-card {
            width: 170px;
            border-radius: .85rem;
            border: none;
            background: #fff
        }

        .stat-card .card-body {
            text-align: center;
            padding: 1.15rem 1rem
        }

        .stat-card .icon {
            font-size: 2.3rem;
            margin-bottom: .5rem
        }

        .stat-card .label {
            color: #6c757d;
            font-size: .83rem;
            margin-bottom: .25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px
        }

        .stat-card .value {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.1
        }

        .card.filter-card {
            margin-bottom: 2.2rem;
            border-radius: 1rem
        }

        .card.filter-card .card-header {
            background: #f8fafc;
            border-radius: 1rem 1rem 0 0;
            border: none
        }

        .card.filter-card .card-body {
            padding-bottom: .7rem
        }

        .card.data-card {
            border-radius: 1rem
        }

        .card.data-card .card-body {
            padding-top: 1.2rem
        }

        .table th,
        .table td {
            vertical-align: middle !important
        }

        .table {
            background: #fff
        }

        /* ✅ Dropdown colored items */
        .dropdown-menu .dropdown-item {
            cursor: pointer;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .dropdown-menu .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }

        /* Default colors */
        .dropdown-item.text-info {
            color: #17a2b8 !important;
        }

        .dropdown-item.text-warning {
            color: #ffc107 !important;
        }

        .dropdown-item.text-success {
            color: #28a745 !important;
        }

        .dropdown-item.text-danger {
            color: #dc3545 !important;
        }

        .dropdown-item.text-primary {
            color: #007bff !important;
        }

        .dropdown-item.text-purple {
            color: #6f42c1 !important;
        }

        /* Hover effects */
        .dropdown-item.text-info:hover {
            background-color: #17a2b8 !important;
            color: white !important;
        }

        .dropdown-item.text-warning:hover {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .dropdown-item.text-success:hover {
            background-color: #28a745 !important;
            color: white !important;
        }

        .dropdown-item.text-danger:hover {
            background-color: #dc3545 !important;
            color: white !important;
        }

        .dropdown-item.text-primary:hover {
            background-color: #007bff !important;
            color: white !important;
        }

        .dropdown-item.text-purple:hover {
            background-color: #6f42c1 !important;
            color: white !important;
        }

        .dropdown-item.text-warning:hover i {
            color: #212529 !important;
        }

        .dropdown-item:hover i {
            color: inherit !important;
        }

        .badge-pill {
            padding: 0.45rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.3px
        }

        .badge-info {
            background: #0bb1e3 !important;
            color: #fff
        }

        .quickview-spinner {
            position: absolute;
            top: 48%;
            left: 48%;
            z-index: 10;
            display: none
        }

        @media (max-width:767.98px) {
            .surat-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem 1rem;
                gap: .7rem
            }

            .stat-wrapper {
                flex-direction: column;
                gap: .8rem
            }

            .stat-card {
                width: 100%
            }

            .surat-header-title {
                font-size: 1.18rem
            }

            .surat-header-desc {
                font-size: .99rem
            }

            .card.filter-card,
            .card.data-card {
                border-radius: .6rem
            }
        }
    </style>
@endpush

@section('content_header')
    @php
        $mode = $mode ?? (request()->routeIs('surat_tugas.approveList') ? 'approve-list' : 'list');
    @endphp
    <div class="surat-header mt-2 mb-3">
        <span class="icon">
            <i class="fas fa-envelope-open-text text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">
                {{ $mode === 'approve-list' ? 'Daftar Surat Menunggu Persetujuan Anda' : 'Daftar Surat Tugas' }}
            </div>
            <div class="surat-header-desc">
                @if ($mode === 'approve-list')
                    Hanya menampilkan surat dengan status <b>pending</b> yang menunggu persetujuan Anda.
                @else
                    Semua surat tugas <b>sekolah</b> — kelola, filter, cetak PDF, dan lacak statusnya di sini.
                @endif
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-2">
        {{-- Statistik --}}
        <div class="d-flex justify-content-center w-100 mb-3">
            <div class="stat-wrapper py-1" style="width:100%;max-width:650px;">
                @foreach ([
            'draft' => ['icon' => 'fa-file-alt', 'label' => 'Draft', 'count' => $stats['draft'] ?? 0, 'color' => 'secondary'],
            'pending' => ['icon' => 'fa-hourglass-half', 'label' => 'Pending', 'count' => $stats['pending'] ?? 0, 'color' => 'warning'],
            'disetujui' => ['icon' => 'fa-check-circle', 'label' => 'Disetujui', 'count' => $stats['disetujui'] ?? 0, 'color' => 'success'],
        ] as $status => $info)
                    <div class="stat-card card shadow-sm mx-2">
                        <div class="card-body">
                            <div class="icon text-{{ $info['color'] }}" data-toggle="tooltip" title="{{ $info['label'] }}">
                                <i class="fas {{ $info['icon'] }}"></i>
                            </div>
                            <div class="label">{{ $info['label'] }}</div>
                            <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Filter dan Tombol --}}
        <div class="card filter-card mb-4 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
                    </h5>
                    @if ($mode !== 'approve-list')
                        <div class="d-flex flex-wrap gap-2">
                            {{-- Tombol Tambah Surat --}}
                            <a href="{{ route('surat_tugas.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Tambah Surat Tugas
                            </a>

                            {{-- 🆕 Tombol Jenis Surat Tugas --}}
                            <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-folder mr-2"></i>Jenis Surat Tugas
                            </a>

                            {{-- 🆕 Tombol Klasifikasi Surat --}}
                            <a href="{{ route('klasifikasi_surat.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-folder-open mr-2"></i>Klasifikasi Surat
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form class="row">
                    <div class="col-md-6 form-group mb-2">
                        <input id="globalSearch" type="text" class="form-control"
                            placeholder="Cari berdasarkan nomor, perihal, pembuat, atau penerima...">
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <select id="statusFilter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
                            <i class="fas fa-redo mr-1"></i>Reset Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>


        {{-- Tabel Utama --}}
        <div class="card data-card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-tugas" class="table table-hover" style="width:100%">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nomor Surat</th>
                                <th>Perihal</th>
                                <th>Tgl Surat</th>
                                <th>Pembuat</th>
                                <th>Penerima</th>
                                <th>Status</th>
                                <th>Berkas</th>
                                <th style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $h)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $h->nomor ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($h->nama_umum, 60) }}</td>

                                    @php $tgl = $h->tanggal_surat; @endphp
                                    <td class="text-center" data-sort="{{ $tgl ? $tgl->timestamp : 0 }}">
                                        {{ $tgl ? $tgl->format('d M Y') : '-' }}
                                        @if ($tgl)
                                            <br><small class="text-muted"><i class="far fa-clock"></i>
                                                {{ $tgl->diffForHumans() }}</small>
                                        @endif
                                    </td>

                                    <td>{{ $h->pembuat?->nama_lengkap ?? 'N/A' }}</td>

                                    <td>
                                        @php
                                            $penerima = $h->penerima->pluck('pengguna.nama_lengkap')->filter();
                                            $penerimaCount = $penerima->count();
                                        @endphp
                                        @if ($penerimaCount > 0)
                                            {{ $penerima->first() }}
                                            @if ($penerimaCount > 1)
                                                <span class="badge badge-info ml-1" data-toggle="tooltip"
                                                    title="Total penerima">+{{ $penerimaCount - 1 }} lainnya</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @php
                                            $badgeMap = [
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                            ];
                                            $badge = $badgeMap[$h->status_surat] ?? 'secondary';
                                        @endphp
                                        <span
                                            class="badge badge-pill badge-{{ $badge }}">{{ ucfirst($h->status_surat) }}</span>
                                    </td>

                                    <td class="text-center">
                                        @if ($h->status_surat == 'disetujui' && $h->signed_pdf_path)
                                            <a href="{{ route('surat_tugas.downloadPdf', $h->id) }}"
                                                class="btn btn-sm btn-danger" title="Download PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- ✅ DROPDOWN WITH COLORED ACTIONS --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">

                                                {{-- 1. Quick View --}}
                                                <a class="dropdown-item text-info quick-view"
                                                    href="{{ route('surat_tugas.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}"
                                                    data-url="{{ route('surat_tugas.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}">
                                                    <i class="fas fa-search"></i> Lihat Cepat
                                                </a>

                                                {{-- 2. Detail Page --}}
                                                <a class="dropdown-item text-info"
                                                    href="{{ route('surat_tugas.show', $h->id) }}">
                                                    <i class="fas fa-eye"></i> Halaman Detail
                                                </a>

                                                <div class="dropdown-divider"></div>

                                                {{-- ✅ 3. EDIT DRAFT (Warning/Kuning) - Tambahkan ini! --}}
                                                @if ($h->status_surat === 'draft' && (int) $h->dibuat_oleh === (int) auth()->id())
                                                    <a class="dropdown-item text-warning"
                                                        href="{{ route('surat_tugas.edit', $h->id) }}">
                                                        <i class="fas fa-edit"></i> Edit Draft
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                @endif

                                                {{-- 4. Tinjau & Setujui (untuk pending approver) --}}
                                                @if ($h->status_surat === 'pending' && (int) $h->next_approver === (int) auth()->id())
                                                    <a class="dropdown-item text-success"
                                                        href="{{ route('surat_tugas.approveForm', $h->id) }}">
                                                        <i class="fas fa-check-circle"></i> Tinjau & Setujui
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                @endif

                                                {{-- 5. Edit/Koreksi (untuk approver) --}}
                                                @can('edit-surat', $h)
                                                    <a class="dropdown-item text-warning"
                                                        href="{{ route('surat_tugas.edit', ['tugas' => $h->id, 'mode' => 'koreksi']) }}">
                                                        <i class="fas fa-pen"></i> Koreksi (Approver)
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                @endcan

                                                {{-- 6. Download PDF --}}
                                                @if ($h->status_surat == 'disetujui' && $h->signed_pdf_path)
                                                    <a class="dropdown-item text-danger"
                                                        href="{{ route('surat_tugas.downloadPdf', $h->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-file-pdf"></i> Download PDF
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                @endif

                                                {{-- 7. Hapus Draft --}}
                                                @if ($h->status_surat === 'draft' && (int) $h->dibuat_oleh === (int) auth()->id())
                                                    <button type="button"
                                                        class="dropdown-item text-danger w-100 text-left btn-delete"
                                                        data-url="{{ route('surat_tugas.destroy', $h->id) }}"
                                                        data-nomor="{{ $h->nomor ?? '—' }}"
                                                        style="border:none;background:transparent;cursor:pointer">
                                                        <i class="fas fa-trash"></i> Hapus Draft
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                            @endforelse
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
                <div class="modal-body p-0" style="position:relative;">
                    <div class="spinner-border text-primary quickview-spinner"></div>
                    <iframe src="about:blank" style="width:100%;border:none;min-height:70vh"></iframe>
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
    // mode & tabel
    'mode'      => $mode ?? (request()->routeIs('surat_tugas.approveList') ? 'approve-list' : 'list'),
    'tableId'   => '#table-tugas',

    // filter selectors
    'searchSelector'       => '#globalSearch',
    'statusFilterSelector' => '#statusFilter',
    'resetBtnSelector'     => '#resetFilters',

    // kolom (by header text; case-insensitive, dilowercase di JS)
    'orderHeaderText'   => 'tgl surat',
    'statusHeaderText'  => 'status',

    // non-orderable: Berkas & Aksi
    'nonOrderableHeaders' => ['Berkas', 'Aksi'],

    // fitur
    'enableQuickView' => true,
    'quickView'       => ['modalId' => '#quickViewModal', 'triggerSelector' => '.quick-view'],
    'enableDelete'    => true,

    // i18n & pesan kosong
    'i18nUrl'         => '/assets/datatables/i18n/id.json',
    'emptyDefaultMsg' => 'Tidak ada data surat tugas.',
    'emptyApproveMsg' => 'Tidak ada surat yang perlu Anda setujui.',

    // teks konfirmasi
    'moduleName'      => 'Surat Tugas',
  ])
@endpush
