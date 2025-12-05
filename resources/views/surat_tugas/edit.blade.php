@extends('layouts.app')
@section('title', 'Edit Surat Tugas')

{{-- Tentukan mode sekali saja --}}
@php
    $mode = request('mode') === 'koreksi' ? 'koreksi' : 'edit';
@endphp

{{-- Header atas halaman --}}
@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-edit fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Edit Surat Tugas</div>
                <div class="header-desc mt-2">
                    Ubah detail & ajukan ulang. Nomor:
                    <span class="nomor-surat-highlight">{{ $tugas->nomor }}</span>
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

        {{-- Guard akses edit (sesuaikan ability policy kamu) --}}
        @can('update', $tugas)

            {{-- Error validasi --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Gagal Memperbarui!</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ✅ Partial form (mode edit/koreksi) --}}
            @include('surat_tugas.partials._form', [
                'mode' => $mode,
                'tugas' => $tugas,
                'admins' => $admins,
                'pejabat' => $pejabat,
                'klasifikasi' => $klasifikasi,
                'taskMaster' => $taskMaster,
                'users' => $users,
            ])
        @else
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-ban"></i> Akses Ditolak</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger mb-3">
                        <strong>Anda tidak memiliki izin</strong> untuk mengedit Surat Tugas ini.
                    </div>
                    <a href="{{ route('surat_tugas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        @endcan

    </div>
@endsection