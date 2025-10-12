@extends('layouts.app')
@section('title', 'Edit Surat Keputusan')

{{-- Tentukan mode sekali saja --}}
@php
$keputusan = $keputusan ?? $sk ?? null;
$mode = request('mode') === 'revisi' ? 'revisi' : 'edit';
@endphp

{{-- Header atas halaman --}}
@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-edit fa-lg"></i>
            </div>
            <div>
                <div class="header-title">
                    {{ $mode === 'revisi' ? 'Revisi' : 'Edit' }} Surat Keputusan
                </div>
                <div class="header-desc mt-2">
                    Perbarui detail surat keputusan dengan nomor 
                    <strong>{{ $keputusan->nomor ?? '-' }}</strong>.
                    @if($mode === 'revisi')
                        <span class="badge badge-warning ml-1">Mode Revisi</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .custom-header-box {
            background: linear-gradient(90deg, #f39c12 0%, #e74c3c 100%);
            color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(44, 62, 80, .13);
            padding: 1.5rem 2rem 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
            border-left: 6px solid #e67e22;
            margin-top: .5rem;
        }

        .header-icon {
            width: 54px;
            height: 54px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
            font-size: 2rem;
            box-shadow: 0 2px 12px 0 rgba(230, 126, 34, .13);
        }

        .header-title {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .header-desc {
            font-size: 1.07rem;
            color: #fef5e7;
            font-weight: 400;
            margin-left: .1rem;
        }

        .header-desc .badge-warning {
            background-color: rgba(255, 255, 255, .3);
            color: #fff;
            font-weight: 600;
            padding: .3rem .6rem;
            border-radius: .3rem;
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

        @can('update', $keputusan)

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <h5><i class="icon fas fa-ban"></i> Gagal Memperbarui!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Info status surat --}}
            @if($keputusan->status_surat ?? false)
                <div class="alert alert-info alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i>
                    <strong>Status Surat:</strong> 
                    <span class="badge badge-{{ $keputusan->status_surat === 'disetujui' ? 'success' : ($keputusan->status_surat === 'pending' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($keputusan->status_surat) }}
                    </span>
                    @if($keputusan->status_surat === 'disetujui')
                        <span class="ml-2 text-muted">Surat ini telah disetujui. Perubahan struktural mungkin dibatasi.</span>
                    @endif
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- ✅ Partial form (mode edit/revisi) --}}
            @include('surat_keputusan.partials._form', [
                'mode' => $mode,
                'keputusan' => $keputusan,
                'pejabat' => $pejabat ?? collect(),
                'admins' => $admins ?? collect(),
                'users' => $users ?? collect(),
                'klasifikasi' => $klasifikasi ?? collect(),
                'bulanRomawi' => $bulanRomawi ?? ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'],
                'tembusanPresets' => $tembusanPresets ?? [
                    'Yth. Rektor',
                    'Yth. Wakil Rektor I',
                    'Yth. Wakil Rektor II',
                    'Dekan Fakultas Ilmu Komputer',
                    'BAAK',
                    'BAUK',
                    'BAK',
                    'Kepala Program Studi Sistem Informasi',
                    'Unit Kepegawaian',
                    'Arsip'
                ],
                'currentYear' => $currentYear ?? now()->year,
                'currentRomawi' => $currentRomawi ?? null,
            ])
        @else
            {{-- Guard akses --}}
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-ban"></i> Akses Ditolak</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Tidak Memiliki Akses</h5>
                        Anda tidak memiliki izin untuk mengedit Surat Keputusan ini.
                        @if($keputusan->status_surat === 'disetujui')
                            <br><small class="text-muted">Surat ini sudah disetujui dan tidak dapat diubah.</small>
                        @endif
                    </div>
                    <a href="{{ route('surat_keputusan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endsection
