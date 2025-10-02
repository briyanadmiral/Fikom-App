{{-- resources/views/surat_keputusan/partials/approve-preview.blade.php --}}

@php
  // === Halaman ini khusus APPROVER ===
  // Paksa TTD & Cap SELALU tampak di halaman approve (meski status masih pending)
  $showSigns = true;

  // Konteks web (pakai CSS web dari _core)
  $context = 'web';

  // Nilai preferensi ukuran & opacity yang diterima langsung dari controller (approveForm/approvePreview)
  $ttdW       = isset($ttdW)       ? (int)$ttdW       : 42;
  $capW       = isset($capW)       ? (int)$capW       : 35;
  $capOpacity = isset($capOpacity) ? (float)$capOpacity : 0.95;

  // Aset gambar base64 (controller sudah menyiapkan via getSigningAssets)
  $ttdImageB64 = $ttdImageB64 ?? null;
  $capImageB64 = $capImageB64 ?? null;
@endphp

{{-- Panggil _core SK agar seluruh isi surat bisa live-preview --}}
@include('surat_keputusan.partials._core', [
  'context'     => $context,
  'keputusan'   => $keputusan,
  'kop'         => $kop ?? null,

  // preferensi ukuran/opacity
  'ttdW'        => $ttdW,
  'capW'        => $capW,
  'capOpacity'  => $capOpacity,

  // aset gambar
  'ttdImageB64' => $ttdImageB64,
  'capImageB64' => $capImageB64,

  // kunci: TTD/Cap HARUS tampak di halaman approve
  'showSigns'   => $showSigns,
])
