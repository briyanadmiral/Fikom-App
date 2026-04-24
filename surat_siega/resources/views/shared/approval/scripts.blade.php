<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('{{ $formId ?? 'form-approve' }}');
        const pane = document.getElementById('{{ $previewPaneId ?? 'approval-preview-pane' }}');
        const content = document.getElementById('{{ $previewContentId ?? 'approval-preview-content' }}');
        const spinner = document.getElementById('{{ $previewSpinnerId ?? 'approval-preview-spinner' }}');
        const resetButton = document.getElementById('{{ $resetButtonId ?? 'btn-reset-approval-layout' }}');
        const submitButton = document.getElementById('{{ $submitButtonId ?? 'btn-approve-submit' }}');
        const previewUrl = @json($previewUrl ?? null);
        const defaults = @json($defaults ?? []);

        if (!form || !pane || !content || !previewUrl) {
            return;
        }

        const controlMap = {
            ttd_w_mm: {
                num: document.querySelector('input[name="ttd_w_mm"]'),
                slider: document.querySelector('input[name="ttd_w_mm_slider"]'),
            },
            cap_w_mm: {
                num: document.querySelector('input[name="cap_w_mm"]'),
                slider: document.querySelector('input[name="cap_w_mm_slider"]'),
            },
            cap_opacity: {
                num: document.querySelector('input[name="cap_opacity"]'),
                slider: document.querySelector('input[name="cap_opacity_slider"]'),
            },
        };

        let dragTarget = null;
        let resizeTarget = null;
        let startX = 0;
        let startY = 0;
        let initialOffsetX = 0;
        let initialOffsetY = 0;
        let startWidth = 0;

        function debounce(fn, wait = 250) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        function getMmRatio() {
            const sheet = content.querySelector('.sheet');
            if (!sheet) {
                return 0.265;
            }

            return 210 / sheet.getBoundingClientRect().width;
        }

        function getType(el) {
            return el.classList.contains('cap') ? 'cap' : 'ttd';
        }

        function syncControl(key, value) {
            const control = controlMap[key];
            if (!control) return;
            if (control.num) control.num.value = value;
            if (control.slider) control.slider.value = value;
        }

        function loadPreview() {
            const params = new URLSearchParams({
                ttd_w_mm: controlMap.ttd_w_mm.num?.value ?? defaults.ttd_w_mm ?? 42,
                cap_w_mm: controlMap.cap_w_mm.num?.value ?? defaults.cap_w_mm ?? 35,
                cap_opacity: controlMap.cap_opacity.num?.value ?? defaults.cap_opacity ?? 0.95,
                ttd_x_mm: document.getElementById('ttd_x_mm')?.value ?? 0,
                ttd_y_mm: document.getElementById('ttd_y_mm')?.value ?? 0,
                cap_x_mm: document.getElementById('cap_x_mm')?.value ?? 0,
                cap_y_mm: document.getElementById('cap_y_mm')?.value ?? 0,
            });

            if (spinner) spinner.style.display = 'block';

            fetch(`${previewUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                    initInteractivePreview();
                })
                .catch(() => {
                    content.innerHTML = '<div class="alert alert-danger m-4">Gagal memuat pratinjau approval.</div>';
                })
                .finally(() => {
                    if (spinner) spinner.style.display = 'none';
                });
        }

        const debouncedPreview = debounce(loadPreview, 220);

        function initInteractivePreview() {
            content.querySelectorAll('.ttd, .cap').forEach((el) => {
                el.style.cursor = 'move';
                el.addEventListener('mousedown', dragStart);
                el.addEventListener('wheel', resizeWheel, { passive: false });
            });

            content.querySelectorAll('.resize-handle').forEach((handle) => {
                handle.addEventListener('mousedown', resizeStart);
            });
        }

        function dragStart(event) {
            if (event.target.classList.contains('resize-handle')) {
                return;
            }

            event.preventDefault();
            dragTarget = event.currentTarget;
            const type = getType(dragTarget);
            initialOffsetX = parseInt(document.getElementById(`${type}_x_mm`)?.value || '0', 10);
            initialOffsetY = parseInt(document.getElementById(`${type}_y_mm`)?.value || '0', 10);
            startX = event.clientX;
            startY = event.clientY;
            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
        }

        function dragMove(event) {
            if (!dragTarget) return;
            const mmRatio = getMmRatio();
            const newX = initialOffsetX + Math.round((event.clientX - startX) * mmRatio);
            const newY = initialOffsetY + Math.round((startY - event.clientY) * mmRatio);
            const type = getType(dragTarget);
            const wrapper = dragTarget.closest('.ttd-area-sign');

            if (wrapper) {
                wrapper.style.setProperty(`--${type}-x`, `${newX}mm`);
                wrapper.style.setProperty(`--${type}-y`, `${newY}mm`);
            }
        }

        function dragEnd(event) {
            if (!dragTarget) return;
            const mmRatio = getMmRatio();
            const newX = initialOffsetX + Math.round((event.clientX - startX) * mmRatio);
            const newY = initialOffsetY + Math.round((startY - event.clientY) * mmRatio);
            const type = getType(dragTarget);

            document.getElementById(`${type}_x_mm`).value = newX;
            document.getElementById(`${type}_y_mm`).value = newY;

            dragTarget = null;
            document.removeEventListener('mousemove', dragMove);
            document.removeEventListener('mouseup', dragEnd);
            debouncedPreview();
        }

        function resizeStart(event) {
            event.preventDefault();
            event.stopPropagation();
            resizeTarget = event.target.closest('.ttd, .cap');
            const type = getType(resizeTarget);
            startX = event.clientX;
            startWidth = parseInt(controlMap[`${type}_w_mm`].num?.value || '40', 10);
            document.addEventListener('mousemove', resizeMove);
            document.addEventListener('mouseup', resizeEnd);
        }

        function resizeMove(event) {
            if (!resizeTarget) return;
            const type = getType(resizeTarget);
            const limit = type === 'ttd' ? 150 : 100;
            const newWidth = Math.max(10, Math.min(limit, startWidth + Math.round((event.clientX - startX) * getMmRatio() * 2)));
            const wrapper = resizeTarget.closest('.ttd-area-sign');

            if (wrapper) {
                wrapper.style.setProperty(`--${type}-w`, `${newWidth}mm`);
            }
        }

        function resizeEnd(event) {
            if (!resizeTarget) return;
            const type = getType(resizeTarget);
            const limit = type === 'ttd' ? 150 : 100;
            const newWidth = Math.max(10, Math.min(limit, startWidth + Math.round((event.clientX - startX) * getMmRatio() * 2)));
            syncControl(`${type}_w_mm`, newWidth);
            resizeTarget = null;
            document.removeEventListener('mousemove', resizeMove);
            document.removeEventListener('mouseup', resizeEnd);
            debouncedPreview();
        }

        function resizeWheel(event) {
            event.preventDefault();
            const type = getType(event.currentTarget);
            const current = parseInt(controlMap[`${type}_w_mm`].num?.value || '40', 10);
            const limit = type === 'ttd' ? 150 : 100;
            const next = Math.max(10, Math.min(limit, current + (event.deltaY < 0 ? 2 : -2)));
            const wrapper = event.currentTarget.closest('.ttd-area-sign');
            if (wrapper) {
                wrapper.style.setProperty(`--${type}-w`, `${next}mm`);
            }
            syncControl(`${type}_w_mm`, next);
            debouncedPreview();
        }

        Object.values(controlMap).forEach(({ num, slider }) => {
            if (!num || !slider) return;
            slider.addEventListener('input', () => {
                num.value = slider.value;
                debouncedPreview();
            });
            num.addEventListener('input', () => {
                slider.value = num.value;
                debouncedPreview();
            });
        });

        resetButton?.addEventListener('click', function () {
            syncControl('ttd_w_mm', defaults.ttd_w_mm ?? 42);
            syncControl('cap_w_mm', defaults.cap_w_mm ?? 35);
            syncControl('cap_opacity', defaults.cap_opacity ?? 0.95);
            document.getElementById('ttd_x_mm').value = defaults.ttd_x_mm ?? 0;
            document.getElementById('ttd_y_mm').value = defaults.ttd_y_mm ?? 0;
            document.getElementById('cap_x_mm').value = defaults.cap_x_mm ?? 0;
            document.getElementById('cap_y_mm').value = defaults.cap_y_mm ?? 0;
            loadPreview();
        });

        form.addEventListener('submit', function () {
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            }
        });

        initInteractivePreview();
    });
</script>
