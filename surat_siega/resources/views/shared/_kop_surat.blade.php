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

  // Untuk PDF: kita gunakan padding vertikal yang dikonversi (max 20mm agar tidak hilang)
  $vPadMm = $isPdf ? $clamp($pxToMm($headerPadPx), 0, 60.0) : null; 
  // Logo maximum height (adjust based on padding)
  $logoMaxMm = $isPdf ? $clamp($pxToMm($logoSizePx), 6.0, max(6.0, $H_MM - $vPadMm - 2)) : null;

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
    {{-- ===================== WEB PREVIEW ===================== --}}
    <div class="kop-wrap-custom" style="position: relative; min-height: 120px; overflow: hidden; background-color: transparent;">
      {{-- Background --}}
      @if($backgroundImg)
        <div style="position: absolute; top: 0; left: 0; width: 100%; z-index: 0;">
          <img src="{{ $backgroundImg }}"
               style="width: 100%; height: auto; display: block; opacity: {{ $bgOpacity }};"
               alt="Background Header">
        </div>
      @endif

      {{-- Konten --}}
      {{-- Fix: Padding hanya untuk TOP agar background tidak molor ke bawah/samping --}}
      <div style="position: relative; z-index: 1; padding-top: {{ $headerPadPx }}px; padding-bottom: 15px; padding-left: {{ $isPdf ? '15mm' : '20px' }}; padding-right: {{ $isPdf ? '15mm' : '20px' }}; background-color: transparent;">
        @if($textAlign === 'center')
          <table style="width:100%; border-collapse:collapse; background-color: transparent;">
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
          <table style="width:100%; border-collapse:collapse; background-color: transparent;">
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
          <table style="width:100%; border-collapse:collapse; background-color: transparent;">
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
              <td style="width:{{ $logoSizePx + 20 }}px; text-align:right; vertical-align:middle; border-left:2px solid {{ $textColor }}; padding-left:15px;">
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
