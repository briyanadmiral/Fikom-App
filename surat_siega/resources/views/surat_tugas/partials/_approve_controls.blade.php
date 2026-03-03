{{-- resources/views/surat_tugas/partials/_approve_controls.blade.php --}}
{{-- Panel Kontrol Approve dengan sliders visual --}}
<div class="row g-3 slider-group">
  {{-- Lebar TTD --}}
  <div class="col-md-4">
    <label for="ttd_w_mm" class="form-label fw-semibold">Lebar TTD (mm)</label>
    <div class="input-group mb-2">
      <input type="number" name="ttd_w_mm" id="ttd_w_mm"
             class="form-control @error('ttd_w_mm') is-invalid @enderror"
             min="10" max="150" step="1" value="{{ old('ttd_w_mm', $ttdW ?? 42) }}" required>
      <span class="input-group-text">mm</span>
      @error('ttd_w_mm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <input type="range" name="ttd_w_mm_slider" min="10" max="150" step="1" value="{{ old('ttd_w_mm', $ttdW ?? 42) }}">
    <div class="form-text">10–150 mm (default: 42)</div>
  </div>

  {{-- Lebar Cap --}}
  <div class="col-md-4">
    <label for="cap_w_mm" class="form-label fw-semibold">Lebar Cap (mm)</label>
    <div class="input-group mb-2">
      <input type="number" name="cap_w_mm" id="cap_w_mm"
             class="form-control @error('cap_w_mm') is-invalid @enderror"
             min="10" max="100" step="1" value="{{ old('cap_w_mm', $capW ?? 35) }}" required>
      <span class="input-group-text">mm</span>
      @error('cap_w_mm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <input type="range" name="cap_w_mm_slider" min="10" max="100" step="1" value="{{ old('cap_w_mm', $capW ?? 35) }}">
    <div class="form-text">10–100 mm (default: 35)</div>
  </div>

  {{-- Opasitas Cap --}}
  <div class="col-md-4">
    <label for="cap_opacity" class="form-label fw-semibold">Opasitas Cap</label>
    <div class="input-group mb-2">
      <input type="number" name="cap_opacity" id="cap_opacity"
             class="form-control @error('cap_opacity') is-invalid @enderror"
             min="0.70" max="1.00" step="0.01" value="{{ old('cap_opacity', $capOpacity ?? 0.95) }}" required>
      @error('cap_opacity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <input type="range" name="cap_opacity_slider" min="0.70" max="1.00" step="0.01" value="{{ old('cap_opacity', $capOpacity ?? 0.95) }}">
    <div class="form-text">0.70–1.00 (default: 0.95)</div>
  </div>

  <!-- Hidden Inputs for Offsets -->
  <input type="hidden" name="ttd_x_mm" value="{{ $preview['ttd_x_mm'] ?? 0 }}">
  <input type="hidden" name="ttd_y_mm" value="{{ $preview['ttd_y_mm'] ?? 0 }}">
  <input type="hidden" name="cap_x_mm" value="{{ $preview['cap_x_mm'] ?? 0 }}">
  <input type="hidden" name="cap_y_mm" value="{{ $preview['cap_y_mm'] ?? 0 }}">
</div>

