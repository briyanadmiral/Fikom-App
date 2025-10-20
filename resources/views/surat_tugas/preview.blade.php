{{-- resources/views/surat_tugas/preview.blade.php --}}
@php
    // visibilitas ttd/cap (aman)
    $showSigns = $showSigns ?? (
        isset($tugas)
            ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
            : false
    );

    // konteks web (pakai .sheet & kop di konten)
    $context     = 'web';
    $ttdW        = $ttdW        ?? ($tugas->ttd_w_mm    ?? null);
    $capW        = $capW        ?? ($tugas->cap_w_mm    ?? null);
    $capOpacity  = $capOpacity  ?? ($tugas->cap_opacity ?? null);

    // aset base64 hanya jika boleh tampil
    $ttdB64 = $showSigns ? ($ttdImageB64 ?? null) : null;
    $capB64 = $showSigns ? ($capImageB64 ?? null) : null;
@endphp

<div class="container-fluid py-3">
  @include('surat_tugas.partials._core', [
    'context'          => $context,
    'tugas'            => $tugas,
    'kop'              => $kop ?? null,
    'penerimaList'     => $penerimaList ?? null,
    'ttdW'             => $ttdW,
    'capW'             => $capW,
    'capOpacity'       => $capOpacity,
    'ttdImageB64'      => $ttdB64,
    'capImageB64'      => $capB64,
    'showSigns'        => $showSigns,
    'showKopInContent' => true,   // kop tampil di konten saat preview web
  ])
</div>
