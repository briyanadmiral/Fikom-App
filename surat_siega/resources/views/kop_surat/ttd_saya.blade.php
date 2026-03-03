@extends('layouts.app')

@section('title','Tanda Tangan Saya')

@push('styles')
<style>
    /* Menggunakan kembali style header dan komponen dari halaman lain */
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        /* Warna cyan/teal untuk identitas personal */
        background: linear-gradient(135deg,#17a2b8 0,#20c997 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #17a2b84d; font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold; color: #0c5460; font-size: 1.85rem;
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
    .form-control, .custom-select { border-radius: .5rem; }
    .input-group-text { background-color: #e9ecef; }

    /* [BARU] Kotak Upload Interaktif */
    .upload-box {
        border: 2px dashed #ced4da; border-radius: .8rem;
        padding: 2rem; text-align: center;
        background-color: #f8f9fa; transition: all .2s ease; cursor: pointer;
    }
    .upload-box:hover, .upload-box.is-dragging {
        border-color: #007bff;
        background-color: #eaf3ff;
    }
    .upload-box .upload-icon { font-size: 3rem; color: #6c757d; }
    .upload-box .upload-text { font-weight: 600; color: #495057; }
    #ttd_file { display: none; } /* Sembunyikan input file asli */

    /* [BARU] Pratinjau di Kanan */
    .preview-card { position: sticky; top: 20px; }
    .preview-pane {
        background-color: #fff;
        border: 1px solid #dee2e6;
        padding: 30px;
        min-height: 400px;
    }
    .preview-pane p {
        font-family: 'Times New Roman', Times, serif; font-size: 12pt;
        line-height: 1.5; color: #333;
        margin-bottom: 50px; text-align: justify;
    }
    .signature-area {
        margin-left: 60%; /* Posisi tanda tangan di kanan */
        text-align: center;
    }
    .signature-image-container {
        height: 50px; /* Area untuk gambar */
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 5px;
    }
    #signature-in-document {
        display: block;
        max-width: 100%;
        max-height: 100%;
    }
    .signature-name {
        font-family: 'Times New Roman', Times, serif; font-size: 12pt;
        font-weight: bold; text-decoration: underline;
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-signature text-white"></i></span>
    <span>
        <div class="page-header-title">Tanda Tangan Digital Saya</div>
        <div class="page-header-desc">
            Unggah dan atur ukuran tanda tangan Anda untuk digunakan di semua surat.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <form method="post" action="{{ route('kop.ttd.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- KOLOM KIRI: FORM --}}
            <div class="col-lg-6">
                <div class="card card-settings">
                    <div class="card-header"><h6 class="mb-0 font-weight-bold">Upload & Atur Tanda Tangan</h6></div>
                    <div class="card-body">
                        {{-- Upload Box --}}
                        <div class="form-group">
                            <label>File Tanda Tangan (PNG Transparan)</label>
                            <div class="upload-box" id="upload-box-container">
                                <i class="fas fa-upload upload-icon mb-2"></i>
                                <p class="upload-text mb-1">Seret file ke sini atau klik untuk memilih</p>
                                <small class="text-muted" id="filename-display">Belum ada file dipilih</small>
                            </div>
                            <input type="file" name="file" id="ttd_file" accept="image/png">
                        </div>

                        {{-- Pengaturan Ukuran --}}
                        <div class="form-group">
                            <label>Ukuran Default di Dokumen</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" name="default_width_mm" id="default_width_mm" class="form-control" min="20" max="80" value="{{ old('default_width_mm', $sig->default_width_mm ?? 35) }}">
                                        <div class="input-group-append"><span class="input-group-text">mm</span></div>
                                    </div>
                                    <small class="text-muted">Lebar (Rekomendasi: 35)</small>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="number" name="default_height_mm" id="default_height_mm" class="form-control" min="10" max="30" value="{{ old('default_height_mm', $sig->default_height_mm ?? 15) }}">
                                        <div class="input-group-append"><span class="input-group-text">mm</span></div>
                                    </div>
                                    <small class="text-muted">Tinggi (Rekomendasi: 15)</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-s mt-3">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PRATINJAU --}}
            <div class="col-lg-6">
                <div class="card card-settings preview-card">
                    <div class="card-header"><h6 class="mb-0 font-weight-bold"><i class="fas fa-eye mr-2"></i>Pratinjau Langsung</h6></div>
                    <div class="card-body">
                        <div class="preview-pane">
                            <p>Demikian surat tugas ini dibuat untuk dapat dilaksanakan dengan sebaik-baiknya dan penuh tanggung jawab. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>
                            <div class="signature-area">
                                <span>Hormat kami,</span>
                                <div class="signature-image-container">
                                    @php
                                        $sigSrc = 'https://placehold.co/300x150/ffffff/ccc?text=Pratinjau+TTD';
                                        if ($sig?->ttd_path && Storage::disk('local')->exists($sig->ttd_path)) {
                                            $sigSrc = 'data:image/png;base64,'.base64_encode(Storage::disk('local')->get($sig->ttd_path));
                                        }
                                    @endphp
                                    <img id="signature-in-document" src="{{ $sigSrc }}" alt="Pratinjau TTD"
                                         style="width: {{ $sig->default_width_mm ?? 35 }}mm; height: {{ $sig->default_height_mm ?? 15 }}mm;">
                                </div>
                                <div class="signature-name">{{ auth()->user()->nama_lengkap }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadBox = document.getElementById('upload-box-container');
    const fileInput = document.getElementById('ttd_file');
    const filenameDisplay = document.getElementById('filename-display');
    const previewImg = document.getElementById('signature-in-document');
    const widthInput = document.getElementById('default_width_mm');
    const heightInput = document.getElementById('default_height_mm');

    // Klik upload box untuk membuka dialog file
    uploadBox.addEventListener('click', () => fileInput.click());

    // Drag and drop events
    uploadBox.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadBox.classList.add('is-dragging');
    });
    uploadBox.addEventListener('dragleave', () => uploadBox.classList.remove('is-dragging'));
    uploadBox.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadBox.classList.remove('is-dragging');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            // Manual trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    });

    // Handle file selection dan update pratinjau
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.type === 'image/png') {
                filenameDisplay.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                Swal.fire('Format Salah', 'Harap pilih file gambar dengan format PNG.', 'error');
                this.value = ''; // Reset input
            }
        }
    });

    // Live update ukuran pratinjau
    function updatePreviewSize() {
        previewImg.style.width = widthInput.value + 'mm';
        previewImg.style.height = heightInput.value + 'mm';
    }
    widthInput.addEventListener('input', updatePreviewSize);
    heightInput.addEventListener('input', updatePreviewSize);

    // Notifikasi sukses
    @if(session('ok'))
        Swal.fire({
            toast: true, position: 'top-end',
            icon: 'success', title: 'Berhasil!', text: "{{ session('ok') }}",
            showConfirmButton: false, timer: 3000
        });
    @endif
});
</script>
@endpush
