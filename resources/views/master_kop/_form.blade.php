<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Informasi Kop Surat</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nama Kop <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kop" class="form-control @error('nama_kop') is-invalid @enderror"
                                   value="{{ old('nama_kop', $kop->nama_kop ?? '') }}" required
                                   placeholder="Contoh: Kop FIKOM">
                            @error('nama_kop')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Unit</label>
                            <input type="text" name="unit_code" class="form-control @error('unit_code') is-invalid @enderror"
                                   value="{{ old('unit_code', $kop->unit_code ?? '') }}"
                                   placeholder="Contoh: FIKOM">
                            @error('unit_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nama Universitas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_universitas" class="form-control @error('nama_universitas') is-invalid @enderror"
                           value="{{ old('nama_universitas', $kop->nama_universitas ?? 'UNIVERSITAS KATOLIK SOEGIJAPRANATA') }}" required>
                    @error('nama_universitas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Nama Fakultas/Unit <span class="text-danger">*</span></label>
                    <input type="text" name="nama_fakultas" class="form-control @error('nama_fakultas') is-invalid @enderror"
                           value="{{ old('nama_fakultas', $kop->nama_fakultas ?? '') }}" required
                           placeholder="Contoh: FAKULTAS ILMU KOMPUTER">
                    @error('nama_fakultas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Alamat <span class="text-danger">*</span></label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2" required>{{ old('alamat', $kop->alamat ?? '') }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="telepon" class="form-control"
                                   value="{{ old('telepon', $kop->telepon ?? '') }}"
                                   placeholder="024-xxxxxxx">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', $kop->email ?? '') }}"
                                   placeholder="fikom@unika.ac.id">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Website</label>
                            <input type="text" name="website" class="form-control"
                                   value="{{ old('website', $kop->website ?? '') }}"
                                   placeholder="www.unika.ac.id">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Logo Kop</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="logoFile" name="logo_kanan" accept="image/*">
                        <label class="custom-file-label" for="logoFile">Pilih gambar...</label>
                    </div>
                    <small class="text-muted">Format: PNG, JPG. Max 2MB.</small>
                    @if($kop && $kop->logo_kanan_path)
                        <div class="mt-2">
                            <img src="{{ Storage::url($kop->logo_kanan_path) }}" alt="Logo" style="max-height: 60px;">
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="isDefault" name="is_default" value="1"
                               {{ old('is_default', $kop->is_default ?? false) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="isDefault">
                            Jadikan sebagai kop default
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Simpan
                </button>
                <a href="{{ route('kop.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info</h3>
            </div>
            <div class="card-body small">
                <p>Template kop digunakan sebagai header pada surat resmi.</p>
                <p class="mb-0">Kop default akan digunakan otomatis saat membuat surat baru.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    $('#logoFile').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih gambar...');
    });
});
</script>
@endpush
