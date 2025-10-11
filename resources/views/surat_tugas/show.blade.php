{{-- Guard visibilitas TTD/Cap --}}
@php
    if (!isset($showSigns)) {
        $showSigns = ($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null);
    }
@endphp

@extends('layouts.app')

@section('title', 'Detail Surat Tugas: ' . ($tugas->nomor ?? '-'))

@push('styles')
    <style>
        /* Menggunakan kembali style header dan komponen dari halaman lain */
        .page-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem;
        }

        .page-header .icon {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #17a2b84d;
            font-size: 2rem;
        }

        .page-header-title {
            font-weight: bold;
            color: #0c5460;
            font-size: 1.85rem;
            margin-bottom: 0.13rem;
            letter-spacing: -1px;
        }

        .page-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        #preview-canvas {
            background-color: #f0f4f9;
            padding: 2rem;
            border-radius: .8rem;
        }

        #preview-document {
            background-color: #fff;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .info-card {
            border: none;
            border-radius: .8rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, .05);
            margin-bottom: 1.5rem;
        }

        .info-card .card-header {
            background-color: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .info-card .card-body {
            padding: 1.25rem;
        }

        .info-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .info-list li {
            display: flex;
            justify-content: space-between;
            padding: .6rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-list li:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-list li:first-child {
            padding-top: 0;
        }

        .info-list .label {
            color: #6c757d;
            font-size: .9rem;
        }

        .info-list .value {
            font-weight: 600;
            text-align: right;
        }

        /* [DISEMPURNAKAN] Jarak antar tombol blok */
        .btn-block+.btn-block {
            margin-top: 0.5rem;
        }
    </style>
@endpush

@section('content_header')
    <div class="page-header mt-2 mb-3">
        <span class="icon"><i class="fas fa-file-alt text-white"></i></span>
        <span>
            <div class="page-header-title">Detail Surat Tugas</div>
            <div class="page-header-desc">Menampilkan rincian lengkap untuk surat <b>{{ $tugas->nomor }}</b>.</div>
        </span>
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- KOLOM KIRI: PRATINJAU SURAT --}}
        <div class="col-lg-8">
            <div id="preview-canvas">
                <div id="preview-document">
                    @include('surat_tugas.partials._core', [
                        'context' => 'web',
                        'tugas' => $tugas,
                        'kop' => $kop ?? null,
                        'ttdW' => $preview['ttd_w_mm'] ?? null,
                        'capW' => $preview['cap_w_mm'] ?? null,
                        'capOpacity' => $preview['cap_opacity'] ?? null,
                        'ttdImageB64' => $showSigns ? $preview['ttd_image_b64'] ?? null : null,
                        'capImageB64' => $showSigns ? $preview['cap_image_b64'] ?? null : null,
                        'showSigns' => $showSigns,
                    ])
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: INFORMASI & AKSI --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px;">
                {{-- Kartu Aksi Utama --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-bolt mr-2 text-primary"></i>Aksi Utama</div>
                    <div class="card-body">
                        {{-- [DIUBAH] Menghapus class `btn-lg` dari semua tombol di bawah ini --}}
                        @if ($tugas->status_surat === 'pending' && Gate::allows('approve-tugas', $tugas))
                            <a href="{{ route('surat_tugas.approveForm', $tugas->id) }}"
                                class="btn btn-success btn-block"><i class="fas fa-check-double mr-2"></i>Tinjau &
                                Setujui</a>
                        @endif
                        @can('update', $tugas)
                            <a href="{{ route('surat_tugas.edit', $tugas->id) }}" class="btn btn-warning btn-block text-dark"><i
                                    class="fas fa-pencil-alt mr-2"></i>Edit Surat</a>
                        @endcan
                        <a href="{{ route('surat_tugas.downloadPdf', $tugas->id) }}" class="btn btn-danger btn-block"
                            target="_blank"><i class="fas fa-file-pdf mr-2"></i>Download PDF</a>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block"><i
                                class="fas fa-arrow-left mr-2"></i>Kembali</a>
                    </div>
                </div>

                {{-- Kartu Info Surat --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-info-circle mr-2 text-info"></i>Informasi Surat</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li><span class="label">Status</span><span class="value"><span
                                        class="badge badge-pill badge-{{ $tugas->status_surat == 'disetujui' ? 'success' : ($tugas->status_surat == 'pending' ? 'warning' : 'secondary') }}">{{ Str::ucfirst($tugas->status_surat) }}</span></span>
                            </li>
                            <li><span class="label">Perihal</span><span class="value">{{ $tugas->nama_umum }}</span></li>
                            <li><span class="label">Klasifikasi</span><span
                                    class="value">{{ optional($tugas->klasifikasi)->kode ?? '-' }}</span></li>
                        </ul>
                    </div>
                </div>

                {{-- Kartu Pihak Terkait --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-users mr-2 text-success"></i>Pihak Terkait</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li><span class="label">Dibuat oleh</span><span
                                    class="value">{{ optional($tugas->pembuat)->nama_lengkap ?? '-' }}</span></li>
                            <li><span class="label">Penandatangan</span><span
                                    class="value">{{ optional($tugas->penandatanganUser)->nama_lengkap ?? '-' }}</span>
                            </li>
                            <li><span class="label">Penerima</span><span class="value">{{ $tugas->penerima->count() }}
                                    Orang</span></li>
                        </ul>
                    </div>
                </div>

                {{-- Kartu Waktu & Tempat --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-calendar-alt mr-2 text-danger"></i>Waktu & Tempat</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li><span class="label">Mulai</span><span
                                    class="value">{{ optional($tugas->waktu_mulai)->isoFormat('D MMM YYYY, HH:mm') ?? '-' }}</span>
                            </li>
                            <li><span class="label">Selesai</span><span
                                    class="value">{{ optional($tugas->waktu_selesai)->isoFormat('D MMM YYYY, HH:mm') ?? '-' }}</span>
                            </li>
                            <li><span class="label">Tempat</span><span class="value">{{ $tugas->tempat ?? '-' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
