@extends('layouts.app')

@section('title', 'Dashboard Arsip Surat')

@push('styles')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* General Dashboard Styling */
        .dashboard-container {
            padding-bottom: 3rem;
        }
        
        /* 0) Header Bar (Floating Style) */
        .dashboard-header {
            position: sticky;
            top: 1rem; /* Add some top spacing for floating effect */
            z-index: 999;
            background: #fff;
            /* backdrop-filter: blur(10px);  Removed as strict white background is safer */
            border: 1px solid #e9ecef;
            border-radius: 12px; /* Floating look */
            padding: 1rem 1.5rem;
            margin-bottom: 2rem; /* Push content down */
            /* margin: -1.5rem ... REVERTED negative margins */
            /* width: regular width */
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-title-group {
            min-width: 200px;
        }

        .header-title-group h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        .header-title-group p {
            margin: 0;
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .header-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            flex: 1;
            justify-content: flex-end;
        }
        
        /* 1) KPI Cards */
        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #f1f3f5;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            border-color: #e2e6ea;
        }
        .kpi-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .kpi-value {
            font-size: 2rem;
            font-weight: 800;
            color: #343a40;
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }
        .kpi-icon {
            opacity: 0.1;
            font-size: 3.5rem;
            position: absolute;
            right: -0.5rem;
            bottom: -0.5rem;
            transform: rotate(-15deg);
        }
        
        /* 2) Work Queue & Section Cards */
        .section-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #f1f3f5;
            margin-bottom: 1.5rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .section-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafbfc;
        }
        .section-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #495057;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 0.75rem;
            color: #007bff;
        }
        
        .work-queue-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .work-queue-tabs .nav-link:hover {
            color: #495057;
            background-color: #f8f9fa;
        }
        .work-queue-tabs .nav-link.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background: #fff;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Helpers */
        .badge-soft {
            padding: 0.4em 0.8em;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.3px;
        }
        .badge-soft-primary { background-color: rgba(0,123,255,0.1); color: #007bff; }
        .badge-soft-success { background-color: rgba(40,167,69,0.1); color: #28a745; }
        .badge-soft-warning { background-color: rgba(255,193,7,0.15); color: #856404; }
        .badge-soft-danger { background-color: rgba(220,53,69,0.1); color: #dc3545; }
        .badge-soft-info { background-color: rgba(23,162,184,0.1); color: #17a2b8; }
        .badge-soft-secondary { background-color: rgba(108,117,125,0.1); color: #6c757d; }
        
        /* 4) Monitoring Items (Fix for Overlapping) */
        .monitoring-item {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .monitoring-item:last-child { border-bottom: none; }
        
        .monitoring-content {
            display: flex;
            align-items: center;
            min-width: 0; /* Crucial for text-truncate */
            flex: 1; 
            margin-right: 1rem;
        }
        .monitoring-icon {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-right: 1rem;
        }
        .monitoring-text {
            min-width: 0; /* Crucial for text-truncate nested */
        }
        .monitoring-meta {
            flex-shrink: 0;
            text-align: right;
        }
        
    </style>
@endpush

@section('content')
<div class="dashboard-container" style="overflow-x: hidden;">

    {{-- 0) Header Bar (Sticky) --}}
    <div class="dashboard-header">
        <div class="header-title-group">
            <h1><i class="fas fa-columns mr-2 text-primary"></i>Dashboard Utama</h1>
            <p>
                <i class="far fa-clock mr-1 text-muted"></i> <span id="realtime-clock" class="font-weight-bold text-dark">--:--:--</span>
                <span class="mx-2 text-muted">|</span>
                {{ now()->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>
        
        <div class="header-actions">
             {{-- Filter Periode --}}
            <form action="{{ route('home') }}" method="GET" class="d-flex align-items-center bg-light p-1 rounded border shadow-sm">
                 <select name="period_month" class="custom-select custom-select-sm border-0 bg-transparent text-muted" style="width: auto; font-weight: 600; cursor: pointer;" onchange="this.form.submit()">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('period_month', now()->month) == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMM') }}
                        </option>
                    @endforeach
                </select>
                <div class="border-left mx-1" style="height: 20px;"></div>
                <select name="tahun" class="custom-select custom-select-sm border-0 bg-transparent text-muted" style="width: auto; font-weight: 600; cursor: pointer;" onchange="this.form.submit()">
                    @foreach(range(now()->year, now()->year-3) as $y)
                        <option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>

            {{-- Online Users Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle shadow-sm font-weight-bold text-muted border mr-2" type="button" data-toggle="dropdown">
                    <i class="fas fa-users text-success mr-1"></i>
                    <span class="d-none d-md-inline">Online</span>
                    <span class="badge badge-success ml-1">{{ $activeUsersCount }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mt-2" style="width: 320px; max-height: 400px; overflow-y: auto;">
                    <div class="dropdown-header bg-light border-bottom py-2">
                         <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark">PENGGUNA AKTIF</span>
                            <span class="badge badge-success">{{ $activeUsersCount }} Online</span>
                         </div>
                    </div>
                     @forelse($onlineUsers as $onUser)
                        <div class="dropdown-item px-3 py-2 border-bottom">
                             <div class="media align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($onUser->nama_lengkap) }}&background=random&color=fff&size=64" 
                                     class="rounded-circle shadow-sm mr-3" 
                                     style="width: 38px; height: 38px;" 
                                     alt="{{ $onUser->nama_lengkap }}">
                                <div class="media-body overflow-hidden">
                                    <h6 class="mt-0 mb-0 small font-weight-bold text-dark text-truncate">{{ Str::upper($onUser->nama_lengkap) }}</h6>
                                     <div class="d-flex align-items-center">
                                        <small class="text-muted mr-auto">{{ $onUser->peran->nama ?? 'User' }}</small>
                                        <i class="fas fa-circle text-success" style="font-size: 8px;"></i>
                                     </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-4">
                            <i class="fas fa-user-slash fa-2x mb-2 opacity-25"></i><br>
                            Tidak ada user online.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Export Buttons --}}
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle shadow-sm font-weight-bold" data-toggle="dropdown">
                    <i class="fas fa-file-excel mr-1"></i> Export
                </button>
                 <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                    <a class="dropdown-item" href="{{ route('laporan.export.excel', ['tahun' => $tahun, 'type' => 'st']) }}">
                        <i class="fas fa-file-excel text-success mr-2"></i> Surat Tugas
                    </a>
                    <a class="dropdown-item" href="{{ route('laporan.export.excel', ['tahun' => $tahun, 'type' => 'sk']) }}">
                        <i class="fas fa-file-excel text-success mr-2"></i> Surat Keputusan
                    </a>
                    <div class="dropdown-divider"></div>
                     <a class="dropdown-item" href="#exportAllFormats" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="exportAllFormats">
                        <i class="fas fa-file-archive text-primary mr-2"></i> Semua Data
                    </a>
                    <div class="collapse mt-1" id="exportAllFormats">
                        <a class="dropdown-item pl-4" href="{{ route('laporan.export.excel', ['tahun' => $tahun, 'type' => 'all']) }}">
                            <i class="fas fa-file-excel text-success mr-2"></i> Excel
                        </a>
                        <a class="dropdown-item pl-4" href="{{ route('laporan.export.excel', ['tahun' => $tahun, 'type' => 'all']) }}">
                            <i class="fas fa-file-csv text-success mr-2"></i> CSV
                        </a>
                        <a class="dropdown-item pl-4" href="{{ route('laporan.export.pdf', ['tahun' => $tahun]) }}">
                            <i class="fas fa-file-pdf text-danger mr-2"></i> PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- Create Button --}}
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle shadow-sm font-weight-bold px-3" type="button" data-toggle="dropdown">
                    <i class="fas fa-plus mr-1"></i> Buat Surat
                </button>
                <div class="dropdown-menu dropdown-menu-right shadow-lg border-0" style="min-width: 250px;">
                    <a class="dropdown-item py-3" href="{{ route('surat_tugas.create') }}">
                        <div class="media align-items-center">
                            <span class="icon-circle bg-light-primary text-primary mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-envelope-open-text"></i></span>
                            <div class="media-body">
                                <h6 class="mt-0 mb-0 font-weight-bold">Surat Tugas</h6>
                                <small class="text-muted">Untuk perjalanan dinas/tugas.</small>
                            </div>
                        </div>
                    </a>
                     <div class="dropdown-divider my-0"></div>
                    <a class="dropdown-item py-3" href="{{ route('surat_keputusan.create') }}">
                        <div class="media align-items-center">
                            <span class="icon-circle bg-light-success text-success mr-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-gavel"></i></span>
                            <div class="media-body">
                                <h6 class="mt-0 mb-0 font-weight-bold">Surat Keputusan</h6>
                                <small class="text-muted"> Penetapan, Pengangkatan, dll.</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
             @if (session('entered_from_dashboard'))
                <form action="{{ route('external.exit') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm ml-1" title="Keluar">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- 1) KPI Cards (Consolidated Stats) --}}
    <div class="row mb-4">
        {{-- Total Surat --}}
        <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
            <div class="kpi-card border-left-primary">
                <div>
                     <div class="kpi-title text-primary">Total Dokumen</div>
                     <div class="kpi-value text-primary">{{ number_format($kpi['total_surat']) }}</div>
                     <small class="text-muted font-weight-bold">Tahun {{ $tahun }}</small>
                </div>
                <i class="fas fa-layer-group kpi-icon text-primary"></i>
            </div>
        </div>
        {{-- Total ST --}}
        <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
             <div class="kpi-card border-left-info">
                <div>
                    <div class="kpi-title text-info">Surat Tugas</div>
                    <div class="kpi-value text-info">{{ number_format($kpi['total_st']) }}</div>
                    <small class="text-muted font-weight-bold">{{ $stats['st_bulan_ini'] ?? 0 }} bulan ini</small>
                </div>
                <i class="fas fa-envelope-open-text kpi-icon text-info"></i>
            </div>
        </div>
        {{-- Total SK --}}
         <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
             <div class="kpi-card border-left-success">
                 <div>
                    <div class="kpi-title text-success">Surat Keputusan</div>
                    <div class="kpi-value text-success">{{ number_format($kpi['total_sk']) }}</div>
                    <small class="text-muted font-weight-bold">{{ $stats['sk_bulan_ini'] ?? 0 }} bulan ini</small>
                 </div>
                <i class="fas fa-gavel kpi-icon text-success"></i>
            </div>
        </div>
        {{-- Waiting Review --}}
        <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
             <div class="kpi-card border-left-warning">
                 <div>
                    <div class="kpi-title text-warning">Perlu Review</div>
                    <div class="kpi-value text-warning">{{ $kpi['waiting_review'] }}</div>
                    <small class="text-muted font-weight-bold">Menunggu persetujuan</small>
                 </div>
                <i class="fas fa-hourglass-half kpi-icon text-warning"></i>
            </div>
        </div>
        {{-- Waiting Sign --}}
        <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
            <div class="kpi-card border-left-danger">
                 <div>
                    <div class="kpi-title text-danger">Menunggu TTD</div>
                    <div class="kpi-value text-danger">{{ $kpi['waiting_sign'] }}</div>
                     <small class="text-muted font-weight-bold">Draft disetujui</small>
                 </div>
                <i class="fas fa-file-signature kpi-icon text-danger"></i>
            </div>
        </div>
        {{-- Archived --}}
        <div class="col-6 col-md-4 col-lg-2 mb-3 mb-lg-0">
            <div class="kpi-card bg-light border-0">
                 <div>
                    <div class="kpi-title text-secondary">Arsip Final</div>
                    <div class="kpi-value text-secondary">{{ $kpi['final'] }}</div>
                    <small class="text-muted font-weight-bold">Terbit & Arsip</small>
                 </div>
                <i class="fas fa-check-circle kpi-icon text-secondary"></i>
            </div>
        </div>
    </div>

    {{-- 2) Operational Area: Work Queue & Notifications --}}
    <div class="row">
        {{-- Left: Antrian Pekerjaan / Work Queue --}}
        <div class="col-lg-8 mb-4">
            <div class="section-card h-100">
                <div class="card-header bg-white p-0 border-bottom-0">
                    <ul class="nav nav-tabs work-queue-tabs" id="myTab" role="tablist">
                        {{-- Tab 1: Action (Only for Approvers) --}}
                        @if($user->canApproveSurat())
                         <li class="nav-item">
                            <a class="nav-link active" id="action-tab" data-toggle="tab" href="#action" role="tab">
                                <i class="fas fa-exclamation-circle mr-1 text-danger"></i> Perlu Action
                                @if($perluAction->count()) <span class="badge badge-danger ml-1 shadow-sm">{{ $perluAction->count() }}</span> @endif
                            </a>
                        </li>
                        @endif

                        {{-- Tab 2: Draft Saya (Active if Action tab hidden) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ !$user->canApproveSurat() ? 'active' : '' }}" id="draft-tab" data-toggle="tab" href="#draft" role="tab">
                                <i class="fas fa-pencil-alt mr-1 text-secondary"></i> Draft Saya
                                @if($myDrafts->count()) <span class="badge badge-secondary ml-1">{{ $myDrafts->count() }}</span> @endif
                            </a>
                        </li>
                        
                        {{-- Tab 3: Dikembalikan --}}
                        <li class="nav-item">
                            <a class="nav-link" id="returned-tab" data-toggle="tab" href="#returned" role="tab">
                                <i class="fas fa-undo mr-1 text-warning"></i> Dikembalikan
                                @if($myRevisions->count()) <span class="badge badge-warning ml-1">{{ $myRevisions->count() }}</span> @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="myTabContent">
                        {{-- Tab 1: Perlu Action --}}
                        @if($user->canApproveSurat())
                        <div class="tab-pane fade show active" id="action" role="tabpanel">
                            @if($perluAction->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                            <tr>
                                                <th class="pl-4 border-top-0" width="10%">Jenis</th>
                                                <th class="border-top-0">Perihal</th>
                                                <th class="border-top-0" width="20%">Pemohon</th>
                                                <th class="border-top-0 text-right pr-4" width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($perluAction as $item)
                                            <tr class="clickable-row" data-href="{{ $item->jenis == 'ST' ? route('surat_tugas.show', $item->id) : route('surat_keputusan.show', $item->id) }}" style="cursor: pointer;">
                                                <td class="pl-4"><span class="badge badge-soft-{{ $item->jenis == 'ST' ? 'info' : 'success' }}">{{ $item->jenis }}</span></td>
                                                <td>
                                                    <div class="font-weight-bold text-dark text-truncate" style="max-width: 300px;">{{ $item->display_title }}</div>
                                                    <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                                                </td>
                                                <td class="small">{{ optional($item->pembuat)->nama_lengkap }}</td>
                                                <td class="text-right pr-4">
                                                    <a href="{{ $item->jenis == 'ST' ? route('surat_tugas.approve.form', $item->id) : route('surat_keputusan.approveForm', $item->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">Review</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-check-double fa-3x mb-3 text-light-gray" style="opacity: 0.2;"></i>
                                    <h5>Semua Beres!</h5>
                                    <p class="mb-0 text-muted small">Tidak ada surat yang menunggu persetujuan Anda.</p>
                                </div>
                            @endif
                        </div>
                        @endif

                        {{-- Tab 2: Draft Saya --}}
                        <div class="tab-pane fade {{ !$user->canApproveSurat() ? 'show active' : '' }}" id="draft" role="tabpanel">
                             @if($myDrafts->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                         <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                            <tr>
                                                <th class="pl-4 border-top-0">Jenis</th>
                                                <th class="border-top-0">Perihal</th>
                                                <th class="border-top-0">Last Update</th>
                                                <th class="text-right pr-4 border-top-0">Edit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myDrafts as $item)
                                            <tr class="clickable-row" data-href="{{ $item->jenis == 'ST' ? route('surat_tugas.edit', $item->id) : route('surat_keputusan.edit', $item->id) }}" style="cursor: pointer;">
                                                <td class="pl-4"><span class="badge badge-soft-secondary">{{ $item->jenis }}</span></td>
                                                <td>
                                                    <div class="text-truncate text-dark font-weight-bold" style="max-width: 350px;">{{ $item->display_title }}</div>
                                                </td>
                                                <td class="small text-muted">{{ $item->updated_at->diffForHumans() }}</td>
                                                <td class="text-right pr-4">
                                                     <a href="{{ $item->jenis == 'ST' ? route('surat_tugas.edit', $item->id) : route('surat_keputusan.edit', $item->id) }}" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="fas fa-pen"></i></a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                             @else
                                <div class="p-5 text-center text-muted small">
                                    <div class="mb-2"><i class="far fa-file-alt fa-2x opacity-50"></i></div>
                                    Belum ada draft tersimpan.
                                </div>
                             @endif
                        </div>

                        {{-- Tab 3: Dikembalikan --}}
                        <div class="tab-pane fade" id="returned" role="tabpanel">
                             @if($myRevisions->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                         <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                            <tr>
                                                <th class="pl-4 border-top-0">Jenis</th>
                                                <th class="border-top-0">Perihal</th>
                                                <th class="border-top-0">Status</th>
                                                <th class="text-right pr-4 border-top-0">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myRevisions as $item)
                                            <tr>
                                                <td class="pl-4"><span class="badge badge-soft-warning">{{ $item->jenis }}</span></td>
                                                <td>
                                                    <div class="text-truncate text-dark font-weight-bold" style="max-width: 300px;">{{ $item->display_title }}</div>
                                                </td>
                                                <td><span class="badge badge-soft-danger">{{ ucfirst($item->status_surat) }}</span></td>
                                                <td class="text-right pr-4">
                                                     <a href="{{ $item->jenis == 'ST' ? route('surat_tugas.show', $item->id) : route('surat_keputusan.show', $item->id) }}" class="btn btn-warning btn-sm text-white shadow-sm font-weight-bold small">Perbaiki</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                             @else
                                <div class="p-5 text-center text-muted small">
                                     <div class="mb-2"><i class="fas fa-check-circle fa-2x opacity-50 text-success"></i></div>
                                    Tidak ada surat revisi.
                                </div>
                             @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Notifikasi & Who's Online --}}
        <div class="col-lg-4 mb-4">
            {{-- 1. Notifikasi --}}
             <div class="section-card mb-4" style="max-height: 400px; display: flex; flex-direction: column;">
                <div class="section-header">
                     <h5 class="section-title"><i class="fas fa-bell text-warning"></i> Notifikasi</h5>
                     <a href="{{ route('notifikasi.index') }}" class="small font-weight-bold">Lihat Semua</a>
                </div>
                <div class="card-body p-0 overflow-auto">
                    <ul class="list-group list-group-flush">
                        @forelse($notifications as $notif)
                            <li class="list-group-item list-group-item-action d-flex align-items-start py-3 {{ !$notif->dibaca ? 'bg-light' : '' }}" onclick="window.location='{{ route('notifikasi.index') }}'" style="cursor: pointer;">
                                <div class="mr-3 pt-1">
                                    @php $icon = method_exists($notif, 'getIcon') ? $notif->getIcon() : 'bi-bell'; @endphp
                                    <div class="icon-circle {{ !$notif->dibaca ? 'bg-warning text-white' : 'bg-light text-muted' }}" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                        <i class="{{ str_contains($icon, 'bi-') ? 'bi '.$icon : 'fas fa-info' }}"></i>
                                    </div>
                                </div>
                                <div class="w-100 overflow-hidden">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-dark font-weight-bold text-truncate" style="font-size: 0.85rem;">
                                            {{ str_replace('_', ' ', ucfirst($notif->tipe)) }}
                                        </span>
                                        <small class="text-muted text-nowrap ml-2" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans(null, true) }}</small>
                                    </div>
                                    <p class="mb-0 text-muted small text-truncate" style="line-height: 1.3;">
                                        {{ $notif->pesan }}
                                    </p>
                                </div>
                            </li>
                        @empty
                            <div class="text-center p-4 text-muted small">
                                <i class="far fa-bell-slash fa-2x mb-2 opacity-25"></i><br>
                                Belum ada notifikasi.
                            </div>
                        @endforelse
                    </ul>
                </div>
            </div>


        </div>
    </div>

    {{-- 3) Analytic Area: Charts --}}
    <div class="row">
        <div class="col-lg-8 mb-4">
             <div class="section-card shadow-sm h-100 border-0">
                <div class="section-header border-0 pb-0">
                    <h5 class="section-title"><i class="fas fa-chart-area text-info"></i> Tren Surat ({{ $tahun }})</h5>
                    <div class="card-tools">
                        <span class="badge badge-light border mr-1"><i class="fas fa-circle text-info" style="font-size: 0.6rem;"></i> SK</span>
                        <span class="badge badge-light border"><i class="fas fa-circle text-primary" style="font-size: 0.6rem;"></i> ST</span>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
             <div class="section-card shadow-sm h-100 border-0">
                <div class="section-header border-0 pb-0">
                     <h5 class="section-title"><i class="fas fa-chart-pie text-secondary"></i> Distribusi Status</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                     <div style="height: 250px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                     </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4) Archive Monitoring (Row 4) --}}
    <div class="row">
         {{-- Monitoring Nomor Terakhir --}}
        <div class="col-md-6 mb-4">
             <div class="section-card h-100 border-left-primary">
                <div class="section-header">
                     <h5 class="section-title"><i class="fas fa-sort-numeric-down text-dark"></i> Nomor Surat Terakhir</h5>
                </div>
                <div class="card-body p-0">
                    {{-- Last ST --}}
                    <div class="monitoring-item">
                        <div class="monitoring-content">
                            <div class="monitoring-icon bg-light-primary text-primary">
                                <i class="fas fa-envelope-open-text fa-lg"></i>
                            </div>
                            <div class="monitoring-text">
                                <div class="small font-weight-bold text-uppercase text-muted mb-0">Surat Tugas (ST)</div>
                                <div class="h5 mb-0 text-dark font-weight-bolder text-truncate" title="{{ $lastST->nomor ?? '-' }}">
                                    {{ $lastST->nomor ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="monitoring-meta">
                            <small class="text-muted d-block text-right mb-1">
                                {{ optional($lastST)->created_at ? $lastST->created_at->format('d M Y') : '-' }}
                            </small>
                            <span class="badge badge-soft-primary float-right">Terbit</span>
                        </div>
                    </div>

                    {{-- Last SK --}}
                    <div class="monitoring-item">
                        <div class="monitoring-content">
                            <div class="monitoring-icon bg-light-success text-success">
                                <i class="fas fa-gavel fa-lg"></i>
                            </div>
                            <div class="monitoring-text">
                                <div class="small font-weight-bold text-uppercase text-muted mb-0">Surat Keputusan (SK)</div>
                                <div class="h5 mb-0 text-dark font-weight-bolder text-truncate" title="{{ $lastSK->nomor ?? '-' }}">
                                    {{ $lastSK->nomor ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="monitoring-meta">
                            <small class="text-muted d-block text-right mb-1">
                                {{ optional($lastSK)->created_at ? $lastSK->created_at->format('d M Y') : '-' }}
                            </small>
                            <span class="badge badge-soft-success float-right">Terbit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Arsip Terakhir --}}
        <div class="col-md-6 mb-4">
            <div class="section-card h-100">
                <div class="section-header">
                     <h5 class="section-title"><i class="fas fa-history text-secondary"></i> Arsip Final Terbaru</h5>
                     <a href="{{ route('surat_keputusan.arsipList') }}" class="btn btn-xs btn-light shadow-sm font-weight-bold">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless table-striped mb-0">
                        <tbody>
                            @forelse($recentFinal as $arch)
                            <tr>
                                <td width="10%" class="pl-3 align-middle"><span class="badge badge-light border font-weight-normal">{{ $arch->jenis }}</span></td>
                                <td class="align-middle">
                                    <div class="text-truncate font-weight-bold text-dark" style="max-width: 250px; font-size: 0.9rem;" title="{{ $arch->display_title }}">
                                        {{ $arch->display_title }}
                                    </div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 250px;"><i class="fas fa-tag mr-1 text-xs"></i> {{ $arch->nomor }}</small>
                                </td>
                                <td class="text-right pr-3 align-middle">
                                    <small class="d-block text-muted">{{ $arch->created_at->format('d M') }}</small>
                                </td>
                            </tr>
                            @empty
                                <tr><td colspan="3" class="text-center small text-muted py-3">Belum ada arsip.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Realtime Clock ---
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            const clockEl = document.getElementById('realtime-clock');
            if(clockEl) clockEl.innerText = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock(); // initial call

        // --- Clickable Rows ---
        const rows = document.querySelectorAll('.clickable-row');
        rows.forEach(row => {
            row.addEventListener('click', function() {
                window.location = this.dataset.href;
            });
        });

        // --- Trend Chart ---
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [{
                    label: 'Surat Tugas',
                    data: @json($trendST),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    pointBackgroundColor: '#4e73df',
                    tension: 0.3,
                    fill: true
                }, {
                    label: 'Surat Keputusan',
                    data: @json($trendSK),
                    borderColor: '#36b9cc',
                    backgroundColor: 'rgba(54, 185, 204, 0.05)',
                    pointBackgroundColor: '#36b9cc',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false } // Custom legend used
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // --- Status Chart (Donut) ---
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: @json(array_map('ucfirst', $statuses)),
                datasets: [{
                    data: @json($statusBreakdown),
                    backgroundColor: ['#858796', '#f6c23e', '#1cc88a', '#e74a3b'],
                    hoverOffset: 4,
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 6 } }
                }
            }
        });
    });
</script>
@endpush
