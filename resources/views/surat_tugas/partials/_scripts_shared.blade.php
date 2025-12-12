<script>
    $(function() {
        // =========================
        // ==== PARAMETER INPUT ====
        // =========================
        const MODE = "{{ $mode ?? 'list' }}";
        const tableId = @json($tableId ?? '#table-tugas');

        // i18n & empty messages
        const i18nUrl = @json($i18nUrl ?? '/assets/datatables/i18n/id.json');
        const emptyDefaultMsg = @json($emptyDefaultMsg ?? 'Tidak ada data surat tugas.');
        const emptyApproveMsg = @json($emptyApproveMsg ?? 'Tidak ada surat yang perlu Anda setujui.');
        const moduleName = @json($moduleName ?? 'Surat Tugas'); // untuk teks konfirmasi

        // selector filter (bisa diganti jika id-nya beda)
        const searchSelector = @json($searchSelector ?? '#globalSearch');
        const statusFilterSelector = @json($statusFilterSelector ?? '#statusFilter');
        const resetBtnSelector = @json($resetBtnSelector ?? '#resetFilters');

        // konfigurasi kolom (boleh isi salah satu: byIndex ATAU byHeaderText)
        // default: auto-cari "tgl surat" dan "status" via header text
        const orderColIndexFallback = {{ $orderColIndex ?? -1 }};
        const statusColIndexFallback = {{ $statusColIndex ?? -1 }};

        // NOTE: encode dulu, lowercasenya di-JS (hindari .toLowerCase() di Blade)
        const orderHeaderText = @json($orderHeaderText ?? 'tgl surat');
        const statusHeaderText = @json($statusHeaderText ?? 'status');
        const orderHeaderLc = (orderHeaderText || '').toLowerCase();
        const statusHeaderLc = (statusHeaderText || '').toLowerCase();

        // kolom non-urut/non-search: bisa by index atau by header text
        const nonOrderableIndices = {!! json_encode($nonOrderable ?? []) !!};
        const nonOrderableHeaders = (@json($nonOrderableHeaders ?? [])).map(h => (h || '').toLowerCase());

        // fitur opsional
        const enableQuickView = @json($enableQuickView ?? true);
        const quickView = {
            modalId: @json($quickView['modalId'] ?? '#quickViewModal'),
            triggerSelector: @json($quickView['triggerSelector'] ?? '.quick-view')
        };
        const enableDelete = @json($enableDelete ?? true);
        const deleteCfg = {
            selector: @json($delete['selector'] ?? '.btn-delete'),
            method: @json($delete['method'] ?? 'DELETE'),
        };
        const csrfToken = @json($csrfToken ?? csrf_token());

        // =========================
        // ======  HELPERS   ======
        // =========================
        const emptyMsg = MODE === 'approve-list' ? emptyApproveMsg : emptyDefaultMsg;
        const debounce = (fn, d = 220) => {
            let t;
            return (...a) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...a), d);
            };
        };

        const $table = $(tableId);
        if (!$table.length) return;

        const thTexts = $table.find('thead th').map((_, th) => $(th).text().trim().toLowerCase()).get();

        const findColIdxByHeader = (textLc) => thTexts.findIndex(t => t === textLc);
        const dateColIdx = (orderColIndexFallback >= 0) ? orderColIndexFallback : findColIdxByHeader(
            orderHeaderLc);
        const statusColIdx = (statusColIndexFallback >= 0) ? statusColIndexFallback : findColIdxByHeader(
            statusHeaderLc);

        // gabung non-orderable: indeks langsung + hasil dari header text
        const nonOrderableIdxFromHeaders = nonOrderableHeaders
            .map(h => findColIdxByHeader(h))
            .filter(i => i >= 0);
        const nonOrderableFinal = [...new Set([...(nonOrderableIndices || []), ...nonOrderableIdxFromHeaders])];

        // =========================
        // ====== DATATABLES  =====
        // =========================
        const dt = $table.DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: i18nUrl,
                emptyTable: emptyMsg
            },
            order: [
                [dateColIdx >= 0 ? dateColIdx : 0, 'desc']
            ],
            columnDefs: nonOrderableFinal.length ? [{
                targets: nonOrderableFinal,
                orderable: false,
                searchable: false
            }] : [],
        }).on('draw', function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        $('[data-toggle="tooltip"]').tooltip();

        // =========================
        // ===== FILTER / SEARCH ===
        // =========================
        if ($(searchSelector).length) {
            $(searchSelector).on('keyup', debounce(function() {
                dt.search(this.value).draw();
            }, 180));
        }
        if ($(statusFilterSelector).length && statusColIdx >= 0) {
            $(statusFilterSelector).on('change', function() {
                const v = this.value;
                dt.column(statusColIdx).search(v ? '^' + v + '$' : '', true, false).draw();
            });
        }
        if ($(resetBtnSelector).length) {
            $(resetBtnSelector).on('click', function(e) {
                e.preventDefault();
                $(searchSelector).val('');
                $(statusFilterSelector).val('');
                dt.search('').columns().search('').draw();
            });
        }

        // =========================
        // ======  FLASH MSG   =====
        // =========================
        // (Handled via Global Layout - app.blade.php)

        // =========================
        // ===== QUICK VIEW  =======
        // =========================
        if (enableQuickView) {
            $table.on('click', quickView.triggerSelector, function(e) {
                e.preventDefault();
                const url = $(this).data('url') || $(this).attr('href');
                if (!url) return;

                const $m = $(quickView.modalId);
                const $s = $m.find('.quickview-spinner');
                const $f = $m.find('iframe');

                $s.show();
                $f.off('load').on('load', function() {
                    $s.hide();
                });
                $f.attr('src', url);
                $m.modal('show');
            });

            $(quickView.modalId).on('hidden.bs.modal', function() {
                const $f = $(this).find('iframe');
                $f.off('load').attr('src', 'about:blank');
                $('.quickview-spinner').hide();
            });
        }

        // =========================
        // =====  DELETE DRAFT  ====
        // =========================
        if (enableDelete) {
            $(document).on('click', deleteCfg.selector, function(e) {
                e.preventDefault();
                const url = $(this).data('url');
                const nomor = $(this).data('nomor') || '—';

                Swal.fire({
                    title: `Hapus Draft ${moduleName}?`,
                    html: `${moduleName} <b>${nomor}</b> akan dihapus secara permanen.`,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                    cancelButtonText: 'Batal',
                    footer: '<small class="text-muted">Aksi ini tidak dapat dibatalkan!</small>'
                }).then(res => {
                    if (!res.isConfirmed) return;
                    const $form = $('<form>', {
                            method: 'POST',
                            action: url,
                            style: 'display:none'
                        })
                        .append($('<input>', {
                            type: 'hidden',
                            name: '_token',
                            value: csrfToken
                        }))
                        .append($('<input>', {
                            type: 'hidden',
                            name: '_method',
                            value: deleteCfg.method
                        }));
                    $('body').append($form);
                    $form.trigger('submit');
                });
            });
        }
    });
</script>
