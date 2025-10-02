{{-- resources/views/surat_keputusan/partials/approve-controls.blade.php --}}
<div class="row g-3 slider-group">
  {{-- Lebar TTD --}}
  <div class="col-md-12 mb-3">
    <label for="ttd_w_mm" class="form-label">Lebar TTD (mm)</label>
    <div class="input-group mb-2">
      <input type="number" name="ttd_w_mm" id="ttd_w_mm"
             class="form-control @error('ttd_w_mm') is-invalid @enderror"
             min="30" max="60" step="1" value="{{ old('ttd_w_mm', $ttdW ?? 42) }}" required>
      <span class="input-group-text">mm</span>
      @error('ttd_w_mm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <input type="range" name="ttd_w_mm_slider" min="30" max="60" step="1" value="{{ old('ttd_w_mm', $ttdW ?? 42) }}">
    <div class="form-text">30–60 mm (default: 42).</div>
  </div>

  {{-- Lebar Cap --}}
  <div class="col-md-12 mb-3">
    <label for="cap_w_mm" class="form-label">Lebar Cap (mm)</label>
    <div class="input-group mb-2">
      <input type="number" name="cap_w_mm" id="cap_w_mm"
             class="form-control @error('cap_w_mm') is-invalid @enderror"
             min="25" max="45" step="1" value="{{ old('cap_w_mm', $capW ?? 35) }}" required>
      <span class="input-group-text">mm</span>
      @error('cap_w_mm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <input type="range" name="cap_w_mm_slider" min="25" max="45" step="1" value="{{ old('cap_w_mm', $capW ?? 35) }}">
    <div class="form-text">25–45 mm (default: 35).</div>
  </div>

  {{-- Opasitas Cap --}}
  <div class="col-md-12 mb-1">
    <label for="cap_opacity" class="form-label">Opasitas Cap</label>
    <div class="input-group mb-2">
      <input type="number" name="cap_opacity" id="cap_opacity"
             class="form-control @error('cap_opacity') is-invalid @enderror"
             min="0.70" max="1.00" step="0.01" value="{{ old('cap_opacity', $capOpacity ?? 0.95) }}" required>
      @error('cap_opacity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <input type="range" name="cap_opacity_slider" min="0.70" max="1.00" step="0.01" value="{{ old('cap_opacity', $capOpacity ?? 0.95) }}">
    <div class="form-text">0.70 (transparan) – 1.00 (solid). Default: 0.95.</div>
  </div>
</div>
