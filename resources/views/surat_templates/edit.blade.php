@extends('layouts.app')

@section('title', 'Edit Template Surat Tugas')

@push('styles')
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #e8590c 0%, #fd7e14 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(253,126,20,.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #dc6502;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(253,126,20,.13);
    }
    .header-title {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    .header-desc {
        font-size: 1.07rem;
        color: #fef9e7;
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

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-edit fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Edit Template</div>
                <div class="header-desc mt-2">
                    Ubah <b>template Surat Tugas</b> yang sudah ada.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-1"></i> Form Edit Template
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('surat_templates.index') }}" class="btn btn-tool" title="Kembali">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('surat_templates.update', $surat_template) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @include('surat_templates.partials._form', [
                            'surat_template' => $surat_template,
                            'jenisTugasList' => $jenisTugasList,
                            'placeholders' => $placeholders,
                        ])
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('surat_templates.index') }}" class="btn btn-default float-right">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
