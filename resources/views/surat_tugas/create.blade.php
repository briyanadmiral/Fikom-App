@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }

    #penerima-list .list-group-item {
        padding: 0.65rem 1rem;
        border-color: #e9ecef;
    }

    #penerima-list .eksternal-label {
        color: #198754;
        font-size: .92em;
        margin-left: 3px;
    }

    .list-group-item-action {
        transition: background-color 0.15s ease-in-out;
    }

    #penerima-table thead th {
        vertical-align: middle;
        text-align: center;
    }

    #penerima-table tbody td:first-child {
        text-align: center;
    }

    #task-preview {
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: .25rem;
        padding: 1.5rem;
        min-height: 158px;
        transition: all 0.3s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #task-preview.has-content {
        align-items: flex-start;
        justify-content: flex-start;
    }

    #task-preview .placeholder-text {
        color: #6c757d;
        font-style: italic;
    }

    #task-preview .preview-title {
        font-weight: 600;
        color: #007bff;
    }

    #task-preview .preview-content {
        font-size: 1.1rem;
    }

    .ck-editor__editable_inline {
        min-height: 250px;
    }
</style>
@endpush

@extends('layouts.app')
@section('title', 'Buat Surat Tugas Baru')

@section('content_header')
<div class="custom-header-box mb-4">
    <div class="d-flex align-items-center">
        <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
            <i class="fas fa-plus-circle fa-lg"></i>
        </div>
        <div>
            <div class="header-title">
                Buat Surat Tugas Baru
            </div>
            <div class="header-desc mt-2">
                Isi formulir di bawah untuk membuat surat tugas baru dan kelola daftar penerima internal maupun
                eksternal.
            </div>
        </div>
    </div>
</div>
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(44, 62, 80, 0.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #3498db;
        margin-top: 0.5rem;
    }

    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(52, 152, 219, 0.13);
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
        margin-left: 0.1rem;
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
            font-size: 0.98rem;
        }
    }
</style>
@endsection

@section('content')
{{-- Error validasi --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-ban"></i> Gagal Menyimpan!</h5>
    Mohon periksa kembali isian Anda. Ada beberapa data yang belum sesuai:
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="tugasForm" action="{{ route('surat_tugas.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="row">
        {{-- Kiri: Form Utama --}}
        <div class="col-lg-8">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="main-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-dasar-tab" data-toggle="pill" href="#tab-dasar"
                                role="tab"><i class="fas fa-file-alt mr-2"></i>Informasi Dasar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-isi-tab" data-toggle="pill" href="#tab-isi" role="tab"><i
                                    class="fas fa-tasks mr-2"></i>Detail Tugas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-pelaksanaan-tab" data-toggle="pill" href="#tab-pelaksanaan"
                                role="tab"><i class="fas fa-calendar-alt mr-2"></i>Pelaksanaan</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="main-tabs-content">
                        {{-- TAB 1: INFORMASI DASAR --}}
                        <div class="tab-pane fade show active" id="tab-dasar" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="nama_pembuat">Nama Pembuat</label>
                                    <select id="nama_pembuat" name="nama_pembuat" class="form-control select2bs4">
                                        @foreach ($admins as $id => $nama)
                                        <option value="{{ $id }}" @selected(old('nama_pembuat', Auth::id())==$id)>
                                            {{ $nama }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="asal_surat">Asal Surat (Pejabat)</label>
                                    <select id="asal_surat" name="asal_surat" class="form-control select2bs4">
                                        @foreach ($pejabat as $p)
                                        <option value="{{ $p->id }}" @selected(old('asal_surat')==$p->id)>
                                            {{ $p->nama_lengkap }} ({{ $p->peran->nama }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group"> <label for="nama_umum">Judul Umum Surat</label> <input type="text" id="nama_umum" name="nama_umum" class="form-control" placeholder="Contoh: Penugasan Panitia Seminar AI" value="{{ old('nama_umum') }}" required> </div>
                            <div class="form-group">
                                <label>Nomor Surat</label>
                                <div class="row align-items-center">
                                    {{-- Input-input terpisah dengan label --}}
                                    <div class="col-md-5">
                                        <label for="klasifikasi_surat" class="small text-muted">Kode</label>
                                        <select id="klasifikasi_surat" name="klasifikasi_surat_id"
                                            class="form-control select2bs4" required>
                                            <option value="" disabled selected>-- Pilih Kode --</option>
                                            @foreach ($klasifikasi as $k)
                                            <option value="{{ $k->id }}" data-kode="{{ $k->kode }}"
                                                @selected(old('klasifikasi_surat_id')==$k->id)>
                                                {{ $k->kode }} - {{ $k->deskripsi }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="bulan" class="small text-muted">Bln</label>
                                        <input type="text" id="bulan" name="bulan"
                                            class="form-control text-center" value="{{ old('bulan', $bulanRomawi) }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="tahun" class="small text-muted">Thn</label>
                                        <input type="number" id="tahun" name="tahun"
                                            class="form-control text-center" value="{{ old('tahun', date('Y')) }}">
                                    </div>

                                    {{-- Nomor Urut dipisah agar lebih jelas --}}
                                    <div class="col-md-2">
                                        <label for="nomor_urut" class="small text-muted">No. Urut</label>
                                        <input type="text" id="nomor_urut" class="form-control text-center"
                                            value="{{ old('nomor_urut', '001') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Input readonly untuk menampilkan hasil gabungan --}}
                            <div class="form-group mt-2">
                                <label>Hasil Nomor Surat (Otomatis)</label>
                                <input type="text" id="nomor_surat_lengkap_display" class="form-control"
                                    style="background-color: #e9ecef; cursor: not-allowed; font-weight: bold; letter-spacing: 1px;"
                                    value="..." readonly>
                                <input type="hidden" name="nomor" id="nomor_surat_lengkap_hidden">
                            </div>

                            {{-- Nomor Surat Manual (tidak berubah) --}}
                            <div class="form-group">
                                <label for="no_surat_manual">Nomor Surat Manual (Opsional)</label>
                                <input type="text" name="no_surat_manual" id="no_surat_manual"
                                    class="form-control" placeholder="Isi jika nomor surat sudah dibuat secara manual"
                                    value="{{ old('no_surat_manual') }}">
                                <small class="form-text text-muted">Jika diisi, nomor surat otomatis di atas akan
                                    diabaikan.</small>
                            </div>
                            {{-- Tahun & Semester --}}
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="tahun">Tahun Periode</label>
                                    <input type="number" id="tahun" name="tahun" class="form-control"
                                        value="{{ old('tahun', date('Y')) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="semester">Semester Periode</label>
                                    <select name="semester" id="semester" class="form-control">
                                        <option value="Ganjil" @selected(old('semester', $semester)=='Ganjil' )>Ganjil</option>
                                        <option value="Genap" @selected(old('semester', $semester)=='Genap' )>Genap</option>
                                    </select>
                                </div>
                            </div>
                            {{-- STATUS PENERIMA (OTOMATIS) --}}
                            <div class="form-group">
                                <label for="status_penerima_display">Status Penerima (Otomatis)</label>
                                <input type="text" id="status_penerima_display" class="form-control"
                                    style="background-color: #e9ecef; cursor: not-allowed;" value="Belum ada penerima"
                                    readonly>
                                <input type="hidden" name="status_penerima" id="status_penerima_hidden">
                            </div>
                        </div>

                        {{-- TAB 2: DETAIL TUGAS --}}
                        <div class="tab-pane fade" id="tab-isi" role="tabpanel">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="jenis_tugas">Jenis Tugas</label>
                                        <select name="jenis_tugas" id="jenis_tugas" class="form-control select2bs4">
                                            <option value="" disabled
                                                {{ old('jenis_tugas') ? '' : 'selected' }}>Pilih Jenis...</option>
                                            @foreach ($taskMaster as $jt)
                                            <option value="{{ $jt->nama }}" @selected(old('jenis_tugas')==$jt->nama)>
                                                {{ $jt->nama }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tugas">Tugas</label>
                                        <select name="tugas" id="tugas" class="form-control select2bs4"
                                            {{ old('tugas') ? '' : 'disabled' }}>
                                            <option value="">{{ old('tugas') ? '' : 'Pilih Tugas...' }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label>Pratinjau Pilihan Tugas</label>
                                    <div id="task-preview"><span class="placeholder-text text-center">Pilih jenis &
                                            tugas untuk melihat pratinjau.</span></div>
                                </div>
                            </div>
                            {{-- HAPUS Perihal, GANTI DENGAN REDAKSI --}}
                            <hr class="my-4">
                            <div class="form-group">
                                <label for="redaksi_pembuka">Redaksi Pembuka</label>
                                <textarea name="redaksi_pembuka" id="redaksi_pembuka" class="form-control" rows="3"
                                    placeholder="Contoh: Sehubungan dengan akan diselenggarakannya acara ...">{{ old('redaksi_pembuka') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="penutup">Redaksi Penutup</label>
                                <textarea name="penutup" id="penutup" class="form-control" rows="3"
                                    placeholder="Contoh: Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.">{{ old('penutup') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="detail_tugas_editor">Isi / Detail Rincian Tugas (Opsional)</label>
                                <textarea name="detail_tugas" id="detail_tugas_editor">{{ old('detail_tugas', '') }}</textarea>
                            </div>
                        </div>

                        {{-- TAB 3: PELAKSANAAN --}}
                        <div class="tab-pane fade" id="tab-pelaksanaan" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="waktu_mulai">Waktu Mulai</label>
                                    <input type="datetime-local" id="waktu_mulai" name="waktu_mulai"
                                        class="form-control"
                                        value="{{ old('waktu_mulai', now()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="waktu_selesai">Waktu Selesai</label>
                                    <input type="datetime-local" id="waktu_selesai" name="waktu_selesai"
                                        class="form-control"
                                        value="{{ old('waktu_selesai', now()->addHours(2)->format('Y-m-d\TH:i')) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tempat">Tempat Pelaksanaan</label>
                                <input type="text" id="tempat" name="tempat" class="form-control"
                                    placeholder="Cth: Ruang Teater, Gedung Thomas Aquinas Lantai 3"
                                    value="{{ old('tempat') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan: Penerima & Aksi --}}
        <div class="col-lg-4">
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Penerima Tugas</h3>
                </div>
                <div class="card-body">
                    <div id="penerima-list-container">
                        <p class="text-muted text-center py-3" id="penerima-placeholder">Belum ada penerima dipilih.
                        </p>
                        <ul id="penerima-list" class="list-group list-group-flush"></ul>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-sm btn-info btn-block mb-2" data-toggle="modal"
                        data-target="#penerimaModal">
                        <i class="fas fa-user-check mr-2"></i> Pilih dari Pengguna Sistem
                    </button>
                    <button type="button" class="btn btn-sm btn-success btn-block" data-toggle="modal"
                        data-target="#penerimaEksternalModal">
                        <i class="fas fa-user-plus mr-2"></i> Tambah Penerima Luar (Manual)
                    </button>
                </div>
            </div>
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-paper-plane mr-2"></i>Aksi & Persetujuan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="penandatangan">Pilih Penandatangan</label>
                        <select name="penandatangan" id="penandatangan" class="form-control select2bs4" required>
                            @foreach ($pejabat as $p)
                            <option value="{{ $p->id }}" @selected(old('penandatangan')==$p->id)>
                                {{ $p->nama_lengkap }} ({{ $p->peran->nama }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <hr>
                    <button type="button" name="action" value="draft" class="btn btn-block btn-secondary mb-2">
                        <i class="fas fa-save mr-2"></i>Simpan Draft
                    </button>
                    <button type="button" name="action" value="submit" class="btn btn-block btn-primary">
                        <i class="fas fa-check-circle mr-2"></i>Ajukan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- MODAL TAMBAH PENERIMA EKSTERNAL --}}
<div class="modal fade" id="penerimaEksternalModal" tabindex="-1" role="dialog"
    aria-labelledby="penerimaEksternalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penerimaEksternalModalLabel"><i class="fas fa-user-plus mr-2"></i>Tambah
                    Penerima Manual</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="form-penerima-eksternal" onsubmit="return false;">
                    <div class="form-group">
                        <label for="nama_eksternal">Nama Lengkap</label>
                        <input type="text" id="nama_eksternal" class="form-control"
                            placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="jabatan_eksternal">Jabatan / Posisi</label>
                        <select id="jabatan_eksternal" class="form-control" required>
                            <option value="" disabled selected>-- Pilih Posisi --</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Dosen Luar">Dosen Luar</option>
                            <option value="Umum">Umum</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="simpanPenerimaEksternal">Tambah ke Daftar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PILIH PENERIMA INTERNAL --}}
<div class="modal fade" id="penerimaModal" tabindex="-1" role="dialog" aria-labelledby="penerimaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penerimaModalLabel"><i class="fas fa-user-check mr-2"></i>Pilih Penerima
                    Tugas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table id="penerima-table" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:5%;"><input type="checkbox" id="select-all-penerima"></th>
                            <th style="width:30%;">Nama Lengkap</th>
                            <th style="width:30%;">Email</th>
                            <th style="width:20%;">Jabatan</th>
                            <th style="width:15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" class="penerima-checkbox" value="{{ $user->id }}"
                                    data-nama="{{ $user->nama_lengkap }}" data-peran-id="{{ $user->peran_id }}"
                                    data-jabatan="{{ $user->jabatan ?: $user->peran->deskripsi }}"
                                    data-status-deskripsi="{{ $user->peran->deskripsi }}">
                            </td>
                            <td>{{ $user->nama_lengkap }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->jabatan ?: '-' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $user->peran->deskripsi }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i
                        class="fas fa-times mr-2"></i>Batal</button>
                <button type="button" class="btn btn-primary" id="simpanPenerima"><i
                        class="fas fa-check mr-2"></i>Simpan Pilihan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    $(function() {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        const table = $('#penerima-table').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_-_END_ dari _TOTAL_",
                zeroRecords: "Tidak ditemukan",
                paginate: {
                    next: ">>",
                    previous: "<<"
                }
            },
            columnDefs: [{
                orderable: false,
                targets: 0
            }]
        });
        ClassicEditor.create(document.querySelector('#detail_tugas_editor'), {
            toolbar: {
                items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList',
                    '|', 'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            }
        }).catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

        const baseNomor = @json($autoNomor);
        const $nomorUrutInput = $('#nomor_urut');
        const $klasifikasiSelect = $('#klasifikasi_surat');
        const $nomorSuratInput = $('#nomor_surat');

        // GANTI FUNGSI updateNomorSurat() LAMA DENGAN INI
        function updateNomorSurat() {
            const nomorUrut = $('#nomor_urut').val().padStart(3, '0');
            const kodeKlasifikasi = $('#klasifikasi_surat').find(':selected').data('kode') || '...';
            const bulan = $('#bulan').val() || '...';
            const tahun = $('#tahun').val() || '....';

            // Format yang akan disimpan ke database
            // Contoh: 001/B.1.1/TG/UNIKA/VIII/2025
            const nomorLengkap = `${nomorUrut}/${kodeKlasifikasi}/TG/UNIKA/${bulan}/${tahun}`;

            // Update input readonly untuk ditampilkan ke user
            $('#nomor_surat_lengkap_display').val(nomorLengkap);

            // Update hidden input yang akan dikirim ke server
            $('#nomor_surat_lengkap_hidden').val(nomorLengkap);
        }

        // Tambahkan event listener untuk semua input yang relevan
        $('#nomor_urut, #klasifikasi_surat, #bulan, #tahun').on('change keyup input', updateNomorSurat);

        // Panggil sekali saat halaman dimuat
        updateNomorSurat();

        // -- Dropdown tugas & preview
        const taskData = @json($taskMaster);
        const $tugasPreview = $('#task-preview');
        const placeholderText =
            `<span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat pratinjau.</span>`;

        function updateTaskPreview() {
            const kategori = $('#jenis_tugas').val();
            const tugas = $('#tugas').val();
            if (kategori && tugas) {
                const previewHtml =
                    `<div><p class="mb-1 text-muted">Jenis Tugas:</p><h5 class="preview-title mb-3"><i class="fas fa-layer-group mr-2"></i>${kategori}</h5><p class="mb-1 text-muted">Tugas:</p><p class="preview-content font-weight-bold">${tugas}</p></div>`;
                $tugasPreview.html(previewHtml).addClass('has-content');
            } else {
                $tugasPreview.html(placeholderText).removeClass('has-content');
            }
        }

        function populateSpecificTask(selectedKategori, preselectedTugas) {
            const $tugasSelect = $('#tugas');
            $tugasSelect.empty().append(new Option('Pilih Tugas...', ''));
            const found = taskData.find(jt => jt.nama === selectedKategori);
            if (found && Array.isArray(found.subtugas) && found.subtugas.length) {
                found.subtugas.forEach(st => {
                    const isSelected = preselectedTugas === st.nama;
                    $tugasSelect.append(new Option(st.nama, st.nama, isSelected, isSelected));
                });
                $tugasSelect.prop('disabled', false);
            } else {
                $tugasSelect.prop('disabled', true);
            }
            $tugasSelect.trigger('change.select2');
            updateTaskPreview();
        }
        $('#jenis_tugas').on('change', function() {
            populateSpecificTask($(this).val(), null);
        });
        $('#tugas').on('change', updateTaskPreview);
        const oldJenis = "{{ old('jenis_tugas', '') }}";
        if (oldJenis) {
            populateSpecificTask(oldJenis, "{{ old('tugas', '') }}");
        }

        // --------- INISIALISASI PENERIMA (internal & eksternal) ---------
        const allUsersData = @json($users - > keyBy('id'));
        const oldInternalIds = @json(old('penerima_internal', []));
        const oldEksternal = @json(old('penerima_eksternal', []));
        let penerimaState = {
            internal: {},
            eksternal: []
        };
        if (oldInternalIds.length > 0) {
            oldInternalIds.forEach(id => {
                if (allUsersData[id]) {
                    penerimaState.internal[id] = {
                        nama: allUsersData[id].nama_lengkap,
                        peran_id: allUsersData[id].peran_id
                    };
                }
            });
        }
        if (oldEksternal.length > 0) {
            penerimaState.eksternal = oldEksternal;
        }

        function updateStatusPenerima() {
            const $displayInput = $('#status_penerima_display');
            const $hiddenInput = $('#status_penerima_hidden');
            const statusSet = new Set();
            for (const id in penerimaState.internal) {
                const peranId = penerimaState.internal[id].peran_id;
                if (peranId == 1 || peranId == 6) {
                    statusSet.add('Tendik');
                } else {
                    statusSet.add('Dosen');
                }
            }
            penerimaState.eksternal.forEach(penerima => {
                const jabatan = (penerima.jabatan || '').toLowerCase();
                if (jabatan === 'mahasiswa') statusSet.add('Mahasiswa');
                else if (jabatan === 'dosen luar') statusSet.add('Dosen');
                else if (jabatan === 'umum') statusSet.add('Umum');
            });
            if (statusSet.size === 0) {
                $displayInput.val('Belum ada penerima');
                $hiddenInput.val('');
            } else {
                const statusArray = Array.from(statusSet).sort();
                let displayText = '';
                if (statusArray.length === 1) displayText = statusArray[0];
                else if (statusArray.length === 2) displayText = statusArray.join(' dan ');
                else {
                    const allButLast = statusArray.slice(0, -1).join(', ');
                    const lastItem = statusArray[statusArray.length - 1];
                    displayText = `${allButLast}, dan ${lastItem}`;
                }
                // ... (logika membentuk displayText tetap seperti semula)

                $displayInput.val(displayText);

                // Normalisasi untuk hidden agar cocok ENUM DB
                // Prioritas sederhana: jika hanya satu status valid → kirim itu.
                // Jika lebih dari satu / tak dikenal → kosongkan (biar backend yang putuskan).
                const allowed = ['dosen', 'tendik', 'mahasiswa'];
                let normalized = '';

                const lowerTokens = displayText
                    .toLowerCase()
                    .split(/[,\s]+dan\s+|,\s*|\s+dan\s+/) // pecah "a, b, dan c" atau "a dan b"
                    .map(s => s.trim())
                    .filter(Boolean);

                if (lowerTokens.length === 1 && allowed.includes(lowerTokens[0])) {
                    normalized = lowerTokens[0];
                } else if (lowerTokens.length >= 1) {
                    // jika ada salah satu yang valid dan kamu ingin pilih prioritas, bisa atur urutan berikut:
                    const priority = ['dosen', 'tendik', 'mahasiswa'];
                    for (const p of priority) {
                        if (lowerTokens.includes(p)) {
                            normalized = p;
                            break;
                        }
                    }
                    // jika tidak ada yang valid → biarkan normalized = ''
                }

                $hiddenInput.val(normalized);

                $hiddenInput.val(displayText.toLowerCase());
            }
        }

        function renderPenerimaList() {
            const listContainer = $('#penerima-list');
            const placeholder = $('#penerima-placeholder');
            listContainer.empty();
            $('input[name^="penerima_internal"],input[name^="penerima_eksternal"]').remove();
            const internalCount = Object.keys(penerimaState.internal).length;
            const eksternalCount = penerimaState.eksternal.length;
            if (internalCount === 0 && eksternalCount === 0) {
                placeholder.show();
            } else {
                placeholder.hide();
                for (const id in penerimaState.internal) {
                    const data = penerimaState.internal[id];
                    const itemHtml = `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-user-tie mr-2 text-info"></i>${data.nama}</div>
                            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="internal" data-id="${id}"><i class="fas fa-times"></i></button>
                        </li>`;
                    listContainer.append(itemHtml);
                    $('#tugasForm').append(`<input type="hidden" name="penerima_internal[]" value="${id}">`);
                }
                penerimaState.eksternal.forEach((p, index) => {
                    const itemHtml = `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-user mr-2 text-success"></i>${p.nama} <span class="eksternal-label">(${p.jabatan})</span></div>
                            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="eksternal" data-id="${index}"><i class="fas fa-times"></i></button>
                        </li>`;
                    listContainer.append(itemHtml);
                    $('#tugasForm').append(
                        `<input type="hidden" name="penerima_eksternal[${index}][nama]" value="${p.nama}">`
                    );
                    $('#tugasForm').append(
                        `<input type="hidden" name="penerima_eksternal[${index}][jabatan]" value="${p.jabatan}">`
                    );
                });
            }
            updateStatusPenerima();
        }
        $('#simpanPenerima').on('click', function() {
            penerimaState.internal = {};
            table.$('.penerima-checkbox:checked').each(function() {
                const id = $(this).val();
                const nama = $(this).data('nama');
                const peranId = $(this).data('peran-id');
                penerimaState.internal[id] = {
                    nama: nama,
                    peran_id: peranId
                };
            });
            renderPenerimaList();
            $('#penerimaModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Penerima Disimpan!',
                text: 'Daftar penerima berhasil diperbarui.',
                showConfirmButton: false,
                timer: 1500
            });
        });
        $('#simpanPenerimaEksternal').on('click', function() {
            const nama = $('#nama_eksternal').val().trim();
            const jabatan = $('#jabatan_eksternal').val().trim();
            if (nama && jabatan) {
                penerimaState.eksternal.push({
                    nama,
                    jabatan
                });
                renderPenerimaList();
                $('#form-penerima-eksternal')[0].reset();
                $('#penerimaEksternalModal').modal('hide');
            } else {
                Swal.fire('Lengkapi Nama & Jabatan', '', 'warning');
            }
        });
        $('#penerima-list').on('click', '.remove-penerima', function() {
            const type = $(this).data('type'),
                id = $(this).data('id');

            if (type === 'internal') {
                delete penerimaState.internal[id];

                // --- AWAL KODE PERBAIKAN ---
                // Cari checkbox di tabel modal dan hilangkan centangnya
                $('#penerima-table .penerima-checkbox[value="' + id + '"]').prop('checked', false);
                // --- AKHIR KODE PERBAIKAN ---

            } else {
                penerimaState.eksternal.splice(id, 1);
            }
            renderPenerimaList();
        });

        // -- Tombol simpan draft/submit + konfirmasi
        let clickedAction = null;
        $('button[name="action"]').on('click', function(e) {
            e.preventDefault();
            clickedAction = $(this).val();
            const internalCount = Object.keys(penerimaState.internal).length;
            const eksternalCount = penerimaState.eksternal.length;
            if (internalCount === 0 && eksternalCount === 0) {
                Swal.fire('Peringatan', 'Anda harus memilih setidaknya satu penerima tugas.',
                    'warning');
                return;
            }
            const isSubmit = clickedAction === 'submit';
            Swal.fire({
                title: isSubmit ? 'Ajukan Surat Tugas?' : 'Simpan sebagai Draft?',
                text: isSubmit ?
                    'Setelah diajukan, surat akan masuk alur persetujuan. Lanjutkan?' : 'Draft bisa diubah nanti. Simpan sekarang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: isSubmit ? 'Ya, ajukan' : 'Ya, simpan draft',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $('input[name="action"]', '#tugasForm').remove();
                    $('<input>')
                        .attr({
                            type: 'hidden',
                            name: 'action',
                            value: clickedAction
                        })
                        .appendTo('#tugasForm');
                    Swal.fire({
                        title: 'Sedang diproses…',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $('#tugasForm').find('button[type="button"]').prop(
                                'disabled', true).addClass('disabled');
                            $('#tugasForm')[0].submit();
                        },
                        showConfirmButton: false,
                    });
                }
            });
        });

        // Fallback submit
        $('#tugasForm').on('submit', function(e) {
            if (!clickedAction) {
                e.preventDefault();
                Swal.fire({
                    title: 'Pilih Aksi',
                    text: 'Anda belum memilih apakah ingin menyimpan draft atau mengajukan surat tugas.',
                    icon: 'warning',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Draft',
                    denyButtonText: 'Ajukan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) clickedAction = 'draft';
                    else if (result.isDenied) clickedAction = 'submit';
                    else return;
                    $('input[name="action"]', '#tugasForm').remove();
                    $('<input>')
                        .attr({
                            type: 'hidden',
                            name: 'action',
                            value: clickedAction
                        })
                        .appendTo('#tugasForm');
                    Swal.fire({
                        title: 'Sedang diproses…',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $('#tugasForm').find('button[type="button"]').prop(
                                'disabled', true).addClass('disabled');
                            $('#tugasForm')[0].submit();
                        },
                        showConfirmButton: false,
                    });
                });
            }
        });

        // Render awal
        renderPenerimaList();
    });
</script>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@endpush