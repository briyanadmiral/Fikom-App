{{-- resources/views/klasifikasi_surat/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Klasifikasi Surat')

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-folder-open fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Klasifikasi Surat</div>
                <div class="header-desc mt-2">
                    Kelola kode klasifikasi untuk surat tugas
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    @push('styles')
        <style>
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

            /* 🆕 Filter Tabs Styling */
            .filter-tabs {
                background: #fff;
                border-radius: 0.5rem;
                padding: 0.75rem 1rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                border: 1px solid #e9ecef;
            }

            .filter-tabs .nav-pills .nav-link {
                border-radius: 0.375rem;
                padding: 0.5rem 1rem;
                margin: 0 0.25rem;
                font-weight: 600;
                color: #495057;
                border: 1px solid #dee2e6;
                background: #f8f9fa;
            }

            .filter-tabs .nav-pills .nav-link:hover {
                background: #e9ecef;
                color: #212529;
            }

            .filter-tabs .nav-pills .nav-link.active {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                border-color: transparent;
            }

            .filter-label {
                font-weight: 600;
                color: #495057;
                margin-right: 1rem;
                align-self: center;
            }

            .card {
                border-radius: 0.5rem;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
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

            .badge-kode {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                padding: 0.35rem 0.65rem;
                border-radius: 0.375rem;
                font-weight: 600;
                font-size: 0.9rem;
                font-family: 'Courier New', monospace;
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

            /* 🆕 Auto-generated code display */
            .code-preview {
                background: #f8f9fa;
                border: 2px dashed #667eea;
                padding: 1rem;
                border-radius: 0.5rem;
                text-align: center;
                margin-bottom: 1rem;
            }

            .code-preview .code-label {
                font-size: 0.875rem;
                color: #6c757d;
                font-weight: 600;
                margin-bottom: 0.5rem;
            }

            .code-preview .code-value {
                font-size: 1.75rem;
                font-weight: 700;
                color: #667eea;
                font-family: 'Courier New', monospace;
                letter-spacing: 2px;
            }

            /* 🆕 Readonly kode in edit modal */
            .kode-display {
                background: #e9ecef;
                border: 2px solid #667eea;
                padding: 0.75rem;
                border-radius: 0.5rem;
                text-align: center;
                font-size: 1.25rem;
                font-weight: 700;
                color: #667eea;
                font-family: 'Courier New', monospace;
                letter-spacing: 2px;
                margin-bottom: 1rem;
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

                .filter-tabs {
                    overflow-x: auto;
                }

                .filter-tabs .nav-pills {
                    flex-wrap: nowrap;
                }
            }
        </style>
    @endpush

    @section('content')
        <div class="container-fluid">

            {{-- 🆕 Filter by Prefix (Abjad) --}}
            <div class="filter-tabs">
                <div class="d-flex align-items-center">
                    <span class="filter-label">
                        <i class="fas fa-filter mr-1"></i>Filter Abjad:
                    </span>
                    <ul class="nav nav-pills flex-grow-1 mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ !$activePrefix ? 'active' : '' }}"
                                href="{{ route('klasifikasi_surat.index', ['search' => $searchTerm]) }}">
                                Semua
                            </a>
                        </li>
                        @foreach ($prefixes as $prefix)
                            <li class="nav-item">
                                <a class="nav-link {{ $activePrefix === $prefix ? 'active' : '' }}"
                                    href="{{ route('klasifikasi_surat.index', ['prefix' => $prefix, 'search' => $searchTerm]) }}">
                                    {{ $prefix }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- 🆕 Search Box --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('klasifikasi_surat.index') }}" class="form-inline">
                        {{-- Preserve prefix filter --}}
                        @if ($activePrefix)
                            <input type="hidden" name="prefix" value="{{ $activePrefix }}">
                        @endif

                        <div class="input-group" style="width: 100%; max-width: 500px;">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari berdasarkan kode atau deskripsi..." value="{{ $searchTerm ?? '' }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                @if ($searchTerm)
                                    <a href="{{ route('klasifikasi_surat.index', ['prefix' => $activePrefix]) }}"
                                        class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Search hint --}}
                        @if ($searchTerm)
                            <small class="text-muted ml-3">
                                Hasil pencarian untuk: <strong>"{{ $searchTerm }}"</strong>
                            </small>
                        @endif
                    </form>
                </div>
            </div>


            {{-- Statistics Card --}}
            @php
                $totalKlasifikasi = $list->count();
                $displayText = $searchTerm
                    ? 'Hasil Pencarian'
                    : ($activePrefix
                        ? "Klasifikasi dengan Prefix {$activePrefix}"
                        : 'Total Klasifikasi Surat');
            @endphp

            <div class="row">
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="small-box {{ $searchTerm ? 'bg-success' : ($activePrefix ? 'bg-purple' : 'bg-primary') }}">
                        <div class="inner">
                            <h3>{{ $totalKlasifikasi }}</h3>
                            <p>{{ $displayText }}</p>
                            @if ($searchTerm)
                                <small>Kata kunci: "{{ $searchTerm }}"</small>
                            @endif
                        </div>
                        <div class="icon">
                            <i class="fas {{ $searchTerm ? 'fa-search' : 'fa-folder-open' }}"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Card --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        Data Klasifikasi Surat

                        {{-- Active filter badges --}}
                        @if ($activePrefix)
                            <span class="badge badge-light ml-2">
                                <i class="fas fa-filter"></i> Prefix: {{ $activePrefix }}
                            </span>
                        @endif

                        @if ($searchTerm)
                            <span class="badge badge-success ml-2">
                                <i class="fas fa-search"></i> "{{ Str::limit($searchTerm, 20) }}"
                            </span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" id="btnTambahKlasifikasi">
                            <i class="fas fa-plus"></i> Tambah Klasifikasi
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="klasifikasiTable" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="80">No</th>
                                    <th width="200">Kode</th>
                                    <th>Deskripsi</th>
                                    <th width="200" class="text-center">Aksi</th>
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
                                            {{-- Tombol Edit --}}
                                            <button class="btn btn-action btn-edit btn-edit-klasifikasi"
                                                data-id="{{ $item->id }}" data-kode="{{ $item->kode }}"
                                                data-deskripsi="{{ $item->deskripsi }}" title="Edit Klasifikasi">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <button data-url="{{ route('klasifikasi_surat.destroy', $item->id) }}"
                                                class="btn btn-action btn-delete" title="Hapus Klasifikasi">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="empty-state">
                                                <i class="fas {{ $searchTerm ? 'fa-search' : 'fa-folder-open' }}"></i>
                                                <h5>
                                                    @if ($searchTerm)
                                                        Tidak Ada Hasil untuk "{{ $searchTerm }}"
                                                    @elseif($activePrefix)
                                                        Belum Ada Klasifikasi dengan Prefix "{{ $activePrefix }}"
                                                    @else
                                                        Belum Ada Klasifikasi
                                                    @endif
                                                </h5>
                                                <p>
                                                    @if ($searchTerm)
                                                        Coba kata kunci lain atau
                                                        <a
                                                            href="{{ route('klasifikasi_surat.index', ['prefix' => $activePrefix]) }}">reset
                                                            pencarian</a>
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

        {{-- 🆕 Modal Tambah Klasifikasi (dengan Abjad + Golongan + Auto Sub-nomor) --}}
        <div class="modal fade" id="modalTambahKlasifikasi" tabindex="-1" role="dialog" aria-hidden="true">
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
                            {{-- Row 1: Abjad & Golongan --}}
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Pilih Abjad/Prefix --}}
                                    <div class="form-group">
                                        <label for="tambah_prefix">
                                            1. Pilih Abjad <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" id="tambah_prefix" name="prefix" required>
                                            <option value="">-- Pilih Abjad --</option>
                                            @foreach (range('A', 'Z') as $letter)
                                                <option value="{{ $letter }}">{{ $letter }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Contoh: A, B, C</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    {{-- Input Nomor Golongan --}}
                                    <div class="form-group">
                                        <label for="tambah_golongan">
                                            2. Nomor Golongan <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="tambah_golongan" name="golongan"
                                            min="1" max="99" placeholder="1-99" required>
                                        <small class="form-text text-muted">
                                            <span id="golonganHint">Angka 1-99</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview Kode (Auto-generated) --}}
                            <div class="code-preview" id="kodePreview" style="display: none;">
                                <div class="code-label">
                                    <i class="fas fa-magic mr-1"></i>Kode yang Akan Dibuat:
                                </div>
                                <div class="code-value" id="generatedCode">-</div>
                                <small class="text-muted">
                                    Sub-nomor akan otomatis di-generate
                                </small>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="form-group">
                                <label for="tambah_deskripsi">
                                    3. Deskripsi / Nama Klasifikasi <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="tambah_deskripsi" name="deskripsi"
                                    placeholder="Contoh: Promosi" required>
                                <small class="form-text text-muted">
                                    Jelaskan kategori klasifikasi ini
                                </small>
                            </div>

                            {{-- Info Box --}}
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Cara Kerja:</strong>
                                <ul class="mb-0 pl-3 mt-2">
                                    <li>Pilih <strong>Abjad</strong> (A-Z) sebagai kategori utama</li>
                                    <li>Pilih <strong>Nomor Golongan</strong> (1-99) sebagai sub-kategori</li>
                                    <li>Sistem akan <strong>otomatis generate</strong> sub-nomor terakhir</li>
                                    <li>Contoh: Jika sudah ada A.1.5, maka kode baru: <code>A.1.6</code></li>
                                </ul>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSubmitTambah" disabled>
                                <i class="fas fa-save mr-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        {{-- 🆕 Modal Edit Klasifikasi (dengan Display Kode) --}}
        <div class="modal fade" id="modalEditKlasifikasi" tabindex="-1" role="dialog" aria-hidden="true">
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
                            {{-- 🆕 Display Kode (Readonly) --}}
                            <div class="form-group">
                                <label>Kode Klasifikasi</label>
                                <div class="kode-display" id="edit_kode_display">-</div>
                                <small class="form-text text-muted">Kode tidak dapat diubah</small>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="form-group">
                                <label for="edit_deskripsi">Deskripsi / Nama Klasifikasi <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_deskripsi" name="deskripsi" required>
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
                // 🆕 Initialize DataTables
                // ========================================
                const table = $('#klasifikasiTable').DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 20, // Default 20 rows per page
                    lengthMenu: [
                        [10, 20, 25, 50, 100, -1],
                        [10, 20, 25, 50, 100, "Semua"]
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                        emptyTable: "Tidak ada data klasifikasi surat",
                        zeroRecords: "Tidak ditemukan data yang sesuai",
                        lengthMenu: "Tampilkan _MENU_ data per halaman",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(difilter dari _MAX_ total data)",
                        search: "Cari:",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    },
                    order: [
                        [1, 'asc']
                    ], // Sort by Kode column
                    columnDefs: [{
                            orderable: false,
                            targets: [3]
                        }, // Disable sorting on Aksi column
                        {
                            className: 'text-center',
                            targets: [0, 3]
                        } // Center align No & Aksi
                    ]
                });

                // ========================================
                // 🆕 AJAX: Auto-generate kode saat prefix & golongan terisi
                // ========================================
                function generateCode() {
                    const prefix = $('#tambah_prefix').val();
                    const golongan = $('#tambah_golongan').val();

                    if (!prefix || !golongan || golongan < 1 || golongan > 99) {
                        $('#kodePreview').hide();
                        $('#generatedCode').text('-');
                        $('#btnSubmitTambah').prop('disabled', true);
                        return;
                    }

                    // Show loading
                    $('#kodePreview').show();
                    $('#generatedCode').html('<i class="fas fa-spinner fa-spin"></i> Generating...');
                    $('#btnSubmitTambah').prop('disabled', true);

                    // AJAX request untuk get next code
                    $.ajax({
                        url: '{{ route('klasifikasi_surat.nextCode') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            prefix: prefix,
                            golongan: golongan
                        },
                        success: function(response) {
                            $('#generatedCode').text(response.code);
                            $('#btnSubmitTambah').prop('disabled', false);

                            // Update hint
                            $('#golonganHint').html(
                                `Golongan <strong>${golongan}</strong> pada abjad <strong>${prefix}</strong>`
                            );
                        },
                        error: function(xhr) {
                            $('#generatedCode').text('Error!');
                            $('#btnSubmitTambah').prop('disabled', true);

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal generate kode. Coba lagi!',
                                timer: 2000
                            });
                        }
                    });
                }

                // Trigger auto-generate saat prefix atau golongan berubah
                $('#tambah_prefix, #tambah_golongan').on('change input', function() {
                    generateCode();
                });

                // ========================================
                // 🆕 AJAX: Load existing golongan saat prefix dipilih
                // ========================================
                $('#tambah_prefix').on('change', function() {
                    const prefix = $(this).val();

                    if (!prefix) {
                        $('#golonganHint').text('Angka 1-99');
                        return;
                    }

                    // Load existing golongan untuk info
                    $.ajax({
                        url: '{{ route('klasifikasi_surat.getGolongan') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            prefix: prefix
                        },
                        success: function(response) {
                            if (response.golongan && response.golongan.length > 0) {
                                const golonganList = response.golongan.join(', ');
                                $('#golonganHint').html(
                                    `Golongan yang sudah ada: <strong>${golonganList}</strong>`
                                );
                            } else {
                                $('#golonganHint').html(
                                    `Belum ada golongan pada abjad <strong>${prefix}</strong>`
                                );
                            }
                        }
                    });
                });

                // ========================================
                // Tombol Tambah - buka modal
                // ========================================
                $('#btnTambahKlasifikasi').on('click', function() {
                    $('#tambah_prefix').val('');
                    $('#tambah_golongan').val('');
                    $('#tambah_deskripsi').val('');
                    $('#kodePreview').hide();
                    $('#generatedCode').text('-');
                    $('#golonganHint').text('Angka 1-99');
                    $('#btnSubmitTambah').prop('disabled', true);
                    $('#modalTambahKlasifikasi').modal('show');
                });

                // ========================================
                // Tombol Edit - buka modal dengan display kode
                // ========================================
                $('body').on('click', '.btn-edit-klasifikasi', function() {
                    const id = $(this).data('id');
                    const kode = $(this).data('kode');
                    const deskripsi = $(this).data('deskripsi');

                    // Set form action
                    const actionUrl = `/klasifikasi_surat/${id}`;
                    $('#formEditKlasifikasi').attr('action', actionUrl);

                    // Display kode (readonly)
                    $('#edit_kode_display').text(kode);
                    $('#edit_deskripsi').val(deskripsi);

                    // Show modal
                    $('#modalEditKlasifikasi').modal('show');
                });

                // ========================================
                // Tombol Hapus - konfirmasi SweetAlert
                // ========================================
                $('body').on('click', '.btn-delete', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');

                    Swal.fire({
                        title: 'Hapus Klasifikasi?',
                        text: "Data klasifikasi surat akan dihapus permanen!",
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
                                .append('@method('DELETE')')
                                .appendTo('body')
                                .submit();
                        }
                    });
                });

                // ========================================
                // Flash Messages
                // ========================================
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: "{{ session('success') }}",
                        timer: 3000,
                        showConfirmButton: false
                    });
                @endif

                @if (session('error'))
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
