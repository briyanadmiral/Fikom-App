{{-- resources/views/shared/_kop_surat.blade.php --}}
@php
  /** @var \App\Models\MasterKopSurat|null $kop */
  $context      = $context ?? 'web';      // 'web' | 'pdf'
  $kop          = $kop ?? \App\Models\MasterKopSurat::first();
  $mode         = $kop->mode ?? 'composed';
  $showDivider  = isset($showDivider) ? (bool)$showDivider : true;  // garis bawah kop
  $showLeftLogo = isset($showLeftLogo) ? (bool)$showLeftLogo : false; // opsional: logo kiri

  // Helper: path -> src (base64 utk pdf, asset utk web)
  $toSrc = function (?string $path) use ($context) {
    if (!$path) return null;
    if ($context === 'pdf') {
      try {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $bin  = $disk->exists($path)
                  ? $disk->get($path)
                  : (\Illuminate\Support\Facades\Storage::exists($path) ? \Illuminate\Support\Facades\Storage::get($path) : null);
        if (!$bin) return null;
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : (($ext === 'svg' || $ext === 'svgz') ? 'image/svg+xml' : 'image/jpeg');
        return 'data:'.$mime.';base64,'.base64_encode($bin);
      } catch (\Throwable $e) { return null; }
    }
    return asset('storage/'.$path);
  };

  $logoLeft  = !empty($kop?->logo_kiri_path)  ? $toSrc($kop->logo_kiri_path)  : null;
  $logoRight = !empty($kop?->logo_kanan_path) ? $toSrc($kop->logo_kanan_path) : null;
  $headerImg = !empty($kop?->header_path)     ? $toSrc($kop->header_path)     : null;
@endphp

@if($mode === 'image' && $headerImg)
  {{-- Mode gambar penuh (fallback) --}}
  <div class="kop-wrap">
    <img src="{{ $headerImg }}" style="width:100%; height:auto;">
  </div>
@else
  {{-- Mode composed: layout seperti Surat Tugas (teks kanan + logo kanan) --}}
  <div class="kop-wrap">
    <table style="width:100%; border-collapse:collapse;">
      <tr>
        {{-- (Opsional) Logo kiri jika ingin ditampilkan --}}
        @if($showLeftLogo && $logoLeft)
          <td style="width:95px; text-align:center; vertical-align:top; padding-right:12px;">
            <img src="{{ $logoLeft }}" alt="Logo" style="max-width:85px; max-height:85px;">
          </td>
        @endif

        {{-- Teks kop --}}
        <td class="kop-td-text" style="width:auto;">
          <div class="kop-text" style="line-height:1.25; text-align:right;">
            <div class="l1" style="font-weight:800; font-size:21px; color:#6A2C8E;">
              {{ strtoupper($kop->judul_atas ?? 'JUDUL INSTANSI') }}
            </div>
            <div class="l2" style="font-weight:800; font-size:15px; margin-top:-2px; color:#6A2C8E;">
              {{ strtoupper($kop->subjudul ?? 'SUBJUDUL') }}
            </div>
            <div class="addr" style="font-size:11px; margin-top:6px; color:#111;">
              {{ $kop->alamat ?? '' }}<br>
              Telp. {{ $kop->telepon ?? '' }}@if(!empty($kop?->fax)) , Fax. {{ $kop->fax }} @endif<br>
              email: {{ $kop->email ?? '' }} @if(!empty($kop?->website)) | {{ $kop->website }} @endif
            </div>
          </div>
        </td>

        {{-- Logo kanan dengan garis pemisah kiri (matching Surat Tugas) --}}
        <td class="kop-td-logo" style="width:130px; text-align:right; border-left:2px solid #000; padding-left:12px;">
          @if($logoRight)
            <img src="{{ $logoRight }}" alt="Logo" style="width:92px; height:auto;">
          @endif
        </td>
      </tr>
    </table>

    {{-- Garis bawah kop --}}
    @if($showDivider)
      <div style="border-bottom:2px solid #000; margin-top:8px;"></div>
    @endif
  </div>
@endif
