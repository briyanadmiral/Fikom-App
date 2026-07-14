{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .nav-tabs-custom {
        border-bottom: 2px solid #e0e6ed;
        margin-bottom: 2rem;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #636e7b;
        font-weight: 600;
        padding: 1rem 1.5rem;
        position: relative;
    }
    .nav-tabs-custom .nav-link.active {
        color: #0056b3;
        background: transparent;
        border-bottom: 3px solid #1498ff;
    }
    .nav-tabs-custom .badge {
        margin-left: 0.5rem;
        padding: 0.25rem 0.6rem;
        border-radius: 12px;
    }
    .card-users {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0, .05);
    }
    .table-users thead th {
        background: #fcfcfc;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        border-bottom: 2px solid #edf2f7;
        padding: 1rem 1.5rem;
    }
    .table-users tbody td {
        vertical-align: middle;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f8f9fa;
    }
    .table-users tbody tr:hover {
        background-color: #fafbfd;
    }
    .btn-verify {
        background: #28a745;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        border: none;
    }
    .btn-verify:hover {
        background: #218838;
        color: white;
    }
    .btn-reject {
        background: #dc3545;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        border: none;
    }
    .btn-reject:hover {
        background: #c82333;
        color: white;
    }
</style>
@endpush

@section('content_header')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.75rem;">Manajemen Pengguna</h1>
        <p class="text-muted mb-0">Kelola akses pengguna (Staff/Dosen & Mahasiswa) sistem surat SIEGA.</p>
    </div>
    <div class="col-md-6 text-md-right mt-3 mt-md-0">
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm px-4 py-2">
            <i class="fas fa-plus mr-2"></i> Tambah Pengguna
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    {{-- Tabs status verifikasi --}}
    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}" href="{{ route('users.index', ['tab' => 'pending']) }}">
                Pending
                <span class="badge badge-warning">{{ $pendingCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'approved' ? 'active' : '' }}" href="{{ route('users.index', ['tab' => 'approved']) }}">
                Approved
                <span class="badge badge-success">{{ $approvedCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'rejected' ? 'active' : '' }}" href="{{ route('users.index', ['tab' => 'rejected']) }}">
                Rejected
                <span class="badge badge-danger">{{ $rejectedCount }}</span>
            </a>
        </li>
    </ul>

    @php
        $staffUsers = $users->filter(fn($u) => (int)$u->peran_id !== 7);
        $studentUsers = $users->filter(fn($u) => (int)$u->peran_id === 7);
    @endphp

    <!-- WADAH 1: DOSEN & TENAGA KEPENDIDIKAN (STAFF) -->
    <div class="card card-users mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-tie mr-2"></i>Daftar Dosen & Staff (NPP)
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-users" id="table-staff">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NPP</th>
                            <th>Email</th>
                            @if($tab === 'approved')
                            <th>Peran</th>
                            @endif
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($staffUsers as $u)
                        <tr>
                            <td>{{ $u->nama_lengkap }}</td>
                            <td>
                                <span class="badge badge-success font-weight-normal" style="font-size: 0.85rem; padding: 4px 8px;">NPP: {{ $u->npp ?? '—' }}</span>
                            </td>
                            <td>{{ $u->email }}</td>
                            @if($tab === 'approved')
                            <td>{{ optional($u->peran)->nama ?? 'N/A' }}</td>
                            @endif
                            <td class="text-center">
                                @if($tab === 'pending')
                                    @if(auth()->check() && auth()->user()->peran_id === 1)
                                        <form action="{{ route('users.approve', $u->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-verify">
                                                <i class="fas fa-check mr-1"></i>Verify
                                            </button>
                                        </form>
                                        <form action="{{ route('users.reject', $u->id) }}" method="POST" class="d-inline ml-2">
                                            @csrf
                                            <button type="submit" class="btn btn-reject">
                                                <i class="fas fa-times mr-1"></i>Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-secondary">Pending Review</span>
                                    @endif
                                @elseif($tab === 'rejected')
                                    @if(auth()->check() && auth()->user()->peran_id === 1)
                                        <form action="{{ route('users.approve', $u->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-verify">
                                                <i class="fas fa-check mr-1"></i>Verify
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger ml-2 btn-hapus-user" data-id="{{ $u->id }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                            @csrf @method('DELETE')
                                        </form>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                @else
                                    <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger ml-2 btn-hapus-user" data-id="{{ $u->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                        @csrf @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- WADAH 2: MAHASISWA -->
    <div class="card card-users">
        <div class="card-header bg-light py-3">
            <h5 class="m-0 font-weight-bold text-info">
                <i class="fas fa-graduation-cap mr-2"></i>Daftar Mahasiswa (NIM)
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-users" id="table-mahasiswa">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Email</th>
                            <th>WhatsApp</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($studentUsers as $u)
                        <tr>
                            <td>{{ $u->nama_lengkap }}</td>
                            <td>
                                <span class="badge badge-info font-weight-normal" style="font-size: 0.85rem; padding: 4px 8px;">NIM: {{ $u->nim ?? '—' }}</span>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->whatsapp ?? '—' }}</td>
                            <td class="text-center">
                                @if($tab === 'pending')
                                    @if(auth()->check() && auth()->user()->peran_id === 1)
                                        <form action="{{ route('users.approve', $u->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-verify">
                                                <i class="fas fa-check mr-1"></i>Verify
                                            </button>
                                        </form>
                                        <form action="{{ route('users.reject', $u->id) }}" method="POST" class="d-inline ml-2">
                                            @csrf
                                            <button type="submit" class="btn btn-reject">
                                                <i class="fas fa-times mr-1"></i>Reject
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-secondary">Pending Review</span>
                                    @endif
                                @elseif($tab === 'rejected')
                                    @if(auth()->check() && auth()->user()->peran_id === 1)
                                        <form action="{{ route('users.approve', $u->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-verify">
                                                <i class="fas fa-check mr-1"></i>Verify
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger ml-2 btn-hapus-user" data-id="{{ $u->id }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                            @csrf @method('DELETE')
                                        </form>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                @else
                                    <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger ml-2 btn-hapus-user" data-id="{{ $u->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                        @csrf @method('DELETE')
                                    </form>
                                @endif
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
<script>
$(function () {
    const dataTableOptions = {
        language: {
            search: 'Cari:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            zeroRecords: 'Tidak ditemukan data yang sesuai',
            emptyTable: "Tidak ada data.",
            paginate: { next: 'Next', previous: 'Previous' }
        }
    };

    // Inisialisasi 2 wadah tabel
    $('#table-staff').DataTable(dataTableOptions);
    $('#table-mahasiswa').DataTable(dataTableOptions);

    $('.btn-hapus-user').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus User?',
            text: "Data akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#form-hapus-' + id).submit();
            }
        });
    });
});
</script>
@endpush
