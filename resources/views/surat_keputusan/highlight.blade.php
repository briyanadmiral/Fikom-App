{{-- resources/views/surat_keputusan/highlight.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Preview Surat Keputusan</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <style>
    body { background:#f2f3f7; margin:0; padding:24px 0; }
    /* kanvas A4 untuk preview web */
    .sheet {
      width:210mm; min-height:297mm; margin:0 auto; background:#fff;
      box-shadow:0 10px 30px rgba(0,0,0,.12);
    }
    @media (max-width: 920px) { .sheet { width:100%; box-shadow:none; } }
    @media print { body{ background:#fff; padding:0; } .sheet{ box-shadow:none; } }
  </style>
</head>
<body>
  <div class="sheet">
    @include('surat_keputusan.partials._core', [
      'keputusan'    => $keputusan,
      'kop'          => $kop ?? null,          {{-- dikirim dari controller --}}
      'context'      => 'web',
      'showSigns'    => $showSigns ?? null,    {{-- guard di partial sudah handle default --}}
      'ttdImageB64'  => $ttdImageB64 ?? null,  {{-- optional: diisi controller kalau mau tampil tanda tangan --}}
      'capImageB64'  => $capImageB64 ?? null,
      // opsional:
      'renderHeader' => true,
      'renderFooter' => !empty($kop?->footer_path),
    ])
  </div>
</body>
</html>
