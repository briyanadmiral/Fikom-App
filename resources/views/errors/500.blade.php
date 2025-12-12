@extends('layouts.app')

@section('title', '500 - Kesalahan Server')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-6 text-center">
            <div class="error-page">
                <h2 class="headline text-danger" style="font-size: 100px; font-weight: 700;">
                    <i class="fas fa-server"></i> 500
                </h2>
                <div class="error-content mt-4">
                    <h3>
                        <i class="fas fa-exclamation-circle text-danger"></i> 
                        Oops! Terjadi Kesalahan Server
                    </h3>
                    <p class="lead text-muted mt-3">
                        Maaf, terjadi kesalahan pada server kami.
                        <br>
                        Tim teknis kami telah diberitahu dan sedang memperbaikinya.
                    </p>
                    <div class="mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Halaman Utama
                        </a>
                        <button onclick="location.reload();" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .error-page .headline {
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    .error-page .error-content h3 {
        color: #333;
    }
</style>
@endpush
