{{-- resources/views/surat_keputusan/surat_pdf.blade.php --}}
@extends('layouts.pdf_surat_keputusan')

@section('content')
@php
  // Alias agar kompatibel baik $sk maupun $keputusan
  $keputusan = $keputusan ?? $sk ?? null;

  /*
   |-------------------------------------------------------------
   | Guard visibilitas TTD/Cap & status draft
   | - showSigns: true hanya jika SK disetujui & sudah ditandatangani
   | - isDraft:   default mengikuti showSigns (jika belum boleh tampil tanda, anggap draft)
   |-------------------------------------------------------------
  */
  $showSigns = (bool)($showSigns ?? false);
  $isDraft   = (bool)($isDraft ?? (!$showSigns));

  /*
   |-------------------------------------------------------------
   | Preferensi ukuran/opacity (fallback ke konfigurasi model)
   |-------------------------------------------------------------
  */
  $ttdW       = $ttdW       ?? (int)($keputusan->ttd_config['w_mm']    ?? $keputusan->ttd_w_mm    ?? 42);
  $capW       = $capW       ?? (int)($keputusan->cap_config['w_mm']    ?? $keputusan->cap_w_mm    ?? 35);
  $capOpacity = $capOpacity ?? (float)($keputusan->cap_config['opacity'] ?? $keputusan->cap_opacity ?? 0.95);

  // Demi keamanan, jangan teruskan aset TTD/Cap jika belum boleh tampil
  $ttdImageB64_safe = $showSigns ? ($ttdImageB64 ?? null) : null;
  $capImageB64_safe = $showSigns ? ($capImageB64 ?? null) : null;

  // Mode rendering untuk partial isi
  $context = 'pdf';
@endphp

{{-- Render isi dokumen via partial core (reusable untuk PDF & Web) --}}
@include('surat_keputusan.partials._core', [
  'context'      => $context,
  'keputusan'    => $keputusan,
  'kop'          => $kop ?? null,

  // preferensi TTD/Cap
  'ttdW'         => $ttdW,
  'capW'         => $capW,
  'capOpacity'   => $capOpacity,

  // aset gambar (sudah diamankan)
  'ttdImageB64'  => $ttdImageB64_safe,
  'capImageB64'  => $capImageB64_safe,

  // patuhi guard
  'showSigns'    => $showSigns,

  // PENTING: kop sudah ada di header layout → jangan render kop di konten
  'showKopInContent' => false,
])
@endsection
