{{-- resources/views/sub_tugas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Sub Tugas - ' . $jenistugas->nama)

@section('content_header')
    <div class="custom-header-box">
        <div class="header-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div>
            <div class="header-title">Sub Tugas: {{ $jenistugas->nama }}</div>
            <div class="header-desc">
                Kelola sub tugas untuk jenis surat tugas ini.
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<style>
    /* Professional Header Box */
    .custom-header-box {
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #17a2b8;
        display: flex;
        align-items: center;
    }

    .header-icon {
        background-color: #e0f7fa;
        color: #17a2b8;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .header-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #343a40;
        line-height: 1.2;
    }

    .header-desc {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 4px;
    }

    /* Card Styling */
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border: none;
        border-radius: 6px;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 1rem 1.25rem;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 500;
        color: #343a40;
    }

    /* Table Styling */
    .table thead th {
        background-color: #f8f9fa;
        color: #343a40;
        border-bottom: 2px solid #dee2e6;
        padding: 0.75rem;
        vertical-align: middle;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .table td {
        vertical-align: middle !important;
        font-size: 0.95rem;
        color: #495057;
    }

    /* Action Buttons */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        margin: 0 2px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }

    .btn-edit {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-edit:hover {
        background-color: #e0a800;
        border-color: #d39e00;
        color: #212529;
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

    /* Modal Styling */
    .modal-content {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border-radius: 6px;
    }
    
    .modal-header {
        background-color: #f4f6f9;
        color: #343a40;
        border-bottom: 1px solid #dee2e6;
        border-radius: 6px 6px 0 0;
        padding: 1rem 1.5rem;
    }
    
    .modal-title {
        font-weight: 600;
        font-size: 1.1rem;
    }

    /* Small Box */
    .small-box {
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .custom-header-box {
            flex-direction: column;
            text-align: center;
            padding: 15px;
        }
        .header-icon {
            margin-right: 0;
            margin-bottom: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Statistics --}}
    @php
        $totalSub = $list->count();
    @endphp

    <div class="row">
        <div class="col-lg-12 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalSub }}</h3>
                    <p>Total Sub Tugas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1"></i> Data Sub Tugas</h3>
            <div class="card-tools">
                <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button class="btn btn-primary btn-sm" id="btnTambahSubTugas">
                    <i class="fas fa-plus mr-1"></i> Tambah Sub Tugas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-subtugas" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Sub Tugas</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $i => $item)
                            <tr>
                                <td class="font-weight-bold">{{ $i+1 }}</td>
                                <td><strong>{{ $item->nama }}</strong></td>
                                <td class="text-center">
                                    <button class="btn btn-action btn-edit btn-edit-subtugas"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->nama }}"
                                            title="Edit Sub Tugas">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form action="{{ route('sub_tugas.destroy', [$jenistugas->id, $item->id]) }}" 
                                          method="POST" 
                                          class="d-inline form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-action btn-delete btn-delete-subtugas" title="Hapus Sub Tugas">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                                        <i class="fas fa-tasks fa-3x mb-3 text-secondary"></i>
                                        <h5 class="font-weight-normal">Belum Ada Sub Tugas</h5>
                                        <p class="mb-0">Klik tombol "Tambah Sub Tugas" untuk menambahkan data baru</p>
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
<div class="modal fade" id="modalTambahSubTugas" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
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
                        <label>Nama Sub Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Pengajuan Biaya">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Sub Tugas --}}
<div class="modal fade" id="modalEditSubTugas" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
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
                        <label>Nama Sub Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
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
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Init DataTable
        $('#table-subtugas').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });

        // 1. Tambah Sub Tugas
        $('#btnTambahSubTugas').click(function() {
            $('#formTambahSubTugas')[0].reset();
            $('#modalTambahSubTugas').appendTo("body").modal('show');
        });

        // 2. Edit Sub Tugas
        $('.btn-edit-subtugas').click(function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const url = "{{ route('sub_tugas.update', [$jenistugas->id, ':id']) }}".replace(':id', id);
            
            $('#formEditSubTugas').attr('action', url);
            $('#edit_nama').val(nama);
            $('#modalEditSubTugas').appendTo("body").modal('show');
        });

        // 3. Delete with SweetAlert (Native Submit Fix)
        $(document).on('click', '.btn-delete-subtugas', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const form = $(this).closest('form');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data sub tugas akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form[0].submit();
                }
            });
        });
    });
</script>
@endpush
