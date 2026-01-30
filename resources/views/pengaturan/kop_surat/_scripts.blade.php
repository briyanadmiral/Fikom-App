{{-- resources/views/pengaturan/kop_surat/_scripts.blade.php --}}
{{-- Extracted JavaScript for Kop Surat settings page --}}

@push('scripts')
<script>
(function(){
    function toggleSections() {
        const modeType = document.querySelector('input[name="mode_type"]:checked')?.value || 'custom';
        const sectionCustom = document.getElementById('section_custom');
        const sectionUpload = document.getElementById('section_upload');

        if (!sectionCustom || !sectionUpload) return;

        if (modeType === 'custom') {
            sectionCustom.style.display = 'block';
            sectionCustom.classList.add('active');
            sectionUpload.style.display = 'none';
            sectionUpload.classList.remove('active');
        } else {
            sectionCustom.style.display = 'none';
            sectionCustom.classList.remove('active');
            sectionUpload.style.display = 'block';
            sectionUpload.classList.add('active');
        }
    }

    function updateRadioCards() {
        document.querySelectorAll('.radio-card').forEach(card => card.classList.remove('active'));
        const checkedRadio = document.querySelector('input[name="mode_type"]:checked');
        if (checkedRadio) {
            const parent = checkedRadio.closest('.radio-card');
            if (parent) parent.classList.add('active');
        }
    }

    function updateFileLabels() {
        document.querySelectorAll('.custom-file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Pilih file...';
                const label = e.target.nextElementSibling;
                if (label) label.textContent = fileName;
            });
        });
    }

    function setupRangeSliders() {
        document.querySelectorAll('.range-slider').forEach(slider => {
            const target = document.querySelector(slider.dataset.target);
            const unit = slider.dataset.unit || '%';

            slider.addEventListener('input', function() {
                if (target) target.textContent = this.value + unit;
            });
        });
    }

    const colorInput = document.getElementById('text_color');
    if(colorInput) {
        colorInput.addEventListener('input', function() {
            const hex = document.getElementById('text_color_hex');
            if (hex) hex.textContent = this.value.toUpperCase();
        });
    }

    function setupZoomControls() {
        const zoomButtons = document.querySelectorAll('.zoom-btn');
        const a4Preview = document.getElementById('a4-preview-container');
        if (!a4Preview) return;

        zoomButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const scale = this.dataset.scale;
                a4Preview.style.transform = `scale(${scale})`;

                zoomButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // Handle delete image
    function setupDeleteImage() {
        document.querySelectorAll('.delete-image-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const typeName = type === 'logo' ? 'Logo' : (type === 'cap' ? 'Cap' : 'Background');

                Swal.fire({
                    title: `Hapus ${typeName}?`,
                    text: `Gambar ${typeName.toLowerCase()} akan dihapus secara permanen!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times mr-1"></i> Batal',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-danger mx-1',
                        cancelButton: 'btn btn-secondary mx-1'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading()
                        });

                        const baseUrl = "{{ route('kop.delete-image', ['type' => 'REPLACE_ME']) }}";
                        const deleteUrl = baseUrl.replace('REPLACE_ME', type);

                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Gambar berhasil dihapus',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => window.location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message || 'Gagal menghapus gambar'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                        });
                    }
                });
            });
        });
    }

    // ===== Preset Selector =====
    function setupPresetSelector() {
        const presetSelect = document.getElementById('presetSelector');
        if (!presetSelect) return;

        presetSelect.addEventListener('change', function() {
            if (!this.value) return;

            Swal.fire({
                title: 'Terapkan Preset?',
                text: 'Pengaturan form akan diubah sesuai preset. Anda tetap harus menyimpan untuk menyimpan perubahan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terapkan',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('{{ route("kop.apply-preset") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ preset: this.value })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.config) {
                            // Apply config to form fields
                            Object.keys(data.config).forEach(key => {
                                const el = document.querySelector(`[name="${key}"]`);
                                if (el) {
                                    if (el.type === 'checkbox') {
                                        el.checked = !!data.config[key];
                                    } else if (el.type === 'radio') {
                                        document.querySelector(`[name="${key}"][value="${data.config[key]}"]`)?.click();
                                    } else {
                                        el.value = data.config[key];
                                    }
                                }
                            });

                            // Update range slider displays
                            document.querySelectorAll('.range-slider').forEach(slider => {
                                const target = document.querySelector(slider.dataset.target);
                                const unit = slider.dataset.unit || '%';
                                if (target) target.textContent = slider.value + unit;
                            });

                            Swal.fire({
                                icon: 'success',
                                title: `Preset "${data.name}" diterapkan!`,
                                text: 'Klik Simpan untuk menyimpan perubahan.',
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({ icon: 'error', title: 'Gagal menerapkan preset' });
                    });
                }
                this.value = ''; // Reset selector
            });
        });
    }

    // ===== Import Functionality =====
    function setupImport() {
        const btnImport = document.getElementById('btnImport');
        const importFile = document.getElementById('importFile');
        if (!btnImport || !importFile) return;

        btnImport.addEventListener('click', () => importFile.click());

        importFile.addEventListener('change', function() {
            if (!this.files.length) return;

            const formData = new FormData();
            formData.append('file', this.files[0]);

            Swal.fire({
                title: 'Mengimport...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('{{ route("kop.import") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Berhasil!',
                        text: 'Halaman akan di-refresh.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Import Gagal', text: data.message });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Import Gagal', text: 'Terjadi kesalahan' });
            });

            this.value = ''; // Reset file input
        });
    }

    // ===== Paper Size Selector =====
    function setupPaperSizeSelector() {
        const paperSelect = document.getElementById('paperSizeSelector');
        const a4Preview = document.getElementById('a4-preview-container');
        if (!paperSelect || !a4Preview) return;

        paperSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const width = selected.dataset.width;
            const height = selected.dataset.height;
            
            a4Preview.style.width = width;
            a4Preview.style.height = height;
        });
    }

    // ===== Preview Refresh Button Handler =====
    function setupPreviewRefresh() {
        const btn = document.getElementById('btnRefreshPreview');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                triggerPreview();
            });
        }
    }

    // ===== Trigger Preview (Shared Function) =====
    function triggerPreview() {
        const form = document.getElementById('formKopSurat');
        const previewContainer = document.getElementById('a4-preview-container');
        const loader = document.getElementById('previewLoader');
        
        if (!form || !previewContainer) {
            console.error('Form or preview container not found');
            return;
        }

        if (loader) loader.style.display = 'inline-block';

        // Create FormData but EXCLUDE _method field
        const formData = new FormData(form);
        formData.delete('_method');
        
        const previewUrl = '{{ route("kop.preview") }}';

        fetch(previewUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.text();
        })
        .then(html => {
            let headerContainer = previewContainer.querySelector('.kop-preview-wrapper');
            let sampleContent = previewContainer.querySelector('.sample-letter-body');

            if (headerContainer && sampleContent) {
                headerContainer.innerHTML = html;
            } else {
                const sampleText = sampleContent ? sampleContent.outerHTML : `
                    <div class="sample-letter-body" style="padding: 20px 40px; color: #333; font-family: 'Times New Roman', serif; line-height: 1.6;">
                        <div style="text-align: center; font-weight: bold; margin-bottom: 20px; text-decoration: underline;">
                            CONTOH ISI SURAT
                        </div>
                        <p>Yang bertanda tangan di bawah ini:</p>
                        <table style="width: 100%; margin-left: 20px; margin-bottom: 20px;">
                            <tr><td style="width: 100px;">Nama</td><td>: ____________________</td></tr>
                            <tr><td>Jabatan</td><td>: ____________________</td></tr>
                        </table>
                        <p>Dengan ini menyatakan bahwa...</p>
                        <br><br><br>
                        <div style="text-align: right; margin-top: 50px;">
                            <p>Hormat kami,</p>
                            <br><br><br>
                            <p>(____________________)</p>
                        </div>
                    </div>
                `;

                previewContainer.innerHTML = `<div class="kop-preview-wrapper">${html}</div>${sampleText}`;
            }

            if (loader) loader.style.display = 'none';
        })
        .catch(err => {
            console.error('Live preview error:', err);
            if (loader) loader.style.display = 'none';
            
            if (previewContainer) {
                previewContainer.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h4>Preview Gagal</h4>
                        <p>${err.message}</p>
                        <small>Silakan refresh halaman dan coba lagi</small>
                    </div>
                `;
            }
        });
    }

    // ===== Live Preview with Debounce =====
    function setupLivePreview() {
        const form = document.getElementById('formKopSurat');
        const previewContainer = document.getElementById('a4-preview-container');
        if (!form || !previewContainer) return;

        let debounceTimer;
        const DEBOUNCE_MS = 800;

        const livePreviewFields = [
            'mode_type', 'text_align', 'nama_fakultas', 'alamat_lengkap', 
            'telepon_lengkap', 'email_website', 'logo_size', 'font_size_title',
            'font_size_text', 'text_color', 'header_padding', 'background_opacity',
            'tampilkan_logo_kanan', 'tampilkan_logo_kiri'
        ];

        livePreviewFields.forEach(fieldName => {
            const elements = document.querySelectorAll(`[name="${fieldName}"]`);
            elements.forEach(el => {
                const eventType = (el.type === 'checkbox' || el.type === 'radio') ? 'change' : 'input';
                el.addEventListener(eventType, () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => triggerPreview(), DEBOUNCE_MS);
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        toggleSections();
        updateRadioCards();
        updateFileLabels();
        setupRangeSliders();
        setupZoomControls();
        setupDeleteImage();
        setupPreviewRefresh();
        setupPresetSelector();
        setupImport();
        setupPaperSizeSelector();
        setupLivePreview();

        document.querySelectorAll('input[name="mode_type"]').forEach(function(radio){
            radio.addEventListener('change', function() {
                toggleSections();
                updateRadioCards();
            });
        });

        document.querySelectorAll('.radio-card').forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });
    });
})();
</script>
@endpush
