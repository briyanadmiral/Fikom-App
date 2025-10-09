@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

{{-- BARU: CSS Tagify --}}
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

<style>
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }

    .list-group-item-action {
        transition: background-color 0.15s ease-in-out;
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

    /* --- TEMBUSAN KEREN --- */
    .tembusan-wrap {
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        overflow: hidden;
    }

    .tembusan-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .75rem 1rem;
        background: linear-gradient(90deg, #5c7cfa 0%, #845ef7 100%);
        color: #fff;
    }

    .tembusan-body {
        padding: 1rem;
        background: #fff;
    }

    .tembusan-preview {
        background: #f8f9ff;
        border: 1px dashed #cdd5ff;
        border-radius: .5rem;
        padding: 1rem;
    }

    .tembusan-preview h6 {
        font-weight: 700;
        color: #3b5bdb;
        margin-bottom: .5rem;
    }

    .tembusan-preview ol {
        margin: 0;
        padding-left: 1.25rem;
    }

    .tembusan-tools .btn {
        margin-left: .5rem;
    }

    .tagify__tag {
        font-weight: 600;
    }

    .tagify__input {
        min-width: 140px;
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

@php
// Nilai default untuk field "Tembusan" (Tagify).
// Pakai old('tembusan') jika kembali dari validasi, jika tidak ada biarkan kosong.
$prefTembusanCsv = old('tembusan', '');
@endphp

<form id="tugasForm" action="{{ route('surat_tugas.store') }}" method="POST">
    @csrf
    {{-- HAPUS: hidden tahun & semester yang duplikat --}}
    {{-- <input type="hidden" name="tahun" value="{{ $tahun }}">
    <input type="hidden" name="semester" value="{{ $semester }}"> --}}

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
                                    <input type="text" id="nama_pembuat" name="nama_pembuat" class="form-control" value="{{ old('nama_pembuat', Auth::user()->nama_lengkap) }}" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="asal_surat">Asal Surat (Unit Kerja)</label>
                                    <input type="text" id="asal_surat" name="asal_surat" class="form-control" value="{{ old('asal_surat', Auth::user()->unit_kerja ?: 'Unit Tidak Diketahui') }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nama_umum">Judul Umum Surat</label>
                                <input type="text" id="nama_umum" name="nama_umum" class="form-control" placeholder="Contoh: Penugasan Panitia Seminar AI" value="{{ old('nama_umum') }}" required>
                            </div>

                            <div class="form-group">
                                <label>Nomor Surat</label>
                                <div class="row align-items-center">
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
                                        <label for="tahun-nomor" class="small text-muted">Thn</label>
                                        <input type="number" id="tahun-nomor"
                                            class="form-control text-center" value="{{ old('tahun', date('Y')) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="nomor_urut" class="small text-muted">No. Urut</label>
                                        <input type="text" id="nomor_urut" class="form-control text-center"
                                            value="{{ old('nomor_urut', '001') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-2">
                                <label>Hasil Nomor Surat (Otomatis)</label>
                                <input type="text" id="nomor_surat_lengkap_display" class="form-control"
                                    style="background-color: #e9ecef; cursor: not-allowed; font-weight: bold; letter-spacing: 1px;"
                                    value="..." readonly>
                                <input type="hidden" name="nomor" id="nomor_surat_lengkap_hidden">
                            </div>

                            <div class="form-group">
                                <label for="no_surat_manual">Nomor Surat Manual (Opsional)</label>
                                <input type="text" name="no_surat_manual" id="no_surat_manual"
                                    class="form-control" placeholder="Isi jika nomor surat sudah dibuat secara manual"
                                    value="{{ old('no_surat_manual') }}">
                                <small class="form-text text-muted">Jika diisi, nomor surat otomatis di atas akan
                                    diabaikan.</small>
                            </div>

                            {{-- === TEMBUSAN KEREN === --}}
                            @php
                            // (Opsional) preset whitelist agar ada saran cepat. Bisa ambil dari DB juga.
                            $tembusanPresets = [
                            'Yth. Rektor', 'Yth. Wakil Rektor I', 'Yth. Wakil Rektor II',
                            'Dekan Fakultas Ilmu Komputer', 'BAAK', 'BAUK', 'BAK',
                            'Kepala Program Studi Sistem Informasi', 'Unit Kepegawaian', 'Arsip'
                            ];
                            $prefTembusanCsv = old('tembusan', $prefTembusanCsv ?? '');
                            @endphp

                            <div class="form-group col-12 p-0">
                                <div class="tembusan-wrap">
                                    <div class="tembusan-head">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-copy mr-2"></i>
                                            <strong>Tembusan</strong>
                                            <span class="ml-2 small" style="opacity:.9">(opsional)</span>
                                        </div>
                                        <div class="tembusan-tools">
                                            <button type="button" class="btn btn-sm btn-light" id="btnPasteTembusan" title="Tempel daftar dari clipboard">
                                                <i class="fas fa-clipboard-list mr-1"></i>Tempel Daftar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light" id="btnClearTembusan" title="Kosongkan">
                                                <i class="fas fa-eraser mr-1"></i>Reset
                                            </button>
                                        </div>
                                    </div>

                                    <div class="tembusan-body">
                                        <label for="tembusan-input" class="mb-1">Ketik & tekan <kbd>Enter</kbd> atau <kbd>,</kbd> untuk membuat tag</label>
                                        <input id="tembusan-input"
                                            name="tembusan"
                                            value="{{ $prefTembusanCsv }}"
                                            class="form-control"
                                            placeholder="Misal: Yth. Rektor, BAAK, Arsip">

                                        @error('tembusan')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror

                                        <div class="custom-control custom-switch mt-3">
                                            <input type="checkbox" class="custom-control-input" id="tembusanShowTitle" checked>
                                            <label class="custom-control-label" for="tembusanShowTitle">Cetak judul <em>“Tembusan Yth:”</em></label>
                                        </div>

                                        <div class="tembusan-preview mt-3" id="tembusanPreview">
                                            <h6 class="mb-2"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>
                                            <div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- (Opsional) simpan versi terformat jika ingin dipakai saat cetak --}}
                                <input type="hidden" name="tembusan_formatted" id="tembusan_formatted">
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="tahun-periode">Tahun Periode</label>
                                    <input type="number" id="tahun-periode" name="tahun" class="form-control"
                                        value="{{ old('tahun', date('Y')) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="semester-periode">Semester Periode</label>
                                    <select name="semester" id="semester-periode" class="form-control">
                                        <option value="Ganjil" @selected(old('semester', $semester)=='Ganjil' )>Ganjil</option>
                                        <option value="Genap" @selected(old('semester', $semester)=='Genap' )>Genap</option>
                                    </select>
                                </div>
                            </div>

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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

{{-- BARU: JS Tagify --}}
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

<script>
    $(function() {
        // ===== Select2 =====
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // ===== CKEditor =====
        ClassicEditor.create(document.querySelector('#detail_tugas_editor'), {
            toolbar: {
                items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                shouldNotGroupWhenFull: true
            }
        }).catch(err => console.error('Error initializing CKEditor:', err));

        // ===== Nomor Surat Otomatis =====
        function updateNomorSurat() {
            const nomorUrut = ($('#nomor_urut').val() || '').toString().padStart(3, '0');
            const kodeKlasifikasi = $('#klasifikasi_surat').find(':selected').data('kode') || '...';
            const bulan = $('#bulan').val() || '...';
            const tahun = $('#tahun-nomor').val() || '....';
            const nomorLengkap = `${nomorUrut}/${kodeKlasifikasi}/TG/UNIKA/${bulan}/${tahun}`;
            $('#nomor_surat_lengkap_display').val(nomorLengkap);
            $('#nomor_surat_lengkap_hidden').val(nomorLengkap);
        }
        $('#nomor_urut, #klasifikasi_surat, #bulan, #tahun-nomor').on('change keyup input', updateNomorSurat);
        updateNomorSurat();

        // ===== Dropdown tugas & preview =====
        const taskData = @json($taskMaster);
        const $tugasPreview = $('#task-preview');
        const placeholderText = `<span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat pratinjau.</span>`;

        function updateTaskPreview() {
            const kategori = $('#jenis_tugas').val();
            const tugas = $('#tugas').val();
            if (kategori && tugas) {
                const previewHtml = `<div>
                    <p class="mb-1 text-muted">Jenis Tugas:</p>
                    <h5 class="preview-title mb-3"><i class="fas fa-layer-group mr-2"></i>${kategori}</h5>
                    <p class="mb-1 text-muted">Tugas:</p>
                    <p class="preview-content font-weight-bold">${tugas}</p>
                </div>`;
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
        if (oldJenis) populateSpecificTask(oldJenis, "{{ old('tugas', '') }}");

        // ============== TEMBUSAN: Tagify Keren + Live Preview ==============
        // Polyfill underscore escape jika belum ada
        window._ = window._ || {};
        if (!_.escape) {
            _.escape = function(s) {
                return String(s).replace(/[&<>"'`=\/]/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '=': '&#x3D;',
                    '`': '&#x60;'
                } [c]));
            };
        }

        // (Opsional) preset untuk dropdown saran cepat
        const tembusanPresets = [
            'Yth. Rektor', 'Yth. Wakil Rektor I', 'Yth. Wakil Rektor II',
            'Dekan Fakultas Ilmu Komputer', 'BAAK', 'BAUK', 'BAK',
            'Kepala Program Studi Sistem Informasi', 'Unit Kepegawaian', 'Arsip'
        ];

        const tembusanInput = document.querySelector('#tembusan-input');
        let tagify;
        if (tembusanInput) {
            // Buat elemen pendukung jika belum ada: toggle title, preview, hidden formatted
            const $formGroup = $(tembusanInput).closest('.form-group, .col-12, form, body').first();

            if (!document.getElementById('tembusanShowTitle')) {
                const toggleHtml = `
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="tembusanShowTitle" checked>
                        <label class="custom-control-label" for="tembusanShowTitle">Cetak judul <em>“Tembusan Yth:”</em></label>
                    </div>`;
                $(tembusanInput).after(toggleHtml);
            }
            if (!document.getElementById('tembusanPreview')) {
                const prevHtml = `
                    <div class="tembusan-preview mt-2" id="tembusanPreview" style="background:#f8f9ff;border:1px dashed #cdd5ff;border-radius:.5rem;padding:1rem;">
                        <h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>
                        <div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>
                    </div>`;
                $('#tembusanShowTitle').parent().after(prevHtml);
            }
            if (!document.getElementById('tembusan_formatted')) {
                const hiddenHtml = `<input type="hidden" name="tembusan_formatted" id="tembusan_formatted">`;
                // sisipkan ke form utama
                $('#tugasForm').append(hiddenHtml);
            }

            // Inisialisasi Tagify (upgrade dari versi basic)
            tagify = new Tagify(tembusanInput, {
                enforceWhitelist: false,
                whitelist: tembusanPresets,
                trim: true,
                duplicates: false,
                delimiters: ",|\n",
                editTags: 1,
                dropdown: {
                    enabled: 1,
                    maxItems: 20,
                    fuzzySearch: true,
                    highlightFirst: true,
                    placeAbove: false
                },
                placeholder: "Misal: Yth. Rektor, BAAK, Arsip",
                transformTag: (tagData) => {
                    let v = (tagData.value || '').trim();
                    if (!v) return;
                    // Title Case
                    v = v.toLowerCase().replace(/\b\w/g, m => m.toUpperCase());
                    // Tambah "Yth." untuk jabatan umum jika belum ada
                    const needsYth = /^(Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris)\b/i.test(v) && !/^Yth\.\s/i.test(v);
                    if (needsYth) v = 'Yth. ' + v;
                    tagData.value = v;
                }
            });

            const renderTembusanPreview = () => {
                const data = tagify.value.map(t => (t.value || '').trim()).filter(Boolean);
                const showTitle = $('#tembusanShowTitle').is(':checked');
                const $preview = $('#tembusanPreview');

                if (data.length === 0) {
                    $preview.html('<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>');
                    $('#tembusan_formatted').val('');
                    return;
                }

                const titleHtml = showTitle ? '<div class="mb-2 font-weight-bold">Tembusan Yth:</div>' : '';
                const listHtml = '<ol class="mb-0">' + data.map(item => `<li>${_.escape(item)}</li>`).join('') + '</ol>';
                $preview.html(`<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`);

                // simpan versi siap-cetak
                const plain = (showTitle ? 'Tembusan Yth:\n' : '') + data.map((v, i) => `${i+1}. ${v}`).join('\n');
                $('#tembusan_formatted').val(plain);
            };

            // Event Tagify
            tagify.on('add', renderTembusanPreview)
                .on('remove', renderTembusanPreview)
                .on('edit:updated', renderTembusanPreview);

            // Toggle judul
            $(document).on('change', '#tembusanShowTitle', renderTembusanPreview);

            // Tombol opsional (aktif bila kamu menyediakan elemen dengan ID di HTML)
            $(document).on('click', '#btnPasteTembusan', async function() {
                try {
                    const txt = await navigator.clipboard.readText();
                    if (!txt) return;
                    const items = txt.split(/[\n,]/).map(s => s.trim()).filter(Boolean);
                    const existing = new Set(tagify.value.map(t => (t.value || '').toLowerCase()));
                    const toAdd = items.filter(s => !existing.has(s.toLowerCase())).map(s => ({
                        value: s
                    }));
                    tagify.addTags(toAdd);
                } catch (e) {
                    Swal.fire('Tidak bisa mengakses clipboard', 'Izinkan akses atau tempel manual.', 'info');
                }
            });
            $(document).on('click', '#btnClearTembusan', function() {
                tagify.removeAllTags();
            });

            // Render awal sesuai old('tembusan')
            setTimeout(renderTembusanPreview, 0);
        }
        // ============ END TEMBUSAN ============

        // ===== INISIALISASI PENERIMA (internal & eksternal) =====
        const allUsersData = @json($users -> keyBy('id'));
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
        if (oldEksternal.length > 0) penerimaState.eksternal = oldEksternal;

        // ===== Aksi Simpan/Ajukan =====
        let clickedAction = null;
        $('button[name="action"]').on('click', function(e) {
            e.preventDefault();
            clickedAction = $(this).val();
            const internalCount = Object.keys(penerimaState.internal).length;
            const eksternalCount = penerimaState.eksternal.length;
            if (internalCount === 0 && eksternalCount === 0) {
                Swal.fire('Peringatan', 'Anda harus memilih setidaknya satu penerima tugas.', 'warning');
                return;
            }
            const isSubmit = clickedAction === 'submit';
            Swal.fire({
                title: isSubmit ? 'Ajukan Surat Tugas?' : 'Simpan sebagai Draft?',
                text: isSubmit ? 'Setelah diajukan, surat akan masuk alur persetujuan. Lanjutkan?' : 'Draft bisa diubah nanti. Simpan sekarang?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: isSubmit ? 'Ya, ajukan' : 'Ya, simpan draft',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $('input[name="action"]', '#tugasForm').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'action',
                        value: clickedAction
                    }).appendTo('#tugasForm');
                    Swal.fire({
                        title: 'Sedang diproses…',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $('#tugasForm').find('button[type="button"]').prop('disabled', true).addClass('disabled');
                            $('#tugasForm')[0].submit();
                        },
                        showConfirmButton: false,
                    });
                }
            });
        });

        // Fallback submit (Enter)
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
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'action',
                        value: clickedAction
                    }).appendTo('#tugasForm');
                    Swal.fire({
                        title: 'Sedang diproses…',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            $('#tugasForm').find('button[type="button"]').prop('disabled', true).addClass('disabled');
                            $('#tugasForm')[0].submit();
                        },
                        showConfirmButton: false,
                    });
                });
            }
        });

        // Render awal daftar penerima
        renderPenerimaList();
    });
</script>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@endpush