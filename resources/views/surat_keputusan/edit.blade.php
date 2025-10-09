{{-- resources/views/surat_keputusan/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Surat Keputusan')

@php
// Alias agar kompatibel baik $sk maupun $keputusan
$keputusan = $keputusan ?? $sk ?? null;
@endphp

@push('styles')
{{-- ============ Library Styles ============ --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css"/>

<style>
    :root{
        --purple-500:#6f42c1;
        --purple-600:#5a33b8;
        --purple-700:#412674;

        --teal-500:#0ab39c;
        --blue-500:#3f8cff;
        --amber-500:#f59f00;
        --green-600:#16a34a;
        --red-500:#ef4444;

        --card-radius:.9rem;
        --input-radius:.55rem;

        --shadow-sm:0 10px 28px rgba(28,28,28,.06);
        --muted:#636e7b;
        --border:#e0e6ed;
        --bg-soft:#f3f6fa;
    }

    /* === Layout & Cards (match create) === */
    .page-header{
        background:var(--bg-soft);
        padding:1.3rem 2.2rem;
        border-radius:1.1rem;
        margin-bottom:1.6rem;
        border:1px solid var(--border);
        display:flex;align-items:center;gap:1.3rem
    }
    .page-header .icon{
        background:linear-gradient(135deg,var(--purple-500) 0,#9a6ee5 100%);
        width:54px;height:54px;display:flex;align-items:center;justify-content:center;border-radius:50%;
        box-shadow:0 1px 10px #6f42c14d;font-size:1.6rem;color:#fff
    }
    .page-header-title{font-weight:700;color:var(--purple-700);font-size:1.7rem;letter-spacing:-.2px;margin:0}
    .page-header-desc{color:var(--muted);font-size:.98rem;margin:.1rem 0 0}

    .card-settings,.card{border:none;border-radius:var(--card-radius);box-shadow:var(--shadow-sm)}
    .card-settings .card-header{background:#fff;border-bottom:1px solid #f0f0f0}
    .card .card-body{padding:1rem 1.1rem}
    .form-control,.custom-select,.form-select{border-radius:var(--input-radius)}
    .input-group-text{background:#eef1f6;border-color:#dfe5ec}

    .card-h{border-bottom:0;color:#fff;padding:.85rem 1.1rem;border-top-left-radius:var(--card-radius);border-top-right-radius:var(--card-radius)}
    .card-h--purple{background:linear-gradient(135deg,var(--purple-500) 0%,#9a6ee5 100%)}
    .card-h--teal{background:linear-gradient(135deg,var(--teal-500) 0%,#41d6c3 100%)}
    .card-h--blue{background:linear-gradient(135deg,var(--blue-500) 0%,#6aa6ff 100%)}
    .card-h--amber{background:linear-gradient(135deg,var(--amber-500) 0%,#f7b733 100%)}
    .card-h--green{background:linear-gradient(135deg,var(--green-600) 0%,#34d399 100%)}
    .card-h--red{background:linear-gradient(135deg,var(--red-500) 0%,#f87171 100%)}

    /* QuickNav & UI feedback */
    .list-quicknav .list-group-item{border:0;padding:.55rem .75rem;border-radius:.6rem;display:flex;align-items:center;gap:.55rem;transition:.15s ease}
    .list-quicknav .list-group-item:hover{background:#f8f7ff}
    .list-quicknav .active{background:#f1eaff;color:var(--purple-600);font-weight:600}
    a.is-complete{background:#eaf9f0!important;color:#146c2e!important;border-left:4px solid #22c55e!important}
    a.has-error{background:#fdecec!important;color:#b42318!important;border-left:4px solid #ef4444!important}
    .text-purple{color:var(--purple-500)!important}
    [id^="section-"]{scroll-margin-top:90px}

    /* Select2 (Bootstrap 4 theme) */
    .select2-container--bootstrap4 .select2-selection{
        min-height:calc(1.5em + .75rem + 2px);padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:var(--input-radius)
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice{
        background:#f1eaff;border:1px solid #cbb5ff;color:var(--purple-600);font-weight:500;border-radius:.5rem
    }

    /* Nomor builder */
    .nomor-builder{display:none;background:#f8f9fa;border:1px solid #dee2e6;border-radius:.5rem}
    .nomor-builder.show{display:block}
    .nomor-builder-preview{font-family:'Courier New',monospace;background:#e9ecef;padding:.2rem .5rem;border-radius:4px}

    /* Dynamic blocks */
    .diktum-item{background:#f8f9fa;border:1px solid #e9ecef;border-radius:.6rem;transition:box-shadow .2s ease}
    .diktum-item:focus-within{box-shadow:0 0 0 .2rem rgba(0,123,255,.25)}
    .menimbang-item .input-group-text,.mengingat-item .input-group-text{min-width:34px;justify-content:center}

    /* Mobile action bar */
    .btn-ghost{background:#f6f7fb;border:1px solid #eef0f4}
    .action-bar{position:sticky;bottom:0;left:0;right:0;z-index:998;background:rgba(255,255,255,.92);backdrop-filter:blur(6px);border-top:1px solid #eaeef4;padding:.75rem}
    @media(min-width:992px){.action-bar{display:none}}

    /* TEMBUSAN */
    .tembusan-wrap{border:1px solid #e9ecef;border-radius:.5rem;overflow:hidden}
    .tembusan-head{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;background:linear-gradient(90deg,#5c7cfa 0%,#845ef7 100%);color:#fff}
    .tembusan-body{padding:1rem;background:#fff}
    .tembusan-preview{background:#f8f9ff;border:1px dashed #cdd5ff;border-radius:.5rem;padding:1rem}
    .tembusan-preview h6{font-weight:700;color:#3b5bdb;margin-bottom:.5rem}
    .tembusan-tools .btn{margin-left:.5rem}
    .tagify__tag{font-weight:600}
    .tagify__input{min-width:140px}
</style>
@endpush

@section('content_header')
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
$mode = 'edit';
$isEdit = true;

// Auto numbering setup
$bulanRomawi = ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
$currentRomawi = $bulanRomawi[date('n')] ?? 'IX';
$currentYear = date('Y');

// Helper
$getPref = fn($key, $default = null) => old($key, $keputusan->{$key} ?? $default);

// Prefill
$prefMenimbang  = (array) $getPref('menimbang', ['']);  $prefMenimbang  = $prefMenimbang ?: [''];
$prefMengingat  = (array) $getPref('mengingat', ['']);  $prefMengingat  = $prefMengingat ?: [''];
$menetapkanData = $getPref('menetapkan', [['judul'=>'KESATU','isi'=>'']]);
if (is_string($menetapkanData)) { $menetapkanData = json_decode($menetapkanData, true) ?: []; }
$prefMenetapkan = $menetapkanData ?: [['judul'=>'KESATU','isi'=>'']];

// Tembusan
$tembusanVal = $getPref('tembusan', []);
$prefTembusanCsv = is_array($tembusanVal) ? implode(', ', $tembusanVal) : $tembusanVal;

// konteks tombol
$isSignerCanApprove = auth()->user()->can('approve', $keputusan);
$isPending = $keputusan->status_surat === 'pending';

// presets tembusan
$tembusanPresets = [
    'Yth. Rektor','Yth. Wakil Rektor I','Yth. Wakil Rektor II',
    'Dekan Fakultas Ilmu Komputer','BAAK','BAUK','BAK',
    'Kepala Program Studi Sistem Informasi','Unit Kepegawaian','Arsip'
];
@endphp

<div class="container-fluid">
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible shadow-sm">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5 class="mb-1"><i class="icon fas fa-ban"></i> Gagal Memperbarui!</h5>
        <small>Mohon periksa kembali isian Anda:</small>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form id="skForm" action="{{ route('surat_keputusan.update', $keputusan->id) }}" method="POST" autocomplete="off">
        @csrf @method('PUT')

        <div class="row">
            <div class="col-lg-8 mb-3">

                {{-- ========================= 1) DATA UTAMA ========================= --}}
                <section id="section-utama" class="card shadow-sm mb-4">
                    <header id="h-utama" class="card-h card-h--purple" data-base="purple">
                        <strong><i class="fas fa-file-alt mr-2"></i>Data Utama</strong>
                    </header>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="nomor" class="form-label font-weight-bold">Nomor SK</label>
                                <div class="input-group">
                                    <input type="text" id="nomor" name="nomor" class="form-control @error('nomor') is-invalid @enderror" value="{{ $getPref('nomor') }}">
                                    <div class="input-group-append">
                                        <button type="button" id="btn-reserve-nomor" class="btn btn-outline-primary" title="Ambil nomor terbaru dari server"><i class="fas fa-sync-alt"></i></button>
                                    </div>
                                </div>
                                @error('nomor') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <div class="d-flex align-items-center mt-2">
                                    <div class="form-check form-switch mr-3">
                                        <input class="form-check-input" type="checkbox" id="toggleNomorManual" aria-label="Mode Nomor Manual">
                                        <label class="form-check-label small" for="toggleNomorManual">Mode Manual</label>
                                    </div>
                                    <a href="#" class="ml-auto small" id="toggleBuilder"><i class="fas fa-sliders-h mr-1"></i>Atur Komponen</a>
                                </div>
                                <div class="nomor-builder mt-2 p-2" aria-live="polite">
                                    <div class="row gx-2 gy-2">
                                        <div class="col-auto"><label class="small mb-1" for="no_urut">No Urut</label><input type="text" id="no_urut" class="form-control form-control-sm" value="001" style="width:90px"></div>
                                        <div class="col-auto align-self-end">/</div>
                                        <div class="col-auto"><label class="small mb-1" for="no_klasifikasi">Klasifikasi</label><input type="text" id="no_klasifikasi" class="form-control form-control-sm" value="{{ old('kode_klasifikasi','B.10.1') }}" style="width:140px"></div>
                                        <div class="col-auto align-self-end">/</div>
                                        <div class="col-auto"><label class="small mb-1" for="no_unit">Unit</label><input type="text" id="no_unit" class="form-control form-control-sm" value="{{ old('unit','TG') }}" style="width:110px"></div>
                                        <div class="col-auto align-self-end">/UNIKA/</div>
                                        <div class="col-auto"><label class="small mb-1" for="no_romawi">Bulan</label><input type="text" id="no_romawi" class="form-control form-control-sm" value="{{ $currentRomawi }}" style="width:80px"></div>
                                        <div class="col-auto align-self-end">/</div>
                                        <div class="col-auto"><label class="small mb-1" for="no_tahun">Tahun</label><input type="text" id="no_tahun" class="form-control form-control-sm" value="{{ $currentYear }}" style="width:90px"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2">Preview: <span class="nomor-builder-preview" id="nomorPreviewText"></span></small>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label for="tanggal_asli" class="form-label font-weight-bold">Tanggal SK</label>
                                <input type="date" id="tanggal_asli" name="tanggal_asli" class="form-control @error('tanggal_asli') is-invalid @enderror" required value="{{ old('tanggal_asli', $keputusan->tanggal_asli ? \Carbon\Carbon::parse($keputusan->tanggal_asli)->format('Y-m-d') : '') }}">
                                @error('tanggal_asli') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="tentang" class="form-label font-weight-bold">Tentang</label>
                                <textarea id="tentang" name="tentang" required rows="2" class="form-control @error('tentang') is-invalid @enderror" placeholder="Contoh: Penetapan Visi, Misi, dan Tujuan Fakultas Ilmu Komputer">{{ $getPref('tentang') }}</textarea>
                                @error('tentang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- ====== TEMBUSAN ====== --}}
                            <div class="col-12">
                                <div class="tembusan-wrap">
                                    <div class="tembusan-head">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-copy mr-2"></i><strong>Tembusan</strong>
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
                                        <input id="tembusan-input" name="tembusan" value="{{ $prefTembusanCsv }}" class="form-control" placeholder="Contoh: Yth. Rektor, BAAK, Arsip">
                                        @error('tembusan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                                        <div class="custom-control custom-switch mt-3">
                                            <input type="checkbox" class="custom-control-input" id="tembusanShowTitle" checked>
                                            <label class="custom-control-label" for="tembusanShowTitle">Cetak judul <em>“Tembusan Yth:”</em></label>
                                        </div>

                                        <div class="tembusan-preview mt-3" id="tembusanPreview" aria-live="polite">
                                            <h6 class="mb-2"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>
                                            <div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="tembusan_formatted" id="tembusan_formatted">
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ========================= 2) MENIMBANG ========================= --}}
                <section id="section-menimbang" class="card shadow-sm mb-4">
                    <header id="h-menimbang" class="card-h card-h--teal" data-base="teal"><strong><i class="fas fa-balance-scale mr-2"></i>Menimbang</strong></header>
                    <div class="card-body">
                        <div id="menimbang-list" class="dynamic-list">
                            @foreach($prefMenimbang as $val)
                                <div class="input-group mb-2 dynamic-item menimbang-item">
                                    <span class="input-group-text dynamic-label"></span>
                                    <input type="text" name="menimbang[]" class="form-control" value="{{ $val }}" placeholder="Tulis poin pertimbangan...">
                                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                                </div>
                            @endforeach
                        </div>
                        @error('menimbang') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-menimbang"><i class="fas fa-plus mr-1"></i>Tambah Butir</button>
                    </div>
                </section>

                {{-- ========================= 3) MENGINGAT ========================= --}}
                <section id="section-mengingat" class="card shadow-sm mb-4">
                    <header id="h-mengingat" class="card-h card-h--blue" data-base="blue"><strong><i class="fas fa-book-open mr-2"></i>Mengingat</strong></header>
                    <div class="card-body">
                        <div id="mengingat-list" class="dynamic-list">
                            @foreach($prefMengingat as $val)
                                <div class="input-group mb-2 dynamic-item mengingat-item">
                                    <span class="input-group-text dynamic-label"></span>
                                    <input type="text" name="mengingat[]" class="form-control" value="{{ $val }}" placeholder="Tulis dasar hukum...">
                                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                                </div>
                            @endforeach
                        </div>
                        @error('mengingat') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-mengingat"><i class="fas fa-plus mr-1"></i>Tambah Butir</button>
                    </div>
                </section>

                {{-- ========================= 4) MENETAPKAN (DIKTUM) ========================= --}}
                <section id="section-menetapkan" class="card shadow-sm mb-4">
                    <header id="h-menetapkan" class="card-h card-h--amber" data-base="amber"><strong><i class="fas fa-gavel mr-2"></i>Menetapkan (Diktum)</strong></header>
                    <div class="card-body">
                        <div id="menetapkan-list">
                            @php $diktumLabels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH']; @endphp
                            @foreach($prefMenetapkan as $i => $mt)
                                <div class="diktum-item p-3 mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-3 col-lg-2">
                                            <label class="form-label small">Judul</label>
                                            <input type="text" class="form-control form-control-sm" name="menetapkan[{{ $i }}][judul]" value="{{ $mt['judul'] ?? ($diktumLabels[$i] ?? 'KETENTUAN') }}" readonly>
                                        </div>
                                        <div class="col">
                                            <label class="form-label small">Isi Keputusan</label>
                                            <textarea class="form-control wysiwyg" name="menetapkan[{{ $i }}][isi]" rows="4">{!! $mt['isi'] ?? '' !!}</textarea>
                                        </div>
                                        <div class="col-auto d-flex align-items-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-menetapkan" title="Hapus diktum"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('menetapkan') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-menetapkan"><i class="fas fa-plus mr-1"></i>Tambah Diktum</button>
                    </div>
                </section>

            </div>

            {{-- KANAN: QUICK NAV + AKSI --}}
            <aside class="col-lg-4">

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

                <div class="card card-settings sticky-top" style="top:320px;">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-save mr-2 text-primary"></i>Aksi & Simpan</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="penandatangan" class="form-label font-weight-bold">Penandatangan</label>
                            {{-- PERBAIKAN: gunakan .form-control agar konsisten dengan AdminLTE/BS4 --}}
                            <select name="penandatangan" id="penandatangan" class="form-control @error('penandatangan') is-invalid @enderror">
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
                        <hr>
                        <p class="text-muted small mb-3">Pilih tombol di bawah untuk menyimpan perubahan.<br>Shortcut: <code>Ctrl+S</code> = Draft, <code>Ctrl+Enter</code> = Submit</p>
                        <div class="d-grid gap-2">
                            @if($isSignerCanApprove && $isPending)
                                <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                                <button type="submit" name="mode" value="revisi_dan_setujui" class="btn btn-success"><i class="fas fa-check mr-1"></i>Simpan & Lanjut Setujui</button>
                            @else
                                <button id="btn-submit-approve" type="submit" name="mode" value="pending" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i>Simpan & Ajukan</button>
                                <button id="btn-submit-draft" type="submit" name="mode" value="draft" class="btn btn-outline-secondary"><i class="fas fa-save mr-1"></i>Draft</button>
                            @endif
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        {{-- Mobile --}}
        <div class="action-bar d-lg-none">
            <div class="d-flex align-items-center">
                @if($isSignerCanApprove && $isPending)
                    <button type="submit" class="btn btn-warning btn-block"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                    <button type="submit" name="mode" value="revisi_dan_setujui" class="btn btn-success btn-block ml-2"><i class="fas fa-check mr-1"></i>Simpan & Setujui</button>
                @else
                    <button id="mb-approve" type="submit" name="mode" value="pending" class="btn btn-success btn-block"><i class="fas fa-paper-plane mr-1"></i>Simpan & Kirim</button>
                    <button id="mb-draft" type="submit" name="mode" value="draft" class="btn btn-ghost btn-block ml-2"><i class="fas fa-save mr-1"></i>Draft</button>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Library Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- CKEditor --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>
$(function(){
    const IS_EDIT = @json($isEdit);
    const skForm = document.getElementById('skForm');
    if (!skForm) return;

    // ==================== 1) INIT (Select2, Tagify, CKEditor) ====================
    $('#penandatangan').select2({
        theme:'bootstrap4',
        placeholder:'Pilih Pejabat Penandatangan',
        width:'100%'
    });

    // Tagify Tembusan + preview
    function escHtml(s){return String(s).replace(/[&<>"'`=\/]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','=':'&#x3D;','`':'&#x60;'}[c]));}
    const TEMBUSAN_PRESETS = @json($tembusanPresets);
    const tembusanInput = document.querySelector('#tembusan-input');
    let tagify;
    if (tembusanInput){
        tagify = new Tagify(tembusanInput, {
            enforceWhitelist:false, whitelist:TEMBUSAN_PRESETS, trim:true, duplicates:false, delimiters:",|\n", editTags:1,
            dropdown:{enabled:1,maxItems:20,fuzzySearch:true,highlightFirst:true,placeAbove:false},
            placeholder:"Contoh: Yth. Rektor, BAAK, Arsip",
            transformTag:(t)=>{
                let v=(t.value||'').trim(); if(!v) return;
                v=v.toLowerCase().replace(/\b\w/g,m=>m.toUpperCase());
                const needsYth=/^(Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris)\b/i.test(v)&&!/^Yth\.\s/i.test(v);
                if(needsYth) v='Yth. '+v;
                t.value=v;
            }
        });
        const $preview = $('#tembusanPreview');
        const $showTitle = $('#tembusanShowTitle');

        function renderTembusanPreview(){
            const data = tagify.value.map(t => (t.value||'').trim()).filter(Boolean);
            if (!data.length){
                $preview.html('<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>');
                $('#tembusan_formatted').val('');
                return;
            }
            const showTitle = $showTitle.is(':checked');
            const titleHtml = showTitle ? '<div class="mb-2 font-weight-bold">Tembusan Yth:</div>' : '';
            const listHtml = '<ol class="mb-0">'+ data.map(v=>`<li>${escHtml(v)}</li>`).join('') +'</ol>';
            $preview.html(`<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`);
            const plain = (showTitle?'Tembusan Yth:\n':'') + data.map((v,i)=>`${i+1}. ${v}`).join('\n');
            $('#tembusan_formatted').val(plain);
        }

        // Prefill → langsung render preview
        setTimeout(renderTembusanPreview, 0);

        tagify.on('add',renderTembusanPreview)
              .on('remove',renderTembusanPreview)
              .on('edit:updated',renderTembusanPreview);

        $(document).on('change','#tembusanShowTitle',renderTembusanPreview);
        $(document).on('click','#btnPasteTembusan',async function(){
            try{
                const txt=await navigator.clipboard.readText();
                if(!txt) return;
                const items=txt.split(/[\n,]/).map(s=>s.trim()).filter(Boolean);
                const existing=new Set(tagify.value.map(t=>(t.value||'').toLowerCase()));
                tagify.addTags(items.filter(s=>!existing.has(s.toLowerCase())).map(s=>({value:s})));
            }catch(e){
                Swal.fire('Tidak bisa mengakses clipboard','Izinkan akses atau tempel manual.','info');
            }
        });
        $(document).on('click','#btnClearTembusan',()=>tagify.removeAllTags());
    }

    // CKEditor
    window.editors = {};
    const initEditor = (textarea) => {
        if (!textarea || !window.ClassicEditor) return;
        ClassicEditor.create(textarea, {
            toolbar:{items:['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo']},
            placeholder:'Ketik isi keputusan di sini...'
        }).then(instance=>{
            window.editors[textarea.name]=instance;
            instance.model.document.on('change:data',()=>updateUI());
        }).catch(err=>console.error('Gagal CKEditor:',err));
    };
    document.querySelectorAll('textarea.wysiwyg').forEach(initEditor);

    // ==================== 3) NOMOR BUILDER ====================
    const nomorField=document.getElementById('nomor');
    const toggleManual=document.getElementById('toggleNomorManual');
    const builderBox=document.querySelector('.nomor-builder');
    const builderInputs=['no_urut','no_klasifikasi','no_unit','no_romawi','no_tahun'];

    function buildNomorString(){
        const urut=String((document.getElementById('no_urut')?.value||'1')).padStart(3,'0');
        const klas=(document.getElementById('no_klasifikasi')?.value||'SK').trim();
        const unit=(document.getElementById('no_unit')?.value||'UNIKA').trim();
        const roma=(document.getElementById('no_romawi')?.value||'I').trim().toUpperCase();
        const thn=(document.getElementById('no_tahun')?.value||new Date().getFullYear()).toString().trim();
        return `${urut}/${klas}/${unit}/UNIKA/${roma}/${thn}`;
    }
    function updateNomorField(){
        const v=buildNomorString();
        if(nomorField) nomorField.value=v;
        const p=document.getElementById('nomorPreviewText');
        if(p) p.textContent=v;
    }
    function setNomorMode(isManual){
        if(nomorField) nomorField.readOnly=!isManual;
        builderInputs.forEach(id=>{
            const el=document.getElementById(id);
            if(el) el.disabled=isManual;
        });
        if(!isManual) updateNomorField();
    }
    if (toggleManual){
        toggleManual.checked=!!IS_EDIT; // edit = manual default
        setNomorMode(!!IS_EDIT);
        toggleManual.addEventListener('change',()=>setNomorMode(toggleManual.checked));
    }
    builderInputs.forEach(id=>{
        const el=document.getElementById(id);
        el && el.addEventListener('input',()=>{ if(!toggleManual?.checked) updateNomorField(); });
    });
    document.getElementById('toggleBuilder')?.addEventListener('click',e=>{
        e.preventDefault();
        builderBox?.classList.toggle('show');
    });
    document.getElementById('btn-reserve-nomor')?.addEventListener('click',async function(){
        if(toggleManual && toggleManual.checked){
            Swal.fire({icon:'info',title:'Mode Manual',text:'Matikan Mode Manual untuk mengambil nomor otomatis.'});
            return;
        }
        try{
            const payload={
                unit:document.getElementById('no_unit')?.value||'',
                kode_klasifikasi:document.getElementById('no_klasifikasi')?.value||'',
                bulan_romawi:document.getElementById('no_romawi')?.value||'',
                tahun:parseInt(document.getElementById('no_tahun')?.value)||new Date().getFullYear()
            };
            let token=document.querySelector('input[name="_token"]')?.value||document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'';
            const res=await fetch(`{{ route('surat_keputusan.nomor.reserve') }}`,{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
                body:JSON.stringify(payload)
            });
            if(!res.ok) throw new Error('Gagal menghubungi server ('+res.status+')');
            const data=await res.json();
            const urutEl=document.getElementById('no_urut'); if(urutEl) urutEl.value=data.no_urut;
            updateNomorField();
            Swal.fire({icon:'success',title:'Nomor Disiapkan',text:data.nomor||'Nomor berhasil diambil.'});
        }catch(err){
            Swal.fire({icon:'error',title:'Gagal',text:err.message});
        }
    });

    // ==================== 4) DYNAMIC LISTS ====================
    function reindexList(listId,type){
        const items=document.querySelectorAll('#'+listId+' .dynamic-item');
        items.forEach((item,i)=>{
            const label=item.querySelector('.dynamic-label');
            if(label) label.textContent=(type==='alpha')?(String.fromCharCode(97+i)+')'):(i+1)+'.';
            const del=item.querySelector('.remove-row, .btn-remove-menetapkan');
            if(del) del.style.display=(item.parentElement.children.length>1)?'':'none';
        });
        if(listId==='menimbang-list') $('#badge-menimbang').text(items.length);
        if(listId==='mengingat-list') $('#badge-mengingat').text(items.length);
    }
    function reindexDiktum(){
        const LABELS=['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
        $('#menetapkan-list .diktum-item').each(function(i){
            $(this).find('input[name$="[judul]"]').val(LABELS[i]||`KETENTUAN ${i+1}`);
        });
        $('#badge-menetapkan').text($('#menetapkan-list .diktum-item').length);
    }

    document.addEventListener('click', async function(e){
        let act=false;
        if(e.target.closest('#add-menimbang')){
            const list=$('#menimbang-list'); const html = `
                <div class="input-group mb-2 dynamic-item menimbang-item">
                    <span class="input-group-text dynamic-label"></span>
                    <input type="text" name="menimbang[]" class="form-control" placeholder="Tulis poin pertimbangan...">
                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                </div>`;
            list.append(html); reindexList('menimbang-list','alpha'); act=true;
        } else if(e.target.closest('#add-mengingat')){
            const list=$('#mengingat-list'); const html = `
                <div class="input-group mb-2 dynamic-item mengingat-item">
                    <span class="input-group-text dynamic-label"></span>
                    <input type="text" name="mengingat[]" class="form-control" placeholder="Tulis dasar hukum...">
                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                </div>`;
            list.append(html); reindexList('mengingat-list','numeric'); act=true;
        } else if(e.target.closest('#add-menetapkan')){
            const i = $('#menetapkan-list .diktum-item').length;
            const html = `
                <div class="diktum-item p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small">Judul</label>
                            <input type="text" class="form-control form-control-sm" name="menetapkan[${i}][judul]" value="KETENTUAN" readonly>
                        </div>
                        <div class="col">
                            <label class="form-label small">Isi Keputusan</label>
                            <textarea class="form-control wysiwyg" name="menetapkan[${i}][isi]" rows="4"></textarea>
                        </div>
                        <div class="col-auto d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-menetapkan" title="Hapus diktum"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>`;
            $('#menetapkan-list').append(html);
            const ta = $('#menetapkan-list .diktum-item:last textarea.wysiwyg').get(0);
            initEditor(ta); reindexDiktum(); act=true;
        } else if(e.target.closest('.remove-row')){
            const item=e.target.closest('.dynamic-item');
            if(item && item.parentElement.children.length>1){
                const parentId = item.parentElement.id; item.remove();
                if (parentId==='menimbang-list') reindexList('menimbang-list','alpha');
                if (parentId==='mengingat-list') reindexList('mengingat-list','numeric');
                act=true;
            }
        } else if(e.target.closest('.btn-remove-menetapkan')){
            const d=e.target.closest('.diktum-item');
            if (d && d.parentElement.children.length>1){
                const ta=d.querySelector('textarea.wysiwyg');
                if (ta?.name && window.editors[ta.name]) {
                    await window.editors[ta.name].destroy().catch(()=>{});
                    delete window.editors[ta.name];
                }
                d.remove(); reindexDiktum(); act=true;
            }
        }
        if(act) setTimeout(updateUI,60);
    });
    reindexList('menimbang-list','alpha');
    reindexList('mengingat-list','numeric');
    reindexDiktum();

    // ==================== 5) UI FEEDBACK & NAV ====================
    function updateUI(){
        const totalMenimbang = $('#menimbang-list .menimbang-item').length;
        const totalMengingat = $('#mengingat-list .mengingat-item').length;
        const totalDiktum    = $('#menetapkan-list .diktum-item').length;
        $('#badge-menimbang').text(totalMenimbang);
        $('#badge-mengingat').text(totalMengingat);
        $('#badge-menetapkan').text(totalDiktum);

        function setStatus(id,st){
            const h=document.querySelector('#h-'+id), n=document.querySelector('#quicknav a[href="#section-'+id+'"]');
            if(h){
                h.className='card-h';
                const base=h.getAttribute('data-base')||'purple';
                h.classList.add(st==='complete'?'card-h--green':st==='error'?'card-h--red':'card-h--'+base);
            }
            if(n){
                n.classList.remove('has-error','is-complete');
                if(st==='complete') n.classList.add('is-complete');
                else if(st==='error') n.classList.add('has-error');
            }
        }
        const hasErr=id=>!!document.querySelector('#section-'+id+' .is-invalid, #section-'+id+' [aria-invalid="true"]');
        const filled=v=>v && String(v).trim().length>0;
        const plain=html=>{ const d=document.createElement('div'); d.innerHTML=html||''; return d.textContent.trim(); };

        const tanggalEl=document.querySelector('[name="tanggal_asli"]');
        const tentangEl=document.querySelector('[name="tentang"]');
        setStatus('utama', hasErr('utama') ? 'error' : (filled(tanggalEl?.value)&&filled(tentangEl?.value)?'complete':'base'));

        const anyMenimbang=[...document.querySelectorAll('[name="menimbang[]"]')].some(i=>filled(i.value));
        setStatus('menimbang', hasErr('menimbang') ? 'error' : (anyMenimbang?'complete':'base'));

        const anyMengingat=[...document.querySelectorAll('[name="mengingat[]"]')].some(i=>filled(i.value));
        setStatus('mengingat', hasErr('mengingat') ? 'error' : (anyMengingat?'complete':'base'));

        const anyDiktum=Object.values(window.editors).some(ed=>filled(plain(ed.getData())));
        setStatus('menetapkan', hasErr('menetapkan') ? 'error' : (anyDiktum?'complete':'base'));
    }
    const navLinks=[...document.querySelectorAll('#quicknav a')];
    const sections=navLinks.map(a=>document.querySelector(a.getAttribute('href'))).filter(Boolean);
    if(sections.length){
        function onScroll(){
            const y=window.scrollY+120;
            let cur=sections[0];
            for(const s of sections){ if(s.offsetTop<=y) cur=s; }
            navLinks.forEach(a=>a.classList.toggle('active', a.getAttribute('href')==='#'+cur.id));
        }
        window.addEventListener('scroll', onScroll, {passive:true});
        onScroll();
    }
    skForm.addEventListener('input',updateUI,true);
    skForm.addEventListener('change',updateUI,true);
    updateUI();

    // ==================== 6) GUARDS & SHORTCUTS ====================
    function validateSigner(e){
        const target = e.currentTarget;
        const submitMode = target?.getAttribute('name')==='mode' ? target.value : null;
        if (submitMode === 'pending'){ // hanya validasi saat mengajukan
            const signer=skForm.querySelector('select[name="penandatangan"]');
            if(!signer || !signer.value){
                e.preventDefault();
                Swal.fire({icon:'warning',title:'Penandatangan Belum Dipilih',text:'Silakan pilih penandatangan terlebih dahulu.'});
                if (signer) $(signer).select2('open');
            }
        }
    }
    $('#btn-submit-approve').on('click', validateSigner);
    $('#mb-approve').on('click', validateSigner);

    // Dirty guard
    let isDirty=false;
    skForm.addEventListener('input',()=>{isDirty=true;});
    window.addEventListener('beforeunload',e=>{ if(isDirty){ e.preventDefault(); e.returnValue='Perubahan belum disimpan. Yakin keluar?'; }});
    skForm.addEventListener('submit',()=>{isDirty=false;});

    // Shortcuts
    document.addEventListener('keydown',function(e){
        if(e.ctrlKey||e.metaKey){
            if(e.key==='s' || e.key==='S'){ e.preventDefault(); document.getElementById('btn-submit-draft')?.click(); }
            else if(e.key==='Enter'){ e.preventDefault(); document.getElementById('btn-submit-approve')?.click(); }
        }
    });
});
</script>
@endpush
