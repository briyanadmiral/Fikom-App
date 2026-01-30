<div class="modal-header">
    <h5 class="modal-title">Detail Aktivitas</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>User:</strong> {{ $audit_log->user_name }}
        </div>
        <div class="col-md-6 text-right">
            <strong>Waktu:</strong> {{ $audit_log->formatted_date }}
        </div>
        <div class="col-md-12 mt-2">
            <strong>Aksi:</strong> <span class="badge {{ $audit_log->action_badge_class }}">{{ $audit_log->action_label }}</span>
            <span class="mx-2">&bull;</span>
            <strong>Objek:</strong> {{ $audit_log->entity_type_label }} - {{ $audit_log->entity_name }}
        </div>
    </div>

    @if($audit_log->action === 'update')
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th style="width: 30%">Field</th>
                    <th style="width: 35%">Sebelum</th>
                    <th style="width: 35%">Sesudah</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $old = $audit_log->old_values ?? [];
                    $new = $audit_log->new_values ?? [];
                    $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                @endphp

                @forelse($keys as $key)
                    @php
                        $oldVal = $old[$key] ?? '-';
                        $newVal = $new[$key] ?? '-';
                        // Skip if values are identical (sometimes happens)
                        if ($oldVal === $newVal) continue;
                    @endphp
                    <tr>
                        <td class="font-weight-bold">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                        <td class="text-muted bg-light"><small>{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</small></td>
                        <td><small>{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Tidak ada perubahan data yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <div class="card bg-light">
            <div class="card-body">
                <h6>Data:</h6>
                <pre class="mb-0 small">{{ json_encode($audit_log->new_values ?? $audit_log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    @endif
    
    <div class="mt-3 small text-muted">
        IP Address: {{ $audit_log->ip_address }} &bull; Device: {{ $audit_log->browser_info }}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
</div>
