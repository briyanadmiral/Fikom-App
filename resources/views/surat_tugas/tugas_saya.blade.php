@extends('layouts.app')
@section('title', 'Surat Tugas Saya')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
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
            margin-bottom: 0.13rem;
            letter-spacing: -1px;
        }

        .surat-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        @media (max-width: 767.98px) {
            .surat-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem 1rem;
                gap: .7rem;
            }

            .surat-header-title {
                font-size: 1.18rem;
            }

            .surat-header-desc {
                font-size: .99rem;
            }
        }

        /* Menggunakan style yang sama persis dengan halaman index untuk konsistensi */
        .stat-wrapper {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            width: 160px;
            border-radius: .75rem;
            border: none;
        }

        .stat-card .card-body {
            text-align: center;
            padding: 1.25rem 1rem;
        }

        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: .5rem;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: .8rem;
            margin-bottom: .25rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.4em 0.7em;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .dropdown-menu a.dropdown-item {
            cursor: pointer;
        }

        .dropdown-menu .fa-fw {
            margin-right: 8px;
        }

        #quickViewModal .modal-body {
            height: 75vh;
        }
    </style>
@endpush

@section('content_header')
    <div class="surat-header mt-2 mb-3">
        <span class="icon">
            <i class="fas fa-user-shield text-white"></i>
        </span>
        <span>
            <div class="surat-header-title">Surat Tugas Saya</div>
            <div class="surat-header-desc">
                Daftar semua <b>surat tugas</b> yang ditujukan ke Anda. Lihat detail, download PDF, serta pantau status
                surat di sini.
            </div>
        </span>
    </div>
@endsection


@section('content')
    <div class="container-fluid">

        {{-- Blok Statistik (Tetap dipertahankan) --}}
        <div class="stat-wrapper">
            @foreach ([
            'draft' => ['icon' => 'fa-file-alt', 'label' => 'Draft', 'count' => $stats['draft'] ?? 0, 'color' => 'secondary'],
            'pending' => ['icon' => 'fa-hourglass-half', 'label' => 'Pending', 'count' => $stats['pending'] ?? 0, 'color' => 'warning'],
            'disetujui' => ['icon' => 'fa-check-circle', 'label' => 'Disetujui', 'count' => $stats['disetujui'] ?? 0, 'color' => 'success'],
        ] as $status => $info)
                <div class="stat-card card shadow-sm">
                    <div class="card-body">
                        <div class="icon text-{{ $info['color'] }}"><i class="fas {{ $info['icon'] }}"></i></div>
                        <div class="label">{{ $info['label'] }}</div>
                        <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Blok Filter dan Tombol (Dengan penyesuaian hak akses) --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian</h5>

                    {{-- Tombol hanya muncul untuk Admin TU di halaman ini --}}
                    @if (auth()->user()->peran_id === 1)
                        <div class="d-flex gap-2">
                            <a href="{{ route('surat_tugas.create') }}" class="btn btn-primary"><i
                                    class="fas fa-plus mr-2"></i>Tambah Surat Tugas</a>
                            <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-outline-secondary"><i
                                    class="fas fa-cog mr-2"></i>Manage Jenis Surat</a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <input id="globalSearch" type="text" class="form-control"
                            placeholder="Cari berdasarkan nomor, perihal, pembuat, atau penerima...">
                    </div>
                    <div class="col-md-3 form-group">
                        <select id="statusFilter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="disetujui">Disetujui</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <button id="resetFilters" class="btn btn-outline-secondary w-100"><i
                                class="fas fa-redo mr-1"></i>Reset Filter</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel Utama --}}
        <div class="card shadow-sm">
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($list as $h)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $h->nomor }}</td>
                                    <td>{{ $h->nama_umum }}</td>
                                    @php $tgl = $h->tanggal_surat; @endphp
                                    <td class="text-center" data-sort="{{ $tgl ? $tgl->timestamp : 0 }}">
                                        {{ $tgl ? $tgl->format('d M Y') : '-' }}
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
                                                <span class="badge badge-info ml-1">+{{ $penerimaCount - 1 }}
                                                    lainnya</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge badge-pill badge-{{ $h->status_surat == 'disetujui' ? 'success' : ($h->status_surat == 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($h->status_surat) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($h->status_surat == 'disetujui')
                                            <a href="{{ route('surat_tugas.downloadPdf', $h->id) }}"
                                                class="btn btn-sm btn-danger" title="Download PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item"
                                                    href="{{ route('surat_tugas.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}"
                                                    data-toggle="modal" data-target="#modal-preview"
                                                    data-preview-url="{{ route('surat_tugas.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}">
                                                    <i class="fas fa-search mr-2"></i> Lihat Cepat
                                                </a>

                                                <a class="dropdown-item" href="{{ route('surat_tugas.show', $h->id) }}"><i
                                                        class="fas fa-fw fa-eye"></i> Halaman Detail</a>

                                                {{-- Hanya tampilkan Download jika sudah disetujui --}}
                                                @if ($h->status_surat == 'disetujui')
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item"
                                                        href="{{ route('surat_tugas.downloadPdf', $h->id) }}"
                                                        target="_blank"><i class="fas fa-fw fa-download"></i> Download
                                                        PDF</a>
                                                @endif

                                                {{-- Tombol Edit HANYA muncul jika Policy 'update' mengizinkan --}}
                                                @can('update', $h)
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="{{ route('surat_tugas.edit', $h->id) }}"><i
                                                            class="fas fa-fw fa-edit text-warning"></i> Edit / Koreksi</a>
                                                @endcan

                                                {{-- Tombol Delete HANYA muncul jika Policy 'delete' mengizinkan --}}
                                                @can('delete', $h)
                                                    <a class="dropdown-item btn-delete"
                                                        data-url="{{ route('surat_tugas.destroy', $h->id) }}"><i
                                                            class="fas fa-fw fa-trash text-danger"></i> Hapus Draft</a>
                                                @endcan
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
@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('surat_tugas.partials._scripts_shared', [
        // mode & tabel
        'mode' => 'user',
        'tableId' => '#table-tugas',
    
        // filter selectors
        'searchSelector' => '#globalSearch',
        'statusFilterSelector' => '#statusFilter',
        'resetBtnSelector' => '#resetFilters',
    
        // kolom (dicari by header text, case-insensitive)
        'orderHeaderText' => 'tgl surat',
        'statusHeaderText' => 'status',
        'nonOrderableHeaders' => ['Berkas', 'Aksi'],
    
        // fitur
        'enableQuickView' => true,
        'quickView' => ['modalId' => '#quickViewModal', 'triggerSelector' => '.quick-view'],
        'enableDelete' => false, // penerima tidak menghapus
    
        // i18n & pesan kosong
        'i18nUrl' => '/assets/datatables/i18n/id.json',
        'emptyDefaultMsg' => 'Tidak ada surat tugas untuk Anda.',
    
        // teks konfirmasi (dipakai kalau ada aksi konfirmasi lain)
        'moduleName' => 'Surat Tugas',
    ])
@endpush
