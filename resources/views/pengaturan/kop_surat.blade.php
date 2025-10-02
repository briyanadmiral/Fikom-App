@extends('layouts.app')

@section('title','Pengaturan Kop Surat')

@push('styles')
<style>
    /* Menggunakan kembali style header dan komponen dari halaman lain */
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#6610f2 0,#8540f5 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #6610f24d; font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold; color: #3c0991; font-size: 1.85rem;
        margin-bottom: 0.13rem; letter-spacing: -1px;
    }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }
    .card-settings {
        border: none; border-radius: .8rem;
        box-shadow: 0 4px 25px rgba(0,0,0, .05);
    }
    .card-settings .card-header {
        background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem;
    }
    .card-settings .card-body { padding: 1.5rem; }
    .form-control, .custom-select { border-radius: .5rem; }

    /* Kotak Upload Gambar dengan Pratinjau */
    .upload-box {
        border: 2px dashed #ced4da; border-radius: .5rem;
        padding: 1rem; text-align: center;
        background-color: #f8f9fa;
    }
    .upload-box .preview-img {
        max-height: 80px; max-width: 100%;
        border-radius: .3rem; margin-bottom: .5rem;
        object-fit: contain;
    }
    .upload-box .custom-file-label {
        text-align: left;
        background-color: #fff;
    }

    /* Pratinjau Surat di Kanan */
    .preview-card { position: sticky; top: 20px; }
    .preview-pane {
        background-color: #fff;
        box-shadow: 0 0 20px rgba(0,0,0,.1);
        padding: 20px;
        font-family: 'Times New Roman', Times, serif;
    }
    .preview-fallback-img { max-width: 100%; height: auto; }

    /* [MODIFIED] Gaya Pratinjau Baru untuk Layout FIKOM */
    .preview-content-fikom {
        display: flex;
        justify-content: space-between;
        align-items: center; /* Rata tengah vertikal */
        gap: 15px;
    }
    .preview-text-fikom {
        flex-grow: 1; /* Teks mengisi ruang tersisa */
        text-align: left;
        font-family: 'Calibri', 'Arial', sans-serif;
    }
    .preview-text-fikom h1 {
        font-size: 14px; font-weight: bold; margin: 0; color: #333;
    }
    .preview-text-fikom p {
        font-size: 10px; margin: 0; line-height: 1.3; color: #555;
    }
    .preview-logo-fikom {
        flex-shrink: 0; /* Logo tidak menyusut */
        padding-left: 15px;
        border-left: 1.5px solid #000;
        text-align: right;
    }
    .preview-logo-fikom img {
        height: 65px; /* Sesuaikan tinggi logo */
        width: auto;
    }
    .kop-divider {
      border-top: 2px solid black;
      margin-top: 8px;
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-sliders-h text-white"></i></span>
    <span>
        <div class="page-header-title">Pengaturan Kop Surat</div>
        <div class="page-header-desc">
            Atur tampilan header, footer, logo, dan cap untuk semua surat yang dibuat.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Gagal menyimpan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('kop.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            {{-- KOLOM KIRI: FORM PENGATURAN --}}
            <div class="col-lg-7">
                {{-- Kartu Mode --}}
                <div class="card card-settings mb-4">
                    <div class="card-header"><h6 class="mb-0 font-weight-bold">1. Pilih Mode Kop Surat</h6></div>
                    <div class="card-body">
                        <select name="mode" id="kop_mode" class="custom-select">
                            <option value="composed" {{ old('mode', $kop->mode ?? 'composed') === 'composed' ? 'selected' : '' }}>
                                Mode Standar Fakultas (Teks Kiri + Logo Kanan)
                            </option>
                            <option value="image" {{ old('mode', $kop->mode) === 'image' ? 'selected' : '' }}>
                                Mode Gambar Penuh (Fallback)
                            </option>
                        </select>
                        <small class="text-muted mt-2 d-block"><b>Mode Standar</b> direkomendasikan untuk hasil cetak terbaik. <b>Mode Gambar</b> digunakan sebagai alternatif jika Anda memiliki gambar kop yang sudah jadi.</small>
                    </div>
                </div>

                {{-- Kartu Detail Kop (Mode Standar Fakultas) --}}
                <div class="card card-settings mb-4" id="composed-settings-card">
                    <div class="card-header"><h6 class="mb-0 font-weight-bold">2. Detail Kop Surat Standar</h6></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Judul Utama (Contoh: FAKULTAS ILMU KOMPUTER)</label>
                            <input name="judul_atas" class="form-control" data-live-preview="#preview-judul-atas" value="{{ old('judul_atas', $kop->judul_atas ?? 'FAKULTAS ILMU KOMPUTER') }}">
                        </div>
                        <div class="form-group">
                            <label>Detail Alamat & Kontak</label>
                            <textarea name="alamat" class="form-control" rows="3" data-live-preview="#preview-alamat">{{ old('alamat', $kop->alamat ?? "Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234\nTelp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 - 8445265\ne-mail: unika@unika.ac.id http://www.unika.ac.id") }}</textarea>
                        </div>
                        
                        {{-- Hidden fields for compatibility --}}
                        <input type="hidden" name="subjudul" value="{{ old('subjudul', $kop->subjudul) }}">
                        <input type="hidden" name="telepon" value="{{ old('telepon', $kop->telepon) }}">
                        <input type="hidden" name="email" value="{{ old('email', $kop->email) }}">
                    </div>
                </div>

                {{-- Kartu Aset Gambar --}}
                <div class="card card-settings">
                    <div class="card-header"><h6 class="mb-0 font-weight-bold">3. Aset Gambar (Logo, Cap, dsb.)</h6></div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Logo Kanan (di mode standar) --}}
                            <div class="col-md-6 mb-3" data-composed-item>
                                <label class="d-block mb-2">Logo Kanan</label>
                                <div class="upload-box">
                                    <img id="logo_kanan_preview" src="{{ $kop->logo_kanan_path ? asset('storage/'.$kop->logo_kanan_path) : 'https://placehold.co/200x80/f8f9fa/ccc?text=Logo' }}" alt="Pratinjau Logo Kanan" class="preview-img">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="logo_kanan" id="logo_kanan" accept="image/png, image/jpeg, image/svg+xml">
                                        <label class="custom-file-label" for="logo_kanan">Pilih file...</label>
                                    </div>
                                </div>
                            </div>
                            {{-- Cap/Stempel --}}
                            <div class="col-md-6 mb-3">
                                <label class="d-block mb-2">Cap/Stempel</label>
                                <div class="upload-box">
                                    <img src="{{ $kop->cap_path ? asset('storage/'.$kop->cap_path) : 'https://placehold.co/200x200/f8f9fa/ccc?text=Cap' }}" alt="Pratinjau Cap" class="preview-img" style="max-height: 120px;">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="cap" id="cap" accept="image/png">
                                        <label class="custom-file-label" for="cap">Pilih PNG...</label>
                                    </div>
                                </div>
                            </div>
                            {{-- Header Fallback --}}
                            <div class="col-12 mb-3" data-image-item>
                                <label class="d-block mb-2">Gambar Header Penuh (Fallback)</label>
                                <div class="upload-box">
                                    <img id="header_preview" src="{{ $kop->header_path ? asset('storage/'.$kop->header_path) : 'https://placehold.co/800x150/f8f9fa/ccc?text=Header' }}" alt="Pratinjau Header" class="preview-img" style="max-height: 100px;">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="header" id="header" accept="image/png, image/jpeg">
                                        <label class="custom-file-label" for="header">Pilih file...</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tombol Simpan --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg mb-3"><i class="fas fa-save mr-2"></i>Simpan Pengaturan</button>
                </div>
            </div>

            {{-- KOLOM KANAN: PRATINJAU LANGSUNG --}}
            <div class="col-lg-5">
                <div class="card card-settings preview-card">
                    <div class="card-header"><h6 class="mb-0 font-weight-bold"><i class="fas fa-eye mr-2"></i>Pratinjau Langsung</h6></div>
                    <div class="card-body">
                        <div class="preview-pane">
                            {{-- [MODIFIED] Tampilan Mode Standar Fakultas --}}
                            <div id="composed-preview">
                                <div class="preview-content-fikom">
                                    <div class="preview-text-fikom">
                                        <h1 id="preview-judul-atas">{{ strtoupper($kop->judul_atas ?? 'FAKULTAS ILMU KOMPUTER') }}</h1>
                                        {{-- Ubah nl2br agar baris baru di textarea tampil di pratinjau --}}
                                        <p id="preview-alamat">{!! nl2br(e($kop->alamat ?? "Jl. PawiyatanLuhur IV/ 1,BendanDuwur, Semarang 50234\nTelp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 - 8445265\ne-mail: unika@unika.ac.id http://www.unika.ac.id")) !!}</p>
                                    </div>
                                    <div class="preview-logo-fikom">
                                        <img id="preview-logo-kanan" src="{{ $kop->logo_kanan_path ? asset('storage/'.$kop->logo_kanan_path) : 'https://placehold.co/200x150/fff/ccc?text=' }}" alt="Logo Kanan">
                                    </div>
                                </div>
                                <div class="kop-divider"></div>
                            </div>
                            {{-- Tampilan Mode Gambar Penuh --}}
                            <div id="image-preview" style="display: none;">
                                <img id="preview-header-img" src="{{ $kop->header_path ? asset('storage/'.$kop->header_path) : 'https://placehold.co/800x150/fff/ccc?text=Header Penuh' }}" class="preview-fallback-img">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-3">* Pratinjau ini adalah representasi visual. Tampilan final pada PDF mungkin sedikit berbeda.</small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeSelect = document.getElementById('kop_mode');
    const composedCard = document.getElementById('composed-settings-card');
    const composedItems = document.querySelectorAll('[data-composed-item]');
    const imageItems = document.querySelectorAll('[data-image-item]');
    const composedPreview = document.getElementById('composed-preview');
    const imagePreview = document.getElementById('image-preview');

    function toggleModeView() {
        const isComposed = modeSelect.value === 'composed';
        composedCard.style.display = isComposed ? 'block' : 'none';
        composedPreview.style.display = isComposed ? 'block' : 'none';
        imagePreview.style.display = !isComposed ? 'block' : 'none';
        composedItems.forEach(el => el.style.display = isComposed ? 'block' : 'none');
        imageItems.forEach(el => el.style.display = !isComposed ? 'block' : 'none');
    }

    // Live preview untuk input teks
    document.querySelectorAll('input[data-live-preview], textarea[data-live-preview]').forEach(input => {
        const previewEl = document.querySelector(input.dataset.livePreview);
        if (previewEl) {
            input.addEventListener('input', () => {
                if (input.tagName.toLowerCase() === 'textarea') {
                    // Ganti newline dengan <br> untuk textarea
                    previewEl.innerHTML = input.value.replace(/\n/g, '<br>') || '...';
                } else {
                    previewEl.textContent = input.value.toUpperCase() || '...';
                }
            });
        }
    });

    // Live preview untuk file input
    function handleImagePreview(inputId, previewImgId) {
        const input = document.getElementById(inputId);
        const previewImg = document.getElementById(previewImgId);
        if (input && previewImg) {
            input.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    handleImagePreview('logo_kanan', 'preview-logo-kanan');
    handleImagePreview('header', 'preview-header-img');

    // Update nama file di label untuk semua input file
    ['cap', 'logo_kanan', 'header'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file...';
                const label = this.nextElementSibling;
                if(label) label.textContent = fileName;
            });
        }
    });

    toggleModeView();
    modeSelect.addEventListener('change', toggleModeView);
});
</script>
@endpush
