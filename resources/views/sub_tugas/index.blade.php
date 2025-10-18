{{-- resources/views/sub_tugas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Sub Tugas - ' . $jenistugas->nama)

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-tasks fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Sub Tugas: {{ $jenistugas->nama }}</div>
                <div class="header-desc mt-2">
                    Kelola sub tugas untuk jenis surat tugas ini
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
    .custom-header-box {
        background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(44, 62, 80, .13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #3498db;
        margin-top: .5rem;
    }

    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, .15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(52, 152, 219, .13);
    }

    .header-title {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }

    .header-desc {
        font-size: 1.07rem;
        color: #e9f3fa;
        font-weight: 400;
        margin-left: .1rem;
    }

    .breadcrumb {
        background: transparent;
        padding: 0.75rem 0;
        margin-bottom: 1rem;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        color: #6c757d;
    }

    .breadcrumb-item a {
        color: #4389a2;
        text-decoration: none;
        font-weight: 500;
    }

    .breadcrumb-item a:hover {
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: #495057;
        font-weight: 600;
    }

    .small-box {
        transition: transform 0.3s ease;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .card {
        border-radius: 0.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
    }

    .card-header {
        background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
        color: white;
        border-radius: 0.5rem 0.5rem 0 0 !important;
        padding: 1rem 1.25rem;
        border: none;
    }

    .card-title {
        font-weight: 600;
        margin: 0;
    }

    .table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem;
    }

    .table tbody tr {
        border-left: 3px solid transparent;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        border-left-color: #4389a2;
    }

    .table tbody td {
        vertical-align: middle;
        padding: 1rem;
    }

    .btn-action {
        padding: 0.4rem 0.75rem;
        border-radius: 5px;
        font-size: 0.875rem;
        margin: 0 2px;
    }

    .btn-edit {
        color: #fff;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-edit:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: #fff;
    }

    .btn-delete {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: #fff;
    }

    .btn-primary {
        background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
        border: none;
    }

    .btn-primary:hover {
        box-shadow: 0 4px 12px rgba(67, 137, 162, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .badge-detail {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        padding: 0.4rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    .modal-header {
        background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
        color: white;
        border-radius: 0.3rem 0.3rem 0 0;
    }

    .modal-header .close {
        color: white;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length select {
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
    }

    .dataTables_wrapper .dataTables_filter input {
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.25rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        border: none !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
        color: #6c757d;
    }

    @media (max-width: 575.98px) {
        .custom-header-box {
            padding: 1.1rem;
        }

        .header-icon {
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
        }

        .header-title {
            font-size: 1.2rem;
        }

        .header-desc {
            margin-left: 0;
            font-size: .98rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Statistics Card --}}
    @php
        $totalSubTugas = $list->count();
        $totalDetail = $list->sum(function($item) {
            return $item->detail->count();
        });
    @endphp

    <div class="row">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSubTugas }}</h3>
                    <p>Total Sub Tugas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalDetail }}</h3>
                    <p>Total Detail Tugas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Sub Tugas</h3>
            <div class="card-tools">
                <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button class="btn btn-primary btn-sm" id="btnTambahSubTugas">
                    <i class="fas fa-plus"></i> Tambah Sub Tugas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-subtugas" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th width="80">No</th>
                            <th>Nama Sub Tugas</th>
                            <th width="150" class="text-center">Detail Tugas</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $i => $item)
                            <tr>
                                <td class="font-weight-bold">{{ $i+1 }}</td>
                                <td>
                                    <strong>{{ $item->nama }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge-detail">
                                        <i class="fas fa-list mr-1"></i>
                                        {{ $item->detail->count() }} Detail
                                    </span>
                                </td>
                                <td class="text-center">
                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-action btn-edit btn-edit-subtugas"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->nama }}"
                                            title="Edit Sub Tugas">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    {{-- Tombol Hapus --}}
                                    <button data-url="{{ route('sub_tugas.destroy', [$jenistugas->id, $item->id]) }}"
                                            class="btn btn-action btn-delete"
                                            title="Hapus Sub Tugas">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-0">
                                    <div class="empty-state">
                                        <i class="fas fa-tasks"></i>
                                        <h5>Belum Ada Sub Tugas</h5>
                                        <p>Klik tombol "Tambah Sub Tugas" untuk menambahkan data baru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal Tambah Sub Tugas --}}
<div class="modal fade" id="modalTambahSubTugas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formTambahSubTugas" method="POST" action="{{ route('sub_tugas.store', $jenistugas->id) }}">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Sub Tugas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tambah_nama">Nama Sub Tugas <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="tambah_nama" 
                               name="nama" 
                               placeholder="Contoh: Koordinator kelompok MK/Rumpun/Konsorsium"
                               required>
                        <small class="form-text text-muted">
                            Sub tugas untuk: <strong>{{ $jenistugas->nama }}</strong>
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Sub Tugas --}}
<div class="modal fade" id="modalEditSubTugas" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formEditSubTugas" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Edit Sub Tugas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama">Nama Sub Tugas <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_nama" 
                               name="nama" 
                               required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update
                    </button>
                </div>
            </form>
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
$(function() {
    // ========================================
    // Initialize DataTables
    // ========================================
    const table = $('#table-subtugas').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 20,
        lengthMenu: [[10, 20, 25, 50, 100, -1], [10, 20, 25, 50, 100, "Semua"]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
            emptyTable: "Belum ada data sub tugas",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            search: "Cari:"
        },
        order: [[1, 'asc']], // Sort by Nama
        columnDefs: [
            { orderable: false, targets: [2, 3] } // Detail Tugas & Aksi tidak bisa sort
        ]
    });

    // ========================================
    // Tombol Tambah - buka modal
    // ========================================
    $('#btnTambahSubTugas').on('click', function() {
        $('#tambah_nama').val('');
        $('#modalTambahSubTugas').modal('show');
    });

    // ========================================
    // Tombol Edit - buka modal
    // ========================================
    $('body').on('click', '.btn-edit-subtugas', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const jenistugasId = {{ $jenistugas->id }};
        
        // Set form action
        const actionUrl = `/jenis_surat_tugas/${jenistugasId}/sub_tugas/${id}`;
        $('#formEditSubTugas').attr('action', actionUrl);
        
        // Fill input
        $('#edit_nama').val(nama);
        
        // Show modal
        $('#modalEditSubTugas').modal('show');
    });

    // ========================================
    // Tombol Hapus - konfirmasi SweetAlert
    // ========================================
    $('body').on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        
        Swal.fire({
            title: 'Hapus Sub Tugas?',
            text: "Sub tugas beserta DETAIL TUGAS nya akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i>Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Data...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $('<form>', {
                    method: 'POST',
                    action: url
                })
                .append('@csrf')
                .append('@method("DELETE")')
                .appendTo('body')
                .submit();
            }
        });
    });

    // ========================================
    // Flash Messages
    // ========================================
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});
</script>
@endpush
