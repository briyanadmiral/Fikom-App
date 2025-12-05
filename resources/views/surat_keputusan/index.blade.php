@extends('layouts.app')
@section('title', 'Daftar Surat Keputusan')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        body {
            background: #f7faff;
        }

        /* === HEADER ATAS (SAMA SEPERTI SURAT TUGAS) === */
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
            background: linear-gradient(135deg, #6f42c1 0, #9b59b6 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #6f42c130;
            font-size: 2rem;
        }

        .surat-header-title {
            font-weight: bold;
            color: #5a2d91;
            font-size: 1.85rem;
            margin-bottom: .13rem;
            letter-spacing: -1px;
        }

        .surat-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        /* === STATISTIK === */
        .stat-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.2rem;
            margin: 1.5rem 0 2.1rem;
            width: 100%;
            max-width: 1000px;
        }

        .stat-card {
            border-radius: .85rem;
            border: none;
            background: #fff;
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

        /* === FILTER CARD & DATA CARD === */
        .card.filter-card {
            margin-bottom: 2.2rem;
            border-radius: 1rem;
        }

        .card.filter-card .card-header {
            background: #f8fafc;
            border-radius: 1rem 1rem 0 0;
            border: none;
        }

        .card.filter-card .card-body {
            padding-bottom: .7rem;
        }

        .card.data-card {
            border-radius: 1rem;
        }

        .card.data-card .card-body {
            padding-top: 1.2rem;
        }

        /* === HEADER KARTU DATA (RUANG KERJA) === */
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
            color: #4a5568;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
        }

        .workspace-pill i {
            margin-right: .4rem;
            color: #6f42c1;
            font-size: .95rem;
        }

        /* ➕ supaya tombol di kanan ada jarak antar-btn (desktop) */
        .data-card-header-right .btn {
            margin-left: .35rem;
        }

        .data-card-header .badge-info {
            font-size: .8rem;
            border-radius: 999px;
        }

        @media (max-width: 767.98px) {
            .data-card-header {
                border-radius: .6rem .6rem 0 0;
                padding: .7rem .9rem;
            }

            .data-card-header-right {
                width: 100%;
                margin-top: .4rem;
                text-align: left;
            }

            .data-card-header-right .btn {
                width: 100%;
                margin-left: 0;
                margin-bottom: .35rem;
            }
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .table {
            background: #fff;
        }

        /* === DROPDOWN COLORED ITEMS === */
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

        .dropdown-item.text-dark {
            color: #343a40 !important;
        }

        .dropdown-item.text-secondary {
            color: #6c757d !important;
        }

        .dropdown-item.text-info:hover {
            background-color: #17a2b8 !important;
            color: #fff !important;
        }

        .dropdown-item.text-warning:hover {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .dropdown-item.text-success:hover {
            background-color: #28a745 !important;
            color: #fff !important;
        }

        .dropdown-item.text-danger:hover {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .dropdown-item.text-primary:hover {
            background-color: #007bff !important;
            color: #fff !important;
        }

        .dropdown-item.text-dark:hover {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        .dropdown-item.text-secondary:hover {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        .dropdown-item.text-warning:hover i {
            color: #212529 !important;
        }

        .dropdown-item:hover i {
            color: inherit !important;
        }

        .badge-pill {
            padding: .45rem .85rem;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .3px;
        }

        .badge-info {
            background: #0bb1e3 !important;
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .surat-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem 1rem;
                gap: .7rem;
            }

            .stat-wrapper {
                flex-direction: column;
                gap: .8rem;
            }

            .stat-card {
                width: 100%;
            }

            .surat-header-title {
                font-size: 1.18rem;
            }

            .surat-header-desc {
                font-size: .99rem;
            }

            .card.filter-card,
            .card.data-card {
                border-radius: .6rem;
            }
        }
    </style>
@endpush

@section('content_header')
    @php
        // mode dikirim dari controller: 'list', 'approve-list', 'terbit-list', 'arsip-list'
        $mode = $mode ?? (request()->routeIs('surat_keputusan.approveList') ? 'approve-list' : 'list');
    @endphp

    <div class="surat-header mt-2 mb-3">
        <span class="icon">
            <i class="fas fa-gavel text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">
                @if ($mode === 'approve-list')
                    Daftar SK Menunggu Persetujuan Anda
                @elseif ($mode === 'terbit-list')
                    Daftar SK Terbit
                @elseif ($mode === 'arsip-list')
                    Arsip Surat Keputusan
                @else
                    Daftar Surat Keputusan
                @endif
            </div>
            <div class="surat-header-desc">
                @if ($mode === 'approve-list')
                    Hanya menampilkan SK dengan status <b>pending</b> yang menunggu persetujuan Anda.
                @elseif ($mode === 'terbit-list')
                    Menampilkan SK yang sudah <b>terbit</b> dan sedang berlaku.
                @elseif ($mode === 'arsip-list')
                    Menampilkan <b>arsip</b> SK yang sudah tidak aktif namun tetap tersimpan sebagai dokumentasi.
                @else
                    Ruang kerja untuk menyusun, mengajukan, dan memantau progres Surat Keputusan.
                @endif
            </div>
        </div>
    </div>
@endsection

@section('content')

    @php
        // fallback kalau controller belum kirim stats lengkap
        $stats = $stats ?? [
            'draft' => 0,
            'pending' => 0,
            'disetujui' => 0,
            'ditolak' => 0,
            'terbit' => 0,
            'arsip' => 0,
        ];
    @endphp

    <div class="container-fluid px-2">

        {{-- Statistik --}}
        <div class="d-flex justify-content-center w-100 mb-3">
            <div class="stat-wrapper py-1 mx-auto">

                @if ($mode === 'approve-list')
                    @php
                        $pendingForUser = $stats['pending'] ?? (isset($list) ? $list->count() : 0);
                    @endphp
                    <div class="stat-card card shadow-sm mx-2">
                        <div class="card-body">
                            <div class="icon text-warning" data-toggle="tooltip" title="SK Pending untuk Anda">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="label">SK PENDING UNTUK ANDA</div>
                            <div class="value text-warning">{{ $pendingForUser }}</div>
                        </div>
                    </div>
                @elseif ($mode === 'terbit-list')
                    @php
                        $terbitCount = $stats['terbit'] ?? (isset($list) ? $list->count() : 0);
                    @endphp
                    <div class="stat-card card shadow-sm mx-2">
                        <div class="card-body">
                            <div class="icon text-info" data-toggle="tooltip" title="SK Terbit (Berlaku)">
                                <i class="fas fa-share-square"></i>
                            </div>
                            <div class="label">SK TERBIT (BERLAKU)</div>
                            <div class="value text-info">{{ $terbitCount }}</div>
                        </div>
                    </div>
                @elseif ($mode === 'arsip-list')
                    @php
                        $arsipCount = $stats['arsip'] ?? (isset($list) ? $list->count() : 0);
                    @endphp
                    <div class="stat-card card shadow-sm mx-2">
                        <div class="card-body">
                            <div class="icon text-dark" data-toggle="tooltip" title="Arsip SK">
                                <i class="fas fa-archive"></i>
                            </div>
                            <div class="label">ARSIP SK</div>
                            <div class="value text-dark">{{ $arsipCount }}</div>
                        </div>
                    </div>
                @else
                    {{-- Ruang kerja: tampilkan beberapa status utama --}}
                    @foreach ([
                        'draft' => [
                            'icon' => 'fa-file-alt',
                            'label' => 'Draft',
                            'count' => $stats['draft'] ?? 0,
                            'color' => 'secondary',
                        ],
                        'pending' => [
                            'icon' => 'fa-hourglass-half',
                            'label' => 'Pending',
                            'count' => $stats['pending'] ?? 0,
                            'color' => 'warning',
                        ],
                        'disetujui' => [
                            'icon' => 'fa-check-circle',
                            'label' => 'Disetujui',
                            'count' => $stats['disetujui'] ?? 0,
                            'color' => 'success',
                        ],
                        'ditolak' => [
                            'icon' => 'fa-times-circle',
                            'label' => 'Ditolak',
                            'count' => $stats['ditolak'] ?? 0,
                            'color' => 'danger',
                        ],
                    ] as $status => $info)
                        <div class="stat-card card shadow-sm mx-2">
                            <div class="card-body">
                                <div class="icon text-{{ $info['color'] }}" data-toggle="tooltip"
                                     title="{{ $info['label'] }}">
                                    <i class="fas {{ $info['icon'] }}"></i>
                                </div>
                                <div class="label">{{ $info['label'] }}</div>
                                <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>

        {{-- ✅ FASE 1.1: Include Advanced Filter --}}
        @include('surat_keputusan.partials._header_filter')

        {{-- Tabel Utama (kolom penting) --}}
        <div class="card data-card shadow-sm">

            {{-- ✅ Header: judul ruang kerja + info hasil + tombol buat SK --}}
            <div class="data-card-header d-flex justify-content-between align-items-center flex-wrap"">
                <div class="data-card-header-left">
                    <span class="workspace-pill">
                        <i class="fas fa-briefcase"></i>
                        @if ($mode === 'approve-list')
                            SK Menunggu Persetujuan Saya
                        @elseif ($mode === 'terbit-list')
                            SK yang Sudah Terbit
                        @elseif ($mode === 'arsip-list')
                            Arsip Surat Keputusan
                        @else
                            Ruang Kerja Surat Keputusan
                        @endif
                    </span>

                    {{-- ✅ Tampilkan jumlah hasil saat ada filter --}}
                    @if (request()->hasAny(['search', 'status', 'tahun', 'bulan', 'penandatangan', 'pembuat', 'tanggal_dari']))
                        <span class="badge badge-info ml-2">
                            {{ $list->count() }} hasil ditemukan
                        </span>
                    @endif
                </div>

                {{-- ✅ Tombol di kanan, gaya sama seperti Surat Tugas --}}
                <div class="data-card-header-right d-flex flex-wrap justify-content-end">
                    @can('create', App\Models\KeputusanHeader::class)
                        @if ($mode === 'list')
                            <a href="{{ route('surat_keputusan.create') }}" class="btn btn-primary mb-2">
                                <i class="fas fa-plus mr-1"></i> Buat SK Baru
                            </a>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-sk" class="table table-hover" style="width: 100%">

                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nomor SK</th>
                                <th>Tentang (Perihal)</th>
                                <th>Tgl Surat</th>
                                <th>Pembuat</th>
                                <th>Penandatangan</th>
                                <th>Disetujui</th>
                                <th>Terbit</th>
                                <th>Status</th>
                                <th style="width: 80px">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($list as $h)
                                @php
                                    // Parse tanggal surat
                                    $tglSurat = $h->tanggal_surat ? \Carbon\Carbon::parse($h->tanggal_surat) : null;

                                    // Parse tanggal approved
                                    $tglApproved = $h->approved_at ? \Carbon\Carbon::parse($h->approved_at) : null;
                                @endphp

                                <tr>
                                    {{-- No --}}
                                    <td class="text-center">{{ $loop->iteration }}</td>

                                    {{-- Nomor SK --}}
                                    <td>{{ $h->nomor ?? '—' }}</td>

                                    {{-- Tentang / Perihal (dibatasi 60 karakter) --}}
                                    <td>{{ \Illuminate\Support\Str::limit($h->tentang, 60) }}</td>

                                    {{-- Kolom Tgl Surat --}}
                                    <td class="text-center">
                                        @if ($tglSurat instanceof \Carbon\Carbon)
                                            {{ $tglSurat->format('d M Y') }}
                                            <small class="text-muted d-block">
                                                <i class="fas fa-clock"></i> {{ $tglSurat->diffForHumans() }}
                                            </small>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Kolom Pembuat --}}
                                    <td>{{ $h->pembuat?->nama_lengkap ?? 'N/A' }}</td>

                                    {{-- Kolom Penandatangan --}}
                                    <td>{{ $h->penandatanganUser?->nama_lengkap ?? '—' }}</td>

                                    {{-- Kolom Disetujui --}}
                                    <td class="text-center">
                                        @if ($tglApproved instanceof \Carbon\Carbon)
                                            {{ $tglApproved->format('d M Y H:i') }}
                                            <small class="text-muted d-block">
                                                <i class="fas fa-clock"></i> {{ $tglApproved->diffForHumans() }}
                                            </small>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Kolom Terbit --}}
                                    <td class="text-center">
                                        @if ($h->tanggal_terbit instanceof \Carbon\Carbon)
                                            {{ $h->tanggal_terbit->format('d M Y') }}
                                            <small class="text-muted d-block">
                                                {{ $h->penerbit?->nama_lengkap ?? 'N/A' }}
                                            </small>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="text-center">
                                        @php
                                            $badgeMap = [
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                                'terbit' => 'info',
                                                'arsip' => 'dark',
                                            ];
                                            $badge = $badgeMap[$h->status_surat] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-pill badge-{{ $badge }}">
                                            {{ ucfirst($h->status_surat) }}
                                        </span>
                                    </td>

                                    {{-- Kolom Aksi (dropdown) --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                    title="Menu aksi">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">

                                                {{-- 1. Lihat Detail --}}
                                                <a class="dropdown-item text-info"
                                                   href="{{ route('surat_keputusan.show', $h->id) }}">
                                                    <i class="fas fa-eye"></i> Lihat Detail
                                                </a>

                                                {{-- 2. Edit + Ajukan (hanya draft/ditolak) --}}
                                                @if (in_array($h->status_surat, ['draft', 'ditolak']))
                                                    @can('update', $h)
                                                        <a class="dropdown-item text-warning"
                                                           href="{{ route('surat_keputusan.edit', $h->id) }}">
                                                            <i class="fas fa-edit"></i> Edit Draft
                                                        </a>
                                                    @endcan

                                                    @if ($h->status_surat === 'draft')
                                                        @can('submit', $h)
                                                            <div class="dropdown-divider"></div>
                                                            <form action="{{ route('surat_keputusan.submit', $h->id) }}"
                                                                  method="POST" style="display: inline;">
                                                                @csrf
                                                                <button type="button"
                                                                        class="dropdown-item text-success w-100 text-left btn-submit-sk"
                                                                        data-nomor="{{ $h->nomor ?? '—' }}">
                                                                    <i class="fas fa-paper-plane"></i> Ajukan untuk Persetujuan
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    @endif
                                                    <div class="dropdown-divider"></div>
                                                @endif

                                                {{-- 3. Approve / Reject / Reopen (pending) --}}
                                                @if ($h->status_surat === 'pending')
                                                    @can('approve', $h)
                                                        <a class="dropdown-item text-success"
                                                           href="{{ route('surat_keputusan.approveForm', $h->id) }}">
                                                            <i class="fas fa-check-circle"></i> Tinjau & Setujui
                                                        </a>
                                                        @can('reject', $h)
                                                            <a href="#" class="dropdown-item text-danger btn-reject"
                                                               data-action="{{ route('surat_keputusan.reject', $h->id) }}"
                                                               data-nomor="{{ $h->nomor ?? '—' }}">
                                                                <i class="fas fa-times"></i> Tolak / Minta Revisi
                                                            </a>
                                                        @endcan
                                                        <div class="dropdown-divider"></div>
                                                    @endcan

                                                    @can('reopen', $h)
                                                        <a href="#" class="dropdown-item text-secondary btn-reopen"
                                                           data-url="{{ route('surat_keputusan.reopen', $h->id) }}"
                                                           data-nomor="{{ $h->nomor ?? '—' }}">
                                                            <i class="fas fa-undo"></i> Tarik ke Draft
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                    @endcan
                                                @endif

                                                {{-- 4. Download PDF --}}
                                                @if (in_array($h->status_surat, ['disetujui', 'terbit', 'arsip']) && $h->signed_pdf_path)
                                                    <a class="dropdown-item text-danger"
                                                       href="{{ route('surat_keputusan.downloadPdf', $h->id) }}"
                                                       target="_blank">
                                                        <i class="fas fa-file-pdf"></i> Download PDF
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                @endif

                                                {{-- 5. Terbitkan --}}
                                                @if ($h->status_surat === 'disetujui')
                                                    @can('publish', $h)
                                                        <form
                                                            action="{{ route('surat_keputusan.terbitkan', ['surat_keputusan' => $h->id]) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="button"
                                                                    class="dropdown-item text-primary w-100 text-left btn-terbitkan-sk"
                                                                    data-nomor="{{ $h->nomor ?? '—' }}">
                                                                <i class="fas fa-share-square"></i> Terbitkan SK
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                    @endcan
                                                @endif

                                                {{-- 5b. Batal Terbitkan --}}
                                                @if ($h->status_surat === 'terbit')
                                                    @can('unpublish', $h)
                                                        <form
                                                            action="{{ route('surat_keputusan.batal_terbitkan', ['surat_keputusan' => $h->id]) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="button"
                                                                    class="dropdown-item text-warning w-100 text-left btn-batal-terbitkan-sk"
                                                                    data-nomor="{{ $h->nomor ?? '' }}">
                                                                <i class="fas fa-undo"></i> Batal Terbitkan
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                    @endcan
                                                @endif

                                                {{-- 6. Arsipkan --}}
                                                @if ($h->status_surat === 'terbit')
                                                    @can('archive', $h)
                                                        <form
                                                            action="{{ route('surat_keputusan.arsipkan', ['surat_keputusan' => $h->id]) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="button"
                                                                    class="dropdown-item text-dark w-100 text-left btn-arsipkan-sk"
                                                                    data-nomor="{{ $h->nomor ?? '' }}">
                                                                <i class="fas fa-archive"></i> Arsipkan SK
                                                            </button>
                                                        </form>
                                                        <div class="dropdown-divider"></div>
                                                    @endcan
                                                @endif

                                                {{-- 7. Hapus Draft --}}
                                                @if ($h->status_surat === 'draft')
                                                    @can('delete', $h)
                                                        <form action="{{ route('surat_keputusan.destroy', $h->id) }}"
                                                              method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                    class="dropdown-item text-danger w-100 text-left btn-hapus-sk"
                                                                    data-nomor="{{ $h->nomor ?? '—' }}">
                                                                <i class="fas fa-trash"></i> Hapus Draft
                                                            </button>
                                                        </form>
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

    {{-- Modal Tolak / Minta Revisi --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="rejectForm" method="POST" action="#">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak / Minta Revisi</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border">
                            Menolak SK <b class="sk-info">—</b> akan mengembalikan dokumen ke pembuat.
                        </div>
                        <div class="form-group">
                            <label>Catatan ke pembuat (opsional)</label>
                            <textarea name="note" class="form-control" rows="4"
                                      placeholder="Contoh: Mohon perbaiki redaksi KESATU dan lengkapi dasar hukum butir 3."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Tolak & Kembalikan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();

            const MODE = "{{ $mode }}";
            let emptyMsg;

            switch (MODE) {
                case 'approve-list':
                    emptyMsg = "Tidak ada SK yang perlu Anda setujui.";
                    break;
                case 'terbit-list':
                    emptyMsg = "Belum ada SK yang sudah terbit.";
                    break;
                case 'arsip-list':
                    emptyMsg = "Belum ada SK dalam arsip.";
                    break;
                default:
                    emptyMsg = "Tidak ada data Surat Keputusan.";
                    break;
            }

            const confirmAction = async (o) =>
                Swal.fire(Object.assign({
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Lanjut',
                    cancelButtonText: 'Batal'
                }, o)).then(r => r.isConfirmed);

            const $table = $('#table-sk');
            const headers = $table.find('thead th').map((i, th) => $(th).text().trim().toLowerCase()).get();
            const statusIdx = headers.indexOf('status');
            const aksiIdx = headers.length - 1;

            const table = $table.DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: "/assets/datatables/i18n/id.json",
                    emptyTable: emptyMsg
                },
                order: [
                    [headers.indexOf('tgl surat') !== -1 ? headers.indexOf('tgl surat') : 0, 'desc']
                ],
                columnDefs: [{
                    targets: [aksiIdx],
                    orderable: false,
                    searchable: false
                }]
            }).on('draw', function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
            @endif

            @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                timer: 3000,
                showConfirmButton: false
            });
            @endif

            $(document).on('click', '.btn-submit-sk', async function (e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                const nomor = $(this).data('nomor') || '—';
                const ok = await confirmAction({
                    title: 'Ajukan SK untuk Persetujuan?',
                    html: `SK <b>${nomor}</b> akan dikirim ke penandatangan untuk disetujui.`,
                    icon: 'question',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Ajukan Sekarang'
                });
                if (ok) $form.trigger('submit');
            });

            $(document).on('click', '.btn-terbitkan-sk', async function (e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                const nomor = $(this).data('nomor') || '—';
                const ok = await confirmAction({
                    title: 'Terbitkan SK?',
                    html: `SK <b>${nomor}</b> akan diterbitkan dan dibagikan ke penerima.`,
                    icon: 'info',
                    confirmButtonColor: '#007bff',
                    confirmButtonText: '<i class="fas fa-share-square"></i> Ya, Terbitkan'
                });
                if (ok) $form.trigger('submit');
            });

            $(document).on('click', '.btn-arsipkan-sk', async function (e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                const nomor = $(this).data('nomor') || '—';
                const ok = await confirmAction({
                    title: 'Arsipkan SK?',
                    html: `SK <b>${nomor}</b> akan dipindahkan ke arsip.`,
                    icon: 'warning',
                    confirmButtonColor: '#343a40',
                    confirmButtonText: '<i class="fas fa-archive"></i> Ya, Arsipkan'
                });
                if (ok) $form.trigger('submit');
            });

            $(document).on('click', '.btn-hapus-sk', async function (e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                const nomor = $(this).data('nomor') || '—';
                const ok = await confirmAction({
                    title: 'Hapus Draft SK?',
                    html: `SK <b>${nomor}</b> akan dihapus secara permanen.`,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                    footer: '<small class="text-muted">Aksi ini tidak dapat dibatalkan!</small>'
                });
                if (ok) $form.trigger('submit');
            });

            $(document).on('click', '.btn-reject', function (e) {
                e.preventDefault();
                const action = $(this).data('action');
                const nomor = $(this).data('nomor') || '—';
                const $m = $('#rejectModal');
                $('#rejectForm').attr('action', action);
                $m.find('.sk-info').text(nomor);
                $m.find('textarea[name="note"]').val('');
                $m.modal('show');
            });

            $(document).on('click', '.btn-reopen', async function (e) {
                e.preventDefault();
                const url = $(this).data('url');
                const nomor = $(this).data('nomor') || '—';
                const ok = await confirmAction({
                    title: 'Tarik ke Draft?',
                    html: `SK <b>${nomor}</b> akan dikembalikan ke status <b>Draft</b> untuk direvisi.`,
                    icon: 'question',
                    confirmButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-undo"></i> Ya, Tarik Sekarang'
                });
                if (!ok) return;

                const form = $('<form>', {
                    method: 'POST',
                    action: url,
                    style: 'display:none'
                }).append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: '{{ csrf_token() }}'
                }));

                $('body').append(form);
                form.trigger('submit');
            });

            // ✅ EVENT HANDLER BATAL TERBITKAN SK
            $(document).on('click', '.btn-batal-terbitkan-sk', async function (e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                const nomor = $(this).data('nomor') || '—';

                const ok = await confirmAction({
                    title: 'Batal Terbitkan SK?',
                    html: `SK <b>${nomor}</b> akan dikembalikan ke status <b>Disetujui</b>. Status terbit akan dibatalkan.`,
                    icon: 'warning',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: '<i class="fas fa-undo"></i> Ya, Batalkan Penerbitan'
                });

                if (ok) {
                    $form.trigger('submit');
                }
            });

        });
    </script>
@endpush
