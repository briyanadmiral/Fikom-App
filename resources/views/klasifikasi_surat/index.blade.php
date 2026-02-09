{{-- resources/views/klasifikasi_surat/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Klasifikasi Surat')

@section('content_header')
    <div class="custom-header-box">
        <div class="header-icon">
            <i class="fas fa-folder-open"></i>
        </div>
        <div>
            <div class="header-title">Klasifikasi Surat</div>
            <div class="header-desc">
                Kelola kode klasifikasi untuk surat tugas.
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

        /* Filter Tabs */
        .filter-tabs {
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .nav-pills .nav-link {
            color: #495057;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.2s;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .nav-pills .nav-link:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .nav-pills .nav-link.active {
            background-color: #007bff;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0,123,255,0.3);
        }

        .filter-label {
            font-weight: 600;
            color: #343a40;
            margin-right: 15px;
            display: flex;
            align-items: center;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #343a40;
            margin: 0;
            display: flex;
            align-items: center;
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

        /* Buttons */
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
            color: #fff !important;
        }

        /* Badge Kode */
        .badge-kode {
            background-color: #e9ecef;
            color: #343a40;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        /* Badge Usage */
        .badge-usage {
            background-color: #e2e6ea;
            color: #495057;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid #dee2e6;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .btn-view-usage:hover .badge-usage {
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

        /* Small Box */
        .small-box {
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        }

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
            .filter-label {
                margin-bottom: 10px;
                width: 100%;
                justify-content: center;
            }
            .nav-pills {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Statistics & Filter --}}
    @php
        $totalKlasifikasi = $list->count();
        $displayText = $searchTerm ? 'Hasil Pencarian' : ($activePrefix ? "Total Prefix {$activePrefix}" : 'Total Klasifikasi');
    @endphp

    <div class="row">
        <div class="col-12">
            {{-- Filter Tabs --}}
            <div class="filter-tabs d-flex flex-wrap align-items-center">
                <div class="filter-label">
                    <i class="fas fa-filter mr-2 text-primary"></i> Filter Abjad:
                </div>
                <ul class="nav nav-pills flex-grow-1" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ !$activePrefix && !$searchTerm ? 'active' : '' }}" 
                           href="{{ route('klasifikasi_surat.index') }}">
                           Semua
                        </a>
                    </li>
                    @foreach ($prefixes as $p)
                        <li class="nav-item">
                            <a class="nav-link {{ $activePrefix == $p ? 'active' : '' }}" 
                               href="{{ route('klasifikasi_surat.index', ['prefix' => $p]) }}">
                                {{ $p }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-table mr-2"></i> Data Klasifikasi Surat
                
                @if ($activePrefix)
                    <span class="badge badge-primary ml-2" style="font-size: 0.8rem;">
                        Prefix: {{ $activePrefix }}
                    </span>
                @endif

                @if ($searchTerm)
                    <span class="badge badge-success ml-2" style="font-size: 0.8rem;">
                        <i class="fas fa-search mr-1"></i> "{{ Str::limit($searchTerm, 20) }}"
                    </span>
                @endif
            </h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm" id="btnTambahKlasifikasi">
                    <i class="fas fa-plus mr-1"></i> Tambah Klasifikasi
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="klasifikasiTable" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th width="120">Kode</th>
                            <th>Deskripsi</th>
                            <th width="150" class="text-center">Penggunaan</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($list as $i => $item)
                            <tr>
                                <td class="font-weight-bold">{{ $i + 1 }}</td>
                                <td>
                                    <span class="badge-kode">{{ $item->kode }}</span>
                                </td>
                                <td>{{ $item->deskripsi }}</td>
                                <td class="text-center">
                                    <div class="btn-view-usage" 
                                         data-id="{{ $item->id }}"
                                         data-kode="{{ $item->kode }}"
                                         data-usage="{{ $item->tugas_headers_count ?? 0 }}">
                                        <span class="badge-usage" title="Klik untuk lihat riwayat penggunaan">
                                            <i class="fas fa-history mr-1"></i>
                                            {{ $item->tugas_headers_count ?? 0 }} Surat
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-action btn-edit btn-edit-klasifikasi"
                                        data-id="{{ $item->id }}" 
                                        data-kode="{{ $item->kode }}"
                                        data-deskripsi="{{ $item->deskripsi }}" 
                                        title="Edit Klasifikasi">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button data-url="{{ route('klasifikasi_surat.destroy', $item->id) }}"
                                        class="btn btn-action btn-delete" 
                                        title="Hapus Klasifikasi">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                                        <i class="fas {{ $searchTerm ? 'fa-search' : 'fa-folder-open' }} fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                                        <h5 class="font-weight-normal">
                                            @if ($searchTerm)
                                                Tidak Ada Hasil untuk "{{ $searchTerm }}"
                                            @elseif($activePrefix)
                                                Belum Ada Klasifikasi dengan Prefix "{{ $activePrefix }}"
                                            @else
                                                Belum Ada Klasifikasi
                                            @endif
                                        </h5>
                                        <p class="mb-0 small">
                                            @if ($searchTerm)
                                                Coba kata kunci lain atau <a href="{{ route('klasifikasi_surat.index', ['prefix' => $activePrefix]) }}">reset pencarian</a>
                                            @else
                                                Klik tombol "Tambah Klasifikasi" untuk menambahkan data baru
                                            @endif
                                        </p>
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

{{-- Modal Tambah Klasifikasi --}}
<div class="modal fade" id="modalTambahKlasifikasi" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formTambahKlasifikasi" method="POST" action="{{ route('klasifikasi_surat.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Klasifikasi Surat
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>1. Pilih Abjad <span class="text-danger">*</span></label>
                                <select class="form-control" id="tambah_prefix" name="prefix" required>
                                    <option value="">-- Pilih Abjad --</option>
                                    @foreach (range('A', 'Z') as $letter)
                                        <option value="{{ $letter }}">{{ $letter }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>2. Nomor Golongan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tambah_golongan" name="golongan" min="1" max="99" placeholder="1-99" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light border text-center" id="kodePreview" style="display: none;">
                        <small class="text-muted d-block text-uppercase font-weight-bold">Estimasi Kode</small>
                        <h3 class="mb-0 text-primary font-weight-bold" id="generatedCode">-</h3>
                    </div>

                    <div class="form-group">
                        <label>3. Deskripsi / Nama Klasifikasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tambah_deskripsi" name="deskripsi" placeholder="Contoh: Promosi" required>
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

{{-- Modal Edit Klasifikasi --}}
<div class="modal fade" id="modalEditKlasifikasi" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1050;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formEditKlasifikasi" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Edit Klasifikasi Surat
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode Klasifikasi</label>
                        <input type="text" id="edit_kode" class="form-control" disabled style="background-color: #e9ecef;">
                        <small class="text-muted">Kode tidak dapat diubah</small>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi <span class="text-danger">*</span></label>
                        <input type="text" name="deskripsi" id="edit_deskripsi" class="form-control" required>
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

{{-- Modal Quick View Usage --}}
<div class="modal fade" id="modalViewUsage" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-history mr-2"></i>Riwayat Penggunaan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center p-4">
                    <h1 class="display-4 text-primary font-weight-bold" id="usageCount">0</h1>
                    <p class="lead">Surat Tugas</p>
                    <p class="text-muted mb-0">Menggunakan kode klasifikasi <strong id="usageKode"></strong></p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
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
        $('#klasifikasiTable').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            }
        });

        // 1. Tambah Klasifikasi
        $('#btnTambahKlasifikasi').click(function() {
            $('#formTambahKlasifikasi')[0].reset();
            $('#kodePreview').hide();
            $('#modalTambahKlasifikasi').appendTo("body").modal('show');
        });

        // Auto Generate Code Preview
        $('#tambah_prefix, #tambah_golongan').change(function() {
            var prefix = $('#tambah_prefix').val();
            var golongan = $('#tambah_golongan').val();

            if (prefix && golongan) {
                $.ajax({
                    url: "{{ route('klasifikasi_surat.nextCode') }}",
                    method: 'GET',
                    data: { prefix: prefix, golongan: golongan },
                    success: function(response) {
                        $('#generatedCode').text(response.code);
                        $('#kodePreview').slideDown();
                    }
                });
            }
        });

        // 2. Edit Klasifikasi
        $(document).on('click', '.btn-edit-klasifikasi', function() {
            const id = $(this).data('id');
            const kode = $(this).data('kode');
            const deskripsi = $(this).data('deskripsi');
            const url = "{{ route('klasifikasi_surat.update', ':id') }}".replace(':id', id);
            
            $('#formEditKlasifikasi').attr('action', url);
            $('#edit_kode').val(kode);
            $('#edit_deskripsi').val(deskripsi);
            $('#modalEditKlasifikasi').appendTo("body").modal('show');
        });

        // 3. Quick View Usage
        $(document).on('click', '.btn-view-usage', function() {
            const kode = $(this).data('kode');
            const count = $(this).data('usage');
            
            $('#usageKode').text(kode);
            $('#usageCount').text(count);
            $('#modalViewUsage').appendTo("body").modal('show');
        });

        // 4. Delete with SweetAlert
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            const url = $(this).data('url');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $(`<form action="${url}" method="POST">
                        @csrf
                        @method('DELETE')
                    </form>`);
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
