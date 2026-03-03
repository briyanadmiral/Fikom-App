@extends('layouts.app')

@section('title','Pengaturan Kop Surat')

{{-- Include extracted styles --}}
@include('pengaturan.kop_surat._styles')

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

{{-- Include extracted scripts --}}
@include('pengaturan.kop_surat._scripts')
