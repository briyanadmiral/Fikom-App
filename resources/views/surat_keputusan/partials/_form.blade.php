{{-- resources/views/surat_keputusan/partials/_form.blade.php --}}

@php
/**
 * VARIABEL YANG DIHARAPKAN DARI CONTROLLER:
 * 
 * $keputusan (nullable, model) - Instance model SK untuk edit, null untuk create
 * $pejabat (collection) - Koleksi pejabat: id, nama_lengkap, peran->deskripsi
 * $mode (optional: 'create' | 'edit') - Mode form, default auto-detect
 * $bulanRomawi (optional, array) - Array bulan romawi I-XII
 * $tembusanPresets (optional, array) - Preset tembusan
 * $currentYear, $currentRomawi (optional) - Untuk builder nomor
 */

// ============ SETUP ============
// PERBAIKAN: Set default $keputusan = null untuk mode create
$keputusan = $keputusan ?? null;
$isEdit = isset($keputusan) && $keputusan !== null;
$mode = $mode ?? ($isEdit ? 'edit' : 'create');

// Route & method
$formAction = $isEdit 
    ? route('surat_keputusan.update', $keputusan->id) 
    : route('surat_keputusan.store');
$formMethod = $isEdit ? 'PUT' : 'POST';

// Auto numbering setup
$bulanRomawi = $bulanRomawi ?? ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
$currentRomawi = $currentRomawi ?? $bulanRomawi[date('n')] ?? 'IX';
$currentYear = $currentYear ?? date('Y');

// Helper untuk ambil value (old > model > default)
$getPref = function($key, $default = null) use ($keputusan) {
    if (old($key) !== null) {
        return old($key);
    }
    if ($keputusan && isset($keputusan->{$key})) {
        return $keputusan->{$key};
    }
    return $default;
};

// ============ PREFILL DATA ============
// Menimbang
$prefMenimbang = (array) $getPref('menimbang', []);
$prefMenimbang = $prefMenimbang ?: [''];

// Mengingat
$prefMengingat = (array) $getPref('mengingat', []);
$prefMengingat = $prefMengingat ?: [''];

// Menetapkan (diktum)
$menetapkanData = $getPref('menetapkan', [['judul' => 'KESATU', 'isi' => '']]);
if (is_string($menetapkanData)) {
    $menetapkanData = json_decode($menetapkanData, true) ?: [['judul' => 'KESATU', 'isi' => '']];
}
$prefMenetapkan = $menetapkanData ?: [['judul' => 'KESATU', 'isi' => '']];

// Tembusan
$tembusanVal = $getPref('tembusan', '');
$prefTembusanCsv = is_array($tembusanVal) ? implode(', ', $tembusanVal) : $tembusanVal;

// Tembusan presets
$tembusanPresets = $tembusanPresets ?? [
    'Yth. Rektor',
    'Yth. Wakil Rektor I',
    'Yth. Wakil Rektor II',
    'Dekan Fakultas Ilmu Komputer',
    'BAAK',
    'BAUK',
    'BAK',
    'Kepala Program Studi Sistem Informasi',
    'Unit Kepegawaian',
    'Arsip'
];

// Context untuk tombol (edit mode)
$isSignerCanApprove = $isEdit && $keputusan && auth()->user()->can('approve', $keputusan);
$isPending = $isEdit && $keputusan && $keputusan->status_surat === 'pending';

@endphp

@once
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
  body{background:#f7faff}
  .surat-header{background:#f3f6fa;padding:1.3rem 2.2rem 1.3rem 1.8rem;border-radius:1.1rem;margin-bottom:2.2rem;border:1px solid #e0e6ed;display:flex;align-items:center;gap:1.3rem}
  .surat-header .icon{background:linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);width:54px;height:54px;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 1px 10px #1498ff30;font-size:2rem}
  .surat-header-title{font-weight:bold;color:#0056b3;font-size:1.85rem;margin-bottom:.13rem;letter-spacing:-1px}
  .surat-header-desc{color:#636e7b;font-size:1.03rem}
  .stat-wrapper{display:flex;justify-content:flex-start;gap:1.2rem;margin-bottom:2.1rem;flex-wrap:wrap}
  .stat-card{width:170px;border-radius:.85rem;border:none;background:#fff}
  .stat-card .card-body{text-align:center;padding:1.15rem 1rem}
  .stat-card .icon{font-size:2.3rem;margin-bottom:.5rem}
  .stat-card .label{color:#6c757d;font-size:.83rem;margin-bottom:.25rem;font-weight:600;text-transform:uppercase;letter-spacing:1px}
  .stat-card .value{font-size:2.1rem;font-weight:700;line-height:1.1}
  .card.filter-card{margin-bottom:2.2rem;border-radius:1rem}
  .card.filter-card .card-header{background:#f8fafc;border-radius:1rem 1rem 0 0;border:none}
  .card.filter-card .card-body{padding-bottom:.7rem}
  .card.data-card{border-radius:1rem}
  .card.data-card .card-body{padding-top:1.2rem}
  .table th,.table td{vertical-align:middle!important}
  .table{background:#fff}
  
  /* ✅ Dropdown dengan warna berbeda (FIXED) */
  .dropdown-menu .dropdown-item {
    cursor: pointer;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
  }
  .dropdown-menu .dropdown-item i {
    width: 20px;
    text-align: center;
    margin-right: 8px;
  }
  
  /* ✅ Warna default (sebelum hover) */
  .dropdown-item.text-info { color: #17a2b8 !important; }
  .dropdown-item.text-warning { color: #ffc107 !important; }
  .dropdown-item.text-success { color: #28a745 !important; }
  .dropdown-item.text-danger { color: #dc3545 !important; }
  .dropdown-item.text-primary { color: #007bff !important; }
  .dropdown-item.text-dark { color: #343a40 !important; }
  .dropdown-item.text-secondary { color: #6c757d !important; }
  
  /* ✅ Hover effect - background berwarna, text putih */
  .dropdown-item.text-info:hover { 
    background-color: #17a2b8 !important; 
    color: white !important; 
  }
  .dropdown-item.text-warning:hover { 
    background-color: #ffc107 !important; 
    color: #212529 !important;
  }
  .dropdown-item.text-success:hover { 
    background-color: #28a745 !important; 
    color: white !important; 
  }
  .dropdown-item.text-danger:hover { 
    background-color: #dc3545 !important; 
    color: white !important; 
  }
  .dropdown-item.text-primary:hover { 
    background-color: #007bff !important; 
    color: white !important; 
  }
  .dropdown-item.text-dark:hover { 
    background-color: #343a40 !important; 
    color: white !important; 
  }
  .dropdown-item.text-secondary:hover { 
    background-color: #6c757d !important; 
    color: white !important; 
  }
  
  .dropdown-item.text-warning:hover i { color: #212529 !important; }
  .dropdown-item:hover i { color: inherit !important; }
  
  .badge-pill{
    padding:0.45rem 0.85rem;
    font-size:0.85rem;
    font-weight:600;
    letter-spacing:0.3px;
  }
  
  @media (max-width:767.98px){
    .surat-header{flex-direction:column;align-items:flex-start;padding:1.2rem 1rem;gap:.7rem}
    .stat-wrapper{flex-direction:column;gap:.8rem}
    .stat-card{width:100%}
    .surat-header-title{font-size:1.18rem}
    .surat-header-desc{font-size:.99rem}
    .card.filter-card,.card.data-card{border-radius:.6rem}
  }
</style>
@endpush
@endonce


{{-- ============ FORM START ============ --}}
@if($errors->any())
<div class="alert alert-danger alert-dismissible shadow-sm">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5 class="mb-1"><i class="icon fas fa-ban"></i> Gagal {{ $isEdit ? 'Memperbarui' : 'Menyimpan' }}!</h5>
    <small>Mohon periksa kembali isian Anda</small>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="skForm" action="{{ $formAction }}" method="POST" autocomplete="off">
    @csrf
    @if($isEdit) @method($formMethod) @endif

    <div class="row">
        {{-- ============ KIRI: FORM CONTENT ============ --}}
        <div class="col-lg-8 mb-3">

            {{-- 1) DATA UTAMA --}}
            <section id="section-utama" class="card shadow-sm mb-4">
                <header id="h-utama" class="card-h card-h--purple" data-base="purple">
                    <strong><i class="fas fa-file-alt mr-2"></i>Data Utama</strong>
                </header>
                <div class="card-body">
                    <div class="row">
                        {{-- Nomor SK --}}
                        <div class="col-md-7 mb-3">
                            <label for="nomor" class="form-label font-weight-bold">Nomor SK</label>
                            <div class="input-group">
                                <input type="text" id="nomor" name="nomor" 
                                       class="form-control @error('nomor') is-invalid @enderror"
                                       value="{{ $getPref('nomor', $autoNomor ?? '') }}" 
                                       placeholder="Nomor akan dibuat otomatis"
                                       aria-describedby="nomorHelp">
                                <div class="input-group-append">
                                    <button type="button" id="btn-reserve-nomor" class="btn btn-outline-primary" title="Ambil nomor terbaru dari server">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            @error('nomor')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <div class="d-flex align-items-center mt-2">
                                <div class="custom-control custom-checkbox mr-3">
                                    <input type="checkbox" class="custom-control-input" id="toggleNomorManual">
                                    <label class="custom-control-label small text-muted" for="toggleNomorManual">Mode Manual</label>
                                </div>
                                <a href="#" class="ml-auto small text-primary" id="toggleBuilder">
                                    <i class="fas fa-sliders-h mr-1"></i>Atur Komponen
                                </a>
                            </div>

                            {{-- Nomor Builder --}}
                            <div class="nomor-builder mt-3" aria-live="polite">
                                <div class="d-flex flex-wrap align-items-end gap-2">
                                    <div>
                                        <label class="small mb-1 text-muted" for="nourut">No Urut</label>
                                        <input type="text" id="nourut" class="form-control form-control-sm" value="001" style="width:70px;">
                                    </div>
                                    <span class="text-muted">/</span>
                                    <div>
                                        <label class="small mb-1 text-muted" for="noklasifikasi">Klasifikasi</label>
                                        <input type="text" id="noklasifikasi" class="form-control form-control-sm" 
                                               value="{{ old('kode_klasifikasi', 'B.10.1') }}" style="width:100px;">
                                    </div>
                                    <span class="text-muted">/</span>
                                    <div>
                                        <label class="small mb-1 text-muted" for="nounit">Unit</label>
                                        <input type="text" id="nounit" class="form-control form-control-sm" 
                                               value="{{ old('unit', 'TG') }}" style="width:70px;">
                                    </div>
                                    <span class="text-muted">/UNIKA/</span>
                                    <div>
                                        <label class="small mb-1 text-muted" for="noromawi">Bulan</label>
                                        <input type="text" id="noromawi" class="form-control form-control-sm" 
                                               value="{{ $currentRomawi ?? 'X' }}" style="width:60px;">
                                    </div>
                                    <span class="text-muted">/</span>
                                    <div>
                                        <label class="small mb-1 text-muted" for="notahun">Tahun</label>
                                        <input type="text" id="notahun" class="form-control form-control-sm" 
                                               value="{{ $currentYear ?? date('Y') }}" style="width:80px;">
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0 py-2 px-3">
                                    <small class="mb-0">
                                        <strong>Preview:</strong> <span class="nomor-builder-preview font-weight-bold" id="nomorPreviewText"></span>
                                    </small>
                                </div>
                            </div>

                        {{-- ✅ REVISI: Tanggal Surat --}}
                        <div class="col-md-5 mb-3">
                            <label for="tanggal_surat" class="form-label font-weight-bold">
                                Tanggal Surat <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="tanggal_surat" name="tanggal_surat" 
                                   class="form-control @error('tanggal_surat') is-invalid @enderror" 
                                   required 
                                   value="{{ old('tanggal_surat', $isEdit ? optional($keputusan->tanggal_surat)->format('Y-m-d') : ($tanggalHariIni ?? now()->format('Y-m-d'))) }}">
                            @error('tanggal_surat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Tanggal resmi yang tercetak di surat
                            </small>
                        </div>

                        {{-- ✅ BARU: Kota Penetapan (tepat di bawah tanggal) --}}
                        <div class="col-md-5 mb-3">
                            <label for="kota_penetapan">Kota Penetapan</label>
                            <input type="text" name="kota_penetapan" id="kota_penetapan"
                                   class="form-control @error('kota_penetapan') is-invalid @enderror"
                                   value="{{ $getPref('kota_penetapan', 'Semarang') }}"
                                   placeholder="Semarang"
                                   maxlength="100">
                            @error('kota_penetapan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt text-danger"></i> 
                                Kota tempat surat ditetapkan (contoh: Semarang, Jakarta, Yogyakarta)
                            </small>
                        </div>

                        {{-- Tentang --}}
                        <div class="col-12 mb-3">
                            <label for="tentang" class="form-label font-weight-bold">
                                Tentang <span class="text-danger">*</span>
                            </label>
                            <textarea id="tentang" name="tentang" rows="3" required 
                                      class="form-control @error('tentang') is-invalid @enderror" 
                                      placeholder="Contoh: Penetapan Visi, Misi, dan Tujuan Fakultas Ilmu Komputer">{{ $getPref('tentang') }}</textarea>
                            @error('tentang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ✅ BARU: Judul Penetapan --}}
                        <div class="col-12 mb-3">
                            <div class="form-group mb-0">
                                <label for="judul_penetapan">Judul Penetapan (Bagian "Menetapkan")</label>
                                <input type="text" name="judul_penetapan" id="judul_penetapan"
                                       class="form-control @error('judul_penetapan') is-invalid @enderror"
                                       value="{{ $getPref('judul_penetapan', 'KEPUTUSAN DEKAN TENTANG ' . strtoupper($getPref('tentang', ''))) }}"
                                       placeholder="Contoh: KEPUTUSAN DEKAN TENTANG PENETAPAN VISI DAN MISI FAKULTAS ILMU KOMPUTER"
                                       maxlength="500">
                                @error('judul_penetapan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle text-info"></i> 
                                    Judul lengkap yang akan muncul di bagian <strong>"Menetapkan"</strong> pada surat. 
                                    Biarkan kosong jika tidak diperlukan.
                                </small>
                            </div>
                        </div>

                        {{-- Penandatangan (utama) --}}
                        <div class="col-12 mb-3">
                            <label for="penandatangan" class="form-label font-weight-bold">
                                Penandatangan <span class="badge badge-secondary">opsional</span>
                            </label>
                            <select id="penandatangan" name="penandatangan" class="form-control select2 @error('penandatangan') is-invalid @enderror">
                                <option value="">-- Pilih Penandatangan --</option>
                                @foreach($pejabat ?? [] as $p)
                                    <option value="{{ $p->id }}" 
                                            data-npp="{{ $p->npp ?? '' }}"
                                            {{ old('penandatangan', $getPref('penandatangan')) == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_lengkap }}
                                        @if($p->peran_id == 2) (Dekan) @endif
                                        @if($p->peran_id == 3) (Wakil Dekan) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('penandatangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>Pilih pejabat yang berwenang menandatangani surat
                            </small>
                        </div>

                        {{-- ✅ BARU: NPP Penandatangan (hidden + display) --}}
                        <div class="col-12 mb-3">
                            <input type="hidden" name="npp_penandatangan" id="npp_penandatangan" 
                                   value="{{ $getPref('npp_penandatangan') }}">

                            <div class="form-group" id="npp_display_group" style="display: none;">
                                <label class="text-muted">
                                    <i class="fas fa-id-card"></i> NPP Penandatangan
                                </label>
                                <div class="input-group">
                                    <input type="text" id="npp_display" class="form-control-plaintext text-muted" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="$('#modal_edit_npp').modal('show')">
                                            <i class="fas fa-edit"></i> Ubah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tembusan --}}
                        <div class="col-12">
                            <div class="tembusan-wrap">
                                <div class="tembusan-head">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-copy mr-2"></i>
                                        <strong>Tembusan</strong>
                                        <span class="ml-2 badge badge-light">opsional</span>
                                    </div>
                                    <div class="tembusan-tools">
                                        <button type="button" class="btn btn-sm btn-light" id="btnPasteTembusan" title="Tempel daftar dari clipboard">
                                            <i class="fas fa-clipboard-list"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light" id="btnClearTembusan" title="Kosongkan">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="tembusan-body">
                                    <label for="tembusan-input" class="mb-2 small">
                                        <i class="fas fa-info-circle text-info mr-1"></i>
                                        Ketik + tekan <kbd>Enter</kbd> atau <kbd>,</kbd> untuk membuat tag
                                    </label>
                                    <input id="tembusan-input" name="tembusan" value="{{ $prefTembusanCsv ?? '' }}" 
                                           class="form-control" placeholder="Contoh: Yth. Rektor, BAAK, Arsip">
                                    @error('tembusan')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    <div class="custom-control custom-switch mt-3">
                                        <input type="checkbox" class="custom-control-input" id="tembusanShowTitle" checked>
                                        <label class="custom-control-label small" for="tembusanShowTitle">
                                            Cetak judul <em>"Tembusan Yth"</em>
                                        </label>
                                    </div>

                                    <div class="tembusan-preview mt-3" id="tembusanPreview" aria-live="polite">
                                        <h6 class="mb-2"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>
                                        <div class="text-muted small">Belum ada tembusan. Tambahkan minimal satu.</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="tembusan_formatted" id="tembusanformatted">
                        </div>

                    </div>
                </div>
            </section>


            {{-- 2) MENIMBANG --}}
            <section id="section-menimbang" class="card shadow-sm mb-4">
                <header id="h-menimbang" class="card-h card-h--teal" data-base="teal">
                    <strong><i class="fas fa-balance-scale mr-2"></i>Menimbang</strong>
                </header>
                <div class="card-body">
                    <div id="menimbang-list" class="dynamic-list">
                        @foreach($prefMenimbang as $val)
                        <div class="input-group mb-2 dynamic-item menimbang-item">
                            <span class="input-group-text dynamic-label"></span>
                            <input type="text" name="menimbang[]" class="form-control" value="{{ $val }}" placeholder="Tulis poin pertimbangan...">
                            <button class="btn btn-outline-danger remove-row" type="button" title="Hapus">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @error('menimbang')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-menimbang">
                        <i class="fas fa-plus mr-1"></i>Tambah Butir
                    </button>
                </div>
            </section>

            {{-- 3) MENGINGAT --}}
            <section id="section-mengingat" class="card shadow-sm mb-4">
                <header id="h-mengingat" class="card-h card-h--blue" data-base="blue">
                    <strong><i class="fas fa-book-open mr-2"></i>Mengingat</strong>
                </header>
                <div class="card-body">
                    <div id="mengingat-list" class="dynamic-list">
                        @foreach($prefMengingat as $val)
                        <div class="input-group mb-2 dynamic-item mengingat-item">
                            <span class="input-group-text dynamic-label"></span>
                            <input type="text" name="mengingat[]" class="form-control" value="{{ $val }}" placeholder="Tulis dasar hukum...">
                            <button class="btn btn-outline-danger remove-row" type="button" title="Hapus">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @error('mengingat')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-mengingat">
                        <i class="fas fa-plus mr-1"></i>Tambah Butir
                    </button>
                </div>
            </section>

            {{-- 4) MENETAPKAN DIKTUM --}}
            <section id="section-menetapkan" class="card shadow-sm mb-4">
                <header id="h-menetapkan" class="card-h card-h--amber" data-base="amber">
                    <strong><i class="fas fa-gavel mr-2"></i>Menetapkan (Diktum)</strong>
                </header>
                <div class="card-body">
                    <div id="menetapkan-list">
                        @php
                            $diktumLabels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
                        @endphp

                        @foreach($prefMenetapkan as $i => $mt)
                        <div class="diktum-item p-3 mb-3">
                            <div class="row g-2">
                                <div class="col-md-3 col-lg-2">
                                    <label class="form-label small">Judul</label>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="menetapkan[{{ $i }}][judul]" 
                                           value="{{ $mt['judul'] ?? $diktumLabels[$i] ?? 'KETENTUAN' }}" 
                                           readonly>
                                </div>
                                <div class="col">
                                    <label class="form-label small">Isi Keputusan</label>
                                    <textarea class="form-control wysiwyg" name="menetapkan[{{ $i }}][isi]" rows="4">{!! $mt['isi'] ?? '' !!}</textarea>
                                </div>
                                <div class="col-auto d-flex align-items-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-menetapkan" title="Hapus diktum">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('menetapkan')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-menetapkan">
                        <i class="fas fa-plus mr-1"></i>Tambah Diktum
                    </button>
                </div>
            </section>
        </div>

        {{-- ============ KANAN: QUICK NAV & AKSI ============ --}}
        <aside class="col-lg-4">
            {{-- QuickNav --}}
            <div class="card card-settings sticky-top mb-3" style="top:20px;">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 font-weight-bold text-purple">
                        <i class="fas fa-list-ul mr-2"></i>Navigasi Cepat
                    </h5>
                    <span class="badge badge-light border">Form</span>
                </div>
                <div class="card-body py-2">
                    <div id="quicknav" class="list-group list-quicknav">
                        <a href="#section-utama" class="list-group-item list-group-item-action active">
                            <i class="far fa-id-card"></i>Data Utama
                        </a>
                        <a href="#section-menimbang" class="list-group-item list-group-item-action">
                            <i class="fas fa-balance-scale"></i>Menimbang
                            <span class="badge badge-secondary ml-auto" id="badge-menimbang">0</span>
                        </a>
                        <a href="#section-mengingat" class="list-group-item list-group-item-action">
                            <i class="fas fa-book"></i>Mengingat
                            <span class="badge badge-secondary ml-auto" id="badge-mengingat">0</span>
                        </a>
                        <a href="#section-menetapkan" class="list-group-item list-group-item-action">
                            <i class="fas fa-gavel"></i>Menetapkan
                            <span class="badge badge-secondary ml-auto" id="badge-menetapkan">0</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Aksi Simpan --}}
            <div class="card card-settings sticky-top" style="top:320px;">
                <div class="card-header">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="fas fa-save mr-2 text-primary"></i>Aksi Simpan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="penandatangan_sidebar" class="form-label font-weight-bold">Penandatangan</label>
                        {{-- NOTE: Sidebar hanya tampilan helper, field utama ada di Data Utama --}}
                        <select id="penandatangan_sidebar" class="form-control">
                            <option value="">-- Pilih Pejabat --</option>
                            @foreach($pejabat ?? collect() as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->nama_lengkap }} ({{ $p->peran->deskripsi ?? 'Pejabat' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            Pilihan ini akan mengikuti field Penandatangan di bagian Data Utama.
                        </small>
                    </div>

                    <hr>

                    <p class="text-muted small mb-3">
                        Pilih tombol di bawah untuk menyimpan.<br>
                        Shortcut: <code>Ctrl+S</code> = Draft, <code>Ctrl+Enter</code> = Submit
                    </p>

                    <div class="d-grid gap-2">
                        @if($isSignerCanApprove && $isPending)
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-save mr-1"></i>Simpan Perubahan
                            </button>
                            <button type="submit" name="mode" value="revisi_dan_setujui" class="btn btn-success btn-block">
                                <i class="fas fa-check mr-1"></i>Simpan & Lanjut Setujui
                            </button>
                        @else
                            <button id="btn-submit-approve" type="submit" name="mode" value="pending" class="btn btn-success btn-block">
                                <i class="fas fa-paper-plane mr-1"></i>{{ $isEdit ? 'Simpan & Ajukan' : 'Submit ke Penandatangan' }}
                            </button>
                            <button id="btn-submit-draft" type="submit" name="mode" value="draft" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-save mr-1"></i>Simpan Draft
                            </button>
                        @endif
                        <a href="{{ route('surat_keputusan.index') }}" class="btn btn-dark btn-block">
                            <i class="fas fa-times mr-1"></i>Batal
                        </a>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- Mobile sticky action bar --}}
    <div class="action-bar d-lg-none">
        <div class="d-flex align-items-center">
            @if($isSignerCanApprove && $isPending)
                <button type="submit" class="btn btn-warning btn-block">
                    <i class="fas fa-save mr-1"></i>Simpan Perubahan
                </button>
                <button type="submit" name="mode" value="revisi_dan_setujui" class="btn btn-success btn-block ml-2">
                    <i class="fas fa-check mr-1"></i>Simpan & Setujui
                </button>
            @else
                <button id="mb-approve" type="submit" name="mode" value="pending" class="btn btn-success btn-block mr-2">
                    <i class="fas fa-paper-plane mr-1"></i>{{ $isEdit ? 'Simpan & Kirim' : 'Kirim' }}
                </button>
                <button id="mb-draft" type="submit" name="mode" value="draft" class="btn btn-ghost btn-block">
                    <i class="fas fa-save mr-1"></i>Draft
                </button>
            @endif
        </div>
    </div>
</form>

{{-- ✅ BARU: Modal Edit NPP Manual --}}
<div class="modal fade" id="modal_edit_npp" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit NPP Penandatangan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label for="npp_manual_input">NPP / NIP</label>
                    <input type="text" id="npp_manual_input" class="form-control" 
                           placeholder="Contoh: 058.1.2002.255" maxlength="50">
                    <small class="text-muted">
                        Kosongkan untuk menggunakan NPP dari data user
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveNppManual()">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============ SCRIPTS ============ --}}
@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>
(function() {
    'use strict';
    
    const IS_EDIT = @json($isEdit);
    const skForm = document.getElementById('skForm');
    if (!skForm) return;    

    const $ = window.jQuery;
    const qs = (sel, root = document) => root.querySelector(sel);
    const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const on = (el, ev, fn) => el && el.addEventListener(ev, fn);

    // ============ Select2 (penandatangan + klasifikasi) ============
    $(document).ready(function() {
        $('#penandatangan').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Pejabat Penandatangan',
            width: '100%'
        });

        // ============================================
        // ✅ BARU: Auto-fill NPP saat pilih penandatangan
        // ============================================
        $('#penandatangan').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const npp = selectedOption.data('npp') || '';
            
            // Set hidden field
            $('#npp_penandatangan').val(npp);
            
            // Update display
            if (npp) {
                $('#npp_display').val('NPP. ' + npp);
                $('#npp_display_group').slideDown(200);
            } else {
                $('#npp_display').val('');
                $('#npp_display_group').slideUp(200);
            }
            
            // Update modal input
            $('#npp_manual_input').val(npp);
        });
        
        // Trigger change on page load (untuk edit mode)
        @if($isEdit && $keputusan && $keputusan->penandatangan)
            $('#penandatangan').trigger('change');
        @endif
        
        // ============================================
        // ✅ BARU: Auto-generate judul penetapan dari tentang
        // ============================================
        $('#tentang').on('input', function() {
            const tentang = $(this).val().trim();
            const currentJudul = $('#judul_penetapan').val();
            
            if (!currentJudul || currentJudul.startsWith('KEPUTUSAN DEKAN TENTANG')) {
                if (tentang) {
                    $('#judul_penetapan').val('KEPUTUSAN DEKAN TENTANG ' + tentang.toUpperCase());
                } else {
                    $('#judul_penetapan').val('');
                }
            }
        });

        // Sinkronisasi dropdown sidebar (helper) dengan field utama
        $('#penandatangan_sidebar').on('change', function() {
            const val = $(this).val();
            $('#penandatangan').val(val).trigger('change');
        });
        const currentSigner = $('#penandatangan').val();
        if (currentSigner) {
            $('#penandatangan_sidebar').val(currentSigner);
        }
    });

    // ============ Tagify (Tembusan) + Preview ============
    const TEMBUSAN_PRESETS = @json($tembusanPresets);
    const tembusanInput = qs('#tembusan-input');
    const preview = qs('#tembusanPreview');
    const showTitle = qs('#tembusanShowTitle');
    let tagify = null;

    function escHtml(s) {
        return String(s).replace(/[&<>"'`=\/]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','=':'&#x3D;','`':'&#x60;'}[c]));
    }

    if (tembusanInput) {
        tagify = new Tagify(tembusanInput, {
            enforceWhitelist: false,
            whitelist: TEMBUSAN_PRESETS,
            trim: true,
            duplicates: false,
            delimiters: ',',
            editTags: 1,
            dropdown: { enabled: 1, maxItems: 20, fuzzySearch: true, highlightFirst: true, placeAbove: false },
            placeholder: 'Contoh: Yth. Rektor, BAAK, Arsip',
            transformTag(t) {
                let v = t.value.trim();
                if (!v) return v;
                v = v.toLowerCase().replace(/\b\w/g, m => m.toUpperCase());
                const needsYth = /Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris/i.test(v) && !/Yth\./i.test(v);
                if (needsYth) v = 'Yth. ' + v;
                t.value = v;
            }
        });

        function renderTembusanPreview() {
            const data = tagify.value.map(t => t.value.trim()).filter(Boolean);
            if (!data.length) {
                preview.innerHTML = `<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>`;
                $('#tembusanformatted').val('');
                return;
            }
            const showTitleChecked = qs('#tembusanShowTitle').checked;
            const titleHtml = showTitleChecked ? `<div class="mb-2 font-weight-bold">Tembusan Yth</div>` : '';
            const listHtml = `<ol class="mb-0">${data.map(v => `<li>${escHtml(v)}</li>`).join('')}</ol>`;
            preview.innerHTML = `<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`;
            const plain = (showTitleChecked ? 'Tembusan Yth\n' : '') + data.map((v,i) => `${i+1}. ${v}`).join('\n');
            $('#tembusanformatted').val(plain);
        }

        tagify.on('add', renderTembusanPreview)
              .on('remove', renderTembusanPreview)
              .on('edit:updated', renderTembusanPreview);
        
        if (showTitle) showTitle.addEventListener('change', renderTembusanPreview);

        qs('#btnPasteTembusan') && on(qs('#btnPasteTembusan'), 'click', async function() {
            try {
                const txt = await navigator.clipboard.readText();
                if (!txt) return;
                const items = txt.split(',').map(s => s.trim()).filter(Boolean);
                const existing = new Set(tagify.value.map(t => t.value.toLowerCase()));
                tagify.addTags(items.filter(s => !existing.has(s.toLowerCase())).map(s => ({value: s})));
            } catch(e) {
                Swal.fire('Tidak bisa mengakses clipboard', 'Izinkan akses atau tempel manual.', 'info');
            }
        });

        qs('#btnClearTembusan') && on(qs('#btnClearTembusan'), 'click', () => tagify.removeAllTags());
        setTimeout(renderTembusanPreview, 0);
    }

    // ============ CKEditor ============
    window.editors = {};
    function initEditor(textarea) {
        if (!textarea || !window.ClassicEditor) return;
        ClassicEditor.create(textarea, {
            toolbar: { items: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'] },
            placeholder: 'Ketik isi keputusan di sini...'
        })
        .then(instance => {
            window.editors[textarea.name] = instance;
            instance.model.document.on('change:data', updateUI);
        })
        .catch(err => console.error('Gagal CKEditor:', err));
    }
    qsa('textarea.wysiwyg').forEach(initEditor);

    // ============ Nomor Builder ============
    const nomorField = qs('#nomor');
    const toggleManual = qs('#toggleNomorManual');
    const builderBox = qs('.nomor-builder');
    const builderInputs = ['nourut', 'noklasifikasi', 'nounit', 'noromawi', 'notahun'];

    function buildNomorString() {
        const urut = String(qs('#nourut')?.value || '1').padStart(3, '0');
        const klas = (qs('#noklasifikasi')?.value || 'SK').trim();
        const unit = (qs('#nounit')?.value || 'UNIKA').trim();
        const roma = (qs('#noromawi')?.value || 'I').trim().toUpperCase();
        const thn = (qs('#notahun')?.value || new Date().getFullYear()).toString().trim();
        return `${urut}/${klas}/${unit}/UNIKA/${roma}/${thn}`;
    }

    function updateNomorField() {
        const v = buildNomorString();
        if (nomorField) nomorField.value = v;
        const p = qs('#nomorPreviewText');
        if (p) p.textContent = v;
    }

    function setNomorMode(isManual) {
        if (nomorField) nomorField.readOnly = !isManual;
        builderInputs.forEach(id => {
            const el = qs(`#${id}`);
            if (el) el.disabled = isManual;
        });
        if (!isManual) updateNomorField();
    }

    if (toggleManual) {
        toggleManual.checked = !!IS_EDIT;
        setNomorMode(!!IS_EDIT);
        on(toggleManual, 'change', () => setNomorMode(toggleManual.checked));
    }

    builderInputs.forEach(id => on(qs(`#${id}`), 'input', () => {
        if (!toggleManual?.checked) updateNomorField();
    }));

    on(qs('#toggleBuilder'), 'click', e => {
        e.preventDefault();
        builderBox?.classList.toggle('show');
    });

    on(qs('#btn-reserve-nomor'), 'click', async function() {
        if (toggleManual && toggleManual.checked) {
            Swal.fire({ icon: 'info', title: 'Mode Manual', text: 'Matikan Mode Manual untuk mengambil nomor otomatis.' });
            return;
        }
        try {
            const payload = {
                unit: qs('#nounit')?.value,
                kode_klasifikasi: qs('#noklasifikasi')?.value,
                bulan_romawi: qs('#noromawi')?.value,
                tahun: parseInt(qs('#notahun')?.value || new Date().getFullYear())
            };
            const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const res = await fetch(@json(route('ajax.nomor.reserve')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(payload)
            });
            
            if (!res.ok) throw new Error(`Gagal menghubungi server (${res.status})`);
            const data = await res.json();
            const urutEl = qs('#nourut');
            if (urutEl) urutEl.value = data.nourut || data.nomor_urut || '001';
            updateNomorField();
            Swal.fire({ icon: 'success', title: 'Nomor Disiapkan', text: `${data.nomor}\nNomor berhasil diambil.` });
        } catch(err) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
        }
    });

    // ============ Dynamic Lists ============
    function reindexList(listId, type) {
        const items = qsa(`#${listId} .dynamic-item`);
        items.forEach((item, i) => {
            const label = item.querySelector('.dynamic-label');
            if (label) label.textContent = type === 'alpha' ? String.fromCharCode(97 + i) + '.' : (i + 1) + '.';
            const del = item.querySelector('.remove-row, .btn-remove-menetapkan');
            if (del) del.style.display = item.parentElement.children.length > 1 ? '' : 'none';
        });
        if (listId === 'menimbang-list') $('#badge-menimbang').text(items.length);
        if (listId === 'mengingat-list') $('#badge-mengingat').text(items.length);
    }

    function reindexDiktum() {
        const LABELS = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
        $('#menetapkan-list .diktum-item').each(function(i) {
            $(this).find('input[name*="[judul]"]').val(LABELS[i] || `KETENTUAN ${i+1}`);
        });
        $('#badge-menetapkan').text($('#menetapkan-list .diktum-item').length);
    }

    document.addEventListener('click', async function(e) {
        let acted = false;

        if (e.target.closest('#add-menimbang')) {
            $('#menimbang-list').append(`
                <div class="input-group mb-2 dynamic-item menimbang-item">
                    <span class="input-group-text dynamic-label"></span>
                    <input type="text" name="menimbang[]" class="form-control" placeholder="Tulis poin pertimbangan...">
                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                </div>
            `);
            reindexList('menimbang-list', 'alpha');
            acted = true;
        } else if (e.target.closest('#add-mengingat')) {
            $('#mengingat-list').append(`
                <div class="input-group mb-2 dynamic-item mengingat-item">
                    <span class="input-group-text dynamic-label"></span>
                    <input type="text" name="mengingat[]" class="form-control" placeholder="Tulis dasar hukum...">
                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                </div>
            `);
            reindexList('mengingat-list', 'numeric');
            acted = true;
        } else if (e.target.closest('#add-menetapkan')) {
            const i = $('#menetapkan-list .diktum-item').length;
            $('#menetapkan-list').append(`
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
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-menetapkan" title="Hapus diktum">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            const ta = $('#menetapkan-list .diktum-item:last textarea.wysiwyg').get(0);
            initEditor(ta);
            reindexDiktum();
            acted = true;
        } else if (e.target.closest('.remove-row')) {
            const item = e.target.closest('.dynamic-item');
            if (item && item.parentElement.children.length > 1) {
                const parentId = item.parentElement.id;
                item.remove();
                if (parentId === 'menimbang-list') reindexList('menimbang-list', 'alpha');
                if (parentId === 'mengingat-list') reindexList('mengingat-list', 'numeric');
                acted = true;
            }
        } else if (e.target.closest('.btn-remove-menetapkan')) {
            const d = e.target.closest('.diktum-item');
            if (d && d.parentElement.children.length > 1) {
                const ta = d.querySelector('textarea.wysiwyg');
                if (ta?.name && window.editors[ta.name]) {
                    await window.editors[ta.name].destroy().catch(() => {});
                    delete window.editors[ta.name];
                }
                d.remove();
                reindexDiktum();
                acted = true;
            }
        }

        if (acted) setTimeout(updateUI, 60);
    });

    reindexList('menimbang-list', 'alpha');
    reindexList('mengingat-list', 'numeric');
    reindexDiktum();

    // ============ UI Status + QuickNav Active ============
    function updateUI() {
        const setStatus = (id, st) => {
            const h = qs(`#h-${id}`), nav = qs(`#quicknav a[href="#section-${id}"]`);
            if (h) {
                h.className = 'card-h';
                const base = h.getAttribute('data-base') || 'purple';
                h.classList.add(st === 'complete' ? 'card-h--green' : (st === 'error' ? 'card-h--red' : `card-h--${base}`));
            }
            if (nav) {
                nav.classList.remove('has-error', 'is-complete');
                if (st === 'complete') nav.classList.add('is-complete');
                else if (st === 'error') nav.classList.add('has-error');
            }
        };

        const hasErr = id => !!(qs(`#section-${id} .is-invalid`) || qs(`#section-${id}[aria-invalid="true"]`));
        const filled = v => !!String(v).trim().length;
        const plain = html => { const d = document.createElement('div'); d.innerHTML = html; return d.textContent.trim(); };

        const tanggalEl = qs('[name="tanggal_surat"]');
        const tentangEl = qs('[name="tentang"]');
        setStatus('utama', hasErr('utama') ? 'error' : (filled(tanggalEl?.value) && filled(tentangEl?.value) ? 'complete' : 'base'));

        const anyMenimbang = qsa('[name="menimbang[]"]').some(i => filled(i.value));
        setStatus('menimbang', hasErr('menimbang') ? 'error' : (anyMenimbang ? 'complete' : 'base'));

        const anyMengingat = qsa('[name="mengingat[]"]').some(i => filled(i.value));
        setStatus('mengingat', hasErr('mengingat') ? 'error' : (anyMengingat ? 'complete' : 'base'));

        const anyDiktum = Object.values(window.editors).some(ed => filled(plain(ed.getData())));
        setStatus('menetapkan', hasErr('menetapkan') ? 'error' : (anyDiktum ? 'complete' : 'base'));

        $('#badge-menimbang').text($('#menimbang-list .menimbang-item').length);
        $('#badge-mengingat').text($('#mengingat-list .mengingat-item').length);
        $('#badge-menetapkan').text($('#menetapkan-list .diktum-item').length);
    }

    const navLinks = qsa('#quicknav a');
    const sections = navLinks.map(a => qs(a.getAttribute('href'))).filter(Boolean);
    if (sections.length) {
        function onScroll() {
            const y = window.scrollY + 120;
            let cur = sections[0];
            for (const s of sections) if (s.offsetTop <= y) cur = s;
            navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + cur.id));
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    skForm.addEventListener('input', updateUI, true);
    skForm.addEventListener('change', updateUI, true);
    updateUI();

    // ============ Submit Guard + Shortcuts ============
    function validateAndSubmit(e, mode) {
        if (mode === 'pending') {
            const signer = skForm.querySelector('select[name="penandatangan"]');
            if (!signer || !signer.value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Penandatangan Belum Dipilih', text: 'Silakan pilih penandatangan terlebih dahulu untuk mengajukan.' });
                if (signer) $('#penandatangan').select2('open');
            }
        }
    }

    on(qs('#btn-submit-approve'), 'click', e => validateAndSubmit(e, 'pending'));
    on(qs('#mb-approve'), 'click', e => validateAndSubmit(e, 'pending'));
    on(qs('#btn-submit-draft'), 'click', e => validateAndSubmit(e, 'draft'));
    on(qs('#mb-draft'), 'click', e => validateAndSubmit(e, 'draft'));

    let isDirty = false;
    skForm.addEventListener('input', () => isDirty = true);
    window.addEventListener('beforeunload', e => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = 'Perubahan belum disimpan. Yakin keluar?';
        }
    });
    skForm.addEventListener('submit', () => isDirty = false);

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === 's' || e.key === 'S') {
                e.preventDefault();
                qs('#btn-submit-draft')?.click();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                qs('#btn-submit-approve')?.click();
            }
        }
    });

})();

// ============================================
// ✅ BARU: Save NPP manual override (global)
// ============================================
function saveNppManual() {
    const nppManual = (window.jQuery ? jQuery('#npp_manual_input').val().trim() : '').trim();
    const $ = window.jQuery;

    if (!$) return;

    // Set ke hidden field
    $('#npp_penandatangan').val(nppManual);
    
    // Update display
    if (nppManual) {
        $('#npp_display').val('NPP. ' + nppManual);
        $('#npp_display_group').slideDown(200);
    } else {
        const selectedOption = $('#penandatangan').find('option:selected');
        const nppUser = selectedOption.data('npp') || '';
        $('#npp_penandatangan').val(nppUser);
        
        if (nppUser) {
            $('#npp_display').val('NPP. ' + nppUser);
            $('#npp_display_group').slideDown(200);
        } else {
            $('#npp_display_group').slideUp(200);
        }
    }
    
    // Close modal
    $('#modal_edit_npp').modal('hide');
    
    if (window.toastr && toastr.success) {
        toastr.success('NPP berhasil diperbarui');
    }
}
</script>
@endpush
@endonce
