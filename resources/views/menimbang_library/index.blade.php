@extends('layouts.app')

@section('title', 'Library Menimbang')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    body { background: #f7faff }
    .surat-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem 1.3rem 1.8rem;
        border-radius: 1.1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e0e6ed;
        display: flex;
        align-items: center;
        gap: 1.3rem
    }
    .surat-header .icon {
        background: linear-gradient(135deg, #6f42c1 0, #9b59b6 100%);
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px rgba(111,66,193,0.3);
        font-size: 1.8rem
    }
    .surat-header-title {
        font-weight: bold;
        color: #6f42c1;
        font-size: 1.6rem;
        margin-bottom: .13rem;
        letter-spacing: -0.5px
    }
    .surat-header-desc {
        color: #636e7b;
        font-size: 1rem
    }
    .card { border-radius: 1rem; }
    @media (max-width:767.98px) {
        .surat-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.2rem 1rem;
            gap: .7rem
        }
        .surat-header-title { font-size: 1.18rem }
    }
</style>
@endpush

@section('content_header')
    <div class="surat-header mt-2">
        <span class="icon">
            <i class="fas fa-book-open text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">Library Menimbang (SK)</div>
            <div class="surat-header-desc">Kelola poin-poin <b>Menimbang</b> yang digunakan dalam Surat Keputusan.</div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('menimbang_library.index') }}" class="form-inline">
                        <div class="input-group input-group-sm mr-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <select name="kategori" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('menimbang_library.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Baru
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table id="table-menimbang" class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px">#</th>
                        <th>Judul</th>
                        <th style="width: 120px">Kategori</th>
                        <th style="width: 80px" class="text-center">Dipakai</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->judul }}</strong>
                                <br><small class="text-muted">{{ Str::limit(strip_tags($item->isi), 100) }}</small>
                            </td>
                            <td>
                                @if($item->kategori)
                                    <span class="badge badge-info">{{ $item->kategori }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $item->usage_count }}</span>
                            </td>
                            <td>
                                <a href="{{ route('menimbang_library.edit', $item->id) }}" class="btn btn-xs btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('menimbang_library.destroy', $item->id) }}" method="POST" class="d-inline form-delete-menimbang">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Belum ada data library menimbang.
                            </td>
                        </tr>
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
<script>
$(function() {
    const table = $('#table-menimbang').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        dom: 't<"row align-items-center px-3 py-2"<"col-sm-6"i><"col-sm-6 text-sm-right"p>>',
        language: {
            url: '/assets/datatables/i18n/id.json',
            emptyTable: 'Belum ada data library menimbang.'
        },
        columnDefs: [
            { targets: [0, -1], orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    $('.form-delete-menimbang').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Hapus Poin Menimbang?',
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});
</script>
@endpush
