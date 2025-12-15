@extends('layouts.app')

@section('title', 'Audit Log')

@push('styles')
<style>
    body { background: #f7faff }
    .surat-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem 1.3rem 1.8rem;
        border-radius: 1.1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e0e6ed;
        display: flex;
        align-items: center;
        gap: 1.3rem
    }
    .surat-header .icon {
        background: linear-gradient(135deg, #6c757d 0, #495057 100%);
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px rgba(108,117,125,0.3);
        font-size: 1.8rem
    }
    .surat-header-title {
        font-weight: bold;
        color: #495057;
        font-size: 1.6rem;
        margin-bottom: .13rem;
        letter-spacing: -0.5px
    }
    .surat-header-desc {
        color: #636e7b;
        font-size: 1rem
    }
    .card { border-radius: 1rem; }
    @media (max-width:767.98px) {
        .surat-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.2rem 1rem;
            gap: .7rem
        }
        .surat-header-title { font-size: 1.18rem }
    }
</style>
@endpush

@section('content_header')
    <div class="surat-header mt-2">
        <span class="icon">
            <i class="fas fa-history text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">Audit Log Activity</div>
            <div class="surat-header-desc">Pantau <b>aktivitas pengguna</b> dan perubahan data di sistem.</div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Log</span>
                    <span class="info-box-number">{{ number_format($stats['total']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-calendar-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Hari Ini</span>
                    <span class="info-box-number">{{ number_format($stats['today']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-history"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">7 Hari Terakhir</span>
                    <span class="info-box-number">{{ number_format($stats['this_week']) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Pencarian</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i> <!-- Default collapsed, so show plus -->
                </button>
            </div>
        </div>
        <div class="card-body" style="display: none;">
            <form method="GET" action="{{ route('audit_logs.index') }}">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="small text-muted">User</label>
                        <select name="user_id" class="form-control form-control-sm select2">
                            <option value="">Semua User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ($validated['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small text-muted">Tipe Entitas</label>
                        <select name="entity_type" class="form-control form-control-sm">
                            <option value="">Semua Tipe</option>
                            @foreach($entityTypes as $key => $label)
                                <option value="{{ $key }}" {{ ($validated['entity_type'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small text-muted">Aksi</label>
                        <select name="action" class="form-control form-control-sm">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $key => $label)
                                <option value="{{ $key }}" {{ ($validated['action'] ?? '') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small text-muted">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $validated['date_from'] ?? '' }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small text-muted">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $validated['date_to'] ?? '' }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block mr-1">
                            Filter
                        </button>
                        <a href="{{ route('audit_logs.index') }}" class="btn btn-default btn-sm btn-block mt-0">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="card">
        <div class="card-header border-0">
            <h3 class="card-title">Daftar Log Aktivitas</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped text-nowrap">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Tipe</th>
                        <th>Objek</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                {{ $log->created_at->format('d/m/Y') }}<br>
                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                {{ $log->user_name ?? 'System' }}
                            </td>
                            <td>
                                @php
                                    $badgeClass = match ($log->action) {
                                        'create' => 'badge-success',
                                        'update' => 'badge-warning',
                                        'delete' => 'badge-danger',
                                        'approve' => 'badge-primary',
                                        'reject' => 'badge-danger',
                                        'login' => 'badge-info',
                                        'logout' => 'badge-secondary',
                                        default => 'badge-light'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $log->action_label }}</span>
                            </td>
                            <td>{{ $log->entity_type_label }}</td>
                            <td>
                                <span title="{{ $log->entity_name }}">
                                    {{ Str::limit($log->entity_name ?? '-', 30) }}
                                </span>
                            </td>
                            <td class="text-muted text-sm">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-2x mb-2"></i><br>
                                Tidak ada log aktivitas yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $logs->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto open filter if params exist
        @if(request()->hasAny(['user_id', 'entity_type', 'action', 'date_from', 'date_to']))
            $('.card-primary').CardWidget('expand');
        @endif

        if($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
    });
</script>
@endpush
