@php
  $context     = $context ?? 'web';
  $isPdf       = $context === 'pdf';

  // Ambil kop (fallback ke DB singleton)
  $kop         = $kop ?? \App\Models\MasterKopSurat::first();
  $modeType    = $kop->mode_type ?? 'custom';           // 'upload' | 'custom'
  $textAlign   = $kop->text_align ?? 'right';           // 'left' | 'center' | 'right'

  // ===== KONTROL STYLING (BASIC) =====
  $logoSizePx   = (int)($kop->logo_size ?? 100);        // px
  $fontTitlePx  = (int)($kop->font_size_title ?? 14);   // px
  $fontTextPx   = (int)($kop->font_size_text ?? 10);    // px
  $textColor    = $kop->text_color ?? '#000000';
  $headerPadPx  = (int)($kop->header_padding ?? 15);    // px (untuk WEB)
  $bgOpacity    = max(0, min(1, ($kop->background_opacity ?? 100) / 100));
  
  // Legacy: Default Font Arial
  $fontFamily   = 'Arial, sans-serif'; 

  // ===== LOGO (DUAL SUPPORT) =====
  $showLogoKanan = (bool)($kop->tampilkan_logo_kanan ?? true);
  $showLogoKiri  = (bool)($kop->tampilkan_logo_kiri ?? false);

  // ===== KONVERSI PX → MM UNTUK PDF =====
  // 1 px ≈ 0.264583 mm (96 dpi)
  $pxToMm = function(int|float $px){ return round($px * 0.264583, 2); };
  $clamp  = function(float $v, float $min, float $max){ return max($min, min($max, $v)); };

  // Header fixed layout: tinggi pasti 33mm (Legacy standard)
  $H_MM = 33.0;

  // Untuk PDF: kita gunakan padding vertikal yang dikonversi dan di-clamp agar konten muat
  $vPadMm = $isPdf ? $clamp($pxToMm($headerPadPx), 2.0, 6.0) : null; 
  // Logo maximum height
  $logoMaxMm = $isPdf ? $clamp($pxToMm($logoSizePx), 6.0, max(6.0, $H_MM - 2*$vPadMm)) : null;

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

  $logoRight     = $showLogoKanan && !empty($kop->logo_kanan_path) ? $toSrc($kop->logo_kanan_path) : null;
  $logoLeft      = $showLogoKiri && !empty($kop->logo_kiri_path) ? $toSrc($kop->logo_kiri_path) : null;
  $backgroundImg = !empty($kop->background_path) ? $toSrc($kop->background_path) : null;

  // Teks utama
  $namaFakultas   = $kop->nama_fakultas ?? 'FAKULTAS ILMU KOMPUTER';
  $alamatLengkap  = $kop->alamat_lengkap ?? 'Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234';
  $teleponLengkap = $kop->telepon_lengkap ?? 'Telp. (024) 8441555, 8505003 (hunting) Fax. (024) 8415429 – 8445265';
  $emailWebsite   = $kop->email_website ?? 'e-mail: unika@unika.ac.id  http://www.unika.ac.id/';
@endphp

{{-- ===================== MODE UPLOAD (gambar utuh) ===================== --}}
@if($modeType === 'upload' && $backgroundImg)
  @if($isPdf)
    {{-- PDF: batasi tinggi persis 33mm --}}
    <div style="position:relative; width:100%; height: {{ $H_MM }}mm; overflow:hidden;">
      <img src="{{ $backgroundImg }}"
           alt="Background Kop"
           style="width:100%; height: {{ $H_MM }}mm; object-fit: cover; display:block;">
    </div>
  @else
    {{-- WEB --}}
    <div style="position: relative; width: 100%; overflow:hidden; padding: {{ $headerPadPx }}px;">
      <img src="{{ $backgroundImg }}" alt="Background Kop" style="width:100%; height:auto; display:block;">
    </div>
  @endif

{{-- ===================== MODE CUSTOM (Legacy) ===================== --}}
@else
  @if($isPdf)
    {{-- PDF: tinggi fixed 33mm --}}
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
          <div style="font-family: {{ $fontFamily }}; line-height:1.25;">
            <div style="font-weight:700; font-size: {{ $fontTitlePx }}px; color: {{ $textColor }};">{{ $namaFakultas }}</div>
            <div style="font-size: {{ $fontTextPx }}px; color: {{ $textColor }};">{{ $alamatLengkap }}</div>
            <div style="font-size: {{ $fontTextPx }}px; color: {{ $textColor }};">{{ $teleponLengkap }}</div>
            <div style="font-size: {{ $fontTextPx }}px; color: {{ $textColor }};">{{ $emailWebsite }}</div>
          </div>
        </div>

      @elseif($textAlign === 'left')
        <table style="position:absolute; left:0; right:0; top: {{ $vPadMm }}mm; bottom: {{ $vPadMm }}mm; z-index:1; width:100%; border-collapse:collapse;">
          <tr>
            @if($logoRight)
              <td style="width: {{ max(10, $logoMaxMm) }}mm; vertical-align:middle; padding-right: 4mm;">
                <img src="{{ $logoRight }}" alt="Logo"
                     style="max-height: {{ $logoMaxMm }}mm; height:auto; width:auto; display:block;">
              </td>
            @endif
            <td style="vertical-align:middle;">
              <div style="font-family: {{ $fontFamily }}; line-height:1.25; color: {{ $textColor }};">
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
              <div style="font-family: {{ $fontFamily }}; line-height:1.25; color: {{ $textColor }}; text-align:right;">
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
      
      {{-- No Divider in Legacy --}}
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
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              @if($logoLeft)
                <td style="width:{{ $logoSizePx + 20 }}px; text-align:left; vertical-align:middle;">
                  <img src="{{ $logoLeft }}" alt="Logo Kiri"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                </td>
              @endif
              <td style="text-align:center; vertical-align:middle;">
                <div style="font-family: {{ $fontFamily }}; line-height:1.3; color: {{ $textColor }};">
                  <div style="font-weight:700; font-size:{{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                  <div style="font-size:{{ $fontTextPx }}px; margin-top:4px;">{{ $alamatLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
                </div>
              </td>
              @if($logoRight)
                <td style="width:{{ $logoSizePx + 20 }}px; text-align:right; vertical-align:middle;">
                  <img src="{{ $logoRight }}" alt="Logo Kanan"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                </td>
              @endif
            </tr>
          </table>

        @elseif($textAlign === 'left')
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              @if($logoLeft)
                <td style="width:{{ $logoSizePx + 20 }}px; text-align:left; vertical-align:middle; padding-right:10px;">
                  <img src="{{ $logoLeft }}" alt="Logo Kiri"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                </td>
              @endif
              <td style="text-align:left; vertical-align:middle;">
                <div style="font-family: {{ $fontFamily }}; line-height:1.3; color: {{ $textColor }};">
                  <div style="font-weight:700; font-size:{{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                  <div style="font-size:{{ $fontTextPx }}px; margin-top:4px;">{{ $alamatLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
                </div>
              </td>
              @if($logoRight)
                <td style="width:{{ $logoSizePx + 20 }}px; text-align:right; vertical-align:middle; padding-left:10px;">
                  <img src="{{ $logoRight }}" alt="Logo Kanan"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                </td>
              @endif
            </tr>
          </table>

        @else
          {{-- RIGHT (default for web) --}}
          <table style="width:100%; border-collapse:collapse;">
            <tr>
              @if($logoLeft)
                <td style="width:{{ $logoSizePx + 20 }}px; text-align:left; vertical-align:middle; padding-right:10px;">
                  <img src="{{ $logoLeft }}" alt="Logo Kiri"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto;">
                </td>
              @endif
              <td style="text-align:right; vertical-align:middle; padding-right:15px;">
                <div style="font-family: {{ $fontFamily }}; line-height:1.3; color: {{ $textColor }};">
                  <div style="font-weight:700; font-size:{{ $fontTitlePx }}px;">{{ $namaFakultas }}</div>
                  <div style="font-size:{{ $fontTextPx }}px; margin-top:4px;">{{ $alamatLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $teleponLengkap }}</div>
                  <div style="font-size:{{ $fontTextPx }}px;">{{ $emailWebsite }}</div>
                </div>
              </td>
              <td style="width:{{ $logoSizePx + 20 }}px; text-align:right; vertical-align:middle; border-left:2px solid #000; padding-left:15px;">
                @if($logoRight)
                  <img src="{{ $logoRight }}" alt="Logo Kanan"
                       style="max-width:{{ $logoSizePx }}px; max-height:{{ (int)($logoSizePx * 0.8) }}px; width:auto; height:auto; margin-left:auto; display:block;">
                @endif
              </td>
            </tr>
          </table>
        @endif
        
        {{-- No Divider --}}
      </div>
    </div>
  @endif
@endif
