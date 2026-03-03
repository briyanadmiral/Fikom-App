@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@push('styles')
{{-- Cropper.js CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" integrity="sha512-hvNR0F/e2J7zPPfLC9auFe3/SE0yG4aJCOd/qxew74NN7eyiSKjr7xJJMu1Jy2wf7FXITpWS1E/RY8yzuXN7VA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    /* ========================================
       PAGE HEADER
    ======================================== */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }
    .page-header .icon {
        background: rgba(255,255,255,0.2);
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.75rem;
        backdrop-filter: blur(10px);
    }
    .page-header-title {
        font-weight: 700;
        color: #fff;
        font-size: 1.75rem;
        margin-bottom: 0.25rem;
        letter-spacing: -0.5px;
    }
    .page-header-desc {
        color: rgba(255,255,255,0.85);
        font-size: 0.95rem;
    }

    /* ========================================
       CARD STYLING
    ======================================== */
    .card-settings {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 2px 20px rgba(0,0,0,0.06);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .card-settings:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    }
    .card-settings .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #eef2f7;
        padding: 1rem 1.5rem;
    }
    .card-settings .card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #2d3748;
    }
    .card-settings .card-body {
        padding: 1.5rem;
    }

    /* ========================================
       FORM ELEMENTS
    ======================================== */
    .form-group label {
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 0.625rem 0.875rem;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }
    .input-group-text {
        background: linear-gradient(135deg, #f8f9fa 0%, #edf2f7 100%);
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem 0 0 0.5rem;
        color: #718096;
    }
    .input-group .form-control {
        border-radius: 0 0.5rem 0.5rem 0;
    }

    /* ========================================
       BUTTONS
    ======================================== */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.625rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .btn-warning {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        border: none;
        padding: 0.625rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(237, 137, 54, 0.3);
    }
    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(237, 137, 54, 0.4);
    }

    /* ========================================
       PROFILE CARD (RIGHT COLUMN)
    ======================================== */
    .profile-summary-card {
        text-align: center;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .profile-summary-card .card-body {
        padding: 2rem 1.5rem;
    }

    /* Profile Photo */
    .profile-photo-wrapper {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 1.25rem auto;
    }
    .profile-photo-lg {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    }
    .profile-avatar-lg {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3.5rem;
        font-weight: 600;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        border: 4px solid #fff;
    }
    .photo-overlay {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #fff;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        border: 3px solid #fff;
    }
    .photo-overlay:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    /* Profile Info */
    .profile-name {
        font-size: 1.35rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }
    .profile-role {
        color: #718096;
        font-weight: 500;
        font-size: 0.9rem;
        background: #edf2f7;
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        margin-bottom: 0.5rem;
    }

    /* Photo Actions */
    .photo-actions {
        margin-top: 1rem;
    }
    .photo-actions .btn {
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        border-radius: 2rem;
    }

    /* Profile Details */
    .profile-details {
        text-align: left;
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-top: 1rem;
    }
    .profile-details p {
        margin: 0.5rem 0;
        color: #4a5568;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }
    .profile-details p i {
        width: 24px;
        color: #667eea;
    }

    /* ========================================
       FORM HELPER TEXT
    ======================================== */
    .form-text {
        font-size: 0.8rem;
        color: #718096;
    }

    /* Hidden file input */
    #foto-input {
        display: none;
    }

    /* ========================================
       CROPPER MODAL STYLES
    ======================================== */
    .cropper-modal-body {
        padding: 0;
        background: #1a1a2e;
        display: flex;
        gap: 0;
    }
    .cropper-main-area {
        flex: 1;
        min-width: 0;
    }
    .cropper-container-wrapper {
        width: 100%;
        height: 350px;
        background: #1a1a2e;
    }
    .cropper-container-wrapper img {
        max-width: 100%;
        display: block;
    }
    
    /* Realtime Preview Panel */
    .cropper-preview-panel {
        width: 200px;
        background: linear-gradient(180deg, #f8fafc 0%, #edf2f7 100%);
        padding: 1.5rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-left: 1px solid #e2e8f0;
    }
    .preview-label {
        font-size: 0.75rem;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    .preview-circle {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        background: #e2e8f0;
    }
    .preview-circle canvas {
        width: 100%;
        height: 100%;
        display: block;
    }
    .preview-hint {
        font-size: 0.7rem;
        color: #a0aec0;
        margin-top: 1rem;
        text-align: center;
        line-height: 1.4;
    }

    .cropper-controls {
        background: #f8fafc;
        padding: 1rem;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        border-top: 1px solid #e2e8f0;
    }
    .cropper-controls .btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
    }
    .cropper-controls .btn-light {
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .cropper-controls .btn-light:hover {
        background: #f1f5f9;
    }
    .cropper-hint {
        text-align: center;
        padding: 0.75rem;
        background: #edf2f7;
        color: #4a5568;
        font-size: 0.85rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .cropper-hint i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    /* Alert styling */
    .alert-success {
        background: linear-gradient(135deg, #68d391 0%, #48bb78 100%);
        border: none;
        color: #fff;
        border-radius: 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .cropper-modal-body {
            flex-direction: column;
        }
        .cropper-preview-panel {
            width: 100%;
            flex-direction: row;
            padding: 1rem;
            gap: 1rem;
            border-left: none;
            border-top: 1px solid #e2e8f0;
        }
        .preview-circle {
            width: 80px;
            height: 80px;
        }
        .preview-label {
            margin-bottom: 0;
        }
        .preview-hint {
            margin-top: 0;
            text-align: left;
        }
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-user-cog text-white"></i></span>
    <span>
        <div class="page-header-title">Pengaturan Akun</div>
        <div class="page-header-desc">Kelola informasi profil dan keamanan akun Anda</div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Notifikasi Sukses --}}
    @if(session('success_profile') || session('success_password') || session('success_foto'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i> 
        <strong>Berhasil!</strong> {{ session('success_profile') ?? session('success_password') ?? session('success_foto') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
            <span aria-hidden="true" style="color: #fff;">&times;</span>
        </button>
    </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI: FORM --}}
        <div class="col-lg-8 mb-4">
            {{-- Kartu Edit Profil --}}
            <div class="card card-settings mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user-edit mr-2 text-primary"></i>Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('account.updateProfile') }}">
                        @csrf
                        @method('PUT')
                        
                        {{-- Nama Lengkap - Full Width --}}
                        <div class="form-group">
                            <label for="nama_lengkap"><i class="fas fa-user mr-2 text-muted"></i>Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" 
                                   value="{{ old('nama_lengkap', $user->nama_lengkap) }}" 
                                   class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                   placeholder="Masukkan nama lengkap dengan gelar" required>
                            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text">Contoh: Dr. John Doe, S.Kom., M.Kom.</small>
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope mr-2 text-muted"></i>Email</label>
                            <input type="email" id="email" name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="email@domain.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text">Digunakan untuk login ke sistem</small>
                        </div>

                        <div class="form-row">
                            {{-- NPP --}}
                            <div class="form-group col-md-6">
                                <label for="npp"><i class="fas fa-id-badge mr-2 text-muted"></i>NPP</label>
                                <input type="text" id="npp" name="npp" 
                                       value="{{ old('npp', $user->npp) }}" 
                                       class="form-control @error('npp') is-invalid @enderror"
                                       placeholder="123.1.4567.890">
                                @error('npp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="form-text">Nomor Pokok Pegawai (opsional)</small>
                            </div>

                            {{-- Jabatan --}}
                            <div class="form-group col-md-6">
                                <label for="jabatan"><i class="fas fa-briefcase mr-2 text-muted"></i>Jabatan</label>
                                <input type="text" id="jabatan" name="jabatan" 
                                       value="{{ old('jabatan', $user->jabatan) }}" 
                                       class="form-control @error('jabatan') is-invalid @enderror"
                                       placeholder="Dosen / Staff">
                                @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            {{-- Kartu Ubah Password --}}
            <div class="card card-settings">
                <div class="card-header">
                    <h5><i class="fas fa-shield-alt mr-2 text-warning"></i>Keamanan Akun</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('account.updatePassword') }}" autocomplete="off">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="current_password"><i class="fas fa-key mr-2 text-muted"></i>Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" 
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                   placeholder="Masukkan password saat ini" required>
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="new_password"><i class="fas fa-lock mr-2 text-muted"></i>Password Baru</label>
                                <input type="password" id="new_password" name="new_password" 
                                       class="form-control @error('new_password', 'updatePassword') is-invalid @enderror" 
                                       placeholder="Minimal 8 karakter" required>
                                @error('new_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="new_password_confirmation"><i class="fas fa-lock mr-2 text-muted"></i>Konfirmasi Password</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" 
                                       class="form-control" 
                                       placeholder="Ulangi password baru" required>
                            </div>
                        </div>

                        <small class="form-text text-muted mb-3 d-block">
                            <i class="fas fa-info-circle mr-1"></i>
                            Password minimal 8 karakter dan harus berbeda dari password lama.
                        </small>
                        
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt mr-2"></i>Perbarui Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- KOLOM KANAN: PROFIL & FOTO --}}
        <div class="col-lg-4 mb-4">
            <div class="card card-settings profile-summary-card sticky-top" style="top:20px;">
                <div class="card-body">
                    {{-- Foto Profile --}}
                    <div class="profile-photo-wrapper">
                        @if($user->foto_path && Storage::disk('public')->exists($user->foto_path))
                            <img src="{{ asset('storage/' . $user->foto_path) }}" alt="Foto Profile" class="profile-photo-lg" id="profile-photo-display">
                        @else
                            <div class="profile-avatar-lg" style="background: linear-gradient(135deg, {{ generate_color_from_string($user->nama_lengkap) }} 0%, {{ generate_color_from_string($user->nama_lengkap . 'salt') }} 100%);" id="profile-avatar-display">
                                {{ get_initials($user->nama_lengkap) }}
                            </div>
                        @endif
                        <label for="foto-input" class="photo-overlay" title="Ganti Foto">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>

                    <h4 class="profile-name">{{ $user->nama_lengkap }}</h4>
                    <span class="profile-role">{{ optional($user->peran)->nama ?? 'Pengguna' }}</span>

                    {{-- Tombol aksi foto --}}
                    <div class="photo-actions">
                        @if($user->foto_path)
                        <form method="POST" action="{{ route('account.deleteFoto') }}" class="d-inline" id="delete-foto-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-foto">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Foto
                            </button>
                        </form>
                        @else
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Klik ikon kamera untuk upload foto
                        </small>
                        @endif
                    </div>

                    {{-- Profile Details --}}
                    <div class="profile-details">
                        <p><i class="fas fa-envelope"></i> {{ $user->email }}</p>
                        @if($user->npp)
                        <p><i class="fas fa-id-badge"></i> NPP: {{ $user->npp }}</p>
                        @endif
                        @if($user->jabatan)
                        <p><i class="fas fa-briefcase"></i> {{ $user->jabatan }}</p>
                        @endif
                        <p><i class="fas fa-clock"></i> Aktif {{ \Carbon\Carbon::parse($user->last_activity)->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            {{-- Form upload foto (hidden) --}}
            <form method="POST" action="{{ route('account.updateFoto') }}" enctype="multipart/form-data" id="foto-upload-form">
                @csrf
                @method('PUT')
                <input type="file" name="foto" id="foto-input" accept="image/jpeg,image/png,image/webp">
            </form>
        </div>
    </div>
</div>

{{-- Modal Crop Foto dengan Realtime Preview --}}
<div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 1rem; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none;">
                <h5 class="modal-title" id="cropperModalLabel">
                    <i class="fas fa-crop-alt mr-2"></i>Sesuaikan Foto Profile
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="cropper-hint">
                <i class="fas fa-lightbulb"></i>
                Geser, zoom, dan sesuaikan area foto. Lihat preview realtime di sebelah kanan.
            </div>
            <div class="modal-body cropper-modal-body">
                {{-- Cropper Area --}}
                <div class="cropper-main-area">
                    <div class="cropper-container-wrapper">
                        <img id="cropper-image" src="" alt="Crop Image">
                    </div>
                </div>
                
                {{-- Realtime Preview Panel --}}
                <div class="cropper-preview-panel">
                    <div class="preview-label">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </div>
                    <div class="preview-circle">
                        <canvas id="preview-canvas" width="140" height="140"></canvas>
                    </div>
                    <div class="preview-hint">
                        Ini adalah tampilan<br>foto profile Anda
                    </div>
                </div>
            </div>
            <div class="cropper-controls">
                <button type="button" class="btn btn-light" id="btn-zoom-in" title="Zoom In">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button type="button" class="btn btn-light" id="btn-zoom-out" title="Zoom Out">
                    <i class="fas fa-search-minus"></i>
                </button>
                <button type="button" class="btn btn-light" id="btn-rotate-left" title="Putar Kiri">
                    <i class="fas fa-undo"></i>
                </button>
                <button type="button" class="btn btn-light" id="btn-rotate-right" title="Putar Kanan">
                    <i class="fas fa-redo"></i>
                </button>
                <button type="button" class="btn btn-light" id="btn-flip-h" title="Flip Horizontal">
                    <i class="fas fa-arrows-alt-h"></i>
                </button>
                <button type="button" class="btn btn-light" id="btn-flip-v" title="Flip Vertical">
                    <i class="fas fa-arrows-alt-v"></i>
                </button>
                <button type="button" class="btn btn-light" id="btn-reset" title="Reset">
                    <i class="fas fa-sync"></i> Reset
                </button>
            </div>
            <div class="modal-footer" style="border: none; padding: 1rem 1.5rem; background: #f8fafc;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 0.5rem;">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn-crop-upload" style="border-radius: 0.5rem;">
                    <i class="fas fa-check mr-1"></i> Gunakan Foto Ini
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Cropper.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" integrity="sha512-9KkIqdfN7ipEW6B6k+Aq20PV31bjODg4AA52W+tYtAE0jE0kMx49bjJ3FgvS56wzmyfMUHbQ4Km2b7l9+Y/+Eg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
$(function() {
    let cropper = null;
    let originalFile = null;
    let previewCanvas = document.getElementById('preview-canvas');
    let previewCtx = previewCanvas.getContext('2d');
    
    // Update preview function
    function updatePreview() {
        if (!cropper) return;
        
        try {
            const croppedCanvas = cropper.getCroppedCanvas({
                width: 140,
                height: 140,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });
            
            if (croppedCanvas) {
                // Clear and draw to preview canvas
                previewCtx.clearRect(0, 0, 140, 140);
                previewCtx.drawImage(croppedCanvas, 0, 0, 140, 140);
            }
        } catch (e) {
            console.log('Preview update skipped');
        }
    }
    
    // Saat file dipilih
    $('#foto-input').on('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Valid',
                text: 'Format yang diizinkan: JPEG, PNG, WebP.'
            });
            this.value = '';
            return;
        }

        // Validasi ukuran file (max 5MB untuk source, akan di-compress saat crop)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran Terlalu Besar',
                text: 'Ukuran foto maksimal 5MB.'
            });
            this.value = '';
            return;
        }

        originalFile = file;

        // Clear preview
        previewCtx.clearRect(0, 0, 140, 140);
        previewCtx.fillStyle = '#e2e8f0';
        previewCtx.fillRect(0, 0, 140, 140);

        // Load gambar ke cropper
        const reader = new FileReader();
        reader.onload = function(e) {
            const cropperImage = document.getElementById('cropper-image');
            cropperImage.src = e.target.result;
            
            // Destroy existing cropper if any
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            
            // Show modal
            $('#cropperModal').modal('show');
        };
        reader.readAsDataURL(file);
    });

    // Initialize cropper when modal is shown
    $('#cropperModal').on('shown.bs.modal', function() {
        const cropperImage = document.getElementById('cropper-image');
        
        cropper = new Cropper(cropperImage, {
            aspectRatio: 1, // Square crop for profile photo
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            minContainerWidth: 300,
            minContainerHeight: 300,
            background: true,
            responsive: true,
            ready: function() {
                // Initial preview update
                updatePreview();
            },
            crop: function() {
                // Update preview on every crop change
                updatePreview();
            },
            zoom: function() {
                // Update preview on zoom
                setTimeout(updatePreview, 50);
            }
        });
    });

    // Destroy cropper when modal is hidden
    $('#cropperModal').on('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        $('#foto-input').val('');
        
        // Clear preview
        previewCtx.clearRect(0, 0, 140, 140);
        previewCtx.fillStyle = '#e2e8f0';
        previewCtx.fillRect(0, 0, 140, 140);
    });

    // Zoom controls
    $('#btn-zoom-in').on('click', function() {
        if (cropper) cropper.zoom(0.1);
    });
    
    $('#btn-zoom-out').on('click', function() {
        if (cropper) cropper.zoom(-0.1);
    });

    // Rotate controls
    $('#btn-rotate-left').on('click', function() {
        if (cropper) cropper.rotate(-90);
    });
    
    $('#btn-rotate-right').on('click', function() {
        if (cropper) cropper.rotate(90);
    });

    // Flip controls
    $('#btn-flip-h').on('click', function() {
        if (cropper) {
            const data = cropper.getData();
            cropper.scaleX(data.scaleX === -1 ? 1 : -1);
        }
    });
    
    $('#btn-flip-v').on('click', function() {
        if (cropper) {
            const data = cropper.getData();
            cropper.scaleY(data.scaleY === -1 ? 1 : -1);
        }
    });

    // Reset
    $('#btn-reset').on('click', function() {
        if (cropper) cropper.reset();
    });

    // Crop and upload
    $('#btn-crop-upload').on('click', function() {
        if (!cropper) return;

        // Get cropped canvas
        const canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
            minWidth: 200,
            minHeight: 200,
            maxWidth: 800,
            maxHeight: 800,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal memproses gambar. Silakan coba lagi.'
            });
            return;
        }

        // Convert to blob and upload
        canvas.toBlob(function(blob) {
            if (!blob) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal mengkonversi gambar.'
                });
                return;
            }

            // Check blob size (max 2MB)
            if (blob.size > 2 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gambar Terlalu Besar',
                    text: 'Hasil crop masih terlalu besar. Coba gunakan area yang lebih kecil.'
                });
                return;
            }

            // Create form data
            const formData = new FormData();
            formData.append('foto', blob, 'profile.jpg');
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');

            // Close modal and show loading
            $('#cropperModal').modal('hide');
            showLoading('Mengupload foto...');

            // Upload via AJAX
            $.ajax({
                url: '{{ route("account.updateFoto") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading();
                    // Reload page to show new photo
                    window.location.reload();
                },
                error: function(xhr) {
                    hideLoading();
                    let message = 'Terjadi kesalahan saat mengupload foto.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join(', ');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Gagal',
                        text: message
                    });
                }
            });
        }, 'image/jpeg', 0.9); // JPEG with 90% quality
    });

    // Hapus foto dengan konfirmasi
    $('#btn-delete-foto').on('click', function() {
        Swal.fire({
            title: 'Hapus Foto Profile?',
            text: 'Foto akan dihapus dan avatar default akan ditampilkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e53e3e',
            cancelButtonColor: '#718096',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading('Menghapus foto...');
                $('#delete-foto-form').submit();
            }
        });
    });
});
</script>
@endpush
