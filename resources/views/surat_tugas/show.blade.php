{{-- resources/views/surat_tugas/show.blade.php --}}
@php
    // ==================== GUARDS & FALLBACKS ====================
    $showSigns =
        $showSigns ??
        (isset($tugas) ? ($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null) : false);

    $pv = isset($preview) && is_array($preview) ? $preview : [];

    $klasifikasiKode =
        optional($tugas->klasifikasi)->kode ??
        (optional($tugas->klasifikasiSurat)->kode ?? ($tugas->klasifikasi_kode ?? ($tugas->klasifikasi ?? '—')));

    $namaPembuat = optional($tugas->pembuat)->nama_lengkap ?? (optional($tugas->creator)->nama_lengkap ?? '—');
    $namaPenandatangan = optional($tugas->penandatanganUser)->nama_lengkap ?? '—';

    $badgeMap = [
        'draft' => 'secondary',
        'pending' => 'warning',
        'disetujui' => 'success',
        'ditolak' => 'danger',
    ];
    $statusText = ucfirst($tugas->status_surat ?? 'Draft');
    $statusBadge = $badgeMap[$tugas->status_surat ?? 'draft'] ?? 'secondary';
@endphp

@extends('layouts.app')
@section('title', 'Detail Surat Tugas: ' . ($tugas->nomor ?? 'Tanpa Nomor'))

@push('styles')
    <style>
        /* ==================== TIDAK MENGUBAH HEADER ==================== */

        /* ==================== HEADER STYLES ==================== */
        .surat-header {
            background: linear-gradient(135deg, #f3f6fa 0%, #e8f0f8 100%);
            padding: 1.5rem 2.2rem;
            border-radius: 1.2rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        .surat-header .icon {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
            font-size: 2rem;
            color: #fff;
            flex-shrink: 0;
        }

        .surat-header-content {
            flex: 1;
        }

        .surat-header-title {
            font-weight: 700;
            color: #0c5460;
            font-size: 1.85rem;
            margin-bottom: 0.3rem;
            letter-spacing: -0.5px;
        }

        .surat-header-desc {
            color: #636e7b;
            font-size: 1.05rem;
            margin: 0;
        }

        /* ==================== PREVIEW CANVAS ==================== */
        #preview-canvas {
            background-color: #f0f4f9;
            padding: 2rem;
            border-radius: .9rem;
            margin-bottom: 1.5rem;
        }

        #preview-document {
            background-color: #fff;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }


        /* ==================== META BAR ==================== */
        .meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin: 0 0 1.2rem 0
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            color: #334155;
            padding: .45rem .75rem;
            border-radius: .6rem;
            font-weight: 600;
            font-size: .86rem
        }

        .chip .icon {
            opacity: .7
        }

        /* ==================== PREVIEW AREA ==================== */
        .preview-wrap {
            background: linear-gradient(180deg, #f7f9fc 0, #f2f6fb 100%);
            border: 1px solid #e7edf6;
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1.2rem
        }

        .paper-shell {
            background: #fff;
            border-radius: .6rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
            overflow: hidden
        }

        /* menjaga proporsi A4 feel */
        .paper-shell-inner {
            padding: 1.2rem
        }

        @media(min-width:1200px) {
            .paper-shell-inner {
                padding: 1.5rem
            }
        }

        /* ==================== KARTU GENERIK ==================== */
        .card.soft {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
            overflow: hidden;
            margin-bottom: 1rem
        }

        .card.soft .card-header {
            background: linear-gradient(135deg, #ffffff 0%, #f9fbfd 100%);
            border-bottom: 1px solid #eef2f7;
            font-weight: 700;
            color: #243447;
            padding: 1rem 1.25rem
        }

        .card.soft .card-body {
            padding: 1.1rem 1.25rem
        }

        /* ==================== LIST INFO ==================== */
        .info-list {
            list-style: none;
            margin: 0;
            padding: 0
        }

        .info-list li {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .65rem 0;
            border-bottom: 1px dashed #eef2f7
        }

        .info-list li:last-child {
            border-bottom: 0
        }

        .info-list .label {
            color: #6b7280;
            font-weight: 600;
            min-width: 130px
        }

        .info-list .value {
            font-weight: 700;
            color: #1f2937;
            text-align: right;
            word-break: break-word
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            padding: .15rem .45rem;
            border-radius: .35rem
        }

        /* ==================== SECTION RANGKUMAN ==================== */
        .section {
            margin-bottom: 1rem
        }

        .section .section-label {
            font-size: .82rem;
            letter-spacing: .3px;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .4rem
        }

        .section .section-content {
            background: #fbfdff;
            border: 1px solid #eef2f7;
            border-left: 3px solid #06b6d4;
            border-radius: .6rem;
            padding: .9rem
        }

        .preline {
            white-space: pre-line;
            line-height: 1.7;
            color: #374151
        }

        .empty-state {
            color: #9aa4b2;
            font-style: italic
        }

        /* ==================== PENERIMA ==================== */
        .penerima-list {
            list-style: none;
            margin: .4rem 0 0 0;
            padding: 0
        }

        .penerima-list li {
            padding: .4rem 0;
            border-bottom: 1px dashed #eef2f7
        }

        .penerima-list li:last-child {
            border-bottom: 0
        }

        .penerima-list li:before {
            content: "•";
            color: #06b6d4;
            margin-right: .45rem
        }

        /* ==================== SIDEBAR ==================== */
        .sticky-sidebar {
            position: sticky;
            top: 20px
        }

        .btn-block+.btn-block {
            margin-top: .55rem
        }

        .btn {
            border-radius: .6rem;
            font-weight: 700
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12)
        }

        @media(max-width:991px) {
            .sticky-sidebar {
                position: static
            }
        }
    </style>
@endpush

@section('content_header')
    {{-- header tetap --}}
    <div class="surat-header mt-2 mb-3">
        <span class="icon"><i class="fas fa-file-alt"></i></span>
        <div class="surat-header-content">
            <h1 class="surat-header-title mb-1">Detail Surat Tugas</h1>
            <p class="surat-header-desc mb-0">
                Menampilkan rincian lengkap untuk surat <strong>{{ $tugas->nomor ?? 'Tanpa Nomor' }}</strong>
            </p>
        </div>
    </div>
@endsection

@section('content')
    {{-- ==================== META BAR CEPAT (di bawah header) ==================== --}}
    <div class="meta-bar">
        <span class="chip">
            <span class="icon"><i class="far fa-check-circle"></i></span>
            Status:
            <span class="badge badge-{{ $statusBadge }} ml-1">{{ $statusText }}</span>
        </span>
        <span class="chip">
            <span class="icon"><i class="far fa-hashtag"></i></span>
            Nomor: <span class="mono">{{ $tugas->nomor ?? '—' }}</span>
        </span>
        <span class="chip">
            <span class="icon"><i class="far fa-calendar-alt"></i></span>
            Tanggal: {{ optional($tugas->tanggal_surat)->isoFormat('D MMMM YYYY') ?? '—' }}
        </span>
        <span class="chip">
            <span class="icon"><i class="far fa-folder-open"></i></span>
            Klasifikasi: <span class="mono">{{ $klasifikasiKode }}</span>
        </span>
        @if ($tugas->signed_at)
            <span class="chip">
                <span class="icon"><i class="far fa-clock"></i></span>
                Disetujui: {{ $tugas->signed_at->isoFormat('D MMM YYYY, HH:mm') }}
            </span>
        @endif
    </div>

    <div class="row">
        {{-- ==================== KOLOM KIRI ==================== --}}
        <div class="col-lg-8">
            {{-- PREVIEW SURAT --}}
            <div class="preview-wrap">
                <div class="paper-shell">
                    <div class="paper-shell-inner">
                        @include('surat_tugas.partials._core', [
                            'context' => 'web',
                            'tugas' => $tugas,
                            'kop' => $kop ?? null,
                            'ttdW' => $pv['ttd_w_mm'] ?? ($tugas->ttd_w_mm ?? 42),
                            'capW' => $pv['cap_w_mm'] ?? ($tugas->cap_w_mm ?? 35),
                            'capOpacity' => $pv['cap_opacity'] ?? ($tugas->cap_opacity ?? 0.95),
                            'ttdImageB64' => $showSigns ? $pv['ttd_image_b64'] ?? null : null,
                            'capImageB64' => $showSigns ? $pv['cap_image_b64'] ?? null : null,
                            'showSigns' => $showSigns,
                            'showKopInContent' => true,
                        ])
                    </div>
                </div>
            </div>

            {{-- RANGKUMAN ISI --}}
            <div class="card soft">
                <div class="card-header"><i class="fas fa-list-alt mr-2 text-primary"></i>Rangkuman Isi Surat</div>
                <div class="card-body">
                    <div class="section">
                        <div class="section-label"><i class="fas fa-paragraph mr-1"></i> Redaksi Pembuka</div>
                        <div class="section-content">
                            @if ($tugas->redaksi_pembuka)
                                <div class="preline">{{ $tugas->redaksi_pembuka }}</div>
                            @else
                                <span class="empty-state">Tidak ada redaksi pembuka.</span>
                            @endif
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-label"><i class="fas fa-tasks mr-1"></i> Detail Tugas (Uraian)</div>
                        <div class="section-content">
                            @if ($tugas->detail_tugas)
                                <div class="preline">{!! nl2br(e($tugas->detail_tugas)) !!}</div>
                            @else
                                <span class="empty-state">Belum ada detail tugas.</span>
                            @endif
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-label"><i class="fas fa-check-circle mr-1"></i> Penutup</div>
                        <div class="section-content">
                            @if ($tugas->penutup)
                                <div class="preline">{{ $tugas->penutup }}</div>
                            @else
                                <span class="empty-state">Tidak ada penutup.</span>
                            @endif
                        </div>
                    </div>

                    <div class="section mb-0">
                        <div class="section-label"><i class="fas fa-copy mr-1"></i> Tembusan</div>
                        <div class="section-content">
                            @if ($tugas->tembusan)
                                <div class="preline">{{ $tugas->tembusan }}</div>
                            @else
                                <span class="empty-state">Tidak ada tembusan.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== KOLOM KANAN (SIDEBAR) ==================== --}}
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                {{-- AKSI UTAMA --}}
                <div class="card soft">
                    <div class="card-header"><i class="fas fa-bolt mr-2 text-warning"></i>Aksi Utama</div>
                    <div class="card-body">
                        @if (($tugas->status_surat ?? null) === 'pending' && Gate::allows('approve', $tugas))
                            <a href="{{ route('surat_tugas.approve.form', $tugas->id) }}"
                                class="btn btn-success btn-block">
                                <i class="fas fa-check-double mr-2"></i>Tinjau & Setujui
                            </a>
                        @endif
                        @can('update', $tugas)
                            <a href="{{ route('surat_tugas.edit', $tugas->id) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-pencil-alt mr-2"></i>Edit Surat
                            </a>
                        @endcan
                        <a href="{{ route('surat_tugas.downloadPdf', $tugas->id) }}" class="btn btn-danger btn-block"
                            target="_blank">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>

                {{-- INFORMASI SURAT --}}
                <div class="card soft">
                    <div class="card-header"><i class="fas fa-info-circle mr-2 text-info"></i>Informasi Surat</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li>
                                <span class="label">Status</span>
                                <span class="value">
                                    <span class="badge badge-pill badge-{{ $statusBadge }}">{{ $statusText }}</span>
                                </span>
                            </li>
                            <li><span class="label">Nomor Surat</span><span class="value"><span
                                        class="mono">{{ $tugas->nomor ?? '—' }}</span></span></li>
                            <li><span class="label">Tanggal Surat</span><span
                                    class="value">{{ optional($tugas->tanggal_surat)->isoFormat('D MMMM YYYY') ?? '—' }}</span>
                            </li>
                            <li><span class="label">Perihal</span><span
                                    class="value">{{ $tugas->nama_umum ?? '—' }}</span></li>
                            <li><span class="label">Klasifikasi</span><span class="value"><span
                                        class="mono">{{ $klasifikasiKode }}</span></span></li>
                            @if ($tugas->signed_at)
                                <li><span class="label">Disetujui Pada</span><span
                                        class="value">{{ $tugas->signed_at->isoFormat('D MMM YYYY, HH:mm') }}</span></li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- PIHAK TERKAIT --}}
                <div class="card soft">
                    <div class="card-header"><i class="fas fa-users mr-2 text-success"></i>Pihak Terkait</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li><span class="label">Dibuat oleh</span><span class="value">{{ $namaPembuat }}</span>
                            </li>
                            <li><span class="label">Penandatangan</span><span
                                    class="value">{{ $namaPenandatangan }}</span></li>
                            <li>
                                <span class="label">Jumlah Penerima</span>
                                <span class="value"><span class="badge badge-primary">{{ $tugas->penerima->count() }}
                                        Orang</span></span>
                            </li>
                        </ul>

                        @if ($tugas->penerima->count() > 0)
                            <div class="mt-3">
                                <div class="section-label mb-2"><i class="fas fa-user-friends mr-1"></i> Daftar Penerima
                                </div>
                                <ul class="penerima-list">
                                    @foreach ($tugas->penerima->take(6) as $p)
                                        <li>{{ optional($p->pengguna)->nama_lengkap ?? 'Nama tidak tersedia' }}</li>
                                    @endforeach
                                    @if ($tugas->penerima->count() > 6)
                                        <li class="text-muted font-italic">+ {{ $tugas->penerima->count() - 6 }} orang
                                            lainnya</li>
                                    @endif
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- WAKTU & TEMPAT --}}
                <div class="card soft">
                    <div class="card-header"><i class="fas fa-calendar-alt mr-2 text-danger"></i>Waktu & Tempat</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li><span class="label">Mulai</span><span
                                    class="value">{{ optional($tugas->waktu_mulai)->isoFormat('D MMM YYYY, HH:mm') ?? '—' }}</span>
                            </li>
                            <li><span class="label">Selesai</span><span
                                    class="value">{{ optional($tugas->waktu_selesai)->isoFormat('D MMM YYYY, HH:mm') ?? '—' }}</span>
                            </li>
                            <li><span class="label">Tempat</span><span class="value">{{ $tugas->tempat ?? '—' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- METADATA --}}
                <div class="card soft">
                    <div class="card-header"><i class="fas fa-database mr-2 text-secondary"></i>Metadata</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li><span class="label">Tahun</span><span class="value">{{ $tugas->tahun ?? '—' }}</span>
                            </li>
                            <li><span class="label">Semester</span><span
                                    class="value">{{ $tugas->semester ?? '—' }}</span></li>
                            @if ($tugas->no_bin)
                                <li><span class="label">No. BIN</span><span class="value"><span
                                            class="mono">{{ $tugas->no_bin }}</span></span></li>
                            @endif
                            @if ($tugas->kode_surat)
                                <li><span class="label">Kode Surat</span><span class="value"><span
                                            class="mono">{{ $tugas->kode_surat }}</span></span></li>
                            @endif
                            <li><span class="label">Bulan (Romawi)</span><span class="value"><span
                                        class="mono">{{ $tugas->bulan ?? '—' }}</span></span></li>
                            <li><span class="label">Dibuat Pada</span><span
                                    class="value">{{ optional($tugas->created_at)->isoFormat('D MMM YYYY, HH:mm') ?? '—' }}</span>
                            </li>
                            @if ($tugas->updated_at && $tugas->updated_at != $tugas->created_at)
                                <li><span class="label">Terakhir Diubah</span><span
                                        class="value">{{ $tugas->updated_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
