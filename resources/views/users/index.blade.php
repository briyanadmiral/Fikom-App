{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@push('styles')
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
    .card-users {
        border-radius: 0.8rem;
        border: none;
        box-shadow: 0 4px 25px rgba(0,0,0, .07);
    }
    .card-users .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.2rem 1.5rem;
    }
    .card-users .card-footer {
        background-color: #fff;
        border-top: 1px solid #f0f0f0;
    }
    .table-users {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .table-users thead th {
        background: #f8f9fa;
        color: #555;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    .table-users tbody td {
        vertical-align: middle;
        font-size: 0.95rem;
        color: #333;
    }
    .table-users tbody tr:hover {
        background-color: #f3f6fa;
    }
    .user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background-color: #007bff;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1.1rem;
        box-shadow: 0 2px 4px rgba(0,123,255,.2);
    }
    .user-info .user-name {
        font-weight: 600;
        color: #212529;
    }
    .user-info .user-email {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .btn-action {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,.15);
    }
    .badge-peran {
        padding: 0.4em 0.8em;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .badge-status {
        padding: .5em .75em;
        font-size: .8rem;
        border-radius: 6px;
    }
</style>
@endpush

@section('content_header')
{{-- Bagian ini tidak diubah sesuai permintaan --}}
<div class="page-header mt-2 mb-3">
    <span class="icon">
        <i class="fas fa-users-cog text-white"></i>
    </span>
    <span>
        <div class="page-header-title">Manajemen Pengguna</div>
        <div class="page-header-desc">
            Kelola seluruh data <b>pengguna, peran, dan akses</b> dalam sistem. Tambahkan, edit, atau hapus pengguna di sini.
        </div>
    </span>
    <span class="ml-auto d-flex align-items-center gap-2">
        <a href="{{ route('users.create') }}" class="btn btn-primary shadow-sm mr-2">
            <i class="fas fa-user-plus mr-1"></i> Tambah Pengguna
        </a>
        <a href="#" class="btn btn-secondary shadow-sm" data-toggle="modal" data-target="#modal-peran">
            <i class="fas fa-user-tag mr-1"></i> Kelola Peran
        </a>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @include('users.peran.modal')

    <div class="card card-users">
        {{-- [BARU] Card Header untuk kontrol seperti pencarian --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 font-weight-bold">Daftar Pengguna</h5>
            <form action="{{ route('users.index') }}" method="GET" class="w-25">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari pengguna..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-users" id="table-users">
                    <thead>
                        <tr>
                            <th class="pl-4">Pengguna</th>
                            <th>NPP</th>
                            <th>Jabatan</th>
                            <th>Peran</th>
                            <th class="text-center">Status</th>
                            <th>Bergabung Pada</th>
                            <th class="text-center pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td class="pl-4">
                                <div class="d-flex align-items-center">
                                    {{-- [BARU] Avatar dengan inisial nama --}}
                                    <div class="user-avatar mr-3" style="background-color: {{ generate_color_from_string($u->nama_lengkap) }};">
                                        {{ get_initials($u->nama_lengkap) }}
                                    </div>
                                    {{-- [BARU] Grouping Nama dan Email --}}
                                    <div class="user-info">
                                        <div class="user-name">{{ $u->nama_lengkap }}</div>
                                        <div class="user-email">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $u->npp ?? '—' }}</td>
                            <td>{{ $u->jabatan ?? '—' }}</td>
                            <td>
                                {!! badge_peran(optional($u->peran)->nama ?? 'N/A') !!}
                            </td>
                            <td class="text-center">
                                @if($u->status == 'aktif')
                                    <span class="badge badge-success badge-status">Aktif</span>
                                @else
                                    <span class="badge badge-danger badge-status">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $u->created_at ? $u->created_at->isoFormat('D MMM YYYY') : '—' }}</td>
                            <td class="text-center pr-4">
                                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-warning btn-action" data-toggle="tooltip" title="Edit Pengguna">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-action btn-hapus-user" data-id="{{ $u->id }}" data-toggle="tooltip" title="Hapus Pengguna">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <form id="form-hapus-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-ghost fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Oops, tidak ada data pengguna yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- [BARU] Card Footer untuk pagination --}}
        @if ($users->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
             <div class="text-muted small">
                Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} hasil
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Inisialisasi Tooltip dari Bootstrap
    $('[data-toggle="tooltip"]').tooltip();

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