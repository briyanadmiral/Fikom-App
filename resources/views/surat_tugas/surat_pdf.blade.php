{{-- resources/views/surat_tugas/surat_pdf.blade.php --}}
@extends('layouts.pdf_surat_tugas')

@section('content')
@php
    // Konteks rendering untuk partial
    $context = 'pdf';

    // Fallback aman jika controller belum mengirim showSigns/isDraft
    $showSigns = (bool)($showSigns ?? (
        isset($tugas)
            ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
            : false
    ));
    $isDraft = (bool)($isDraft ?? !$showSigns);

    // Ambil preferensi ukuran/opacity dari DB jika tidak di-override
    $ttdW       = $ttdW       ?? ($tugas->ttd_w_mm      ?? null);
    $capW       = $capW       ?? ($tugas->cap_w_mm      ?? null);
    $capOpacity = $capOpacity ?? ($tugas->cap_opacity   ?? null);

    // Demi keamanan: hanya oper gambar TTD/Cap bila boleh tampil
    $ttdImageB64_safe = $showSigns ? ($ttdImageB64 ?? null) : null;
    $capImageB64_safe = $showSigns ? ($capImageB64 ?? null) : null;
@endphp

@include('surat_tugas.partials._core', [
    'context'         => $context,
    'tugas'           => $tugas,
    'kop'             => $kop ?? null,
    'penerimaList'    => $penerimaList ?? null,

    // preferensi ukuran/opacity
    'ttdW'            => $ttdW,
    'capW'            => $capW,
    'capOpacity'      => $capOpacity,

    // aset gambar (sudah diamankan)
    'ttdImageB64'     => $ttdImageB64_safe,
    'capImageB64'     => $capImageB64_safe,

    // flag kunci agar _core ikut patuh
    'showSigns'       => $showSigns,

    // PENTING: kop sudah ada di header layout -> jangan render kop di konten
    'showKopInContent'=> false,
])
@endsection
