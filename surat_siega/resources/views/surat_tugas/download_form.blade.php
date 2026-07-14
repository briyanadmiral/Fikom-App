{{-- resources/views/surat_tugas/download_form.blade.php --}}
@extends('layouts.app')
@section('title', 'Download PDF: ' . ($tugas->nomor ?? 'Tanpa Nomor'))

@section('content')
@php
    $friendlyName = 'SuratTugas_' . (preg_replace('/[^a-zA-Z0-9_-]/', '_', $tugas->nomor) ?? 'TanpaNomor') . '.pdf';
    $downloadUrl = route('surat_tugas.downloadPdf', [$tugas->id, $friendlyName]);
@endphp
<div class="container-fluid px-2">
    <div class="row">
        {{-- PANEL KUSTOMISASI (kiri, compact) --}}
        <div class="col-lg-3 col-xl-2 mb-3">
            <div class="sticky-top" style="top:15px">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white py-2">
                        <h6 class="mb-0 small"><i class="fas fa-sliders-h mr-1"></i>Opsi PDF</h6>
                    </div>
                    <div class="card-body py-2 px-3">
                        @if ($showSigns)
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="optTtd" checked>
                                <label class="custom-control-label small" for="optTtd">Tanda Tangan</label>
                            </div>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="optNama" checked>
                                <label class="custom-control-label small" for="optNama">Nama & NPP</label>
                            </div>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="optCap" checked>
                                <label class="custom-control-label small" for="optCap">Cap / Stempel</label>
                            </div>
                            @if ($tugas->signed_pdf_path)
                                <hr class="my-2 border-secondary">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="optForceTemplate">
                                    <label class="custom-control-label small text-warning font-weight-bold" for="optForceTemplate">
                                        <i class="fas fa-file-code mr-1"></i>Gunakan Template
                                    </label>
                                </div>
                            @endif
                            <button type="button" class="btn btn-danger btn-block btn-sm" id="btnDownloadPdf">
                                <i class="fas fa-download mr-1"></i>Download PDF
                            </button>
                        @else
                            <p class="text-muted small mb-2">Draft — tanpa TTD.</p>
                            <a href="{{ $downloadUrl }}?download=1&t={{ time() }}" class="btn btn-danger btn-block btn-sm">
                                <i class="fas fa-download mr-1"></i>Download (Draft)
                            </a>
                        @endif
                    </div>
                </div>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary btn-block mt-2">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
            </div>
        </div>

        {{-- PREVIEW SURAT (kanan, lebih besar + scroll) --}}
        <div class="col-lg-9 col-xl-10">
            @if ($tugas->signed_pdf_path)
                <div class="alert alert-info border-info shadow-sm py-2 px-3 mb-3" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-info-circle fa-lg mr-2 mt-1 text-info"></i>
                        <div>
                            <span class="font-weight-bold text-info small d-block">Dokumen Hasil Import Tersedia</span>
                            <p class="mb-0 small text-dark">
                                Surat tugas ini telah ditimpa dengan file PDF bertanda tangan/cap hasil import. 
                                Tombol <strong>Download PDF</strong> secara default akan mengunduh file import tersebut.
                                <br>
                                <span class="text-muted"><i class="fas fa-check-circle mr-1"></i>Pratinjau di bawah menampilkan file PDF hasil import asli. Aktifkan <strong>Gunakan Template</strong> untuk memodifikasi template sistem.</span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold"><i class="fas fa-file-alt mr-1 text-primary"></i>Pratinjau Surat Tugas</span>
                    @if (!$showSigns)
                        <span class="badge badge-warning small">Draft</span>
                    @endif
                </div>
                <div class="card-body p-2 bg-light" style="overflow-x:auto">
                    @if ($tugas->signed_pdf_path)
                        <div id="pdfPreview" class="w-100" style="height: 750px;">
                            <iframe src="{{ $downloadUrl }}?t={{ time() }}" class="w-100 h-100" style="border: none;"></iframe>
                        </div>
                    @endif
                    <div id="htmlPreview" @if ($tugas->signed_pdf_path) style="display: none;" @endif>
                        @include('surat_tugas.partials._core', [
                            'context' => 'web',
                            'tugas' => $tugas,
                            'kop' => $kop ?? null,
                            'penerimaList' => $penerimaList ?? null,
                            'ttdW' => $tugas->ttd_w_mm ?? ($ttdW ?? 42),
                            'capW' => $tugas->cap_w_mm ?? ($capW ?? 35),
                            'capOpacity' => $tugas->cap_opacity ?? ($capOpacity ?? 0.95),
                            'ttdImageB64' => $showSigns ? ($ttdImageB64 ?? null) : null,
                            'capImageB64' => $showSigns ? ($capImageB64 ?? null) : null,
                            'showSigns' => $showSigns,
                            'showKopInContent' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('btnDownloadPdf').addEventListener('click', function() {
        var ttd = document.getElementById('optTtd').checked ? '1' : '0';
        var nama = document.getElementById('optNama').checked ? '1' : '0';
        var cap = document.getElementById('optCap').checked ? '1' : '0';
        
        var hasImported = @json(!empty($tugas->signed_pdf_path));
        var forceTemplate = document.getElementById('optForceTemplate') && document.getElementById('optForceTemplate').checked;
        var isCustomized = (ttd !== '1' || nama !== '1' || cap !== '1');

        var url = "{{ $downloadUrl }}";
        var separator = url.indexOf('?') !== -1 ? '&' : '?';

        if (hasImported && !forceTemplate && !isCustomized) {
            url += separator + "download=1";
        } else {
            url += separator + "ttd=" + ttd + "&nama=" + nama + "&cap=" + cap + "&download=1";
        }
        
        url += "&t=" + new Date().getTime();

        window.open(url, '_blank');
    });

    // Real-time Preview Update
    function updatePreviewVisibility() {
        var optTtd = document.getElementById('optTtd');
        var optNama = document.getElementById('optNama');
        var optCap = document.getElementById('optCap');
        var optForceTemplate = document.getElementById('optForceTemplate');
        
        var ttd = optTtd ? optTtd.checked : true;
        var nama = optNama ? optNama.checked : true;
        var cap = optCap ? optCap.checked : true;
        var forceTemplate = optForceTemplate && optForceTemplate.checked;
        
        var hasImported = @json(!empty($tugas->signed_pdf_path));
        
        // If user customized (unchecked anything), or forced template, we render template.
        var useTemplate = !hasImported || forceTemplate || !ttd || !nama || !cap;

        if (hasImported) {
            var pdfPreview = document.getElementById('pdfPreview');
            var htmlPreview = document.getElementById('htmlPreview');
            if (pdfPreview && htmlPreview) {
                if (useTemplate) {
                    pdfPreview.style.display = 'none';
                    htmlPreview.style.display = 'block';
                } else {
                    pdfPreview.style.display = 'block';
                    htmlPreview.style.display = 'none';
                }
            }
        }
        
        // 1. Tanda Tangan
        var ttdEl = document.querySelector('.ttd-area-sign .ttd');
        if (ttdEl) {
            ttdEl.style.display = ttd ? 'block' : 'none';
        }
        
        // 2. Cap / Stempel
        var capEl = document.querySelector('.ttd-area-sign .cap');
        if (capEl) {
            capEl.style.display = cap ? 'block' : 'none';
        }
        
        // 3. Nama & NPP
        var areaSign = document.querySelector('.ttd-area-sign');
        if (areaSign) {
            var namaNppEl = areaSign.nextElementSibling;
            if (namaNppEl && namaNppEl.classList.contains('ttd-teks')) {
                namaNppEl.style.display = nama ? 'block' : 'none';
            }
        }
    }

    // Add event listeners
    ['optTtd', 'optNama', 'optCap', 'optForceTemplate'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', updatePreviewVisibility);
        }
    });

    // Initialize visibility on load
    updatePreviewVisibility();
</script>
@endpush
