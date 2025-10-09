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
  // ==== context: 'pdf' | 'web' (default 'web') ====
  $context = $context ?? 'web';

  // Pakai daftar penerima yang dikirim controller jika ada; kalau tidak, hitung dari relasi
  $penerimaList = isset($penerimaList) && is_array($penerimaList)
    ? $penerimaList
    : ($tugas->penerima?->pluck('pengguna.nama_lengkap')->filter()->values()->all() ?? []);

  $roleNames = collect($tugas->penerima ?? [])
    ->map(fn($p) => optional(optional($p->pengguna)->peran)->deskripsi)
    ->filter()->unique()->values()->all();

  $statusDisplay = $roleNames
    ? implode(', ', $roleNames)
    : (\Illuminate\Support\Str::headline($tugas->status_penerima ?? '') ?: '-');

  $tugasSpesifik = optional($tugas->subTugas)->nama
    ?? ($tugas->tugas ?? $tugas->nama_umum ?? '-');

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
    @page { margin: 2cm; }
    body { font-family: "Times New Roman", Times, serif; margin: 0; font-size: 16px; }
    table { border-collapse: collapse; width: 100%; }
    td { padding: 4px 8px; vertical-align: top; }

    /* Hanya elemen umum, kop ditangani oleh shared/_kop_surat */
    .judul { text-align: center; font-weight: 700; font-size: 22px; text-decoration: underline; }
    .nomor { text-align: center; margin: 6px 0 20px; }
    .isi-surat { line-height: 1.5; }
    .detail-tugas { margin: 1.2em 0 1.2em 40px; }
    
    /* Blok Tembusan (di dalam kolom kiri) */
    .tembusan-wrapper { margin-top: 0; page-break-inside: avoid; }
    .tembusan-wrapper strong { font-size: 1em; }
    .tembusan-wrapper ol { margin: 5px 0 0 0; padding-left: 20px; }
    .tembusan-wrapper li { margin-bottom: 2px; }

    /* Blok TTD */
    .ttd-wrapper { display: table; width: 100%; margin-top: 25px; page-break-inside: avoid; }
    .ttd-kolom-kiri { display: table-cell; width: 55%; vertical-align: bottom; } /* Diubah ke bottom */
    .ttd-kolom-kanan { display: table-cell; width: 45%; vertical-align: top; page-break-inside: avoid; }
    .ttd-teks { text-align: left; page-break-inside: avoid; line-height: 1.5; }
    .ttd-area-sign { position: relative; min-height: 28mm; margin-top: 6mm; text-align: center; }
    .ttd-area-sign .ttd, .ttd-area-sign .cap { display: inline-block; vertical-align: bottom; }

    .ttd-area-sign .ttd { width: var(--ttd-w, 42mm); margin-left: -50mm; margin-bottom: 10mm; }
    .ttd-area-sign .cap { width: var(--cap-w, 35mm); opacity: var(--cap-opacity, 0.95); margin-left: -20mm; margin-bottom: 6mm; position: relative; z-index: 2; }
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

    .kop-wrap{ position:absolute; top:10mm; left:15mm; right:15mm; }
    .kop-wrap + .judul { margin-top: 12px; }
    
    .tembusan-wrapper { margin-top: 0; }
    .tembusan-wrapper strong { font-size: 1em; }
    .tembusan-wrapper ol { margin: 5px 0 0 0; padding-left: 20px; }
    .tembusan-wrapper li { margin-bottom: 2px; }

    .ttd-wrapper { display: table; width: 100%; margin-top: 25px; }
    .ttd-kolom-kiri { display: table-cell; width: 55%; vertical-align: bottom; } /* Diubah ke bottom */
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
@include('shared._kop_surat', [
    'kop'      => $kop ?? null,
    'context'  => $context,
])

<div class="judul">SURAT TUGAS</div>
<div class="nomor">Nomor : {{ $tugas->nomor ?? '-' }}</div>

<div class="isi-surat">
    Dekan Fakultas Ilmu Komputer Universitas Katolik Soegijapranata dengan ini memberikan tugas kepada:
    <div class="detail-tugas">
        <table>
            <tr>
                <td style="width: 15%;">Nama</td>
                <td style="width: 2%;">:</td>
                <td>{{ !empty($penerimaList) ? implode(', ', $penerimaList) : '—' }}</td>
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

<div class="ttd-wrapper">
    <div class="ttd-kolom-kiri">
        {{-- TEMBUSAN --}}
@php
    // Ambil yang paling rapi dulu (hasil preview Tagify), fallback ke kolom tembusan mentah
    $text = $sk->tembusan_formatted ?? $sk->tembusan;

    $items = [];
    if (is_string($text) && trim($text) !== '') {
        $s = trim($text);

        // Kasus string berlapis tanda kutip:  "\"[{\"value\":\"Test\"}]\""  -> buang kutip luar
        if (strlen($s) >= 2 && $s[0] === '"' && substr($s, -1) === '"') {
            $s = substr($s, 1, -1);
        }

        // Coba decode JSON: [{"value":"..."}] / ["nama1","nama2"] / dsb
        $decoded = json_decode($s, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            foreach ((array) $decoded as $it) {
                $items[] = trim(
                    is_array($it) ? ($it['value'] ?? $it['text'] ?? $it['name'] ?? (string)reset($it))
                                  : (string) $it
                );
            }
        } else {
            // Bukan JSON -> split pakai koma / titik koma / newline
            $items = preg_split('/[,\n;]+/', $s);
        }
    }

    // Bersihkan & unik
    $items = array_values(array_filter(array_unique(array_map('trim', $items))));
@endphp

@if(!empty($items))
    <p><strong>Tembusan:</strong></p>
    <ol style="margin:0; padding-left:18px">
        @foreach($items as $i)
            @if($i !== '')
                <li>{{ $i }}</li>
            @endif
        @endforeach
    </ol>
@endif

    </div>
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