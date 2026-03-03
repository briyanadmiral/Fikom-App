@extends('layouts.app')

@section('title', 'Edit Dasar Hukum')

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-edit fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Edit Dasar Hukum</div>
                <div class="header-desc mt-2">
                    Ubah data referensi <b>dasar hukum</b> yang sudah ada.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #20c997 0%, #17a2b8 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(23,162,184,.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #138496;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(23,162,184,.13);
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
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">Edit Dasar Hukum</h3>
        </div>
        <form action="{{ route('mengingat_library.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="judul">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" 
                                   value="{{ old('judul', $item->judul) }}" required maxlength="200">
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('kategori', $item->kategori) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="isi">Isi Lengkap <span class="text-danger">*</span></label>
                    <textarea name="isi" id="isi" class="form-control @error('isi') is-invalid @enderror" 
                              rows="4" required maxlength="10000">{{ old('isi', $item->isi) }}</textarea>
                    @error('isi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nomor_referensi">Nomor Referensi</label>
                            <input type="text" name="nomor_referensi" id="nomor_referensi" class="form-control @error('nomor_referensi') is-invalid @enderror" 
                                   value="{{ old('nomor_referensi', $item->nomor_referensi) }}" maxlength="100">
                            @error('nomor_referensi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tanggal_referensi">Tanggal Penetapan</label>
                            <input type="date" name="tanggal_referensi" id="tanggal_referensi" class="form-control @error('tanggal_referensi') is-invalid @enderror" 
                                   value="{{ old('tanggal_referensi', $item->tanggal_referensi?->format('Y-m-d')) }}">
                            @error('tanggal_referensi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    Dasar hukum ini telah digunakan <strong>{{ $item->usage_count }}</strong> kali.
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save mr-1"></i> Update
                </button>
                <a href="{{ route('mengingat_library.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
