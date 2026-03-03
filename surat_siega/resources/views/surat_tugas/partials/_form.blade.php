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

@include('surat_tugas.partials._form_styles')

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
                                    @php
                                        // ✅ Allow edit if Create Mode OR (Edit Mode & Status is Draft/Ditolak)
                                        $lockAsalSurat = $isEdit && !in_array($tugas->status_surat ?? '', ['draft', 'ditolak']);
                                    @endphp
                                    @if ($lockAsalSurat)
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
                                            {{ $lockStructural ? 'readonly' : '' }}
                                            pattern="^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$"
                                            title="Masukkan bulan dalam angka Romawi (contoh: I, II, X)"
                                            oninput="this.value = this.value.toUpperCase()">
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


                            {{-- Row 7: Mode Nomor Turunan (Suffix Letter) --}}
                            {{-- Muncul saat CREATE atau EDIT surat PENDING (Admin TU only) --}}
                            @if (auth()->user()->peran_id == 1 && (!$isEdit || ($isEdit && $tugas->status_surat === 'pending')))
                            <div class="form-group" id="turunan-wrapper">
                                <div class="card card-outline card-warning">
                                    <div class="card-header py-2">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="is_turunan" name="is_turunan" value="1"
                                                   {{ old('is_turunan', $tugas->suffix ?? false) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_turunan">
                                                <strong><i class="fas fa-code-branch mr-1"></i> Mode Turunan (Suffix Letter)</strong>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body py-2" id="turunan-section" style="display:none;">
                                        <small class="form-text text-muted mb-2">
                                            <i class="fas fa-info-circle text-warning"></i> 
                                            Untuk surat yang menggunakan nomor induk yang sudah ada (misal: 002 → 002A, 002B).
                                            Biasa digunakan untuk surat yang ditandatangani setelah kegiatan berlangsung.
                                            <br><strong>Catatan:</strong> Surat induk bisa yang masih Pending (belum ditandatangani) atau yang sudah Disetujui.
                                        </small>
                                        
                                        <label for="parent_tugas_id">Pilih Nomor Induk <span class="text-danger">*</span></label>
                                        <select name="parent_tugas_id" id="parent_tugas_id" 
                                                class="form-control select2bs4">
                                            <option value="">-- Pilih Nomor Induk --</option>
                                            @foreach($parentableNomors ?? [] as $pn)
                                                @php
                                                    $statusBadge = $pn->status_surat === 'disetujui' 
                                                        ? '✓ Disetujui' 
                                                        : '⏳ Pending';
                                                @endphp
                                                <option value="{{ $pn->id }}" 
                                                        data-nomor="{{ $pn->nomor }}"
                                                        {{ old('parent_tugas_id', $tugas->parent_tugas_id ?? null) == $pn->id ? 'selected' : '' }}>
                                                    {{ $pn->nomor }} - {{ Str::limit($pn->nama_umum, 40) }} [{{ $statusBadge }}]
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        <div class="mt-3 p-3 rounded" id="suffix-preview" style="display:none; background-color: #f4f6f9; border: 1px solid #dcdcdc; border-left: 5px solid #28a745;">
                                            <div class="row align-items-center">
                                                <div class="col-md-3 col-sm-4 border-right">
                                                    <label class="text-uppercase text-secondary mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">Suffix</label>
                                                    <div class="font-weight-bold text-success" style="font-size: 2rem; line-height: 1;" id="next-suffix">{{ $tugas->suffix ?? 'A' }}</div>
                                                </div>
                                                <div class="col-md-9 col-sm-8 pl-md-4">
                                                    <label class="text-uppercase text-secondary mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">Preview Nomor Lengkap</label>
                                                    <div class="font-weight-bold text-dark" style="font-size: 1.25rem; word-break: break-all; color: #333 !important;" id="suffix-nomor-preview">...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

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
                            
                            {{-- Template Selector - REDESIGNED --}}
                            @if(isset($templates) && count($templates) > 0)
                                <div class="template-selector-section mb-4">
                                    <div class="template-selector-header">
                                        <div class="d-flex align-items-center">
                                            <div class="template-icon-box">
                                                <i class="fas fa-file-signature"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h5 class="mb-0 font-weight-bold text-white">Template Surat</h5>
                                                <small class="text-white-50">Pilih template untuk mengisi formulir secara otomatis</small>
                                            </div>
                                        </div>
                                        <span class="badge badge-light">{{ count($templates) }} Template</span>
                                    </div>
                                    
                                    <div class="template-selector-body">
                                        <div class="row">
                                            <div class="col-lg-5 mb-3 mb-lg-0">
                                                <label class="small font-weight-bold text-muted mb-2">
                                                    <i class="fas fa-list mr-1"></i> Pilih Template
                                                </label>
                                                <select id="template_selector" class="form-control select2bs4" data-placeholder="-- Pilih Template --">
                                                    <option value=""></option>
                                                    @foreach($templates as $tpl)
                                                        <option value="{{ $tpl->id }}" 
                                                                data-jenis="{{ $tpl->jenisTugas?->nama ?? '' }}"
                                                                data-sub-tugas="{{ $tpl->subTugas?->nama ?? '' }}"
                                                                data-deskripsi="{{ Str::limit($tpl->deskripsi ?? 'Tidak ada deskripsi', 80) }}">
                                                            {{ $tpl->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="col-lg-7">
                                                {{-- Template Preview Card --}}
                                                <div id="template-preview-card" class="template-preview-card d-none">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="template-preview-title mb-1" id="tpl-preview-name">-</h6>
                                                            <p class="template-preview-desc mb-0" id="tpl-preview-desc">-</p>
                                                        </div>
                                                    </div>
                                                    <div class="template-preview-badges mb-3">
                                                        <span class="badge badge-info mr-1" id="tpl-preview-jenis" style="display:none;">
                                                            <i class="fas fa-folder mr-1"></i> <span></span>
                                                        </span>
                                                        <span class="badge badge-success mr-1" id="tpl-preview-subtugas" style="display:none;">
                                                            <i class="fas fa-tag mr-1"></i> <span></span>
                                                        </span>
                                                    </div>
                                                    <button type="button" class="btn btn-warning btn-sm btn-block" id="btn-apply-template">
                                                        <i class="fas fa-magic mr-1"></i> Terapkan Template Ini
                                                    </button>
                                                </div>
                                                
                                                {{-- Empty State --}}
                                                <div id="template-preview-empty" class="template-preview-empty">
                                                    <i class="fas fa-hand-pointer fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">Pilih template dari dropdown untuk melihat informasi</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

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
        class="btn btn-block btn-primary btn-submit-action"
        data-title="{{ ($tugas->status_surat ?? '') === 'draft' ? 'Ajukan Surat?' : 'Ajukan Ulang Surat?' }}"
        data-text="Surat akan dikirim ke penandatangan untuk disetujui." data-icon="question"
        data-confirm-text="Ya, Ajukan Sekarang" data-confirm-color="#007bff"
        {{ $lockStructural ? 'disabled' : '' }}>
        <i class="fas fa-check-circle mr-2"></i>{{ ($tugas->status_surat ?? '') === 'draft' ? 'Ajukan' : 'Ajukan Ulang' }}
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

@include('surat_tugas.partials._form_scripts')

