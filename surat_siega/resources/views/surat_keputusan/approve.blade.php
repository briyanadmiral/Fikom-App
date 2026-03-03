{{-- resources/views/surat_keputusan/approve.blade.php --}}
@extends('layouts.app')

@section('title', 'Tinjau & Setujui Surat Keputusan')

@push('styles')
<style>
    /* Styling Halaman Persetujuan */
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
        margin-bottom: .13rem;
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
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem 1.5rem;
    }

    /* Styling Kontrol Slider */
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

    /* Styling Area Pratinjau */
    #preview-container {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: .5rem;
    }

    #preview-pane {
        background: #fff;
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
        <h1 class="page-header-title">Persetujuan Surat Keputusan</h1>
        <p class="page-header-desc">
            Tinjau, atur tata letak TTD/Cap, setujui atau minta revisi SK <b>{{ $sk->nomor ?? '—' }}</b>.
        </p>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('surat_keputusan.approve', $sk->id) }}" method="POST" id="form-approve">
        @csrf
        <div class="row">

            {{-- Kolom Kiri: Panel Kontrol --}}
            <div class="col-lg-4 mb-4">
                <div class="sticky-top" style="top: 20px;">
                    {{-- Card Informasi Surat --}}
                    <div class="card card-control mb-4">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold">
                                <i class="fas fa-info-circle mr-2 text-primary"></i>Informasi Surat
                            </h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Nomor</dt>
                                <dd class="col-sm-8">{{ $sk->nomor ?? '—' }}</dd>
                                <dt class="col-sm-4">Tentang</dt>
                                <dd class="col-sm-8">{{ $sk->tentang ?? '—' }}</dd>
                                <dt class="col-sm-4">Pembuat</dt>
                                <dd class="col-sm-8">{{ $sk->pembuat?->nama_lengkap ?? 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Card Panel Persetujuan & Aksi --}}
                    <div class="card card-control">
                        <div class="card-header">
                            <h6 class="mb-0 font-weight-bold">
                                <i class="fas fa-sliders-h mr-2 text-success"></i>Panel Persetujuan
                            </h6>
                        </div>
                        <div class="card-body">
                            {{-- Meng-include file partial untuk kontrol slider TTD/Cap --}}
                            @include('surat_keputusan.partials._approve_controls', [
                                'ttdW' => old('ttd_w_mm', $ttdW ?? 42),
                                'capW' => old('cap_w_mm', $capW ?? 35),
                                'capOpacity' => old('cap_opacity', $capOpacity ?? 0.95),
                            ])

                            {{-- Menampilkan pesan error validasi jika ada --}}
                            @if ($errors->any())
                            <div class="alert alert-danger mt-3 py-2 small">
                                @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Grup tombol aksi --}}
                            <div class="mt-4 d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg" id="btn-approve">
                                    <i class="fas fa-check-double mr-2"></i>Setujui & Tandatangani
                                </button>
                                <button type="button" id="btn-reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo mr-1"></i> Reset Ukuran
                                </button>
                                {{-- Tombol Tolak/Revisi hanya muncul jika pengguna adalah penandatangan yang ditunjuk --}}
                                @can('reject', $sk)
                                <button type="button" class="btn btn-danger mt-2" data-toggle="modal" data-target="#rejectModal">
                                    <i class="fas fa-times mr-1"></i> Tolak / Minta Revisi
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Pratinjau Dokumen --}}
            <div class="col-lg-8">
                <div class="card card-preview">
                    <div class="card-header">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Pratinjau Dokumen Final</h6>
                    </div>
                    <div class="card-body" id="preview-container">
                        <div id="preview-pane">
                            {{-- Spinner akan muncul saat pratinjau dimuat ulang --}}
                            <div id="pv-spinner" class="spinner-border text-primary" style="display:none;"></div>
                            <div id="pv-content">
                                {{-- Meng-include file partial untuk pratinjau surat --}}
                                @include('surat_keputusan.partials._approve_preview', [
                                    'sk' => $sk,
                                    'kop' => $kop ?? null,
                                    'showSigns' => true,
                                    'ttdImageB64' => $ttdImageB64 ?? null,
                                    'capImageB64' => $capImageB64 ?? null,
                                    'ttdW' => $ttdW ?? 42,
                                    'capW' => $capW ?? 35,
                                    'capOpacity' => $capOpacity ?? 0.95,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- Modal untuk menolak atau meminta revisi --}}
@can('reject', $sk)
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="rejectForm" method="POST" action="{{ route('surat_keputusan.reject', $sk->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Tolak / Minta Revisi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        Dokumen akan dikembalikan ke pembuat dengan status <b>ditolak</b>. Anda dapat menambahkan catatan perbaikan di bawah ini.
                    </div>
                    <div class="form-group">
                        <label for="rejection-note">Catatan untuk pembuat (opsional)</label>
                        <textarea id="rejection-note" name="note" class="form-control" rows="4" placeholder="Contoh: Mohon perbaiki redaksi KESATU dan lengkapi dasar hukum butir 3."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Permintaan Revisi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Controls & Elements ---
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
            cap_opacity: "{{ (float)($capOpacity ?? 0.95) }}",
            ttd_x_mm: 0, ttd_y_mm: 0, cap_x_mm: 0, cap_y_mm: 0
        };

        const urlBase = "{{ route('surat_keputusan.approvePreview', $sk->id) }}";
        const content = document.getElementById('pv-content'); // Where html is loaded
        const spinner = document.getElementById('pv-spinner');
        const container = document.getElementById('preview-pane');

        // --- Drag & Resize Variables ---
        let activeEl = null;
        let startX = 0, startY = 0;
        let initialOffX = 0, initialOffY = 0;
        
        let activeResizeEl = null;
        let startResizeX = 0;
        let startWidthMm = 0;

        // --- Helpers ---
        function debounce(fn, wait = 250) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        function getMmRatio() {
            const sheet = content.querySelector('.sheet');
            if (!sheet) return 1; 
            return 210 / sheet.clientWidth;
        }
        
        function getType(el) {
            return el.classList.contains('cap') ? 'cap' : 'ttd';
        }

        // --- Init ---
        function initDragAndResize() {
            const ttdEl = content.querySelector('.ttd');
            const capEl = content.querySelector('.cap');

            [ttdEl, capEl].forEach(el => {
                if (!el) return;
                
                // DRAG
                el.style.cursor = 'move';
                el.removeEventListener('mousedown', dragStart);
                el.addEventListener('mousedown', dragStart);
                
                // WHEEL
                el.removeEventListener('wheel', resizeWheel);
                el.addEventListener('wheel', resizeWheel, {passive: false});
            });
            
            // RESIZE HANDLE
            const handles = content.querySelectorAll('.resize-handle');
            handles.forEach(h => {
                h.removeEventListener('mousedown', resizeStart);
                h.addEventListener('mousedown', resizeStart);
            });
        }

        function dragStart(e) {
            if (e.target.classList.contains('resize-handle')) return;
            e.preventDefault();
            activeEl = e.currentTarget;
            
            const type = getType(activeEl);
            initialOffX = parseInt(document.getElementById(`${type}_x_mm`).value) || 0;
            initialOffY = parseInt(document.getElementById(`${type}_y_mm`).value) || 0;

            startX = e.clientX;
            startY = e.clientY;

            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
        }

        function dragMove(e) {
            if (!activeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startX;
            const dyPx = startY - e.clientY;

            const newX = initialOffX + Math.round(dxPx * mmRatio);
            const newY = initialOffY + Math.round(dyPx * mmRatio);

            const type = getType(activeEl);
            const wrapper = activeEl.closest('.ttd-area-sign');
            if (wrapper) {
                wrapper.style.setProperty(`--${type}-x`, `${newX}mm`);
                wrapper.style.setProperty(`--${type}-y`, `${newY}mm`);
            }
        }

        function dragEnd(e) {
            if (!activeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startX;
            const dyPx = startY - e.clientY;
            
            const newX = initialOffX + Math.round(dxPx * mmRatio);
            const newY = initialOffY + Math.round(dyPx * mmRatio);

            const type = getType(activeEl);
            const inputX = document.getElementById(`${type}_x_mm`);
            const inputY = document.getElementById(`${type}_y_mm`);
            if(inputX) inputX.value = newX;
            if(inputY) inputY.value = newY;

            activeEl = null;
            document.removeEventListener('mousemove', dragMove);
            document.removeEventListener('mouseup', dragEnd);
            debouncedLoad(); // Sync preview
        }

        // --- Resize Logic ---
        function resizeStart(e) {
            e.stopPropagation();
            e.preventDefault();
            activeResizeEl = e.target.closest('.ttd, .cap');
            
            const type = getType(activeResizeEl);
            // Controls access
            const ctrl = controls[`${type}_w_mm`];
            startWidthMm = parseInt(ctrl.num.value) || (type === 'ttd' ? 42 : 35);
            startResizeX = e.clientX;

            document.addEventListener('mousemove', resizeMove);
            document.addEventListener('mouseup', resizeEnd);
        }

        function resizeMove(e) {
            if (!activeResizeEl) return;
            const mmRatio = getMmRatio();
            const dxPx = e.clientX - startResizeX;
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
            // Update inputs
            const ctrl = controls[`${type}_w_mm`];
            if(ctrl) {
                if (ctrl.num) ctrl.num.value = newW;
                if (ctrl.slider) ctrl.slider.value = newW;
            }

            activeResizeEl = null;
            document.removeEventListener('mousemove', resizeMove);
            document.removeEventListener('mouseup', resizeEnd);
            debouncedLoad();
        }
        
        function resizeWheel(e) {
            e.preventDefault();
            const el = e.currentTarget;
            const type = getType(el);
            const ctrl = controls[`${type}_w_mm`];
            let currentW = parseInt(ctrl.num.value) || 40;
            
            const delta = e.deltaY < 0 ? 2 : -2; 
            let newW = currentW + delta;
            
            if (newW < 10) newW = 10;
            if (newW > 150) newW = 150;
            
            const wrapper = el.closest('.ttd-area-sign');
            wrapper.style.setProperty(`--${type}-w`, `${newW}mm`);
            
            if(ctrl) {
                if (ctrl.num) ctrl.num.value = newW;
                if (ctrl.slider) ctrl.slider.value = newW;
            }
            
            debouncedLoad();
        }

        // --- Core Functions ---
        function loadPreview() {
            // Collect all params including offsets
            const params = new URLSearchParams({
                ttd_w_mm: controls.ttd_w_mm.num?.value ?? defaults.ttd_w_mm,
                cap_w_mm: controls.cap_w_mm.num?.value ?? defaults.cap_w_mm,
                cap_opacity: controls.cap_opacity.num?.value ?? defaults.cap_opacity,
                ttd_x_mm: document.getElementById('ttd_x_mm')?.value ?? 0,
                ttd_y_mm: document.getElementById('ttd_y_mm')?.value ?? 0,
                cap_x_mm: document.getElementById('cap_x_mm')?.value ?? 0,
                cap_y_mm: document.getElementById('cap_y_mm')?.value ?? 0,
            });

            spinner.style.display = 'block';
            fetch(`${urlBase}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                    initDragAndResize(); 
                })
                .catch(error => {
                    console.error('Error loading preview:', error);
                    content.innerHTML = '<div class="alert alert-danger text-center m-5">Gagal memuat pratinjau. Silakan refresh halaman.</div>';
                })
                .finally(() => {
                    spinner.style.display = 'none';
                });
        }

        const debouncedLoad = debounce(loadPreview);

        // --- Bind Events ---
        Object.values(controls).forEach(({ num, slider }) => {
            if (slider && num) {
                slider.addEventListener('input', () => {
                    num.value = slider.value;
                    debouncedLoad();
                });
                num.addEventListener('input', () => {
                    slider.value = num.value;
                    debouncedLoad();
                });
            }
        });

        document.getElementById('btn-reset')?.addEventListener('click', () => {
             // Reset sliders/inputs to server defaults
            Object.entries(defaults).forEach(([key, value]) => {
                if (controls[key]) {
                    controls[key].num.value = value;
                    controls[key].slider.value = value;
                }
            });
            // Reset offsets to 0
            ['ttd_x_mm', 'ttd_y_mm', 'cap_x_mm', 'cap_y_mm'].forEach(id => {
                const el = document.getElementById(id);
                if(el) el.value = 0;
            });
            
            loadPreview();
        });

        document.getElementById('form-approve')?.addEventListener('submit', function(e) {
            const approveButton = document.getElementById('btn-approve');
            if (approveButton) {
                approveButton.disabled = true;
                approveButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            }
        });

        // Initialize on load
        initDragAndResize();
    });
</script>
@endpush