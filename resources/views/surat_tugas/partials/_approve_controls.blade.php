{{-- resources/views/surat_tugas/partials/approve-controls.blade.php --}}
{{-- Panel Kontrol Approve (tanpa offset; fokus ukuran & opacity). 
    Catatan: JANGAN pakai <form> di partial ini. Form utama ada di approve.blade.php --}}
<div class="row g-3">
    <div class="col-md-4">
        <label for="ttd_w_mm" class="form-label fw-semibold">Lebar TTD (mm)</label>
        <input type="number" name="ttd_w_mm" id="ttd_w_mm" class="form-control @error('ttd_w_mm') is-invalid @enderror"
            min="30" max="60" step="1" value="{{ old('ttd_w_mm', $ttdW ?? 42) }}" required>
        @error('ttd_w_mm')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Rentang aman: 30–60 mm (default: 42).</div>
    </div>

    <div class="col-md-4">
        <label for="cap_w_mm" class="form-label fw-semibold">Lebar Cap (mm)</label>
        <input type="number" name="cap_w_mm" id="cap_w_mm"
            class="form-control @error('cap_w_mm') is-invalid @enderror" min="25" max="45" step="1"
            value="{{ old('cap_w_mm', $capW ?? 35) }}" required>
        @error('cap_w_mm')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Rentang aman: 25–45 mm (default: 35).</div>
    </div>

    <div class="col-md-4">
        <label for="cap_opacity" class="form-label fw-semibold">Opasitas Cap</label>
        <input type="number" name="cap_opacity" id="cap_opacity"
            class="form-control @error('cap_opacity') is-invalid @enderror" min="0.70" max="1.00" step="0.01"
            value="{{ old('cap_opacity', $capOpacity ?? 0.95) }}" required>
        @error('cap_opacity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">0.70 (lebih transparan) – 1.00 (solid). Default: 0.95.</div>
    </div>
</div>
