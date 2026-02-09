{{-- resources/views/surat_templates/partials/_form.blade.php --}}
@php
    $isEdit = isset($surat_template) && $surat_template->exists;
@endphp

<div class="row">
    <div class="col-md-12">
        {{-- Nama Template --}}
        <div class="form-group">
            <label for="nama" class="font-weight-bold">
                <i class="fas fa-tag text-primary mr-1"></i> Nama Template <span class="text-danger">*</span>
            </label>
            <input type="text" name="nama" id="nama" 
                   class="form-control form-control-lg @error('nama') is-invalid @enderror" 
                   value="{{ old('nama', $isEdit ? $surat_template->nama : '') }}" 
                   required placeholder="Contoh: Template Undangan Rapat Fakultas">
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Berikan nama yang deskriptif untuk memudahkan pencarian</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        {{-- Jenis Tugas --}}
        <div class="form-group">
            <label for="jenis_tugas_id" class="font-weight-bold">
                <i class="fas fa-folder text-warning mr-1"></i> Jenis Tugas
            </label>
            <select name="jenis_tugas_id" id="jenis_tugas_id" class="form-control select2">
                <option value="">-- Pilih Jenis Tugas --</option>
                @foreach($jenisTugasList as $jenis)
                    <option value="{{ $jenis->id }}" {{ old('jenis_tugas_id', $isEdit ? $surat_template->jenis_tugas_id : null) == $jenis->id ? 'selected' : '' }}>
                        {{ $jenis->nama }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Kategori utama template</small>
        </div>
    </div>
    <div class="col-md-6">
        {{-- Sub Tugas --}}
        <div class="form-group">
            <label for="sub_tugas_id" class="font-weight-bold">
                <i class="fas fa-tasks text-info mr-1"></i> Sub Tugas
            </label>
            <select name="sub_tugas_id" id="sub_tugas_id" class="form-control select2">
                <option value="">-- Pilih Sub Tugas --</option>
                @foreach($subTugasList as $sub)
                    <option value="{{ $sub->id }}" 
                            data-jenis="{{ $sub->jenis_tugas_id }}"
                            {{ old('sub_tugas_id', $isEdit ? $surat_template->sub_tugas_id : null) == $sub->id ? 'selected' : '' }}>
                        {{ $sub->nama }} @if($sub->jenisTugas) ({{ $sub->jenisTugas->nama }}) @endif
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Sub kategori spesifik (opsional)</small>
        </div>
    </div>
</div>

{{-- Deskripsi --}}
<div class="form-group">
    <label for="deskripsi" class="font-weight-bold">
        <i class="fas fa-align-left text-secondary mr-1"></i> Deskripsi
    </label>
    <textarea name="deskripsi" id="deskripsi" rows="2" class="form-control"
              placeholder="Deskripsi singkat tentang template ini untuk mempermudah pencarian...">{{ old('deskripsi', $isEdit ? $surat_template->deskripsi : '') }}</textarea>
    <small class="text-muted">Maksimal 500 karakter</small>
</div>

<hr class="my-4">

{{-- Placeholder Reference --}}
<div class="card border-0 bg-gradient-light mb-4" style="background: linear-gradient(145deg, #f8f9fc 0%, #e8eef8 100%);">
    <div class="card-body">
        <h5 class="mb-3 font-weight-bold text-dark">
            <i class="fas fa-code text-primary mr-2"></i> Placeholder yang Tersedia
        </h5>
        <p class="text-muted small mb-3">
            Klik tombol placeholder untuk menyisipkan kode ke dalam isi template. Placeholder akan diganti secara otomatis saat surat dibuat.
        </p>
        <div class="d-flex flex-wrap gap-2" id="placeholder-buttons">
            @foreach($placeholders as $placeholder => $description)
                <button type="button" class="btn btn-sm btn-outline-primary m-1 placeholder-btn shadow-sm"
                        data-placeholder="{{ $placeholder }}" 
                        data-toggle="tooltip" 
                        data-placement="top"
                        title="{{ $description }}"
                        style="border-radius: 20px; font-family: 'Consolas', monospace; font-size: 0.8rem;">
                    <i class="fas fa-plus-circle mr-1" style="font-size: 0.7rem;"></i>{{ $placeholder }}
                </button>
            @endforeach
        </div>
    </div>
</div>

{{-- Detail Tugas dengan CKEditor --}}
<div class="form-group">
    <label for="detail_tugas_editor" class="font-weight-bold">
        <i class="fas fa-file-alt text-success mr-1"></i> Isi Template (Detail Tugas) <span class="text-danger">*</span>
    </label>
    <div class="card border shadow-sm">
        <div class="card-body p-0">
            <textarea name="detail_tugas" id="detail_tugas_editor">{{ old('detail_tugas', $isEdit ? $surat_template->detail_tugas : '') }}</textarea>
        </div>
    </div>
    @error('detail_tugas')
        <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted mt-2">
        <i class="fas fa-lightbulb text-warning mr-1"></i>
        Tips: Gunakan <code>@{{ placeholder }}</code> untuk bagian yang dinamis. Placeholder akan diganti saat surat dibuat.
    </small>
</div>

{{-- Tembusan --}}
<div class="form-group">
    <label for="tembusan" class="font-weight-bold">
        <i class="fas fa-paper-plane text-purple mr-1"></i> Tembusan (CC)
    </label>
    <textarea name="tembusan" id="tembusan" rows="3" class="form-control"
              placeholder="Yth. Rektor&#10;Yth. Wakil Rektor I&#10;Arsip">{{ old('tembusan', $isEdit ? $surat_template->tembusan : '') }}</textarea>
    <small class="text-muted">Pisahkan dengan baris baru untuk setiap penerima tembusan</small>
</div>

{{-- Live Preview Toggle --}}
<div class="card border-0 bg-light mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1 font-weight-bold text-dark">
                    <i class="fas fa-eye text-info mr-2"></i> Preview Template
                </h6>
                <small class="text-muted">Lihat hasil template dengan data contoh</small>
            </div>
            <button type="button" class="btn btn-info btn-sm" id="btnPreview" onclick="showPreview()">
                <i class="fas fa-search mr-1"></i> Lihat Preview
            </button>
        </div>
        <div id="previewArea" class="mt-3 d-none">
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <strong>Preview Hasil</strong>
                </div>
                <div class="card-body" id="previewContent" style="max-height: 300px; overflow-y: auto;">
                    <!-- Preview content will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 280px;
        border: none !important;
    }
    .ck.ck-editor__main>.ck-editor__editable {
        background: #fff;
    }
    .ck.ck-toolbar {
        background: #f8f9fc !important;
        border-bottom: 1px solid #e3e6f0 !important;
    }
    .placeholder-btn {
        transition: all 0.2s ease;
    }
    .placeholder-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.25) !important;
    }
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78,115,223,.15);
    }
    .gap-2 > * {
        margin: 0.25rem;
    }
</style>
@endpush
@endonce

@push('scripts')
{{-- CKEditor 5 CDN --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    let editorInstance = null;

    $(document).ready(function() {
        // Initialize Select2
        if($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true,
                placeholder: function() {
                    return $(this).data('placeholder') || 'Pilih...';
                }
            });
        }

        // Initialize Tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Initialize CKEditor
        ClassicEditor
            .create(document.querySelector('#detail_tugas_editor'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', '|',
                        'undo', 'redo'
                    ]
                },
                placeholder: 'Tulis isi template surat tugas di sini...',
                language: 'id'
            })
            .then(editor => {
                editorInstance = editor;
                console.log('CKEditor initialized successfully');
            })
            .catch(error => {
                console.error('CKEditor initialization error:', error);
            });

        // Placeholder button click handler
        $('.placeholder-btn').on('click', function() {
            const placeholder = $(this).data('placeholder');
            insertPlaceholderToEditor(placeholder);
            
            // Visual feedback
            $(this).addClass('btn-primary').removeClass('btn-outline-primary');
            setTimeout(() => {
                $(this).addClass('btn-outline-primary').removeClass('btn-primary');
            }, 300);
        });

        // Filter sub tugas based on jenis tugas selection
        $('#jenis_tugas_id').on('change', function() {
            const jenisId = $(this).val();
            filterSubTugas(jenisId);
        });

        // Initial filter on page load if editing
        @if($isEdit && $surat_template->jenis_tugas_id)
            filterSubTugas('{{ $surat_template->jenis_tugas_id }}');
        @endif
    });

    function insertPlaceholderToEditor(placeholder) {
        if (editorInstance) {
            const viewFragment = editorInstance.data.processor.toView(placeholder);
            const modelFragment = editorInstance.data.toModel(viewFragment);
            editorInstance.model.insertContent(modelFragment);
            editorInstance.editing.view.focus();
        }
    }

    function filterSubTugas(jenisId) {
        const $subTugas = $('#sub_tugas_id');
        const currentVal = $subTugas.val();
        
        $subTugas.find('option').each(function() {
            const $opt = $(this);
            const optJenis = $opt.data('jenis');
            
            if (!optJenis || !jenisId || optJenis == jenisId) {
                $opt.prop('disabled', false).show();
            } else {
                $opt.prop('disabled', true).hide();
            }
        });
        
        // Refresh select2
        $subTugas.trigger('change.select2');
    }

    @verbatim
    function showPreview() {
        const content = editorInstance ? editorInstance.getData() : '';
        if (!content.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Template Kosong',
                text: 'Silakan isi template terlebih dahulu.',
                confirmButtonColor: '#4e73df'
            });
            return;
        }

        // Replace placeholders with sample data
        let previewContent = content;
        const sampleData = {
            '{{nama_penerima}}': '<strong class="text-primary">Dr. Contoh Nama, M.Pd.</strong>',
            '{{tanggal}}': '<strong class="text-primary">' + new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) + '</strong>',
            '{{jabatan}}': '<strong class="text-primary">Dekan</strong>',
            '{{nomor_surat}}': '<strong class="text-primary">001/ST/FIKOM/' + new Date().getFullYear() + '</strong>',
            '{{tahun}}': '<strong class="text-primary">' + new Date().getFullYear() + '</strong>',
            '{{bulan}}': '<strong class="text-primary">' + new Date().toLocaleDateString('id-ID', {month: 'long'}) + '</strong>',
            '{{tempat}}': '<strong class="text-primary">Ruang Rapat Fakultas</strong>',
            '{{waktu}}': '<strong class="text-primary">09:00 WIB</strong>'
        };

        for (const [key, value] of Object.entries(sampleData)) {
            previewContent = previewContent.split(key).join(value);
        }

        $('#previewContent').html(previewContent);
        $('#previewArea').removeClass('d-none').hide().slideDown(300);
        
        // Scroll to preview
        $('html, body').animate({
            scrollTop: $('#previewArea').offset().top - 100
        }, 500);
    }
    @endverbatim
</script>
@endpush
