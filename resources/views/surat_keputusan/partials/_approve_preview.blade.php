{{-- resources/views/surat_keputusan/partials/approve-preview.blade.php --}}
@php
  // Jangan paksa override showSigns; pakai yang dikirim controller jika ada
  $showSigns = isset($showSigns) ? (bool)$showSigns : true;
  $context   = $context ?? 'web';

  $ttdW       = isset($ttdW)       ? (int)$ttdW       : 42;
  $capW       = isset($capW)       ? (int)$capW       : 35;
  $capOpacity = isset($capOpacity) ? (float)$capOpacity : 0.95;

  // Base64 image seharusnya sudah dikirim controller; jangan di-null-kan lagi.
  $ttdImageB64 = $ttdImageB64 ?? null;
  $capImageB64 = $capImageB64 ?? null;
@endphp

@include('surat_keputusan.partials._core', [
  'context'     => $context,
  'sk'          => $sk,
  'kop'         => $kop ?? null,

  'ttdW'        => $ttdW,
  'capW'        => $capW,
  'capOpacity'  => $capOpacity,

  'ttdImageB64' => $ttdImageB64,
  'capImageB64' => $capImageB64,

  'showSigns'   => $showSigns,
])
