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

// Jika string dan kelihatannya JSON Tagify lama → decode dulu
if (is_string($tembusanVal)) {
    $raw = trim(html_entity_decode($tembusanVal, ENT_QUOTES, 'UTF-8'));

    if ($raw !== '' && str_starts_with($raw, '[{')) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $tembusanVal = collect($decoded)
                ->map(function ($item) {
                    $val = is_array($item)
                        ? ($item['value'] ?? ($item['text'] ?? ($item['name'] ?? reset($item))))
                        : $item;
                    return trim((string) $val);
                })
                ->filter()
                ->unique()
                ->values()
                ->all();
        }
    } else {
        // Format baru: newline → ubah jadi array
        $parts = preg_split('/[\r\n]+/', $raw);
        $tembusanVal = array_values(array_filter(array_map('trim', $parts)));
    }
}

// Terakhir, jadikan CSV untuk value awal Tagify
$prefTembusanCsv = is_array($tembusanVal)
    ? implode(', ', $tembusanVal)
    : (string) $tembusanVal;


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

@include('surat_keputusan.partials._form_styles')

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
            {{-- BARIS 1: Nomor SK (7 kolom) + Tanggal Surat (5 kolom) --}}
            <div class="col-md-7 mb-3">
                <label for="nomor" class="form-label font-weight-bold">Nomor SK</label>
                <div class="input-group">
                    <input type="text" id="nomor" name="nomor" 
                           class="form-control @error('nomor') is-invalid @enderror"
                           value="{{ $getPref('nomor', $autoNomor ?? '') }}" 
                           placeholder="Nomor akan dibuat otomatis">
                    <div class="input-group-append">
                        <button type="button" id="btn-reserve-nomor" class="btn btn-outline-primary">
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
                <div class="nomor-builder mt-3">
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
            </div>

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

            {{-- BARIS 2: Kota Penetapan (6 kolom) + Penandatangan (6 kolom) --}}
            <div class="col-md-6 mb-3">
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


            {{-- BARIS 3: Tentang (Full Width) --}}
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

            {{-- BARIS 4: Judul Penetapan (Full Width) --}}
            <div class="col-12 mb-3">
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

            {{-- BARIS 5: NPP Penandatangan (Hidden + Display, jika ada) --}}
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

            {{-- BARIS 6: Tembusan (Full Width) --}}
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

                        <div class="tembusan-preview mt-3" id="tembusanPreview">
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Pertimbangan yang mendasari keputusan
                        </small>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalMenimbangLibrary">
                            <i class="fas fa-book mr-1"></i>Pilih dari Library
                        </button>
                    </div>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Dasar hukum yang menjadi rujukan
                        </small>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalMengingatLibrary">
                            <i class="fas fa-book mr-1"></i>Pilih dari Library
                        </button>
                    </div>
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
                        <label for="penandatangan" class="form-label font-weight-bold">
                            Penandatangan <span class="badge badge-secondary">opsional</span>
                        </label>
                        <select id="penandatangan" name="penandatangan" 
                                class="form-control @error('penandatangan') is-invalid @enderror">
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
                            <i class="fas fa-user-tie mr-1"></i>Pejabat yang menandatangani
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

{{-- ✅ Modal Library Menimbang --}}
<div class="modal fade" id="modalMenimbangLibrary" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-book"></i> Pilih dari Library Menimbang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="search-menimbang" class="form-control" placeholder="Cari konten menimbang...">
                </div>
                <div id="menimbang-library-list" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat library...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ✅ Modal Library Mengingat --}}
<div class="modal fade" id="modalMengingatLibrary" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-book"></i> Pilih dari Library Mengingat
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="search-mengingat" class="form-control" placeholder="Cari dasar hukum...">
                </div>
                <div id="mengingat-library-list" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat library...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ SCRIPTS ============ --}}
@include('surat_keputusan.partials._form_scripts')

