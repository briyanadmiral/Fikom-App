{{-- resources/views/surat_keputusan/preview.blade.php --}}

@php
  // Alias agar kompatibel baik $sk maupun $keputusan
  $keputusan = $keputusan ?? $sk ?? null;
@endphp

@php
  // Guard visibilitas (boleh override via $showSigns=true dari controller)
  if (!isset($showSigns)) {
      $showSigns = isset($keputusan)
        ? (($keputusan->status_surat ?? null) === 'disetujui' && !empty($keputusan->signed_at ?? null))
        : false;
  }

  // Konteks selalu web untuk pratinjau HTML
  $context = 'web';

  // Fallback ukuran dari DB jika tidak dikirim
  $ttdW       = $ttdW       ?? ($keputusan->ttd_config['w_mm']   ?? $keputusan->ttd_w_mm   ?? null);
  $capW       = $capW       ?? ($keputusan->cap_config['w_mm']   ?? $keputusan->cap_w_mm   ?? null);
  $capOpacity = $capOpacity ?? ($keputusan->cap_config['opacity']?? $keputusan->cap_opacity?? null);

  // Aset base64 bisa dipasok dari controller agar cepat (tanpa I/O di view)
  $ttdImageB64 = $ttdImageB64 ?? null;
  $capImageB64 = $capImageB64 ?? null;
@endphp

<div class="container-fluid py-3">
  @include('surat_keputusan.partials._core', [
    'context'      => $context,
    'keputusan'    => $keputusan,
    'kop'          => $kop ?? null,
    // preferensi ukuran/opacity
    'ttdW'         => $ttdW,
    'capW'         => $capW,
    'capOpacity'   => $capOpacity,
    // aset gambar
    'ttdImageB64'  => $ttdImageB64,
    'capImageB64'  => $capImageB64,
    // kontrol visibilitas
    'showSigns'    => $showSigns,
  ])
</div>
