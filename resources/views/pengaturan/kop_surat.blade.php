{{-- resources/views/pengaturan/kop_surat.blade.php --}}
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
    .align-selector input[type="radio"] {
        display: none;
    }
    .align-selector label {
        padding: 10px 20px;
        cursor: pointer;
        background: white;
        color: #667eea;
        transition: all 0.3s ease;
        margin: 0;
        border-right: 1px solid #e3e8ef;
    }
    .align-selector label:last-child {
        border-right: none;
    }
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

    .custom-range:focus {
        outline: none;
    }

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
    
    @media (max-width: 1400px) {
        .a4-preview {
            transform: scale(0.8);
        }
        .preview-wrapper {
            max-height: 80vh;
        }
    }
    @media (max-width: 1200px) {
        .a4-preview {
            transform: scale(0.6);
        }
    }
    @media (max-width: 992px) {
        .a4-preview {
            transform: scale(0.5);
        }
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
                    text: '{{ session('success') }}',
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

    <div class="row">
        <div class="col-lg-5">
            <div class="card card-modern">
                <div class="card-header-modern">
                    <i class="fas fa-cog mr-2"></i>Pengaturan Kop Surat
                    <span class="badge-gradient float-right">
                        <i class="fas fa-crown mr-1"></i>Admin Only
                    </span>
                </div>
                <div class="card-body" style="padding: 30px; max-height: 90vh; overflow-y: auto;">
                    <form action="{{ route('kop.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- PILIHAN MODE --}}
                        <div class="mb-4">
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
                        </div>

                        {{-- SECTION: CUSTOM MODE --}}
                        <div id="section_custom" class="section-box">
                            <h5 class="mb-4" style="color: #667eea; font-weight: 600;">
                                <i class="fas fa-edit mr-2"></i>Custom Header (Teks + Background)
                            </h5>

                            {{-- Upload Background untuk Custom Mode --}}
                            <div class="form-group mb-4">
                                <label class="font-weight-bold" style="color: #2d3748;">
                                    <i class="fas fa-image mr-2"></i>Background Header (Opsional)
                                </label>
                                <div class="custom-file">
                                    <input type="file" name="background_header" class="custom-file-input" id="background_custom" accept="image/*">
                                    <label class="custom-file-label" for="background_custom">Pilih gambar background...</label>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>Background sebagai latar, teks akan ditampilkan di atasnya
                                </small>
                                @if($kop?->background_path && ($kop->mode_type ?? 'custom') === 'custom')
                                    <div class="mt-2 text-center position-relative" style="display: inline-block;">
                                        <img src="{{ asset('storage/' . $kop->background_path) }}" 
                                             class="img-thumbnail" 
                                             style="max-height:80px; border-radius: 8px;">
                                        <button type="button" class="btn btn-danger btn-sm delete-image-btn" 
                                                data-type="background" 
                                                style="position: absolute; top: -8px; right: -8px; padding: 2px 6px; border-radius: 50%; font-size: 10px;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>

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
                                           min="30" max="200" step="5"
                                           value="{{ old('logo_size', $kop->logo_size ?? 100) }}"
                                           data-target="#logo_size_value">
                                    <div class="d-flex justify-content-between" style="font-size: 10px; color: #999;">
                                        <span>30%</span>
                                        <span>200%</span>
                                    </div>
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
                                           min="0" max="50" step="5"
                                           value="{{ old('header_padding', $kop->header_padding ?? 15) }}"
                                           data-target="#padding_value"
                                           data-unit="px">
                                </div>

                                {{-- Background Opacity --}}
                                <div class="form-group mb-0">
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

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold" style="color: #2d3748;">
                                            <i class="fas fa-image mr-2"></i>Logo Kanan
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" name="logo_kanan" class="custom-file-input" id="logo_kanan" accept="image/*">
                                            <label class="custom-file-label" for="logo_kanan">Pilih file...</label>
                                        </div>
                                        @if($kop?->logo_kanan_path)
                                            <div class="mt-3 text-center position-relative" style="display: inline-block;">
                                                <img src="{{ asset('storage/'.$kop->logo_kanan_path) }}" 
                                                     class="img-thumbnail" 
                                                     style="max-height:80px; border-radius: 10px; border: 2px solid #667eea;">
                                                <button type="button" class="btn btn-danger btn-sm delete-image-btn" 
                                                        data-type="logo" 
                                                        style="position: absolute; top: -8px; right: -8px; padding: 2px 6px; border-radius: 50%; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold" style="color: #2d3748;">
                                            <i class="fas fa-stamp mr-2"></i>Cap/Stamp
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" name="cap" class="custom-file-input" id="cap" accept="image/*">
                                            <label class="custom-file-label" for="cap">Pilih file...</label>
                                        </div>
                                        @if($kop?->cap_path)
                                            <div class="mt-3 text-center position-relative" style="display: inline-block;">
                                                <img src="{{ asset('storage/'.$kop->cap_path) }}" 
                                                     class="img-thumbnail" 
                                                     style="max-height:80px; border-radius: 10px; border: 2px solid #667eea;">
                                                <button type="button" class="btn btn-danger btn-sm delete-image-btn" 
                                                        data-type="cap" 
                                                        style="position: absolute; top: -8px; right: -8px; padding: 2px 6px; border-radius: 50%; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
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
                                    <input type="file" name="background" class="custom-file-input" id="background_full" accept="image/*">
                                    <label class="custom-file-label" for="background_full">Pilih gambar kop yang sudah jadi...</label>
                                </div>
                                <div class="alert alert-info mt-3" style="border-radius: 10px; background: #e8f4fd; border: none;">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Info:</strong> Gambar ini akan ditampilkan langsung tanpa overlay teks.<br>
                                    • Format: JPG/PNG<br>
                                    • Max: 4MB<br>
                                    • Ukuran: A4 (210x297mm atau 2480x3508px @300dpi)
                                </div>
                                @if($kop?->background_path && ($kop->mode_type ?? 'custom') === 'upload')
                                    <div class="mt-3 text-center position-relative" style="display: inline-block;">
                                        <img src="{{ asset('storage/' . $kop->background_path) }}" 
                                             alt="Background Preview" 
                                             class="img-thumbnail" 
                                             style="max-height: 200px; border-radius: 10px; border: 2px solid #667eea;">
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
                            <button type="submit" class="btn btn-gradient btn-lg">
                                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- PREVIEW A4 FULL SIZE --}}
        <div class="col-lg-7">
            <div class="card card-modern sticky-top" style="top: 20px;">
                <div class="card-header-modern">
                    <i class="fas fa-eye mr-2"></i>Preview Full A4
                </div>
                <div class="card-body p-0">
                    <div class="preview-wrapper">
                        <div class="preview-controls">
                            <button type="button" class="zoom-btn active" data-scale="1">100%</button>
                            <button type="button" class="zoom-btn" data-scale="0.8">80%</button>
                            <button type="button" class="zoom-btn" data-scale="0.6">60%</button>
                            <button type="button" class="zoom-btn" data-scale="0.5">50%</button>
                        </div>
                        <div class="a4-preview" id="a4-preview-container">
                            @include('shared._kop_surat', ['context' => 'web', 'showDivider' => true])
                            
                            {{-- Sample content untuk menunjukkan full A4 --}}
                            <div style="padding: 20px; font-size: 11pt; line-height: 1.6;">
                                <p style="text-align: center; margin-bottom: 20px;">
                                    <strong>CONTOH ISI SURAT</strong>
                                </p>
                                <p>Yang bertanda tangan di bawah ini:</p>
                                <p>Nama: ___________________<br>
                                Jabatan: ___________________</p>
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
    </div>
</div>

@push('scripts')
<script>
(function(){
    function toggleSections() {
        const modeType = document.querySelector('input[name="mode_type"]:checked')?.value || 'custom';
        const sectionCustom = document.getElementById('section_custom');
        const sectionUpload = document.getElementById('section_upload');
        
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
        document.querySelectorAll('.radio-card').forEach(card => {
            card.classList.remove('active');
        });
        const checkedRadio = document.querySelector('input[name="mode_type"]:checked');
        if (checkedRadio) {
            checkedRadio.closest('.radio-card').classList.add('active');
        }
    }
    
    function updateFileLabels() {
        document.querySelectorAll('.custom-file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Pilih file...';
                const label = e.target.nextElementSibling;
                label.textContent = fileName;
            });
        });
    }
    
    function setupRangeSliders() {
        document.querySelectorAll('.range-slider').forEach(slider => {
            const target = document.querySelector(slider.dataset.target);
            const unit = slider.dataset.unit || '%';
            
            slider.addEventListener('input', function() {
                target.textContent = this.value + unit;
            });
        });
    }

    const colorInput = document.getElementById('text_color');
    if(colorInput) {
        colorInput.addEventListener('input', function() {
            document.getElementById('text_color_hex').textContent = this.value.toUpperCase();
        });
    }
    
    function setupZoomControls() {
        const zoomButtons = document.querySelectorAll('.zoom-btn');
        const a4Preview = document.getElementById('a4-preview-container');
        
        zoomButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const scale = this.dataset.scale;
                a4Preview.style.transform = `scale(${scale})`;
                
                zoomButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Handle delete image dengan SweetAlert2
    function setupDeleteImage() {
        document.querySelectorAll('.delete-image-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const typeName = type === 'logo' ? 'Logo' : (type === 'cap' ? 'Cap' : 'Background');
                
                // Konfirmasi dengan SweetAlert2
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
                        // Show loading
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // AJAX Delete
                        fetch(`/pengaturan/kop-surat/delete-image/${type}`, {
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
                                // Success notification
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message,
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter', Swal.stopTimer)
                                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                                    }
                                }).then(() => {
                                    // Reload page after notification
                                    window.location.reload();
                                });
                            } else {
                                // Error notification
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message,
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat menghapus gambar',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        });
                    }
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
@endsection
