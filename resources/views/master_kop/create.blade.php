@extends('layouts.app')
@section('title', 'Tambah Template Kop')

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-plus-circle fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Tambah Template Kop</div>
                <div class="header-desc mt-2">
                    Buat template <b>kop surat</b> baru untuk unit atau fakultas.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,123,255,.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #0056b3;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(0,123,255,.13);
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
    <form action="{{ route('kop.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('master_kop._form', ['kop' => null])
    </form>
</div>
@endsection
