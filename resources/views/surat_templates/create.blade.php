@extends('layouts.app')

@section('title', 'Buat Template Surat Tugas')

@push('styles')
<style>
    body { background: #f4f7fb; }
    
    /* === MODERN HEADER === */
    .page-header {
        background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
        color: #fff;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 40px rgba(253,126,20,0.2);
        position: relative;
        overflow: hidden;
    }
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .page-header .icon-box {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.2);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .page-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    /* === FORM CARD === */
    .form-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .form-card-header {
        background: linear-gradient(135deg, #f8f9fc 0%, #fff 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .form-card-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        color: #2d3436;
    }
    .form-card-body {
        padding: 2rem;
    }
    .form-card-footer {
        background: #f8f9fc;
        padding: 1.25rem 2rem;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* === BUTTONS === */
    .btn-save {
        border-radius: 12px;
        padding: 0.8rem 2rem;
        font-weight: 600;
        background: linear-gradient(135deg, #1cc88a 0%, #169b6b 100%);
        border: none;
        color: #fff;
        box-shadow: 0 4px 15px rgba(28,200,138,0.3);
    }
    .btn-save:hover {
        background: linear-gradient(135deg, #169b6b 0%, #13855c 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(28,200,138,0.4);
    }
    .btn-back {
        border-radius: 12px;
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        background: #fff;
        border: 2px solid #e9ecef;
        color: #6c757d;
    }
    .btn-back:hover {
        background: #f8f9fc;
        border-color: #ced4da;
        color: #495057;
    }
    
    /* === RESPONSIVE === */
    @media (max-width: 767.98px) {
        .page-header { padding: 1.5rem; }
        .page-header h1 { font-size: 1.35rem; }
        .form-card-body { padding: 1.25rem; }
        .form-card-footer { 
            flex-direction: column;
            gap: 1rem;
        }
        .form-card-footer .btn { width: 100%; }
    }
</style>
@endpush

@section('content_header')
    <div class="page-header mt-2">
        <div class="d-flex align-items-center">
            <div class="icon-box mr-3">
                <i class="fas fa-plus-circle text-white"></i>
            </div>
            <div>
                <h1>Buat Template Baru</h1>
                <p>Buat template Surat Tugas untuk mempercepat pembuatan surat berulang</p>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid px-2">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="form-card">
                <div class="form-card-header">
                    <h3>
                        <i class="fas fa-edit text-primary mr-2"></i> Form Template Baru
                    </h3>
                    <a href="{{ route('surat_templates.index') }}" class="btn btn-sm btn-light" title="Kembali ke Daftar">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
                
                <form method="POST" action="{{ route('surat_templates.store') }}" id="templateForm">
                    @csrf
                    <div class="form-card-body">
                        @include('surat_templates.partials._form', [
                            'surat_template' => null,
                            'jenisTugasList' => $jenisTugasList,
                            'subTugasList' => $subTugasList,
                            'placeholders' => $placeholders,
                        ])
                    </div>
                    
                    <div class="form-card-footer">
                        <a href="{{ route('surat_templates.index') }}" class="btn btn-back">
                            <i class="fas fa-arrow-left mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save mr-1"></i> Simpan Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
