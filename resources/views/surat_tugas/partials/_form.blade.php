{{-- resources/views/surat_tugas/partials/_form.blade.php --}}
@php
    /** VARIABEL YANG DIHARAPKAN DARI CONTROLLER:
     * $tugas (nullable, model)
     * $admins (id => nama)
     * $pejabat (collection: id, nama_lengkap, peran->nama)
     * $klasifikasi (collection: id, kode, deskripsi)
     * $taskMaster (array/collection: each { nama, subtugas[] { nama } })
     * $users (collection user untuk modal penerima)
     * $tahun, $semester (opsional untuk create)
     * $bulanRomawi (opsional untuk create; jika null → dihitung)
     * $mode (opsional: 'create' | 'edit' | 'koreksi')
     */
    $isEdit = isset($tugas);
    $mode = $mode ?? ($isEdit ? 'edit' : 'create');

    // Lock structural fields dalam kondisi tertentu
    $lockStructural = $isEdit && in_array($tugas->status_surat ?? '', ['disetujui']); // Hanya lock jika sudah disetujui

    // Route & method
    $formAction = $isEdit
        ? route('surat_tugas.update', ['tugas' => $tugas, 'mode' => request('mode')])
        : route('surat_tugas.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';

    // Helper bulan Romawi jika belum ada
    if (empty($bulanRomawi)) {
        $rom = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulanRomawi = $rom[(int) date('n')] ?? 'X';
    }

    // Prefill nomor untuk edit
    if ($isEdit) {
        $parts = explode('/', $tugas->nomor ?? '');
        $noUrutInit = old('nomor_urut', $parts[0] ?? '001');
        // asumsi struktur: 001/KODE/TG/UNIKA/ROMAWI/TAHUN
        $bulanInit = old('bulan', $tugas->bulan ?? ($parts[4] ?? $bulanRomawi));
        $tahunNomor = old('tahun_nomor', $tugas->tahun ?? ($parts[5] ?? date('Y')));
    } else {
        $noUrutInit = old('nomor_urut'); // ✅ FIX: Don't set default empty string
        $bulanInit = old('bulan', $bulanRomawi);
        $tahunNomor = old('tahun_nomor', date('Y'));
    }

    // Prefill periode
    $tahunPeriode = old('tahun', $isEdit ? $tugas->tahun ?? date('Y') : $tahun ?? date('Y'));
    $semesterPeriode = old('semester', $isEdit ? $tugas->semester ?? 'Ganjil' : $semester ?? 'Ganjil');

    // Prefill Tembusan
    $tembusanCsv = old(
        'tembusan',
        (string) ($isEdit ? str_replace(["\r\n", "\n"], ',', (string) ($tugas->tembusan ?? '')) : ''),
    );

    // ===== Data siap-pakai untuk JS =====
    $usersMap = collect($users)->mapWithKeys(function ($u) {
        return [
            $u->id => [
                'nama_lengkap' => $u->nama_lengkap,
                'peran_id' => $u->peran_id,
            ],
        ];
    });

    if ($isEdit) {
        $initialInternal = old(
            'penerima_internal',
            optional($tugas)->penerima
                ? $tugas->penerima->whereNotNull('pengguna_id')->pluck('pengguna_id')->values()->all()
                : [],
        );
        $initialEksternal = old(
            'penerima_eksternal',
            optional($tugas)->penerima
                ? $tugas->penerima
                    ->whereNull('pengguna_id')
                    ->map(fn($p) => ['nama' => $p->nama_penerima, 'jabatan' => $p->jabatan_penerima])
                    ->values()
                    ->all()
                : [],
        );
    } else {
        $initialInternal = old('penerima_internal', []);
        $initialEksternal = old('penerima_eksternal', []);
    }
@endphp

@once
    @push('styles')
        {{-- Vendor CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
        <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
        {{-- Minor tweaks --}}
        <style>
            .select2-container--bootstrap4 {
                width: 100% !important;
                /* pastikan full width kolom */
            }

            .select2-container--bootstrap4 .select2-selection--single {
                height: calc(2.25rem + 2px) !important;
                line-height: calc(2.25rem + 2px) !important;
            }

            .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
                line-height: calc(2.25rem + 2px) !important;
                padding-left: .75rem;
                /* biar teks sejajar dgn input lain */
            }

            .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
                height: calc(2.25rem + 2px) !important;
                top: 0;
                /* center vertikal */
            }

            #penerima-table thead th {
                text-align: center;
                vertical-align: middle
            }

            #penerima-table tbody td:first-child {
                text-align: center
            }

            .ck-editor__editable_inline {
                min-height: 250px
            }

            /* --- TEMBUSAN --- */
            .tembusan-wrap {
                border: 1px solid #e9ecef;
                border-radius: .5rem;
                overflow: hidden
            }

            .tembusan-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: .75rem 1rem;
                background: linear-gradient(90deg, #5c7cfa 0%, #845ef7 100%);
                color: #fff
            }

            .tembusan-body {
                padding: 1rem;
                background: #fff
            }

            .tembusan-preview {
                background: #f8f9ff;
                border: 1px dashed #cdd5ff;
                border-radius: .5rem;
                padding: 1rem
            }

            .tembusan-preview h6 {
                font-weight: 700;
                color: #3b5bdb;
                margin-bottom: .5rem
            }

            .tembusan-tools .btn {
                margin-left: .5rem
            }

            .tagify__tag {
                font-weight: 600
            }

            .tagify__input {
                min-width: 140px
            }

            /* Task preview */
            #task-preview {
                background: #f8f9fa;
                border: 1px dashed #ced4da;
                border-radius: .25rem;
                padding: 1.5rem;
                min-height: 158px;
                display: flex;
                align-items: center;
                justify-content: center
            }

            #task-preview.has-content {
                align-items: flex-start;
                justify-content: flex-start
            }

            #task-preview .placeholder-text {
                color: #6c757d;
                font-style: italic
            }

            #task-preview .preview-title {
                font-weight: 600;
                color: #007bff
            }

            #task-preview .preview-content {
                font-size: 1.1rem
            }

            /* ===============================
   * FIX LAYOUT TEMBUSAN / TAGIFY (REVISI FINAL)
   * ===============================*/
  
  /* Container Utama Tagify */
  .tembusan-body .tagify {
      width: 100%;
      height: auto !important;  /* Wajib auto agar memanjang ke bawah */
      min-height: 44px;
      
      background: #fff;
      border: 1px solid #ced4da;
      border-radius: .6rem;
      padding: 4px 8px; /* Padding di dalam kotak putih */

      /* Layout: Gunakan Flexbox + Wrap */
      display: flex;
      align-items: center; /* Pastikan item sejajar vertikal (tengah) */
      flex-wrap: wrap;     /* Wajib wrap agar turun baris */
  }

  /* Style Tag Individual */
  .tembusan-body .tagify__tag {
      /* Gunakan MARGIN, jangan GAP. Ini lebih stabil untuk Tagify */
      margin: 3px 6px 3px 0 !important; 
  }
  
  /* Area Input (Tempat Mengetik) */
  .tembusan-body .tagify__input {
      margin: 3px 0 !important;
      padding: 0;
      
      /* Penting: Input harus mengisi sisa ruang (flex-grow) 
         dan punya lebar minimal agar tidak gepeng */
      flex-grow: 1; 
      min-width: 150px; 
      
      line-height: 2rem; /* Tinggi baris disesuaikan agar placeholder pas */
      display: inline-block;
  }

  /* Fix untuk Placeholder "Misal: Yth..." agar tidak tertutup */
  .tembusan-body .tagify__input::before {
      line-height: 2rem !important;
      position: static !important; /* Hindari absolute positioning */
      display: inline-block;
      color: #adb5bd; /* Warna text placeholder */
  }

  /* Dropdown suggestions */
  .tembusan-body .tagify__dropdown {
      z-index: 1060;
  }
        </style>
    @endpush
@endonce

<form id="tugasForm" action="{{ $formAction }}" method="POST" novalidate>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        {{-- Kiri --}}
        <div class="col-lg-8">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-dasar"
                                role="tab"><i class="fas fa-file-alt mr-2"></i>Informasi Dasar</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-isi" role="tab"><i
                                    class="fas fa-tasks mr-2"></i>Detail Tugas</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pelaksanaan"
                                role="tab"><i class="fas fa-calendar-alt mr-2"></i>Pelaksanaan</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- TAB 1: INFORMASI DASAR --}}
                        <div class="tab-pane fade show active" id="tab-dasar" role="tabpanel">
                            {{-- Row 1: Nama Pembuat & Asal Surat --}}
                            <div class="row">
                                {{-- Nama Pembuat --}}
                                <div class="col-md-6 form-group">
                                    <label for="pembuat_id">Nama Pembuat</label>
                                    @php
                                        $pembuatIdOld = old(
                                            'pembuat_id',
                                            $isEdit ? $tugas->nama_pembuat ?? $tugas->dibuat_oleh : Auth::id(),
                                        );
                                        $pembuatLabel =
                                            $admins[$pembuatIdOld] ?? (optional($tugas->pembuat)->nama_lengkap ?? '');
                                    @endphp
                                    @if ($isEdit)
                                        <input type="text" id="pembuat_id_display" class="form-control"
                                            value="{{ $pembuatLabel }}" readonly>
                                        <input type="hidden" name="pembuat_id" value="{{ $pembuatIdOld }}">
                                        {{-- kirim ID, bukan label --}}
                                        <input type="hidden" name="nama_pembuat" id="nama_pembuat_hidden"
                                            value="{{ $pembuatIdOld }}">
                                    @else
                                        <select id="pembuat_id" name="pembuat_id" class="form-control select2bs4"
                                            required>
                                            @foreach ($admins as $id => $nama)
                                                <option value="{{ $id }}" @selected($pembuatIdOld == $id)>
                                                    {{ $nama }}</option>
                                            @endforeach
                                        </select>
                                        {{-- kirim ID, bukan label --}}
                                        <input type="hidden" name="nama_pembuat" id="nama_pembuat_hidden"
                                            value="">
                                    @endif
                                </div>

                                {{-- Asal Surat (Pejabat) --}}
                                <div class="col-md-6 form-group">
                                    <label for="asal_surat_id">Asal Surat (Pejabat)</label>
                                    @php
                                        $asalIdOld = old('asal_surat_id', $isEdit ? $tugas->asal_surat ?? null : null);
                                        $asalLabel =
                                            optional($pejabat->firstWhere('id', $asalIdOld))->nama_lengkap ??
                                            ($isEdit ? optional($tugas->asalSurat)->nama_lengkap ?? '' : '');
                                    @endphp
                                    @if ($isEdit)
                                        <input type="text" id="asal_surat_id_display" class="form-control"
                                            value="{{ $asalLabel }}" readonly>
                                        <input type="hidden" name="asal_surat_id" value="{{ $asalIdOld }}">
                                        {{-- kirim ID, bukan label --}}
                                        <input type="hidden" name="asal_surat" id="asal_surat_hidden"
                                            value="{{ $asalIdOld }}">
                                    @else
                                        <select id="asal_surat_id" name="asal_surat_id" class="form-control select2bs4"
                                            required>
                                            <option value="" disabled {{ $asalIdOld ? '' : 'selected' }}>-- Pilih
                                                Pejabat --</option>
                                            @foreach ($pejabat as $p)
                                                <option value="{{ $p->id }}" @selected($asalIdOld == $p->id)>
                                                    {{ $p->nama_lengkap }} ({{ $p->peran->nama }})
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- kirim ID, bukan label --}}
                                        <input type="hidden" name="asal_surat" id="asal_surat_hidden" value="">
                                    @endif
                                </div>
                            </div>

                            {{-- Row 2: Tanggal Surat --}}
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="required">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat"
                                        class="form-control @error('tanggal_surat') is-invalid @enderror"
                                        value="{{ old('tanggal_surat', $isEdit ? $tugas->tanggal_surat?->format('Y-m-d') ?? date('Y-m-d') : date('Y-m-d')) }}"
                                        {{ $lockStructural ? 'readonly' : '' }} required>
                                    @error('tanggal_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Tanggal surat akan digunakan untuk generate
                                        nomor surat otomatis
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    {{-- Kolom kosong untuk alignment --}}
                                </div>
                            </div>

                            {{-- Row 3: Judul Umum Surat --}}
                            <div class="form-group">
                                <label for="nama_umum">Judul Umum Surat <span class="text-danger">*</span></label>
                                <input type="text" id="nama_umum" name="nama_umum"
                                    class="form-control @error('nama_umum') is-invalid @enderror"
                                    placeholder="Contoh: Penugasan Panitia Seminar AI"
                                    value="{{ old('nama_umum', $isEdit ? $tugas->nama_umum : '') }}"
                                    autocomplete="off" onkeydown="return true;" required>
                                @error('nama_umum')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Row 4: Nomor Surat (Komponen) --}}
                            <div class="form-group">
                                <label>Nomor Surat (Komponen)</label>
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <label class="small text-muted">Klasifikasi</label>
                                        @php
                                            // === Konfigurasi komponen klasifikasi (untuk modal) ===
                                            $klModalId = 'modalKlasifikasi';
                                            $klHiddenId = 'klasifikasi_surat_id';
                                            $klKodeId = 'klasifikasi_kode';
                                            $klDisplayId = 'klasifikasi_display';

                                            $selectedKlasId = old(
                                                'klasifikasi_surat_id',
                                                $isEdit ? $tugas->klasifikasi_surat_id : null,
                                            );
                                            $selectedKlas = optional($klasifikasi->firstWhere('id', $selectedKlasId));
                                            $selectedKode = $selectedKlas->kode ?? '';
                                            $selectedLabel = $selectedKlas
                                                ? trim(
                                                    ($selectedKlas->kode ?? '') .
                                                        ' - ' .
                                                        ($selectedKlas->deskripsi ?? ($selectedKlas->nama ?? '')),
                                                )
                                                : '';
                                        @endphp

                                        <div class="input-group">
                                            <input id="{{ $klDisplayId }}" type="text" class="form-control"
                                                value="{{ $selectedLabel }}" placeholder="Klik tombol Pilih"
                                                readonly>
                                            <div class="input-group-append">
                                                @unless ($lockStructural)
                                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                                        data-target="#{{ $klModalId }}">
                                                        <i class="fas fa-search"></i> Pilih
                                                    </button>
                                                @endunless
                                            </div>
                                        </div>

                                        {{-- nilai yang dikirim ke server --}}
                                        <input type="hidden" name="klasifikasi_surat_id" id="{{ $klHiddenId }}"
                                            value="{{ $selectedKlasId }}">
                                        {{-- hanya untuk menyusun nomor --}}
                                        <input type="hidden" id="{{ $klKodeId }}" value="{{ $selectedKode }}">

                                        @error('klasifikasi_surat_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror

                                        {{-- MODAL PILIH KLASIFIKASI --}}
                                        @include('surat_tugas.partials._modal_klasifikasi', [
                                            'modalId' => $klModalId,
                                            'hiddenId' => $klHiddenId,
                                            'displayId' => $klDisplayId,
                                            'kodeTargetId' => $klKodeId,
                                            'items' => $klasifikasi,
                                        ])
                                    </div>

                                    <div class="col-md-3">
                                        <label for="bulan" class="small text-muted">Bulan (Romawi)</label>
                                        <input type="text" id="bulan" name="bulan"
                                            class="form-control text-center" value="{{ $bulanInit }}"
                                            {{ $lockStructural ? 'readonly' : '' }}>
                                        @if ($lockStructural)
                                            <input type="hidden" name="bulan" value="{{ $bulanInit }}">
                                        @endif
                                    </div>

                                    <div class="col-md-2">
                                        <label class="small text-muted">Tahun</label>
                                        <input type="number" id="tahun-nomor" name="tahun_nomor"
                                            class="form-control text-center" value="{{ $tahunNomor }}"
                                            {{ $lockStructural ? 'readonly' : '' }}>
                                        @if ($lockStructural)
                                            <input type="hidden" name="tahun_nomor" value="{{ $tahunNomor }}">
                                        @endif
                                    </div>

                                    <div class="col-md-2">
                                        <label class="small text-muted">No. Urut</label>
                                        <input type="text" id="nomor_urut" name="nomor_urut"
                                            class="form-control text-center" value="{{ $noUrutInit }}"
                                            {{ $lockStructural ? 'readonly' : (!$isEdit ? 'readonly' : '') }}>
                                        @if ($lockStructural && $noUrutInit)
                                            <input type="hidden" name="nomor_urut" value="{{ $noUrutInit }}">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Row 5: Nomor Surat Lengkap (Preview) --}}
                            <div class="form-group">
                                <label>Nomor Surat Lengkap (Otomatis)</label>
                                <div class="input-group">
                                    <input type="text" id="nomor_surat_lengkap_display"
                                        class="form-control font-weight-bold"
                                        style="background:#e9ecef;cursor:not-allowed;letter-spacing:1px"
                                        value="..." readonly>
                                    <div class="input-group-append">
                                        @if (!$isEdit)
                                            <button class="btn btn-outline-primary" type="button"
                                                id="btn-reserve-nomor" title="Siapkan Nomor" disabled>
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="btn-reset-nomor" title="Reset Nomor">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    @if ($isEdit)
                                        Nomor disusun ulang dari komponen Kode/Bulan/Tahun/Urut. Isi "Nomor Surat
                                        Manual" untuk override.
                                    @else
                                        Klik <i class="fas fa-sync"></i> untuk menyiapkan nomor urut berikutnya dari
                                        server. Isi "Nomor Surat Manual" untuk override.
                                    @endif
                                </small>
                                <input type="hidden" id="nomor_surat_lengkap_hidden" name="nomor"
                                    value="{{ old('nomor', $isEdit ? $tugas->nomor : '') }}">
                            </div>

                            {{-- Row 6: Nomor Surat Manual (Override) --}}
                            <div class="form-group">
                                <label for="no_surat_manual">Nomor Surat Manual (Opsional)</label>
                                <input type="text" name="no_surat_manual" id="no_surat_manual"
                                    class="form-control @error('no_surat_manual') is-invalid @enderror"
                                    placeholder="Isi jika nomor surat sudah dibuat secara manual"
                                    value="{{ old('no_surat_manual', $isEdit ? $tugas->no_surat_manual : '') }}">
                                <small class="form-text text-muted">
                                    Jika diisi, nomor otomatis akan diabaikan.
                                </small>
                                @error('no_surat_manual')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- === TEMBUSAN === --}}
                            @php
                                $tembusanPresets = [
                                    'Yth. Rektor',
                                    'Yth. Wakil Rektor I',
                                    'Yth. Wakil Rektor II',
                                    'Dekan Fakultas Ilmu Komputer',
                                    'BAAK',
                                    'BAUK',
                                    'BAK',
                                    'Kepala Program Studi Sistem Informasi',
                                    'Unit Kepegawaian',
                                    'Arsip',
                                ];
                            @endphp
                            <div class="form-group col-12 p-0">
                                <div class="tembusan-wrap">
                                    <div class="tembusan-head">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-copy mr-2"></i><strong>Tembusan</strong>
                                            <span class="ml-2 small" style="opacity:.9">(opsional)</span>
                                        </div>
                                        <div class="tembusan-tools">
                                            <button type="button" class="btn btn-sm btn-light" id="btnPasteTembusan"
                                                title="Tempel daftar dari clipboard">
                                                <i class="fas fa-clipboard-list mr-1"></i>Tempel Daftar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light" id="btnClearTembusan"
                                                title="Kosongkan">
                                                <i class="fas fa-eraser mr-1"></i>Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="tembusan-body">
                                        <label for="tembusan-input" class="mb-1">Ketik & tekan <kbd>Enter</kbd> atau
                                            <kbd>,</kbd> untuk membuat tag</label>
                                        <input id="tembusan-input" name="tembusan" value="{{ $tembusanCsv }}"
                                            class="form-control" placeholder="Misal: Yth. Rektor, BAAK, Arsip">
                                        @error('tembusan')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror

                                        <div class="custom-control custom-switch mt-3">
                                            <input type="checkbox" class="custom-control-input"
                                                id="tembusanShowTitle" checked>
                                            <label class="custom-control-label" for="tembusanShowTitle">Cetak judul
                                                <em>"Tembusan Yth:"</em></label>
                                        </div>

                                        <div class="tembusan-preview mt-3" id="tembusanPreview">
                                            <h6 class="mb-2"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>
                                            <div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="tembusan_formatted" id="tembusan_formatted">
                            </div>

                            {{-- PERIODE --}}
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="tahun-periode">Tahun Periode</label>
                                    <input type="number" id="tahun-periode" name="tahun" class="form-control"
                                        value="{{ $tahunPeriode }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="semester-periode">Semester Periode</label>
                                    <select name="semester" id="semester-periode" class="form-control">
                                        <option value="Ganjil" @selected($semesterPeriode == 'Ganjil')>Ganjil</option>
                                        <option value="Genap" @selected($semesterPeriode == 'Genap')>Genap</option>
                                    </select>
                                </div>
                            </div>

                            {{-- STATUS PENERIMA --}}
                            <div class="form-group">
                                <label for="status_penerima_display">Status Penerima (Otomatis)</label>
                                <input type="text" id="status_penerima_display" class="form-control"
                                    style="background:#e9ecef;cursor:not-allowed" value="Belum ada penerima" readonly>
                                <input type="hidden" name="status_penerima" id="status_penerima_hidden">
                            </div>
                        </div>

                        {{-- TAB 2: DETAIL TUGAS --}}
                        <div class="tab-pane fade" id="tab-isi" role="tabpanel">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="jenis_tugas">Jenis Tugas</label>
                                        @if ($lockStructural)
                                            {{-- Mode locked: readonly display --}}
                                            <input type="text" class="form-control"
                                                value="{{ old('jenis_tugas', $isEdit ? $tugas->jenis_tugas ?? null : null) }}"
                                                readonly style="background:#e9ecef;cursor:not-allowed;">
                                            <input type="hidden" name="jenis_tugas" id="jenis_tugas"
                                                value="{{ old('jenis_tugas', $isEdit ? $tugas->jenis_tugas ?? null : null) }}">
                                        @else
                                            <select name="jenis_tugas" id="jenis_tugas"
                                                class="form-control select2bs4">
                                                <option value="" disabled
                                                    {{ old('jenis_tugas', $isEdit ? $tugas->jenis_tugas ?? null : null) ? '' : 'selected' }}>
                                                    -- Pilih Jenis...--</option>
                                                @foreach ($taskMaster as $jt)
                                                    <option value="{{ $jt->nama }}" @selected(old('jenis_tugas', $isEdit ? $tugas->jenis_tugas ?? null : null) == $jt->nama)>
                                                        {{ $jt->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="tugas">Tugas</label>
                                        @if ($lockStructural)
                                            {{-- Mode locked: readonly display --}}
                                            <input type="text" class="form-control"
                                                value="{{ old('tugas', $isEdit ? $tugas->tugas ?? null : null) }}"
                                                readonly style="background:#e9ecef;cursor:not-allowed;">
                                            <input type="hidden" name="tugas" id="tugas"
                                                value="{{ old('tugas', $isEdit ? $tugas->tugas ?? null : null) }}">
                                        @else
                                            <select name="tugas" id="tugas" class="form-control select2bs4"
                                                {{ old('tugas', $isEdit ? $tugas->tugas ?? null : null) ? '' : 'disabled' }}>
                                                <option value="">
                                                    {{ old('tugas', $isEdit ? $tugas->tugas ?? null : null) ? '' : '-- Pilih Tugas... --' }}
                                                </option>
                                            </select>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label>Pratinjau Pilihan Tugas</label>
                                    <div id="task-preview">
                                        <span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat
                                            pratinjau.</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <div class="form-group">
                                <label for="redaksi_pembuka">Redaksi Pembuka</label>
                                <textarea name="redaksi_pembuka" id="redaksi_pembuka" class="form-control" rows="3"
                                    placeholder="Contoh: Sehubungan dengan akan diselenggarakannya acara ...">{{ old('redaksi_pembuka', $isEdit ? $tugas->redaksi_pembuka : '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="penutup">Redaksi Penutup</label>
                                <textarea name="penutup" id="penutup" class="form-control" rows="3"
                                    placeholder="Contoh: Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.">{{ old('penutup', $isEdit ? $tugas->penutup : '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="detail_tugas_editor">Isi / Detail Rincian Tugas (Opsional)</label>
                                <textarea name="detail_tugas" id="detail_tugas_editor">{{ old('detail_tugas', $isEdit ? $tugas->detail_tugas : '') }}</textarea>
                            </div>
                        </div>

                        {{-- TAB 3: PELAKSANAAN --}}
                        <div class="tab-pane fade" id="tab-pelaksanaan" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="waktu_mulai">Waktu Mulai</label>
                                    <input type="datetime-local" id="waktu_mulai" name="waktu_mulai"
                                        class="form-control"
                                        value="{{ old('waktu_mulai', $isEdit && $tugas->waktu_mulai ? $tugas->waktu_mulai->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="waktu_selesai">Waktu Selesai</label>
                                    <input type="datetime-local" id="waktu_selesai" name="waktu_selesai"
                                        class="form-control"
                                        value="{{ old('waktu_selesai', $isEdit && $tugas->waktu_selesai ? $tugas->waktu_selesai->format('Y-m-d\TH:i') : now()->addHours(2)->format('Y-m-d\TH:i')) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tempat">Tempat Pelaksanaan</label>
                                <input type="text" id="tempat" name="tempat" class="form-control"
                                    placeholder="Cth: Ruang Teater, Gedung Thomas Aquinas Lantai 3"
                                    value="{{ old('tempat', $isEdit ? $tugas->tempat : '') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan --}}
        <div class="col-lg-4">
            <div class="card card-success card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-2"></i>Penerima Tugas</h3>
                </div>
                <div class="card-body">
                    <div id="penerima-list-container">
                        <p class="text-muted text-center py-3" id="penerima-placeholder">Belum ada penerima dipilih.
                        </p>
                        <ul id="penerima-list" class="list-group list-group-flush"></ul>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-sm btn-info btn-block mb-2" data-toggle="modal"
                        data-target="#penerimaModal">
                        <i class="fas fa-user-check mr-2"></i> Pilih dari Pengguna Sistem
                    </button>
                    <button type="button" class="btn btn-sm btn-success btn-block" data-toggle="modal"
                        data-target="#penerimaEksternalModal">
                        <i class="fas fa-user-plus mr-2"></i> Tambah Penerima Luar (Manual)
                    </button>
                </div>
            </div>

            <div class="card card-info card-outline position-sticky" style="top:80px">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-paper-plane mr-2"></i>Aksi & Persetujuan
                    </h3>
                </div>
                <div class="card-body">
                    {{-- Pilih Penandatangan --}}
                    <div class="form-group">
                        <label for="penandatangan_id">Pilih Penandatangan <span class="text-danger">*</span></label>
                        <select name="penandatangan_id" id="penandatangan_id" class="form-control select2bs4"
                            {{ $lockStructural ? 'disabled' : '' }} required>
                            <option value="" disabled
                                {{ old('penandatangan_id', $isEdit ? $tugas->penandatangan : null) ? '' : 'selected' }}>
                                -- Pilih Penandatangan --
                            </option>
                            @foreach ($pejabat as $p)
                                <option value="{{ $p->id }}" @selected(old('penandatangan_id', $isEdit ? $tugas->penandatangan : null) == $p->id)>
                                    {{ $p->nama_lengkap }} ({{ $p->peran->nama }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <hr>

                    {{-- TOMBOL AKSI --}}
                    @php
                        $userRole = Auth::user()->peran_id ?? null;
                        $isPembuat = $userRole == 1;
                        $isPenandatangan = in_array($userRole, [2, 3]);
                    @endphp

                    @if ($mode === 'koreksi' && $isPenandatangan)
                        {{-- ✅ FIXED: Submit form dulu, baru redirect ke approve --}}
                        <button type="submit" name="action" value="save_and_review"
                            class="btn btn-block btn-info mb-2 btn-submit-action" data-title="Simpan dan Tinjau?"
                            data-text="Perubahan akan disimpan, lalu Anda akan dibawa ke halaman persetujuan."
                            data-icon="question" data-confirm-text="Ya, Simpan dan Tinjau"
                            data-confirm-color="#17a2b8" {{ $lockStructural ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-2"></i>Simpan dan Tinjau
                        </button>

                        <button type="submit" name="action" value="draft"
                            class="btn btn-block btn-secondary mb-2 btn-submit-action"
                            data-title="Simpan sebagai Draft?" data-text="Koreksi akan disimpan sebagai draft."
                            data-icon="info" data-confirm-text="Ya, Simpan Draft" data-confirm-color="#6c757d"
                            {{ $lockStructural ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-2"></i>Simpan Draft
                        </button>
                    @elseif ($mode === 'koreksi' && $isPembuat)
                        {{-- ✅ Pembuat di mode koreksi → simpan koreksi & ajukan ulang --}}
                        <button type="submit" name="action" value="draft"
                            class="btn btn-block btn-secondary mb-2 btn-submit-action" data-title="Simpan Koreksi?"
                            data-text="Koreksi akan disimpan sebagai draft." data-icon="info"
                            data-confirm-text="Ya, Simpan Koreksi" data-confirm-color="#6c757d"
                            {{ $lockStructural ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-2"></i>Simpan Koreksi
                        </button>

                        <button type="submit" name="action" value="submit"
                            class="btn btn-block btn-primary btn-submit-action" data-title="Ajukan Ulang Surat?"
                            data-text="Surat akan dikirim ke penandatangan untuk disetujui." data-icon="question"
                            data-confirm-text="Ya, Ajukan Sekarang" data-confirm-color="#007bff"
                            {{ $lockStructural ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle mr-2"></i>Ajukan Ulang
                        </button>
                    @elseif ($isEdit && $isPembuat)
                        {{-- ✅ Pembuat edit normal → draft & submit --}}
                        <button type="submit" name="action" value="draft"
                            class="btn btn-block btn-secondary mb-2 btn-submit-action"
                            data-title="Simpan sebagai Draft?"
                            data-text="Surat akan disimpan sebagai draft dan belum diajukan." data-icon="info"
                            data-confirm-text="Ya, Simpan Draft" data-confirm-color="#6c757d"
                            {{ $lockStructural ? 'disabled' : '' }}>
                            <i class="fas fa-save mr-2"></i>Simpan Draft
                        </button>

                        <button type="submit" name="action" value="submit"
                            class="btn btn-block btn-primary btn-submit-action" data-title="Ajukan Ulang Surat?"
                            data-text="Surat akan dikirim ke penandatangan untuk disetujui." data-icon="question"
                            data-confirm-text="Ya, Ajukan Sekarang" data-confirm-color="#007bff"
                            {{ $lockStructural ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle mr-2"></i>Ajukan Ulang
                        </button>
                    @elseif (!$isEdit)
                        {{-- ✅ Create baru → draft & submit --}}
                        <button type="submit" name="action" value="draft"
                            class="btn btn-block btn-secondary mb-2 btn-submit-action"
                            data-title="Simpan sebagai Draft?"
                            data-text="Surat akan disimpan sebagai draft dan belum diajukan." data-icon="info"
                            data-confirm-text="Ya, Simpan Draft" data-confirm-color="#6c757d">
                            <i class="fas fa-save mr-2"></i>Simpan Draft
                        </button>

                        <button type="submit" name="action" value="submit"
                            class="btn btn-block btn-primary btn-submit-action" data-title="Ajukan Surat?"
                            data-text="Surat akan dikirim ke penandatangan untuk disetujui." data-icon="question"
                            data-confirm-text="Ya, Ajukan Sekarang" data-confirm-color="#007bff">
                            <i class="fas fa-check-circle mr-2"></i>Ajukan
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</form>


{{-- MODAL PENERIMA EKSTERNAL --}}
<div class="modal fade" id="penerimaEksternalModal" tabindex="-1" role="dialog"
    aria-labelledby="penerimaEksternalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penerimaEksternalModalLabel"><i class="fas fa-user-plus mr-2"></i>Tambah
                    Penerima Manual</h5>
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="form-penerima-eksternal" onsubmit="return false;">
                    <div class="form-group"><label for="nama_eksternal">Nama Lengkap</label><input type="text"
                            id="nama_eksternal" class="form-control" required></div>
                    <div class="form-group">
                        <label for="jabatan_eksternal">Jabatan / Posisi</label>
                        <select id="jabatan_eksternal" class="form-control" required>
                            <option value="" disabled selected>-- Pilih Posisi --</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Dosen Luar">Dosen Luar</option>
                            <option value="Umum">Umum</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="simpanPenerimaEksternal">Tambah ke Daftar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PENERIMA INTERNAL --}}
<div class="modal fade" id="penerimaModal" tabindex="-1" role="dialog" aria-labelledby="penerimaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penerimaModalLabel"><i class="fas fa-user-check mr-2"></i>Pilih Penerima
                    Tugas</h5>
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table id="penerima-table" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:5%;"><input type="checkbox" id="select-all-penerima"></th>
                            <th style="width:30%;">Nama Lengkap</th>
                            <th style="width:30%;">Email</th>
                            <th style="width:20%;">Jabatan</th>
                            <th style="width:15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" class="penerima-checkbox" value="{{ $user->id }}"
                                        data-nama="{{ $user->nama_lengkap }}" data-peran-id="{{ $user->peran_id }}"
                                        data-jabatan="{{ $user->jabatan ?: $user->peran->deskripsi }}"
                                        data-status-deskripsi="{{ $user->peran->deskripsi }}">
                                </td>
                                <td>{{ $user->nama_lengkap }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->jabatan ?: '-' }}</td>
                                <td><span class="badge badge-info">{{ $user->peran->deskripsi }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i
                        class="fas fa-times mr-2"></i>Batal</button>
                <button type="button" class="btn btn-primary" id="simpanPenerima"><i
                        class="fas fa-check mr-2"></i>Simpan Pilihan</button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        {{-- Vendor JS --}}
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
        <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        $(function() {
            // ====== Safe escape util (untuk mencegah XSS pada preview) ======
            window._ = window._ || {};
            if (!_.escape) {
                _.escape = (s) =>
                    String(s ?? '').replace(/[&<>"'=\/`]/g, (c) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '=': '&#x3D;',
                    '`': '&#x60;',
                    } [c]));
            }

            // ====== STATE & ELEM ======
            const isEdit = @json($isEdit);
            const tugasForm = $('#tugasForm');
            const $disp = $('#nomor_surat_lengkap_display');
            const $hidden = $('#nomor_surat_lengkap_hidden');
            const $urut = $('#nomor_urut');
            const $manual = $('#no_surat_manual');
            let isSubmitting = false;
            let clickedAction = null;

            // ====== Select2 ======
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // ====== Datetime guard ======
            $('#waktu_selesai').on('change', function() {
                const mulai = $('#waktu_mulai').val(),
                    selesai = $(this).val();
                if (mulai && selesai && new Date(selesai) < new Date(mulai)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Waktu Tidak Valid',
                        text: 'Waktu selesai harus setelah waktu mulai'
                    });
                    $(this).val('');
                }
            });
            $('#waktu_mulai').on('change', function() {
                $('#waktu_selesai').attr('min', $(this).val());
            });

            // ====== DataTable ======
            let table;
            if ($.fn.DataTable.isDataTable('#penerima-table')) {
                table = $('#penerima-table').DataTable();
            } else {
                table = $('#penerima-table').DataTable({
                    responsive: true,
                    lengthChange: true,
                    autoWidth: false,
                    stateSave: true,
                    order: [],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_-_END_ dari _TOTAL_',
                        zeroRecords: 'Tidak ditemukan',
                        paginate: {
                            next: '>>',
                            previous: '<<'
                        }
                    },
                    columnDefs: [{
                        orderable: false,
                        targets: 0
                    }]
                });
            }

            // Select all (halaman aktif)
            $('#select-all-penerima').on('change', function() {
                const checked = this.checked;
                table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox').prop('checked', checked);
            });

            // Sync header checkbox
            $('#penerima-table').on('change', '.penerima-checkbox', function() {
                const totalOnPage = table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox').length;
                const checkedOnPage = table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox:checked').length;
                $('#select-all-penerima').prop('checked', totalOnPage === checkedOnPage && totalOnPage > 0);
            });

            // Sync centang saat redraw
            table.on('draw.dt', function() {
                $('#select-all-penerima').prop('checked', false);
                table.rows({
                    page: 'current'
                }).nodes().to$().find('.penerima-checkbox').each(function() {
                    const id = $(this).val();
                    $(this).prop('checked', !!penerimaState.internal[id]);
                });
            });

            // Pre-check saat modal show
            $('#penerimaModal').on('shown.bs.modal', function() {
                table.rows().every(function() {
                    $(this.node()).find('.penerima-checkbox').each(function() {
                        const id = $(this).val();
                        $(this).prop('checked', !!penerimaState.internal[id]);
                    });
                });
            });

            // ====== CKEditor (secure) ======
            const editorEl = document.querySelector('#detail_tugas_editor');
            if (editorEl && window.ClassicEditor) {
                ClassicEditor.create(editorEl, {
                    toolbar: {
                        items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList',
                            'numberedList', '|', 'undo', 'redo'
                        ],
                        shouldNotGroupWhenFull: true
                    },
                    htmlSupport: {
                        disallow: [{
                            name: 'script'
                        }, {
                            name: 'iframe'
                        }, {
                            attributes: [{
                                key: /^on.*$/,
                                value: true
                            }]
                        }]
                    },
                    link: {
                        addTargetToExternalLinks: true,
                        decorators: {
                            isExternal: {
                                mode: 'automatic',
                                callback: url => url.startsWith('http'),
                                attributes: {
                                    rel: 'noopener noreferrer'
                                }
                            }
                        }
                    }
                }).catch(console.error);
            }

            // ====== NOMOR SURAT ======
            function extractNoUrut(nomor) {
                const m = (nomor || '').trim().match(/^([0-9]{1,4}[A-Z]?)/);
                return m ? m[1] : '';
            }

            function markNomorStale() {
                $disp.val('(belum disiapkan)');
                $hidden.val('');
                if (!isEdit) $urut.val('');
            }

            function buildNomorFromParts() {
    const noUrut = String($('#nomor_urut').val() || '').padStart(3, '0');
    const kode = ($('#klasifikasi_kode').val() || '').trim() || '...';
    const bulan = ($('#bulan').val() || '').toUpperCase() || '...';
    const tahun = $('#tahun-nomor').val() || '....';
    
    // ✅ DEBUG
    console.log('=== BUILD NOMOR ===');
    console.log('No Urut:', noUrut);
    console.log('Kode dari #klasifikasi_kode:', kode);
    console.log('Bulan:', bulan);
    console.log('Tahun:', tahun);
    
    const result = `${noUrut}/${kode}/ST.IKOM/UNIKA/${bulan}/${tahun}`;
    console.log('Nomor final:', result);
    
    return result;
}


            async function reserveNomor(showToast = true) {
    const manual = $manual.val().trim();
    if (manual) {
        $disp.val(manual);
        $hidden.val(manual);
        $urut.val(extractNoUrut(manual));
        if (showToast) Swal.fire({
            icon: 'success',
            title: 'Nomor Manual Dipakai',
            text: manual,
            timer: 1400,
            showConfirmButton: false
        });
        return { nomor: manual, manual: true };
    }
    
    // ✅ PERBAIKAN: Jangan strip titik!
    const kodeKlas = ($('#klasifikasi_kode').val() || '').trim();
    const bulan = ($('#bulan').val() || '').toUpperCase();
    const tahun = parseInt($('#tahun-nomor').val(), 10) || new Date().getFullYear();
    
    // ✅ DEBUG
    console.log('=== DATA YANG DIKIRIM KE SERVER ===');
    console.log('kode_klasifikasi:', kodeKlas);
    console.log('Apakah ada titik?', kodeKlas.includes('.'));
    console.log('bulan_romawi:', bulan);
    console.log('tahun:', tahun);
    
    if (!kodeKlas || !bulan || !tahun) {
        Swal.fire('Lengkapi Kode/Bulan/Tahun dahulu', '', 'info');
        return null;
    }

    try {
        $('#btn-reserve-nomor').prop('disabled', true);
        const csrf = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
        
        const payload = {
            doc_type: 'ST',
            unit_display: 'ST.IKOM',
            kode_klasifikasi: kodeKlas,  // ✅ Harus tetap ada titik!
            bulan_romawi: bulan,
            tahun
        };
        
        console.log('Payload JSON:', JSON.stringify(payload));
        
        const res = await fetch(@json(route('ajax.nomor.reserve')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
        
        if (!res.ok) throw new Error('Reserve nomor gagal');
        const data = await res.json();
        
        console.log('Response dari server:', data);
        
        $disp.val(data.nomor);
        $hidden.val(data.nomor);
        $urut.val(extractNoUrut(data.nomor));
        
        if (showToast) Swal.fire({
            icon: 'success',
            title: 'Nomor Disiapkan',
            text: data.nomor,
            timer: 1500,
            showConfirmButton: false
        });
        
        return data;
    } catch (e) {
        console.error('Error reserve nomor:', e);
        Swal.fire('Gagal', 'Tidak bisa menyiapkan nomor. Coba lagi.', 'error');
        return null;
    } finally {
        $('#btn-reserve-nomor').prop('disabled', false);
    }
}

            if (isEdit) {
                const updateNomorSurat = () => {
                    const nomor = buildNomorFromParts();
                    $disp.val(nomor);
                    $hidden.val(nomor);
                };
                $('#nomor_urut, #klasifikasi_kode, #bulan, #tahun-nomor, #klasifikasi_surat_id')
                    .on('change keyup input', updateNomorSurat);
                if ($manual.val().trim()) {
                    const v = $manual.val().trim();
                    $disp.val(v);
                    $hidden.val(v);
                    $urut.val(extractNoUrut(v));
                } else {
                    const built = buildNomorFromParts();
                    $disp.val(built);
                    $hidden.val(built);
                }
            } else {
                const onScopeChange = () => {
                    if (!$manual.val().trim()) markNomorStale();
                };
                $('#klasifikasi_surat_id, #klasifikasi_kode, #bulan, #tahun-nomor')
                    .on('change keyup input', onScopeChange);
                $(document).on('click', '#btn-reserve-nomor', () => reserveNomor(true));
                $(document).on('click', '#btn-reset-nomor', () => markNomorStale());
                $manual.on('input', function() {
                    const v = $(this).val().trim();
                    if (v === '') return markNomorStale();
                    $disp.val(v);
                    $hidden.val(v);
                    $urut.val(extractNoUrut(v));
                });
                markNomorStale();
            }

            function toggleReserveBtn() {
                const can = ($('#klasifikasi_kode').val() || '').trim() && ($('#bulan').val() || '') && ($(
                    '#tahun-nomor').val() || '');
                $('#btn-reserve-nomor').prop('disabled', !can);
            }
            $('#klasifikasi_kode, #bulan, #tahun-nomor').on('input change', toggleReserveBtn);
            toggleReserveBtn();

            // ====== Dropdown tugas & preview ======
            const taskData = @json($taskMaster);
            const $tugasPreview = $('#task-preview');
            const placeholderText =
                `<span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat pratinjau.</span>`;

            function updateTaskPreview() {
                const kategori = $('#jenis_tugas').val(),
                    tugas = $('#tugas').val();
                if (kategori && tugas) {
                    const safeKategori = _.escape(kategori);
                    const safeTugas = _.escape(tugas);
                    $tugasPreview.html(`<div>
        <p class="mb-1 text-muted">Jenis Tugas:</p>
        <h5 class="preview-title mb-3"><i class="fas fa-layer-group mr-2"></i>${safeKategori}</h5>
        <p class="mb-1 text-muted">Tugas:</p>
        <p class="preview-content font-weight-bold">${safeTugas}</p>
      </div>`).addClass('has-content');
                } else {
                    $tugasPreview.html(placeholderText).removeClass('has-content');
                }
            }

            function populateSpecificTask(selectedKategori, preselectedTugas) {
                const $tugasSelect = $('#tugas');
                $tugasSelect.empty().append(new Option('Pilih Tugas...', ''));
                const found = (taskData || []).find(jt => jt.nama === selectedKategori);
                if (found && Array.isArray(found.subtugas) && found.subtugas.length) {
                    found.subtugas.forEach(st => {
                        const selected = preselectedTugas === st.nama;
                        $tugasSelect.append(new Option(st.nama, st.nama, selected, selected));
                    });
                    $tugasSelect.prop('disabled', false);
                } else {
                    $tugasSelect.prop('disabled', true);
                }
                $tugasSelect.trigger('change.select2');
                updateTaskPreview();
            }
            $('#jenis_tugas').on('change', function() {
                populateSpecificTask($(this).val(), null);
            });
            $('#tugas').on('change', updateTaskPreview);
            @if ($isEdit)
                populateSpecificTask(@json(old('jenis_tugas', $tugas->jenis_tugas)), @json(old('tugas', $tugas->tugas)));
            @else
                if (@json(old('jenis_tugas', ''))) {
                    populateSpecificTask(@json(old('jenis_tugas', '')), @json(old('tugas', '')));
                }
            @endif

            // ====== TEMBUSAN (Tagify) ======
            const tembusanPresets = @json($tembusanPresets ?? []);
            const tembusanInput = document.querySelector('#tembusan-input');
            if (tembusanInput) {
                const tagify = new Tagify(tembusanInput, {
                    enforceWhitelist: false,
                    whitelist: tembusanPresets,
                    trim: true,
                    duplicates: false,
                    delimiters: ",|\n",
                    editTags: 1,
                    dropdown: {
                        enabled: 1,
                        maxItems: 20,
                        fuzzySearch: true,
                        highlightFirst: true,
                        placeAbove: false
                    },
                    placeholder: "Misal: Yth. Rektor, BAAK, Arsip",
                    transformTag: (t) => {
                        let v = (t.value || '').trim();
                        if (!v) return;
                        v = v.toLowerCase().replace(/\b\w/g, m => m.toUpperCase());
                        const needsYth =
                            /^(Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris)\b/i.test(
                                v) && !/^Yth\.\s/i.test(v);
                        if (needsYth) v = 'Yth. ' + v;
                        t.value = v;
                    }
                });
                const renderTembusanPreview = () => {
                    const data = tagify.value.map(t => (t.value || '').trim()).filter(Boolean);
                    const showTitle = $('#tembusanShowTitle').is(':checked');
                    const $preview = $('#tembusanPreview');
                    if (!data.length) {
                        $preview.html(
                            '<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>'
                        );
                        $('#tembusan_formatted').val('');
                        return;
                    }
                    const titleHtml = showTitle ? '<div class="mb-2 font-weight-bold">Tembusan Yth:</div>' : '';
                    const listHtml = '<ol class="mb-0">' + data.map(v => `<li>${_.escape(v)}</li>`).join('') +
                        '</ol>';
                    $preview.html(
                        `<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`
                    );
                    const plain = (showTitle ? 'Tembusan Yth:\n' : '') + data.map((v, i) => `${i+1}. ${v}`)
                        .join('\n');
                    $('#tembusan_formatted').val(plain);
                };
                tagify.on('add', renderTembusanPreview).on('remove', renderTembusanPreview).on('edit:updated',
                    renderTembusanPreview);
                $(document).on('change', '#tembusanShowTitle', renderTembusanPreview);
                $(document).on('click', '#btnPasteTembusan', async function() {
                    try {
                        const txt = await navigator.clipboard.readText();
                        if (!txt) return;
                        const items = txt.split(/[\n,]/).map(s => s.trim()).filter(Boolean);
                        const existing = new Set(tagify.value.map(t => (t.value || '').toLowerCase()));
                        const toAdd = items.filter(s => !existing.has(s.toLowerCase())).map(s => ({
                            value: s
                        }));
                        tagify.addTags(toAdd);
                    } catch (e) {
                        Swal.fire('Tidak bisa mengakses clipboard', 'Izinkan akses atau tempel manual.',
                            'info');
                    }
                });
                $(document).on('click', '#btnClearTembusan', () => tagify.removeAllTags());
                setTimeout(renderTembusanPreview, 0);
            }

            // ====== PENERIMA ======
            const allUsersData = @json($usersMap);
            const initialInternal = @json($initialInternal);
            const initialEksternal = @json($initialEksternal);
            let penerimaState = {
                internal: {},
                eksternal: Array.isArray(initialEksternal) ? initialEksternal : []
            };

            (initialInternal || []).forEach(id => {
                const u = allUsersData[id];
                if (u) penerimaState.internal[id] = {
                    nama: u.nama_lengkap,
                    peran_id: u.peran_id
                };
            });

            function updateStatusPenerima() {
                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;
                const total = internalCount + eksternalCount;
                const $display = $('#status_penerima_display');
                const $hiddenStatus = $('#status_penerima_hidden');

                if (total === 0) {
                    $display.val('Belum ada penerima');
                    $hiddenStatus.val('');
                } else {
                    const parts = [];
                    if (internalCount > 0) parts.push(`${internalCount} Internal`);
                    if (eksternalCount > 0) parts.push(`${eksternalCount} Eksternal`);
                    const statusText = `${total} Penerima (${parts.join(', ')})`;
                    $display.val(statusText);
                    $hiddenStatus.val(statusText); // nilai ini akan disanitasi di FormRequest
                }
            }

            function renderPenerimaList() {
                const list = $('#penerima-list');
                const placeholder = $('#penerima-placeholder');
                list.empty();
                $('input[name^="penerima_internal"],input[name^="penerima_eksternal"]').remove();

                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;

                if (internalCount === 0 && eksternalCount === 0) {
                    placeholder.show();
                } else {
                    placeholder.hide();
                    for (const id in penerimaState.internal) {
                        const d = penerimaState.internal[id];
                        list.append(
                            `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div><i class="fas fa-user-tie mr-2 text-info"></i>${_.escape(d.nama)}</div>
            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="internal" data-id="${id}">
              <i class="fas fa-times"></i>
            </button>
          </li>`
                        );
                        tugasForm.append(`<input type="hidden" name="penerima_internal[]" value="${id}">`);
                    }
                    penerimaState.eksternal.forEach((p, i) => {
                        list.append(
                            `<li class="list-group-item d-flex justify-content-between align-items-center">
            <div><i class="fas fa-user mr-2 text-success"></i>${_.escape(p.nama)} <span class="eksternal-label">(${_.escape(p.jabatan)})</span></div>
            <button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="eksternal" data-id="${i}">
              <i class="fas fa-times"></i>
            </button>
          </li>`
                        );
                        tugasForm.append(
                            `<input type="hidden" name="penerima_eksternal[${i}][nama]" value="${_.escape(p.nama)}">`
                        );
                        tugasForm.append(
                            `<input type="hidden" name="penerima_eksternal[${i}][jabatan]" value="${_.escape(p.jabatan)}">`
                        );
                    });
                }
                updateStatusPenerima();
            }

            $('#simpanPenerima').on('click', function() {
                penerimaState.internal = {};
                table.$('.penerima-checkbox:checked').each(function() {
                    const id = $(this).val();
                    const nama = $(this).data('nama');
                    const peranId = $(this).data('peran-id');
                    penerimaState.internal[id] = {
                        nama,
                        peran_id: peranId
                    };
                });
                renderPenerimaList();
                $('#penerimaModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Penerima Disimpan!',
                    text: 'Daftar penerima berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 1500
                });
            });

            $('#simpanPenerimaEksternal').on('click', function() {
                const nama = $('#nama_eksternal').val().trim();
                const jabatan = $('#jabatan_eksternal').val().trim();
                if (nama && jabatan) {
                    penerimaState.eksternal.push({
                        nama,
                        jabatan
                    });
                    renderPenerimaList();
                    $('#form-penerima-eksternal')[0].reset();
                    $('#penerimaEksternalModal').modal('hide');
                } else {
                    Swal.fire('Lengkapi Nama & Jabatan', '', 'warning');
                }
            });

            $('#penerima-list').on('click', '.remove-penerima', function() {
                const type = $(this).data('type'),
                    id = $(this).data('id');
                if (type === 'internal') {
                    delete penerimaState.internal[id];
                    $('#penerima-table .penerima-checkbox[value="' + id + '"]').prop('checked', false);
                } else {
                    penerimaState.eksternal.splice(id, 1);
                }
                renderPenerimaList();
            });

            // ====== Validasi ringkas ======
            function validateForm() {
                const errors = [];
                const namaUmum = $('#nama_umum').val().trim();
                if (!namaUmum || namaUmum.length < 10) errors.push('Judul Umum Surat minimal 10 karakter');
                if (!$('#klasifikasi_surat_id').val()) errors.push('Klasifikasi surat wajib dipilih');
                const penandatanganVal = String($('#penandatangan_id').val() || '').trim();
                if (!penandatanganVal) errors.push('Penandatangan wajib dipilih');
                const mulai = $('#waktu_mulai').val(),
                    selesai = $('#waktu_selesai').val();
                if (mulai && selesai && new Date(selesai) < new Date(mulai)) errors.push(
                    'Waktu selesai harus setelah waktu mulai');

                if (errors.length) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<ul style="text-align:left;">' + errors.map(e => `<li>${_.escape(e)}</li>`)
                            .join('') +
                            '</ul>'
                    });
                    return false;
                }
                return true;
            }

            // ====== Sinkron hidden nama pembuat & asal surat (kirim ID) ======
            function syncNamaPembuat() {
                $('#nama_pembuat_hidden').val($('#pembuat_id').val() || '');
            }

            function syncAsalSurat() {
                const $opt = $('#asal_surat_id').find(':selected');
                if ($opt.length) $('#asal_surat_hidden').val($opt.val()); // ID, bukan label
            }
            $('#pembuat_id').on('change', syncNamaPembuat);
            $('#asal_surat_id').on('change', syncAsalSurat);
            syncNamaPembuat();
            syncAsalSurat();

            // ✅ AUTO-SYNC: Saat Asal Surat (Pejabat) dipilih, otomatis set Penandatangan ke orang yang sama
            $('#asal_surat_id').on('change', function() {
                const selectedPejabat = $(this).val();
                if (selectedPejabat) {
                    // Set value dan trigger change agar Select2 update UI
                    $('#penandatangan_id').val(selectedPejabat).trigger('change');
                }
            });

            // Safeguard sebelum submit (isi hidden ID bila readonly)
            tugasForm.on('submit', function() {
                if (!$('#nama_pembuat_hidden').val()) {
                    const val = $('input[name="pembuat_id"]').val() || $('#pembuat_id').val();
                    $('#nama_pembuat_hidden').val(val || '');
                }
                if (!$('#asal_surat_hidden').val()) {
                    const val = $('input[name="asal_surat_id"]').val() || $('#asal_surat_id').val();
                    $('#asal_surat_hidden').val(val || '');
                }
            });

            // ====== ACTION SUBMIT/DRAFT (SATU-SATUNYA HANDLER) ======
            $('button[name="action"]').on('click', function(e) {
                e.preventDefault();
                clickedAction = $(this).val();

                const internalCount = Object.keys(penerimaState.internal).length;
                const eksternalCount = penerimaState.eksternal.length;
                if (internalCount === 0 && eksternalCount === 0) {
                    Swal.fire('Peringatan', 'Anda harus memilih setidaknya satu penerima tugas.',
                        'warning');
                    return;
                }
                if (clickedAction === 'submit' && !validateForm()) return;

                const isSubmit = clickedAction === 'submit';
                Swal.fire({
                    title: isSubmit ? 'Ajukan Surat Tugas?' : 'Simpan sebagai Draft?',
                    text: isSubmit ?
                        'Setelah diajukan, surat akan masuk alur persetujuan. Lanjutkan?' :
                        'Draft bisa diubah nanti. Simpan sekarang?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: isSubmit ? 'Ya, ajukan' : 'Ya, simpan draft',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(async (result) => {
                    if (!result.isConfirmed) return;
                    if (isSubmitting) return;
                    isSubmitting = true;

                    Swal.fire({
                        title: 'Sedang diproses…',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                        showConfirmButton: false
                    });

                    // Pastikan ada nomor
                    if (!$hidden.val() && !$manual.val().trim()) {
                        const ok = await reserveNomor(false);
                        if (!ok) {
                            isSubmitting = false;
                            Swal.close();
                            return;
                        }
                    }
                    if ($manual.val().trim()) {
                        const v = $manual.val().trim();
                        $disp.val(v);
                        $hidden.val(v);
                        $urut.val(extractNoUrut(v));
                    }

                    tugasForm.find('input[name="action"]').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'action',
                        value: clickedAction
                    }).appendTo(tugasForm);

                    tugasForm.find('button[type="button"]').prop('disabled', true).addClass(
                        'disabled');
                    tugasForm.get(0).submit();
                });
            });

            // Submit via ENTER (tanpa klik tombol): minta pilih aksi
            tugasForm.on('submit', function(e) {
                if (clickedAction) return; // sudah ada pilihan
                e.preventDefault();
                Swal.fire({
                    title: 'Pilih Aksi',
                    text: 'Anda belum memilih apakah ingin menyimpan draft atau mengajukan surat tugas.',
                    icon: 'warning',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Draft',
                    denyButtonText: 'Ajukan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(async (res) => {
                    if (!res.isConfirmed && !res.isDenied) return;
                    clickedAction = res.isConfirmed ? 'draft' : 'submit';
                    $('button[name="action"][value="' + clickedAction + '"]').trigger('click');
                });
            });

            // ====== Render awal penerima ======
            renderPenerimaList();

            // ====== Prefill preview tugas saat edit ======
            @if ($isEdit && !empty(old('jenis_tugas', $tugas->jenis_tugas ?? null)) && !empty(old('tugas', $tugas->tugas ?? null)))
                setTimeout(function() {
                    updateTaskPreview();
                }, 300);
            @endif

        });
    </script>
@endpush
