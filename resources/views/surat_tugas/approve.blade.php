{{-- resources/views/surat_keputusan/approve.blade.php --}}
@extends('layouts.app')

@section('title', 'Tinjau & Setujui Surat Keputusan')

@push('styles')
<style>
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

    .card-control, .card-preview {
        border: none; border-radius: .8rem;
        box-shadow: 0 4px 25px rgba(0,0,0, .07);
    }
    .card-control .card-header, .card-preview .card-header {
        background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem;
    }

    /* Slider */
    .slider-group label { font-weight: 600; }
    .slider-group .input-group-text { font-size: .8rem; }
    input[type=range] { -webkit-appearance: none; margin: 10px 0; width: 100%; background: transparent;}
    input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 8px; cursor: pointer; background: #dee2e6; border-radius: 5px; }
    input[type=range]::-webkit-slider-thumb { height: 20px; width: 20px; border-radius: 50%; background: #007bff; cursor: pointer; -webkit-appearance: none; margin-top: -6px; box-shadow: 0 0 5px rgba(0,0,0,.2); }

    /* Preview */
    #preview-container { background-color: #f8f9fa; padding: 2rem; border-radius: .5rem; }
    #preview-pane { background-color: #fff; box-shadow: 0 0 15px rgba(0,0,0,.1); position: relative; }
    #pv-spinner { position: absolute; top: 48%; left: 48%; z-index: 10; }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-stamp text-white"></i></span>
    <span>
        <div class="page-header-title">Persetujuan Surat Keputusan</div>
        <div class="page-header-desc">
            Tinjau, atur tata letak TTD/Cap, dan setujui SK <b>{{ $keputusan->nomor }}</b>.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('surat_keputusan.approve', $keputusan->id) }}" method="POST" id="form-approve">
        @csrf
        <div class="row">
            {{-- KOLOM KIRI: KONTROL --}}
            <div class="col-lg-4 mb-4">

                {{-- Info SK --}}
                <div class="card card-control mb-4">
                    <div class="card-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-info-circle mr-2 text-primary"></i>Informasi Surat
                        </h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nomor</dt><dd class="col-sm-8">{{ $keputusan->nomor }}</dd>
                            <dt class="col-sm-4">Tentang</dt><dd class="col-sm-8">{{ $keputusan->tentang }}</dd>
                            <dt class="col-sm-4">Pembuat</dt><dd class="col-sm-8">{{ optional($keputusan->pembuat)->nama_lengkap ?? 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>

                {{-- Panel Persetujuan --}}
                <div class="card card-control">
                    <div class="card-header">
                        <h6 class="mb-0 font-weight-bold">
                            <i class="fas fa-sliders-h mr-2 text-success"></i>Panel Persetujuan
                        </h6>
                    </div>
                    <div class="card-body">

                        {{-- Partial kontrol (pastikan file ini ada di folder SK) --}}
                        @include('surat_keputusan.partials.approve-controls', [
                            'ttdW'       => old('ttd_w_mm', $ttdW),
                            'capW'       => old('cap_w_mm', $capW),
                            'capOpacity' => old('cap_opacity', $capOpacity),
                        ])

                        @if ($errors->any())
                        <div class="alert alert-danger mt-3 py-2 small">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-check-double mr-2"></i>Setujui & Tandatangani
                            </button>
                            <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-block mt-2">
                                <i class="fas fa-redo mr-1"></i> Reset Ukuran
                            </button>
                            <a href="{{ route('surat_keputusan.show', $keputusan->id) }}" class="btn btn-light btn-block mt-2">
                                <i class="fas fa-eye mr-1"></i> Halaman Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PRATINJAU --}}
            <div class="col-lg-8">
                <div class="card card-preview">
                    <div class="card-header">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Pratinjau Dokumen Final</h6>
                    </div>
                    <div class="card-body" id="preview-container">
                        <div id="preview-pane">
                            <div id="pv-spinner" class="spinner-border text-primary" style="display:none;"></div>
                            @include('surat_keputusan.partials.approve-preview', [
                                'keputusan'   => $keputusan,
                                'kop'         => $kop ?? null,
                                'showSigns'   => true,
                                'ttdImageB64' => $ttdImageB64 ?? null,
                                'capImageB64' => $capImageB64 ?? null,
                                'ttdW'        => $ttdW ?? 42,
                                'capW'        => $capW ?? 35,
                                'capOpacity'  => $capOpacity ?? 0.95,
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pane    = document.getElementById('preview-pane');
    const spinner = document.getElementById('pv-spinner');

    // Ambil semua elemen kontrol
    const controls = {
        ttd_w_mm:    { num: document.querySelector('input[name="ttd_w_mm"]'),    slider: document.querySelector('input[name="ttd_w_mm_slider"]') },
        cap_w_mm:    { num: document.querySelector('input[name="cap_w_mm"]'),    slider: document.querySelector('input[name="cap_w_mm_slider"]') },
        cap_opacity: { num: document.querySelector('input[name="cap_opacity"]'), slider: document.querySelector('input[name="cap_opacity_slider"]') }
    };

    // Nilai default dari server
    const defaults = {
        ttd_w_mm:    "{{ (int)($ttdW ?? 42) }}",
        cap_w_mm:    "{{ (int)($capW ?? 35) }}",
        cap_opacity: "{{ (float)($capOpacity ?? 0.95) }}"
    };

    // endpoint preview khusus SK
    const urlBase = "{{ route('surat_keputusan.approvePreview', $keputusan->id) }}";

    function debounce(fn, wait = 250) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(this,args), wait); };
    }

    function loadPreview() {
        const params = new URLSearchParams({
            ttd_w_mm:    controls.ttd_w_mm.num?.value ?? defaults.ttd_w_mm,
            cap_w_mm:    controls.cap_w_mm.num?.value ?? defaults.cap_w_mm,
            cap_opacity: controls.cap_opacity.num?.value ?? defaults.cap_opacity,
        });

        spinner.style.display = 'block';

        fetch(`${urlBase}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => { pane.innerHTML = html; })
            .catch(() => { pane.innerHTML = '<div class="text-danger p-5 text-center">Gagal memuat pratinjau. Coba refresh halaman.</div>'; })
            .finally(() => { spinner.style.display = 'none'; });
    }

    const debouncedLoad = debounce(loadPreview);

    // Sinkronisasi slider <-> input number
    Object.keys(controls).forEach(k => {
        const { num, slider } = controls[k];
        if (slider && num) {
            slider.addEventListener('input', () => { num.value = slider.value; debouncedLoad(); });
            num.addEventListener('input',    () => { slider.value = num.value; debouncedLoad(); });
        }
    });

    // Reset ke default
    const btnReset = document.getElementById('btn-reset');
    btnReset?.addEventListener('click', () => {
        Object.keys(controls).forEach(k => {
            const { num, slider } = controls[k];
            if (num)    num.value    = defaults[k];
            if (slider) slider.value = defaults[k];
        });
        loadPreview();
    });
});
</script>
@endpush
