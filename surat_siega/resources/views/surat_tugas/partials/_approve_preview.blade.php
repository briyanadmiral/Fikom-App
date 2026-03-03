{{-- resources/views/surat_tugas/approve-preview.blade.php --}}

@php
    // === Halaman ini khusus APPROVER ===
    // Paksa TTD & Cap SELALU tampak di halaman approve (meski status masih pending)
    $showSigns = true;

    // Konteks web (pakai CSS web dari _core)
    $context = 'web';

    // Nilai preferensi ukuran & opacity yang diterima dari controller (approveForm/approvePreview)
    $ttdW = isset($preview['ttd_w_mm']) ? (int) $preview['ttd_w_mm'] : 42;
    $capW = isset($preview['cap_w_mm']) ? (int) $preview['cap_w_mm'] : 35;
    $capOpacity = isset($preview['cap_opacity']) ? (float) $preview['cap_opacity'] : 0.95;

    // Aset gambar base64 (controller sudah menyiapkan via getSigningAssets)
    $ttdImageB64 = $preview['ttd_image_b64'] ?? null;
    $capImageB64 = $preview['cap_image_b64'] ?? null;
@endphp

{{-- 
  Tidak perlu wrapper tambahan.
  Langsung panggil _core supaya seluruh konten diganti saat live-preview.
--}}
@include('surat_tugas.partials._core', [
    'context' => $context,
    'tugas' => $tugas,
    'kop' => $kop ?? null,
    
    // allow resize in approval
    'allowResize' => true,

    // preferensi ukuran/opacity
    'ttdW' => $ttdW,
    'capW' => $capW,
    'capOpacity' => $capOpacity,
    // Offsets
    'ttdX' => isset($preview['ttd_x_mm']) ? (int)$preview['ttd_x_mm'] : null,
    'ttdY' => isset($preview['ttd_y_mm']) ? (int)$preview['ttd_y_mm'] : null,
    'capX' => isset($preview['cap_x_mm']) ? (int)$preview['cap_x_mm'] : null,
    'capY' => isset($preview['cap_y_mm']) ? (int)$preview['cap_y_mm'] : null,

    // aset gambar
    'ttdImageB64' => $ttdImageB64,
    'capImageB64' => $capImageB64,

    // kunci: TTD/Cap HARUS tampak di halaman approve
    'showSigns' => $showSigns,
])
