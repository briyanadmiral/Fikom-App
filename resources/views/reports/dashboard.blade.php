@extends('layouts.app')

@section('title', 'Laporan & Analitik')

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
        background: linear-gradient(135deg, #28a745 0, #20c997 100%);
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px rgba(40,167,69,0.3);
        font-size: 1.8rem
    }
    .surat-header-title {
        font-weight: bold;
        color: #155724;
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
            <i class="fas fa-chart-bar text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">Laporan & Analitik</div>
            <div class="surat-header-desc">Statistik dan analisis <b>Surat Tugas</b> dan <b>Surat Keputusan</b> per periode.</div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Filter Year --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="{{ route('laporan.dashboard') }}" class="form-inline">
                <label class="mr-2">Tahun:</label>
                <select name="tahun" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="col-md-8 text-right">
            <a href="{{ route('laporan.export.excel') }}?tahun={{ $tahun }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            <a href="{{ route('laporan.export.pdf') }}?tahun={{ $tahun }}" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_st']) }}</h3>
                    <p>Surat Tugas ({{ $tahun }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_sk']) }}</h3>
                    <p>Surat Keputusan ({{ $tahun }})</p>
                </div>
                <div class="icon">
                    <i class="fas fa-gavel"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['pending_review']) }}</h3>
                    <p>Menunggu Persetujuan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['st_bulan_ini'] + $stats['sk_bulan_ini']) }}</h3>
                    <p>Dokumen Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Monthly Trend Chart --}}
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Tren Bulanan {{ $tahun }}
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Status Distribution Pie Charts --}}
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Distribusi Status ST
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="stStatusChart" style="max-height: 180px;"></canvas>
                </div>
            </div>
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Distribusi Status SK
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="skStatusChart" style="max-height: 180px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1"></i>
                        Ringkasan per Status
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-center">Surat Tugas</th>
                                <th class="text-center">Surat Keputusan</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $allStatuses = ['draft', 'pending', 'disetujui', 'ditolak', 'terbit', 'arsip'];
                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'pending' => 'Menunggu',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    'terbit' => 'Terbit',
                                    'arsip' => 'Arsip'
                                ];
                            @endphp
                            @foreach($allStatuses as $status)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $status === 'draft' ? 'secondary' : ($status === 'pending' ? 'warning' : ($status === 'disetujui' ? 'success' : ($status === 'ditolak' ? 'danger' : ($status === 'terbit' ? 'info' : 'dark')))) }}">
                                            {{ $statusLabels[$status] }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $stStatusDist[$status] ?? 0 }}</td>
                                    <td class="text-center">{{ $skStatusDist[$status] ?? 0 }}</td>
                                    <td class="text-center"><strong>{{ ($stStatusDist[$status] ?? 0) + ($skStatusDist[$status] ?? 0) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th>Total</th>
                                <th class="text-center">{{ $stats['total_st'] }}</th>
                                <th class="text-center">{{ $stats['total_sk'] }}</th>
                                <th class="text-center"><strong>{{ $stats['total_st'] + $stats['total_sk'] }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    
    // Monthly Trend Chart
    const stTrend = @json(array_values($stTrend));
    const skTrend = @json(array_values($skTrend));
    
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Surat Tugas',
                    data: stTrend,
                    borderColor: 'rgb(23, 162, 184)',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Surat Keputusan',
                    data: skTrend,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // ST Status Pie Chart
    const stStatusData = @json($stStatusDist);
    new Chart(document.getElementById('stStatusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(stStatusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                data: Object.values(stStatusData),
                backgroundColor: ['#6c757d', '#ffc107', '#28a745', '#dc3545', '#17a2b8', '#343a40']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12 }
                }
            }
        }
    });

    // SK Status Pie Chart
    const skStatusData = @json($skStatusDist);
    new Chart(document.getElementById('skStatusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(skStatusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                data: Object.values(skStatusData),
                backgroundColor: ['#6c757d', '#ffc107', '#28a745', '#dc3545', '#17a2b8', '#343a40']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12 }
                }
            }
        }
    });
});
</script>
@endsection
