@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
    <style>
        body {
            background: #f7faff;
        }

        /* ===== HEADER NOTIFIKASI (mirip header SK/ST) ===== */
        .notif-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem 1.3rem 1.8rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem;
            margin-top: .5rem;
        }

        .notif-header .icon {
            background: linear-gradient(135deg, #f1c40f 0, #f39c12 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px rgba(243, 156, 18, 0.3);
            font-size: 2rem;
        }

        .notif-header-title {
            font-weight: 700;
            color: #8e44ad;
            font-size: 1.8rem;
            margin-bottom: 0.15rem;
            letter-spacing: -0.5px;
        }

        .notif-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        /* ===== STATISTIK NOTIF (grid rapi) ===== */
        .notif-stat-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.2rem;
            margin: 0 0 2rem;
            width: 100%;
            max-width: 720px;
        }

        .notif-stat-card {
            border-radius: .85rem;
            border: none;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .04);
        }

        .notif-stat-card .card-body {
            text-align: center;
            padding: 1.15rem 1rem;
        }

        .notif-stat-card .icon {
            font-size: 2.1rem;
            margin-bottom: .4rem;
        }

        .notif-stat-card .label {
            color: #6c757d;
            font-size: .83rem;
            margin-bottom: .25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .notif-stat-card .value {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
        }

        /* ===== KARTU DAFTAR NOTIFIKASI ===== */
        .notif-list-card {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .03);
            overflow: hidden;
        }

        .notif-list-card .card-header {
            background: #ffffff;
            border-bottom: 1px solid #edf1f7;
            padding: .85rem 1.25rem;
        }

        .notif-list-card .card-title {
            font-weight: 600;
            font-size: 1.05rem;
            margin: 0;
        }

        .notif-list-card .card-body {
            padding: 0;
        }

        .notif-list-card .list-group-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        /* Hover efek item */
        .notif-list-card .list-group-item:hover {
            background-color: #f8f9fa !important;
            border-left-color: #007bff;
        }

        /* Unread notification styling */
        .notif-list-card .list-group-item.unread {
            border-left-color: #ffc107;
            background-color: #fffbf0 !important;
        }

        /* Icon badge kiri (amplop) */
        .notif-icon-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .notif-icon-badge i {
            font-size: 1.2rem;
        }

        /* Button & badge smooth */
        .btn,
        .badge {
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        }

        /* Alert animasi masuk */
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

        /* Empty state icon animasi */
        .fa-bell-slash {
            animation: swing 2s ease-in-out infinite;
        }

        @keyframes swing {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(8deg);
            }

            75% {
                transform: rotate(-8deg);
            }
        }

        @media (max-width: 575.98px) {
            .notif-header {
                padding: 1.2rem 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: .7rem;
            }

            .notif-header .icon {
                width: 44px;
                height: 44px;
                font-size: 1.4rem;
            }

            .notif-header-title {
                font-size: 1.3rem;
            }

            .notif-header-desc {
                font-size: .98rem;
            }

            .notif-list-card {
                border-radius: .7rem;
            }
        }
    </style>
@endpush

@section('content_header')
    <div class="notif-header mt-2 mb-3">
        <span class="icon">
            <i class="fas fa-bell text-white"></i>
        </span>
        <div>
            <div class="notif-header-title">Notifikasi</div>
            <div class="notif-header-desc">
                Halaman ini menampilkan semua notifikasi yang Anda terima dari sistem.
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-2">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif

        @php
            $totalNotifs = $notifs->count();
            $unreadNotifs = $notifs->where('dibaca', false)->count();
            $readNotifs = $notifs->where('dibaca', true)->count();
        @endphp

        {{-- Statistik di atas --}}
        <div class="d-flex justify-content-center w-100 mb-3">
            <div class="notif-stat-wrapper py-1 mx-auto">
                <div class="notif-stat-card card shadow-sm">
                    <div class="card-body">
                        <div class="icon text-primary" data-toggle="tooltip" title="Total Notifikasi">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="label">Total</div>
                        <div class="value text-primary">{{ $totalNotifs }}</div>
                    </div>
                </div>
                <div class="notif-stat-card card shadow-sm">
                    <div class="card-body">
                        <div class="icon text-warning" data-toggle="tooltip" title="Belum Dibaca">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="label">Belum Dibaca</div>
                        <div class="value text-warning">{{ $unreadNotifs }}</div>
                    </div>
                </div>
                <div class="notif-stat-card card shadow-sm">
                    <div class="card-body">
                        <div class="icon text-success" data-toggle="tooltip" title="Sudah Dibaca">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <div class="label">Sudah Dibaca</div>
                        <div class="value text-success">{{ $readNotifs }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Notifikasi --}}
        <div class="card notif-list-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-list mr-2"></i>Daftar Notifikasi
                </h3>
                <div class="card-tools">
                    @if ($unreadNotifs > 0)
                        <form action="{{ route('notifikasi.markAllRead') }}" method="POST" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($notifs as $n)
                        <li class="list-group-item {{ !$n->dibaca ? 'unread' : '' }}">
                            <div class="d-flex align-items-start">
                                <div class="mr-3">
                                    @if (!$n->dibaca)
                                        <span class="notif-icon-badge bg-warning text-white">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    @else
                                        <span class="notif-icon-badge bg-success text-white">
                                            <i class="fas fa-envelope-open"></i>
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
                                    @if (!$n->dibaca)
                                        <form action="{{ route('notifikasi.read', $n->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                                title="Tandai Dibaca">
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
                                <p>Notifikasi baru akan muncul di sini ketika ada pembaruan.</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            // Tooltip untuk ikon statistik
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-dismiss alerts after 5 seconds
            $('.alert').delay(5000).fadeOut('slow');
        });
    </script>
@endpush
