@php
  $kop = $kop ?? \App\Models\MasterKopSurat::first();

  // Hitung margin top dinamis berdasarkan padding header
  // Default values untuk SK mungkin butuh lebih besar dari ST karena judul SK panjang?
  // Tapi user minta "layaknya menjadi header kayak surat tugas". Kita samakan base-nya.
  $defaultMarginMm = 45; // Base margin sedikit lebih besar dari ST (38) buat jaga-jaga
  $defaultHeaderH  = 40; // Header height

  // Extra padding dari user (px -> mm)
  $userPaddingPx = $kop->header_padding ?? 15;
  $userPaddingMm = round($userPaddingPx * 0.264583, 2);

  $basePaddingMm = round(15 * 0.264583, 2);
  $extraMm = max(0, $userPaddingMm - $basePaddingMm);

  $newMarginTop   = $defaultMarginMm + $extraMm;
  $newHeaderHeight= $defaultHeaderH + $extraMm;

  // Header full mentok atas page
  $newHeaderTop   = -1 * $newMarginTop;
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    /* Ruang untuk header/footer di setiap halaman */
    @page { margin: {{ $newMarginTop }}mm 15mm 22mm 15mm; size: A4 portrait; }

    body { font-family: "Times New Roman", serif; font-size: 11pt; margin: 0; }

    /* Header fixed: Full width mentok kiri kanan page */
    .pdf-header {
      position: fixed;
      top: {{ $newHeaderTop }}mm;
      left: -15mm; right: -15mm; /* Melebar ke margin kiri kanan */
      width: auto;
      height: {{ $newHeaderHeight }}mm;
      z-index: 1000;
      padding: 0; /* Padding dikontrol internal kop */
    }

    /* Footer fixed */
    .pdf-footer {
      position: fixed; bottom: -17mm; left: 0; right: 0; height: 17mm;
      border-top: 1px solid #333; padding: 3mm 15mm 0 15mm; z-index: 1000;
      font-size: 9pt; color: #333;
      display: flex; align-items: center; justify-content: space-between;
    }

    .page-number::after { content: "Halaman " counter(page); }

    .pdf-content { position: relative; z-index: 1; }

    .draft-watermark {
      position: fixed; top: 45%; left: 15%; transform: rotate(-20deg);
      font-size: 72pt; color: #000; opacity: .08; z-index: 9999;
      pointer-events: none;
    }
  </style>
</head>
<body>
  @if(!empty($isDraft))
    <div class="draft-watermark">DRAFT</div>
  @endif

  <header class="pdf-header">
    {{-- Pass context pdf agar kop menyesuaikan ukuran --}}
    @include('shared._kop_surat', ['kop' => $kop ?? null, 'context' => 'pdf'])
  </header>

  <footer class="pdf-footer">
    <div class="disclaimer">
      Dokumen ini dihasilkan secara elektronik. Tidak memerlukan tanda tangan basah.
    </div>
    <div class="page-number"></div>
  </footer>

  <main class="pdf-content">
    @yield('content')
  </main>
</body>
</html>
