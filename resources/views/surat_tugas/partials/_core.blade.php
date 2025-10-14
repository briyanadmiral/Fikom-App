{{-- resources/views/surat_tugas/partials/_core.blade.php --}}

{{-- === GUARD VISIBILITAS TTD/CAP (fallback) === --}}
@php
  if (!isset($showSigns)) {
    $showSigns = isset($tugas)
      ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
      : false;
  }
@endphp

@php
  // context: 'pdf' | 'web'
  $context = $context ?? 'web';

  // Flag kop di dalam konten (OFF di PDF, ON di Web)
  $showKopInContent = $showKopInContent ?? ($context === 'web');

  // Data penerima (list nama)
  $penerimaList = isset($penerimaList) && is_array($penerimaList)
    ? $penerimaList
    : ($tugas->penerima?->pluck('pengguna.nama_lengkap')->filter()->values()->all() ?? []);

  // Status penerima (gabungan peran)
  $roleNames = collect($tugas->penerima ?? [])
    ->map(fn($p) => optional(optional($p->pengguna)->peran)->deskripsi)
    ->filter()->unique()->values()->all();

  $statusDisplay = $roleNames
    ? implode(', ', $roleNames)
    : (\Illuminate\Support\Str::headline($tugas->status_penerima ?? '') ?: '-');

  // Tugas spesifik
  $tugasSpesifik = optional($tugas->subTugas)->nama
    ?? ($tugas->tugas ?? $tugas->nama_umum ?? '-');

  // Preferensi TTD & Cap
  $ttdW_final       = $ttdW       ?? 42;
  $capW_final       = $capW       ?? 35;
  $capOpacity_final = $capOpacity ?? 0.95;

  // HARDENING: Jika belum boleh tampil, kosongkan aset base64
  if (!$showSigns) { $ttdImageB64 = null; $capImageB64 = null; }
@endphp

{{-- ====================== STYLING & WRAPPER ====================== --}}
@if($context === 'pdf')
<style>
  /* PENTING: tidak ada @page di sini; sudah di layout PDF */
  body { font-family: "Times New Roman", Times, serif; margin:0; font-size:11pt; line-height:1.6; }

  /* Utilitas kontrol pecah halaman */
  .avoid-break    { page-break-inside: avoid; }
  .keep-with-next { page-break-after: avoid; }
  p, li           { orphans:3; widows:3; }
  li              { page-break-inside: avoid; }

  table { border-collapse: collapse; width: 100%; }
  td { padding: 4px 8px; vertical-align: top; }

  .judul { text-align:center; font-weight:700; font-size:14pt; text-decoration:underline; }
  .nomor { text-align:center; margin: 4mm 0 7mm; }
  .isi-surat { line-height: 1.6; }
  .detail-tugas { margin: 6mm 0 6mm 10mm; }

  .tembusan-wrapper { margin-top:0; page-break-inside: avoid; }
  .tembusan-wrapper ol { margin: 2mm 0 0 0; padding-left: 6mm; }
  .tembusan-wrapper li { margin-bottom: 1mm; }

  /* TTD */
  .ttd-wrapper { display: table; width: 100%; margin-top: 8mm; page-break-inside: avoid; }
  .ttd-kolom-kiri  { display: table-cell; width:55%; vertical-align: bottom; }
  .ttd-kolom-kanan { display: table-cell; width:45%; vertical-align: top; page-break-inside: avoid; }
  .ttd-teks { text-align:left; line-height:1.5; page-break-inside: avoid; }

  .ttd-area-sign { position:relative; min-height:28mm; margin-top:6mm; text-align:center; }
  .ttd-area-sign .ttd, .ttd-area-sign .cap { display:inline-block; vertical-align:bottom; }
  .ttd-area-sign .ttd { width: var(--ttd-w, 42mm); margin-left: -50mm; margin-bottom: 10mm; }
  .ttd-area-sign .cap { width: var(--cap-w, 35mm); opacity: var(--cap-opacity, 0.95); margin-left: -20mm; margin-bottom: 6mm; position: relative; z-index: 2; }
</style>

{{-- Kop di konten dimatikan saat PDF (sudah di header layout) --}}
@else
{{-- ====================== CSS UNTUK WEB PREVIEW ====================== --}}
<style>
  body { margin:0; font-family:"Times New Roman", Times, serif; background:#f6f7fb; }
  .sheet {
    width:210mm; min-height:297mm; margin:8mm auto; background:#fff; position:relative;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding: 38mm 15mm 25mm 15mm; /* ruang kop & footer web preview */
  }

  .judul { text-align:center; font-weight:700; font-size:22px; text-decoration:underline; margin-top:6px; }
  .nomor { text-align:center; margin:6px 0 20px; }
  .isi-surat { line-height: 1.5; }
  .detail-tugas { margin: 1.2em 0 1.2em 40px; }

  table { border-collapse: collapse; width: 100%; }
  td { padding: 4px 8px; vertical-align: top; }

  /* Posisi kop di web preview */
  .kop-wrap { position:absolute; top:10mm; left:15mm; right:15mm; }

  .tembusan-wrapper { margin-top:0; }
  .tembusan-wrapper ol { margin: 5px 0 0 0; padding-left: 20px; }
  .tembusan-wrapper li { margin-bottom: 2px; }

  .ttd-wrapper { display: table; width: 100%; margin-top: 25px; }
  .ttd-kolom-kiri { display: table-cell; width:55%; vertical-align: bottom; }
  .ttd-kolom-kanan { display: table-cell; width:45%; vertical-align: top; }
  .ttd-teks { text-align: left; line-height: 1.5; }

  .ttd-area-sign { position: relative; min-height: 28mm; margin-top: 6mm; text-align: center; }
  .ttd-area-sign .ttd, .ttd-area-sign .cap { display: inline-block; vertical-align: bottom; }
  .ttd-area-sign .ttd { width: var(--ttd-w, 42mm); margin-left: -50mm; margin-bottom: 10mm; }
  .ttd-area-sign .cap { width: var(--cap-w, 35mm); opacity: var(--cap-opacity, 0.95); margin-left: -20mm; margin-bottom: 6mm; position: relative; z-index: 2; }
</style>
<div class="sheet">
@endif

{{-- ====================== HTML KONTEN ====================== --}}

{{-- Kop: tampilkan di web preview, sembunyikan di PDF (handled by layout) --}}
@if($showKopInContent && !empty($kop))
  <div class="kop-wrap">
    @include('shared._kop_surat', [
      'kop'      => $kop,
      'context'  => $context,
      'showDivider' => true
    ])
  </div>
@endif

<div class="judul keep-with-next">SURAT TUGAS</div>
<div class="nomor">Nomor : {{ $tugas->nomor ?? '-' }}</div>

<div class="isi-surat">
  Dekan Fakultas Ilmu Komputer Universitas Katolik Soegijapranata dengan ini memberikan tugas kepada:
  <div class="detail-tugas">
    <table>
      <tr>
        <td style="width: 15%;">Nama</td>
        <td style="width: 2%;">:</td>
        <td>
          @if(!empty($penerimaList))
            @foreach($penerimaList as $index => $nama)
              {{ $index + 1 }}. {{ $nama }}<br>
            @endforeach
          @else
            —
          @endif
        </td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td>{{ $statusDisplay }}</td>
      </tr>
      <tr>
        <td>Tugas</td>
        <td>:</td>
        <td>{{ $tugasSpesifik }}</td>
      </tr>
      <tr>
        <td>Waktu</td>
        <td>:</td>
        <td>
          @php
            $waktuList = [];
            if (!empty($tugas->semester)) $waktuList[] = $tugas->semester;
            if (!empty($tugas->tahun))    $waktuList[] = $tugas->tahun;
            echo !empty($waktuList) ? implode(' ', $waktuList) : '-';
          @endphp
        </td>
      </tr>
    </table>
  </div>

  Harap melaksanakan tugas dengan sebaik-baiknya dan penuh tanggung jawab serta memberikan laporan setelah selesai melaksanakan tugas.
</div>

<div class="ttd-wrapper avoid-break">
  <div class="ttd-kolom-kiri">
    {{-- TEMBUSAN --}}
    @php
      $text  = $tugas->tembusan_formatted ?? $tugas->tembusan;
      $items = [];
      if (is_string($text) && trim($text) !== '') {
        $s = trim($text);
        if (strlen($s) >= 2 && $s[0] === '"' && substr($s, -1) === '"') { $s = substr($s, 1, -1); }
        $decoded = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          foreach ((array) $decoded as $it) {
            $items[] = trim(is_array($it) ? ($it['value'] ?? $it['text'] ?? $it['name'] ?? (string)reset($it)) : (string)$it);
          }
        } else {
          $items = preg_split('/[,\n;]+/', $s);
        }
      }
      $items = array_values(array_filter(array_unique(array_map('trim', $items))));
    @endphp

    @if(!empty($items))
      <div class="tembusan-wrapper">
        <p class="keep-with-next"><strong>Tembusan:</strong></p>
        <ol style="margin:0; padding-left:18px">
          @foreach($items as $i)
            @if($i !== '')
              <li>{{ $i }}</li>
            @endif
          @endforeach
        </ol>
      </div>
    @endif
  </div>

  <div class="ttd-kolom-kanan">
    <div class="ttd-teks keep-with-next">
      Semarang, {{ \Carbon\Carbon::parse($tugas->tanggal_surat ?? now())->translatedFormat('d F Y') }}<br>
      @php
        $penandatangan = $tugas->penandatanganUser;
        $jabatanTtd = 'Pejabat Penandatangan';
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

    {{-- AREA TTD & CAP --}}
    <div class="ttd-area-sign" style="--ttd-w: {{$ttdW_final}}mm; --cap-w: {{$capW_final}}mm; --cap-opacity: {{$capOpacity_final}};">
      @if($showSigns)
        @if(!empty($ttdImageB64)) <img class="ttd" src="{{ $ttdImageB64 }}" alt="TTD"> @endif
        @if(!empty($capImageB64)) <img class="cap" src="{{ $capImageB64 }}" alt="Cap"> @endif
      @endif
    </div>

    <div class="ttd-teks avoid-break">
      <strong>{{ optional($tugas->penandatanganUser)->nama_lengkap ?? '-' }}</strong><br>
      NPP. {{ optional($tugas->penandatanganUser)->npp ?? '-' }}
    </div>
  </div>
</div>

@if($context === 'web')
</div>
@endif
