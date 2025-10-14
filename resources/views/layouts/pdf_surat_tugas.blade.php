<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 38mm 15mm 22mm 15mm; size: A4 portrait; }
    body { font-family: "Times New Roman", serif; font-size: 11pt; margin: 0; }

    .pdf-header {
      position: fixed; top: -33mm; left: 0; right: 0; height: 33mm;
      z-index: 1000; padding: 0 15mm;
    }
    .pdf-footer {
      position: fixed; bottom: -17mm; left: 0; right: 0; height: 17mm;
      border-top: 1px solid #333; padding: 3mm 15mm 0 15mm; z-index: 1000;
      font-size: 9pt; color: #333; display:flex; align-items:center; justify-content:space-between;
    }
    .page-number::after { content: "Halaman " counter(page); }
    .pdf-content { position: relative; z-index: 1; }

    .draft-watermark {
      position: fixed; top: 45%; left: 15%; transform: rotate(-20deg);
      font-size: 72pt; color: #000; opacity: .08; z-index: 9999; pointer-events: none;
    }
  </style>
</head>
<body>
  @if(!empty($isDraft))
    <div class="draft-watermark">{{ $draftLabel ?? 'DRAFT' }}</div>
  @endif

  <header class="pdf-header">
    @include('shared._kop_surat', ['kop' => $kop ?? null, 'context' => 'pdf', 'showDivider' => true])
  </header>

  <footer class="pdf-footer">
    <div>Dokumen ini dihasilkan secara elektronik.</div>
    <div class="page-number"></div>
  </footer>

  <main class="pdf-content">
    @yield('content')
  </main>
</body>
</html>
