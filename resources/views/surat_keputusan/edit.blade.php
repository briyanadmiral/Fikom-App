{{-- resources/views/surat_keputusan/edit.blade.php --}}
@extends('layouts.app')
{{-- PERUBAHAN: Judul halaman --}}
@section('title', 'Edit Surat Keputusan')

@php
// Alias agar kompatibel baik $sk maupun $keputusan
$keputusan = $keputusan ?? $sk ?? null;
@endphp

@push('styles')
{{-- Library Styles --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

<style>
    /* Page & Card Styles (Sama seperti create.blade.php) */
    .page-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem;
        border-radius: 1.1rem;
        margin-bottom: 1.6rem;
        border: 1px solid #e0e6ed;
        display: flex;
        align-items: center;
        gap: 1.3rem
    }

    .page-header .icon {
        background: linear-gradient(135deg, #6f42c1 0, #9a6ee5 100%);
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px #6f42c14d;
        font-size: 1.6rem;
        color: #fff
    }

    .page-header-title {
        font-weight: 700;
        color: #412674;
        font-size: 1.7rem;
        letter-spacing: -.2px;
        margin: 0
    }

    .page-header-desc {
        color: #636e7b;
        font-size: .98rem;
        margin: .1rem 0 0
    }

    .card-settings,
    .card {
        border: none;
        border-radius: .9rem;
        box-shadow: 0 10px 28px rgba(28, 28, 28, .06)
    }

    .card-settings .card-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f0
    }

    .card .card-body {
        padding: 1rem 1.1rem;
    }

    .form-control,
    .custom-select,
    .form-select {
        border-radius: .55rem
    }

    .input-group-text {
        background: #eef1f6;
        border-color: #dfe5ec
    }

    .card-h {
        border-bottom: 0;
        color: #fff;
        padding: .85rem 1.1rem;
        border-top-left-radius: .9rem;
        border-top-right-radius: .9rem
    }

    .card-h--purple {
        background: linear-gradient(135deg, #6f42c1 0%, #9a6ee5 100%)
    }

    .card-h--teal {
        background: linear-gradient(135deg, #0ab39c 0%, #41d6c3 100%)
    }

    .card-h--blue {
        background: linear-gradient(135deg, #3f8cff 0%, #6aa6ff 100%)
    }

    .card-h--amber {
        background: linear-gradient(135deg, #f59f00 0%, #f7b733 100%)
    }

    .card-h--green {
        background: linear-gradient(135deg, #16a34a 0%, #34d399 100%)
    }

    .card-h--red {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%)
    }

    /* QuickNav & UI Feedback Styles */
    .list-quicknav .list-group-item {
        border: 0;
        padding: .55rem .75rem;
        border-radius: .6rem;
        display: flex;
        align-items: center;
        gap: .55rem
    }

    .list-quicknav .active {
        background: #f1eaff;
        color: #5a33b8;
        font-weight: 600
    }

    a.is-complete {
        background: #eaf9f0 !important;
        color: #146c2e !important;
        border-left: 4px solid #22c55e !important
    }

    a.has-error {
        background: #fdecec !important;
        color: #b42318 !important;
        border-left: 4px solid #ef4444 !important
    }

    .text-purple {
        color: #6f42c1 !important
    }

    /* Form Element Styles */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: .55rem
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background: #f1eaff;
        border: 1px solid #cbb5ff;
        color: #5a33b8;
        font-weight: 500;
        border-radius: .5rem
    }

    .nomor-builder {
        display: none;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: .5rem
    }

    .nomor-builder.show {
        display: block
    }

    .nomor-builder-preview {
        font-family: 'Courier New', monospace;
        background: #e9ecef;
        padding: .2rem .5rem;
        border-radius: 4px
    }

    .diktum-item {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: .6rem;
        transition: box-shadow .2s ease
    }

    .diktum-item:focus-within {
        box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25)
    }

    .menimbang-item .input-group-text,
    .mengingat-item .input-group-text {
        min-width: 34px;
        justify-content: center
    }

    /* Action Bar for Mobile */
    .btn-ghost {
        background: #f6f7fb;
        border: 1px solid #eef0f4
    }

    .action-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 998;
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(6px);
        border-top: 1px solid #eaeef4;
        padding: .75rem
    }

    @media(min-width:992px) {
        .action-bar {
            display: none
        }
    }
</style>
@endpush

@section('content_header')
{{-- PERUBAHAN: Header halaman --}}
<div class="page-header mt-2">
    <span class="icon"><i class="fas fa-pencil-alt"></i></span>
    <div>
        <h1 class="page-header-title">Edit Surat Keputusan</h1>
        <p class="page-header-desc mb-0">Perbarui detail surat keputusan dengan nomor <strong>{{ $keputusan->nomor }}</strong>.</p>
    </div>
</div>
@endsection

@section('content')
@php
// ==============================
// SETUP & PREFILL DATA
// ==============================
// PERUBAHAN: Mode diatur ke 'edit'
$mode = 'edit';
$isEdit = true;
// Variabel $keputusan diasumsikan sudah ada dari Controller

// Auto numbering setup
$bulanRomawi = ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
$currentRomawi = $bulanRomawi[date('n')] ?? 'IX';
$currentYear = date('Y');

// Helper function yang sama, otomatis mengambil data dari $keputusan
$getPref = fn($key, $default = null) => old($key, $keputusan->{$key} ?? $default);

// Prefill data otomatis menggunakan $getPref
$prefMenimbang = (array) $getPref('menimbang', ['']);
$prefMenimbang = empty($prefMenimbang) ? [''] : $prefMenimbang;

$prefMengingat = (array) $getPref('mengingat', ['']);
$prefMengingat = empty($prefMengingat) ? [''] : $prefMengingat;

$menetapkanData = $getPref('menetapkan', [['judul' => 'KESATU', 'isi' => '']]);
if (is_string($menetapkanData)) {
$menetapkanData = json_decode($menetapkanData, true) ?: [];
}
$prefMenetapkan = empty($menetapkanData) ? [['judul' => 'KESATU', 'isi' => '']] : $menetapkanData;

$tembusanVal = $getPref('tembusan', []);
$prefTembusanCsv = is_array($tembusanVal) ? implode(', ', $tembusanVal) : $tembusanVal;

$prefPenerimaIds = old('penerima_internal', ($keputusan->penerima ?? collect())->pluck('id')->all());

// ===== Variabel konteks tombol aksi (dibutuhkan Desktop & Mobile) =====
$isSignerCanApprove = auth()->user()->can('approve', $keputusan);
$isPending = $keputusan->status_surat === 'pending';
@endphp

<div class="container-fluid">
    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible shadow-sm">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5 class="mb-1"><i class="icon fas fa-ban"></i> Gagal Memperbarui!</h5>
        <small>Mohon periksa kembali isian Anda:</small>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- PERUBAHAN: Action form ke route 'update' dan method 'PUT' --}}
    <form id="skForm" action="{{ route('surat_keputusan.update', $keputusan->id) }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- KIRI: FORM CONTENT --}}
            <div class="col-lg-8 mb-3">

                {{-- ========================= 1) DATA UTAMA ========================= --}}
                <div id="section-utama" class="card shadow-sm mb-4">
                    <div id="h-utama" class="card-h card-h--purple" data-base="purple"><strong><i class="fas fa-file-alt mr-2"></i>Data Utama</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="nomor" class="form-label fw-bold">Nomor SK</label>
                                <div class="input-group">
                                    <input type="text" id="nomor" name="nomor" class="form-control @error('nomor') is-invalid @enderror" value="{{ $getPref('nomor') }}">
                                    <div class="input-group-append">
                                        <button type="button" id="btn-reserve-nomor" class="btn btn-outline-primary" title="Ambil nomor terbaru dari server"><i class="fas fa-sync-alt"></i></button>
                                    </div>
                                </div>
                                @error('nomor') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <div class="d-flex align-items-center mt-2">
                                    <div class="form-check form-switch mr-3">
                                        <input class="form-check-input" type="checkbox" id="toggleNomorManual">
                                        <label class="form-check-label small" for="toggleNomorManual">Mode Manual</label>
                                    </div>
                                    <a href="#" class="ml-auto small" id="toggleBuilder"><i class="fas fa-sliders-h mr-1"></i>Atur Komponen</a>
                                </div>
                                <div class="nomor-builder mt-2 p-2">
                                    {{-- Komponen builder tetap ada untuk jika pengguna ingin membuat nomor baru --}}
                                    <div class="row gx-2 gy-2">
                                        <div class="col-auto"><label class="small mb-1">No Urut</label><input type="text" id="no_urut" class="form-control form-control-sm" placeholder="001" value="001" style="width:90px"></div>
                                        <div class="col-auto align-self-end">/</div>
                                        <div class="col-auto"><label class="small mb-1">Klasifikasi</label><input type="text" id="no_klasifikasi" class="form-control form-control-sm" placeholder="B.10.1" value="{{ old('kode_klasifikasi','B.10.1') }}" style="width:140px"></div>
                                        <div class="col-auto align-self-end">/</div>
                                        <div class="col-auto"><label class="small mb-1">Unit</label><input type="text" id="no_unit" class="form-control form-control-sm" placeholder="TG" value="{{ old('unit','TG') }}" style="width:110px"></div>
                                        <div class="col-auto align-self-end">/UNIKA/</div>
                                        <div class="col-auto"><label class="small mb-1">Bulan</label><input type="text" id="no_romawi" class="form-control form-control-sm" placeholder="IX" value="{{ $currentRomawi }}" style="width:80px"></div>
                                        <div class="col-auto align-self-end">/</div>
                                        <div class="col-auto"><label class="small mb-1">Tahun</label><input type="text" id="no_tahun" class="form-control form-control-sm" placeholder="2025" value="{{ $currentYear }}" style="width:90px"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2">Preview: <span class="nomor-builder-preview" id="nomorPreviewText"></span></small>
                                </div>
                            </div>
                            {{-- PERUBAHAN: Format tanggal dari object $keputusan --}}
                            <div class="col-md-5"><label for="tanggal_asli" class="form-label fw-bold">Tanggal SK</label><input type="date" id="tanggal_asli" name="tanggal_asli" class="form-control @error('tanggal_asli') is-invalid @enderror" required value="{{ old('tanggal_asli', $keputusan->tanggal_asli ? \Carbon\Carbon::parse($keputusan->tanggal_asli)->format('Y-m-d') : '') }}">@error('tanggal_asli') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="col-12"><label for="tentang" class="form-label fw-bold">Tentang</label><textarea id="tentang" name="tentang" required rows="2" class="form-control @error('tentang') is-invalid @enderror" placeholder="Contoh: Penetapan Visi, Misi, dan Tujuan Fakultas Ilmu Komputer">{{ $getPref('tentang') }}</textarea>@error('tentang') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="col-md-6">
                                <label for="penandatangan" class="form-label fw-bold">Penandatangan</label>
                                <select name="penandatangan" id="penandatangan" class="form-select @error('penandatangan') is-invalid @enderror">
                                    <option value="">-- Pilih Pejabat --</option>
                                    @foreach(($pejabat ?? collect()) as $p)
                                    <option value="{{ $p->id }}" @selected((string)$getPref('penandatangan')===(string)$p->id)>
                                        {{ $p->nama_lengkap }} ({{ $p->peran->deskripsi ?? 'Pejabat' }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('penandatangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted d-block mt-1">Wajib diisi saat mengajukan.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="penerima_internal" class="form-label">Penerima Notifikasi Internal</label>
                                <select id="penerima_internal" name="penerima_internal[]" class="form-select" multiple>
                                    @foreach(($users ?? collect()) as $u)
                                    <option value="{{ $u->id }}" @selected(in_array($u->id, $prefPenerimaIds))>
                                        {{ $u->nama_lengkap }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Hanya untuk notifikasi sistem, tidak tercetak.</small>
                            </div>
                            <div class="col-12"><label for="tembusan-input" class="form-label">Tembusan (opsional)</label><input id="tembusan-input" name="tembusan" value="{{ $prefTembusanCsv }}" class="form-control" placeholder="Contoh: Yth. Rektor, BAAK, Arsip">@error('tembusan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror</div>
                        </div>
                    </div>
                </div>

                {{-- ========================= 2) MENIMBANG ========================= --}}
                <div id="section-menimbang" class="card shadow-sm mb-4">
                    <div id="h-menimbang" class="card-h card-h--teal" data-base="teal"><strong><i class="fas fa-balance-scale mr-2"></i>Menimbang</strong></div>
                    <div class="card-body">
                        <div id="menimbang-list" class="dynamic-list">
                            @foreach($prefMenimbang as $val)
                            <div class="input-group mb-2 dynamic-item menimbang-item"><span class="input-group-text dynamic-label"></span><input type="text" name="menimbang[]" class="form-control" value="{{$val}}" placeholder="Tulis poin pertimbangan..."><button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button></div>
                            @endforeach
                        </div>
                        @error('menimbang') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-menimbang"><i class="fas fa-plus mr-1"></i>Tambah Butir</button>
                    </div>
                </div>

                {{-- ========================= 3) MENGINGAT ========================= --}}
                <div id="section-mengingat" class="card shadow-sm mb-4">
                    <div id="h-mengingat" class="card-h card-h--blue" data-base="blue"><strong><i class="fas fa-book-open mr-2"></i>Mengingat</strong></div>
                    <div class="card-body">
                        <div id="mengingat-list" class="dynamic-list">
                            @foreach($prefMengingat as $val)
                            <div class="input-group mb-2 dynamic-item mengingat-item"><span class="input-group-text dynamic-label"></span><input type="text" name="mengingat[]" class="form-control" value="{{$val}}" placeholder="Tulis dasar hukum..."><button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button></div>
                            @endforeach
                        </div>
                        @error('mengingat') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-mengingat"><i class="fas fa-plus mr-1"></i>Tambah Butir</button>
                    </div>
                </div>

                {{-- ========================= 4) MENETAPKAN (DIKTUM) ========================= --}}
                <div id="section-menetapkan" class="card shadow-sm mb-4">
                    <div id="h-menetapkan" class="card-h card-h--amber" data-base="amber"><strong><i class="fas fa-gavel mr-2"></i>Menetapkan (Diktum)</strong></div>
                    <div class="card-body">
                        <div id="menetapkan-list">
                            @php $diktumLabels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH']; @endphp
                            @foreach($prefMenetapkan as $i => $mt)
                            <div class="diktum-item p-3 mb-3">
                                <div class="row g-2">
                                    <div class="col-md-3 col-lg-2"><label class="form-label small">Judul</label><input type="text" class="form-control form-control-sm" name="menetapkan[{{$i}}][judul]" value="{{$mt['judul']??($diktumLabels[$i]??'KETENTUAN')}}" readonly></div>
                                    <div class="col"><label class="form-label small">Isi Keputusan</label><textarea class="form-control wysiwyg" name="menetapkan[{{$i}}][isi]" rows="4">{!! $mt['isi']??'' !!}</textarea></div>
                                    <div class="col-auto d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-menetapkan" title="Hapus diktum"><i class="fas fa-trash-alt"></i></button></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('menetapkan') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-menetapkan"><i class="fas fa-plus mr-1"></i>Tambah Diktum</button>
                    </div>
                </div>

            </div>

            {{-- KANAN: QUICK NAV + AKSI --}}
            <div class="col-lg-4">
                {{-- Navigasi Cepat (Sama seperti create.blade.php) --}}
                <div class="card card-settings sticky-top mb-3" style="top:20px;">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 font-weight-bold text-purple"><i class="fas fa-list-ul mr-2"></i>Navigasi Cepat</h5>
                        <span class="badge badge-light border">Form</span>
                    </div>
                    <div class="card-body py-2">
                        <div class="list-group list-quicknav" id="quicknav">
                            <a href="#section-utama" class="list-group-item list-group-item-action active"><i class="far fa-id-card"></i>Data Utama</a>
                            <a href="#section-menimbang" class="list-group-item list-group-item-action"><i class="fas fa-balance-scale"></i>Menimbang <span class="badge badge-secondary ml-1" id="badge-menimbang">0</span></a>
                            <a href="#section-mengingat" class="list-group-item list-group-item-action"><i class="fas fa-book"></i>Mengingat <span class="badge badge-secondary ml-1" id="badge-mengingat">0</span></a>
                            <a href="#section-menetapkan" class="list-group-item list-group-item-action"><i class="fas fa-gavel"></i>Menetapkan <span class="badge badge-secondary ml-1" id="badge-menetapkan">0</span></a>
                        </div>
                    </div>
                </div>

                {{-- Aksi (Desktop) --}}
                <div class="card card-settings sticky-top" style="top:320px;">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-save mr-2 text-primary"></i>Aksi & Simpan</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Pilih tombol di bawah untuk menyimpan perubahan.<br>Shortcut: <code>Ctrl+S</code> = Draft, <code>Ctrl+Enter</code> = Submit</p>
                        <div class="d-grid gap-2">

                            @if($isSignerCanApprove && $isPending)
                            {{-- Penandatangan sedang merevisi dokumen pending --}}
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                            </button>
                            {{-- [PERBAIKAN] Ubah <a> menjadi <button> dengan mode khusus --}}
                            <button type="submit" name="mode" value="revisi_dan_setujui" class="btn btn-success">
                                <i class="fas fa-check mr-1"></i>Simpan & Lanjut Setujui
                            </button>
                            @else
                            {{-- Default (pembuat/admin TU) --}}
                            <button id="btn-submit-approve" type="submit" name="mode" value="pending" class="btn btn-success">
                                <i class="fas fa-paper-plane mr-1"></i>Simpan & Ajukan
                            </button>
                            <button id="btn-submit-draft" type="submit" name="mode" value="draft" class="btn btn-outline-secondary">
                                <i class="fas fa-save mr-1"></i>Draft
                            </button>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="action-bar d-lg-none">
            <div class="d-flex align-items-center">
                @if($isSignerCanApprove && $isPending)
                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fas fa-save mr-1"></i>Simpan Perubahan
                </button>
                <button type="submit" name="mode" value="revisi_dan_setujui" class="btn btn-success btn-block ms-2">
                    <i class="fas fa-check mr-1"></i>Simpan & Setujui
                </button>
                @else
                <button id="mb-approve" type="submit" name="mode" value="terkirim" class="btn btn-success btn-block">
                    <i class="fas fa-paper-plane mr-1"></i>Simpan & Kirim
                </button>
                <button id="mb-draft" type="submit" name="mode" value="draft" class="btn btn-ghost btn-block ms-2">
                    <i class="fas fa-save mr-1"></i>Draft
                </button>
                @endif
            </div>
        </div>
        {{-- [END REPLACE] --}}
    </form>

</div>
@endsection

@push('scripts')
{{-- Library Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // PERUBAHAN: Variabel IS_EDIT akan bernilai true di halaman ini
        const IS_EDIT = @json($isEdit);
        const skForm = document.getElementById('skForm');
        if (!skForm) return;

        // =========================================================================
        // 1. INITIALIZATIONS (Select2, Tagify, CKEditor)
        // =========================================================================
        $('#penerima_internal').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih satu atau lebih pengguna',
            width: '100%',
            allowClear: true,
            dropdownParent: $('#penerima_internal').closest('.col-md-6')
        });

        const tembusanInput = document.querySelector('#tembusan-input');
        if (tembusanInput) new Tagify(tembusanInput);

        window.editors = {};
        const CKEDITOR_SRC = 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js';
        let ckPromise = null;

        function loadCkScript() {
            if (window.ClassicEditor) return Promise.resolve(window.ClassicEditor);
            if (!ckPromise) {
                ckPromise = new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = CKEDITOR_SRC;
                    s.onload = () => resolve(window.ClassicEditor);
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }
            return ckPromise;
        }

        async function initEditor(textarea) {
            if (!textarea) return;
            try {
                const ClassicEditor = await loadCkScript();
                const instance = await ClassicEditor.create(textarea, {
                    toolbar: {
                        items: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
                    },
                    placeholder: 'Ketik isi keputusan di sini...'
                });
                window.editors[textarea.name] = instance;
                instance.model.document.on('change:data', () => updateUI());
            } catch (err) {
                console.error('Gagal memuat CKEditor:', err);
            }
        }
        document.querySelectorAll('textarea.wysiwyg').forEach(initEditor);

        // =========================================================================
        // 2. NOMOR SK MANAGEMENT (mendukung mode edit)
        // =========================================================================
        const nomorField = document.getElementById('nomor');
        const toggleManual = document.getElementById('toggleNomorManual');
        const builderBox = document.querySelector('.nomor-builder');
        const builderInputs = ['no_urut', 'no_klasifikasi', 'no_unit', 'no_romawi', 'no_tahun'];

        function buildNomorString() {
            const noUrutEl = document.getElementById('no_urut');
            const klasEl = document.getElementById('no_klasifikasi');
            const unitEl = document.getElementById('no_unit');
            const romaEl = document.getElementById('no_romawi');
            const tahunEl = document.getElementById('no_tahun');

            const urut = String(noUrutEl && noUrutEl.value ? noUrutEl.value : '1').padStart(3, '0');
            const klas = (klasEl && klasEl.value) ? klasEl.value : 'SK';
            const unit = (unitEl && unitEl.value) ? unitEl.value : 'UNIKA';
            const roma = (romaEl && romaEl.value) ? romaEl.value : 'I';
            const thn = (tahunEl && tahunEl.value) ? tahunEl.value : new Date().getFullYear();

            return urut + '/' + klas + '/' + unit + '/UNIKA/' + roma + '/' + thn;
        }

        function updateNomorField() {
            const v = buildNomorString();
            if (nomorField) nomorField.value = v;
            const previewText = document.getElementById('nomorPreviewText');
            if (previewText) previewText.textContent = v;
        }

        function setNomorMode(isManual) {
            if (nomorField) nomorField.readOnly = !isManual;
            builderInputs.forEach(function(id) {
                const el = document.getElementById(id);
                if (el) el.disabled = isManual;
            });
            if (!isManual) updateNomorField();
        }

        if (toggleManual) {
            toggleManual.checked = !!IS_EDIT; // true di halaman edit
            setNomorMode(!!IS_EDIT);
            toggleManual.addEventListener('change', function() {
                setNomorMode(toggleManual.checked);
            });
        }

        builderInputs.forEach(function(id) {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', function() {
                if (!toggleManual || !toggleManual.checked) updateNomorField();
            });
        });

        const toggleBuilderLink = document.getElementById('toggleBuilder');
        if (toggleBuilderLink) {
            toggleBuilderLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (builderBox) builderBox.classList.toggle('show');
            });
        }

        const btnReserve = document.getElementById('btn-reserve-nomor');
        if (btnReserve) {
            btnReserve.addEventListener('click', async function() {
                if (toggleManual && toggleManual.checked) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Mode Manual',
                        text: 'Matikan Mode Manual untuk mengambil nomor otomatis.'
                    });
                    return;
                }
                try {
                    const unitEl = document.getElementById('no_unit');
                    const klasEl = document.getElementById('no_klasifikasi');
                    const romaEl = document.getElementById('no_romawi');
                    const tahunEl = document.getElementById('no_tahun');

                    const payload = {
                        unit: (unitEl && unitEl.value) ? unitEl.value : '',
                        kode_klasifikasi: (klasEl && klasEl.value) ? klasEl.value : '',
                        bulan_romawi: (romaEl && romaEl.value) ? romaEl.value : '',
                        tahun: tahunEl && tahunEl.value ? parseInt(tahunEl.value) : new Date().getFullYear()
                    };

                    // Ambil CSRF token dari input hidden atau meta
                    let token = '';
                    const tokenInput = document.querySelector('input[name="_token"]');
                    if (tokenInput) token = tokenInput.value;
                    if (!token) {
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) token = meta.getAttribute('content');
                    }

                    const res = await fetch(`{{ route('surat_keputusan.nomor.reserve') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify(payload)
                    });
                    if (!res.ok) throw new Error('Gagal menghubungi server (' + res.status + ')');

                    const data = await res.json();
                    const urutEl = document.getElementById('no_urut');
                    if (urutEl) urutEl.value = data.no_urut;
                    updateNomorField();
                    Swal.fire({
                        icon: 'success',
                        title: 'Nomor Disiapkan',
                        text: data.nomor
                    });
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message
                    });
                }
            });
        }

        // =========================================================================
        // 3. DYNAMIC LISTS MANAGEMENT
        // =========================================================================
        function reindexList(listId, type) {
            const items = document.querySelectorAll('#' + listId + ' .dynamic-item');
            items.forEach(function(item, i) {
                const label = item.querySelector('.dynamic-label');
                if (label) {
                    label.textContent = (type === 'alpha') ? (String.fromCharCode(97 + i) + ')') : ((i + 1) + '.');
                }
                const delBtn = item.querySelector('.remove-row, .btn-remove-menetapkan');
                if (delBtn) delBtn.style.display = (item.parentElement.children.length > 1) ? '' : 'none';
            });
        }

        function reindexDiktum() {
            const labels = ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH', 'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH'];
            const items = document.querySelectorAll('#menetapkan-list .diktum-item');
            items.forEach(function(item, i) {
                item.querySelectorAll('input, textarea').forEach(function(el) {
                    el.name = el.name.replace(/menetapkan\[\d+\]/, 'menetapkan[' + i + ']');
                });
                const judulInput = item.querySelector('input[name$="[judul]"]');
                if (judulInput) judulInput.value = labels[i] || 'KETENTUAN';
                reindexList('menetapkan-list');
            });
        }

        document.addEventListener('click', async function(e) {
            let actionTaken = false;

            if (e.target.closest('#add-menimbang')) {
                const list = document.getElementById('menimbang-list');
                if (list && list.firstElementChild) {
                    const clone = list.firstElementChild.cloneNode(true);
                    const input = clone.querySelector('input');
                    if (input) input.value = '';
                    list.appendChild(clone);
                    reindexList('menimbang-list', 'alpha');
                    actionTaken = true;
                }
            } else if (e.target.closest('#add-mengingat')) {
                const list = document.getElementById('mengingat-list');
                if (list && list.firstElementChild) {
                    const clone = list.firstElementChild.cloneNode(true);
                    const input = clone.querySelector('input');
                    if (input) input.value = '';
                    list.appendChild(clone);
                    reindexList('mengingat-list', 'numeric');
                    actionTaken = true;
                }
            } else if (e.target.closest('#add-menetapkan')) {
                const list = document.getElementById('menetapkan-list');
                if (list && list.firstElementChild) {
                    const clone = list.firstElementChild.cloneNode(true);
                    const textarea = clone.querySelector('textarea.wysiwyg');
                    if (textarea) textarea.value = '';
                    clone.querySelectorAll('.ck-editor').forEach(function(el) {
                        el.remove();
                    });
                    list.appendChild(clone);
                    reindexDiktum();
                    await initEditor(clone.querySelector('textarea.wysiwyg'));
                    actionTaken = true;
                }
            } else if (e.target.closest('.remove-row')) {
                const item = e.target.closest('.dynamic-item');
                if (item && item.parentElement && item.parentElement.children.length > 1) {
                    const list = item.parentElement;
                    item.remove();
                    if (list.id === 'menimbang-list') reindexList('menimbang-list', 'alpha');
                    if (list.id === 'mengingat-list') reindexList('mengingat-list', 'numeric');
                    actionTaken = true;
                }
            } else if (e.target.closest('.btn-remove-menetapkan')) {
                const item = e.target.closest('.diktum-item');
                if (item && item.parentElement && item.parentElement.children.length > 1) {
                    const textarea = item.querySelector('textarea.wysiwyg');
                    if (textarea && textarea.name && window.editors[textarea.name]) {
                        window.editors[textarea.name].destroy().catch(function(err) {
                            console.error(err);
                        });
                        delete window.editors[textarea.name];
                    }
                    item.remove();
                    reindexDiktum();
                    actionTaken = true;
                }
            }

            if (actionTaken) setTimeout(updateUI, 60);
        });

        reindexList('menimbang-list', 'alpha');
        reindexList('mengingat-list', 'numeric');
        reindexDiktum();

        // =========================================================================
        // 4. UI FEEDBACK & NAVIGATION
        // =========================================================================
        function updateUI() {
            const badgeMenimbang = document.getElementById('badge-menimbang');
            const badgeMengingat = document.getElementById('badge-mengingat');
            const badgeMenetapkan = document.getElementById('badge-menetapkan');

            if (badgeMenimbang) badgeMenimbang.textContent = document.querySelectorAll('#menimbang-list .menimbang-item').length;
            if (badgeMengingat) badgeMengingat.textContent = document.querySelectorAll('#mengingat-list .mengingat-item').length;
            if (badgeMenetapkan) badgeMenetapkan.textContent = document.querySelectorAll('#menetapkan-list .diktum-item').length;

            const setStatus = function(id, status) {
                const header = document.querySelector('#h-' + id);
                const navLink = document.querySelector('#quicknav a[href="#section-' + id + '"]');
                if (header) {
                    header.className = 'card-h';
                    const baseColor = header.getAttribute('data-base') || 'purple';
                    const newClass = (status === 'complete') ? 'card-h--green' : (status === 'error') ? 'card-h--red' : ('card-h--' + baseColor);
                    header.classList.add(newClass);
                }
                if (navLink) {
                    navLink.classList.remove('has-error', 'is-complete');
                    if (status === 'complete') navLink.classList.add('is-complete');
                    else if (status === 'error') navLink.classList.add('has-error');
                }
            };

            const hasErr = function(id) {
                return !!document.querySelector('#section-' + id + ' .is-invalid, #section-' + id + ' [aria-invalid="true"]');
            };
            const filled = function(v) {
                return v && String(v).trim().length > 0;
            };
            const plain = function(html) {
                const d = document.createElement('div');
                d.innerHTML = html || '';
                return d.textContent.trim();
            };

            const tanggalEl = document.querySelector('[name="tanggal_asli"]');
            const tentangEl = document.querySelector('[name="tentang"]');

            setStatus('utama',
                hasErr('utama') ? 'error' :
                (filled(tanggalEl ? tanggalEl.value : '') && filled(tentangEl ? tentangEl.value : '') ? 'complete' : 'base')
            );

            const anyMenimbang = Array.prototype.slice.call(document.querySelectorAll('[name="menimbang[]"]')).some(function(i) {
                return filled(i.value);
            });
            setStatus('menimbang', hasErr('menimbang') ? 'error' : (anyMenimbang ? 'complete' : 'base'));

            const anyMengingat = Array.prototype.slice.call(document.querySelectorAll('[name="mengingat[]"]')).some(function(i) {
                return filled(i.value);
            });
            setStatus('mengingat', hasErr('mengingat') ? 'error' : (anyMengingat ? 'complete' : 'base'));

            const anyDiktum = Object.values(window.editors).some(function(ed) {
                return filled(plain(ed.getData()));
            });
            setStatus('menetapkan', hasErr('menetapkan') ? 'error' : (anyDiktum ? 'complete' : 'base'));
        }

        const navLinks = Array.prototype.slice.call(document.querySelectorAll('#quicknav a'));
        const sections = navLinks.map(function(a) {
            return document.querySelector(a.getAttribute('href'));
        }).filter(Boolean);
        if (sections.length) {
            const onScroll = function() {
                const scrollY = window.scrollY + 120;
                let current = sections[0];
                for (let i = 0; i < sections.length; i++) {
                    const sec = sections[i];
                    if (sec.offsetTop <= scrollY) current = sec;
                }
                navLinks.forEach(function(a) {
                    a.classList.toggle('active', a.getAttribute('href') === ('#' + current.id));
                });
            };
            window.addEventListener('scroll', onScroll, {
                passive: true
            });
            onScroll();
        }

        skForm.addEventListener('input', updateUI, true);
        skForm.addEventListener('change', updateUI, true);
        updateUI();

        // =========================================================================
        // 5. FORM ACTIONS & GUARDS
        // =========================================================================
        const validateSigner = function(e) {
            const signer = skForm.querySelector('select[name="penandatangan"]');
            if (!signer || !signer.value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Penandatangan Belum Dipilih',
                    text: 'Silakan pilih penandatangan terlebih dahulu.'
                });
                if (signer) signer.focus();
            }
        };

        const btnApprove = document.getElementById('btn-submit-approve');
        if (btnApprove) btnApprove.addEventListener('click', validateSigner);

        const mbApprove = document.getElementById('mb-approve');
        if (mbApprove) mbApprove.addEventListener('click', validateSigner);

        let isDirty = false;
        skForm.addEventListener('input', function() {
            isDirty = true;
        });
        window.addEventListener('beforeunload', function(e) {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = 'Perubahan belum disimpan. Yakin keluar?';
            }
        });
        skForm.addEventListener('submit', function() {
            isDirty = false;
        });

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's' || e.key === 'S') {
                    e.preventDefault();
                    const d = document.getElementById('btn-submit-draft');
                    if (d) d.click();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const a = document.getElementById('btn-submit-approve');
                    if (a) a.click();
                }
            }
        });
    });
</script>

@endpush