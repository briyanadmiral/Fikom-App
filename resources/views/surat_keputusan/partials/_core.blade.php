{{-- resources/views/surat_keputusan/partials/_core.blade.php --}}
@php
  // context: 'web' | 'pdf'
  $context = $context ?? 'web';

  // Konsolidasi objek (biar fleksibel dipanggil dengan $keputusan atau $sk)
  $sk = $keputusan ?? $sk ?? null;

  // Decode array
  $menimbang = is_array($sk?->menimbang) ? $sk->menimbang : (json_decode($sk->menimbang ?? '[]', true) ?: []);
  $mengingat = is_array($sk?->mengingat) ? $sk->mengingat : (json_decode($sk->mengingat ?? '[]', true) ?: []);

  // 'memutuskan' sudah HTML siap cetak
  $memutuskanHtml = $sk->memutuskan ?? '';

  // Aset TTD/Cap (pastikan ada default agar tidak "undefined variable")
  $ttdImageB64 = $ttdImageB64 ?? null;
  $capImageB64 = $capImageB64 ?? null;

  // Tampilkan TTD & Cap hanya jika sudah disetujui & ada signed_at (kecuali halaman approve/preview override via $showSigns=true)
  $showSigns = $showSigns ?? ( ($sk->status_surat ?? null) === 'disetujui' && !empty($sk->signed_at ?? null) );

  // Fallback ukuran dari config di DB bila slider tidak mengirim nilai
  $ttdW       = isset($ttdW)       ? (int)$ttdW       : (int)($sk->ttd_config['w_mm']   ?? 42);
  $capW       = isset($capW)       ? (int)$capW       : (int)($sk->cap_config['w_mm']   ?? 35);
  $capOpacity = isset($capOpacity) ? (float)$capOpacity : (float)($sk->cap_config['opacity'] ?? 0.95);

  // Tembusan: dukung pemisah koma atau baris baru
  $tembusanRaw = (string)($sk->tembusan ?? '');
  $tembusanItems = collect(preg_split('/[\r\n,]+/', $tembusanRaw))
      ->map(fn($v) => trim($v))
      ->filter()
      ->values()
      ->all();
@endphp

@if($context === 'pdf')
  <style>
    @page { margin: 2cm; }
    body { font-family: "Times New Roman", Times, serif; margin: 0; font-size: 16px; }
    .judul { text-align:center; font-weight:700; font-size:22px; text-decoration:underline; margin-top:6px; }
    .nomor { text-align:center; margin:6px 0 18px; }
    ol { margin: 0; padding-left: 1.2rem; }
    .alpha { list-style: lower-alpha; }
    .section-title { font-weight:700; text-transform:uppercase; margin: .8rem 0 .25rem; }
    /* Area ttd/cap dibuat relatif untuk layering yang stabil di DomPDF */
    .sign-box { position: relative; width: 100%; height: 28mm; margin: 6mm 0; }
    .sign-box img { position: absolute; left: 50%; transform: translateX(-50%); display:block; }
  </style>
@else
  <style>
    .sheet{
      width:210mm; min-height:297mm; margin:8mm auto; background:#fff; position:relative;
      box-shadow:0 10px 30px rgba(0,0,0,.08); padding:40mm 15mm 25mm 15mm;
      font-family:"Times New Roman", Times, serif;
    }
    .judul { text-align:center; font-weight:700; font-size:22px; text-decoration:underline; margin-top:6px; }
    .nomor { text-align:center; margin:6px 0 18px; }
    ol { margin: 0; padding-left: 1.2rem; }
    .alpha { list-style: lower-alpha; }
    .section-title { font-weight:700; text-transform:uppercase; margin: .8rem 0 .25rem; }
    .sign-box { position: relative; width: 100%; height: 28mm; margin: 6mm 0; }
    .sign-box img { position: absolute; left: 50%; transform: translateX(-50%); display:block; }
  </style>
  <div class="sheet">
@endif

{{-- === KOP SURAT (shared, sama seperti Surat Tugas) === --}}
@include('shared._kop_surat', ['kop' => $kop ?? null, 'context' => $context])

{{-- === JUDUL & NOMOR === --}}
<div class="judul">SURAT KEPUTUSAN</div>
<div class="nomor">Nomor : {{ $sk->nomor ?? '-' }}</div>

{{-- === TENTANG === --}}
<div style="text-align:center; font-weight:600; margin-bottom:.8rem;">
  TENTANG<br>
  <span style="text-transform:uppercase;">{{ $sk->tentang ?? '-' }}</span>
</div>

{{-- === MENIMBANG === --}}
<div class="section">
  <div class="section-title">Menimbang</div>
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

{{-- === MENGINGAT === --}}
<div class="section">
  <div class="section-title">Mengingat</div>
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

{{-- === MEMUTUSKAN / MENETAPKAN === --}}
<div class="section">
  <div class="section-title">Memutuskan</div>
  <div style="font-weight:600; margin-bottom:.25rem;">Menetapkan:</div>
  @if(!empty($memutuskanHtml))
    {!! $memutuskanHtml !!}
  @else
    <div>-</div>
  @endif
</div>

{{-- === BLOK TTD === --}}
@php
  $pen = $sk->penandatanganUser ?? null;
  $jabatanTtd = 'Pejabat Penandatangan';
  if ($pen) {
      if ((int)$pen->peran_id === 2)      $jabatanTtd = 'Dekan Fakultas Ilmu Komputer';
      elseif ((int)$pen->peran_id === 3)  $jabatanTtd = 'a.n. Dekan Fakultas Ilmu Komputer<br>Wakil Dekan Fakultas Ilmu Komputer';
  }
@endphp

<div style="margin-top: 24px; display:flex; justify-content:flex-end;">
  <div style="min-width:320px; text-align:left;">
    <div>Semarang, {{ \Carbon\Carbon::parse($sk->tanggal_surat ?? $sk->tanggal_asli ?? now())->translatedFormat('d F Y') }}</div>
    <div>{!! $jabatanTtd !!}</div>

    {{-- Area layering cap (di bawah) dan ttd (di atas). Hindari margin negatif agar stabil di DomPDF --}}
    <div class="sign-box">
      @if($showSigns && !empty($capImageB64))
        <img class="cap"
             src="{{ $capImageB64 }}" alt="Cap"
             style="top:2mm; width: {{ $capW }}mm; opacity: {{ $capOpacity }}; z-index: 1;">
      @endif
      @if($showSigns && !empty($ttdImageB64))
        <img class="ttd"
             src="{{ $ttdImageB64 }}" alt="TTD"
             style="top:6mm; width: {{ $ttdW }}mm; z-index: 2;">
      @endif
    </div>

    <div>
      <strong>{{ $pen->nama_lengkap ?? '(.............................)' }}</strong><br>
      NPP. {{ $pen->npp ?? '-' }}
    </div>
  </div>
</div>

{{-- === TEMBUSAN === --}}
@if(!empty($tembusanItems))
  <div style="margin-top: 10px;">
    <strong>Tembusan:</strong>
    <ol style="margin-top:.4rem;">
      @foreach($tembusanItems as $t) <li>{{ $t }}</li> @endforeach
    </ol>
  </div>
@endif

@if($context === 'web')
  </div>
@endif
