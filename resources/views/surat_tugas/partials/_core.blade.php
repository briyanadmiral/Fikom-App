{{-- === GUARD VISIBILITAS TTD/CAP (fallback) === --}}
@php
  if (!isset($showSigns)) {
    $showSigns = isset($tugas)
      ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
      : false;
  }
@endphp

@php
  // ==== context: 'pdf' | 'web' (default 'web') ====
  $context = $context ?? 'web';

  // Helper path gambar (header/logo/foot) untuk WEB/PDF
  $img = function ($path) use ($context) {
      if (!$path) return null;
      return $context === 'pdf' ? public_path('storage/'.$path) : asset('storage/'.$path);
  };

  // Base64 untuk DomPDF (lebih stabil untuk gambar dinamis)
  $img64 = function ($path) {
      if (!$path) return null;
      $file = public_path('storage/'.$path);
      if (!is_file($file)) return null;
      $ext = pathinfo($file, PATHINFO_EXTENSION);
      return 'data:image/'.$ext.';base64,'.base64_encode(file_get_contents($file));
  };

  $penerimaList = $tugas->penerima?->pluck('pengguna.nama_lengkap')->filter()->values()->all() ?? [];
  $roleNames = collect($tugas->penerima ?? [])->map(fn($p) => optional(optional($p->pengguna)->peran)->deskripsi)->filter()->unique()->values()->all();
  $statusDisplay = $roleNames ? implode(', ', $roleNames) : (\Illuminate\Support\Str::headline($tugas->status_penerima ?? '') ?: '-');
  $tugasSpesifik = optional($tugas->subTugas)->nama ?? ($tugas->tugas ?? $tugas->nama_umum ?? '-');

  // Variabel TTD & Cap
  $ttdW_final       = $ttdW       ?? 42;
  $capW_final       = $capW       ?? 35;
  $capOpacity_final = $capOpacity ?? 0.95;

  // HARDENING: Jika belum boleh tampil, kosongkan gambar agar aman
  if (!$showSigns) {
    $ttdImageB64 = null;
    $capImageB64 = null;
  }
@endphp

{{-- ====================== STYLING & WRAPPER ====================== --}}
@if($context === 'pdf')
  <style>
    /* Menggunakan @page untuk margin cetak yang benar */
    @page { margin: 2cm; }

    /* Hapus margin dari body, karena sudah diatur oleh @page */
    body {
        font-family: "Times New Roman", Times, serif;
        margin: 0;
        font-size: 16px;
    }

    table { border-collapse: collapse; width: 100%; }
    td { padding: 4px 8px; vertical-align: top; }

    /* Header */
    .kop-wrap {
        position: static;
        width: 100%;
        padding-bottom: 8px;
        border-bottom: 2px solid #000;
        margin-bottom: 20px;
    }
    .kop-td-text { width: calc(100% - 130px); }
    .kop-td-logo { width: 130px; text-align: right; border-left: 2px solid #000; padding-left: 12px; }
    .kop-text { line-height: 1.25; text-align: right; }
    .kop-text .l1 { font-weight: 800; font-size: 21px; color: #6A2C8E; }
    .kop-text .l2 { font-weight: 800; font-size: 15px; margin-top: -2px; color: #6A2C8E; }
    .kop-text .addr { font-size: 11px; margin-top: 6px; color: #111; }
    .logo-kanan { width: 92px; height: auto; }

    .judul { text-align: center; font-weight: 700; font-size: 22px; text-decoration: underline; }
    .nomor { text-align: center; margin: 6px 0 20px; }
    .isi-surat { line-height: 1.5; }
    .detail-tugas { margin: 1.2em 0 1.2em 40px; }

    /* Blok TTD */
    .ttd-wrapper { display: table; width: 100%; margin-top: 25px; page-break-inside: avoid; }
    .ttd-kolom-kiri { display: table-cell; width: 55%; }
    .ttd-kolom-kanan { display: table-cell; width: 45%; vertical-align: top; page-break-inside: avoid; }
    .ttd-teks { text-align: left; page-break-inside: avoid; line-height: 1.5; }
    .ttd-area-sign { position: relative; min-height: 28mm; margin-top: 6mm; text-align: center; }
    .ttd-area-sign .ttd, .ttd-area-sign .cap { display: inline-block; vertical-align: bottom; }
    .ttd-area-sign .ttd { width: var(--ttd-w, 42mm); }
    .ttd-area-sign .cap { width: var(--cap-w, 35mm); opacity: var(--cap-opacity, 0.95); margin-left: -40mm; margin-bottom: 6mm; position: relative; z-index: 2; }
  </style>
@else
  {{-- CSS UNTUK WEB PREVIEW --}}
  <style>
    body { margin:0; font-family:"Times New Roman", Times, serif; background:#f6f7fb; }
    .sheet{ width:210mm; min-height:297mm; margin:8mm auto; background:#fff; position:relative;
            box-shadow:0 10px 30px rgba(0,0,0,.08); padding:40mm 15mm 25mm 15mm; }
    .judul { text-align:center; font-weight:700; font-size:22px; text-decoration:underline; margin-top:6px; }
    .nomor { text-align:center; margin:6px 0 20px; }
    .isi-surat { line-height: 1.5; }
    .detail-tugas { margin: 1.2em 0 1.2em 40px; }
    table{ border-collapse:collapse; width: 100%; }
    td{ padding:4px 8px; vertical-align:top; }
    .kop-wrap{ position:absolute; top:10mm; left:15mm; right:15mm; padding-bottom:8px; border-bottom:2px solid #000; }
    .kop-tbl{ width:100%; border-collapse:collapse; }
    .kop-td-text{ width: calc(100% - 130px); }
    .kop-td-logo{ width:130px; text-align:right; border-left:2px solid #000; padding-left:12px; }
    .kop-text{ line-height:1.25; text-align:right; }
    .kop-text .l1{ font-weight:800; font-size:21px; letter-spacing:.4px; color:#6A2C8E; }
    .kop-text .l2{ font-weight:800; font-size:15px; margin-top:-2px; color:#6A2C8E; }
    .kop-text .addr{ font-size:11px; margin-top:6px; color:#111; text-align:right; }
    .logo-kanan{ width:92px; height:auto; }
    .ttd-wrapper { display: table; width: 100%; margin-top: 25px; }
    .ttd-kolom-kiri { display: table-cell; width: 55%; }
    .ttd-kolom-kanan { display: table-cell; width: 45%; vertical-align: top; }
    .ttd-teks { text-align: left; line-height: 1.5; }
    .ttd-area-sign { position: relative; min-height: 28mm; margin-top: 6mm; text-align: center; }
    .ttd-area-sign .ttd, .ttd-area-sign .cap { display: inline-block; vertical-align: bottom; }
    .ttd-area-sign .ttd { width: var(--ttd-w, 42mm); margin-left: -50mm; margin-bottom: 10mm; }
    .ttd-area-sign .cap { width: var(--cap-w, 35mm); opacity: var(--cap-opacity, 0.95); margin-left: -20mm; margin-bottom: 6mm; position: relative; z-index: 2; }
  </style>
  <div class="sheet">
@endif

{{-- ====================== HTML KONTEN ====================== --}}
@if($kop && ($kop->mode ?? null) === 'composed')
    <div class="kop-wrap">
        <table>
            <tr>
                <td class="kop-td-text">
                    <div class="kop-text">
                        <div class="l1">{{ strtoupper($kop->judul_atas ?? '') }}</div>
                        <div class="l2">{{ strtoupper($kop->subjudul ?? '') }}</div>
                        <div class="addr">
                            {{ $kop->alamat ?? '' }}<br>
                            Telp. {{ $kop->telepon ?? '' }}@if(!empty($kop?->fax)) , Fax. {{ $kop->fax }} @endif<br>
                            email: {{ $kop->email ?? '' }} @if(!empty($kop?->website)) | {{ $kop->website }} @endif
                        </div>
                    </div>
                </td>
                <td class="kop-td-logo">
                    @if(($kop->tampilkan_logo_kanan ?? false) && !empty($kop->logo_kanan_path))
                        @if($context === 'pdf')
                            @php $src = $img64($kop->logo_kanan_path); @endphp
                            @if($src)<img class="logo-kanan" src="{{ $src }}">@endif
                        @else
                            <img class="logo-kanan" src="{{ $img($kop->logo_kanan_path) }}">
                        @endif
                    @endif
                </td>
            </tr>
        </table>
    </div>
@endif

<div class="judul">SURAT TUGAS</div>
<div class="nomor">Nomor : {{ $tugas->nomor ?? '-' }}</div>

<div class="isi-surat">
  Dekan Fakultas Ilmu Komputer Universitas Katolik Soegijapranata dengan ini memberikan tugas kepada:
  <div class="detail-tugas">
    <table>
      <tr>
        <td style="width: 15%;">Nama</td><td style="width: 2%;">:</td>
        <td>{{ !empty($penerimaList) ? implode(', ', $penerimaList) : '—' }}</td>
      </tr>
      <tr><td>Status</td><td>:</td><td>{{ $statusDisplay }}</td></tr>
      <tr><td>Tugas</td><td>:</td><td>{{ $tugasSpesifik }}</td></tr>
      <tr>
        <td>Waktu</td>
        <td>:</td>
        <td>
          @php
            $waktuList = [];
            if (!empty($tugas->semester)) $waktuList[] = $tugas->semester;
            if (!empty($tugas->tahun)) $waktuList[] = $tugas->tahun;
            echo !empty($waktuList) ? implode(' ', $waktuList) : '-';
          @endphp
        </td>
      </tr>
    </table>
  </div>

  Harap melaksanakan tugas dengan sebaik-baiknya dan penuh tanggung jawab serta memberikan laporan setelah selesai melaksanakan tugas.
</div>

<div class="ttd-wrapper">
  <div class="ttd-kolom-kiri"></div>
  <div class="ttd-kolom-kanan">
    <div class="ttd-teks">
      Semarang, {{ \Carbon\Carbon::parse($tugas->tanggal_surat ?? now())->translatedFormat('d F Y') }}
      <br>
      @php
        $penandatangan = $tugas->penandatanganUser;
        $jabatanTtd = 'Pejabat Penandatangan'; // Fallback default
        if ($penandatangan) {
            if ($penandatangan->peran_id == 2) {
                $jabatanTtd = 'Dekan Fakultas Ilmu Komputer';
            } elseif ($penandatangan->peran_id == 3) {
                $jabatanTtd = 'a.n. Dekan Fakultas Ilmu Komputer<br>Wakil Dekan Fakultas Ilmu Komputer';
            }
        }
      @endphp
      {!! $jabatanTtd !!}
    </div>

    {{-- === AREA TTD & CAP === --}}
    <div class="ttd-area-sign" style="--ttd-w: {{$ttdW_final}}mm; --cap-w: {{$capW_final}}mm; --cap-opacity: {{$capOpacity_final}};">
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
      <strong>{{ optional($tugas->penandatanganUser)->nama_lengkap ?? '-' }}</strong><br>
      NPP. {{ optional($tugas->penandatanganUser)->npp ?? '-' }}
    </div>
  </div>
</div>

@if($context === 'web')
  </div>
@endif
