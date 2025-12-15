{{-- resources/views/surat_templates/partials/_form.blade.php --}}
@php
    $isEdit = isset($surat_template) && $surat_template->exists;
@endphp

<div class="row">
    <div class="col-md-8">
        {{-- Nama Template --}}
        <div class="form-group">
            <label for="nama">Nama Template <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="nama" 
                   class="form-control @error('nama') is-invalid @enderror" 
                   value="{{ old('nama', $isEdit ? $surat_template->nama : '') }}" 
                   required placeholder="Contoh: Template Undangan Rapat">
            @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        {{-- Jenis Tugas --}}
        <div class="form-group">
            <label for="jenis_tugas_id">Kategori (Jenis Tugas)</label>
            <select name="jenis_tugas_id" id="jenis_tugas_id" class="form-control select2">
                <option value="">-- Pilih Jenis Tugas --</option>
                @foreach($jenisTugasList as $jenis)
                    <option value="{{ $jenis->id }}" {{ old('jenis_tugas_id', $isEdit ? $surat_template->jenis_tugas_id : null) == $jenis->id ? 'selected' : '' }}>
                        {{ $jenis->nama }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- Deskripsi --}}
<div class="form-group">
    <label for="deskripsi">Deskripsi</label>
    <textarea name="deskripsi" id="deskripsi" rows="2" class="form-control"
              placeholder="Deskripsi singkat tentang template ini...">{{ old('deskripsi', $isEdit ? $surat_template->deskripsi : '') }}</textarea>
</div>

{{-- Placeholder Reference --}}
<div class="callout callout-info">
    <h5><i class="fas fa-info-circle mr-1"></i> Placeholder yang Tersedia</h5>
    <p class="text-muted small mb-2">
        Klik tombol di bawah ini untuk menyisipkan placeholder ke dalam detail tugas.
    </p>
    <div class="btn-group flex-wrap" id="placeholder-buttons">
        @foreach($placeholders as $placeholder => $description)
            <button type="button" class="btn btn-outline-info btn-xs mb-1 mr-1 placeholder-btn"
                    data-placeholder="{{ $placeholder }}" title="{{ $description }}">
                {{ $placeholder }}
            </button>
        @endforeach
    </div>
</div>

{{-- Detail Tugas dengan CKEditor --}}
<div class="form-group">
    <label for="detail_tugas_editor">Isi Template (Detail Tugas) <span class="text-danger">*</span></label>
    <textarea name="detail_tugas" id="detail_tugas_editor">{{ old('detail_tugas', $isEdit ? $surat_template->detail_tugas : '') }}</textarea>
    @error('detail_tugas')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">
        Gunakan <code>&#123;&#123;placeholder&#125;&#125;</code> untuk bagian yang dinamis (misal: <code>&#123;&#123;nama_penerima&#125;&#125;</code>).
    </small>
</div>

{{-- Tembusan --}}
<div class="form-group">
    <label for="tembusan">Tembusan (CC)</label>
    <textarea name="tembusan" id="tembusan" rows="3" class="form-control"
              placeholder="1. Rektor&#10;2. Arsip">{{ old('tembusan', $isEdit ? $surat_template->tembusan : '') }}</textarea>
</div>

@once
@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 250px;
    }
    .placeholder-btn {
        transition: all 0.2s ease;
    }
    .placeholder-btn:hover {
        transform: scale(1.05);
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
                width: '100%'
            });
        }

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
        });
    });

    function insertPlaceholderToEditor(placeholder) {
        if (editorInstance) {
            const viewFragment = editorInstance.data.processor.toView(placeholder);
            const modelFragment = editorInstance.data.toModel(viewFragment);
            editorInstance.model.insertContent(modelFragment);
            editorInstance.editing.view.focus();
        }
    }
</script>
@endpush
