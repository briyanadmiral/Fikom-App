@extends('layouts.app')
@section('title', 'Buat Surat Tugas Baru')

{{-- Header atas halaman --}}
@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-plus-circle fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Buat Surat Tugas Baru</div>
                <div class="header-desc mt-2">
                    Isi formulir di bawah untuk membuat surat tugas baru dan kelola daftar penerima internal maupun
                    eksternal.
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

        @can('create', App\Models\TugasHeader::class)

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
                    <h5><i class="icon fas fa-ban"></i> Gagal Menyimpan!</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- ✅ Partial form (mode create) --}}
            @include('surat_tugas.partials._form', [
                'mode' => 'create',
                'admins' => $admins,
                'pejabat' => $pejabat,
                'klasifikasi' => $klasifikasi,
                'taskMaster' => $taskMaster,
                'users' => $users,
                'tahun' => $tahun ?? now()->year,
                'semester' => $semester ?? 'Ganjil',
                'bulanRomawi' => $bulanRomawi ?? null,
                'templates' => $templates ?? collect([]),
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
                        Anda tidak memiliki izin untuk membuat Surat Tugas baru. Hanya Admin TU yang dapat membuat Surat Tugas.
                    </div>
                    <a href="{{ route('surat_tugas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endsection
