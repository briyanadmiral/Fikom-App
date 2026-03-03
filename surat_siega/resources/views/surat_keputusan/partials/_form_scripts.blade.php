@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<script>
(function() {
    'use strict';
    
    const IS_EDIT = @json($isEdit);
    const skForm = document.getElementById('skForm');
    if (!skForm) return;    

    const $ = window.jQuery;
    const qs = (sel, root = document) => root.querySelector(sel);
    const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const on = (el, ev, fn) => el && el.addEventListener(ev, fn);

        // ============ Select2 (penandatangan + helper sidebar) ============
    const penandatanganSelect  = qs('#penandatangan');
    const penandatanganSidebar = qs('#penandatangan_sidebar');

    // Native JS: auto-fill NPP saat penandatangan dipilih
    if (penandatanganSelect) {
        penandatanganSelect.addEventListener('change', function () {
            const selectedOption   = this.options[this.selectedIndex];
            const npp              = selectedOption.getAttribute('data-npp') || '';

            const nppField         = qs('#npp_penandatangan');
            const nppDisplay       = qs('#npp_display');
            const nppDisplayGroup  = qs('#npp_display_group');
            const nppManualInput   = qs('#npp_manual_input');

            if (nppField) nppField.value = npp;

            if (npp) {
                if (nppDisplay)      nppDisplay.value     = 'NPP. ' + npp;
                if (nppDisplayGroup) nppDisplayGroup.style.display = 'block';
            } else {
                if (nppDisplay)      nppDisplay.value     = '';
                if (nppDisplayGroup) nppDisplayGroup.style.display = 'none';
            }

            if (nppManualInput) nppManualInput.value = npp;
        });

        // Trigger saat edit (prefill NPP)
        if (IS_EDIT && penandatanganSelect.value) {
            penandatanganSelect.dispatchEvent(new Event('change'));
        }
    }

    // jQuery: select2 + auto-generate judul + sinkronisasi sidebar
    $(function () {
        // aktifkan select2 kalau ada jQuery
        if ($('#penandatangan').length && $.fn.select2) {
            $('#penandatangan').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih Pejabat Penandatangan',
                width: '100%'
            });
        }

        // Auto-generate judul penetapan dari "Tentang"
        $('#tentang').on('input', function () {
            const tentang      = $(this).val().trim();
            const $judulField  = $('#judul_penetapan');
            const currentJudul = $judulField.val();

            if (!currentJudul || currentJudul.startsWith('KEPUTUSAN DEKAN TENTANG')) {
                if (tentang) {
                    $judulField.val('KEPUTUSAN DEKAN TENTANG ' + tentang.toUpperCase());
                } else {
                    $judulField.val('');
                }
            }
        });

        // Sinkronisasi dropdown sidebar dengan field utama
        $('#penandatangan_sidebar').on('change', function () {
            const val = $(this).val();
            $('#penandatangan').val(val).trigger('change');
        });

        const currentSigner = $('#penandatangan').val();
        if (currentSigner) {
            $('#penandatangan_sidebar').val(currentSigner);
        }
    });

    // ============ Tagify (Tembusan) + Preview ============
    const TEMBUSAN_PRESETS = @json($tembusanPresets);
    const tembusanInput = qs('#tembusan-input');
    const preview = qs('#tembusanPreview');
    const showTitle = qs('#tembusanShowTitle');
    let tagify = null;

    function escHtml(s) {
        return String(s).replace(/[&<>"'`=\/]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','=':'&#x3D;','`':'&#x60;'}[c]));
    }

    if (tembusanInput) {
        tagify = new Tagify(tembusanInput, {
            enforceWhitelist: false,
            whitelist: TEMBUSAN_PRESETS,
            trim: true,
            duplicates: false,
            delimiters: ',',
            editTags: 1,
            dropdown: { enabled: 1, maxItems: 20, fuzzySearch: true, highlightFirst: true, placeAbove: false },
            placeholder: 'Contoh: Yth. Rektor, BAAK, Arsip',
            transformTag(t) {
                let v = t.value.trim();
                if (!v) return v;
                v = v.toLowerCase().replace(/\b\w/g, m => m.toUpperCase());
                const needsYth = /Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris/i.test(v) && !/Yth\./i.test(v);
                if (needsYth) v = 'Yth. ' + v;
                t.value = v;
            }
        });

        function renderTembusanPreview() {
            const data = tagify.value.map(t => t.value.trim()).filter(Boolean);
            if (!data.length) {
                preview.innerHTML = `<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>`;
                $('#tembusanformatted').val('');
                return;
            }
            const showTitleChecked = qs('#tembusanShowTitle').checked;
            const titleHtml = showTitleChecked ? `<div class="mb-2 font-weight-bold">Tembusan Yth</div>` : '';
            const listHtml = `<ol class="mb-0">${data.map(v => `<li>${escHtml(v)}</li>`).join('')}</ol>`;
            preview.innerHTML = `<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`;
            const plain = (showTitleChecked ? 'Tembusan Yth\n' : '') + data.map((v,i) => `${i+1}. ${v}`).join('\n');
            $('#tembusanformatted').val(plain);
        }

        tagify.on('add', renderTembusanPreview)
              .on('remove', renderTembusanPreview)
              .on('edit:updated', renderTembusanPreview);
        
        if (showTitle) showTitle.addEventListener('change', renderTembusanPreview);

        qs('#btnPasteTembusan') && on(qs('#btnPasteTembusan'), 'click', async function() {
            try {
                const txt = await navigator.clipboard.readText();
                if (!txt) return;
                const items = txt.split(',').map(s => s.trim()).filter(Boolean);
                const existing = new Set(tagify.value.map(t => t.value.toLowerCase()));
                tagify.addTags(items.filter(s => !existing.has(s.toLowerCase())).map(s => ({value: s})));
            } catch(e) {
                Swal.fire('Tidak bisa mengakses clipboard', 'Izinkan akses atau tempel manual.', 'info');
            }
        });

        qs('#btnClearTembusan') && on(qs('#btnClearTembusan'), 'click', () => tagify.removeAllTags());
        setTimeout(renderTembusanPreview, 0);
    }

    // ============ CKEditor ============
    window.editors = {};
    function initEditor(textarea) {
        if (!textarea || !window.ClassicEditor) return;
        ClassicEditor.create(textarea, {
            toolbar: { items: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'] },
            placeholder: 'Ketik isi keputusan di sini...'
        })
        .then(instance => {
            window.editors[textarea.name] = instance;
            instance.model.document.on('change:data', updateUI);
        })
        .catch(err => console.error('Gagal CKEditor:', err));
    }
    qsa('textarea.wysiwyg').forEach(initEditor);

    // ============ Nomor Builder ============
    const nomorField = qs('#nomor');
    const toggleManual = qs('#toggleNomorManual');
    const builderBox = qs('.nomor-builder');
    const builderInputs = ['nourut', 'noklasifikasi', 'nounit', 'noromawi', 'notahun'];

    function buildNomorString() {
        const urut = String(qs('#nourut')?.value || '1').padStart(3, '0');
        const klas = (qs('#noklasifikasi')?.value || 'SK').trim();
        const unit = (qs('#nounit')?.value || 'UNIKA').trim();
        const roma = (qs('#noromawi')?.value || 'I').trim().toUpperCase();
        const thn = (qs('#notahun')?.value || new Date().getFullYear()).toString().trim();
        return `${urut}/${klas}/${unit}/UNIKA/${roma}/${thn}`;
    }

    function updateNomorField() {
        const v = buildNomorString();
        if (nomorField) nomorField.value = v;
        const p = qs('#nomorPreviewText');
        if (p) p.textContent = v;
    }

    function setNomorMode(isManual) {
        if (nomorField) nomorField.readOnly = !isManual;
        builderInputs.forEach(id => {
            const el = qs(`#${id}`);
            if (el) el.disabled = isManual;
        });
        if (!isManual) updateNomorField();
    }

    if (toggleManual) {
        toggleManual.checked = !!IS_EDIT;
        setNomorMode(!!IS_EDIT);
        on(toggleManual, 'change', () => setNomorMode(toggleManual.checked));
    }

    builderInputs.forEach(id => on(qs(`#${id}`), 'input', () => {
        if (!toggleManual?.checked) updateNomorField();
    }));

    on(qs('#toggleBuilder'), 'click', e => {
        e.preventDefault();
        builderBox?.classList.toggle('show');
    });

    on(qs('#btn-reserve-nomor'), 'click', async function() {
        if (toggleManual && toggleManual.checked) {
            Swal.fire({ icon: 'info', title: 'Mode Manual', text: 'Matikan Mode Manual untuk mengambil nomor otomatis.' });
            return;
        }
        try {
            const payload = {
                unit: qs('#nounit')?.value,
                kode_klasifikasi: qs('#noklasifikasi')?.value,
                bulan_romawi: qs('#noromawi')?.value,
                tahun: parseInt(qs('#notahun')?.value || new Date().getFullYear())
            };
            const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const res = await fetch(@json(route('ajax.nomor.reserve')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(payload)
            });
            
            if (!res.ok) throw new Error(`Gagal menghubungi server (${res.status})`);
            const data = await res.json();
            const urutEl = qs('#nourut');
            if (urutEl) urutEl.value = data.nourut || data.nomor_urut || '001';
            updateNomorField();
            Swal.fire({ icon: 'success', title: 'Nomor Disiapkan', text: `${data.nomor}\nNomor berhasil diambil.` });
        } catch(err) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
        }
    });

    // ============ Dynamic Lists ============
    function reindexList(listId, type) {
        const items = qsa(`#${listId} .dynamic-item`);
        items.forEach((item, i) => {
            const label = item.querySelector('.dynamic-label');
            if (label) label.textContent = type === 'alpha' ? String.fromCharCode(97 + i) + '.' : (i + 1) + '.';
            const del = item.querySelector('.remove-row, .btn-remove-menetapkan');
            if (del) del.style.display = item.parentElement.children.length > 1 ? '' : 'none';
        });
        if (listId === 'menimbang-list') $('#badge-menimbang').text(items.length);
        if (listId === 'mengingat-list') $('#badge-mengingat').text(items.length);
    }

    function reindexDiktum() {
        const LABELS = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
        $('#menetapkan-list .diktum-item').each(function(i) {
            $(this).find('input[name*="[judul]"]').val(LABELS[i] || `KETENTUAN ${i+1}`);
        });
        $('#badge-menetapkan').text($('#menetapkan-list .diktum-item').length);
    }

    document.addEventListener('click', async function(e) {
        let acted = false;

        if (e.target.closest('#add-menimbang')) {
            $('#menimbang-list').append(`
                <div class="input-group mb-2 dynamic-item menimbang-item">
                    <span class="input-group-text dynamic-label"></span>
                    <input type="text" name="menimbang[]" class="form-control" placeholder="Tulis poin pertimbangan...">
                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                </div>
            `);
            reindexList('menimbang-list', 'alpha');
            acted = true;
        } else if (e.target.closest('#add-mengingat')) {
            $('#mengingat-list').append(`
                <div class="input-group mb-2 dynamic-item mengingat-item">
                    <span class="input-group-text dynamic-label"></span>
                    <input type="text" name="mengingat[]" class="form-control" placeholder="Tulis dasar hukum...">
                    <button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button>
                </div>
            `);
            reindexList('mengingat-list', 'numeric');
            acted = true;
        } else if (e.target.closest('#add-menetapkan')) {
            const i = $('#menetapkan-list .diktum-item').length;
            $('#menetapkan-list').append(`
                <div class="diktum-item p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small">Judul</label>
                            <input type="text" class="form-control form-control-sm" name="menetapkan[${i}][judul]" value="KETENTUAN" readonly>
                        </div>
                        <div class="col">
                            <label class="form-label small">Isi Keputusan</label>
                            <textarea class="form-control wysiwyg" name="menetapkan[${i}][isi]" rows="4"></textarea>
                        </div>
                        <div class="col-auto d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-menetapkan" title="Hapus diktum">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            const ta = $('#menetapkan-list .diktum-item:last textarea.wysiwyg').get(0);
            initEditor(ta);
            reindexDiktum();
            acted = true;
        } else if (e.target.closest('.remove-row')) {
            const item = e.target.closest('.dynamic-item');
            if (item && item.parentElement.children.length > 1) {
                const parentId = item.parentElement.id;
                item.remove();
                if (parentId === 'menimbang-list') reindexList('menimbang-list', 'alpha');
                if (parentId === 'mengingat-list') reindexList('mengingat-list', 'numeric');
                acted = true;
            }
        } else if (e.target.closest('.btn-remove-menetapkan')) {
            const d = e.target.closest('.diktum-item');
            if (d && d.parentElement.children.length > 1) {
                const ta = d.querySelector('textarea.wysiwyg');
                if (ta?.name && window.editors[ta.name]) {
                    await window.editors[ta.name].destroy().catch(() => {});
                    delete window.editors[ta.name];
                }
                d.remove();
                reindexDiktum();
                acted = true;
            }
        }

        if (acted) setTimeout(updateUI, 60);
    });

    reindexList('menimbang-list', 'alpha');
    reindexList('mengingat-list', 'numeric');
    reindexDiktum();

    // ============ UI Status + QuickNav Active ============
    function updateUI() {
        const setStatus = (id, st) => {
            const h = qs(`#h-${id}`), nav = qs(`#quicknav a[href="#section-${id}"]`);
            if (h) {
                h.className = 'card-h';
                const base = h.getAttribute('data-base') || 'purple';
                h.classList.add(st === 'complete' ? 'card-h--green' : (st === 'error' ? 'card-h--red' : `card-h--${base}`));
            }
            if (nav) {
                nav.classList.remove('has-error', 'is-complete');
                if (st === 'complete') nav.classList.add('is-complete');
                else if (st === 'error') nav.classList.add('has-error');
            }
        };

        const hasErr = id => !!(qs(`#section-${id} .is-invalid`) || qs(`#section-${id}[aria-invalid="true"]`));
        const filled = v => !!String(v).trim().length;
        const plain = html => { const d = document.createElement('div'); d.innerHTML = html; return d.textContent.trim(); };

        const tanggalEl = qs('[name="tanggal_surat"]');
        const tentangEl = qs('[name="tentang"]');
        setStatus('utama', hasErr('utama') ? 'error' : (filled(tanggalEl?.value) && filled(tentangEl?.value) ? 'complete' : 'base'));

        const anyMenimbang = qsa('[name="menimbang[]"]').some(i => filled(i.value));
        setStatus('menimbang', hasErr('menimbang') ? 'error' : (anyMenimbang ? 'complete' : 'base'));

        const anyMengingat = qsa('[name="mengingat[]"]').some(i => filled(i.value));
        setStatus('mengingat', hasErr('mengingat') ? 'error' : (anyMengingat ? 'complete' : 'base'));

        const anyDiktum = Object.values(window.editors).some(ed => filled(plain(ed.getData())));
        setStatus('menetapkan', hasErr('menetapkan') ? 'error' : (anyDiktum ? 'complete' : 'base'));

        $('#badge-menimbang').text($('#menimbang-list .menimbang-item').length);
        $('#badge-mengingat').text($('#mengingat-list .mengingat-item').length);
        $('#badge-menetapkan').text($('#menetapkan-list .diktum-item').length);
    }

    const navLinks = qsa('#quicknav a');
    const sections = navLinks.map(a => qs(a.getAttribute('href'))).filter(Boolean);
    if (sections.length) {
        function onScroll() {
            const y = window.scrollY + 120;
            let cur = sections[0];
            for (const s of sections) if (s.offsetTop <= y) cur = s;
            navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + cur.id));
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    skForm.addEventListener('input', updateUI, true);
    skForm.addEventListener('change', updateUI, true);
    updateUI();

    // ============ Submit Guard + Shortcuts ============
    function validateAndSubmit(e, mode) {
        if (mode === 'pending') {
            const signer = skForm.querySelector('select[name="penandatangan"]');
            if (!signer || !signer.value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Penandatangan Belum Dipilih', text: 'Silakan pilih penandatangan terlebih dahulu untuk mengajukan.' });
                if (signer) $('#penandatangan').select2('open');
            }
        }
    }

    on(qs('#btn-submit-approve'), 'click', e => validateAndSubmit(e, 'pending'));
    on(qs('#mb-approve'), 'click', e => validateAndSubmit(e, 'pending'));
    on(qs('#btn-submit-draft'), 'click', e => validateAndSubmit(e, 'draft'));
    on(qs('#mb-draft'), 'click', e => validateAndSubmit(e, 'draft'));

    let isDirty = false;
    skForm.addEventListener('input', () => isDirty = true);
    window.addEventListener('beforeunload', e => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = 'Perubahan belum disimpan. Yakin keluar?';
        }
    });
    skForm.addEventListener('submit', () => isDirty = false);

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === 's' || e.key === 'S') {
                e.preventDefault();
                qs('#btn-submit-draft')?.click();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                qs('#btn-submit-approve')?.click();
            }
        }
    });

})();

// ============================================
// ✅ BARU: Save NPP manual override (global)
// ============================================
function saveNppManual() {
    const nppManual = (window.jQuery ? jQuery('#npp_manual_input').val().trim() : '').trim();
    const $ = window.jQuery;

    if (!$) return;

    // Set ke hidden field
    $('#npp_penandatangan').val(nppManual);
    
    // Update display
    if (nppManual) {
        $('#npp_display').val('NPP. ' + nppManual);
        $('#npp_display_group').slideDown(200);
    } else {
        const selectedOption = $('#penandatangan').find('option:selected');
        const nppUser = selectedOption.data('npp') || '';
        $('#npp_penandatangan').val(nppUser);
        
        if (nppUser) {
            $('#npp_display').val('NPP. ' + nppUser);
            $('#npp_display_group').slideDown(200);
        } else {
            $('#npp_display_group').slideUp(200);
        }
    }
    
    // Close modal
    $('#modal_edit_npp').modal('hide');
    
    if (window.toastr && toastr.success) {
        toastr.success('NPP berhasil diperbarui');
    }
}

// ============ LIBRARY INTEGRATION ============
// Load Menimbang Library
$('#modalMenimbangLibrary').on('show.bs.modal', function() {
    loadLibraryItems('menimbang');
});

// Load Mengingat Library
$('#modalMengingatLibrary').on('show.bs.modal', function() {
    loadLibraryItems('mengingat');
});

// Search in libraries
let searchTimeout;
$('#search-menimbang, #search-mengingat').on('keyup', function() {
    clearTimeout(searchTimeout);
    const type = $(this).attr('id').includes('menimbang') ? 'menimbang' : 'mengingat';
    const query = $(this).val();
    
    searchTimeout = setTimeout(() => {
        loadLibraryItems(type, query);
    }, 300);
});

function loadLibraryItems(type, search = '') {
    const container = $(`#${type}-library-list`);
    const url = `/ajax/${type}-library/search?q=${encodeURIComponent(search)}`;
    
    container.html(`
        <div class="text-center text-muted py-4">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Memuat library...</p>
        </div>
    `);
    
    $.get(url)
        .done(function(response) {
            if (response.data && response.data.length > 0) {
                let html = '<div class="list-group">';
                response.data.forEach(item => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action library-item" 
                           data-type="${type}" data-content="${escapeHtml(item.isi)}">
                            <div class="d-flex w-100 justify-content-between">
                                <p class="mb-1">${escapeHtml(item.isi)}</p>
                                <small class="text-muted">
                                    <i class="fas fa-plus-circle"></i>
                                </small>
                            </div>
                            ${item.kategori ? `<small class="badge badge-info">${item.kategori}</small>` : ''}
                            ${item.jumlah_penggunaan ? `<small class="text-muted ml-2"><i class="fas fa-chart-line"></i> ${item.jumlah_penggunaan}x</small>` : ''}
                        </a>
                    `;
                });
                html += '</div>';
                container.html(html);
            } else {
                container.html(`
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Tidak ada data di library</p>
                        <small>Silakan tambahkan melalui menu Library Konten</small>
                    </div>
                `);
            }
        })
        .fail(function() {
            container.html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Gagal memuat library. Silakan coba lagi.
                </div>
            `);
        });
}

// Insert library item ke form
$(document).on('click', '.library-item', function(e) {
    e.preventDefault();
    const type = $(this).data('type');
    const content = $(this).data('content');
    
    // Add row baru dengan konten dari library
    const addBtn = $(`#add-${type}`);
    addBtn.click();
    
    // Isi konten ke row terakhir
    setTimeout(() => {
        const lastInput = $(`#${type}-list .dynamic-item:last-child input`);
        lastInput.val(content);
        lastInput.focus();
        
        // Close modal
        $(`#modal${type.charAt(0).toUpperCase() + type.slice(1)}Library`).modal('hide');
        
        if (window.toastr && toastr.success) {
            toastr.success('Konten berhasil ditambahkan dari library');
        }
    }, 100);
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

</script>
@endpush
@endonce
