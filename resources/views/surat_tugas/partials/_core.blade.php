{{-- resources/views/surat_tugas/partials/_core.blade.php --}}
{{-- ✅ REFACTORED: Security enhanced + konsistensi Web vs PDF --}}

{{-- === GUARD VISIBILITAS TTD/CAP (fallback) === --}}
@php
    if (!isset($showSigns)) {
        $showSigns = isset($tugas)
            ? ($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null)
            : false;
    }
@endphp

@php
    // context: 'pdf' | 'web'
    $context = $context ?? 'web';

    // Flag: tampilkan kop di dalam konten? (PDF = false, Web = true by default)
    $showKopInContent = $showKopInContent ?? $context === 'web';

    // ✅ Data penerima dengan sanitasi
    if (isset($penerimaList) && is_array($penerimaList)) {
        $penerimaList = array_map(fn($n) => sanitize_output($n), $penerimaList);
    } else {
        $penerimaList = ($tugas->penerima ?? collect())
            ->pluck('pengguna.nama_lengkap')
            ->filter()
            ->map(fn($n) => sanitize_output($n))
            ->values()
            ->all();
    }

    // ✅ Status penerima dengan sanitasi
    $roleNames = collect($tugas->penerima ?? [])
        ->map(fn($p) => optional(optional($p->pengguna)->peran)->deskripsi)
        ->filter()
        ->unique()
        ->map(fn($d) => sanitize_output($d))
        ->values()
        ->all();

    $statusDisplay = $roleNames
        ? implode(', ', $roleNames)
        : sanitize_output(\Illuminate\Support\Str::headline($tugas->status_penerima ?? '') ?: '-');

    // ✅ Tugas spesifik dengan sanitasi
    $subNama = data_get($tugas, 'tugasDetail.subTugas.nama');
    $tugasSpesifik = sanitize_output($subNama ?: ($tugas->tugas ?: ($tugas->nama_umum ?: '-')));

    // Preferensi TTD & Cap dengan validasi
    $ttdW_final = filter_var($ttdW ?? 42, FILTER_VALIDATE_INT) ?: 42;
    $capW_final = filter_var($capW ?? 35, FILTER_VALIDATE_INT) ?: 35;
    $capOpacity_final = filter_var($capOpacity ?? 0.95, FILTER_VALIDATE_FLOAT) ?: 0.95;

    // HARDENING: Jika belum boleh tampil, kosongkan aset base64
    if (!$showSigns) {
        $ttdImageB64 = null;
        $capImageB64 = null;
    }
@endphp

{{-- ====================== STYLING & WRAPPER ====================== --}}
@if ($context === 'pdf')
    <style>
        /* PENTING: @page ada di layout PDF. */
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 0;
            font-size: 11pt;
            line-height: 1.6;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .keep-with-next {
            page-break-after: avoid;
        }

        p,
        li {
            orphans: 3;
            widows: 3;
        }

        li {
            page-break-inside: avoid;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .judul {
            text-align: center;
            font-weight: 700;
            font-size: 14pt;
            text-decoration: underline;
        }

        .nomor {
            text-align: center;
            margin: 4mm 0 7mm;
        }

        .isi-surat {
            line-height: 1.6;
        }

        .detail-tugas {
            margin: 6mm 0 6mm 10mm;
        }

        .tembusan-wrapper {
            margin-top: 0;
            page-break-inside: avoid;
        }

        .tembusan-wrapper ol {
            margin: 2mm 0 0 0;
            padding-left: 6mm;
        }

        .tembusan-wrapper li {
            margin-bottom: 1mm;
        }

        /* TTD/Cap — anchor dari bawah tengah (tanpa margin negatif) */
        .ttd-wrapper {
            display: table;
            width: 100%;
            margin-top: 8mm;
            page-break-inside: avoid;
        }

        .ttd-kolom-kiri {
            display: table-cell;
            width: 55%;
            vertical-align: bottom;
        }

        .ttd-kolom-kanan {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            page-break-inside: avoid;
        }

        .ttd-teks {
            text-align: left;
            line-height: 1.5;
            page-break-inside: avoid;
        }

        .ttd-area-sign {
            position: relative;
            height: 35mm;
            margin-top: 6mm;
        }

        .ttd-area-sign .ttd,
        .ttd-area-sign .cap {
            position: absolute;
            bottom: 0;
            left: 50%;
        }

        .ttd-area-sign .ttd {
            transform: translateX(-50%);
            width: var(--ttd-w, 42mm);
        }

        .ttd-area-sign .cap {
            transform: translateX(-25%);
            width: var(--cap-w, 35mm);
            opacity: var(--cap-opacity, .95);
            z-index: 2;
        }
    </style>
@else
    {{-- ====================== CSS UNTUK WEB PREVIEW ====================== --}}
    @php
        // Top padding konten pada .sheet:
        // - Jika kop ikut di konten => 15mm (kop "memakan" ruangnya sendiri)
        // - Jika kop absolut (tidak di konten) => 38mm (simulasi margin header PDF)
        $topPad = $showKopInContent ? '15mm' : '38mm';
    @endphp
    <style>
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            background: #f6f7fb;
        }

        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 8mm auto;
            background: #fff;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            padding: {{ $topPad }} 15mm 25mm 15mm;
            /* ruang atas dinamis */
        }

        .judul {
            text-align: center;
            font-weight: 700;
            font-size: 14pt;
            text-decoration: underline;
            margin-top: 1mm;
        }

        .nomor {
            text-align: center;
            margin: 4mm 0 7mm;
        }

        .isi-surat {
            line-height: 1.6;
        }

        .detail-tugas {
            margin: 6mm 0 6mm 10mm;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 4px 8px;
            vertical-align: top;
        }

        /* Posisi kop di web preview: in-flow (default) atau absolute (opsional) */
        @if ($showKopInContent)
            .kop-wrap {
                position: relative;
                margin-bottom: 10mm;
            }
        @else
            .kop-wrap {
                position: absolute;
                top: 10mm;
                left: 15mm;
                right: 15mm;
            }
        @endif

        .tembusan-wrapper {
            margin-top: 0;
        }

        .tembusan-wrapper ol {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }

        .tembusan-wrapper li {
            margin-bottom: 2px;
        }

        /* TTD/Cap — sama dengan PDF (anchor dari bawah tengah) */
        .ttd-wrapper {
            display: table;
            width: 100%;
            margin-top: 8mm;
        }

        .ttd-kolom-kiri {
            display: table-cell;
            width: 55%;
            vertical-align: bottom;
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
            height: 35mm;
            margin-top: 6mm;
        }

        .ttd-area-sign .ttd,
        .ttd-area-sign .cap {
            position: absolute;
            bottom: 0;
            left: 50%;
        }

        .ttd-area-sign .ttd {
            transform: translateX(-50%);
            width: var(--ttd-w, 42mm);
        }

        .ttd-area-sign .cap {
            transform: translateX(-25%);
            width: var(--cap-w, 35mm);
            opacity: var(--cap-opacity, .95);
            z-index: 2;
        }
    </style>
    <div class="sheet">
@endif

{{-- ====================== HTML KONTEN ====================== --}}

{{-- Kop: tampilkan di web bila $showKopInContent = true. Di PDF kop ada di layout header. --}}
@if ($showKopInContent && !empty($kop))
    <div class="kop-wrap">
        @include('shared._kop_surat', [
            'kop' => $kop,
            'context' => $context,
            'showDivider' => true,
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
                <td style="width:15%;">Nama</td>
                <td style="width:2%;">:</td>
                <td>
                    @if (!empty($penerimaList))
                        @foreach ($penerimaList as $i => $nama)
                            {{ $i + 1 }}. {{ $nama }}<br>
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
                        if (!empty($tugas->semester)) {
                            $waktuList[] = sanitize_output($tugas->semester);
                        }
                        if (!empty($tugas->tahun)) {
                            $tahun = filter_var($tugas->tahun, FILTER_VALIDATE_INT);
                            if ($tahun !== false) {
                                $waktuList[] = $tahun;
                            }
                        }
                        echo !empty($waktuList) ? implode(' ', $waktuList) : '-';
                    @endphp
                </td>
            </tr>
        </table>
    </div>

    Harap melaksanakan tugas dengan sebaik-baiknya dan penuh tanggung jawab serta memberikan laporan setelah selesai
    melaksanakan tugas.
</div>

<div class="ttd-wrapper avoid-break">
    <div class="ttd-kolom-kiri">
        {{-- ✅ TEMBUSAN - aman & fleksibel (JSON / teks biasa) --}}
        @php
            $items = [];

            $rawTembusan = $tugas->tembusan_formatted ?? ($tugas->tembusan ?? '');

            if (is_string($rawTembusan) && trim($rawTembusan) !== '') {
                $text = sanitize_input($rawTembusan, 5000);
                $s = trim($text);

                // Hilangkan tanda kutip luar (kadang hasil Tagify stringified)
                if (strlen($s) >= 2 && $s[0] === '"' && substr($s, -1) === '"') {
                    $s = substr($s, 1, -1);
                }

                // Coba decode JSON
                $decoded = json_decode($s, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $it) {
                        $value = is_array($it)
                            ? $it['value'] ?? ($it['text'] ?? ($it['name'] ?? (string) reset($it)))
                            : (string) $it;
                        $clean = trim(strip_tags($value));
                        if ($clean !== '') {
                            $items[] = sanitize_input($clean, 200);
                        }
                    }
                } else {
                    // Split manual jika formatnya teks biasa
                    foreach (preg_split('/[,\n;]+/', $s) as $raw) {
                        $clean = trim(strip_tags($raw));
                        if ($clean !== '') {
                            $items[] = sanitize_input($clean, 200);
                        }
                    }
                }

                // Filter out benar-benar kosong
                $items = array_filter(array_unique($items), fn($x) => $x !== '');
            }
        @endphp

        @if (count($items) > 0)
            <div class="tembusan-wrapper">
                <p class="keep-with-next"><strong>Tembusan:</strong></p>
                <ol style="margin:0; padding-left:18px">
                    @foreach ($items as $i)
                        <li>{{ $i }}</li>
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
                    $peranId = filter_var($penandatangan->peran_id, FILTER_VALIDATE_INT);
                    if ($peranId === 2) {
                        $jabatanTtd = 'Dekan Fakultas Ilmu Komputer';
                    } elseif ($peranId === 3) {
                        $jabatanTtd = 'a.n. Dekan Fakultas Ilmu Komputer<br>Wakil Dekan Fakultas Ilmu Komputer';
                    }
                }
            @endphp
            {!! $jabatanTtd !!}
        </div>

        {{-- AREA TTD & CAP --}}
        <div class="ttd-area-sign"
            style="--ttd-w: {{ $ttdW_final }}mm; --cap-w: {{ $capW_final }}mm; --cap-opacity: {{ $capOpacity_final }};">
            @if ($showSigns)
                @if (!empty($ttdImageB64))
                    <img class="ttd" src="{{ $ttdImageB64 }}" alt="TTD">
                @endif
                @if (!empty($capImageB64))
                    <img class="cap" src="{{ $capImageB64 }}" alt="Cap">
                @endif
            @endif
        </div>

        <div class="ttd-teks avoid-break">
            <strong>{{ optional($tugas->penandatanganUser)->nama_lengkap ?? '-' }}</strong><br>
            NPP. {{ optional($tugas->penandatanganUser)->npp ?? '-' }}
        </div>
    </div>
</div>

@if ($context === 'web')
    </div> {{-- .sheet --}}
@endif
