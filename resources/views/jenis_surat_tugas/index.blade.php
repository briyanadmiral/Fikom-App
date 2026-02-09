{{-- resources/views/jenis_surat_tugas/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Daftar Jenis Surat Tugas')

@section('content_header')
    <div class="custom-header-box">
        <div class="header-icon">
            <i class="fas fa-list"></i>
        </div>
        <div>
            <div class="header-title">Daftar Jenis Surat Tugas</div>
            <div class="header-desc">
                Halaman ini menampilkan semua jenis surat tugas yang tersedia dalam sistem.
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
        border-left: 4px solid #007bff;
        display: flex;
        align-items: center;
    }

    .header-icon {
        background-color: #e7f2ff;
        color: #007bff;
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
    
    /* Sub Tugas Badge */
    .badge-sub-tugas {
        background-color: #e9ecef;
        color: #495057;
        font-size: 0.85rem;
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid #dee2e6;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    
    .btn-view-subtugas:hover .badge-sub-tugas {
        background-color: #dee2e6;
        color: #212529;
        border-color: #ced4da;
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
    
    /* DataTables Pagination Overrides */
    .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }
    
    .page-link {
        color: #007bff;
    }

    /* Small Box (Stat Cards if any) */
    .small-box {
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    
    /* Responsive Fixes */
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

    /* Professional Button Colors */
    .btn-edit {
        color: #212529 !important;
        background-color: #ffc107 !important;
        border-color: #ffc107 !important;
    }
    .btn-edit:hover {
        background-color: #e0a800 !important;
        border-color: #d39e00 !important;
        color: #212529 !important;
    }
    .btn-delete {
        color: #fff !important;
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }
    .btn-delete:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }
</style>

@endpush

@section('content')
<div class="container-fluid">

    {{-- Statistics Card --}}
    @php
        $totalJenis = $list->count();
        $totalSubTugas = $list->sum(function($item) {
            return $item->subTugas->count();
        });
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
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalSubTugas }}</h3>
                    <p>Total Sub Tugas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Data Jenis Surat Tugas</h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm" id="btnTambahJenis">
                    <i class="fas fa-plus"></i> Tambah Data
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-jenis" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th width="80">No</th>
                            <th>Nama Jenis Surat Tugas</th>
                            <th width="150" class="text-center">Sub Tugas</th>
                            <th width="220" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $i => $item)
                            <tr>
                                <td class="font-weight-bold">{{ $i+1 }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-center">
                                    <div class="btn-view-subtugas" 
                                         data-nama="{{ $item->nama }}"
                                         data-subtugas='@json($item->subTugas)'>
                                        <span class="badge-sub-tugas" title="Klik untuk lihat detail" style="cursor: pointer;">
                                            <i class="fas fa-eye mr-1"></i>
                                            {{ $item->subTugas->count() }} Sub
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    {{-- Tombol Kelola Sub Tugas --}}
                                    <a href="{{ route('sub_tugas.index', $item->id) }}"
                                       class="btn btn-action btn-subtugas"
                                       title="Kelola Sub Tugas">
                                        <i class="fas fa-list-ul"></i>
                                    </a>
                                    
                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-action btn-edit btn-edit-jenis"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->nama }}"
                                            title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    {{-- Tombol Hapus --}}
                                    <button data-url="{{ route('jenis_surat_tugas.destroy', $item->id) }}"
                                            class="btn btn-action btn-delete"
                                            title="Hapus Data">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-0">
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

{{-- Modal Tambah Jenis Surat Tugas --}}
<div class="modal fade" id="modalTambahJenis" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formTambahJenis" method="POST" action="{{ route('jenis_surat_tugas.store') }}">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Jenis Surat Tugas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tambah_nama">Nama Jenis Surat Tugas <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="tambah_nama" 
                               name="nama" 
                               placeholder="Contoh: Surat Tugas Perjalanan Dinas"
                               required>
                        <small class="form-text text-muted">Nama harus unik dan belum terdaftar</small>
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

{{-- Modal Edit Jenis Surat Tugas --}}
<div class="modal fade" id="modalEditJenis" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formEditJenis" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Edit Jenis Surat Tugas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama">Nama Jenis Surat Tugas <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_nama" 
                               name="nama" 
                               required>
                        <small class="form-text text-muted">Nama harus unik dan belum terdaftar</small>
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
    {{-- Include Modal View Sub Tugas --}}
    @include('jenis_surat_tugas.partials.modal_view_subtugas')

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
    const table = $('#table-jenis').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 20,
        lengthMenu: [[10, 20, 25, 50, 100, -1], [10, 20, 25, 50, 100, "Semua"]],
        language: {
            "emptyTable": "Tidak ada data yang tersedia pada tabel ini",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
            "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "loadingRecords": "Sedang memuat...",
            "processing": "Sedang memproses...",
            "search": "Cari:",
            "zeroRecords": "Tidak ditemukan data yang sesuai",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            },
            "aria": {
                "sortAscending": ": aktifkan untuk mengurutkan kolom ke atas",
                "sortDescending": ": aktifkan untuk mengurutkan kolom ke bawah"
            }
        },
        order: [[1, 'asc']], // Sort by Nama
        columnDefs: [
            { orderable: false, targets: [2, 3] } // Sub Tugas & Aksi tidak bisa sort
        ]
    });

    // ========================================
    // Tombol Tambah - buka modal
    // ========================================
    $('#btnTambahJenis').on('click', function() {
        $('#tambah_nama').val('');
        $('#modalTambahJenis').modal('show');
    });

    // ========================================
    // Tombol Edit - buka modal
    // ========================================
    $('body').on('click', '.btn-edit-jenis', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        // Set form action
        const actionUrl = `/jenis_surat_tugas/${id}`;
        $('#formEditJenis').attr('action', actionUrl);
        
        // Fill input
        $('#edit_nama').val(nama);
        
        // Show modal
        $('#modalEditJenis').modal('show');
    });

    // ========================================
    // Tombol View Sub Tugas - buka modal
    // ========================================
    $('body').on('click', '.btn-view-subtugas', function() {
        const nama = $(this).data('nama');
        const subTugas = $(this).data('subtugas'); // Auto-parsed from JSON
        const id = $(this).closest('tr').find('.btn-edit-jenis').data('id'); // Get ID for link
        
        // Update modal content
        $('#modalViewSubTugas .modal-title').html(`<i class="fas fa-tasks mr-2"></i> ${nama}`);
        $('#btnManageSubTugas').attr('href', `/jenis_surat_tugas/${id}/sub_tugas`);
        
        let html = '';
        
        if (subTugas.length > 0) {
            html += '<ul class="list-group list-group-flush">';
            subTugas.forEach((item, index) => {
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge badge-secondary mr-2">${index + 1}</span>
                            <span class="font-weight-bold">${item.nama}</span>
                        </div>
                        <i class="fas fa-check-circle text-success"></i>
                    </li>
                `;
            });
            html += '</ul>';
        } else {
            html = `
                <div class="text-center p-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 text-light-gray"></i>
                    <h5>Belum ada Sub Tugas</h5>
                    <p class="mb-0">Silakan tambahkan sub tugas baru.</p>
                </div>
            `;
        }
        
        $('#subTugasList').html(html);
        
        // Move modal to body to prevent z-index issues
        $('#modalViewSubTugas').appendTo("body");
        
        $('#modalViewSubTugas').modal('show');
    });

    // ========================================
    // Tombol Hapus - konfirmasi SweetAlert
    // ========================================
    $('body').on('click', '.btn-delete', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // ✅ PREVENT GLOBAL HANDLER FROM FIRING!
        
        const url = $(this).data('url');
        
        console.log('🔴 DELETE BUTTON CLICKED');
        console.log('URL:', url);
        
        Swal.fire({
            title: 'Hapus Data?',
            text: "Jenis Tugas beserta SUB TUGAS nya akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times mr-1"></i>Batal',
            reverseButtons: true
        }).then((result) => {
            console.log('🟡 SWAL RESULT:', result);
            
            if (result.isConfirmed) {
                console.log('✅ USER CONFIRMED DELETE');
                
                try {
                    // Create form using native DOM to bypass anti-injection.js interference
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    
                    console.log('📝 Form created:', form);
                    
                    // Add CSRF token
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    tokenInput.value = '{{ csrf_token() }}';
                    form.appendChild(tokenInput);
                    
                    console.log('🔐 CSRF token added');
                    
                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    console.log('🔧 Method DELETE added');
                    
                    // Append to body and submit
                    document.body.appendChild(form);
                    console.log('📤 Form appended to body, submitting...');
                    
                    form.submit();
                    console.log('✅ Form.submit() called - should redirect now!');
                } catch (error) {
                    console.error('❌ ERROR during form submission:', error);
                }
            } else {
                console.log('❌ USER CANCELLED DELETE');
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
