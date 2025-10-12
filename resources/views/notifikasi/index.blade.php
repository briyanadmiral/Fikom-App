@extends('layouts.app')

@section('title', 'Notifikasi')


@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-bell fa-lg"></i>
            </div>
            <div>
                <div class="header-title ">Notifikasi</div>
                <div class="header-desc mt-2">
                    Halaman ini menampilkan semua notifikasi yang diterima oleh pengguna.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .custom-header-box {
            background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
            color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(44, 62, 80, .13);
            padding: 1.5rem 2rem 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            border-left: 6px solid #3498db;
            margin-top: .5rem;
        }

        .header-icon {
            width: 54px;
            height: 54px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            font-size: 2rem;
            box-shadow: 0 2px 12px 0 rgba(52, 152, 219, .13);
        }

        .header-title {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .header-desc {
            font-size: 1.07rem;
            color: #e9f3fa;
            font-weight: 400;
            margin-left: .1rem;
        }

        @media (max-width: 575.98px) {
            .custom-header-box {
                padding: 1.1rem;
            }

            .header-icon {
                width: 44px;
                height: 44px;
                font-size: 1.2rem;
            }

            .header-title {
                font-size: 1.2rem;
            }

            .header-desc {
                margin-left: 0;
                font-size: .98rem;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    @php
        $totalNotifs = $notifs->count();
        $unreadNotifs = $notifs->where('dibaca', false)->count();
        $readNotifs = $notifs->where('dibaca', true)->count();
    @endphp

    <div class="row">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalNotifs }}</h3>
                    <p>Total Notifikasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-inbox"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $unreadNotifs }}</h3>
                    <p>Belum Dibaca</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $readNotifs }}</h3>
                    <p>Sudah Dibaca</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope-open"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Notifikasi</h3>
            <div class="card-tools">
                @if($unreadNotifs > 0)
                    <form action="{{ route('notifikasi.markAllRead') }}" method="POST" style="display:inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse($notifs as $n)
                    <li class="list-group-item {{ !$n->dibaca ? 'bg-light' : '' }}">
                        <div class="d-flex align-items-start">
                            <div class="mr-3">
                                @if(!$n->dibaca)
                                    <span class="badge badge-warning badge-pill p-2">
                                        <i class="fas fa-envelope fa-lg"></i>
                                    </span>
                                @else
                                    <span class="badge badge-success badge-pill p-2">
                                        <i class="fas fa-envelope-open fa-lg"></i>
                                    </span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1 {{ !$n->dibaca ? 'font-weight-bold' : '' }}">
                                    {{ $n->pesan }}
                                </p>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> {{ $n->dibuat_pada->diffForHumans() }}
                                    <span class="mx-2">•</span>
                                    {{ $n->dibuat_pada->format('d M Y, H:i') }}
                                </small>
                            </div>
                            <div class="ml-3">
                                @if(!$n->dibaca)
                                    <form action="{{ route('notifikasi.read', $n->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary" title="Tandai Dibaca">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Dibaca
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center py-5">
                        <div class="text-muted">
                            <i class="far fa-bell-slash fa-3x mb-3 d-block"></i>
                            <h5>Belum Ada Notifikasi</h5>
                            <p>Notifikasi akan muncul di sini ketika ada pembaruan</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Small box hover effect */
    .small-box {
        transition: transform 0.3s ease;
    }
    
    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* List item hover */
    .list-group-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .list-group-item:hover {
        background-color: #f8f9fa !important;
        border-left-color: #007bff;
    }

    /* Unread notification styling */
    .list-group-item.bg-light {
        border-left-color: #ffc107;
        background-color: #fffbf0 !important;
    }

    /* Badge styling */
    .badge-pill {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Smooth transitions */
    .btn, .badge {
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: scale(1.05);
    }

    /* Alert auto-hide */
    .alert {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Empty state icon animation */
    .fa-bell-slash {
        animation: swing 2s ease-in-out infinite;
    }

    @keyframes swing {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(10deg); }
        75% { transform: rotate(-10deg); }
    }
</style>
@endpush

@push('scripts')
<script>
$(function() {
    // Auto-dismiss alerts after 5 seconds
    $('.alert').delay(5000).fadeOut('slow');
});
</script>
@endpush
