@once
    @push('scripts')
        {{-- Vendor JS --}}
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        $(function() {
            // ====== Safe escape util (untuk mencegah XSS pada preview) ======
            window._ = window._ || {};
            if (!_.escape) {
                _.escape = (s) =>
                    String(s ?? '').replace(/[&<>"'=\/`]/g, (c) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '=': '&#x3D;',
                    '`': '&#x60;',
                    } [c]));
            }

            // ====== STATE & ELEM ======
            const isEdit = @json($isEdit);
            const tugasForm = $('#tugasForm');
            const $disp = $('#nomor_surat_lengkap_display');
            const $hidden = $('#nomor_surat_lengkap_hidden');
            const $urut = $('#nomor_urut');
            const $manual = $('#no_surat_manual');
            let isSubmitting = false;
            let clickedAction = null;

            // ====== Select2 ======
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // ====== Datetime guard ======
            $('#waktu_selesai').on('change', function() {
                const mulai = $('#waktu_mulai').val(),
                    selesai = $(this).val();
                if (mulai && selesai && new Date(selesai) < new Date(mulai)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Waktu Tidak Valid',
                        text: 'Waktu selesai harus setelah waktu mulai'
                    });
                    $(this).val('');
                }
            });
            $('#waktu_mulai').on('change', function() {
                $('#waktu_selesai').attr('min', $(this).val());
            });

            // ====== DataTable ======
            let table;
            if ($.fn.DataTable.isDataTable('#penerima-table')) {
                table = $('#penerima-table').DataTable();
            } else {
                table = $('#penerima-table').DataTable({
                    responsive: true,
                    lengthChange: true,
                    autoWidth: false,
                    stateSave: true,
                    order: [],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_-_END_ dari _TOTAL_',
                        zeroRecords: 'Tidak ditemukan',
                        paginate: {
                            next: '>>',
                            previous: '<<'
                        }
                    },
                    columnDefs: [{
                        orderable: false,
                        targets: 0
                    }]
                });
            }

            // Select all (halaman aktif)
            $('#select-all-penerima').on('change', function() {
                const checked = this.checked;
                table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox').prop('checked', checked);
            });

            // Sync header checkbox
            $('#penerima-table').on('change', '.penerima-checkbox', function() {
                const totalOnPage = table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox').length;
                const checkedOnPage = table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox:checked').length;
                $('#select-all-penerima').prop('checked', totalOnPage === checkedOnPage && totalOnPage > 0);
            });

            // Sync centang saat redraw
            table.on('draw.dt', function() {
                $('#select-all-penerima').prop('checked', false);
                table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox').each(function() {
                    const id = $(this).val();
                    $(this).prop('checked', !!penerimaState.internal[id]);
                });
            });

            // Pre-check saat modal show
            $('#penerimaModal').on('shown.bs.modal', function() {
                table.rows().every(function() {
                    $(this.node()).find('.penerima-checkbox').each(function() {
                        const id = $(this).val();
                        $(this).prop('checked', !!penerimaState.internal[id]);
                    });
                });
            });

            // ====== CKEditor (secure) ======
            const editorEl = document.querySelector('#detail_tugas_editor');
            if (editorEl && window.ClassicEditor) {
                ClassicEditor.create(editorEl, {
                    toolbar: {
                        items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList',
                            'numberedList', '|', 'undo', 'redo'
                        ],
                        shouldNotGroupWhenFull: true
                    },
                    htmlSupport: {
                        disallow: [{
                            name: 'script'
                        }, {
                            name: 'iframe'
                        }, {
                            attributes: [{
                                key: /^on.*$/,
                                value: true
                            }]
                        }]
                    },
                    link: {
                        addTargetToExternalLinks: true,
                        decorators: {
                            isExternal: {
                                mode: 'automatic',
                                callback: url => url.startsWith('http'),
                                attributes: {
                                    rel: 'noopener noreferrer'
                                }
                            }
                        }
                    }
                })
                .then(editor => {
                    window.detailEditor = editor;
                })
                .catch(console.error);
            }

            // ====== NOMOR SURAT ======
            function extractNoUrut(nomor) {
                const m = (nomor || '').trim().match(/^([0-9]{1,4}[A-Z]?)/);
                return m ? m[1] : '';
            }

            function markNomorStale() {
                $disp.val('(belum disiapkan)');
                $hidden.val('');
                if (!isEdit) $urut.val('');
            }

            function buildNomorFromParts() {
    const noUrut = String($('#nomor_urut').val() || '').padStart(3, '0');
    const kode = ($('#klasifikasi_kode').val() || '').trim() || '...';
    const bulan = ($('#bulan').val() || '').toUpperCase() || '...';
    const tahun = $('#tahun-nomor').val() || '....';
    
    // ✅ DEBUG
    console.log('=== BUILD NOMOR ===');
    console.log('No Urut:', noUrut);
    console.log('Kode dari #klasifikasi_kode:', kode);
    console.log('Bulan:', bulan);
    console.log('Tahun:', tahun);
    
    const result = `${noUrut}/${kode}/ST.IKOM/UNIKA/${bulan}/${tahun}`;
    console.log('Nomor final:', result);
    
    return result;
}


            async function reserveNomor(showToast = true) {
    const manual = $manual.val().trim();
    if (manual) {
        $disp.val(manual);
        $hidden.val(manual);
        $urut.val(extractNoUrut(manual));
        if (showToast) Swal.fire({
            icon: 'success',
            title: 'Nomor Manual Dipakai',
            text: manual,
            timer: 1400,
            showConfirmButton: false
        });
        return { nomor: manual, manual: true };
    }
    
    // ✅ PERBAIKAN: Jangan strip titik!
    const kodeKlas = ($('#klasifikasi_kode').val() || '').trim();
    const bulan = ($('#bulan').val() || '').toUpperCase();
    const tahun = parseInt($('#tahun-nomor').val(), 10) || new Date().getFullYear();
    
    // ✅ DEBUG
    console.log('=== DATA YANG DIKIRIM KE SERVER ===');
    console.log('kode_klasifikasi:', kodeKlas);
    console.log('Apakah ada titik?', kodeKlas.includes('.'));
    console.log('bulan_romawi:', bulan);
    console.log('tahun:', tahun);
    
    if (!kodeKlas || !bulan || !tahun) {
        Swal.fire('Lengkapi Kode/Bulan/Tahun dahulu', '', 'info');
        return null;
    }

    try {
        $('#btn-reserve-nomor').prop('disabled', true);
        const csrf = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        
        const payload = {
            doc_type: 'ST',
            unit_display: 'ST.IKOM',
            kode_klasifikasi: kodeKlas,  // ✅ Harus tetap ada titik!
            bulan_romawi: bulan,
            tahun
        };
        
        console.log('Payload JSON:', JSON.stringify(payload));
        
        const res = await fetch(@json(route('ajax.nomor.reserve')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
        
        if (!res.ok) throw new Error('Reserve nomor gagal');
        const data = await res.json();
        
        console.log('Response dari server:', data);
        
        $disp.val(data.nomor);
        $hidden.val(data.nomor);
        $urut.val(extractNoUrut(data.nomor));
        
        if (showToast) Swal.fire({
            icon: 'success',
            title: 'Nomor Disiapkan',
            text: data.nomor,
            timer: 1500,
            showConfirmButton: false
        });
        
        return data;
    } catch (e) {
        console.error('Error reserve nomor:', e);
        Swal.fire('Gagal', 'Tidak bisa menyiapkan nomor. Coba lagi.', 'error');
        return null;
    } finally {
        $('#btn-reserve-nomor').prop('disabled', false);
    }
}

            if (isEdit) {
                const updateNomorSurat = () => {
                    const nomor = buildNomorFromParts();
                    $disp.val(nomor);
                    $hidden.val(nomor);
                };
                $('#nomor_urut, #klasifikasi_kode, #bulan, #tahun-nomor, #klasifikasi_surat_id')
                    .on('change keyup input', updateNomorSurat);
                if ($manual.val().trim()) {
                    const v = $manual.val().trim();
                    $disp.val(v);
                    $hidden.val(v);
                    $urut.val(extractNoUrut(v));
                } else {
                    const built = buildNomorFromParts();
                    $disp.val(built);
                    $hidden.val(built);
                }
            } else {
                const onScopeChange = () => {
                    if (!$manual.val().trim()) markNomorStale();
                };
                $('#klasifikasi_surat_id, #klasifikasi_kode, #bulan, #tahun-nomor')
                    .on('change keyup input', onScopeChange);
                $(document).on('click', '#btn-reserve-nomor', () => reserveNomor(true));
                $(document).on('click', '#btn-reset-nomor', () => markNomorStale());
                $manual.on('input', function() {
                    const v = $(this).val().trim();
                    if (v === '') return markNomorStale();
                    $disp.val(v);
                    $hidden.val(v);
                    $urut.val(extractNoUrut(v));
                });
                markNomorStale();
            }

            function toggleReserveBtn() {
                const can = ($('#klasifikasi_kode').val() || '').trim() && ($('#bulan').val() || '') && ($(
                    '#tahun-nomor').val() || '');
                $('#btn-reserve-nomor').prop('disabled', !can);
            }
            $('#klasifikasi_kode, #bulan, #tahun-nomor').on('input change', toggleReserveBtn);
            toggleReserveBtn();

            // ====== Dropdown tugas & preview ======
            const taskData = @json($taskMaster);
            const $tugasPreview = $('#task-preview');
            const placeholderText =
                `<span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat pratinjau.</span>`;

            function updateTaskPreview() {
                const kategori = $('#jenis_tugas').val(),
                    tugas = $('#tugas').val();
                if (kategori && tugas) {
                    const safeKategori = _.escape(kategori);
                    const safeTugas = _.escape(tugas);
                    $tugasPreview.html(`<div>
        <p class="mb-1 text-muted">Jenis Tugas:</p>
        <h5 class="preview-title mb-3"><i class="fas fa-layer-group mr-2"></i>${safeKategori}</h5>
        <p class="mb-1 text-muted">Tugas:</p>
        <p class="preview-content font-weight-bold">${safeTugas}</p>
      </div>`).addClass('has-content');
                } else {
                    $tugasPreview.html(placeholderText).removeClass('has-content');
                }
            }

            function populateSpecificTask(selectedKategori, preselectedTugas) {
                const $tugasSelect = $('#tugas');
                $tugasSelect.empty().append(new Option('Pilih Tugas...', ''));
                const found = (taskData || []).find(jt => jt.nama === selectedKategori);
                if (found && Array.isArray(found.subtugas) && found.subtugas.length) {
                    found.subtugas.forEach(st => {
                        const selected = preselectedTugas === st.nama;
                        $tugasSelect.append(new Option(st.nama, st.nama, selected, selected));
                    });
                    $tugasSelect.prop('disabled', false);
                } else {
                    $tugasSelect.prop('disabled', true);
                }
                $tugasSelect.trigger('change.select2');
                updateTaskPreview();
            }
            $('#jenis_tugas').on('change', function() {
                populateSpecificTask($(this).val(), null);
            });
            $('#tugas').on('change', updateTaskPreview);
            @if ($isEdit)
                populateSpecificTask(@json(old('jenis_tugas', $tugas->jenis_tugas)), @json(old('tugas', $tugas->tugas)));
            @else
                if (@json(old('jenis_tugas', ''))) {
                    populateSpecificTask(@json(old('jenis_tugas', '')), @json(old('tugas', '')));
                }
            @endif

            // ====== TEMBUSAN (Tagify) ======
            const tembusanPresets = @json($tembusanPresets ?? []);
            const tembusanInput = document.querySelector('#tembusan-input');
            if (tembusanInput) {
                const tagify = new Tagify(tembusanInput, {
                    enforceWhitelist: false,
                    whitelist: tembusanPresets,
                    trim: true,
                    duplicates: false,
                    delimiters: ",|\n",
                    editTags: 1,
                    dropdown: {
                        enabled: 1,
                        maxItems: 20,
                        fuzzySearch: true,
                        highlightFirst: true,
                        placeAbove: false
                    },
                    placeholder: "Misal: Yth. Rektor, BAAK, Arsip",
                    transformTag: (t) => {
                        let v = (t.value || '').trim();
                        if (!v) return;
                        v = v.toLowerCase().replace(/\b\w/g, m => m.toUpperCase());
                        const needsYth =
                            /^(Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris)\b/i.test(
                                v) && !/^Yth\.\s/i.test(v);
                        if (needsYth) v = 'Yth. ' + v;
                        t.value = v;
                    }
                });
                window.tagifyTembusan = tagify;
                const renderTembusanPreview = () => {
                    const data = tagify.value.map(t => (t.value || '').trim()).filter(Boolean);
                    const showTitle = $('#tembusanShowTitle').is(':checked');
                    const $preview = $('#tembusanPreview');
                    if (!data.length) {
                        $preview.html(
                            '<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>'
                        );
                        $('#tembusan_formatted').val('');
                        return;
                    }
                    const titleHtml = showTitle ? '<div class="mb-2 font-weight-bold">Tembusan Yth:</div>' : '';
                    const listHtml = '<ol class="mb-0">' + data.map(v => `<li>${_.escape(v)}</li>`).join('') +
                        '</ol>';
                    $preview.html(
                        `<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`
                    );
                    const plain = (showTitle ? 'Tembusan Yth:\n' : '') + data.map((v, i) => `${i+1}. ${v}`)
                        .join('\n');
                    $('#tembusan_formatted').val(plain);
                };
                tagify.on('add', renderTembusanPreview).on('remove', renderTembusanPreview).on('edit:updated',
                    renderTembusanPreview);
                $(document).on('change', '#tembusanShowTitle', renderTembusanPreview);
                $(document).on('click', '#btnPasteTembusan', async function() {
                    try {
                        const txt = await navigator.clipboard.readText();
                        if (!txt) return;
                        const items = txt.split(/[\n,]/).map(s => s.trim()).filter(Boolean);
                        const existing = new Set(tagify.value.map(t => (t.value || '').toLowerCase()));
                        const toAdd = items.filter(s => !existing.has(s.toLowerCase())).map(s => ({
                            value: s
                        }));
                        tagify.addTags(toAdd);
                    } catch (e) {
                        Swal.fire('Tidak bisa mengakses clipboard', 'Izinkan akses atau tempel manual.',
                            'info');
                    }
                });
                $(document).on('click', '#btnClearTembusan', () => tagify.removeAllTags());
                setTimeout(renderTembusanPreview, 0);
            }

            // ====== PENERIMA ======
            const allUsersData = @json($usersMap);
            const initialInternal = @json($initialInternal);
            const initialEksternal = @json($initialEksternal);
            let penerimaState = {
                internal: {},
                eksternal: Array.isArray(initialEksternal) ? initialEksternal : []
            };

            (initialInternal || []).forEach(id => {
                const u = allUsersData[id];
                if (u) penerimaState.internal[id] = {
                    nama: u.nama_lengkap,
                    peran_id: u.peran_id
                };
            });

            function updateStatusPenerima() {
                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;
                const total = internalCount + eksternalCount;
                const $display = $('#status_penerima_display');
                const $hiddenStatus = $('#status_penerima_hidden');

                if (total === 0) {
                    $display.val('Belum ada penerima');
                    $hiddenStatus.val('');
                } else {
                    const parts = [];
                    if (internalCount > 0) parts.push(`${internalCount} Internal`);
                    if (eksternalCount > 0) parts.push(`${eksternalCount} Eksternal`);
                    const statusText = `${total} Penerima (${parts.join(', ')})`;
                    $display.val(statusText);
                    $hiddenStatus.val(statusText); // nilai ini akan disanitasi di FormRequest
                }
            }

            function renderPenerimaList() {
                const list = $('#penerima-list');
                const placeholder = $('#penerima-placeholder');
                list.empty();
                $('input[name^="penerima_internal"],input[name^="penerima_eksternal"]').remove();

                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;

                if (internalCount === 0 && eksternalCount === 0) {
                    placeholder.show();
                } else {
                    placeholder.hide();
                    for (const id in penerimaState.internal) {
                        const d = penerimaState.internal[id];
                        list.append(
                            `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div><i class="fas fa-user-tie mr-2 text-info"></i>${_.escape(d.nama)}</div>
            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="internal" data-id="${id}">
              <i class="fas fa-times"></i>
            </button>
          </li>`
                        );
                        tugasForm.append(`<input type="hidden" name="penerima_internal[]" value="${id}">`);
                    }
                    penerimaState.eksternal.forEach((p, i) => {
                        list.append(
                            `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div><i class="fas fa-user mr-2 text-success"></i>${_.escape(p.nama)} <span class="eksternal-label">(${_.escape(p.jabatan)})</span></div>
            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="eksternal" data-id="${i}">
              <i class="fas fa-times"></i>
            </button>
          </li>`
                        );
                        tugasForm.append(
                            `<input type="hidden" name="penerima_eksternal[${i}][nama]" value="${_.escape(p.nama)}">`
                        );
                        tugasForm.append(
                            `<input type="hidden" name="penerima_eksternal[${i}][jabatan]" value="${_.escape(p.jabatan)}">`
                        );
                    });
                }
                updateStatusPenerima();
            }

            $('#simpanPenerima').on('click', function(e) {
                e.preventDefault();
                console.log('Simpan Penerima Internal diklik');
                
                penerimaState.internal = {};
                // ✅ Gunakan table.rows().nodes() agar semua data di semua halaman terbaca
                const rows = table.rows().nodes();
                $(rows).find('.penerima-checkbox:checked').each(function() {
                    const id = $(this).val();
                    const nama = $(this).data('nama');
                    const peranId = $(this).data('peran-id');
                    penerimaState.internal[id] = {
                        nama,
                        peran_id: peranId
                    };
                });
                
                console.log('Total internal terpilih:', Object.keys(penerimaState.internal).length);
                renderPenerimaList();
                $('#penerimaModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Penerima Disimpan!',
                    text: 'Daftar penerima berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            $('#simpanPenerimaEksternal').on('click', function() {
                const nama = $('#nama_eksternal').val().trim();
                const jabatan = $('#jabatan_eksternal').val().trim();
                if (nama && jabatan) {
                    penerimaState.eksternal.push({
                        nama,
                        jabatan
                    });
                    renderPenerimaList();
                    $('#form-penerima-eksternal')[0].reset();
                    $('#penerimaEksternalModal').modal('hide');
                } else {
                    Swal.fire('Lengkapi Nama & Jabatan', '', 'warning');
                }
            });

            $('#penerima-list').on('click', '.remove-penerima', function() {
                const type = $(this).data('type'),
                    id = $(this).data('id');
                if (type === 'internal') {
                    delete penerimaState.internal[id];
                    $('#penerima-table .penerima-checkbox[value="' + id + '"]').prop('checked', false);
                } else {
                    penerimaState.eksternal.splice(id, 1);
                }
                renderPenerimaList();
            });

            // ====== Validasi ringkas ======
            function validateForm() {
                const errors = [];
                const namaUmum = $('#nama_umum').val().trim();
                if (!namaUmum || namaUmum.length < 10) errors.push('Judul Umum Surat minimal 10 karakter');
                if (!$('#klasifikasi_surat_id').val()) errors.push('Klasifikasi surat wajib dipilih');
                const penandatanganVal = String($('#penandatangan_id').val() || '').trim();
                if (!penandatanganVal) errors.push('Penandatangan wajib dipilih');
                const mulai = $('#waktu_mulai').val(),
                    selesai = $('#waktu_selesai').val();
                if (mulai && selesai && new Date(selesai) < new Date(mulai)) errors.push(
                    'Waktu selesai harus setelah waktu mulai');

                if (errors.length) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<ul style="text-align:left;">' + errors.map(e => `<li>${_.escape(e)}</li>`)
                            .join('') +
                            '</ul>'
                    });
                    return false;
                }
                return true;
            }

            // ====== Sinkron hidden nama pembuat & asal surat (kirim ID) ======
            function syncNamaPembuat() {
                $('#nama_pembuat_hidden').val($('#pembuat_id').val() || '');
            }

            // ====== Bidirectional Sync Asal Surat <-> Penandatangan ======
            
            // Helper to safely set Select2 value without triggering infinite loop
            function safeSetValue($element, value) {
                if ($element.val() != value) {
                    $element.val(value).trigger('change');
                }
            }

            function syncAsalSurat() {
                const $opt = $('#asal_surat_id').find(':selected');
                if ($opt.length) $('#asal_surat_hidden').val($opt.val()); // ID, bukan label
            }
            $('#pembuat_id').on('change', syncNamaPembuat);
            // $('#asal_surat_id').on('change', syncAsalSurat); // Digabung di bawah
            syncNamaPembuat();
            syncAsalSurat();

            // 1. Sync Asal Surat -> Penandatangan
            $('#asal_surat_id').on('change', function() {
                syncAsalSurat(); // Update hidden input
                const selectedVal = $(this).val();
                if (selectedVal) {
                    safeSetValue($('#penandatangan_id'), selectedVal);
                }
            });

            // 2. Sync Penandatangan -> Asal Surat
            $('#penandatangan_id').on('change', function() {
                const selectedVal = $(this).val();
                if (selectedVal) {
                    // Jika Asal Surat masih berupa Select (Create Mode)
                    if ($('#asal_surat_id').is('select')) {
                        safeSetValue($('#asal_surat_id'), selectedVal);
                    } 
                    // Jika Asal Surat berupa Text/Hidden (Edit Mode - Asal Surat biasanya readonly/hidden)
                    else {
                        // Update hidden value (untuk name="asal_surat")
                        $('#asal_surat_hidden').val(selectedVal);
                        
                        // Update hidden value (untuk name="asal_surat_id") karena backend prioritaskan field ini
                        const $baseHidden = $('input[name="asal_surat_id"]');
                        if($baseHidden.length) {
                             $baseHidden.val(selectedVal);
                        }
                        
                        // Update display text jika ada
                        // Ambil nama dari option di dropdown penandatangan
                        const selectedText = $(this).find("option:selected").text().trim();
                        // Format text biasanya "Nama (Jabatan)", kita ambil full text saja untuk display
                        if($('#asal_surat_id_display').length) {
                             $('#asal_surat_id_display').val(selectedText);
                        }
                    }
                }
            });

            // Safeguard sebelum submit (isi hidden ID bila readonly)
            tugasForm.on('submit', function() {
                if (!$('#nama_pembuat_hidden').val()) {
                    const val = $('input[name="pembuat_id"]').val() || $('#pembuat_id').val();
                    $('#nama_pembuat_hidden').val(val || '');
                }
                if (!$('#asal_surat_hidden').val()) {
                    const val = $('input[name="asal_surat_id"]').val() || $('#asal_surat_id').val();
                    $('#asal_surat_hidden').val(val || '');
                }
            });

            // ====== ACTION SUBMIT/DRAFT (SATU-SATUNYA HANDLER) ======
            $('button[name="action"]').on('click', function(e) {
                e.preventDefault();
                clickedAction = $(this).val();

                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;
                if (internalCount === 0 && eksternalCount === 0) {
                    Swal.fire('Peringatan', 'Anda harus memilih setidaknya satu penerima tugas.',
                        'warning');
                    return;
                }
                if (clickedAction === 'submit' && !validateForm()) return;

                const isSubmit = clickedAction === 'submit';
                Swal.fire({
                    title: isSubmit ? 'Ajukan Surat Tugas?' : 'Simpan sebagai Draft?',
                    text: isSubmit ?
                        'Setelah diajukan, surat akan masuk alur persetujuan. Lanjutkan?' :
                        'Draft bisa diubah nanti. Simpan sekarang?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: isSubmit ? 'Ya, ajukan' : 'Ya, simpan draft',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(async (result) => {
                    if (!result.isConfirmed) return;
                    if (isSubmitting) return;
                    isSubmitting = true;

                    Swal.fire({
                        title: 'Sedang diproses…',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                        showConfirmButton: false
                    });

                    // Pastikan ada nomor
                    if (!$hidden.val() && !$manual.val().trim()) {
                        const ok = await reserveNomor(false);
                        if (!ok) {
                            isSubmitting = false;
                            Swal.close();
                            return;
                        }
                    }
                    if ($manual.val().trim()) {
                        const v = $manual.val().trim();
                        $disp.val(v);
                        $hidden.val(v);
                        $urut.val(extractNoUrut(v));
                    }

                    tugasForm.find('input[name="action"]').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'action',
                        value: clickedAction
                    }).appendTo(tugasForm);

                    tugasForm.find('button[type="button"]').prop('disabled', true).addClass(
                        'disabled');
                    tugasForm.get(0).submit();
                });
            });

            // Submit via ENTER (tanpa klik tombol): minta pilih aksi
            tugasForm.on('submit', function(e) {
                if (clickedAction) return; // sudah ada pilihan
                e.preventDefault();
                Swal.fire({
                    title: 'Pilih Aksi',
                    text: 'Anda belum memilih apakah ingin menyimpan draft atau mengajukan surat tugas.',
                    icon: 'warning',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Draft',
                    denyButtonText: 'Ajukan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(async (res) => {
                    if (!res.isConfirmed && !res.isDenied) return;
                    clickedAction = res.isConfirmed ? 'draft' : 'submit';
                    $('button[name="action"][value="' + clickedAction + '"]').trigger('click');
                });
            });

            // ====== Render awal penerima ======
            renderPenerimaList();

            // ====== Prefill preview tugas saat edit ======
            @if ($isEdit && !empty(old('jenis_tugas', $tugas->jenis_tugas ?? null)) && !empty(old('tugas', $tugas->tugas ?? null)))
                setTimeout(function() {
                    updateTaskPreview();
                }, 300);
            @endif


            // ====== TEMPLATE SELECTOR LOGIC (ENHANCED) ======
            @if(isset($templates) && count($templates) > 0)
                const availableTemplates = @json($templates);
                const $tplSelector = $('#template_selector');
                const $previewCard = $('#template-preview-card');
                const $previewEmpty = $('#template-preview-empty');

                // Update preview card when template is selected
                $tplSelector.on('change', function() {
                    const tplId = $(this).val();
                    if (!tplId) {
                        $previewCard.addClass('d-none');
                        $previewEmpty.removeClass('d-none');
                        return;
                    }

                    const tpl = availableTemplates.find(t => t.id == tplId);
                    if (!tpl) return;

                    // Show preview card
                    $previewEmpty.addClass('d-none');
                    $previewCard.removeClass('d-none');

                    // Update preview info
                    $('#tpl-preview-name').text(tpl.nama || '-');
                    $('#tpl-preview-desc').text(tpl.deskripsi || 'Tidak ada deskripsi');

                    // Show Jenis Tugas badge
                    if (tpl.jenis_tugas && tpl.jenis_tugas.nama) {
                        $('#tpl-preview-jenis').show().find('span').text(tpl.jenis_tugas.nama);
                    } else {
                        $('#tpl-preview-jenis').hide();
                    }

                    // Show Sub Tugas badge
                    if (tpl.sub_tugas && tpl.sub_tugas.nama) {
                        $('#tpl-preview-subtugas').show().find('span').text(tpl.sub_tugas.nama);
                    } else {
                        $('#tpl-preview-subtugas').hide();
                    }
                });

                // Apply template button
                $('#btn-apply-template').on('click', function() {
                    const tplId = $tplSelector.val();
                    if(!tplId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Template',
                            text: 'Silakan pilih template terlebih dahulu dari dropdown.',
                        });
                        return;
                    }

                    const tpl = availableTemplates.find(t => t.id == tplId);
                    if(!tpl) return;

                    Swal.fire({
                        title: 'Terapkan Template?',
                        html: `<div class="text-left">
                            <p class="mb-2">Template: <strong>${tpl.nama}</strong></p>
                            <p class="small text-muted mb-0">Field berikut akan diganti:</p>
                            <ul class="small mb-0">
                                ${tpl.detail_tugas ? '<li>Detail Tugas</li>' : ''}
                                ${tpl.tembusan ? '<li>Tembusan</li>' : ''}
                                ${tpl.jenis_tugas ? '<li>Jenis Tugas</li>' : ''}
                                ${tpl.sub_tugas ? '<li>Sub Tugas (Tugas)</li>' : ''}
                            </ul>
                        </div>`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#fd7e14',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-magic mr-1"></i> Ya, Terapkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // 1. Set Detail Tugas (CKEditor)
                            if(window.detailEditor) {
                                window.detailEditor.setData(tpl.detail_tugas || '');
                            } else {
                                $('#detail_tugas_editor').val(tpl.detail_tugas || '');
                            }

                            // 2. Set Tembusan (Tagify)
                            const tembusanCsv = tpl.tembusan || '';
                            if(window.tagifyTembusan) {
                                window.tagifyTembusan.removeAllTags();
                                if(tembusanCsv) {
                                    window.tagifyTembusan.addTags(tembusanCsv.split(',').map(t => t.trim()).filter(t => t));
                                }
                            } else {
                                $('#tembusan-input').val(tembusanCsv);
                            }

                            // 3. Set Jenis Tugas (if exists)
                            if(tpl.jenis_tugas && tpl.jenis_tugas.nama) {
                                const jtName = tpl.jenis_tugas.nama;
                                const $jtSelect = $('#jenis_tugas');
                                if($jtSelect.find(`option[value="${jtName}"]`).length) {
                                    $jtSelect.val(jtName).trigger('change');
                                    
                                    // 4. Set Sub Tugas / Tugas (after Jenis is set) - NEW!
                                    if(tpl.sub_tugas && tpl.sub_tugas.nama) {
                                        // Wait for tugas dropdown to populate
                                        setTimeout(() => {
                                            const stName = tpl.sub_tugas.nama;
                                            const $tugasSelect = $('#tugas');
                                            if($tugasSelect.find(`option[value="${stName}"]`).length) {
                                                $tugasSelect.val(stName).trigger('change');
                                            }
                                        }, 300);
                                    }
                                }
                            }
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Template Diterapkan!',
                                text: 'Formulir telah diisi dengan data dari template.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                });
            @endif

            // ====== MODE TURUNAN (SUFFIX LETTER) LOGIC ======
            @php
                $showModeTurunan = auth()->user()->peran_id == 1 && (!$isEdit || ($isEdit && $tugas->status_surat === 'pending'));
            @endphp
            @if ($showModeTurunan)
                const $isTurunan = $('#is_turunan');
                const $turunanSection = $('#turunan-section');
                const $parentSelect = $('#parent_tugas_id');
                const $suffixPreview = $('#suffix-preview');
                const $nextSuffix = $('#next-suffix');
                const $suffixNomorPreview = $('#suffix-nomor-preview');

                // Toggle turunan section
                $isTurunan.on('change', function() {
                    const isChecked = $(this).is(':checked');
                    $turunanSection.toggle(isChecked);
                    
                    if (isChecked) {
                        // Disable komponen nomor biasa
                        $('#nomor_urut, #bulan, #tahun-nomor').prop('readonly', true);
                        $('#btn-reserve-nomor').prop('disabled', true);
                        $('#nomor_surat_lengkap_display').val('-- Mode Turunan Aktif --');
                    } else {
                        // Re-enable komponen nomor biasa
                        $('#bulan, #tahun-nomor').prop('readonly', false);
                        $('#btn-reserve-nomor').prop('disabled', false);
                        $suffixPreview.hide();
                        buildNomorPreview();
                    }
                });

                // Trigger on page load if already checked (old value)
                if ($isTurunan.is(':checked')) {
                    $isTurunan.trigger('change');
                }

                // Initialize Select2 for parent selector
                $parentSelect.select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Pilih Nomor Induk --',
                    allowClear: true,
                    width: '100%'
                });

                // Load next suffix when parent selected
                $parentSelect.on('change', function() {
                    const parentId = $(this).val();
                    if (!parentId) {
                        $suffixPreview.hide();
                        $('#nomor_surat_lengkap_hidden').val('');
                        return;
                    }
                    
                    // Show loading
                    $nextSuffix.text('...');
                    $suffixNomorPreview.text('Memuat...');
                    $suffixPreview.show();
                    
                    $.get('/ajax/surat-tugas/' + parentId + '/next-suffix')
                        .done(function(data) {
                            // Update suffix preview
                            $nextSuffix.text(data.suffix);
                            $suffixNomorPreview.text(data.nomor_preview);
                            $('#nomor_surat_lengkap_hidden').val(data.nomor_preview);
                            $('#nomor_surat_lengkap_display').val(data.nomor_preview);
                            
                            // ✅ AUTO-FILL form dari parent data
                            if (data.parent_data) {
                                const pd = data.parent_data;
                                
                                // Field teks biasa
                                if (pd.nama_umum) $('#nama_umum').val(pd.nama_umum);
                                if (pd.tempat) $('#tempat').val(pd.tempat);
                                if (pd.redaksi_pembuka) $('#redaksi_pembuka').val(pd.redaksi_pembuka);
                                if (pd.penutup) $('#penutup').val(pd.penutup);
                                
                                // Detail tugas (use correct ID: detail_tugas_editor)
                                if (pd.detail_tugas) {
                                    $('#detail_tugas_editor').val(pd.detail_tugas);
                                    // Update TinyMCE jika ada
                                    if (typeof tinymce !== 'undefined' && tinymce.get('detail_tugas_editor')) {
                                        tinymce.get('detail_tugas_editor').setContent(pd.detail_tugas);
                                    }
                                }
                                
                                // Datetime fields
                                if (pd.waktu_mulai) $('#waktu_mulai').val(pd.waktu_mulai);
                                if (pd.waktu_selesai) $('#waktu_selesai').val(pd.waktu_selesai);
                                
                                // Select fields - FIXED klasifikasi modal fields
                                if (pd.klasifikasi_surat_id) {
                                    // Set hidden ID (for form submission)
                                    $('#klasifikasi_surat_id').val(pd.klasifikasi_surat_id);
                                    // Set display field (readonly text showing label)
                                    if (pd.klasifikasi_label) {
                                        $('#klasifikasi_display').val(pd.klasifikasi_label);
                                    }
                                    // Set kode hidden field (for nomor generation)
                                    if (pd.klasifikasi_kode) {
                                        $('#klasifikasi_kode').val(pd.klasifikasi_kode);
                                    }
                                    // Trigger nomor regeneration
                                    if (typeof refreshNomorPreview === 'function') {
                                        refreshNomorPreview();
                                    }
                                    console.log('Klasifikasi auto-filled:', pd.klasifikasi_surat_id, pd.klasifikasi_label);
                                }
                                if (pd.asal_surat_id) {
                                    $('#asal_surat_id').val(pd.asal_surat_id).trigger('change');
                                }
                                if (pd.jenis_tugas) {
                                    $('#jenis_tugas').val(pd.jenis_tugas).trigger('change');
                                    // Tunggu sebentar agar dropdown tugas ter-load
                                    setTimeout(function() {
                                        if (pd.tugas) {
                                            $('#tugas').val(pd.tugas).trigger('change');
                                        }
                                    }, 300);
                                }
                                if (pd.status_penerima) $('#status_penerima').val(pd.status_penerima);
                                if (pd.penandatangan_id) $('#penandatangan_id').val(pd.penandatangan_id).trigger('change');
                                
                                // Tembusan (correct ID: tembusan-input, use Tagify API)
                                if (pd.tembusan) {
                                    const $tembusanInput = $('#tembusan-input');
                                    if ($tembusanInput.length) {
                                        // Set nilai input langsung
                                        $tembusanInput.val(pd.tembusan);
                                        
                                        // Jika menggunakan Tagify, update tags
                                        if ($tembusanInput[0] && $tembusanInput[0].tagify) {
                                            const tagifyInstance = $tembusanInput[0].tagify;
                                            const tembusanArr = pd.tembusan.split(',').map(t => t.trim()).filter(t => t);
                                            tagifyInstance.removeAllTags();
                                            tagifyInstance.addTags(tembusanArr);
                                        }
                                    }
                                }
                                
                                // Penerima Internal & Eksternal
                                // FIXED: Use penerimaState structure (internal is object, not array)
                                console.log('Auto-filling penerima...', pd);
                                
                                // Check if penerimaState and allUsersData exist (defined in form script)
                                if (typeof penerimaState !== 'undefined' && typeof allUsersData !== 'undefined') {
                                    console.log('penerimaState found, updating...');
                                    
                                    // Clear existing
                                    penerimaState.internal = {};
                                    penerimaState.eksternal = [];
                                    
                                    // Add internal penerima - MUST match penerimaState structure
                                    if (pd.penerima_internal && pd.penerima_internal.length > 0) {
                                        console.log('Adding internal penerima:', pd.penerima_internal);
                                        pd.penerima_internal.forEach(function(userId) {
                                            const u = allUsersData[userId];
                                            if (u) {
                                                penerimaState.internal[userId] = {
                                                    nama: u.nama_lengkap || u.nama || 'User #' + userId,
                                                    peran_id: u.peran_id || null
                                                };
                                            } else {
                                                // Fallback jika user tidak ada di allUsersData
                                                penerimaState.internal[userId] = {
                                                    nama: 'User #' + userId,
                                                    peran_id: null
                                                };
                                            }
                                        });
                                    }
                                    
                                    // Add external penerima
                                    if (pd.penerima_eksternal && pd.penerima_eksternal.length > 0) {
                                        console.log('Adding external penerima:', pd.penerima_eksternal);
                                        pd.penerima_eksternal.forEach(function(penerima) {
                                            penerimaState.eksternal.push({
                                                nama: penerima.nama || '',
                                                jabatan: penerima.jabatan || '',
                                                instansi: penerima.instansi || ''
                                            });
                                        });
                                    }
                                    
                                    console.log('Final penerimaState:', penerimaState);
                                    
                                    // Trigger render - function is in same scope
                                    setTimeout(function() {
                                        if (typeof renderPenerimaList === 'function') {
                                            console.log('Calling renderPenerimaList()...');
                                            renderPenerimaList();
                                            updateStatusPenerima();
                                        } else {
                                            console.warn('renderPenerimaList function not found in scope!');
                                        }
                                    }, 500);
                                } else {
                                    console.warn('penerimaState or allUsersData not defined!');
                                }
                                
                                // Show success notification
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data Ter-copy!',
                                    text: 'Form otomatis terisi dari surat induk',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    position: 'top-end',
                                    toast: true
                                });
                            }
                        })
                        .fail(function(xhr) {
                            const msg = xhr.responseJSON?.error || 'Gagal memuat suffix';
                            $nextSuffix.text('!');
                            $suffixNomorPreview.text(msg);
                            Swal.fire('Error', msg, 'error');
                        });
                });

                // Trigger if already has old value
                if ($parentSelect.val()) {
                    $parentSelect.trigger('change');
                }
            @endif

        });
    </script>
@endpush
