<div class="modal-header border-0 pb-0">
    <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 1.5rem; top: 1.5rem; z-index: 10;">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body p-4 pt-2">
    <div class="text-center mb-4">
        <div class="mb-3 d-inline-flex align-items-center justify-content-center">
            @if($audit_log->action === 'delete')
                <div class="bg-danger text-white rounded-circle p-3 shadow">
                    <i class="fas fa-trash-alt fa-2x"></i>
                </div>
            @elseif($audit_log->action === 'create')
                <div class="bg-success text-white rounded-circle p-3 shadow">
                    <i class="fas fa-plus fa-2x"></i>
                </div>
            @elseif($audit_log->action === 'update')
                <div class="bg-primary text-white rounded-circle p-3 shadow">
                    <i class="fas fa-edit fa-2x"></i>
                </div>
            @else
                <div class="bg-secondary text-white rounded-circle p-3 shadow">
                    <i class="fas fa-info fa-2x"></i>
                </div>
            @endif
        </div>
        <h4 class="font-weight-bold text-dark mb-1">{{ ucwords($audit_log->action_label) }}</h4>
        <p class="text-muted small">
            <i class="far fa-clock mr-1"></i> {{ $audit_log->formatted_date }} 
            <span class="mx-1">&bull;</span> 
            {{ $audit_log->created_at->diffForHumans() }}
        </p>
    </div>

    <div class="card mb-4 border-0 shadow-sm bg-light">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-md-6 border-right">
                    <small class="text-uppercase text-muted font-weight-bold d-block mb-1">Dilakukan Oleh</small>
                    <div class="d-flex align-items-center">
                        @if($audit_log->user && $audit_log->user->foto_path)
                            <img src="{{ asset('storage/' . $audit_log->user->foto_path) }}" class="rounded-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-white text-primary border d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px; font-weight: bold;">
                                {{ substr($audit_log->user_name ?? 'S', 0, 1) }}
                            </div>
                        @endif
                        <span class="font-weight-bold text-dark">{{ $audit_log->user_name ?? 'System' }}</span>
                    </div>
                </div>
                <div class="col-md-6 pl-4">
                    <small class="text-uppercase text-muted font-weight-bold d-block mb-1">Objek / Entitas</small>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-light border mr-2">{{ $audit_log->entity_type_label }}</span>
                        <span class="text-dark font-weight-medium text-truncate" style="max-width: 150px;" title="{{ $audit_log->entity_name }}">
                            {{ $audit_log->entity_name ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h6 class="font-weight-bold text-dark mb-3 pl-1 border-left-primary pl-2" style="border-left: 4px solid #4f46e5;">
        Rincian Perubahan
    </h6>

    @if($audit_log->action === 'update')
        <div class="table-responsive rounded border mb-0">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-top-0 text-muted font-weight-bold text-uppercase small" style="width: 25%">Field</th>
                        <th class="border-top-0 text-danger font-weight-bold text-uppercase small" style="width: 37.5%">
                            <i class="fas fa-minus-circle mr-1"></i> Sebelum
                        </th>
                        <th class="border-top-0 text-success font-weight-bold text-uppercase small" style="width: 37.5%">
                            <i class="fas fa-plus-circle mr-1"></i> Sesudah
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $old = $audit_log->old_values ?? [];
                        $new = $audit_log->new_values ?? [];
                        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                        $changed = false;

                        // Helper formatting function
                        $formatValue = function($value) {
                            if (is_string($value) && (
                                preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{6}Z)?$/', $value) ||
                                preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)
                            )) {
                                try {
                                    return \Carbon\Carbon::parse($value)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
                                } catch (\Exception $e) {
                                    return $value;
                                }
                            }
                            return $value;
                        };
                    @endphp

                    @foreach($keys as $key)
                        @php
                            $oldVal = $old[$key] ?? null;
                            $newVal = $new[$key] ?? null;
                            
                            // Normalisasi null/empty string untuk perbandingan
                            if (is_null($oldVal) && is_null($newVal)) continue;
                            if ($oldVal === $newVal) continue;
                            
                            $changed = true;

                            // Apply formatting
                            $displayOld = is_string($oldVal) ? $formatValue($oldVal) : $oldVal;
                            $displayNew = is_string($newVal) ? $formatValue($newVal) : $newVal;
                        @endphp
                        <tr>
                            <td class="font-weight-bold text-dark bg-light align-middle">
                                {{ ucwords(str_replace(['_', '-'], ' ', $key)) }}
                            </td>
                            <td class="bg-white text-danger border-right-0">
                                @if(is_array($oldVal))
                                    <pre class="mb-0 small text-danger bg-light p-2 rounded border-0">{{ json_encode($oldVal, JSON_PRETTY_PRINT) }}</pre>
                                @elseif(is_bool($oldVal))
                                    <span class="badge badge-danger">{{ $oldVal ? 'True' : 'False' }}</span>
                                @elseif(empty($oldVal) && $oldVal !== 0 && $oldVal !== '0')
                                    <span class="text-muted font-italic small">Empty</span>
                                @else
                                    <div style="word-break: break-all;">{{ $displayOld }}</div>
                                @endif
                            </td>
                            <td class="bg-white text-success border-left-0">
                                @if(is_array($newVal))
                                    <pre class="mb-0 small text-success bg-light p-2 rounded border-0">{{ json_encode($newVal, JSON_PRETTY_PRINT) }}</pre>
                                @elseif(is_bool($newVal))
                                    <span class="badge badge-success">{{ $newVal ? 'True' : 'False' }}</span>
                                @elseif(empty($newVal) && $newVal !== 0 && $newVal !== '0')
                                    <span class="text-muted font-italic small">Empty</span>
                                @else
                                    <div style="word-break: break-all;">{{ $displayNew }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    @if(!$changed)
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle mb-2 fa-lg"></i><br>
                                Tidak ada perubahan data yang signifikan tercatat.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @else
        <div class="card bg-dark text-light border-0">
            <div class="card-header bg-dark border-bottom border-secondary d-flex justify-content-between align-items-center py-2">
                <small class="font-weight-bold text-uppercase text-muted">Raw Data</small>
                <button class="btn btn-xs btn-outline-light" onclick="copyToClipboard(this)">
                    <i class="far fa-copy"></i> Copy
                </button>
            </div>
            <div class="card-body p-0">
                <pre class="mb-0 p-3 small text-monospace" style="color: #a5b3ce; max-height: 300px; overflow-y: auto;">{{ json_encode($audit_log->new_values ?? $audit_log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    @endif
    
    <div class="mt-4 pt-3 border-top d-flex justify-content-between text-muted small">
        <span><i class="fas fa-desktop mr-1"></i> {{ $audit_log->ip_address }}</span>
        <span><i class="fas fa-globe mr-1"></i> {{ Str::limit($audit_log->browser_info, 30) }}</span>
    </div>
</div>
<div class="modal-footer border-0 pt-0 pb-4 pr-4">
    <button type="button" class="btn btn-light font-weight-bold text-muted px-4" data-dismiss="modal">Tutup</button>
</div>

<script>
function copyToClipboard(btn) {
    const pre = btn.closest('.card').querySelector('pre');
    const textArea = document.createElement("textarea");
    textArea.value = pre.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("Copy");
    textArea.remove();
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => {
        btn.innerHTML = originalHtml;
    }, 2000);
}
</script>
