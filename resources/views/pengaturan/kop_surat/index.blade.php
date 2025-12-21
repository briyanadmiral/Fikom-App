@extends('layouts.app')

@section('title','Pengaturan Kop Surat')

@push('styles')
<style>
    .gradient-blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-modern {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .card-modern:hover {
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.25);
    }
    .card-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 20px 25px;
        font-weight: 600;
        font-size: 18px;
    }
    .radio-card {
        border: 2px solid #e3e8ef;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    .radio-card:hover {
        border-color: #667eea;
        background: #f8f9ff;
        transform: scale(1.02);
    }
    .radio-card.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
    }
    .radio-card input[type="radio"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    .radio-card-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .form-control:focus, .custom-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    .btn-gradient:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        color: white;
    }
    .section-box {
        border: 2px solid #e3e8ef;
        border-radius: 15px;
        padding: 25px;
        background: white;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .section-box.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
    }
    .badge-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .align-selector {
        display: inline-flex;
        border: 2px solid #667eea;
        border-radius: 10px;
        overflow: hidden;
    }
    .align-selector input[type="radio"] { display: none; }
    .align-selector label {
        padding: 10px 20px;
        cursor: pointer;
        background: white;
        color: #667eea;
        transition: all 0.3s ease;
        margin: 0;
        border-right: 1px solid #e3e8ef;
    }
    .align-selector label:last-child { border-right: none; }
    .align-selector input[type="radio"]:checked + label {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }

    /* Custom Range Slider Styling */
    .custom-range {
        width: 100%;
        height: 8px;
        background: #e3e8ef;
        outline: none;
        border-radius: 10px;
        -webkit-appearance: none;
        appearance: none;
    }
    .custom-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    .custom-range::-webkit-slider-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.6);
    }
    .custom-range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        cursor: pointer;
        border-radius: 50%;
        border: none;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    .custom-range::-moz-range-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.6);
    }
    .custom-range:focus { outline: none; }
    .custom-range:focus::-webkit-slider-thumb {
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
    }

    /* A4 PREVIEW STYLING */
    .preview-wrapper {
        background: #535353;
        padding: 20px;
        border-radius: 15px;
        overflow-y: auto;
        max-height: 90vh;
    }
    .a4-preview {
        width: 21cm;
        height: 29.7cm;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
        position: relative;
        overflow: hidden;
        transform-origin: top center;
    }

    .preview-controls {
        text-align: center;
        margin-bottom: 15px;
    }
    .preview-controls button {
        background: white;
        border: 2px solid #667eea;
        color: #667eea;
        padding: 8px 15px;
        border-radius: 8px;
        margin: 0 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .preview-controls button:hover {
        background: #667eea;
        color: white;
    }
    .preview-controls button.active {
        background: #667eea;
        color: white;
    }

    /* Sticky hanya di layar LG ke atas */
    @media (min-width: 992px) {
        .kop-preview-sticky {
            position: sticky;
            top: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- SweetAlert2 untuk Success Message --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            });
        </script>
    @endif

    {{-- SweetAlert2 untuk Error Messages --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan!',
                    html: '<ul style="text-align:left; padding-left:20px; margin:0;">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#667eea',
                    customClass: {
                        confirmButton: 'btn btn-gradient'
                    },
                    buttonsStyling: false
                });
            });
        </script>
    @endif

    {{-- ROW UTAMA: FORM (KIRI) + PREVIEW (KANAN) --}}
    <div class="row">
        {{-- LEFT: FORM --}}
        <div class="col-12 col-lg-5 mb-4 mb-lg-0">
            <div class="card card-modern">
                <div class="card-header-modern">
                    <i class="fas fa-cog mr-2"></i>Pengaturan Kop Surat
                </div>

                <form
                    id="formKopSurat"
                    action="{{ route('kop.update') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf
                    @method('PUT')

                    {{-- IMPORTANT: semua isi form dibungkus dalam card-body agar padding konsisten --}}
                    <div class="card-body">

                        {{-- Quick Actions: Preset & Export/Import --}}
                        <div class="form-group mb-4 p-3" style="background: #f0f4ff; border-radius: 10px; border: 1px solid #e3e8ef;">
                            <div class="row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <label class="font-weight-bold" style="color: #2d3748;">
                                        <i class="fas fa-palette mr-2"></i>Template Preset
                                    </label>
                                    <select id="presetSelector" class="form-control" style="border-radius: 10px;">
                                        <option value="">-- Pilih Preset --</option>
                                        @foreach($presets ?? [] as $key => $preset)
                                            <option value="{{ $key }}">{{ $preset['name'] }} - {{ $preset['description'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold" style="color: #2d3748;">
                                        <i class="fas fa-exchange-alt mr-2"></i>Backup / Restore
                                    </label>
                                    <div class="d-flex">
                                        <a href="{{ route('kop.export') }}" class="btn btn-outline-primary btn-sm flex-fill mr-2" style="border-radius: 8px;">
                                            <i class="fas fa-download mr-1"></i> Export
                                        </a>
                                        <button type="button" id="btnImport" class="btn btn-outline-secondary btn-sm flex-fill" style="border-radius: 8px;">
                                            <i class="fas fa-upload mr-1"></i> Import
                                        </button>
                                        <input type="file" id="importFile" accept=".json" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Nama Kop (Legacy support) --}}
                        <div class="form-group mb-4">
                             <label class="font-weight-bold" style="color: #2d3748;">
                                <i class="fas fa-tag mr-2"></i>Nama Template / Instansi
                            </label>
                            <input type="text" name="nama_kop" class="form-control"
                                   value="{{ old('nama_kop', $kop->nama_kop ?? '') }}"
                                   placeholder="Contoh: Kop Utama Fakultas"
                                   style="border-radius: 10px; padding: 12px;">
                        </div>

                        <h5 class="mb-3" style="color: #667eea; font-weight: 600;">
                            <i class="fas fa-layer-group mr-2"></i>Pilih Mode Kop Surat
                        </h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="radio-card {{ ($kop->mode_type ?? 'custom') === 'custom' ? 'active' : '' }}"
                                     data-mode="custom">
                                    <input type="radio" id="mode_custom" name="mode_type" value="custom"
                                           {{ ($kop->mode_type ?? 'custom') === 'custom' ? 'checked' : '' }}>
                                    <label for="mode_custom" style="cursor: pointer; margin: 0; width: 100%;">
                                        <div class="text-center">
                                            <div class="radio-card-icon">
                                                <i class="fas fa-magic"></i>
                                            </div>
                                            <h6 style="color: #2d3748; font-weight: 600;">Custom Header</h6>
                                            <small class="text-muted" style="font-size: 12px;">
                                                Edit teks + upload background
                                            </small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="radio-card {{ ($kop->mode_type ?? 'custom') === 'upload' ? 'active' : '' }}"
                                     data-mode="upload">
                                    <input type="radio" id="mode_upload" name="mode_type" value="upload"
                                           {{ ($kop->mode_type ?? 'custom') === 'upload' ? 'checked' : '' }}>
                                    <label for="mode_upload" style="cursor: pointer; margin: 0; width: 100%;">
                                        <div class="text-center">
                                            <div class="radio-card-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <h6 style="color: #2d3748; font-weight: 600;">Upload Gambar Full</h6>
                                            <small class="text-muted" style="font-size: 12px;">
                                                Upload 1 gambar sudah jadi
                                            </small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: CUSTOM MODE --}}
                        <div id="section_custom" class="section-box">
                            <h5 class="mb-4" style="color: #667eea; font-weight: 600;">
                                <i class="fas fa-edit mr-2"></i>Custom Header (Teks + Background)
                            </h5>

                            {{-- Text Alignment --}}
                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-align-left mr-2"></i>Posisi Teks
                                </label>
                                <div class="align-selector">
                                    <input type="radio" id="align_left" name="text_align" value="left"
                                           {{ ($kop->text_align ?? 'right') === 'left' ? 'checked' : '' }}>
                                    <label for="align_left">
                                        <i class="fas fa-align-left mr-1"></i> Kiri
                                    </label>

                                    <input type="radio" id="align_center" name="text_align" value="center"
                                           {{ ($kop->text_align ?? 'right') === 'center' ? 'checked' : '' }}>
                                    <label for="align_center">
                                        <i class="fas fa-align-center mr-1"></i> Tengah
                                    </label>

                                    <input type="radio" id="align_right" name="text_align" value="right"
                                           {{ ($kop->text_align ?? 'right') === 'right' ? 'checked' : '' }}>
                                    <label for="align_right">
                                        <i class="fas fa-align-right mr-1"></i> Kanan
                                    </label>
                                </div>
                            </div>

                            {{-- KONTROL STYLING LENGKAP --}}
                            <div class="mt-4 p-3" style="background: #f8f9ff; border-radius: 10px; border: 1px solid #e3e8ef;">
                                <h6 class="mb-3" style="color: #667eea; font-weight: 600;">
                                    <i class="fas fa-sliders-h mr-2"></i>Kontrol Tampilan
                                </h6>

                                {{-- Logo Size --}}
                                <div class="form-group">
                                    <label class="font-weight-bold d-flex justify-content-between" style="color: #2d3748; font-size: 13px;">
                                        <span><i class="fas fa-expand-arrows-alt mr-1"></i>Ukuran Logo</span>
                                        <span class="badge badge-primary" id="logo_size_value">{{ old('logo_size', $kop->logo_size ?? 100) }}%</span>
                                    </label>
                                    <input type="range" name="logo_size" id="logo_size"
                                           class="custom-range range-slider"
                                           min="30" max="300" step="5"
                                           value="{{ old('logo_size', $kop->logo_size ?? 100) }}"
                                           data-target="#logo_size_value">
                                </div>

                                {{-- Font Size Title --}}
                                <div class="form-group">
                                    <label class="font-weight-bold d-flex justify-content-between" style="color: #2d3748; font-size: 13px;">
                                        <span><i class="fas fa-text-height mr-1"></i>Ukuran Font Judul</span>
                                        <span class="badge badge-primary" id="font_title_value">{{ old('font_size_title', $kop->font_size_title ?? 14) }}px</span>
                                    </label>
                                    <input type="range" name="font_size_title" id="font_size_title"
                                           class="custom-range range-slider"
                                           min="10" max="30" step="1"
                                           value="{{ old('font_size_title', $kop->font_size_title ?? 14) }}"
                                           data-target="#font_title_value"
                                           data-unit="px">
                                </div>

                                {{-- Font Size Text --}}
                                <div class="form-group">
                                    <label class="font-weight-bold d-flex justify-content-between" style="color: #2d3748; font-size: 13px;">
                                        <span><i class="fas fa-font mr-1"></i>Ukuran Font Teks</span>
                                        <span class="badge badge-primary" id="font_text_value">{{ old('font_size_text', $kop->font_size_text ?? 10) }}px</span>
                                    </label>
                                    <input type="range" name="font_size_text" id="font_size_text"
                                           class="custom-range range-slider"
                                           min="8" max="20" step="1"
                                           value="{{ old('font_size_text', $kop->font_size_text ?? 10) }}"
                                           data-target="#font_text_value"
                                           data-unit="px">
                                </div>

                                {{-- Text Color --}}
                                <div class="form-group">
                                    <label class="font-weight-bold" style="color: #2d3748; font-size: 13px;">
                                        <i class="fas fa-palette mr-1"></i>Warna Teks
                                    </label>
                                    <div class="input-group">
                                        <input type="color" name="text_color" id="text_color"
                                               class="form-control"
                                               value="{{ old('text_color', $kop->text_color ?? '#000000') }}"
                                               style="height: 42px; cursor: pointer;">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="text_color_hex">{{ old('text_color', $kop->text_color ?? '#000000') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Header Padding --}}
                                <div class="form-group">
                                    <label class="font-weight-bold d-flex justify-content-between" style="color: #2d3748; font-size: 13px;">
                                        <span><i class="fas fa-arrows-alt mr-1"></i>Padding Header</span>
                                        <span class="badge badge-primary" id="padding_value">{{ old('header_padding', $kop->header_padding ?? 15) }}px</span>
                                    </label>
                                    <input type="range" name="header_padding" id="header_padding"
                                           class="custom-range range-slider"
                                           min="0" max="250" step="5"
                                           value="{{ old('header_padding', $kop->header_padding ?? 15) }}"
                                           data-target="#padding_value"
                                           data-unit="px">
                                </div>

                                {{-- Background Opacity --}}
                                <div class="form-group">
                                    <label class="font-weight-bold d-flex justify-content-between" style="color: #2d3748; font-size: 13px;">
                                        <span><i class="fas fa-adjust mr-1"></i>Transparansi Background</span>
                                        <span class="badge badge-primary" id="opacity_value">{{ old('background_opacity', $kop->background_opacity ?? 100) }}%</span>
                                    </label>
                                    <input type="range" name="background_opacity" id="background_opacity"
                                           class="custom-range range-slider"
                                           min="0" max="100" step="10"
                                           value="{{ old('background_opacity', $kop->background_opacity ?? 100) }}"
                                           data-target="#opacity_value">
                                    <div class="d-flex justify-content-between" style="font-size: 10px; color: #999;">
                                        <span>Transparan</span>
                                        <span>Solid</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- TEKS HEADER --}}
                            <div class="form-group mt-4">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-university mr-2"></i>Nama Fakultas/Instansi
                                </label>
                                <input name="nama_fakultas" class="form-control"
                                       value="{{ old('nama_fakultas', $kop->nama_fakultas ?? 'FAKULTAS ILMU KOMPUTER') }}"
                                       placeholder="FAKULTAS ILMU KOMPUTER"
                                       style="border-radius: 10px; padding: 12px;">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Alamat Lengkap
                                </label>
                                <textarea name="alamat_lengkap" class="form-control" rows="2"
                                          placeholder="Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234"
                                          style="border-radius: 10px; padding: 12px;">{{ old('alamat_lengkap', $kop->alamat_lengkap ?? 'Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-phone mr-2"></i>Telepon & Fax
                                </label>
                                <input name="telepon_lengkap" class="form-control"
                                       value="{{ old('telepon_lengkap', $kop->telepon_lengkap ?? 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265') }}"
                                       placeholder="Telp. (024) 8441555, 8505003 (hunting)"
                                       style="border-radius: 10px; padding: 12px;">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-envelope mr-2"></i>Email & Website
                                </label>
                                <input name="email_website" class="form-control"
                                       value="{{ old('email_website', $kop->email_website ?? 'e-mail: unika@unika.ac.id http://www.unika.ac.id/') }}"
                                       placeholder="e-mail: unika@unika.ac.id http://www.unika.ac.id/"
                                       style="border-radius: 10px; padding: 12px;">
                            </div>

                            {{-- LOGO SECTION --}}
                            <div class="mt-4 p-3" style="background: #f0f4ff; border-radius: 10px; border: 1px solid #e3e8ef;">
                                <h6 class="mb-3" style="color: #667eea; font-weight: 600;">
                                    <i class="fas fa-images mr-2"></i>Pengaturan Logo
                                </h6>
                                
                                <div class="row">
                                    {{-- Logo Kanan (Utama) --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold" style="color: #2d3748;">
                                                <i class="fas fa-image mr-2"></i>Logo Kanan
                                            </label>
                                            <div class="custom-control custom-switch mb-2">
                                                <input type="checkbox" class="custom-control-input" id="tampilkan_logo_kanan" name="tampilkan_logo_kanan" value="1"
                                                       {{ old('tampilkan_logo_kanan', $kop->tampilkan_logo_kanan ?? true) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="tampilkan_logo_kanan">Tampilkan</label>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" name="logo_kanan" class="custom-file-input" id="logo_kanan" accept="image/*">
                                                <label class="custom-file-label" for="logo_kanan" style="font-size: 12px;">Pilih file...</label>
                                            </div>

                                            @if($kop?->logo_kanan_path)
                                                <div class="mt-2 text-center position-relative" style="display: inline-block;">
                                                    <img src="{{ asset('storage/'.$kop->logo_kanan_path) }}"
                                                         class="img-thumbnail"
                                                         style="max-height:60px;">
                                                    <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                                            data-type="logo_kanan"
                                                            style="position: absolute; top: -6px; right: -6px; padding: 1px 5px; border-radius: 50%; font-size: 9px;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Logo Kiri --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold" style="color: #2d3748;">
                                                <i class="fas fa-image mr-2"></i>Logo Kiri
                                            </label>
                                            <div class="custom-control custom-switch mb-2">
                                                <input type="checkbox" class="custom-control-input" id="tampilkan_logo_kiri" name="tampilkan_logo_kiri" value="1"
                                                       {{ old('tampilkan_logo_kiri', $kop->tampilkan_logo_kiri ?? false) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="tampilkan_logo_kiri">Tampilkan</label>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" name="logo_kiri" class="custom-file-input" id="logo_kiri" accept="image/*">
                                                <label class="custom-file-label" for="logo_kiri" style="font-size: 12px;">Pilih file...</label>
                                            </div>

                                            @if($kop?->logo_kiri_path)
                                                <div class="mt-2 text-center position-relative" style="display: inline-block;">
                                                    <img src="{{ asset('storage/'.$kop->logo_kiri_path) }}"
                                                         class="img-thumbnail"
                                                         style="max-height:60px;">
                                                    <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                                            data-type="logo_kiri"
                                                            style="position: absolute; top: -6px; right: -6px; padding: 1px 5px; border-radius: 50%; font-size: 9px;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Cap/Stamp --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold" style="color: #2d3748;">
                                                <i class="fas fa-stamp mr-2"></i>Cap/Stamp
                                            </label>
                                            <div class="mt-4 pt-1"></div>
                                            <div class="custom-file">
                                                <input type="file" name="cap" class="custom-file-input" id="cap" accept="image/*">
                                                <label class="custom-file-label" for="cap" style="font-size: 12px;">Pilih file...</label>
                                            </div>

                                            @if($kop?->cap_path)
                                                <div class="mt-2 text-center position-relative" style="display: inline-block;">
                                                    <img src="{{ asset('storage/'.$kop->cap_path) }}"
                                                         class="img-thumbnail"
                                                         style="max-height:60px;">
                                                    <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                                            data-type="cap"
                                                            style="position: absolute; top: -6px; right: -6px; padding: 1px 5px; border-radius: 50%; font-size: 9px;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BACKGROUND IMAGE WATERMARK (Always Visible for Custom Mode) --}}
                        <div class="mt-4 p-3" style="background: #fff4e6; border-radius: 10px; border: 1px solid #ffe0b2;">
                            <h6 class="mb-3" style="color: #f57c00; font-weight: 600;">
                                <i class="fas fa-image mr-2"></i>Background Watermark (Untuk Mode Custom)
                            </h6>
                            
                            <div class="alert alert-warning mb-3" style="border-radius: 8px; font-size: 12px;">
                                <i class="fas fa-lightbulb mr-2"></i>
                                <strong>Tips:</strong> Upload gambar background yang akan ditampilkan di belakang teks kop (sebagai watermark). Atur opacity di "Transparansi Background".
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-file-image mr-2"></i>File Background Image
                               </label>
                                <div class="custom-file">
                                    <input type="file" name="background_custom" class="custom-file-input" id="background_custom" accept="image/*">
                                    <label class="custom-file-label" for="background_custom">Pilih gambar background...</label>
                                </div>

                                @if($kop?->background_path && ($kop->mode_type ?? 'custom') === 'custom')
                                    <div class="mt-3 text-center position-relative" style="display: inline-block;">
                                        <img src="{{ asset('storage/' . $kop->background_path) }}"
                                             alt="Background Preview"
                                             class="img-thumbnail"
                                             style="max-height: 150px;">
                                        <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                                data-type="background"
                                                style="position: absolute; top: -8px; right: -8px; padding: 4px 8px; border-radius: 50%;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- SECTION: UPLOAD FULL MODE --}}
                        <div id="section_upload" class="section-box">
                            <h5 class="mb-4" style="color: #667eea; font-weight: 600;">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>Upload Gambar Full (Sudah Jadi)
                            </h5>

                            <div class="form-group">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-file-image mr-2"></i>File Gambar Kop Lengkap
                                </label>
                                <div class="custom-file">
                                    <input type="file" name="background_upload" class="custom-file-input" id="background_upload" accept="image/*">
                                    <label class="custom-file-label" for="background_upload">Pilih gambar kop yang sudah jadi...</label>
                                </div>

                                <div class="alert alert-info mt-3" style="border-radius: 10px; background: #e8f4fd; border: none;">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Info:</strong> Gambar ini akan ditampilkan langsung tanpa overlay teks.<br>
                                    • Format: JPG/PNG<br>
                                    • Ukuran: A4 (210x297mm atau 2480x3508px @300dpi)
                                </div>

                                @if($kop?->background_path && ($kop->mode_type ?? 'custom') === 'upload')
                                    <div class="mt-3 text-center position-relative" style="display: inline-block;">
                                        <img src="{{ asset('storage/' . $kop->background_path) }}"
                                             alt="Background Preview"
                                             class="img-thumbnail"
                                             style="max-height: 200px;">
                                        <button type="button" class="btn btn-danger btn-sm delete-image-btn"
                                                data-type="background"
                                                style="position: absolute; top: -8px; right: -8px; padding: 4px 8px; border-radius: 50%;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="button" id="btnRefreshPreview" class="btn btn-outline-primary btn-lg mr-2">
                                <i class="fas fa-sync-alt mr-2"></i> Refresh Preview
                            </button>
                            <button type="submit" class="btn btn-gradient btn-lg">
                                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
                            </button>
                        </div>

                    </div> {{-- /card-body --}}
                </form>
            </div>
        </div>

        {{-- RIGHT: PREVIEW --}}
        <div class="col-12 col-lg-7">
            <div class="card card-modern kop-preview-sticky">
                <div class="card-header-modern d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-eye mr-2"></i>Preview Full A4</span>
                    <span id="previewLoader" class="spinner-border spinner-border-sm text-light" style="display:none;" role="status"></span>
                </div>

                <div class="card-body p-0">
                    <div class="preview-wrapper">
                        <div class="preview-controls d-flex justify-content-between align-items-center flex-wrap">
                            <div class="d-flex align-items-center">
                                <button type="button" class="zoom-btn active" data-scale="1">100%</button>
                                <button type="button" class="zoom-btn" data-scale="0.8">80%</button>
                                <button type="button" class="zoom-btn" data-scale="0.6">60%</button>
                                <button type="button" class="zoom-btn" data-scale="0.5">50%</button>
                            </div>
                            <div class="d-flex align-items-center">
                                <select id="paperSizeSelector" class="form-control form-control-sm" style="width: auto; border-radius: 8px; font-size: 12px;">
                                    @foreach($paperSizes ?? [] as $key => $size)
                                        <option value="{{ $key }}" data-width="{{ $size['width'] }}" data-height="{{ $size['height'] }}" {{ $key === 'A4' ? 'selected' : '' }}>
                                            {{ $size['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="a4-preview" id="a4-preview-container">
                            <div class="kop-preview-wrapper">
                                @include('shared._kop_surat', ['context' => 'web', 'showDivider' => true])
                            </div>

                            {{-- Sample content --}}
                            <div class="sample-letter-body" style="padding: 20px; font-size: 11pt; line-height: 1.6;">
                                <p style="text-align: center; margin-bottom: 20px;">
                                    <strong>CONTOH ISI SURAT</strong>
                                </p>
                                <p>Yang bertanda tangan di bawah ini:</p>
                                <p>
                                    Nama: ___________________<br>
                                    Jabatan: ___________________
                                </p>
                                <p>Dengan ini menyatakan bahwa...</p>
                                <p style="margin-top: 100px; text-align: right;">
                                    Hormat kami,<br><br><br><br>
                                    ( ___________________ )
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>
@endsection

@push('scripts')
<script>
(function(){
    function toggleSections() {
        const modeType = document.querySelector('input[name="mode_type"]:checked')?.value || 'custom';
        const sectionCustom = document.getElementById('section_custom');
        const sectionUpload = document.getElementById('section_upload');

        if (!sectionCustom || !sectionUpload) return;

        if (modeType === 'custom') {
            sectionCustom.style.display = 'block';
            sectionCustom.classList.add('active');
            sectionUpload.style.display = 'none';
            sectionUpload.classList.remove('active');
        } else {
            sectionCustom.style.display = 'none';
            sectionCustom.classList.remove('active');
            sectionUpload.style.display = 'block';
            sectionUpload.classList.add('active');
        }
    }

    function updateRadioCards() {
        document.querySelectorAll('.radio-card').forEach(card => card.classList.remove('active'));
        const checkedRadio = document.querySelector('input[name="mode_type"]:checked');
        if (checkedRadio) {
            const parent = checkedRadio.closest('.radio-card');
            if (parent) parent.classList.add('active');
        }
    }

    function updateFileLabels() {
        document.querySelectorAll('.custom-file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Pilih file...';
                const label = e.target.nextElementSibling;
                if (label) label.textContent = fileName;
            });
        });
    }

    function setupRangeSliders() {
        document.querySelectorAll('.range-slider').forEach(slider => {
            const target = document.querySelector(slider.dataset.target);
            const unit = slider.dataset.unit || '%';

            slider.addEventListener('input', function() {
                if (target) target.textContent = this.value + unit;
            });
        });
    }

    const colorInput = document.getElementById('text_color');
    if(colorInput) {
        colorInput.addEventListener('input', function() {
            const hex = document.getElementById('text_color_hex');
            if (hex) hex.textContent = this.value.toUpperCase();
        });
    }

    function setupZoomControls() {
        const zoomButtons = document.querySelectorAll('.zoom-btn');
        const a4Preview = document.getElementById('a4-preview-container');
        if (!a4Preview) return;

        zoomButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const scale = this.dataset.scale;
                a4Preview.style.transform = `scale(${scale})`;

                zoomButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Handle delete image
    function setupDeleteImage() {
        document.querySelectorAll('.delete-image-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const typeName = type === 'logo' ? 'Logo' : (type === 'cap' ? 'Cap' : 'Background');

                Swal.fire({
                    title: `Hapus ${typeName}?`,
                    text: `Gambar ${typeName.toLowerCase()} akan dihapus secara permanen!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger mx-1',
                        cancelButton: 'btn btn-secondary mx-1'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading()
                        });

                        // Route construct for delete-image with placeholder 'REPLACE_ME'
                        // We use a cleaner approach: route usually is /settings/kop/delete-image/{type}
                        // Let's rely on Laravel route helper outputting strict URL
                        const baseUrl = "{{ route('kop.delete-image', ['type' => 'REPLACE_ME']) }}";
                        const deleteUrl = baseUrl.replace('REPLACE_ME', type);

                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Gambar berhasil dihapus',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message || 'Gagal menghapus gambar'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                        });
                    }
                });
            });
        });
    }

    // Preview Refresh Handler
    function setupPreviewRefresh() {
        const btnRefresh = document.getElementById('btnRefreshPreview');
        if (!btnRefresh) return;

        btnRefresh.addEventListener('click', function() {
            const loader = document.getElementById('previewLoader');
            const previewContainer = document.getElementById('a4-preview-container');

            if (!loader || !previewContainer) return;

            loader.style.display = 'inline-block';
            btnRefresh.disabled = true;

            const formData = new FormData(document.getElementById('formKopSurat'));

            // Use the correct singleton preview route
            fetch("{{ route('kop.preview') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newKopHeader = doc.body.innerHTML;

                // Make sure we preserve sample content if any logic requires it
                // But usually preview returns partial.
                // Our partial is just the header. We need to re-append sample content.
                // Actually the current layout in view has sample content AFTER the partial include.
                // But if we replace innerHTML of a4-preview-container which contains BOTH partial AND sample content...
                // The partial only renders the header?
                // The controller preview method renders 'shared._kop_surat'.
                // So expected HTML is JUST the header.
                // We should only replace the header part. 
                // BUT, 'shared._kop_surat' might be the whole thing inside preview container?
                // Let's check view structure:
                // <div class="a4-preview" id="a4-preview-container">
                //    @@include('shared._kop_surat', ...)
                //    <div sample-content>...</div>
                // </div>
                // If we replace innerHTML, we lose sample content.
                // Solution: Wrap @@include in a div or target it specifically.
                // Or easier: Just prepend the new header and keep the existing sample content div.
                
                // Let's find the sample content div first.
                const sampleContent = previewContainer.querySelector('div[style*="padding: 20px"]');
                
                // Set innerHTML to new header
                previewContainer.innerHTML = newKopHeader;
                
                // Re-append sample content
                if (sampleContent) previewContainer.appendChild(sampleContent);

                loader.style.display = 'none';
                btnRefresh.disabled = false;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Preview Updated!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            })
            .catch(error => {
                console.error('Preview error:', error);
                loader.style.display = 'none';
                btnRefresh.disabled = false;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Preview Failed',
                        text: 'Gagal memuat preview',
                        toast: true,
                        position: 'top-end',
                        timer: 3000
                    });
                }
            });
        });
    }

    // ===== Preset Selector =====
    function setupPresetSelector() {
        const presetSelect = document.getElementById('presetSelector');
        if (!presetSelect) return;

        presetSelect.addEventListener('change', function() {
            if (!this.value) return;

            Swal.fire({
                title: 'Terapkan Preset?',
                text: 'Pengaturan form akan diubah sesuai preset. Anda tetap harus menyimpan untuk menyimpan perubahan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terapkan',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('{{ route("kop.apply-preset") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ preset: this.value })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.config) {
                            // Apply config to form fields
                            Object.keys(data.config).forEach(key => {
                                const el = document.querySelector(`[name="${key}"]`);
                                if (el) {
                                    if (el.type === 'checkbox') {
                                        el.checked = !!data.config[key];
                                    } else if (el.type === 'radio') {
                                        document.querySelector(`[name="${key}"][value="${data.config[key]}"]`)?.click();
                                    } else {
                                        el.value = data.config[key];
                                    }
                                }
                            });

                            // Update range slider displays
                            document.querySelectorAll('.range-slider').forEach(slider => {
                                const target = document.querySelector(slider.dataset.target);
                                const unit = slider.dataset.unit || '%';
                                if (target) target.textContent = slider.value + unit;
                            });

                            Swal.fire({
                                icon: 'success',
                                title: `Preset "${data.name}" diterapkan!`,
                                text: 'Klik Simpan untuk menyimpan perubahan.',
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({ icon: 'error', title: 'Gagal menerapkan preset' });
                    });
                }
                this.value = ''; // Reset selector
            });
        });
    }

    // ===== Import Functionality =====
    function setupImport() {
        const btnImport = document.getElementById('btnImport');
        const importFile = document.getElementById('importFile');
        if (!btnImport || !importFile) return;

        btnImport.addEventListener('click', () => importFile.click());

        importFile.addEventListener('change', function() {
            if (!this.files.length) return;

            const formData = new FormData();
            formData.append('file', this.files[0]);

            Swal.fire({
                title: 'Mengimport...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('{{ route("kop.import") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Berhasil!',
                        text: 'Halaman akan di-refresh.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Import Gagal', text: data.message });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Import Gagal', text: 'Terjadi kesalahan' });
            });

            this.value = ''; // Reset file input
        });
    }

    // ===== Paper Size Selector =====
    function setupPaperSizeSelector() {
        const paperSelect = document.getElementById('paperSizeSelector');
        const a4Preview = document.getElementById('a4-preview-container');
        if (!paperSelect || !a4Preview) return;

        paperSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const width = selected.dataset.width;
            const height = selected.dataset.height;
            
            a4Preview.style.width = width;
            a4Preview.style.height = height;
        });
    }

    // ===== Preview Refresh Button Handler =====
    function setupPreviewRefresh() {
        const btn = document.getElementById('btnRefreshPreview');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                triggerPreview();
            });
        }
    }

    // ===== Trigger Preview (Shared Function) =====
    function triggerPreview() {
        const form = document.getElementById('formKopSurat');
        const previewContainer = document.getElementById('a4-preview-container');
        const loader = document.getElementById('previewLoader');
        
        if (!form || !previewContainer) {
            console.error('Form or preview container not found');
            return;
        }

        if (loader) loader.style.display = 'inline-block';

        // Create FormData but EXCLUDE _method field (form uses PUT for update but preview needs POST)
        const formData = new FormData(form);
        formData.delete('_method'); // Remove the _method=PUT field
        
        const previewUrl = '{{ route("kop.preview") }}';
        
        console.log('Preview URL:', previewUrl);
        console.log('FormData has _method?', formData.has('_method'));

        fetch(previewUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(res => {
            console.log('Response status:', res.status);
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.text();
        })
        .then(html => {
            console.log('=== PREVIEW DEBUG ===');
            console.log('HTML Length:', html.length);
            console.log('HTML Preview (Start):', html.substring(0, 100));

            // Find existing header container or create wrapper
            let headerContainer = previewContainer.querySelector('.kop-preview-wrapper');
            let sampleContent = previewContainer.querySelector('.sample-letter-body');

            console.log('Header Container Found?', !!headerContainer);
            console.log('Sample Content Found?', !!sampleContent);

            // If structure is already correct (Header + Body)
            if (headerContainer && sampleContent) {
                console.log('Updating existing header container...');
                headerContainer.innerHTML = html;
            } else {
                console.log('Rebuilding preview structure...');
                // ... (existing logic)
                // If structure is broken or first load, rebuild it properly
                // We need to preserve the sample content text if it exists, roughly
                const sampleText = sampleContent ? sampleContent.outerHTML : `
                    <div class="sample-letter-body" style="padding: 20px 40px; color: #333; font-family: 'Times New Roman', serif; line-height: 1.6;">
                        <div style="text-align: center; font-weight: bold; margin-bottom: 20px; text-decoration: underline;">
                            CONTOH ISI SURAT
                        </div>
                        <p>Yang bertanda tangan di bawah ini:</p>
                        <table style="width: 100%; margin-left: 20px; margin-bottom: 20px;">
                            <tr><td style="width: 100px;">Nama</td><td>: ____________________</td></tr>
                            <tr><td>Jabatan</td><td>: ____________________</td></tr>
                        </table>
                        <p>Dengan ini menyatakan bahwa...</p>
                        <br><br><br>
                        <div style="text-align: right; margin-top: 50px;">
                            <p>Hormat kami,</p>
                            <br><br><br>
                            <p>(____________________)</p>
                        </div>
                    </div>
                `;

                previewContainer.innerHTML = `<div class="kop-preview-wrapper">${html}</div>${sampleText}`;
            }

            if (loader) loader.style.display = 'none';
        })
        .catch(err => {
            console.error('Live preview error:', err);
            if (loader) loader.style.display = 'none';
            
            // Show error in preview
            if (previewContainer) {
                previewContainer.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h4>Preview Gagal</h4>
                        <p>${err.message}</p>
                        <small>Silakan refresh halaman dan coba lagi</small>
                    </div>
                `;
            }
        });
    }

    // ===== Live Preview with Debounce (NOW ENABLED) =====
    function setupLivePreview() {
        const form = document.getElementById('formKopSurat');
        const previewContainer = document.getElementById('a4-preview-container');
        const loader = document.getElementById('previewLoader');
        if (!form || !previewContainer) return;

        let debounceTimer;
        const DEBOUNCE_MS = 800; // 800ms delay untuk mengurangi request

        // Semua field yang trigger live preview
        const livePreviewFields = [
            'mode_type', 'text_align', 'nama_fakultas', 'alamat_lengkap', 
            'telepon_lengkap', 'email_website', 'logo_size', 'font_size_title',
            'font_size_text', 'text_color', 'header_padding', 'background_opacity',
            'tampilkan_logo_kanan', 'tampilkan_logo_kiri'
        ];

        livePreviewFields.forEach(fieldName => {
            const elements = document.querySelectorAll(`[name="${fieldName}"]`);
            elements.forEach(el => {
                const eventType = (el.type === 'checkbox' || el.type === 'radio') ? 'change' : 'input';
                el.addEventListener(eventType, () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => triggerPreview(), DEBOUNCE_MS);
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        toggleSections();
        updateRadioCards();
        updateFileLabels();
        setupRangeSliders();
        setupZoomControls();
        setupDeleteImage();
        setupPreviewRefresh();
        setupPresetSelector();
        setupImport();
        setupPaperSizeSelector();
        setupLivePreview();

        document.querySelectorAll('input[name="mode_type"]').forEach(function(radio){
            radio.addEventListener('change', function() {
                toggleSections();
                updateRadioCards();
            });
        });

        document.querySelectorAll('.radio-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });
    });
})();
</script>
@endpush
