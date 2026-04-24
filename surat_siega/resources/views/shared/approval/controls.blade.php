@php
    $ttdW = old('ttd_w_mm', $ttdW ?? 42);
    $capW = old('cap_w_mm', $capW ?? 35);
    $capOpacity = old('cap_opacity', $capOpacity ?? 0.95);
@endphp

<div class="row approval-control">
    <div class="col-md-12 mb-3">
        <label for="ttd_w_mm" class="form-label">Lebar TTD</label>
        <div class="input-group mb-2">
            <input type="number" name="ttd_w_mm" id="ttd_w_mm" class="form-control @error('ttd_w_mm') is-invalid @enderror"
                min="10" max="150" step="1" value="{{ $ttdW }}" required>
            <span class="input-group-text">mm</span>
        </div>
        <input type="range" name="ttd_w_mm_slider" min="10" max="150" step="1" value="{{ $ttdW }}">
    </div>

    <div class="col-md-12 mb-3">
        <label for="cap_w_mm" class="form-label">Lebar Cap</label>
        <div class="input-group mb-2">
            <input type="number" name="cap_w_mm" id="cap_w_mm" class="form-control @error('cap_w_mm') is-invalid @enderror"
                min="10" max="100" step="1" value="{{ $capW }}" required>
            <span class="input-group-text">mm</span>
        </div>
        <input type="range" name="cap_w_mm_slider" min="10" max="100" step="1" value="{{ $capW }}">
    </div>

    <div class="col-md-12 mb-3">
        <label for="cap_opacity" class="form-label">Opasitas Cap</label>
        <div class="input-group mb-2">
            <input type="number" name="cap_opacity" id="cap_opacity" class="form-control @error('cap_opacity') is-invalid @enderror"
                min="0.70" max="1.00" step="0.01" value="{{ $capOpacity }}" required>
        </div>
        <input type="range" name="cap_opacity_slider" min="0.70" max="1.00" step="0.01" value="{{ $capOpacity }}">
    </div>
</div>

<input type="hidden" name="ttd_x_mm" id="ttd_x_mm" value="{{ $ttdX ?? ($preview['ttd_x_mm'] ?? 0) }}">
<input type="hidden" name="ttd_y_mm" id="ttd_y_mm" value="{{ $ttdY ?? ($preview['ttd_y_mm'] ?? 0) }}">
<input type="hidden" name="cap_x_mm" id="cap_x_mm" value="{{ $capX ?? ($preview['cap_x_mm'] ?? 0) }}">
<input type="hidden" name="cap_y_mm" id="cap_y_mm" value="{{ $capY ?? ($preview['cap_y_mm'] ?? 0) }}">
