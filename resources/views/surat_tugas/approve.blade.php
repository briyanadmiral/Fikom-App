@extends('layouts.app')

@section('title', 'Tinjau & Setujui Surat Tugas')

@push('styles')
    <style>
        /* Menggunakan kembali style header dan komponen dari halaman lain */
        .page-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem;
        }

        .page-header .icon {
            /* Warna oranye/kuning untuk status "menunggu" atau "aksi diperlukan" */
            background: linear-gradient(135deg, #ffc107 0, #ff9800 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #ffc1074d;
            font-size: 2rem;
        }

        .page-header-title {
            font-weight: bold;
            color: #785300;
            font-size: 1.85rem;
            margin-bottom: 0.13rem;
            letter-spacing: -1px;
        }

        .page-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        .card-control,
        .card-preview {
            border: none;
            border-radius: .8rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, .07);
        }

        .card-control .card-header,
        .card-preview .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 1rem 1.5rem;
        }

        /* [BARU] Slider Interaktif */
        .slider-group label {
            font-weight: 600;
        }

        .slider-group .input-group-text {
            font-size: .8rem;
        }

        input[type=range] {
            -webkit-appearance: none;
            margin: 10px 0;
            width: 100%;
            background: transparent;
        }

        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 8px;
            cursor: pointer;
            background: #dee2e6;
            border-radius: 5px;
        }

        input[type=range]::-webkit-slider-thumb {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #007bff;
            cursor: pointer;
            -webkit-appearance: none;
            margin-top: -6px;
            box-shadow: 0 0 5px rgba(0, 0, 0, .2);
        }

        /* [BARU] Kanvas Pratinjau */
        #preview-container {
            background-color: #f8f9fa;
            padding: 2rem;
            border-radius: .5rem;
        }

        #preview-pane {
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, .1);
            position: relative;
        }

        #pv-spinner {
            position: absolute;
            top: 48%;
            left: 48%;
            z-index: 10;
        }
    </style>
@endpush

@section('content_header')
    <div class="page-header mt-2 mb-3">
        <span class="icon"><i class="fas fa-stamp text-white"></i></span>
        <span>
            <div class="page-header-title">Persetujuan Surat Tugas</div>
            <div class="page-header-desc">Tinjau, atur tata letak TTD/Cap, dan setujui surat <b>{{ $tugas->nomor }}</b>.
            </div>
        </span>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <form action="{{ route('surat_tugas.approve', $tugas->id) }}" method="POST" id="form-approve">
            @csrf
            <div class="row">
                {{-- KOLOM KIRI: KONTROL PERSETUJUAN --}}
                <div class="col-lg-4 mb-4">
                    {{-- Kartu Info Surat --}}
                    <div class="card card-control mb-4">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold"><i class="fas fa-info-circle mr-2 text-primary"></i>Informasi
                                Surat</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Nomor</dt>
                                <dd class="col-sm-8">{{ $tugas->nomor }}</dd>
                                <dt class="col-sm-4">Perihal</dt>
                                <dd class="col-sm-8">{{ $tugas->nama_umum }}</dd>
                                <dt class="col-sm-4">Pembuat</dt>
                                <dd class="col-sm-8">{{ optional($tugas->pembuat)->nama_lengkap ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Kartu Panel Persetujuan --}}
                    <div class="card card-control">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold"><i class="fas fa-sliders-h mr-2 text-success"></i>Panel
                                Persetujuan</h6>
                        </div>
                        <div class="card-body">
                            {{-- Partial kontrol yang sudah di-refactor --}}
                            @include('surat_tugas.partials._approve_controls', [
                                'ttdW' => old('ttd_w_mm', $preview['ttd_w_mm']),
                                'capW' => old('cap_w_mm', $preview['cap_w_mm']),
                                'capOpacity' => old('cap_opacity', $preview['cap_opacity']),
                            ])

                            {{-- Inline alert removed in favor of global SweetAlert2 handler --}}

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success btn-lg btn-block">
                                    <i class="fas fa-check-double mr-2"></i>Setujui & Tandatangani
                                </button>
                                <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-block mt-2">
                                    <i class="fas fa-redo mr-1"></i> Reset Ukuran
                                </button>
                                <a href="{{ route('surat_tugas.show', $tugas->id) }}" class="btn btn-light btn-block mt-2">
                                    <i class="fas fa-eye mr-1"></i> Halaman Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: PRATINJAU DOKUMEN --}}
                <div class="col-lg-8">
                    <div class="card card-preview">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Pratinjau Dokumen Final
                            </h6>
                        </div>
                        <div class="card-body" id="preview-container" style="position: relative;">
                            <div id="pv-spinner" class="spinner-border text-primary" style="display:none;"></div>
                            <div id="preview-pane">
                                @include('surat_tugas.partials._approve_preview', [
                                    'tugas' => $tugas,
                                    'kop' => $kop,
                                    'preview' => $preview,
                                    'showSigns' => true,
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
        // --- Controls ---
        const controls = {
            ttd_w_mm: {
                num: document.querySelector('input[name="ttd_w_mm"]'),
                slider: document.querySelector('input[name="ttd_w_mm_slider"]')
            },
            cap_w_mm: {
                num: document.querySelector('input[name="cap_w_mm"]'),
                slider: document.querySelector('input[name="cap_w_mm_slider"]')
            },
            cap_opacity: {
                num: document.querySelector('input[name="cap_opacity"]'),
                slider: document.querySelector('input[name="cap_opacity_slider"]')
            }
        };

        const defaults = {
            ttd_w_mm: "{{ (int)($ttdW ?? 42) }}",
            cap_w_mm: "{{ (int)($capW ?? 35) }}",
            cap_opacity: "{{ (float)($capOpacity ?? 0.95) }}" // Note: This might need blade var if set
        };

        const urlBase = "{{ route('surat_tugas.approvePreview', $tugas->id) }}";
        const btnReset = document.getElementById('btn-reset');
        const spinner = document.getElementById('pv-spinner');
        const pane = document.getElementById('preview-pane');

        // --- Drag & Resize Variables ---
        let activeEl = null;
        let startX = 0, startY = 0;
        let initialOffX = 0, initialOffY = 0;
        
        let activeResizeEl = null;
        let startResizeX = 0;
        let startWidthMm = 0;

        function debounce(fn, wait = 250) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        // --- Helpers ---
        function getMmRatio() {
            const sheet = pane.querySelector('.sheet');
            if (!sheet) return 0.265; // fallback
            return 210 / sheet.getBoundingClientRect().width; // mm per pixel
        }

        function getType(el) {
            return el.classList.contains('cap') ? 'cap' : 'ttd';
        }

        // --- Drag Logic ---
        function initDragAndResize() {
            const ttdEl = pane.querySelector('.ttd');
            const capEl = pane.querySelector('.cap');

            [ttdEl, capEl].forEach(el => {
                if (!el) return;
                
                // DRAG
                el.style.cursor = 'move';
                el.removeEventListener('mousedown', dragStart);
                el.addEventListener('mousedown', dragStart);
                
                // WHEEL RESIZE
                el.removeEventListener('wheel', resizeWheel);
                el.addEventListener('wheel', resizeWheel, {passive: false});
            });

            // RESIZE HANDLE
            const handles = pane.querySelectorAll('.resize-handle');
            handles.forEach(h => {
                h.removeEventListener('mousedown', resizeStart);
                h.addEventListener('mousedown', resizeStart);
            });
        }

        function dragStart(e) {
            if (e.target.classList.contains('resize-handle')) return; // Pass to resizeStart
            e.preventDefault();
            activeEl = e.currentTarget; // The wrapper div
            
            const type = getType(activeEl);
            const inpX = document.querySelector(`input[name="${type}_x_mm"]`);
            const inpY = document.querySelector(`input[name="${type}_y_mm"]`);
            
            initialOffX = parseInt(inpX.value) || 0;
            initialOffY = parseInt(inpY.value) || 0;

            startX = e.clientX;
            startY = e.clientY;

            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
        }

        function dragMove(e) {
            if (!activeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startX;
            const dyPx = startY - e.clientY; // Inverted Y

            const newX = initialOffX + Math.round(dxPx * mmRatio);
            const newY = initialOffY + Math.round(dyPx * mmRatio);

            const type = getType(activeEl);
            const wrapper = activeEl.closest('.ttd-area-sign');
            wrapper.style.setProperty(`--${type}-x`, `${newX}mm`);
            wrapper.style.setProperty(`--${type}-y`, `${newY}mm`);
        }

        function dragEnd(e) {
            if (!activeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startX;
            const dyPx = startY - e.clientY;
            
            const newX = initialOffX + Math.round(dxPx * mmRatio);
            const newY = initialOffY + Math.round(dyPx * mmRatio);
            
            const type = getType(activeEl);
            document.querySelector(`input[name="${type}_x_mm"]`).value = newX;
            document.querySelector(`input[name="${type}_y_mm"]`).value = newY;

            activeEl = null;
            document.removeEventListener('mousemove', dragMove);
            document.removeEventListener('mouseup', dragEnd);
            debouncedLoadPreview();
        }

        // --- Resize Logic ---
        function resizeStart(e) {
            e.stopPropagation();
            e.preventDefault();
            activeResizeEl = e.target.closest('.ttd, .cap');
            
            const type = getType(activeResizeEl);
            const input = document.querySelector(`input[name="${type}_w_mm"]`);
            startWidthMm = parseInt(input.value) || (type === 'ttd' ? 42 : 35);
            startResizeX = e.clientX;

            document.addEventListener('mousemove', resizeMove);
            document.addEventListener('mouseup', resizeEnd);
        }

        function resizeMove(e) {
            if (!activeResizeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startResizeX;
            // 2x factor because center anchored
            const wChange = Math.round(dxPx * mmRatio * 2); 
            let newW = startWidthMm + wChange;
            
            if (newW < 10) newW = 10;
            if (newW > 150) newW = 150;

            const type = getType(activeResizeEl);
            const wrapper = activeResizeEl.closest('.ttd-area-sign');
            wrapper.style.setProperty(`--${type}-w`, `${newW}mm`);
        }

        function resizeEnd(e) {
            if (!activeResizeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startResizeX;
            const wChange = Math.round(dxPx * mmRatio * 2);
            let newW = startWidthMm + wChange;
            if (newW < 10) newW = 10;
            if (newW > 150) newW = 150;

            const type = getType(activeResizeEl);
            const input = document.querySelector(`input[name="${type}_w_mm"]`);
            const slider = document.querySelector(`input[name="${type}_w_mm_slider"]`);
            
            if (input) input.value = newW;
            if (slider) slider.value = newW;

            activeResizeEl = null;
            document.removeEventListener('mousemove', resizeMove);
            document.removeEventListener('mouseup', resizeEnd);
            debouncedLoadPreview();
        }
        
        function resizeWheel(e) {
            e.preventDefault();
            const el = e.currentTarget;
            const type = getType(el);
            const input = document.querySelector(`input[name="${type}_w_mm"]`);
            let currentW = parseInt(input.value) || 40;
            
            // Scroll UP (deltaY < 0) -> Increase
            const delta = e.deltaY < 0 ? 2 : -2; 
            let newW = currentW + delta;
            
            if (newW < 10) newW = 10;
            if (newW > 150) newW = 150;
            
            const wrapper = el.closest('.ttd-area-sign');
            wrapper.style.setProperty(`--${type}-w`, `${newW}mm`);
            
            const slider = document.querySelector(`input[name="${type}_w_mm_slider"]`);
            if (input) input.value = newW;
            if (slider) slider.value = newW;
            
            debouncedLoadPreview();
        }

        // --- Core ---
        function loadPreview() {
            const ttdX = document.querySelector('input[name="ttd_x_mm"]').value;
            const ttdY = document.querySelector('input[name="ttd_y_mm"]').value;
            const capX = document.querySelector('input[name="cap_x_mm"]').value;
            const capY = document.querySelector('input[name="cap_y_mm"]').value;

            const params = new URLSearchParams({
                partial: 'true',
                ttd_w_mm: controls.ttd_w_mm.num.value,
                cap_w_mm: controls.cap_w_mm.num.value,
                cap_opacity: controls.cap_opacity.num.value,
                ttd_x_mm: ttdX, ttd_y_mm: ttdY, cap_x_mm: capX, cap_y_mm: capY
            });

            spinner.style.display = 'block';
            fetch(`${urlBase}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.text())
                .then(html => {
                    pane.innerHTML = html;
                    initDragAndResize(); // Re-init
                })
                .catch(() => {
                    pane.innerHTML = '<div class="text-danger p-5 text-center">Gagal memuat pratinjau.</div>';
                })
                .finally(() => {
                    spinner.style.display = 'none';
                });
        }

        const debouncedLoadPreview = debounce(loadPreview);

        Object.keys(controls).forEach(key => {
            const { num, slider } = controls[key];
            if (num && slider) {
                slider.addEventListener('input', () => {
                    num.value = slider.value;
                    debouncedLoadPreview();
                });
                num.addEventListener('input', () => {
                    slider.value = num.value;
                    debouncedLoadPreview();
                });
            }
        });

        btnReset.addEventListener('click', function() {
            document.querySelector('input[name="ttd_x_mm"]').value = 0;
            document.querySelector('input[name="ttd_y_mm"]').value = 0;
            document.querySelector('input[name="cap_x_mm"]').value = 0;
            document.querySelector('input[name="cap_y_mm"]').value = 0;
            /* Defaults hard reload or reset inputs */
             location.reload(); 
        });
        
        // Init
        initDragAndResize();
    });
</script>
@endpush
