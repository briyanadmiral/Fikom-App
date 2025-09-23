{{-- resources/views/surat_tugas/approve-preview.blade.php --}}

{{-- === GUARD VISIBILITAS TTD/CAP (APPROVE PREVIEW) === --}}
@php
  // Jika controller belum mengirim $showSigns, fallback ke status surat.
  if (!isset($showSigns)) {
    $showSigns = isset($tugas)
      ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
      : false;
  }
@endphp

@php
  // File ini tetap sebagai wrapper untuk _core saat approval
  $context = 'web';

  // Ambil nilai dari array $preview yang dikirim Controller
  $ttdW       = $preview['ttd_w_mm'] ?? 42;
  $capW       = $preview['cap_w_mm'] ?? 35;
  $capOpacity = $preview['cap_opacity'] ?? 0.95;

  // HARDENING: jika belum boleh tampil, jangan oper gambar ke partial
  $ttdImageB64 = $showSigns ? ($preview['ttd_image_b64'] ?? null) : null;
  $capImageB64 = $showSigns ? ($preview['cap_image_b64'] ?? null) : null;
@endphp

{{-- 
  Kita tidak butuh div/container tambahan di sini.
  Cukup panggil _core agar pratinjau surat (elemen .sheet) menjadi elemen teratas.
--}}
@include('surat_tugas.partials._core', [
  'context'     => $context,
  'tugas'       => $tugas,
  'kop'         => $kop ?? null,

  // preferensi ukuran/opacity
  'ttdW'        => $ttdW,
  'capW'        => $capW,
  'capOpacity'  => $capOpacity,

  // aset gambar (akan null jika belum boleh tampil)
  'ttdImageB64' => $ttdImageB64,
  'capImageB64' => $capImageB64,

  // kunci: oper flag supaya _core ikut patuh
  'showSigns'   => $showSigns,
])
