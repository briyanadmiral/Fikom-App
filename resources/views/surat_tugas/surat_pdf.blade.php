{{-- resources/views/surat_tugas/surat_pdf.blade.php --}}

{{-- === GUARD VISIBILITAS TTD/CAP (PDF) === --}}
@php
  // Controller idealnya mengirim 'showSigns' & 'isDraft'.
  // Fallback aman bila belum dikirim:
  if (!isset($showSigns)) {
    $showSigns = isset($tugas)
      ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
      : false;
  }
  // Jika controller tidak set isDraft, anggap draft bila showSigns=false
  $isDraft = $isDraft ?? (!$showSigns);
@endphp

{{-- Surat Tugas - PDF (satu render, tanpa page-break tambahan) --}}
@php
  $context = 'pdf';

  // Ambil ukuran & opacity dari DB jika tidak di-override controller
  $ttdW       = $ttdW       ?? ($tugas->ttd_w_mm ?? null);
  $capW       = $capW       ?? ($tugas->cap_w_mm ?? null);
  $capOpacity = $capOpacity ?? ($tugas->cap_opacity ?? null);

  // HARDENING: Jangan oper gambar TTD/Cap ke partial jika belum boleh tampil
  $ttdImageB64_safe = $showSigns ? ($ttdImageB64 ?? null) : null;
  $capImageB64_safe = $showSigns ? ($capImageB64 ?? null) : null;
@endphp

@include('surat_tugas.partials._core', [
  'context'      => $context,
  'tugas'        => $tugas,
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