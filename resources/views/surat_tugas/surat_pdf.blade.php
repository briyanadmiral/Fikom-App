{{-- resources/views/surat_keputusan/surat_pdf.blade.php --}}

@php
  // === GUARD VISIBILITAS TTD/CAP (PDF) ===
  // Controller idealnya mengirim 'showSigns'. Fallback aman bila belum dikirim:
  if (!isset($showSigns)) {
    $showSigns = isset($keputusan)
      ? (($keputusan->status_surat ?? null) === 'disetujui' && !empty($keputusan->signed_at ?? null))
      : false;
  }
  // Jika controller tidak set isDraft, anggap draft bila showSigns=false
  $isDraft = $isDraft ?? (!$showSigns);
@endphp

@php
  $context = 'pdf';

  // Ambil ukuran & opacity dari parameter atau fallback ke konfigurasi model
  $ttdW       = $ttdW       ?? (method_exists($keputusan,'ttdWidthMm') ? $keputusan->ttdWidthMm() : (int)($keputusan->ttd_config['w_mm'] ?? 42));
  $capW       = $capW       ?? (method_exists($keputusan,'capWidthMm') ? $keputusan->capWidthMm() : (int)($keputusan->cap_config['w_mm'] ?? 35));
  $capOpacity = $capOpacity ?? (method_exists($keputusan,'capOpacity') ? $keputusan->capOpacity() : (float)($keputusan->cap_config['opacity'] ?? 0.95));

  // HARDENING: Jangan oper gambar TTD/Cap ke partial jika belum boleh tampil
  $ttdImageB64_safe = $showSigns ? ($ttdImageB64 ?? null) : null;
  $capImageB64_safe = $showSigns ? ($capImageB64 ?? null) : null;
@endphp

@include('surat_keputusan.partials._core', [
  'context'      => $context,
  'keputusan'    => $keputusan,
  'kop'          => $kop ?? null,
  'penerimaList' => $penerimaList ?? null,

  // preferensi ukuran/opacity
  'ttdW'         => $ttdW,
  'capW'         => $capW,
  'capOpacity'   => $capOpacity,

  // aset gambar (aman: akan null jika belum boleh tampil)
  'ttdImageB64'  => $ttdImageB64_safe,
  'capImageB64'  => $capImageB64_safe,

  // flag kunci agar _core ikut patuh
  'showSigns'    => $showSigns,
])

@if ($isDraft)
  <div style="
    position: fixed; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-20deg);
    font-size: 70px; color: rgba(214, 55, 50, 0.25); font-weight: 700; z-index: 0;
    white-space: nowrap; pointer-events: none;
  ">BELUM DISETUJUI</div>
@endif
