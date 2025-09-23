@extends('layouts.app')

@section('title', 'Dashboard')

@php
    // --- SEMUA LOGIKA DIAMBIL DI SINI UNTUK KEBERSIHAN VIEW ---
    $user = auth()->user();
    $peranId = $user->peran_id;

    // --- 1. MEMBUAT QUERY DASAR YANG DISESUAIKAN PERAN ---
    // Logika ini mengadaptasi dari TugasController@mine, @all, dan @approveList
    $baseQuery = \App\Models\TugasHeader::query();

    if ($peranId === 1) {
        // Admin TU: bisa melihat semua surat yang ada di sistem.
    } elseif ($user->canApproveSurat()) {
        // Dekan & Wakil Dekan: melihat surat yang dibuatnya, menunggu persetujuannya, atau ditujukan padanya.
        $baseQuery->where(function ($query) use ($user) {
            $query->where('dibuat_oleh', $user->id)
                  ->orWhere('next_approver', $user->id)
                  ->orWhereHas('penerima', fn($q) => $q->where('pengguna_id', $user->id));
        });
    } else {
        // Peran lain (Dosen, Tendik, dll): hanya melihat surat yang ditujukan padanya.
        $baseQuery->where('status_surat', 'disetujui') // Hanya lihat yang sudah final
                  ->whereHas('penerima', fn($q) => $q->where('pengguna_id', $user->id));
    }

    // --- 2. MENGAMBIL DATA BERDASARKAN QUERY YANG SUDAH DIFILTER ---
    $suratCollection = $baseQuery->with('pembuat')->latest('updated_at')->get();

    // --- 3. MENGHITUNG STATISTIK DARI DATA YANG SUDAH DIFILTER ---
    $suratStats = [
        'draft'     => $suratCollection->where('status_surat', 'draft')->count(),
        'pending'   => $suratCollection->where('status_surat', 'pending')->count(),
        'disetujui' => $suratCollection->where('status_surat', 'disetujui')->count(),
    ];
    $suratStats['total'] = $suratCollection->count();

    // --- 4. MENGAMBIL 5 SURAT TERBARU DARI DATA YANG SUDAH DIFILTER ---
    $recentSurat = $suratCollection->take(5);

    // --- 5. MENGAMBIL DATA PENGGUNA ONLINE ---
    $onlineUsers = DB::table('pengguna as p')
        ->leftJoin('peran as pr', 'p.peran_id', '=', 'pr.id')
        ->whereNotNull('p.last_activity')
        ->where('p.last_activity', '>=', now()->subMinutes(5))
        ->select('p.nama_lengkap', 'pr.nama as nama_peran')
        ->orderBy('p.nama_lengkap')
        ->get();
    $jumlahOnline = $onlineUsers->count();
@endphp

@push('styles')
<style>
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#6610f2 0,#8540f5 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #6610f24d; font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold; color: #3c0991; font-size: 1.85rem;
        margin-bottom: 0.13rem; letter-spacing: -1px;
    }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }

    /* [DISEMPURNAKAN] Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #6f42c1 0%, #007bff 100%);
        border-radius: .8rem; color: white; padding: 2rem;
        display: flex; justify-content: space-between; align-items: center;
    }
    .welcome-banner h4 { font-weight: 300; }
    .welcome-banner img { max-width: 150px; opacity: 0.8; }

    /* [DISEMPURNAKAN] Kartu Statistik dengan warna dan ikon lebih jelas */
    .stat-card {
        border: none; border-radius: .8rem; box-shadow: 0 4px 25px rgba(0,0,0,.07);
        position: relative; overflow: hidden;
    }
    .stat-card .card-body { padding: 1.25rem; position: relative; z-index: 2; }
    .stat-card .stat-value { font-size: 2rem; font-weight: 700; color: #fff; }
    .stat-card .stat-label { font-size: .9rem; color: #fff; font-weight: 500; opacity: 0.8; }
    .stat-card .stat-icon {
        position: absolute; right: 15px; bottom: 10px;
        font-size: 4rem; color: #fff; opacity: 0.2; transform: rotate(-15deg); z-index: 1;
    }

    .dashboard-card {
        border: none; border-radius: .8rem;
        box-shadow: 0 4px 25px rgba(0,0,0, .07); height: 100%;
    }
    .dashboard-card .card-header {
        background-color: #fff; border-bottom: 1px solid #f0f0f0;
        font-weight: 600; padding: 1rem 1.25rem;
    }

    /* [DISEMPURNAKAN] Tombol Aksi Cepat dengan ikon berwarna */
    .quick-action-item a {
        display: flex; align-items: center; padding: .75rem 1rem; border-radius: .5rem;
        font-weight: 500; color: #343a40; transition: all .2s ease;
    }
    .quick-action-item a:hover { background-color: #e9ecef; transform: scale(1.02); }
    .quick-action-icon {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        margin-right: .75rem; color: #fff;
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-tachometer-alt text-white"></i></span>
    <span>
        <div class="page-header-title">Dashboard</div>
        <div class="page-header-desc">Ringkasan aktivitas dan statistik untuk Anda.</div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Welcome Banner --}}
    <div class="welcome-banner mb-4 shadow-sm">
        <div>
            <h4>Selamat Datang Kembali, <strong>{{ $user->nama_lengkap }}!</strong></h4>
            <p class="mb-0">Ringkasan aktivitas Anda per {{ now()->isoFormat('D MMMM YYYY') }}.</p>
        </div>
        <img src="{{ asset('assets/img/Siega.png') }}"
     alt="Illustration"
     class="d-none d-sm-block">
    </div>

    {{-- Kartu Statistik Surat (data sesuai peran) --}}
    <div class="row">
        @foreach([
            'total'     => ['color' => 'primary', 'icon' => 'fa-folder-open'],
            'pending'   => ['color' => 'warning', 'icon' => 'fa-hourglass-half'],
            'disetujui' => ['color' => 'success', 'icon' => 'fa-check-circle'],
            'draft'     => ['color' => 'secondary', 'icon' => 'fa-file-alt']
        ] as $status => $info)
        <div class="col-lg-3 col-6">
            <div class="stat-card bg-{{ $info['color'] }} mb-4">
                <div class="card-body">
                    <div class="stat-value">{{ $suratStats[$status] }}</div>
                    <div class="stat-label text-uppercase">{{ str_replace('_', ' ', $status) }}</div>
                    <div class="stat-icon"><i class="fas {{ $info['icon'] }}"></i></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        {{-- KOLOM KIRI --}}
        <div class="col-lg-8">
            {{-- Surat Terbaru (data sesuai peran) --}}
            <div class="card dashboard-card mb-4">
                <div class="card-header"><i class="fas fa-history mr-2"></i>Aktivitas Surat Terbaru Anda</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentSurat as $surat)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('surat_tugas.show', $surat->id) }}" class="font-weight-bold text-dark">{{ $surat->nomor }}</a>
                                <small class="d-block text-muted">{{ Str::limit($surat->nama_umum, 60) }} oleh {{ optional($surat->pembuat)->nama_lengkap ?? 'N/A' }}</small>
                            </div>
                            <span class="badge badge-pill badge-{{ $surat->status_surat == 'disetujui' ? 'success' : ($surat->status_surat == 'pending' ? 'warning' : 'secondary') }}">
                                {{ $surat->status_surat }}
                            </span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-4">Tidak ada aktivitas surat yang relevan untuk Anda.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="col-lg-4">
            {{-- [DIPERBAIKI] Akses Cepat Sesuai Peran --}}
            <div class="card dashboard-card mb-4">
                <div class="card-header"><i class="fas fa-bolt mr-2"></i>Akses Cepat</div>
                <div class="card-body">
                    @if($peranId == 1)
                    <div class="quick-action-item"><a href="{{ route('surat_tugas.create') }}"><span class="quick-action-icon bg-primary"><i class="fas fa-plus"></i></span>Buat Surat Tugas</a></div>
                    <div class="quick-action-item"><a href="{{ route('surat_keputusan.create') }}"><span class="quick-action-icon" style="background-color: #3498db;"><i class="fas fa-plus"></i></span>Buat Surat Keputusan</a></div>
                    @endif
                    
                    @if($user->canApproveSurat())
                    <div class="quick-action-item"><a href="{{ route('surat_tugas.approveList') }}"><span class="quick-action-icon bg-warning text-dark"><i class="fas fa-check-double"></i></span>Persetujuan Surat Tugas</a></div>
                    <div class="quick-action-item"><a href="{{ route('surat_keputusan.approveList') }}"><span class="quick-action-icon bg-warning text-dark"><i class="fas fa-check-double"></i></span>Persetujuan SK</a></div>
                    @endif

                    <div class="quick-action-item"><a href="{{ route('surat_tugas.mine') }}"><span class="quick-action-icon bg-info"><i class="fas fa-envelope"></i></span>Surat Tugas Saya</a></div>
                    <div class="quick-action-item"><a href="{{ route('surat_keputusan.mine') }}"><span class="quick-action-icon bg-info"><i class="fas fa-gavel"></i></span>Surat Keputusan Saya</a></div>

                    @can('viewAny', App\Models\User::class)
                    <div class="quick-action-item"><a href="{{ route('users.index') }}"><span class="quick-action-icon bg-success"><i class="fas fa-users-cog"></i></span>Manajemen Pengguna</a></div>
                    @endcan
                </div>
            </div>

            {{-- Pengguna Online (tidak berubah) --}}
            <div class="card dashboard-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users mr-2"></i>Pengguna Online</span>
                    <span class="badge badge-success">{{ $jumlahOnline }}</span>
                </div>
                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                    <ul class="list-unstyled mb-0">
                        @forelse ($onlineUsers as $onlineUser)
                        <li class="d-flex align-items-center mb-3">
                            <div class="online-user-avatar mr-3" style="background-color: {{ generate_color_from_string($onlineUser->nama_lengkap) }};">
                                {{ get_initials($onlineUser->nama_lengkap) }}
                            </div>
                            <div>
                                <div class="online-user-name">{{ $onlineUser->nama_lengkap }}</div>
                                <div class="online-user-role text-muted">{{ $onlineUser->nama_peran ?? 'N/A' }}</div>
                            </div>
                        </li>
                        @empty
                        <li class="text-center text-muted small py-3">Tidak ada pengguna yang online saat ini.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

