{{-- resources/views/surat_keputusan/show.blade.php --}}

@php
    // Alias agar kompatibel baik $sk maupun $keputusan
    $keputusan = $keputusan ?? ($sk ?? null);

    // Guard visibilitas TTD/Cap (default: muncul hanya jika sudah disetujui & sudah signed)
    if (!isset($showSigns)) {
        $statusSurat = $keputusan->status_surat ?? null;
        $showSigns =
            in_array($statusSurat, ['disetujui', 'terbit', 'arsip'], true) && !empty($keputusan->signed_at ?? null);
    }

    // ============================
    // PARSING TEMBUSAN (GLOBAL)
    // ============================
    $rawTembusan = (string) ($keputusan->tembusan ?? '');
    $tembusanItems = collect();

    if ($rawTembusan !== '') {
        // decode &quot; → "
        $decodedStr = trim(html_entity_decode($rawTembusan, ENT_QUOTES, 'UTF-8'));

        // Format lama: JSON Tagify [{"value":"Yth. Rektor"}, ...]
        if (str_starts_with($decodedStr, '[{')) {
            $json = json_decode($decodedStr, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $tembusanItems = collect($json)
                    ->map(function ($item) {
                        if (is_array($item)) {
                            $val = $item['value'] ?? ($item['text'] ?? ($item['name'] ?? reset($item)));
                        } else {
                            $val = $item;
                        }

                        return trim((string) $val);
                    })
                    ->filter()
                    ->unique()
                    ->values();
            }
        }

        // Kalau bukan JSON → anggap format baru (per baris / koma / titik koma)
        if ($tembusanItems->isEmpty()) {
            $tembusanItems = collect(
                preg_split('/[\r\n,;]+/', $decodedStr)
            )
                ->map(fn($v) => trim($v))
                ->filter()
                ->unique()
                ->values();
        }
    }
@endphp

@extends('layouts.app')
@section('title', 'Detail Surat Keputusan: ' . ($keputusan->nomor ?? '-'))

@push('styles')
    <style>
        .page-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem;
            border-radius: 1.1rem;
            margin-bottom: 1.6rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem
        }

        .page-header .icon {
            background: linear-gradient(135deg, #6f42c1 0, #9a6ee5 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #6f42c14d;
            font-size: 1.6rem;
            color: #fff
        }

        .page-header-title {
            font-weight: 700;
            color: #412674;
            font-size: 1.7rem;
            letter-spacing: -.2px;
            margin: 0
        }

        .page-header-desc {
            color: #636e7b;
            font-size: .98rem;
            margin: .1rem 0 0
        }

        #preview-canvas {
            background: #f0f4f9;
            padding: 2rem;
            border-radius: .8rem
        }

        #preview-document {
            background: #fff;
            box-shadow: 0 5px 25px rgba(0, 0, 0, .1)
        }

        .info-card {
            border: none;
            border-radius: .8rem;
            box-shadow: 0 4px 25px rgba(0, 0, 0, .05);
            margin-bottom: 1.2rem
        }

        .info-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 1rem 1.25rem;
            font-weight: 600
        }

        .info-card .card-body {
            padding: 1.1rem 1.25rem
        }

        .info-list {
            list-style: none;
            padding-left: 0;
            margin: 0
        }

        .info-list li {
            display: flex;
            justify-content: space-between;
            padding: .55rem 0;
            border-bottom: 1px solid #f0f0f0
        }

        .info-list li:first-child {
            padding-top: 0
        }

        .info-list li:last-child {
            border-bottom: none
        }

        .info-list .label {
            color: #6c757d;
            font-size: .9rem
        }

        .info-list .value {
            font-weight: 600;
            text-align: right
        }

        .btn-block + .btn-block {
            margin-top: .5rem
        }
    </style>
@endpush

@section('content_header')
    <div class="page-header mt-2">
        <span class="icon"><i class="fas fa-gavel"></i></span>
        <div>
            <h1 class="page-header-title">Detail Surat Keputusan</h1>
            <p class="page-header-desc mb-0">Rincian lengkap untuk SK <b>{{ $keputusan->nomor ?? '-' }}</b>.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        {{-- KIRI: PREVIEW DOKUMEN --}}
        <div class="col-lg-8">
            <div id="preview-canvas">
                <div id="preview-document">
                    @include('surat_keputusan.partials._core', [
                        'context' => 'web',
                        'keputusan' => $keputusan,
                        'kop' => $kop ?? null,
                        // preferensi ukuran/opacity (opsional; fallback di _core)
                        'ttdW' => $ttdW ?? ($preview['ttd_w_mm'] ?? null),
                        'capW' => $capW ?? ($preview['cap_w_mm'] ?? null),
                        'capOpacity' => $capOpacity ?? ($preview['cap_opacity'] ?? null),
                        // aset TTD/Cap hanya bila boleh tampil
                        'ttdImageB64' => $showSigns ? ($ttdImageB64 ?? ($preview['ttd_image_b64'] ?? null)) : null,
                        'capImageB64' => $showSigns ? ($capImageB64 ?? ($preview['cap_image_b64'] ?? null)) : null,
                        'showSigns' => $showSigns,
                    ])
                </div>
            </div>
        </div>

        {{-- KANAN: INFO & AKSI --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top:20px">

                {{-- Aksi Utama --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-bolt mr-2 text-primary"></i>Aksi Utama</div>
                    <div class="card-body">
                        @if ($keputusan->status_surat === 'pending')
                            @can('approve', $keputusan)
                                <a href="{{ route('surat_keputusan.approveForm', $keputusan->id) }}"
                                   class="btn btn-success btn-block">
                                    <i class="fas fa-check-double mr-2"></i>Tinjau & Setujui
                                </a>
                            @endcan
                        @endif

                        @can('update', $keputusan)
                            <a href="{{ route('surat_keputusan.edit', $keputusan->id) }}"
                               class="btn btn-warning btn-block text-dark">
                                <i class="fas fa-pencil-alt mr-2"></i>Edit
                            </a>
                        @endcan

                        <a href="{{ route('surat_keputusan.downloadPdf', $keputusan->id) }}"
                           class="btn btn-danger btn-block" target="_blank">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>

                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                    </div>
                </div>

                {{-- Info SK --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-info-circle mr-2 text-info"></i>Informasi SK</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li>
                                <span class="label">Status</span>
                                <span class="value">
                                    @php $st = $keputusan->status_surat; @endphp
                                    <span
                                        class="badge badge-pill badge-{{ $st == 'disetujui' ? 'success' : ($st == 'pending' ? 'warning' : ($st == 'terbit' ? 'info' : ($st == 'arsip' ? 'dark' : 'secondary'))) }}">
                                        {{ ucfirst($st) }}
                                    </span>
                                </span>
                            </li>
                            <li>
                                <span class="label">Nomor</span>
                                <span class="value">{{ $keputusan->nomor ?? '-' }}</span>
                            </li>
                            <li>
                                <span class="label">Tentang</span>
                                <span class="value">{{ $keputusan->tentang ?? '-' }}</span>
                            </li>
                            <li>
                                <span class="label">Tanggal Surat</span>
                                <span class="value">
                                    @php
                                        $tglSurat = $keputusan->tanggal_surat ?? ($keputusan->tanggal_asli ?? null);
                                    @endphp
                                    {{ $tglSurat ? \Carbon\Carbon::parse($tglSurat)->isoFormat('D MMM YYYY') : '-' }}
                                </span>
                            </li>

                            {{-- Info Disetujui --}}
                            @if ($keputusan->status_surat === 'disetujui' && $keputusan->approved_at)
                                <li>
                                    <span class="label">Disetujui Pada</span>
                                    <span class="value">
                                        {{ $keputusan->approved_at?->format('d F Y, H:i') }} WIB
                                        <br>
                                        <small class="text-muted d-block">
                                            oleh {{ optional($keputusan->approver)->nama_lengkap ?? 'N/A' }}
                                        </small>
                                    </span>
                                </li>
                            @endif

                            {{-- Info Terbit --}}
                            @if (in_array($keputusan->status_surat, ['terbit', 'arsip']) && $keputusan->tanggal_terbit)
                                <li>
                                    <span class="label">Diterbitkan Pada</span>
                                    <span class="value">
                                        <span class="badge badge-info mb-1">
                                            <i class="fas fa-share-square"></i> Terbit
                                        </span><br>
                                        {{ $keputusan->tanggal_terbit?->format('d F Y, H:i') }} WIB
                                        <br>
                                        <small class="text-muted d-block">
                                            oleh {{ optional($keputusan->penerbit)->nama_lengkap ?? 'N/A' }}
                                        </small>
                                    </span>
                                </li>
                            @endif

                            {{-- Info Arsip --}}
                            @if ($keputusan->status_surat === 'arsip' && $keputusan->tanggal_arsip)
                                <li>
                                    <span class="label">Diarsipkan Pada</span>
                                    <span class="value">
                                        <span class="badge badge-dark mb-1">
                                            <i class="fas fa-archive"></i> Arsip
                                        </span><br>
                                        {{ $keputusan->tanggal_arsip?->format('d F Y, H:i') }} WIB
                                        <br>
                                        <small class="text-muted d-block">
                                            oleh {{ optional($keputusan->pengarsip)->nama_lengkap ?? 'N/A' }}
                                        </small>
                                    </span>
                                </li>
                            @endif

                        </ul>
                    </div>
                </div>

                {{-- Pihak Terkait --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-users mr-2 text-success"></i>Pihak Terkait</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li>
                                <span class="label">Pembuat</span>
                                <span class="value">{{ optional($keputusan->pembuat)->nama_lengkap ?? '-' }}</span>
                            </li>
                            <li>
                                <span class="label">Penandatangan</span>
                                <span class="value">
                                    {{ optional($keputusan->penandatanganUser)->nama_lengkap ?? '-' }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Ringkas Isi --}}
                <div class="card info-card">
                    <div class="card-header"><i class="fas fa-list-ol mr-2 text-purple"></i>Ringkas Isi</div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li>
                                <span class="label">Menimbang</span>
                                <span class="value">
                                    {{ is_countable($keputusan->menimbang)
                                        ? count($keputusan->menimbang)
                                        : count(json_decode($keputusan->menimbang ?? '[]', true)) }}
                                </span>
                            </li>
                            <li>
                                <span class="label">Mengingat</span>
                                <span class="value">
                                    {{ is_countable($keputusan->mengingat)
                                        ? count($keputusan->mengingat)
                                        : count(json_decode($keputusan->mengingat ?? '[]', true)) }}
                                </span>
                            </li>
                            <li>
                                <span class="label">Tembusan</span>
                                <span class="value">{{ $tembusanItems->count() }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Daftar Tembusan (opsional) --}}
                @if($tembusanItems->count())
                    <div class="card info-card">
                        <div class="card-header">
                            <i class="fas fa-copy mr-2 text-secondary"></i>Tembusan
                        </div>
                        <div class="card-body">
                            <ol class="mb-0 pl-3">
                                @foreach($tembusanItems as $t)
                                    <li>{{ $t }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Lampiran --}}
    @if ($keputusan && $keputusan->attachments && $keputusan->attachments->count() > 0)
        <div class="card card-outline card-secondary mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-paperclip"></i> Lampiran Dokumen ({{ $keputusan->attachments->count() }})
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($keputusan->attachments as $att)
                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="{{ $att->file_icon }} fa-3x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                {{ \Illuminate\Support\Str::limit($att->nama_file, 30) }}
                                            </h6>
                                            <small class="text-muted d-block mb-1">
                                                <span class="badge badge-info">{{ $att->kategori_label }}</span>
                                                • {{ $att->file_size_human }}
                                            </small>
                                            @if ($att->deskripsi)
                                                <p class="mb-0 small text-muted">{{ $att->deskripsi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-download"></i> {{ $att->download_count }}x
                                            @if ($att->last_downloaded_at)
                                                <br>
                                                <span class="small">
                                                    <i class="far fa-clock"></i>
                                                    {{ \Carbon\Carbon::parse($att->last_downloaded_at)->diffForHumans() }}
                                                </span>
                                            @endif
                                        </small>
                                        <a href="{{ route('surat_keputusan.attachments.download', [$keputusan->id, $att->id]) }}"
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
