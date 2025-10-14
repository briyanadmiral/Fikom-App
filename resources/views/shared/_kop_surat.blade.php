{{-- resources/views/shared/_kop_surat.blade.php --}}
@php
  $context     = $context ?? 'web';
  $isPdf       = $context === 'pdf';

  // Ambil kop (fallback ke DB)
  $kop         = $kop ?? \App\Models\MasterKopSurat::first();
  $modeType    = $kop->mode_type ?? 'custom';           // 'upload' | 'custom'
  $textAlign   = $kop->text_align ?? 'right';           // 'left' | 'center' | 'right'
  $showDivider = isset($showDivider) ? (bool)$showDivider : true;

  // ===== KONTROL STYLING (DARI DATABASE) =====
  $logoSizePx   = (int)($kop->logo_size ?? 100);        // px
  $fontTitlePx  = (int)($kop->font_size_title ?? 14);   // px
  $fontTextPx   = (int)($kop->font_size_text ?? 10);    // px
  $textColor    = $kop->text_color ?? '#000000';
  $headerPadPx  = (int)($kop->header_padding ?? 15);    // px (untuk WEB), nanti dikonversi di PDF
  $bgOpacity    = max(0, min(1, ($kop->background_opacity ?? 100) / 100));

  // ===== KONVERSI PX → MM UNTUK PDF =====
  // 1 px ≈ 0.264583 mm (96 dpi)
  $pxToMm = function(int|float $px){ return round($px * 0.264583, 2); };
  $clamp  = function(float $v, float $min, float $max){ return max($min, min($max, $v)); };

  // Header fixed kita di layout: tinggi pasti 33mm
  $H_MM = 33.0;

  // Untuk PDF: kita gunakan padding vertikal yang dikonversi dan di-clamp agar konten muat
  $vPadMm = $isPdf ? $clamp($pxToMm($headerPadPx), 2.0, 6.0) : null; // hanya PDF
  // Logo maximum height: sisa tinggi setelah padding atas-bawah + ruang divider (±2mm)
  $logoMaxMm = $isPdf ? $clamp($pxToMm($logoSizePx), 6.0, max(6.0, $H_MM - 2*$vPadMm - ($showDivider ? 2.0 : 0.0))) : null;

  // Sumber gambar (local → base64 utk PDF)
  $toSrc = function (?string $path) use ($context) {
    if (!$path) return null;
    if ($context === 'pdf') {
      try {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $bin  = $disk->exists($path) ? $disk->get($path) : null;
        if (!$bin) return null;
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        return 'data:'.$mime.';base64,'.base64_encode($bin);
      } catch (\Throwable $e) { return null; }
    }
    return asset('storage/'.$path);
  };

  $logoRight     = !empty($kop?->logo_kanan_path) ? $toSrc($kop->logo_kanan_path) : null;
  $backgroundImg = !empty($kop?->background_path) ? $toSrc($kop->background_path) : null;

  // Teks default
  $namaFakultas   = $kop->nama_fakultas   ?? 'FAKULTAS ILMU KOMPUTER';
  $alamatLengkap  = $kop->alamat_lengkap  ?? 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234';
  $teleponLengkap = $kop->telepon_lengkap ?? 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265';
  $emailWebsite   = $kop->email_website   ?? 'e-mail: unika@unika.ac.id  http://www.unika.ac.id/';
@endphp

{{-- ===================== MODE UPLOAD (gambar utuh) ===================== --}}
@if($modeType === 'upload' && $backgroundImg)
  @if($isPdf)
    {{-- PDF: batasi tinggi persis 33mm --}}
    <div style="position:relative; width:100%; height: {{ $H_MM }}mm; overflow:hidden;">
      <img src="{{ $backgroundImg }}"
           alt="Background Kop"
           style="width:100%; height: {{ $H_MM }}mm; object-fit: cover; display:block;">

      @if($showDivider)
        <div style="position:absolute; left:0; right:0; bottom:0; height:0; border-bottom:2px solid #000;"></div>
      @endif
    </div>
  @else
    {{-- WEB: biarkan fleksibel, gunakan padding dari DB --}}
    <div style="position: relative; width: 100%; overflow:hidden; padding: {{ $headerPadPx }}px;">
      <img src="{{ $backgroundImg }}" alt="Background Kop" style="width:100%; height:auto; display:block;">
    </div>
    @if($showDivider)
      <hr style="border-top:2px solid #000; margin:8px 0;">
    @endif
  @endif

{{-- ===================== MODE CUSTOM (teks + logo + bg) ===================== --}}
@else
  @if($isPdf)
    {{-- PDF: tinggi fixed 33mm, tanpa padding horizontal (layout header sudah 15mm) --}}
    <div style="position:relative; width:100%; height: {{ $H_MM }}mm; overflow:hidden;">
      {{-- Background --}}
      @if($backgroundImg)
        <div style="position:absolute; inset:0; z-index:0;">
          <img src="{{ $backgroundImg }}"
               alt="Background Header"
               style="width:100%; height:100%; object-fit:cover; display:block; opacity: {{ $bgOpacity }};">
        </div>
      @endif

      {{-- Konten: tata letak berdasarkan textAlign --}}
      @if($textAlign === 'center')
        <div style="position:absolute; left:0; right:0; top: {{ $vPadMm }}mm; bottom: {{ $vPadMm }}mm; z-index:1; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;">
          @if($logoRight)
            <div style="margin-bottom:1.5mm;">
              <img src="{{ $logoRight }}" alt="Logo"
                   style="max-height: {{ $logoMaxMm }}mm; height:auto; width:auto; display:inline-block;">
            </div>
          @endif
          <div style="font-family: Arial, sans-serif; line-height:1.25;">
            <div style="font-weight:700; font-size: {{ $fontTitlePx }}px; color: {{ $textColor }};">{{ $namaFakultas }}</div>
            <div style="font-size: {{ $fontTextPx }}px; color: {{ $textColor }};">{{ $alamatLengkap }}</div>
            <div style="font-size: {{ $fontTextPx }}px; color: {{ $textColor }};">{{ $teleponLengkap }}</div>
            <div style="font-size: {{ $fontTextPx }}px; color: {{ $textColor }};">{{ $emailWebsite }}</div>
          </div>
        </div>

      @elseif($textAlign === 'left')
        {{-- Pakai tabel agar kompatibel dengan DomPDF --}}
        <table style="position:absolute; left:0; right:0; top: {{ $vPadMm }}mm; bottom: {{ $vPadMm }}mm; z-index:1; width:100%; border-collapse:collapse;">
          <tr>
            @if($logoRight)
              <td style="width: {{ max(10, $logoMaxMm) }}mm; vertical-align:middle; padding-right: 4mm;">
                <img src="{{ $logoRight }}" alt="Logo"
                     style="max-height: {{ $logoMaxMm }}mm; height:auto; width:auto; display:block;">
              </td>
            @endif
            <td style="vertical-align:middle;">
              <div style="font-family: Arial, sans-serif; line-height:1.25; color: {{ $textColor }};">
                <div style="font-weight:700; font-size: {{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                <div style="font-size: {{ $fontTextPx }}px;">{{ $alamatLengkap }}</div>
                <div style="font-size: {{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                <div style="font-size: {{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
              </div>
            </td>
          </tr>
        </table>

      @else
        {{-- RIGHT (default) --}}
        <table style="position:absolute; left:0; right:0; top: {{ $vPadMm }}mm; bottom: {{ $vPadMm }}mm; z-index:1; width:100%; border-collapse:collapse;">
          <tr>
            <td style="text-align:right; vertical-align:middle; padding-right: 4mm;">
              <div style="font-family: Arial, sans-serif; line-height:1.25; color: {{ $textColor }}; text-align:right;">
                <div style="font-weight:700; font-size: {{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                <div style="font-size: {{ $fontTextPx }}px;">{{ $alamatLengkap }}</div>
                <div style="font-size: {{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                <div style="font-size: {{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
              </div>
            </td>
            <td style="width: {{ max(12, $logoMaxMm + 3) }}mm; text-align:right; vertical-align:middle; border-left: 2px solid #000; padding-left: 4mm;">
              @if($logoRight)
                <img src="{{ $logoRight }}" alt="Logo"
                     style="max-height: {{ $logoMaxMm }}mm; height:auto; width:auto; display:block; margin-left:auto;">
              @endif
            </td>
          </tr>
        </table>
      @endif

      {{-- Divider absolute di bawah agar tidak menambah tinggi header --}}
      @if($showDivider)
        <div style="position:absolute; left:0; right:0; bottom:0; height:0; border-bottom:2px solid #000; z-index:2;"></div>
      @endif
    </div>

  @else
    {{-- ===================== WEB PREVIEW ===================== --}}
    <div class="kop-wrap-custom" style="position: relative; min-height: 120px; overflow: hidden;">
      {{-- Background --}}
      @if($backgroundImg)
        <div style="position: absolute; inset: 0; z-index: 0;">
          <img src="{{ $backgroundImg }}"
               style="width: 100%; height: 100%; object-fit: cover; display: block; opacity: {{ $bgOpacity }};"
               alt="Background Header">
        </div>
      @endif

      {{-- Konten --}}
      <div style="position: relative; z-index: 1; padding: {{ $headerPadPx }}px;">
        @if($textAlign === 'center')
          <div style="text-align:center;">
            @if($logoRight)
              <div style="margin-bottom:10px;">
                <img src="{{ $logoRight }}" alt="Logo"
                     style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
              </div>
            @endif
            <div style="font-family: Arial, sans-serif; line-height:1.3; color: {{ $textColor }};">
              <div style="font-weight:700; font-size:{{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
              <div style="font-size:{{ $fontTextPx }}px; margin-top:4px;">{{ $alamatLengkap }}</div>
              <div style="font-size:{{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
              <div style="font-size:{{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
            </div>
          </div>

        @elseif($textAlign === 'left')
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              @if($logoRight)
                <td style="width:{{ $logoSizePx + 20 }}px; text-align:left; vertical-align:middle; padding-right:15px;">
                  <img src="{{ $logoRight }}" alt="Logo"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                </td>
              @endif
              <td style="text-align:left; vertical-align:middle;">
                <div style="font-family: Arial, sans-serif; line-height:1.3; color: {{ $textColor }};">
                  <div style="font-weight:700; font-size:{{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                  <div style="font-size:{{ $fontTextPx }}px; margin-top:4px;">{{ $alamatLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
                </div>
              </td>
            </tr>
          </table>

        @else
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              <td style="text-align:right; vertical-align:middle; padding-right:15px;">
                <div style="font-family: Arial, sans-serif; line-height:1.3; color: {{ $textColor }}; text-align:right;">
                  <div style="font-weight:700; font-size:{{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $alamatLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
                </div>
              </td>
              <td style="width:{{ $logoSizePx + 30 }}px; text-align:right; vertical-align:middle; border-left:2px solid #000; padding-left:15px;">
                @if($logoRight)
                  <img src="{{ $logoRight }}" alt="Logo"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                @endif
              </td>
            </tr>
          </table>
        @endif
      </div>

      {{-- Divider --}}
      @if($showDivider)
        <div style="border-bottom:2px solid #000; margin-top:0; position: relative; z-index: 1;"></div>
      @endif
    </div>
  @endif
@endif
