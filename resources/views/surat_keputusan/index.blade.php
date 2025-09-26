@extends('layouts.app')
@section('title', 'Daftar Surat Keputusan')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        /* Menggunakan kembali style dari halaman Surat Tugas untuk konsistensi */
        .page-header {
            background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
            margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
            display: flex; align-items: center; gap: 1.3rem;
        }
        /* Warna ikon header untuk Surat Keputusan */
        .page-header .icon {
            background: linear-gradient(135deg, #6f42c1 0%, #9a6ee5 100%);
            width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; box-shadow: 0 1px 10px #6f42c14d; font-size: 2rem;
        }
        .page-header-title { font-weight: bold; color: #412674; font-size: 1.85rem; margin-bottom: 0.13rem; letter-spacing: -1px; }
        .page-header-desc { color: #636e7b; font-size: 1.03rem; }

        .stat-card-lg {
            background-color: #fff; border: 1px solid #e9ecef; border-left-width: 4px;
            border-radius: .7rem; padding: 1rem 1.25rem; transition: all .2s ease; cursor: pointer;
        }
        .stat-card-lg:hover { transform: translateY(-3px); box-shadow: 0 7px 20px rgba(0,0,0,.08); }
        .stat-card-lg .stat-value { font-size: 1.75rem; font-weight: 700; }
        .stat-card-lg .stat-label { font-size: .9rem; color: #6c757d; font-weight: 500; }

        .filter-card { border: none; border-radius: .8rem; background-color: #fff; box-shadow: 0 4px 25px rgba(0,0,0,.05); }
        .filter-card .card-header { background-color: transparent; border-bottom: 1px solid #f0f0f0; }

        .card-data { border: none; border-radius: .8rem; box-shadow: 0 4px 25px rgba(0,0,0, .07); }
        .card-data .card-header { background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem; }
        .table-data thead th { background: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.85rem; border-bottom: 2px solid #dee2e6; }
        .table-data tbody td { vertical-align: middle; }
        .surat-info .surat-nomor { font-weight: 600; color: #343a40; display: block; }
        .surat-info .surat-perihal { font-size: .9rem; color: #6c757d; }

        .status-badge { font-size: .75rem; font-weight: 700; padding: .4em .8em; border-radius: 50px; text-transform: uppercase; letter-spacing: .5px; }
        .status-badge-draft { background-color: #e9ecef; color: #6c757d; }
        .status-badge-pending { background-color: #fff3cd; color: #856404; }
        .status-badge-disetujui { background-color: #d4edda; color: #155724; }
        .btn-action { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,.15); }
        .stat-card-lg.border-left-primary   { border-left-color: #007bff !important; }
.stat-card-lg.border-left-secondary { border-left-color: #6c757d !important; }
.stat-card-lg.border-left-warning   { border-left-color: #ffc107 !important; }
.stat-card-lg.border-left-success   { border-left-color: #28a745 !important; }

    </style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-gavel text-white" aria-hidden="true"></i></span>
    <span>
        <div class="page-header-title">Daftar Surat Keputusan</div>
        <div class="page-header-desc">Kelola, filter, dan lacak status semua Surat Keputusan (SK) di sini.</div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Statistik Interaktif --}}
    <div class="row mb-4">
        @php
            $total = isset($list) ? $list->count() : (($stats['draft'] ?? 0) + ($stats['pending'] ?? 0) + ($stats['disetujui'] ?? 0));
        @endphp
        @foreach([
            'semua'     => ['label'=>'Total SK','count'=>$total,'color'=>'primary','icon'=>'fa-archive'],
            'draft'     => ['label'=>'Draft','count'=>$stats['draft'] ?? 0,'color'=>'secondary','icon'=>'fa-file-alt'],
            'pending'   => ['label'=>'Pending','count'=>$stats['pending'] ?? 0,'color'=>'warning','icon'=>'fa-hourglass-half'],
            'disetujui' => ['label'=>'Disetujui','count'=>$stats['disetujui'] ?? 0,'color'=>'success','icon'=>'fa-check-circle'],
        ] as $status => $info)
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card-lg border-left-{{ $info['color'] }}" data-status="{{ $status === 'semua' ? '' : $status }}" role="button" tabindex="0" aria-label="Filter status {{ $status }}">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="stat-value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                        <div class="stat-label">{{ $info['label'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas {{ $info['icon'] }} fa-2x text-gray-300" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Panel Filter --}}
    <div class="card filter-card mb-4">
        <div class="card-header">
            <h6 class="mb-0 font-weight-bold">
                <i class="fas fa-filter text-primary mr-2" aria-hidden="true"></i>Filter & Pencarian
            </h6>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-lg-6 form-group mb-lg-0">
                    <label for="globalSearch">Cari Apa Saja</label>
                    <input id="globalSearch" type="text" class="form-control" placeholder="Ketik nomor, perihal, atau pembuat...">
                </div>
                <div class="col-lg-3 col-md-6 form-group mb-lg-0">
                    <label for="statusFilter">Status Surat</label>
                    <select id="statusFilter" class="custom-select">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
                        <i class="fas fa-redo mr-2" aria-hidden="true"></i>Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Utama --}}
    <div class="card card-data">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 font-weight-bold">Data Surat Keputusan</h5>
            @if((int)auth()->user()->peran_id === 1)
                <a href="{{ route('surat_keputusan.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2" aria-hidden="true"></i>Tambah SK Baru
                </a>
            @endif
        </div>
        <div class="card-body">
            <table id="table-keputusan" class="table table-hover table-data" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Surat</th>
                        <th>Pembuat</th>
                        <th>Tanggal Dibuat</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $sk)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="surat-info">
                                <a href="{{ route('surat_keputusan.show', $sk->id) }}" class="surat-nomor">{{ $sk->nomor }}</a>
                                <span class="surat-perihal">{{ \Illuminate\Support\Str::limit($sk->tentang, 60) }}</span>
                            </div>
                        </td>
                        <td>{{ $sk->pembuat->nama_lengkap ?? '-' }}</td>
                        <td data-order="{{ optional($sk->created_at)->timestamp ?? 0 }}">
                            {{ optional($sk->created_at)->isoFormat('D MMM YYYY') ?? '-' }}
                        </td>
                        <td>
                            <span class="status-badge status-badge-{{ $sk->status_surat }}">{{ $sk->status_surat }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('surat_keputusan.show', $sk->id) }}"
                               class="btn btn-primary btn-action" data-toggle="tooltip" title="Lihat Detail" aria-label="Lihat Detail">
                                <i class="fas fa-eye fa-sm" aria-hidden="true"></i>
                            </a>

                            {{-- Edit hanya jika draft milik sendiri (aman terhadap controller) --}}
                            @if($sk->status_surat === 'draft' && (int)auth()->id() === (int)$sk->dibuat_oleh)
                                <a href="{{ route('surat_keputusan.edit', $sk->id) }}"
                                   class="btn btn-warning btn-action" data-toggle="tooltip" title="Edit Draft" aria-label="Edit Draft">
                                    <i class="fas fa-pencil-alt fa-sm" aria-hidden="true"></i>
                                </a>
                            @endif

                            {{-- Tidak ada tombol HAPUS di sini karena belum ada rute destroy di controller --}}
                        </td>
                    </tr>
                    @empty
                    {{-- Opsional: baris kosong/placeholder --}}
                    @endforelse
                </tbody>
            </table>
        </div>
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

        const table = $('#table-keputusan').DataTable({
            responsive: true,
            autoWidth: false,
            language: { url: "/assets/datatables/i18n/id.json" },
            columnDefs: [{ targets: [0, 5], orderable: false, searchable: false }]
        });

        // Filter dari panel filter
        $('#globalSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#statusFilter').on('change', function() {
            const status = this.value;
            // kolom status = index 4
            table.column(4).search(status ? '^' + status + '$' : '', true, false).draw();
        });
        $('#resetFilters').on('click', function() {
            $('#globalSearch, #statusFilter').val('');
            table.search('').columns().search('').draw();
        });

        // Filter dari klik kartu statistik
        $('.stat-card-lg').on('click', function() {
            const status = $(this).data('status');
            $('#statusFilter').val(status).trigger('change');
        });

        // Notifikasi sukses
        @if(session('success'))
            Swal.fire({
                toast: true, position: 'top-end',
                icon: 'success', title: 'Berhasil!', text: @json(session('success')),
                showConfirmButton: false, timer: 3000
            });
        @endif
    });
    </script>
@endpush
