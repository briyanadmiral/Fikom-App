{{-- resources/views/surat_keputusan/partials/approve-preview.blade.php --}}
@php
    // Ambil konfigurasi ttd/cap dari paket $preview (hasil buildPreview di controller)
    $ttdCfg = $preview['ttd'] ?? [];
    $capCfg = $preview['cap'] ?? [];
@endphp

<div class="sk-preview-wrapper">
    @include('surat_keputusan.partials._core', [
        'keputusan' => $keputusan,
        'kop'       => $kop ?? null,

        // Tampilkan tanda (TTD & Cap) saat review
        'showSigns' => $showSigns ?? true,

        // Konfigurasi yang dipakai _core untuk merender layer tanda
        'ttd' => $ttdCfg,
        'cap' => $capCfg,
    ])
</div>
