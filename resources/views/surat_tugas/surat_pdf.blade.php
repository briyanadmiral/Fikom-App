{{-- resources/views/surat_tugas/surat_pdf.blade.php --}}
@extends('layouts.pdf_surat_tugas')

@section('content')
@php
    // konteks PDF & guard tanda tangan
    $context   = 'pdf';
    $showSigns = (bool)($showSigns ?? (
        isset($tugas)
            ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
            : false
    ));
    $isDraft = (bool)($isDraft ?? !$showSigns);

    // preferensi ukuran/opacity dari DB jika tidak override
    $ttdW       = $ttdW       ?? ($tugas->ttd_w_mm    ?? null);
    $capW       = $capW       ?? ($tugas->cap_w_mm    ?? null);
    $capOpacity = $capOpacity ?? ($tugas->cap_opacity ?? null);

    // aset gambar hanya bila boleh ditampilkan
    $ttdImageB64_safe = $showSigns ? ($ttdImageB64 ?? null) : null;
    $capImageB64_safe = $showSigns ? ($capImageB64 ?? null) : null;
@endphp

@include('surat_tugas.partials._core', [
    'context'          => $context,
    'tugas'            => $tugas,
    'kop'              => $kop ?? null,
    'penerimaList'     => $penerimaList ?? null,

    // preferensi TTD/Cap
    'ttdW'             => $ttdW,
    'capW'             => $capW,
    'capOpacity'       => $capOpacity,

    // aset yang sudah diamankan
    'ttdImageB64'      => $ttdImageB64_safe,
    'capImageB64'      => $capImageB64_safe,

    // flag kepatuhan
    'showSigns'        => $showSigns,

    // kop sudah ada di layout pdf -> matikan kop di konten
    'showKopInContent' => false,
])
@endsection
