{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    /* [STYLE HEADER TETAP SAMA KARENA ANDA MENYUKAINYA] */
    .page-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem 1.3rem 1.8rem;
        border-radius: 1.1rem;
        margin-bottom: 2.2rem;
        border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);
        width: 54px; height: 54px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px #1498ff30;
        font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold;
        color: #0056b3;
        font-size: 1.85rem;
        margin-bottom: 0.13rem;
        letter-spacing: -1px;
    }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }
    @media (max-width: 767.98px) {
        .page-header { flex-direction: column; align-items: flex-start; padding: 1.2rem 1rem; gap: .7rem; }
        .page-header-title { font-size: 1.18rem; }
        .page-header-desc { font-size: .99rem; }
    }
    /* [AKHIR DARI STYLE HEADER] */

    /* [STYLE BARU UNTUK KONTEN] */
    .metric-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid #f0f0f0;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .metric-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: #ccc;
    }
    .metric-card.role-1::before { background: #dc3545; } /* Admin */
    .metric-card.role-2::before { background: #007bff; } /* Dekan */
    .metric-card.role-3::before { background: #17a2b8; } /* Wakil Dekan */
    .metric-card.role-4::before { background: #6610f2; } /* Kaprodi */
    .metric-card.role-5::before { background: #28a745; } /* Dosen */
    .metric-card.role-6::before { background: #6c757d; } /* Tendik */

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
        margin-bottom: 0.2rem;
    }
    .metric-label {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .metric-icon {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        color: #f8f9fa;
        z-index: 0;
    }

    .card-users {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0, .05);
        overflow: hidden;
    }
    .card-users .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.5rem;
    }
    .table-users {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-bottom: 0 !important;
    }
    .table-users thead th {
        background: #fcfcfc;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-size: 0.75rem;
        border-bottom: 2px solid #edf2f7 !important;
        padding: 1rem 1.5rem;
        vertical-align: middle;
    }
    .table-users tbody td {
        vertical-align: middle;
        font-size: 0.95rem;
        color: #495057;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f8f9fa;
    }
    .table-users tbody tr:last-child td {
        border-bottom: none;
    }
    .table-users tbody tr:hover {
        background-color: #fafbfd;
    }
    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.25rem;
        box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: transform .2s;
    }
    .user-avatar:hover { transform: scale(1.05); }
    
    .user-info .user-name {
        font-weight: 600;
        color: #343a40;
        font-size: 1rem;
        margin-bottom: 2px;
    }
    .user-info .user-email {
        font-size: 0.85rem;
        color: #8898aa;
    }
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center; justify-content: center;
        transition: all 0.2s ease;
        border: none;
    }
    .btn-action-edit { background: #fff3cd; color: #856404; }
    .btn-action-edit:hover { background: #ffeeba; color: #533f03; }
    .btn-action-delete { background: #f8d7da; color: #721c24; }
    .btn-action-delete:hover { background: #f5c6cb; color: #491217; }
    
    .badge-peran {
        padding: 0.5em 0.9em;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .badge-status {
        padding: .5em .9em;
        font-size: .75rem;
        border-radius: 2rem;
        font-weight: 600;
    }

    /* Custom DataTables Styling to match */
    div.dataTables_wrapper div.dataTables_length select {
        width: auto;
        display: inline-block;
        border-radius: 8px;
        padding: 0.3rem 2rem 0.3rem 0.75rem;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 8px;
        padding: 0.3rem 0.75rem;
        margin-left: 0.5rem;
    }
    div.dataTables_wrapper div.dataTables_info {
        padding-top: 1rem;
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
    }
    div.dataTables_wrapper div.dataTables_paginate {
        margin: 0;
        white-space: nowrap;
        text-align: right;
        padding-top: 1rem;
    }
    div.dataTables_wrapper div.dataTables_paginate ul.pagination {
        margin: 2px 0;
        white-space: nowrap;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content_header')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.75rem;">Manajemen Pengguna</h1>
        <p class="text-muted mb-0">Kelola data pengguna, peran akses, dan statistik akun.</p>
    </div>
    <div class="col-md-6 text-md-right mt-3 mt-md-0">
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm px-4 py-2" style="border-radius: 8px;">
            <i class="fas fa-plus mr-2"></i> Tambah Pengguna
        </a>
        <button class="btn btn-white shadow-sm px-3 py-2 ml-2" data-toggle="modal" data-target="#modal-peran" style="border-radius: 8px; border: 1px solid #e0e0e0;">
            <i class="fas fa-cog text-secondary mr-1"></i> Peran
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('users.peran.modal')

    {{-- [BARU] Role Metrics Row --}}
    <div class="row mb-4">
        @foreach($roles as $role)
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3 mb-xl-0">
            <div class="metric-card role-{{ $role->id }}">
                <div class="metric-value">{{ $role->users_count }}</div>
                <div class="metric-label">{{ $role->nama }}</div>
                <div class="metric-icon">
                    @switch($role->id)
                        @case(1) <i class="fas fa-user-shield text-danger" style="opacity:0.1"></i> @break
                        @case(2) <i class="fas fa-user-tie text-primary" style="opacity:0.1"></i> @break
                        @case(3) <i class="fas fa-user-tie text-info" style="opacity:0.1"></i> @break
                        @case(4) <i class="fas fa-chalkboard-teacher text-purple" style="color:#6f42c1; opacity:0.1"></i> @break
                        @case(5) <i class="fas fa-chalkboard-teacher text-success" style="opacity:0.1"></i> @break
                        @default <i class="fas fa-user text-secondary" style="opacity:0.1"></i>
                    @endswitch
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card card-users">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-users" id="table-users" style="width:100%">
                    <thead>
                        <tr>
                            <th class="pl-4">Pengguna</th>
                            <th>NPP</th>
                            <th>Jabatan</th>
                            <th>Peran</th>
                            <th class="text-center">Status</th>
                            <th>Bergabung</th>
                            <th class="text-center pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    {{-- Avatar dengan inisial nama --}}
                                    <div class="user-avatar mr-3" style="background-color: {{ generate_color_from_string($u->nama_lengkap) }}; box-shadow: 0 3px 6px {{ generate_color_from_string($u->nama_lengkap) }}40;">
                                        {{ get_initials($u->nama_lengkap) }}
                                    </div>
                                    <div class="user-info">
                                        <div class="user-name">{{ $u->nama_lengkap }}</div>
                                        <div class="user-email">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="font-weight-bold text-secondary">{{ $u->npp ? $u->npp : '—' }}</td>
                            <td>{{ $u->jabatan ?? '—' }}</td>
                            <td>
                                @php
                                    $badgeClass = match($u->peran_id) {
                                        1 => 'badge-danger',
                                        2 => 'badge-primary',
                                        3 => 'badge-info',
                                        4 => 'badge-warning', // Kaprodi
                                        5 => 'badge-success', // Dosen
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} badge-peran shadow-sm">
                                    {{ optional($u->peran)->nama ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($u->status == 'aktif')
                                    <span class="badge badge-success badge-status bg-success-soft text-success border border-success" style="background: #e6fffa;">Aktif</span>
                                @else
                                    <span class="badge badge-secondary badge-status bg-secondary-soft text-secondary border">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="text-muted small font-weight-bold" data-sort="{{ $u->created_at ? $u->created_at->timestamp : 0 }}">{{ $u->created_at ? $u->created_at->isoFormat('D MMM YYYY') : '—' }}</td>
                            <td class="text-center pr-4">
                                <div class="btn-group shadow-sm" style="border-radius: 8px;">
                                    <a href="{{ route('users.edit', $u->id) }}" class="btn btn-action-edit btn-action" data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-pencil-alt" style="font-size: 0.9rem;"></i>
                                    </a>
                                    <button type="button" class="btn btn-action-delete btn-action btn-hapus-user ml-2" data-id="{{ $u->id }}" data-toggle="tooltip" title="Hapus">
                                        <i class="fas fa-trash-alt" style="font-size: 0.9rem;"></i>
                                    </button>
                                </div>
                                <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                    @csrf @method('DELETE')
                                </form>
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
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(function () {
    // Inisialisasi Tooltip dari Bootstrap
    $('[data-toggle="tooltip"]').tooltip();

    // Inisialisasi DataTables
    var table = $('#table-users').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            emptyTable: "Tidak ada data pengguna yang ditemukan.",
            zeroRecords: "Tidak ada data yang cocok dengan pencarian",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)"
        },
        columnDefs: [
            { orderable: false, targets: 6 } // Kolom Aksi tidak bisa disort
        ],
        drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Notifikasi sukses pakai SweetAlert2
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2000,
            toast: true,
            position: 'top-end'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
        });
    @endif

    // Hapus User pakai SweetAlert2
    $(document).on('click', '.btn-hapus-user', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Anda Yakin?',
            text: 'Pengguna akan dihapus (soft delete) dan tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#form-hapus-'+id).submit();
            }
        });
    });
});
</script>
@endpush