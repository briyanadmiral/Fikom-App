@extends('layouts.app')
@section('title', 'Surat Keputusan Saya')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
    /* ========== Header Ungu ========== */
    .header-title {
        color: #800080;
        font-size: 2rem;
        font-weight: 700;
        border-bottom: 4px solid #800080;
        display: inline-block;
        padding-bottom: .25rem;
        margin-bottom: 1.5rem;
    }

    /* ========== Statistik Minimalis Centered ========== */
    .stat-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .stat-card { width:160px; border-radius:.75rem; cursor: pointer; }
    .stat-card .card-body { text-align:center; padding:1rem }
    .stat-card .icon { font-size:1.75rem; margin-bottom:.5rem }
    .stat-card .label { color:#6c757d; font-size:.85rem; margin-bottom:.25rem; font-weight:600 }
    .stat-card .value { font-size:1.75rem; font-weight:700; line-height:1 }

    /* ========== Override form-select ========== */
    .form-select {
      -webkit-appearance:none; -moz-appearance:none; appearance:none;
      width:100% !important; padding:.375rem .75rem !important;
      border:1px solid #ced4da !important; border-radius:.375rem !important;
      background:#fff url("data:image/svg+xml;charset=UTF-8,%3Csvg viewBox='0 0 4 5' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 0L0 2h4zm0 5L0 3h4z' fill='%236c757d'/%3E%3C/svg%3E") no-repeat right .75rem center !important;
      background-size:.65em auto !important;
    }
    .form-select::-ms-expand { display:none }
    .form-select:focus {
      border-color:#800080 !important;
      box-shadow:0 0 0 .2rem rgba(128,0,128,.25) !important;
      outline:none !important;
    }

    /* ========== Badge Status ========== */
    .badge-status-draft     { background:#f8f9fa; color:#6c757d }
    .badge-status-pending   { background:#fff3cd; color:#856404 }
    .badge-status-disetujui { background:#d1e7dd; color:#0f5132 }

    /* ========== DataTables tweak ========== */
    #table-sk_wrapper .dataTables_length,
    #table-sk_wrapper .dataTables_filter { display:none }
    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background:#0d6efd !important; color:#fff !important;
    }
    .dataTables_paginate .paginate_button:hover {
        background:rgba(13,110,253,.1) !important; color:#0d6efd !important;
    }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">

    {{-- Header Ungu --}}
    <h2 class="header-title">
        <i class="fas fa-clipboard-list me-2" aria-hidden="true"></i> Surat Keputusan Saya
    </h2>

    {{-- Flash via SweetAlert --}}
    @if(session('success') || session('error'))
        <div class="d-none" id="swal-flash"
             data-message="{{ session('success') ?? session('error') }}"
             data-type="{{ session('success') ? 'success' : 'error' }}">
        </div>
    @endif

    {{-- Statistik --}}
    <div class="stat-wrapper">
        @foreach([
            'draft'     => ['icon'=>'fa-file-alt','label'=>'Draft','count'=>$stats['draft'] ?? 0,'color'=>'secondary'],
            'pending'   => ['icon'=>'fa-hourglass-half','label'=>'Pending','count'=>$stats['pending'] ?? 0,'color'=>'warning'],
            'disetujui' => ['icon'=>'fa-check-circle','label'=>'Disetujui','count'=>$stats['disetujui'] ?? 0,'color'=>'success'],
        ] as $statusKey => $info)
        <div class="stat-card card shadow-sm" data-status="{{ $statusKey }}">
            <div class="card-body">
                <div class="icon text-{{ $info['color'] }}">
                    <i class="fas {{ $info['icon'] }}" aria-hidden="true"></i>
                </div>
                <div class="label">{{ $info['label'] }}</div>
                <div class="value">{{ $info['count'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tombol Tambah SK (Admin TU) --}}
    @if((int)auth()->user()->peran_id === 1)
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('surat_keputusan.create') }}"
           class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus me-2" aria-hidden="true"></i>Tambah Surat Keputusan
        </a>
    </div>
    @endif

    {{-- Filter/Search --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary" aria-hidden="true"></i>Filter & Pencarian
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-uppercase" for="globalSearch">Cari</label>
                    <input id="globalSearch" class="form-control" placeholder="Ketik nomor, judul...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase" for="statusFilter">Status</label>
                    <select id="statusFilter" class="form-select w-100">
                        <option value="">Semua Status</option>
                        @foreach(['draft'=>'Draft','pending'=>'Pending','disetujui'=>'Disetujui'] as $val=>$lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase" for="startDate">Dari Tgl Dibuat</label>
                    <input id="startDate" type="date" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase" for="endDate">Sampai Tgl Dibuat</label>
                    <input id="endDate" type="date" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
                        <i class="fas fa-redo me-1" aria-hidden="true"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Surat Keputusan --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="table-sk"
                       class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Nomor</th>
                            <th>Tanggal SK</th>
                            <th>Judul/Perihal</th>
                            <th>Pembuat</th>
                            <th>Status</th>
                            <th>Tgl Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $sk)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $sk->nomor }}</td>
                            <td>{{ optional($sk->tanggal_asli)->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($sk->tentang ?? '-', 80) }}</td>
                            <td>{{ $sk->pembuat->nama_lengkap ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge badge-status-{{ $sk->status_surat }}">
                                    {{ ucfirst($sk->status_surat) }}
                                </span>
                            </td>
                            <td data-order="{{ optional($sk->created_at)->timestamp ?? 0 }}">
                                {{ optional($sk->created_at)->format('d-m-Y H:i') ?? '-' }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- VIEW --}}
                                    <a href="{{ route('surat_keputusan.show', $sk->id) }}"
                                       class="btn btn-sm btn-outline-info" title="Lihat">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>

                                    {{-- EDIT (hanya draft milik sendiri & Admin TU) --}}
                                    @if($sk->status_surat==='draft' && (int)auth()->user()->peran_id===1 && (int)auth()->id()===(int)$sk->dibuat_oleh)
                                        <a href="{{ route('surat_keputusan.edit', $sk->id) }}"
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                    @endif

                                    {{-- APPROVE (pending & penandatangan adalah user Dekan/Wadek) --}}
                                    @if($sk->status_surat==='pending'
                                        && in_array((int)auth()->user()->peran_id,[2,3], true)
                                        && (int)$sk->penandatangan === (int)auth()->user()->id)
                                        <form action="{{ route('surat_keputusan.approve', $sk->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Approve">
                                                <i class="fas fa-check" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- OPSIONAL: tombol PDF hanya jika sudah disetujui
                                    @if($sk->status_surat==='disetujui')
                                        <a href="{{ route('surat_keputusan.pdf', $sk->id) }}"
                                           class="btn btn-sm btn-success" title="Unduh PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @endif
                                    --}}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada surat keputusan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div><!-- /.table-responsive -->
        </div><!-- /.card-body -->
    </div><!-- /.card -->

</div><!-- /.container-fluid -->
@endsection

@push('scripts')
    <!-- Dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // SweetAlert2 flash
    const flash = document.getElementById('swal-flash');
    if (flash) {
        Swal.fire({
            icon: flash.dataset.type,
            title: flash.dataset.type==='success' ? 'Berhasil!' : 'Error',
            text: flash.dataset.message,
            timer: 2500,
            showConfirmButton: false
        });
    }

    moment.locale('id');
    // Custom date filter untuk kolom "Tgl Dibuat" (index 6, format 'DD-MM-YYYY HH:mm')
    $.fn.dataTable.ext.search.push((settings,data) => {
        const min = $('#startDate').val(),
              max = $('#endDate').val(),
              dateStr = data[6] || '';
        if (!min && !max) return true;
        const d = moment(dateStr, "DD-MM-YYYY HH:mm");
        if (!d.isValid()) return false;
        const s = min ? moment(min,"YYYY-MM-DD").startOf('day') : null;
        const e = max ? moment(max,"YYYY-MM-DD").endOf('day')   : null;
        if (s && e) return d.isBetween(s,e,null,'[]');
        if (s)       return d.isSameOrAfter(s);
        if (e)       return d.isSameOrBefore(e);
        return true;
    });

    // init DataTable
    const table = $('#table-sk').DataTable({
        dom: 'rt<"d-flex justify-content-between align-items-center mt-3 px-3 pb-2"ip>',
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
        columnDefs:[
            { orderable:false, targets:[0,7] },
            { searchable:false, targets:[0,5,7] }
        ],
        pagingType:"simple_numbers"
    });

    // Filter dari panel filter
    $('#globalSearch').on('keyup',    () => table.search($('#globalSearch').val()).draw());
    $('#statusFilter').on('change',   () => table.column(5).search($('#statusFilter').val()
        ? `^${$('#statusFilter').val()}$` : '', true,false).draw());
    $('#startDate,#endDate').on('change', () => table.draw());
    $('#resetFilters').on('click',    () => {
        $('#globalSearch,#statusFilter,#startDate,#endDate').val('');
        table.search('').columns().search('').draw();
    });

    // Klik kartu statistik -> set filter status
    $('.stat-card').on('click', function() {
        const status = $(this).data('status') || '';
        $('#statusFilter').val(status).trigger('change');
    });
});
</script>
@endpush
