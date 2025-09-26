{{-- resources/views/surat_keputusan/partials/approve-controls.blade.php --}}
@php
    // Nilai awal (prioritas: old() -> param eksplisit -> preview -> fallback)
    $ttdWInit       = old('ttd_w_mm', $ttdW ?? ($preview['ttd_w_mm'] ?? 35));
    $capWInit       = old('cap_w_mm', $capW ?? ($preview['cap_w_mm'] ?? 30));
    $capOpacityInit = old('cap_opacity', $capOpacity ?? ($preview['cap_opacity'] ?? 85));

    // Batasan
    $minTtd = 20;  $maxTtd = 120;
    $minCap = 15;  $maxCap = 120;
    $minOpc = 10;  $maxOpc = 100;
@endphp

<form id="form-approve-sk"
      action="{{ route('surat_keputusan.approve', $keputusan->id) }}"
      method="POST" novalidate>
    @csrf

    {{-- Lebar TTD (mm) --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Lebar TTD (mm)</label>
        <div class="input-group">
            <input type="range"
                   class="form-range flex-fill"
                   name="ttd_w_mm_slider"
                   min="{{ $minTtd }}" max="{{ $maxTtd }}"
                   value="{{ $ttdWInit }}"
                   aria-label="Slider lebar TTD">
            <span class="input-group-text">mm</span>
            <input type="number"
                   class="form-control"
                   name="ttd_w_mm"
                   min="{{ $minTtd }}" max="{{ $maxTtd }}"
                   value="{{ $ttdWInit }}"
                   aria-label="Input lebar TTD (mm)">
        </div>
        <div class="form-text">Saran: 30–60 mm. TTD otomatis diskalakan proporsional.</div>
    </div>

    {{-- Lebar Cap (mm) --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Lebar Cap (mm)</label>
        <div class="input-group">
            <input type="range"
                   class="form-range flex-fill"
                   name="cap_w_mm_slider"
                   min="{{ $minCap }}" max="{{ $maxCap }}"
                   value="{{ $capWInit }}"
                   aria-label="Slider lebar cap">
            <span class="input-group-text">mm</span>
            <input type="number"
                   class="form-control"
                   name="cap_w_mm"
                   min="{{ $minCap }}" max="{{ $maxCap }}"
                   value="{{ $capWInit }}"
                   aria-label="Input lebar cap (mm)">
        </div>
        <div class="form-text">Saran: 25–45 mm.</div>
    </div>

    {{-- Opasitas Cap (%) --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Opasitas Cap (%)</label>
        <div class="input-group">
            <input type="range"
                   class="form-range flex-fill"
                   name="cap_opacity_slider"
                   min="{{ $minOpc }}" max="{{ $maxOpc }}"
                   value="{{ $capOpacityInit }}"
                   aria-label="Slider opasitas cap">
            <span class="input-group-text">%</span>
            <input type="number"
                   class="form-control"
                   name="cap_opacity"
                   min="{{ $minOpc }}" max="{{ $maxOpc }}"
                   value="{{ $capOpacityInit }}"
                   aria-label="Input opasitas cap (%)">
        </div>
        <div class="form-text">Semakin kecil, semakin transparan (watermark).</div>
    </div>

    {{-- Tombol aksi --}}
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-check-double me-1"></i> Setujui & Tandatangani
        </button>
        <button type="button" id="btn-reset-approve" class="btn btn-outline-secondary"
                data-default-ttd="{{ $ttdWInit }}"
                data-default-cap="{{ $capWInit }}"
                data-default-opc="{{ $capOpacityInit }}">
            <i class="fas fa-redo me-1"></i> Reset Ukuran
        </button>
    </div>
</form>

@push('scripts')
<script>
(function(){
    const pane = document.getElementById('previewPane');
    const previewUrl = @json(route('surat_keputusan.approvePreview', $keputusan->id));

    // Ambil elemen kontrol
    const ttdNum  = document.querySelector('input[name="ttd_w_mm"]');
    const ttdSl   = document.querySelector('input[name="ttd_w_mm_slider"]');
    const capNum  = document.querySelector('input[name="cap_w_mm"]');
    const capSl   = document.querySelector('input[name="cap_w_mm_slider"]');
    const opcNum  = document.querySelector('input[name="cap_opacity"]');
    const opcSl   = document.querySelector('input[name="cap_opacity_slider"]');

    const btnReset = document.getElementById('btn-reset-approve');

    function sync(from, to){
        if (!from || !to) return;
        to.value = from.value;
    }

    function qs(params){
        const p = new URLSearchParams(params);
        return p.toString();
    }

    function debounce(fn, wait=250){
        let t; return (...args) => { clearTimeout(t); t = setTimeout(()=>fn(...args), wait); };
    }

    function loadPreview(){
        if (!pane) return;
        const params = {
            ttd_w_mm: ttdNum?.value || '',
            cap_w_mm: capNum?.value || '',
            cap_opacity: opcNum?.value || ''
        };
        pane.style.opacity = .5;
        fetch(previewUrl + '?' + qs(params), { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.text())
            .then(html => { pane.innerHTML = html; })
            .catch(() => { pane.innerHTML = '<div class="text-danger p-5 text-center">Gagal memuat pratinjau.</div>'; })
            .finally(() => { pane.style.opacity = 1; });
    }
    const debouncedLoadPreview = debounce(loadPreview, 200);

    // Sinkronisasi slider <-> number + refresh preview
    [['input','change']].forEach(evts => {
        if (ttdSl && ttdNum){
            evts.forEach(evt => ttdSl.addEventListener(evt, ()=>{ sync(ttdSl, ttdNum); debouncedLoadPreview(); }));
            evts.forEach(evt => ttdNum.addEventListener(evt, ()=>{ sync(ttdNum, ttdSl); debouncedLoadPreview(); }));
        }
        if (capSl && capNum){
            evts.forEach(evt => capSl.addEventListener(evt, ()=>{ sync(capSl, capNum); debouncedLoadPreview(); }));
            evts.forEach(evt => capNum.addEventListener(evt, ()=>{ sync(capNum, capSl); debouncedLoadPreview(); }));
        }
        if (opcSl && opcNum){
            evts.forEach(evt => opcSl.addEventListener(evt, ()=>{ sync(opcSl, opcNum); debouncedLoadPreview(); }));
            evts.forEach(evt => opcNum.addEventListener(evt, ()=>{ sync(opcNum, opcSl); debouncedLoadPreview(); }));
        }
    });

    // Reset ke nilai default (atribut data-*)
    btnReset?.addEventListener('click', () => {
        const dT = btnReset.getAttribute('data-default-ttd') || '';
        const dC = btnReset.getAttribute('data-default-cap') || '';
        const dO = btnReset.getAttribute('data-default-opc') || '';
        if (ttdNum && ttdSl){ ttdNum.value = dT; ttdSl.value = dT; }
        if (capNum && capSl){ capNum.value = dC; capSl.value = dC; }
        if (opcNum && opcSl){ opcNum.value = dO; opcSl.value = dO; }
        loadPreview();
    });
})();
</script>
@endpush
