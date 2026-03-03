@extends('layouts.app')
@section('title', 'Tanda Tangan Saya')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.min.css">
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(102,126,234,.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #5a5de0;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(102,126,234,.13);
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
        margin-left: .1rem;
    }
    .signature-pad-wrapper {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        background: #f8f9fa;
    }
    .signature-pad-wrapper canvas {
        width: 100%;
        height: 200px;
        border-radius: 6px;
    }
    .current-signature {
        max-width: 300px;
        max-height: 150px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
    }
    .method-tabs .nav-link {
        border-radius: 0.5rem 0.5rem 0 0;
    }
    .method-tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-color: transparent;
    }
    .card { border-radius: 1rem; }
    @media (max-width: 575.98px) {
        .custom-header-box { padding: 1.1rem; }
        .header-icon { width: 44px; height: 44px; font-size: 1.2rem; }
        .header-title { font-size: 1.2rem; }
        .header-desc { margin-left: 0; font-size: .98rem; }
    }
</style>
@endpush

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-signature fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Tanda Tangan Saya</div>
                <div class="header-desc mt-2">
                    Kelola <b>tanda tangan digital</b> Anda untuk digunakan pada surat resmi.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            {{-- Current Signature --}}
            @if($signature && $signature->ttd_path)
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-check-circle mr-2"></i>TTD Saat Ini</h3>
                    <div class="card-tools">
                        <form action="{{ route('signature.destroy') }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Yakin hapus tanda tangan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body text-center">
                    <img src="{{ route('signature.preview') }}" alt="Tanda Tangan" class="current-signature">
                    <p class="text-muted mt-2 mb-0">
                        <small>Terakhir diubah: {{ $signature->updated_at?->format('d M Y, H:i') }}</small>
                    </p>
                </div>
            </div>
            @endif

            {{-- Input Signature --}}
            <div class="card card-primary card-outline">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs method-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#drawTab">
                                <i class="fas fa-pen mr-1"></i>Gambar TTD
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#uploadTab">
                                <i class="fas fa-upload mr-1"></i>Upload File
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- Draw Tab --}}
                        <div class="tab-pane fade show active" id="drawTab">
                            <form action="{{ route('signature.update') }}" method="POST" id="drawForm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="signature_data" id="signatureData">

                                <div class="signature-pad-wrapper mb-3">
                                    <canvas id="signaturePad"></canvas>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" id="clearBtn">
                                        <i class="fas fa-eraser mr-1"></i>Hapus
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>Simpan TTD
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Upload Tab --}}
                        <div class="tab-pane fade" id="uploadTab">
                            <form action="{{ route('signature.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label>Upload Gambar TTD</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="signatureFile" 
                                               name="signature_file" accept="image/png,image/jpeg">
                                        <label class="custom-file-label" for="signatureFile">Pilih file PNG/JPG...</label>
                                    </div>
                                    <small class="text-muted">Format: PNG, JPG. Max 2MB. Latar transparan lebih baik.</small>
                                </div>

                                <div id="uploadPreview" class="mb-3 text-center" style="display: none;">
                                    <img id="previewImg" class="current-signature">
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i>Simpan TTD
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Tips --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lightbulb mr-2"></i>Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li class="mb-2">Gunakan mouse atau stylus untuk hasil terbaik saat menggambar.</li>
                        <li class="mb-2">Untuk upload, gunakan gambar dengan latar transparan (PNG).</li>
                        <li class="mb-2">TTD ini akan digunakan pada surat yang Anda tandatangani.</li>
                        <li>Pastikan TTD sesuai dengan tanda tangan resmi Anda.</li>
                    </ul>
                </div>
            </div>

            {{-- User Info --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pengguna</h6>
                    <p class="mb-1"><strong>{{ $user->nama_lengkap }}</strong></p>
                    <p class="mb-0 text-muted">{{ $user->jabatan ?? $user->role_name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
$(function() {
    // Initialize Signature Pad
    const canvas = document.getElementById('signaturePad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 2.0,              // Garis lebih tebal (default: 0.5)
        maxWidth: 5.0,              // Maksimal tebal (default: 2.5)
        velocityFilterWeight: 0.7   // Smooth velocity untuk konsistensi
    });

    // Resize canvas
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }
    
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Clear button
    $('#clearBtn').on('click', function() {
        signaturePad.clear();
    });

    // Form submit - draw
    $('#drawForm').on('submit', function(e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Silakan gambar tanda tangan terlebih dahulu.');
            return false;
        }
        $('#signatureData').val(signaturePad.toDataURL('image/png'));
    });

    // File upload preview
    $('#signatureFile').on('change', function() {
        const file = this.files[0];
        $(this).next('.custom-file-label').text(file ? file.name : 'Pilih file PNG/JPG...');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#uploadPreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#uploadPreview').hide();
        }
    });
});
</script>
@endpush
