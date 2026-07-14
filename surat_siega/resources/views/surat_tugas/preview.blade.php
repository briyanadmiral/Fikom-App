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
  @if ($tugas && $tugas->signed_pdf_path && Storage::disk('local')->exists($tugas->signed_pdf_path))
    @php
      $friendlyName = 'SuratTugas_' . (preg_replace('/[^a-zA-Z0-9_-]/', '_', $tugas->nomor) ?? 'TanpaNomor') . '.pdf';
      $downloadUrl = route('surat_tugas.downloadPdf', [$tugas->id, $friendlyName]);
    @endphp
    <div style="height: 750px;">
        <iframe src="{{ $downloadUrl }}?t={{ time() }}" class="w-100 h-100" style="border: none;"></iframe>
    </div>
  @else
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
      'showNamaPenandatangan' => $showNamaPenandatangan ?? ($preview['show_nama'] ?? true),
      'showKopInContent' => true,   // kop tampil di konten saat preview web
    ])
  @endif
</div>
