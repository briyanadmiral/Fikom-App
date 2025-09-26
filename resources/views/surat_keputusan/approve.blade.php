@extends('layouts.app')

@section('title', 'Tinjau & Setujui Surat Keputusan')

@push('styles')
<style>
    /* Header */
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#ffc107 0,#ff9800 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #ffc1074d; font-size: 2rem;
    }
    .page-header-title { font-weight: bold; color: #785300; font-size: 1.85rem; margin-bottom: 0.13rem; letter-spacing: -1px; }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }

    /* Cards */
    .card-control, .card-preview {
        border: none; border-radius: .8rem;
        box-shadow: 0 4px 25px rgba(0,0,0, .07);
    }
    .card-control .card-header, .card-preview .card-header {
        background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem;
    }

    /* Preview pane */
    #preview-container {
        background-color: #f8f9fa;
        padding: 2rem;
        border-radius: .5rem;
    }
    #previewPane {
        background-color: #fff;
        box-shadow: 0 0 15px rgba(0,0,0,.1);
        position: relative;
    }

    /* Small helpers */
    .dl-plain dt { width: 30%; float: left; clear: left; font-weight: 600; color: #495057; }
    .dl-plain dd { width: 70%; float: left; margin: 0 0 .35rem 0; }
    .text-muted-70 { color: rgba(0,0,0,.6); }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-stamp text-white"></i></span>
    <span>
        <div class="page-header-title">Persetujuan Surat Keputusan</div>
        <div class="page-header-desc">
            Tinjau, atur tata letak TTD/Cap, dan setujui surat
            <b class="text-muted-70">{{ $keputusan->nomor }}</b>.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- KOLOM KIRI: INFO & PANEL PERSETUJUAN --}}
        <div class="col-lg-4 mb-4">
            {{-- Info Surat --}}
            <div class="card card-control mb-4">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>Informasi Surat
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="dl-plain clearfix mb-0">
                        <dt>Nomor</dt><dd>{{ $keputusan->nomor }}</dd>
                        <dt>Tentang</dt><dd>{{ $keputusan->tentang }}</dd>
                        <dt>Pembuat</dt><dd>{{ optional($keputusan->pembuat)->nama_lengkap ?? '-' }}</dd>
                        <dt>Penandatangan</dt><dd>{{ optional($keputusan->penandatanganUser)->nama_lengkap ?? '-' }}</dd>
                        <dt>Status</dt><dd><span class="badge badge-pill badge-warning text-dark">Pending</span></dd>
                    </dl>
                </div>
            </div>

            {{-- Panel Persetujuan (berisi form + slider + tombol approve) --}}
            <div class="card card-control">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-sliders-h mr-2 text-success"></i>Panel Persetujuan
                    </h6>
                </div>
                <div class="card-body">
                    @include('surat_keputusan.partials.approve-controls', [
                        // opsional: kirim nilai awal; kalau tidak dikirim, partial pakai default dari model
                        'keputusan'  => $keputusan,
                        'ttdW'       => $ttdW ?? null,
                        'capW'       => $capW ?? null,
                        'capOpacity' => $capOpacity ?? null,
                    ])

                    {{-- Notifikasi error validasi (jika ada) --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3 py-2 small">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Tautan bantu --}}
                    <div class="mt-3">
                        <a href="{{ route('surat_keputusan.show', $keputusan->id) }}" class="btn btn-light w-100">
                            <i class="fas fa-eye mr-1"></i> Lihat Halaman Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: PRATINJAU --}}
        <div class="col-lg-8">
            <div class="card card-preview">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">
                        <i class="fas fa-file-alt mr-2"></i>Pratinjau Dokumen Final
                    </h6>
                </div>
                <div class="card-body" id="preview-container">
                    <div id="previewPane">
                        {{-- Render awal preview (TTD/Cap tampil; diupdate via AJAX ketika slider digeser) --}}
                        @include('surat_keputusan.partials.approve-preview', [
                            'keputusan'   => $keputusan,
                            'kop'         => $kop ?? null,
                            'ttdImageB64' => $ttdImageB64 ?? null,
                            'capImageB64' => $capImageB64 ?? null,
                            'ttdW'        => $ttdW ?? null,
                            'capW'        => $capW ?? null,
                            'capOpacity'  => $capOpacity ?? null,
                        ])
                    </div>
                </div>
            </div>

            {{-- Tips kecil --}}
            <div class="text-muted small mt-2">
                <i class="fas fa-info-circle"></i>
                Geser slider di panel kiri untuk mengatur ukuran TTD/Cap. Pratinjau akan diperbarui otomatis.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Flash via SweetAlert (opsional) --}}
@if(session('success') || session('error'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: @json(session('success') ? 'success' : 'error'),
        title: @json(session('success') ? 'Berhasil!' : 'Gagal'),
        text: @json(session('success') ?? session('error')),
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif
@endpush
