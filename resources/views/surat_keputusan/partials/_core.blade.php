{{-- resources/views/surat_keputusan/partials/_core.blade.php --}}
@php
// context: 'web' | 'pdf'
$context = $context ?? 'web';

// Konsolidasi objek
$sk = $keputusan ?? $sk ?? null;

// Data list
$menimbang = is_array($sk?->menimbang) ? $sk->menimbang : (json_decode($sk->menimbang ?? '[]', true) ?: []);
$mengingat = is_array($sk?->mengingat) ? $sk->mengingat : (json_decode($sk->mengingat ?? '[]', true) ?: []);

// -- MODIFIED START: Ubah 'memutuskan' menjadi array terstruktur --
// 'memutuskan' sekarang diharapkan berupa array/JSON, bukan HTML tunggal.
$menetapkanItems = is_array($sk?->menetapkan) ? $sk->menetapkan : (json_decode($sk?->menetapkan ?? '[]', true) ?: []);
$ordinalWords = ['KESATU', 'KEDUA', 'KETIGA', 'KEEMPAT', 'KELIMA', 'KEENAM', 'KETUJUH', 'KEDELAPAN', 'KESEMBILAN', 'KESEPULUH'];
// -- MODIFIED END --

// Aset tanda tangan & cap
$ttdImageB64 = $ttdImageB64 ?? null;
$capImageB64 = $capImageB64 ?? null;

// Tampilkan TTD/Cap (override via $showSigns di halaman approve)
$showSigns = $showSigns ?? ( ($sk->status_surat ?? null) === 'disetujui' && !empty($sk->signed_at ?? null) );

// Ukuran fallback
$ttdW = isset($ttdW) ? (int)$ttdW : (int)($sk->ttd_config['w_mm'] ?? 42);
$capW = isset($capW) ? (int)$capW : (int)($sk->cap_config['w_mm'] ?? 35);
$capOpacity = isset($capOpacity) ? (float)$capOpacity : (float)($sk->cap_config['opacity'] ?? 0.95);

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

@if($context === 'pdf')
<style>
  @page {
    margin: 2cm;
  }

  body {
    font-family: "Times New Roman", Times, serif;
    margin: 0;
    font-size: 12pt;
    line-height: 1.35;
  }

  .sheet {
    padding: 0;
  }

  .judul-wrap {
    text-align: center;
    margin-top: .2cm;
  }

  .judul-1,
  .judul-2 {
    font-weight: 700;
    font-size: 14pt;
    text-transform: uppercase;
  }

  .judul-3 {
    font-weight: 700;
    font-size: 12pt;
    text-transform: uppercase;
    margin-top: 2mm;
  }

  .tentang {
    text-align: center;
    margin: .6cm 0 .5cm;
  }

  .tentang small {
    display: block;
    text-transform: lowercase;
    font-weight: 600;
    margin-bottom: 2mm;
  }

  .tentang .isi {
    text-transform: uppercase;
    font-weight: 700;
  }

  .deklarasi {
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    margin: .4cm 0 .35cm;
  }

  /* -- MODIFIED START: Menggunakan Flexbox untuk layout yang lebih solid -- */
  .section,
  .diktum-item {
    display: flex;
    align-items: flex-start;
    /* Rata atas */
    margin-bottom: 3mm;
    text-align: justify;
  }

  .section .label,
  .diktum-item .label {
    flex-shrink: 0;
    /* Jangan biarkan label menyusut */
    width: 3.2cm;
    /* Lebar tetap untuk label */
    font-weight: 700;
  }

  .diktum-item .label {
    text-transform: uppercase;
  }

  .section .colon,
  .diktum-item .colon {
    flex-shrink: 0;
    font-weight: 700;
    margin: 0 0.15cm;
  }

  .section .content,
  .diktum-item .content {
    flex-grow: 1;
    /* Konten mengisi sisa ruang */
  }

  .section .content ol,
  .section .content ul,
  .diktum-item .content ol,
  .diktum-item .content ul {
    margin: 0;
    padding-left: 1cm;
  }

  /* -- MODIFIED END -- */

  ol {
    margin: 0;
    padding-left: 1.1cm;
  }

  .alpha {
    list-style: lower-alpha;
  }

  .memutuskan-title {
    text-align: center;
    font-weight: 700;
    letter-spacing: .2em;
    margin: .6cm 0 .2cm;
  }

  .menetapkan-label {
    font-weight: 700;
    margin-bottom: 2mm;
  }

  .ttd-wrapper {
    display: table;
    width: 100%;
    margin-top: 25px;
    page-break-inside: avoid;
  }

  .ttd-kolom-kiri {
    display: table-cell;
    width: 55%;
  }

  .ttd-kolom-kanan {
    display: table-cell;
    width: 45%;
    vertical-align: top;
    page-break-inside: avoid;
  }

  .ttd-teks {
    text-align: left;
    page-break-inside: avoid;
    line-height: 1.5;
  }

  .ttd-area-sign {
    position: relative;
    min-height: 28mm;
    margin-top: 6mm;
    text-align: center;
  }

  .ttd-area-sign .ttd,
  .ttd-area-sign .cap {
    display: inline-block;
    vertical-align: bottom;
  }

  .ttd-area-sign .ttd {
      width: var(--ttd-w, 42mm);
      /* [PERBAIKAN] Samakan dengan CSS web */
      margin-left: -30mm;
      margin-bottom: 10mm;
    }
   .ttd-area-sign .cap {
      width: var(--cap-w, 35mm);
      opacity: var(--cap-opacity, 0.95);
      position: relative;
      z-index: 2;
      /* [PERBAIKAN] Samakan dengan CSS web */
      margin-left: -20mm;
      margin-bottom: 6mm;
    }

  .tembusan {
    margin-top: .6cm;
  }

  .tembusan-list {
    padding-left: 0;
    list-style-type: none;
    margin-top: 2mm;
  }
</style>
@else
<style>
  .sheet {
    width: 210mm;
    /* [PERBAIKAN] Hapus tinggi tetap, biarkan konten menentukan tinggi */
    min-height: auto;
    height: auto;
    margin: 8mm auto;
    background: #fff;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
    padding: 38mm 16mm 24mm 16mm;
    font-family: "Times New Roman", Times, serif;
    font-size: 12pt;
    line-height: 1.35;
  }

  .judul-wrap {
    text-align: center;
    margin-top: 2mm;
  }

  .judul-1,
  .judul-2 {
    font-weight: 700;
    font-size: 14pt;
    text-transform: uppercase;
  }

  .judul-3 {
    font-weight: 700;
    font-size: 12pt;
    text-transform: uppercase;
    margin-top: 2mm;
  }

  .tentang {
    text-align: center;
    margin: .6cm 0 .5cm;
  }

  .tentang small {
    display: block;
    text-transform: lowercase;
    font-weight: 600;
    margin-bottom: 2mm;
  }

  .tentang .isi {
    text-transform: uppercase;
    font-weight: 700;
  }

  .deklarasi {
    text-align: center;
    font-weight: 700;
    text-transform: uppercase;
    margin: .4cm 0 .35cm;
  }

  /* -- MODIFIED START: Menggunakan Flexbox untuk layout yang lebih solid -- */
  .section,
  .diktum-item {
    display: flex;
    align-items: flex-start;
    /* Rata atas */
    margin-bottom: 3mm;
    text-align: justify;
  }

  .section .label,
  .diktum-item .label {
    flex-shrink: 0;
    /* Jangan biarkan label menyusut */
    width: 3.2cm;
    /* Lebar tetap untuk label */
    font-weight: 700;
  }

  .diktum-item .label {
    text-transform: uppercase;
  }

  .section .colon,
  .diktum-item .colon {
    flex-shrink: 0;
    font-weight: 700;
    margin: 0 0.15cm;
  }

  .section .content,
  .diktum-item .content {
    flex-grow: 1;
    /* Konten mengisi sisa ruang */
  }

  .section .content ol,
  .section .content ul,
  .diktum-item .content ol,
  .diktum-item .content ul {
    margin: 0;
    padding-left: 1cm;
  }

  /* -- MODIFIED END -- */

  ol {
    margin: 0;
    padding-left: 1.1cm;
  }

  .alpha {
    list-style: lower-alpha;
  }

  .memutuskan-title {
    text-align: center;
    font-weight: 700;
    letter-spacing: .2em;
    margin: .6cm 0 .2cm;
  }

  .menetapkan-label {
    font-weight: 700;
    margin-bottom: 2mm;
  }

  .ttd-wrapper {
    display: table;
    width: 100%;
    margin-top: 25px;
  }

  .ttd-kolom-kiri {
    display: table-cell;
    width: 55%;
  }

  .ttd-kolom-kanan {
    display: table-cell;
    width: 45%;
    vertical-align: top;
  }

  .ttd-teks {
    text-align: left;
    line-height: 1.5;
  }

  .ttd-area-sign {
    position: relative;
    min-height: 28mm;
    margin-top: 6mm;
    text-align: center;
  }

  .ttd-area-sign .ttd,
  .ttd-area-sign .cap {
    display: inline-block;
    vertical-align: bottom;
  }

  .ttd-area-sign .ttd {
    width: var(--ttd-w, 42mm);
    margin-left: -30mm;
    margin-bottom: 10mm;
  }

  .ttd-area-sign .cap {
    width: var(--cap-w, 35mm);
    opacity: var(--cap-opacity, 0.95);
    margin-left: -20mm;
    margin-bottom: 6mm;
    position: relative;
    z-index: 2;
  }

  .tembusan-list {
    padding-left: 0;
    list-style-type: none;
    margin-top: 2mm;
  }
</style>
<div class="sheet">
  @endif

  {{-- === KOP SURAT === --}}
  @include('shared._kop_surat', ['kop' => $kop ?? null, 'context' => $context])

  {{-- === JUDUL 3 BARIS === --}}
  <div class="judul-wrap">
    <div class="judul-1">KEPUTUSAN DEKAN FAKULTAS ILMU KOMPUTER</div>
    <div class="judul-2">UNIVERSITAS KATOLIK SOEGIJAPRANATA</div>
    <div class="judul-3">NOMOR {{ $sk->nomor ?? '—' }}</div>
  </div>

  {{-- === TENTANG === --}}
  <div class="tentang">
    <small>tentang</small>
    <div class="isi">{{ $sk->tentang ?? '—' }}</div>
  </div>

  {{-- === DEKLARASI DEKAN === --}}
  <div class="deklarasi">
    DEKAN FAKULTAS ILMU KOMPUTER<br>
    UNIVERSITAS KATOLIK SOEGIJAPRANATA
  </div>

  {{-- === MENIMBANG === --}}
  <div class="section">
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
  <div class="section">
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
  <div class="memutuskan-title">M E M U T U S K A N</div>
  <div class="menetapkan-label">Menetapkan :</div>

  {{-- -- MODIFIED START: Ubah tampilan blok Memutuskan -- --}}
  <div class="menetapkan-items">
    @if(!empty($menetapkanItems))
    @foreach($menetapkanItems as $index => $item)
    <div class="diktum-item">
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
  {{-- -- MODIFIED END -- --}}


  {{-- === BLOK TANDA TANGAN (match Surat Tugas) === --}}
  <div class="ttd-wrapper">
    <div class="ttd-kolom-kiri"></div>
    <div class="ttd-kolom-kanan">
      <div class="ttd-teks">
        Ditetapkan di {{ $kotaPenetapan }}<br>
        Pada tanggal : {{ $tglTampil }}<br>
        {!! $jabatanPrefix !!}{{ rtrim($jabatanBaris1, ',') }},
      </div>

      <div class="ttd-area-sign" style="--ttd-w: {{$ttdW}}mm; --cap-w: {{$capW}}mm; --cap-opacity: {{$capOpacity}};">
        @if($showSigns)
        @if(!empty($ttdImageB64))
        <img class="ttd" src="{{ $ttdImageB64 }}" alt="TTD">
        @endif
        @if(!empty($capImageB64))
        <img class="cap" src="{{ $capImageB64 }}" alt="Cap">
        @endif
        @endif
      </div>

      <div class="ttd-teks">
        <strong>{{ $pen->nama_lengkap ?? '(.............................)' }}</strong><br>
        NPP. {{ $pen->npp ?? '-' }}
      </div>
    </div>
  </div>

  {{-- === TEMBUSAN === --}}
  @if(!empty($tembusanItems))
  <div class="tembusan">
    <strong>Tembusan:</strong>
    {{-- -- MODIFIED START: Ganti <ol> menjadi <div> untuk menghilangkan nomor -- --}}
    <div class="tembusan-list">
      @foreach($tembusanItems as $t)
      <div>{{ $t }}</div>
      @endforeach
    </div>
    {{-- -- MODIFIED END -- --}}
  </div>
  @endif

  @if($context === 'web')
</div>
@endif