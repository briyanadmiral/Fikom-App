@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
<style>
    /* Header Style from User Management Page */
    .page-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem 1.3rem 1.8rem;
        border-radius: 1.1rem;
        margin-bottom: 2.2rem;
        border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);
        width: 54px; height: 54px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px #1498ff30;
        font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold;
        color: #0056b3;
        font-size: 1.85rem;
        margin-bottom: 0.13rem;
        letter-spacing: -1px;
    }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }
    @media (max-width: 767.98px) {
        .page-header { flex-direction: column; align-items: flex-start; padding: 1.2rem 1rem; gap: .7rem; }
        .page-header-title { font-size: 1.18rem; }
        .page-header-desc { font-size: .99rem; }
    }

    /* Notification Specific Styles */
    .notif-item {
        transition: background-color 0.2s;
    }
    .notif-item:hover {
        background-color: #f8f9fa;
    }
    .notif-item.unread {
        background-color: #f0f7ff;
    }
    .notif-card {
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding-bottom: 1rem;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #007bff;
        border-bottom: 2px solid #007bff;
        background: transparent;
    }
    .icon-circle {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.1rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10"> {{-- Widened to match the look better --}}
            
            {{-- Page Header --}}
            <div class="page-header mt-2 mb-4">
                <span class="icon">
                    <i class="fas fa-bell text-white"></i>
                </span>
                <span>
                    <div class="page-header-title">Notifikasi</div>
                    <div class="page-header-desc">
                        Pantau semua aktivitas dan pembaruan penting dalam sistem.
                    </div>
                </span>
                <span class="ml-auto d-flex align-items-center gap-2">
                    @if ($stats['unread'] > 0)
                        <form action="{{ route('notifikasi.markAllRead') }}" method="POST" class="mr-2">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-primary shadow-sm font-weight-bold">
                                <i class="fas fa-check-double mr-1"></i> Tandai Dibaca
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('notifikasi.prune') }}" method="POST" onsubmit="return confirm('Hapus notifikasi lama (>30 hari)?');">
                        @csrf
                        <button class="btn btn-outline-danger shadow-sm font-weight-bold" data-toggle="tooltip" title="Bersihkan Notifikasi Lama">
                            <i class="fas fa-trash-alt mr-1"></i> Bersihkan
                        </button>
                    </form>
                </span>
            </div>

            {{-- Card List --}}
            <div class="card notif-card overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') != 'unread' ? 'active' : '' }}" href="{{ route('notifikasi.index') }}">Semua</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'unread' ? 'active' : '' }}" href="{{ route('notifikasi.index', ['filter' => 'unread']) }}">
                                Belum Dibaca <span class="badge badge-pill badge-primary ml-1">{{ $stats['unread'] }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($notifs as $n)
                        @php
                            $iconClass = 'bg-light text-secondary';
                            if(Str::contains($n->tipe, ['success', 'approve', 'terbit'])) $iconClass = 'bg-success-light text-success';
                            elseif(Str::contains($n->tipe, ['warning', 'pending'])) $iconClass = 'bg-warning-light text-warning';
                            elseif(Str::contains($n->tipe, ['danger', 'reject', 'tolak'])) $iconClass = 'bg-danger-light text-danger';
                            elseif(Str::contains($n->tipe, ['info', 'tugas'])) $iconClass = 'bg-info-light text-info';
                        @endphp

                        <div class="list-group-item notif-item p-4 {{ !$n->dibaca ? 'unread' : '' }}">
                            <div class="d-flex align-items-start">
                                <div class="mr-3">
                                    <div class="icon-circle shadow-sm" style="background-color: #fff;">
                                        <i class="bi {{ $n->getIcon() }} text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 {{ !$n->dibaca ? 'font-weight-bold text-dark' : 'text-secondary' }}">
                                            {{ $n->pesan }}
                                        </h6>
                                        <span class="small text-muted text-nowrap ml-2">
                                            {{ $n->dibuat_pada->diffForHumans() }}
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mt-2">
                                        <span class="badge badge-light border text-uppercase mr-3" style="font-size: 10px; letter-spacing: 0.5px;">
                                            {{ str_replace('_', ' ', $n->tipe) }}
                                        </span>
                                        
                                        @if($n->link)
                                            <a href="{{ url($n->link) }}" class="small font-weight-bold text-decoration-none mr-3">
                                                Lihat Detail <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        @endif

                                        @if(!$n->dibaca)
                                            <form action="{{ route('notifikasi.read', $n->id) }}" method="POST" class="ml-auto">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-muted" title="Tandai sudah dibaca">
                                                    <small>Tandai Dibaca</small>
                                                </button>
                                            </form>
                                        @else
                                            <small class="text-muted ml-auto"><i class="fas fa-check-double text-success"></i> Dibaca</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted display-4">
                                <i class="bi bi-bell-slash"></i>
                            </div>
                            <h5 class="text-muted">Tidak ada notifikasi</h5>
                            <p class="text-muted small">Anda sudah membaca semua notifikasi.</p>
                        </div>
                    @endforelse
                </div>

                @if($notifs->hasPages())
                    <div class="card-footer bg-white text-center py-3">
                        {{ $notifs->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
        $('.alert').delay(5000).fadeOut('slow');
    });
</script>
@endpush
