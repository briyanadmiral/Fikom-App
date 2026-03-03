@extends('layouts.app')

@section('title', 'Tambah Menimbang')

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-plus-circle fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Tambah Poin Menimbang</div>
                <div class="header-desc mt-2">
                    Tambahkan poin <b>Menimbang</b> baru untuk digunakan dalam Surat Keputusan.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #6f42c1 0%, #9b59b6 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(111,66,193,.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #8e44ad;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(142,68,173,.13);
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
    .card { border-radius: 1rem; }
    @media (max-width: 575.98px) {
        .custom-header-box { padding: 1.1rem; }
        .header-icon { width: 44px; height: 44px; font-size: 1.2rem; }
        .header-title { font-size: 1.2rem; }
        .header-desc { margin-left: 0; font-size: .98rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Poin Menimbang Baru</h3>
        </div>
        <form action="{{ route('menimbang_library.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="judul">Judul <span class="text-danger">*</span></label>
                    <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" 
                           value="{{ old('judul') }}" required maxlength="200" placeholder="Contoh: Kelancaran Pelaksanaan Seminar">
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Judul singkat untuk identifikasi poin menimbang.</small>
                </div>

                <div class="form-group">
                    <label for="isi">Isi Menimbang <span class="text-danger">*</span></label>
                    <textarea name="isi" id="isi" class="form-control @error('isi') is-invalid @enderror" 
                              rows="4" required maxlength="10000" placeholder="Contoh: bahwa untuk kelancaran pelaksanaan...">{{ old('isi') }}</textarea>
                    @error('isi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Isi lengkap poin menimbang. Biasanya diawali dengan "bahwa..."</small>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" 
                           value="{{ old('kategori') }}" maxlength="50" placeholder="Contoh: akademik, kepegawaian, keuangan">
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Kategori untuk memudahkan pencarian.</small>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
                <a href="{{ route('menimbang_library.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
