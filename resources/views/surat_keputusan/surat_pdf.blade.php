<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keputusan - PDF</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    {{-- DomPDF cenderung stabil dengan font serif standar --}}
    <style>
        html, body { font-family: "Times New Roman", Times, serif; color:#000; }
        /* Margin diatur oleh partial _core via @page; biarkan kosong di sini */

        /* Watermark saat belum final */
        .watermark{
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-20deg);
            opacity: .08; color:#444;
            font-size: 120px; font-weight: 900; letter-spacing: 6px;
            z-index: 0;
            white-space: nowrap;
        }
        /* Bungkus konten agar selalu di atas watermark */
        .doc { position: relative; z-index: 1; }

        /* Footer fixed agar muncul di setiap halaman */
        .pdf-footer{
            position: fixed;
            left: 0; right: 0; bottom: 0;
            text-align: center;
        }
        .pdf-footer img{ width: 100%; height: auto; }
    </style>
</head>
<body>
@php
    // === Siapkan footer dari pengaturan (gambar -> base64 untuk DomPDF) ===
    $footerImgB64 = null;
    if (!empty($kop?->footer_path)) {
        try {
            $bin = \Illuminate\Support\Facades\Storage::disk('public')->exists($kop->footer_path)
                ? \Illuminate\Support\Facades\Storage::disk('public')->get($kop->footer_path)
                : (\Illuminate\Support\Facades\Storage::exists($kop->footer_path)
                    ? \Illuminate\Support\Facades\Storage::get($kop->footer_path)
                    : null);
            if ($bin) {
                $ext  = strtolower(pathinfo($kop->footer_path, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : (($ext === 'svg' || $ext === 'svgz') ? 'image/svg+xml' : 'image/jpeg');
                $footerImgB64 = 'data:'.$mime.';base64,'.base64_encode($bin);
            }
        } catch (\Throwable $e) { /* ignore */ }
    }

    // Tentukan apakah tanda tangan/cap tampil
    $__showSigns = $showSigns ?? false;
@endphp

{{-- Watermark (contoh: DRAFT). Muncul hanya jika belum final --}}
@if(!$__showSigns)
    <div class="watermark">D R A F T</div>
@endif

<div class="doc">
    @include('surat_keputusan.partials._core', [
        'keputusan'    => $keputusan,
        'kop'          => $kop ?? null,
        'context'      => 'pdf',
        'showSigns'    => $__showSigns,         {{-- tampilkan TTD/Cap hanya bila final --}}
        'ttdImageB64'  => $ttdImageB64 ?? null,
        'capImageB64'  => $capImageB64 ?? null,
        'ttdW'         => $ttdW ?? null,        {{-- mm; jika null pakai default helper --}}
        'capW'         => $capW ?? null,        {{-- mm --}}
        'capOpacity'   => $capOpacity ?? null,  {{-- 0..1 --}}
    ])
</div>

{{-- Footer global (gambar atau HTML), diulang tiap halaman --}}
@if($footerImgB64)
    <div class="pdf-footer">
        <img src="{{ $footerImgB64 }}" alt="Footer">
    </div>
@elseif(!empty($kop?->footer_html))
    <div class="pdf-footer">
        {!! $kop->footer_html !!}
    </div>
@endif
</body>
</html>
