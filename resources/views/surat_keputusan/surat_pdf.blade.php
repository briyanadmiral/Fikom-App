{{-- resources/views/surat_keputusan/surat_pdf.blade.php --}}

@php
  // Alias agar kompatibel baik $sk maupun $keputusan
  $keputusan = $keputusan ?? $sk ?? null;
@endphp


@php
  /*
   |-------------------------------------------------------------
   | Guard visibilitas TTD/Cap & status draft
   | - showSigns: true hanya jika SK disetujui & sudah ditandatangani
   | - isDraft:   default mengikuti showSigns (jika belum boleh tampil tanda, anggap draft)
   |-------------------------------------------------------------
  */
  if (!isset($showSigns)) {
    $showSigns = isset($keputusan)
      ? (($keputusan->status_surat ?? null) === 'disetujui' && !empty($keputusan->signed_at ?? null))
      : false;
  }
  $isDraft = $isDraft ?? (!$showSigns);

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

  $context = 'pdf';
@endphp

{{-- Render isi dokumen via partial core (paritas 100% dengan tampilan approve/preview) --}}
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
])

{{-- Watermark bila DRAFT (belum disetujui) --}}
@if ($isDraft)
  <div style="
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-20deg);
    font-size: 70px;
    color: rgba(214, 55, 50, 0.22);
    font-weight: 800;
    z-index: 0;
    white-space: nowrap;
    pointer-events: none;
  ">
    BELUM DISETUJUI
  </div>
@endif
