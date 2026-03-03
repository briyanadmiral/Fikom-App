@extends('layouts.app')

@section('title', 'Audit Log')

@push('styles')
<style>
    /* Modern Header */
    .surat-header {
        background: #fff;
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 4px 25px rgba(0,0,0,0.03);
        border: 1px solid #eef2f7;
    }
    .surat-header .icon {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        color: white;
        font-size: 1.75rem;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
    }
    .surat-header-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
        letter-spacing: -0.5px;
    }
    .surat-header-desc {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    /* Stats Cards */
    .stats-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid #eef2f7;
        box-shadow: 0 2px 15px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: transform 0.2s;
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stats-info h6 {
        margin: 0;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stats-info h3 {
        margin: 0.25rem 0 0;
        color: #1e293b;
        font-weight: 700;
        font-size: 1.75rem;
    }

    /* Filter Card */
    .filter-card {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #eef2f7;
        box-shadow: 0 2px 15px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .filter-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .filter-title {
        font-weight: 600;
        color: #334155;
        margin: 0;
        font-size: 1rem;
    }

    /* Data Table */
    .table-responsive {
        border-radius: 1rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.02);
        background: #fff;
    }
    .table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #e2e8f0;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
    }
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    .table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    /* Badges */
    .badge-custom {
        padding: 0.4em 0.8em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 6px;
    }

    /* Entity Link */
    .entity-link {
        color: #4f46e5;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: color 0.2s;
    }
    .entity-link:hover {
        color: #4338ca;
        text-decoration: underline;
    }

    /* Avatar small */
    .avatar-xs {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 0.5rem;
        border: 2px solid #e2e8f0;
    }

    /* Timeline dot for Time column */
    .timeline-time {
        display: flex;
        flex-direction: column;
    }
    .time-main {
        font-weight: 600;
        color: #1e293b;
    }
    .time-sub {
        font-size: 0.8rem;
        color: #94a3b8;
    }
</style>
@endpush

@section('content_header')
    <div class="surat-header mt-2">
        <span class="icon">
            <i class="fas fa-history"></i>
        </span>
        <div class="flex-grow-1">
            <div class="surat-header-title">Audit Log Activity</div>
            <div class="surat-header-desc">Pantau <b>aktivitas pengguna</b> dan riwayat perubahan data pada sistem secara real-time.</div>
        </div>
        <div class="text-right">
            <a href="{{ route('audit_logs.export', request()->all()) }}" class="btn btn-outline-success shadow-sm rounded-pill px-4">
                <i class="fas fa-file-excel mr-2"></i> Export Excel
            </a>
            
            @if(auth()->user()->peran_id === 1)
                <button type="button" class="btn btn-outline-danger shadow-sm ml-2 rounded-pill px-4" data-toggle="modal" data-target="#modalPrune">
                    <i class="fas fa-trash-alt mr-2"></i> Bersihkan Log
                </button>
            @endif
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid pb-5">
    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stats-card">
                <div class="stats-icon bg-info-soft text-info" style="background: rgba(56, 189, 248, 0.1); color: #0284c7;">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stats-info">
                    <h6>Total Log Aktivitas</h6>
                    <h3>{{ number_format($stats['total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stats-card">
                <div class="stats-icon bg-success-soft text-success" style="background: rgba(74, 222, 128, 0.1); color: #16a34a;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stats-info">
                    <h6>Aktivitas Hari Ini</h6>
                    <h3>{{ number_format($stats['today']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon bg-warning-soft text-warning" style="background: rgba(251, 191, 36, 0.1); color: #d97706;">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stats-info">
                    <h6>7 Hari Terakhir</h6>
                    <h3>{{ number_format($stats['this_week']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {{-- Filters --}}
            <div class="filter-card mb-4">
                <div class="filter-header" data-toggle="collapse" data-target="#filterPanel" style="cursor: pointer;">
                    <h3 class="filter-title"><i class="fas fa-filter mr-2 text-primary"></i> Filter Pencarian</h3>
                    <i class="fas fa-chevron-down text-muted"></i>
                </div>
                <div id="filterPanel" class="collapse {{ request()->hasAny(['user_id', 'entity_type', 'action', 'date_from', 'date_to']) ? 'show' : '' }}">
                    <div class="card-body bg-white p-4">
                        <form method="GET" action="{{ route('audit_logs.index') }}">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">User</label>
                                    <select name="user_id" class="form-control select2">
                                        <option value="">Semua User</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ ($validated['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                                {{ $user->nama_lengkap }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Tipe Entitas</label>
                                    <select name="entity_type" class="form-control select2">
                                        <option value="">Semua Tipe</option>
                                        @foreach($entityTypes as $key => $label)
                                            <option value="{{ $key }}" {{ ($validated['entity_type'] ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Aksi</label>
                                    <select name="action" class="form-control">
                                        <option value="">Semua Aksi</option>
                                        @foreach($actions as $key => $label)
                                            <option value="{{ $key }}" {{ ($validated['action'] ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Dari Tanggal</label>
                                    <input type="date" name="date_from" value="{{ $validated['date_from'] ?? '' }}"
                                           class="form-control">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="small text-muted font-weight-bold text-uppercase">Sampai Tanggal</label>
                                    <input type="date" name="date_to" value="{{ $validated['date_to'] ?? '' }}"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 text-right">
                                    <a href="{{ route('audit_logs.index') }}" class="btn btn-light text-muted mr-2">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-search mr-1"></i> Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Log Table --}}
            <div class="card border-0 shadow-sm" style="border-radius: 1rem; overflow: hidden;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title font-weight-bold text-dark m-0">Daftar Log Aktivitas</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="18%">Waktu</th>
                                <th width="20%">User</th>
                                <th width="15%" class="text-center">Aksi</th>
                                <th width="25%">Entitas / Objek</th>
                                <th width="15%">Browser / IP</th>
                                <th width="7%" class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <div class="timeline-time">
                                            <span class="time-main">{{ $log->formatted_date }}</span>
                                            <span class="time-sub">{{ $log->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($log->user && $log->user->foto_path)
                                                 <img src="{{ asset('storage/' . $log->user->foto_path) }}" class="avatar-xs" alt="User Image">
                                            @else
                                                <div class="avatar-xs bg-light d-flex align-items-center justify-content-center text-primary font-weight-bold">
                                                    {{ substr($log->user_name ?? 'S', 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="d-block font-weight-bold text-dark">{{ $log->user_name ?? 'System' }}</span>
                                                <small class="text-muted">{{ $log->user ? ($log->user->peran->nama ?? 'Unknown') : 'System' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-custom {{ $log->action_badge_class }}">
                                            {{ $log->action_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-xs font-weight-bold text-uppercase text-muted mb-1">
                                                {{ $log->entity_type_label }}
                                            </span>
                                            @if($log->entity_route)
                                                <a href="{{ $log->entity_route }}" class="entity-link" target="_blank" title="Buka Detail">
                                                    {{ Str::limit($log->entity_name ?? '-', 40) }} 
                                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            @else
                                                <span class="text-dark font-weight-medium" title="{{ $log->entity_name }}">
                                                    {{ Str::limit($log->entity_name ?? '-', 40) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-dark font-weight-medium" style="font-size: 0.85rem">{{ $log->ip_address }}</span>
                                            <span class="text-muted text-xs" title="{{ $log->user_agent }}">{{ Str::limit($log->browser_info, 20) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light text-primary btn-detail rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0;" data-id="{{ $log->id }}" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="py-5">
                                            <img src="https://cdni.iconscout.com/illustration/premium/thumb/no-data-found-8867280-7265556.png?f=webp" alt="No Data" style="max-width: 200px; opacity: 0.7;">
                                            <h5 class="text-muted mt-3">Tidak ada log aktivitas ditemukan</h5>
                                            <p class="text-muted small">Coba ubah filter pencarian Anda atau reset filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $logs->firstItem() ?? 0 }} sampai {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri
                        </div>
                        <div>
                            {{ $logs->withQueryString()->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detail Modal --}}
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Prune Modal --}}
@if(auth()->user()->peran_id === 1)
<div class="modal fade" id="modalPrune" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <form action="{{ route('audit_logs.prune') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white border-0" style="border-radius: 1rem 1rem 0 0;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-trash-alt mr-2"></i> Bersihkan Log Lama</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-danger-soft rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: rgba(220, 38, 38, 0.1);">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Konfirmasi Pembersihan</h5>
                        <p class="text-muted">Tindakan ini akan <strong>menghapus permanen</strong> log aktivitas yang sudah lama untuk menghemat ruang penyimpanan.</p>
                    </div>
                    
                    <div class="form-group bg-light p-3 rounded border">
                        <label class="font-weight-bold text-dark mb-2">Hapus log yang lebih tua dari:</label>
                        <select name="retention_period" class="form-control custom-select">
                            <option value="1">1 Tahun (Rekomendasi)</option>
                            <option value="2">2 Tahun</option>
                            <option value="3">3 Tahun</option>
                            <option value="6m">6 Bulan</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning small border-0 mb-0">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Perhatian:</strong> Data log yang sudah dihapus tidak dapat dikembalikan lagi.
                    </div>
                </div>
                <div class="modal-footer bg-light border-0" style="border-radius: 0 0 1rem 1rem;">
                    <button type="button" class="btn btn-light font-weight-bold text-muted" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger font-weight-bold px-4">Ya, Bersihkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        // Detail Modal Handler
        $(document).on('click', '.btn-detail', function() {
            var id = $(this).data('id');
            var url = "{{ route('audit_logs.show', ':id') }}".replace(':id', id);
            
            $('#modalDetail').modal('show');
            $('#modalDetail .modal-content').load(url, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "<div class='modal-body text-center py-5'><i class='fas fa-exclamation-circle fa-3x text-danger mb-3'></i><h5 class='text-danger'>Error Loading Data</h5><p class='text-muted'>" + xhr.status + " " + xhr.statusText + "</p></div>";
                    $('#modalDetail .modal-content').html(msg);
                }
            });
        });
        
        // Tooltip initialization
        $('[title]').tooltip();
    });
</script>
@endpush
