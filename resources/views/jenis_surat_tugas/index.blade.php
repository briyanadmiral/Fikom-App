{{-- resources/views/jenis_surat_tugas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Daftar Jenis Surat Tugas')

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-list fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Daftar Jenis Surat Tugas</div>
                <div class="header-desc mt-2">
                    Halaman ini menampilkan semua jenis surat tugas yang tersedia dalam sistem.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
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

        /* Small box hover effect */
        .small-box {
            transition: transform 0.3s ease;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Card styling */
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

        /* Table styling */
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
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            border-left-color: #4389a2;
            transform: translateX(2px);
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem;
        }

        /* Button styling */
        .btn-action {
            padding: 0.4rem 0.75rem;
            border-radius: 5px;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-action:hover {
            transform: scale(1.05);
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
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 137, 162, 0.4);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
            animation: swing 2s ease-in-out infinite;
        }

        @keyframes swing {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(10deg); }
            75% { transform: rotate(-10deg); }
        }

        /* Alert animation */
        .alert {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
    
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Statistics Card --}}
    @php
        $totalJenis = $list->count();
    @endphp

    <div class="row">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalJenis }}</h3>
                    <p>Total Jenis Surat Tugas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-folder-open"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Jenis Surat Tugas</h3>
            <div class="card-tools">
                <a href="{{ route('jenis_surat_tugas.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Data
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-jenis" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th width="80">No</th>
                            <th>Nama Jenis Surat Tugas</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $i => $item)
                            <tr>
                                <td class="font-weight-bold">{{ $i+1 }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <a href="{{ route('jenis_surat_tugas.edit', $item->id) }}"
                                       class="btn btn-action btn-edit"
                                       title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button data-url="{{ route('jenis_surat_tugas.destroy', $item->id) }}"
                                            class="btn btn-action btn-delete"
                                            title="Hapus Data">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-0">
                                    <div class="empty-state">
                                        <i class="far fa-folder-open"></i>
                                        <h5>Belum Ada Data</h5>
                                        <p>Data jenis surat tugas akan muncul di sini</p>
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
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function() {
    // Auto-dismiss alerts after 5 seconds
    $('.alert').delay(5000).fadeOut('slow');

    // Initialize DataTable
    $('#table-jenis').DataTable({
        paging: false,
        info: false,
        searching: false,
        columnDefs: [
            { orderable: false, targets: 2 }
        ],
        language: {
            emptyTable: "Belum ada data jenis surat tugas"
        }
    });

    // Delete confirmation with SweetAlert
    $('body').on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const url = $(this).data('url');
        
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data akan dihapus permanen dari sistem",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus Data...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form
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
});
</script>
@endpush
