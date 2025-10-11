{{-- === GUARD VISIBILITAS TTD/CAP (PREVIEW) === --}}
@php
    if (!isset($showSigns)) {
        $showSigns = isset($tugas)
            ? ($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null)
            : false;
    }
@endphp


{{-- Preview Surat Tugas (hasil final, paritas dengan PDF) --}}
@php
    $context = 'web';

    // Ambil nilai yang tersimpan di database jika ada
    $ttdW = $ttdW ?? ($tugas->ttd_w_mm ?? null);
    $capW = $capW ?? ($tugas->cap_w_mm ?? null);
    $capOpacity = $capOpacity ?? ($tugas->cap_opacity ?? null);

    // Jika TTD & Cap sudah ada, kita perlu base64-nya untuk ditampilkan
    // (Logika ini mungkin perlu Anda tambahkan di Controller yang memanggil view ini)
    // Contoh:
    // $ttdImageB64 = ... helper to get base64 from stored ttd path ...
    // $capImageB64 = ... helper to get base64 from stored cap path ...

@endphp

<div class="container-fluid py-3">
    @include('surat_tugas.partials._core', [
        'context' => $context,
        'tugas' => $tugas,
        'kop' => $kop ?? null,
        'penerimaList' => $penerimaList ?? null,
        'ttdW' => $ttdW,
        'capW' => $capW,
        'capOpacity' => $capOpacity,
        'ttdImageB64' => $ttdImageB64 ?? null, // Pastikan variabel ini dikirim dari controller
        'capImageB64' => $capImageB64 ?? null, // Pastikan variabel ini dikirim dari controller
    ])
</div>
