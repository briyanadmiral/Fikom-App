{{-- resources/views/surat_keputusan/partials/_core.blade.php --}}
@php
  // context: 'web' | 'pdf'
  $context = $context ?? 'web';

  // Flag render kop di area konten (untuk web preview)
  $showKopInContent = $showKopInContent ?? true; // default: true (backward compatibility)

  // Konsolidasi objek
  $sk = $keputusan ?? $sk ?? null;

  // Data list
  $menimbang = is_array($sk?->menimbang) ? $sk->menimbang : (json_decode($sk->menimbang ?? '[]', true) ?: []);
  $mengingat = is_array($sk?->mengingat) ? $sk->mengingat : (json_decode($sk->mengingat ?? '[]', true) ?: []);

  // 'menetapkan' sebagai array terstruktur
  $menetapkanItems = is_array($sk?->menetapkan) ? $sk->menetapkan : (json_decode($sk?->menetapkan ?? '[]', true) ?: []);
  // Pastikan indeks berurutan agar aman dipakai $index
  $menetapkanItems = array_values($menetapkanItems);
  $ordinalWords = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];

  // Aset tanda tangan & cap
  $ttdImageB64 = $ttdImageB64 ?? null;
  $capImageB64 = $capImageB64 ?? null;

  // Tampilkan TTD/Cap (override via $showSigns)
  $showSigns = $showSigns ?? ( 
    in_array($sk->status_surat ?? null, ['disetujui', 'terbit', 'arsip'], true)
    && !empty($sk->signed_at ?? null) 
);

  // Ukuran fallback
  $ttdW = isset($ttdW) ? (int)$ttdW : (int)($sk->ttd_config['w_mm'] ?? 42);
  $capW = isset($capW) ? (int)$capW : (int)($sk->cap_config['w_mm'] ?? 35);
  $capOpacity = isset($capOpacity) ? (float)$capOpacity : (float)($sk->cap_config['opacity'] ?? 0.95);

  // ✅ OFFSETS (X/Y)
  $ttdX = isset($ttdX) ? (int)$ttdX : (isset($sk->ttd_config['x']) ? (int)$sk->ttd_config['x'] : 0);
  $ttdY = isset($ttdY) ? (int)$ttdY : (isset($sk->ttd_config['y']) ? (int)$sk->ttd_config['y'] : 0);
  $capX = isset($capX) ? (int)$capX : (isset($sk->cap_config['x']) ? (int)$sk->cap_config['x'] : 0);
  $capY = isset($capY) ? (int)$capY : (isset($sk->cap_config['y']) ? (int)$sk->cap_config['y'] : 0);

  // Tembusan
  $tembusanRaw = (string)($sk->tembusan ?? '');
  $tembusanItems = collect(preg_split('/[\r\n,]+/', $tembusanRaw))
    ->map(fn($v) => trim($v))->filter()->values()->all();

  // Penandatangan & meta
  $pen = $sk->penandatanganUser ?? null;
  $kotaPenetapan = $sk->kota_penetapan ?? 'Semarang';
  $tglTampil = \Carbon\Carbon::parse($sk->tanggal_surat ?? $sk->tanggal_asli ?? now())->translatedFormat('d F Y');
  $jabatanPrefix = '';
  $jabatanBaris1 = 'Dekan,';
  if ($pen && (int)$pen->peran_id === 3) {
    $jabatanPrefix = 'a.n. Dekan Fakultas Ilmu Komputer<br>';
    $jabatanBaris1 = 'Wakil Dekan,';
  }
@endphp

{{-- ====================== STYLING & WRAPPER ====================== --}}
@if($context === 'pdf')
<style>
  /* PENTING: TIDAK ada @page di sini karena sudah di layout */
  body {
    font-family: "Times New Roman", Times, serif;
    margin: 0;
    font-size: 11pt;
    line-height: 1.6;
  }

  /* Utilitas untuk kontrol page break */
  .avoid-break       { page-break-inside: avoid; }
  .keep-with-next    { page-break-after: avoid; }
  .page-break        { page-break-before: always; }

  /* Orphans & widows */
  p, li { orphans: 3; widows: 3; }

  .sheet { padding: 0; }

  .judul-wrap { text-align: center; margin-top: .2cm; }

  .judul-1, .judul-2 {
    font-weight: 700; font-size: 14pt; text-transform: uppercase;
    page-break-after: avoid;
  }
  .judul-3 {
    font-weight: 700; font-size: 12pt; text-transform: uppercase;
    margin-top: 2mm; page-break-after: avoid;
  }

  .tentang { text-align: center; margin: .6cm 0 .5cm; page-break-after: avoid; }
  .tentang small { display: block; text-transform: lowercase; font-weight: 600; margin-bottom: 2mm; }
  .tentang .isi { text-transform: uppercase; font-weight: 700; }

  .deklarasi {
    text-align: center; font-weight: 700; text-transform: uppercase;
    margin: .4cm 0 .35cm; page-break-after: avoid;
  }

  /* Struktur blok */
  .section, .diktum-item {
    display: flex; align-items: flex-start; margin-bottom: 3mm; text-align: justify;
    page-break-inside: avoid;
  }
  .section .label, .diktum-item .label {
    flex-shrink: 0; width: 3.2cm; font-weight: 700;
  }
  .diktum-item .label { text-transform: uppercase; }
  .section .colon, .diktum-item .colon {
    flex-shrink: 0; font-weight: 700; margin: 0 0.15cm;
  }
  .section .content, .diktum-item .content { flex-grow: 1; }
  .section .content ol, .section .content ul,
  .diktum-item .content ol, .diktum-item .content ul { margin: 0; padding-left: 1cm; }
  ol { margin: 0; padding-left: 1.1cm; }
  .alpha { list-style: lower-alpha; }
  /* Hindari pecah item list */
  li { page-break-inside: avoid; }

  .memutuskan-title {
    text-align: center; font-weight: 700; letter-spacing: .2em;
    margin: .6cm 0 .2cm; page-break-after: avoid;
  }
  .menetapkan-label {
    font-weight: 700; margin-bottom: 2mm; page-break-after: avoid;
  }

  /* TTD Wrapper - PREVENT SPLIT */
  .ttd-wrapper {
    display: table; width: 100%; margin-top: 25px;
    page-break-inside: avoid;
  }
  .ttd-kolom-kiri { display: table-cell; width: 55%; }
  .ttd-kolom-kanan { display: table-cell; width: 45%; vertical-align: top; page-break-inside: avoid; }

  .ttd-teks { text-align: left; page-break-inside: avoid; line-height: 1.5; }

  .ttd-area-sign {
    position: relative; min-height: 28mm; margin-top: 6mm; text-align: center;
  }
  .ttd-area-sign .ttd, .ttd-area-sign .cap { display: inline-block; vertical-align: bottom; }

  .ttd-area-sign .ttd {
    /* translateX(-50%) keeps it centered, then we add X offset */
    transform: translateX(calc(-50% + var(--ttd-x, 0mm)));
    bottom: var(--ttd-y, 0mm); 
    width: var(--ttd-w, 42mm);
    left: 50%;
    margin-bottom: 0; margin-left: 0; /* Override legacy margins */
  }
  
  .ttd-area-sign .ttd img, .ttd-area-sign .cap img {
    width: 100%; height: auto; display: block;
  }

  .ttd-area-sign .cap {
    /* translateX(-25%) default offset for stamp, then add X offset */
    transform: translateX(calc(-25% + var(--cap-x, 0mm)));
    bottom: var(--cap-y, 0mm);
    width: var(--cap-w, 35mm);
    opacity: var(--cap-opacity, .95);
    z-index: 2;
    left: 50%;
    margin-bottom: 0; margin-left: 0; /* Override legacy margins */
  }

  .tembusan { margin-top: .6cm; }
  .tembusan-list { padding-left: 0; list-style-type: none; margin-top: 2mm; }
</style>

{{-- Kop di konten HANYA jika diminta (untuk fleksibilitas khusus) --}}
@if($showKopInContent && $kop)
  @include('shared._kop_surat', ['context' => 'pdf', 'kop' => $kop, 'showDivider' => true])
@endif

{{-- CONTENT tanpa wrapper (karena sudah dalam layout PDF) --}}

@else
{{-- ====================== CSS UNTUK WEB PREVIEW ====================== --}}
<style>
  .sheet {
    width: 210mm; min-height: auto; height: auto; margin: 8mm auto;
    background: #fff; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,.08);
    padding: 38mm 16mm 24mm 16mm; /* ruang kop & footer web preview */
    font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.35;
  }

  .judul-wrap { text-align: center; margin-top: 2mm; }
  .judul-1, .judul-2 { font-weight: 700; font-size: 14pt; text-transform: uppercase; }
  .judul-3 { font-weight: 700; font-size: 12pt; text-transform: uppercase; margin-top: 2mm; }

  .tentang { text-align: center; margin: .6cm 0 .5cm; }
  .tentang small { display: block; text-transform: lowercase; font-weight: 600; margin-bottom: 2mm; }
  .tentang .isi { text-transform: uppercase; font-weight: 700; }

  .deklarasi { text-align: center; font-weight: 700; text-transform: uppercase; margin: .4cm 0 .35cm; }

  .section, .diktum-item {
    display: flex; align-items: flex-start; margin-bottom: 3mm; text-align: justify;
  }
  .section .label, .diktum-item .label { flex-shrink: 0; width: 3.2cm; font-weight: 700; }
  .diktum-item .label { text-transform: uppercase; }
  .section .colon, .diktum-item .colon { flex-shrink: 0; font-weight: 700; margin: 0 0.15cm; }
  .section .content, .diktum-item .content { flex-grow: 1; }
  .section .content ol, .section .content ul,
  .diktum-item .content ol, .diktum-item .content ul { margin: 0; padding-left: 1cm; }
  ol { margin: 0; padding-left: 1.1cm; }
  .alpha { list-style: lower-alpha; }

  .memutuskan-title { text-align: center; font-weight: 700; letter-spacing: .2em; margin: .6cm 0 .2cm; }
  .menetapkan-label { font-weight: 700; margin-bottom: 2mm; }

  .ttd-wrapper { display: table; width: 100%; margin-top: 25px; }
  .ttd-kolom-kiri { display: table-cell; width: 55%; }
  .ttd-kolom-kanan { display: table-cell; width: 45%; vertical-align: top; }
  .ttd-teks { text-align: left; line-height: 1.5; }

  .ttd-area-sign { position: relative; min-height: 28mm; margin-top: 6mm; text-align: center; }
  .ttd-area-sign .ttd, .ttd-area-sign .cap { display: inline-block; vertical-align: bottom; }
  .ttd-area-sign .ttd {
    transform: translateX(calc(-50% + var(--ttd-x, 0mm)));
    bottom: var(--ttd-y, 0mm); 
    width: var(--ttd-w, 42mm);
    left: 50%;
    margin-bottom: 0; margin-left: 0;
  }

  .ttd-area-sign .ttd img, .ttd-area-sign .cap img {
    width: 100%; height: auto; display: block;
  }
  .resize-handle {
    width: 12px; height: 12px;
    background: #ffffff;
    border: 1px solid #007bff;
    border-radius: 50%;
    position: absolute;
    bottom: -6px; right: -6px;
    cursor: nwse-resize;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }

  .ttd-area-sign .cap {
    transform: translateX(calc(-25% + var(--cap-x, 0mm)));
    bottom: var(--cap-y, 0mm);
    width: var(--cap-w, 35mm);
    opacity: var(--cap-opacity, .95);
    z-index: 2;
    left: 50%;
    margin-bottom: 0; margin-left: 0;
  }

  .tembusan-list { padding-left: 0; list-style-type: none; margin-top: 2mm; }
</style>

<div class="sheet">
  {{-- Kop selalu tampil di web preview --}}
  @if($kop)
    <div class="kop-wrap" style="position: absolute; top: 10mm; left: 15mm; right: 15mm;">
      @include('shared._kop_surat', ['context' => 'web', 'kop' => $kop, 'showDivider' => true])
    </div>
  @endif
@endif

{{-- ====================== HTML KONTEN SURAT KEPUTUSAN ====================== --}}

{{-- === JUDUL 3 BARIS === --}}
<div class="judul-wrap keep-with-next">
  <div class="judul-1">KEPUTUSAN DEKAN FAKULTAS ILMU KOMPUTER</div>
  <div class="judul-2">UNIVERSITAS KATOLIK SOEGIJAPRANATA</div>
  <div class="judul-3">NOMOR {{ $sk->nomor ?? '—' }}</div>
</div>

{{-- === TENTANG === --}}
<div class="tentang keep-with-next">
  <small>tentang</small>
  <div class="isi">{{ $sk->tentang ?? '—' }}</div>
</div>

{{-- === DEKLARASI DEKAN === --}}
<div class="deklarasi keep-with-next">
  DEKAN FAKULTAS ILMU KOMPUTER<br>
  UNIVERSITAS KATOLIK SOEGIJAPRANATA
</div>

{{-- === MENIMBANG === --}}
<div class="section avoid-break">
  <span class="label">Menimbang</span><span class="colon">:</span>
  <div class="content">
    @if(count($menimbang))
      <ol class="alpha">
        @foreach($menimbang as $item)
          <li>{{ $item }}</li>
        @endforeach
      </ol>
    @else
      <div>-</div>
    @endif
  </div>
</div>

{{-- === MENGINGAT === --}}
<div class="section avoid-break">
  <span class="label">Mengingat</span><span class="colon">:</span>
  <div class="content">
    @if(count($mengingat))
      <ol>
        @foreach($mengingat as $item)
          <li>{{ $item }}</li>
        @endforeach
      </ol>
    @else
      <div>-</div>
    @endif
  </div>
</div>

{{-- === MEMUTUSKAN / MENETAPKAN === --}}
<div class="memutuskan-title keep-with-next">M E M U T U S K A N</div>
<div class="menetapkan-label keep-with-next">Menetapkan :</div>

{{-- Blok Memutuskan dengan array terstruktur --}}
<div class="menetapkan-items">
  @if(!empty($menetapkanItems))
    @foreach($menetapkanItems as $index => $item)
      <div class="diktum-item avoid-break">
        <span class="label">{{ $item['judul'] ?? ($ordinalWords[$index] ?? 'BERIKUTNYA') }}</span>
        <span class="colon">:</span>
        <div class="content">
          {!! $item['isi'] !!}
        </div>
      </div>
    @endforeach
  @else
    <div>-</div>
  @endif
</div>

{{-- === BLOK TANDA TANGAN === --}}
<div class="ttd-wrapper avoid-break">
  <div class="ttd-kolom-kiri"></div>
  <div class="ttd-kolom-kanan">
    <div class="ttd-teks keep-with-next">
      Ditetapkan di {{ $kotaPenetapan }}<br>
      Pada tanggal : {{ $tglTampil }}<br>
      {!! $jabatanPrefix !!}{{ rtrim($jabatanBaris1, ',') }},
    </div>

    <div class="ttd-area-sign" style="--ttd-w: {{$ttdW}}mm; --cap-w: {{$capW}}mm; --cap-opacity: {{$capOpacity}}; --ttd-x: {{$ttdX}}mm; --ttd-y: {{$ttdY}}mm; --cap-x: {{$capX}}mm; --cap-y: {{$capY}}mm;">
      @if($showSigns)
        @if(!empty($ttdImageB64))
            <div class="ttd-draggable ttd">
                <img src="{{ $ttdImageB64 }}" alt="TTD">
                <div class="resize-handle ttd-handle"></div>
            </div>
        @endif
        @if(!empty($capImageB64))
            <div class="cap-draggable cap">
                <img src="{{ $capImageB64 }}" alt="Cap">
                <div class="resize-handle cap-handle"></div>
            </div>
        @endif
      @endif
    </div>

    <div class="ttd-teks avoid-break">
      <strong>{{ $pen->nama_lengkap ?? '(.............................)' }}</strong><br>
      NPP. {{ $pen->npp ?? '-' }}
    </div>
  </div>
</div>

{{-- === TEMBUSAN === --}}
@if(!empty($tembusanItems))
  <div class="tembusan avoid-break">
    <strong>Tembusan:</strong>
    <div class="tembusan-list">
      @foreach($tembusanItems as $t)
        <div>{{ $t }}</div>
      @endforeach
    </div>
  </div>
@endif

@if($context === 'web')
</div>
@endif
