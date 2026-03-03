@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
<style>
    /* Modern Card & Layout */
    .notif-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .card-modern {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        background: #fff;
        overflow: hidden;
    }
    
    /* Header Styling */
    .notif-header {
        padding: 2rem 2.5rem;
        background: linear-gradient(to right, #ffffff, #f8f9fa);
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .header-title h4 {
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.25rem;
        font-size: 1.5rem;
        letter-spacing: -0.5px;
    }
    
    .header-subtitle {
        color: #6c757d;
        font-size: 0.95rem;
    }
    
    /* Action Buttons */
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
    }
    
    .btn-mark-read {
        background: #eef2ff;
        color: #4361ee;
    }
    
    .btn-mark-read:hover {
        background: #dbe4ff;
        color: #3046bc;
        transform: translateY(-1px);
    }
    
    .btn-clear {
        background: #fff0f0;
        color: #ef476f;
    }
    
    .btn-clear:hover {
        background: #ffe0e0;
        color: #d63056;
        transform: translateY(-1px);
    }

    /* Tabs Styling */
    .custom-tabs .nav-link {
        border: none;
        color: #8898aa;
        font-weight: 600;
        padding: 1rem 1.5rem;
        position: relative;
        background: transparent;
        transition: color 0.3s;
    }
    
    .custom-tabs .nav-link:hover {
        color: #5e72e4;
    }
    
    .custom-tabs .nav-link.active {
        color: #5e72e4;
        background: transparent;
    }
    
    .custom-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: #5e72e4;
        border-radius: 3px 3px 0 0;
    }
    
    .badge-pill-custom {
        padding: 0.35em 0.6em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 10rem;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }

    /* Notification Items */
    .notif-list {
        background: #fff;
    }
    
    .notif-item {
        padding: 1.5rem 2.5rem;
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.2s;
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
    }
    
    .notif-item:last-child {
        border-bottom: none;
    }
    
    .notif-item:hover {
        background-color: #fbfbfb;
    }
    
    .notif-item.unread {
        background-color: #f8faff;
    }
    
    .notif-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #4361ee;
    }
    
    /* Icon Styling */
    .notif-icon-wrapper {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .icon-success { background: #e0fbf0; color: #00b894; }
    .icon-warning { background: #fff8e1; color: #ffa502; }
    .icon-danger  { background: #ffebee; color: #ff5252; }
    .icon-info    { background: #e3f2fd; color: #1e90ff; }
    .icon-default { background: #f1f2f6; color: #a4b0be; }
    
    /* Content Typography */
    .notif-content {
        flex-grow: 1;
    }
    
    .notif-text {
        color: #2d3436;
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 0.35rem;
    }
    
    .notif-item.unread .notif-text {
        font-weight: 600;
        color: #000;
    }
    
    .notif-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.8rem;
    }
    
    .notif-time {
        color: #b2bec3;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    
    .notif-type-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f1f2f6;
        color: #747d8c;
        font-weight: 600;
    }
    
    /* Individual Actions */
    .action-link {
        color: #4361ee;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.8rem;
        transition: opacity 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .action-link:hover {
        opacity: 0.8;
        text-decoration: none;
        color: #3046bc;
    }

    .btn-read-toggle {
        background: transparent;
        border: none;
        color: #b2bec3;
        padding: 4px;
        cursor: pointer;
        transition: color 0.2s;
    }

    .btn-read-toggle:hover {
        color: #4361ee;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-icon {
        font-size: 4rem;
        color: #dfe6e9;
        margin-bottom: 1rem;
    }

    @media (max-width: 576px) {
        .notif-header { flex-direction: column; align-items: flex-start; gap: 1rem; padding: 1.5rem; }
        .header-actions { width: 100%; display: flex; justify-content: space-between; }
        .notif-item { padding: 1.25rem; gap: 1rem; }
        .notif-icon-wrapper { width: 40px; height: 40px; font-size: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="notif-container">
        
        <div class="card card-modern">
            {{-- Header --}}
            <div class="notif-header">
                <div class="header-title">
                    <h4>Notifikasi</h4>
                    <div class="header-subtitle">
                        {{ $stats['unread'] > 0 ? $stats['unread'] . ' notifikasi baru menanti Anda.' : 'Anda sudah membaca semua notifikasi.' }}
                    </div>
                </div>
                <div class="header-actions">
                    @if ($stats['unread'] > 0)
                        <form action="{{ route('notifikasi.markAllRead') }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('PATCH')
                            <button class="btn-action btn-mark-read mr-2" title="Tandai semua sudah dibaca">
                                <i class="fas fa-check-double"></i> <span class="d-none d-sm-inline">Tandai Dibaca</span>
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('notifikasi.prune') }}" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus notifikasi lama (>30 hari)?');">
                        @csrf
                        <button class="btn-action btn-clear" title="Bersihkan notifikasi lama">
                            <i class="fas fa-trash-alt"></i> <span class="d-none d-sm-inline">Bersihkan</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="border-bottom px-4">
                <ul class="nav custom-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ request('filter') != 'unread' ? 'active' : '' }}" href="{{ route('notifikasi.index') }}">
                            Semua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('filter') == 'unread' ? 'active' : '' }}" href="{{ route('notifikasi.index', ['filter' => 'unread']) }}">
                            Belum Dibaca
                            @if($stats['unread'] > 0)
                                <span class="badge badge-pill badge-primary badge-pill-custom ml-1">{{ $stats['unread'] }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Notification List --}}
            <div class="notif-list">
                @forelse($notifs as $n)
                    @php
                        // Determine type for styling
                        $type = Str::lower($n->tipe);
                        $iconClass = 'icon-default';
                        $icon = 'fa-bell'; // Default FontAwesome icon
                        
                        if(Str::contains($type, ['success', 'approve', 'terbit', 'setuju'])) {
                            $iconClass = 'icon-success';
                            $icon = 'fa-check-circle';
                        } elseif(Str::contains($type, ['warning', 'pending', 'revisi'])) {
                            $iconClass = 'icon-warning';
                            $icon = 'fa-exclamation-circle';
                        } elseif(Str::contains($type, ['danger', 'reject', 'tolak', 'hapus'])) {
                            $iconClass = 'icon-danger';
                            $icon = 'fa-times-circle';
                        } elseif(Str::contains($type, ['info', 'tugas', 'baru'])) {
                            $iconClass = 'icon-info';
                            $icon = 'fa-info-circle';
                        }
                    @endphp

                    <div class="notif-item {{ !$n->dibaca ? 'unread' : '' }}">
                        {{-- Icon --}}
                        <div class="notif-icon-wrapper {{ $iconClass }}">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        
                        {{-- Content --}}
                        <div class="notif-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="notif-text">
                                    {{ $n->pesan }}
                                </div>
                                
                                {{-- Read Status / Toggle --}}
                                @if(!$n->dibaca)
                                    <form action="{{ route('notifikasi.read', $n->id) }}" method="POST" class="ml-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="noredirect" value="1">
                                        <button type="submit" class="btn btn-sm btn-light text-primary font-weight-bold shadow-sm" style="border-radius: 50px; padding: 5px 15px; font-size: 0.8rem;" title="Tandai sudah dibaca">
                                            <i class="fas fa-check mr-1"></i> Tandai Baca
                                        </button>
                                    </form>
                                @else
                                    <div class="ml-3 text-muted small" title="Sudah dibaca">
                                        <i class="fas fa-check-double text-success"></i>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="notif-meta mt-2">
                                <span class="notif-type-badge">
                                    {{ str_replace('_', ' ', $n->tipe) }}
                                </span>
                                <span class="notif-time">
                                    <i class="far fa-clock"></i> {{ $n->dibuat_pada->diffForHumans() }}
                                </span>
                                
                                @if($n->link)
                                    <span class="text-muted mx-1">&bull;</span>
                                    <a href="{{ route('notifikasi.read', $n->id) }}" class="action-link">
                                        Lihat Detail <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h5 class="text-muted font-weight-bold">Tidak ada notifikasi</h5>
                        <p class="text-muted mb-0">Hore! Anda sudah membaca semua pembaruan saat ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($notifs->hasPages())
                <div class="card-footer bg-white border-top-0 pt-3 pb-4 text-center">
                    {{ $notifs->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
        // Auto-hide alerts
        $('.alert').delay(4000).fadeOut('slow');
    });
</script>
@endpush
