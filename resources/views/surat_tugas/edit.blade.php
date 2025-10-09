@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />

<style>
  .select2-container--bootstrap4 .select2-selection--single {
    height: calc(2.25rem + 2px) !important
  }

  .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: calc(2.25rem + 2px) !important
  }

  #penerima-list .list-group-item {
    padding: .65rem 1rem;
    border-color: #e9ecef
  }

  #penerima-list .eksternal-label {
    color: #198754;
    font-size: .92em;
    margin-left: 3px
  }

  #penerima-table thead th {
    vertical-align: middle;
    text-align: center
  }

  #penerima-table tbody td:first-child {
    text-align: center
  }

  #task-preview {
    background: #f8f9fa;
    border: 1px dashed #ced4da;
    border-radius: .25rem;
    padding: 1.5rem;
    min-height: 158px;
    transition: .3s;
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

  .ck-editor__editable_inline {
    min-height: 250px
  }

  /* Header cantik */
  @keyframes pulseGlow {

    0%,
    100% {
      filter: drop-shadow(0 0 5px #00f6ff) drop-shadow(0 0 15px #00f6ff)
    }

    50% {
      filter: drop-shadow(0 0 10px #00f6ff) drop-shadow(0 0 25px #00f6ff)
    }
  }

  @keyframes auroraGradient {
    0% {
      background-position: 0% 50%
    }

    50% {
      background-position: 100% 50%
    }

    100% {
      background-position: 0% 50%
    }
  }

  .page-header-ui-container {
    background: radial-gradient(ellipse at center, hsl(210, 20%, 20%) 0%, hsl(210, 25%, 10%) 100%);
    color: #fff;
    padding: 2rem 2.5rem;
    border-radius: .75rem;
    border: 1px solid rgba(255, 255, 255, .1);
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    box-shadow: 0 15px 30px rgba(0, 0, 0, .4), inset 0 1px 1px rgba(255, 255, 255, .1)
  }

  .page-header-icon {
    font-size: 3.5rem;
    margin-right: 2rem;
    color: #00f6ff;
    animation: pulseGlow 4s ease-in-out infinite
  }

  .page-header-title {
    margin: 0 0 .5rem 0;
    font-size: 2.25rem;
    font-weight: 700;
    text-shadow: 0 2px 5px rgba(0, 0, 0, .5)
  }

  .page-header-divider {
    border: 0;
    height: 2px;
    margin-block: .75rem;
    width: 80px;
    background: linear-gradient(90deg, #00f6ff, #ff00c1, #a900ff, #00f6ff);
    background-size: 300% 100%;
    animation: auroraGradient 6s linear infinite;
    border-radius: 2px;
    box-shadow: 0 0 5px #ff00c1, 0 0 10px #a900ff
  }

  .page-header-subtitle {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 400;
    color: rgba(255, 255, 255, .85);
    line-height: 1.6
  }

  .nomor-surat-highlight {
    color: #00f6ff;
    font-weight: 600;
    background-color: rgba(0, 246, 255, .1);
    padding: 2px 6px;
    border-radius: 4px
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
</style>
@endpush

@extends('layouts.app')
@section('title', 'Edit Surat Tugas')

@section('content_header')
<div class="page-header-ui-container">
  <div class="page-header-icon"><i class="fas fa-edit"></i></div>
  <div class="page-header-text">
    <h1 class="page-header-title">Edit Surat Tugas</h1>
    <hr class="page-header-divider">
    <p class="page-header-subtitle">
      Ubah detail, kelola penerima, dan ajukan surat tugas dengan nomor:
      <span class="nomor-surat-highlight">{{ $tugas->nomor }}</span>
    </p>
  </div>
</div>
@endsection

@section('content')
@if ($errors->any())
<div class="alert alert-danger alert-dismissible">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
  <h5><i class="icon fas fa-ban"></i> Gagal Memperbarui!</h5>
  Mohon periksa kembali isian Anda:
  <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

@php
// Pecah nomor untuk prefill field seperti di create
$parts = explode('/', $tugas->nomor ?? '');
$noUrutInit = old('nomor_urut', $parts[0] ?? '001');
$bulanInit = old('bulan', $tugas->bulan ?? ($parts[4] ?? 'X'));
$tahunInit = old('tahun', $tugas->tahun ?? ($parts[5] ?? date('Y')));

// Prefill Tagify: simpan sebagai CSV (create juga begitu)
$tembusanCsv = old('tembusan', str_replace(["\r\n","\n"], ',', (string)($tugas->tembusan ?? '')));
@endphp

<form id="tugasForm" action="{{ route('surat_tugas.update', ['tugas' => $tugas, 'mode' => request('mode')]) }}" method="POST">
  @csrf
  @method('PUT')

  <div class="row">
    {{-- Kiri --}}
    <div class="col-lg-8">
      <div class="card card-primary card-tabs">
        <div class="card-header p-0 pt-1">
          <ul class="nav nav-tabs" id="main-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-dasar" role="tab"><i class="fas fa-file-alt mr-2"></i>Informasi Dasar</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-isi" role="tab"><i class="fas fa-tasks mr-2"></i>Detail Tugas</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pelaksanaan" role="tab"><i class="fas fa-calendar-alt mr-2"></i>Pelaksanaan</a></li>
          </ul>
        </div>

        <div class="card-body">
          <div class="tab-content" id="main-tabs-content">
            {{-- TAB 1 --}}
            <div class="tab-pane fade show active" id="tab-dasar" role="tabpanel">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label for="nama_pembuat">Nama Pembuat</label>
                  {{-- Samakan dengan create: readonly teks --}}
                  <input type="text" id="nama_pembuat" name="nama_pembuat" class="form-control"
                    value="{{ old('nama_pembuat', $tugas->nama_pembuat) }}" readonly>
                </div>
                <div class="col-md-6 form-group">
                  <label for="asal_surat">Asal Surat (Unit Kerja)</label>
                  <input type="text" id="asal_surat" name="asal_surat" class="form-control"
                    value="{{ old('asal_surat', $tugas->asal_surat) }}" readonly>
                </div>
              </div>

              <div class="form-group">
                <label for="nama_umum">Perihal Surat</label>
                <input type="text" name="nama_umum" id="nama_umum" class="form-control"
                  placeholder="Contoh: Penugasan Draft Panitia Acara Dies Natalis"
                  value="{{ old('nama_umum', $tugas->nama_umum) }}" required>
              </div>

              {{-- NOMOR SURAT (SKEMA create: Kode/Bln/Thn/NoUrut + hasil otomatis) --}}
              <div class="form-group">
                <label>Nomor Surat</label>
                <div class="row align-items-center">
                  <div class="col-md-5">
                    <label for="klasifikasi_surat" class="small text-muted">Kode</label>
                    <select id="klasifikasi_surat" name="klasifikasi_surat_id" class="form-control select2bs4" required>
                      <option value="" disabled {{ old('klasifikasi_surat_id', $tugas->klasifikasi_surat_id) ? '' : 'selected' }}>-- Pilih Kode --</option>
                      @foreach ($klasifikasi as $k)
                      <option value="{{ $k->id }}" data-kode="{{ $k->kode }}"
                        @selected(old('klasifikasi_surat_id', $tugas->klasifikasi_surat_id) == $k->id)>
                        {{ $k->kode }} - {{ $k->deskripsi }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="bulan" class="small text-muted">Bln</label>
                    <input type="text" id="bulan" name="bulan" class="form-control text-center"
                      value="{{ $bulanInit }}">
                  </div>
                  <div class="col-md-2">
                    <label for="tahun-nomor" class="small text-muted">Thn</label>
                    <input type="number" id="tahun-nomor" class="form-control text-center"
                      value="{{ $tahunInit }}">
                  </div>
                  <div class="col-md-2">
                    <label for="nomor_urut" class="small text-muted">No. Urut</label>
                    <input type="text" id="nomor_urut" class="form-control text-center"
                      value="{{ $noUrutInit }}">
                  </div>
                </div>
              </div>

              <div class="form-group mt-2">
                <label>Hasil Nomor Surat (Otomatis)</label>
                <input type="text" id="nomor_surat_lengkap_display" class="form-control"
                  style="background-color:#e9ecef;cursor:not-allowed;font-weight:bold;letter-spacing:1px" value="..." readonly>
                <input type="hidden" name="nomor" id="nomor_surat_lengkap_hidden" value="{{ old('nomor', $tugas->nomor) }}">
              </div>

              {{-- No. Surat Manual (opsional) --}}
              <div class="form-group">
                <label for="no_surat_manual">Nomor Surat Manual (Opsional)</label>
                <input type="text" name="no_surat_manual" id="no_surat_manual" class="form-control"
                  placeholder="Isi jika nomor surat sudah dibuat secara manual"
                  value="{{ old('no_surat_manual', $tugas->no_surat_manual) }}">
                <small class="form-text text-muted">Jika diisi, nomor surat otomatis di atas akan diabaikan.</small>
              </div>

              {{-- === TEMBUSAN (Tagify + Preview) === --}}
              @php
              $tembusanPresets = [
              'Yth. Rektor','Yth. Wakil Rektor I','Yth. Wakil Rektor II',
              'Dekan Fakultas Ilmu Komputer','BAAK','BAUK','BAK',
              'Kepala Program Studi Sistem Informasi','Unit Kepegawaian','Arsip'
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
                      <button type="button" class="btn btn-sm btn-light" id="btnPasteTembusan" title="Tempel daftar dari clipboard">
                        <i class="fas fa-clipboard-list mr-1"></i>Tempel Daftar
                      </button>
                      <button type="button" class="btn btn-sm btn-light" id="btnClearTembusan" title="Kosongkan">
                        <i class="fas fa-eraser mr-1"></i>Reset
                      </button>
                    </div>
                  </div>
                  <div class="tembusan-body">
                    <label for="tembusan-input" class="mb-1">Ketik & tekan <kbd>Enter</kbd> atau <kbd>,</kbd> untuk membuat tag</label>
                    <input id="tembusan-input" name="tembusan" value="{{ $tembusanCsv }}" class="form-control"
                      placeholder="Misal: Yth. Rektor, BAAK, Arsip">
                    @error('tembusan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                    <div class="custom-control custom-switch mt-3">
                      <input type="checkbox" class="custom-control-input" id="tembusanShowTitle" checked>
                      <label class="custom-control-label" for="tembusanShowTitle">Cetak judul <em>“Tembusan Yth:”</em></label>
                    </div>

                    <div class="tembusan-preview mt-3" id="tembusanPreview">
                      <h6 class="mb-2"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>
                      <div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="tembusan_formatted" id="tembusan_formatted">
              </div>

              <div class="row">
                <div class="col-md-6 form-group">
                  <label for="tahun-periode">Tahun Periode</label>
                  <input type="number" id="tahun-periode" name="tahun" class="form-control" value="{{ old('tahun', $tahunInit) }}">
                </div>
                <div class="col-md-6 form-group">
                  <label for="semester-periode">Semester Periode</label>
                  <select name="semester" id="semester-periode" class="form-control">
                    <option value="Ganjil" @selected(old('semester', $tugas->semester)=='Ganjil')>Ganjil</option>
                    <option value="Genap" @selected(old('semester', $tugas->semester)=='Genap')>Genap</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="status_penerima_display">Status Penerima (Otomatis)</label>
                <input type="text" id="status_penerima_display" class="form-control" style="background:#e9ecef;cursor:not-allowed" value="Belum ada penerima" readonly>
                <input type="hidden" name="status_penerima" id="status_penerima_hidden">
              </div>
            </div>

            {{-- TAB 2 --}}
            <div class="tab-pane fade" id="tab-isi" role="tabpanel">
              <div class="row">
                <div class="col-md-7">
                  <div class="form-group">
                    <label for="jenis_tugas">Jenis Tugas</label>
                    <select name="jenis_tugas" id="jenis_tugas" class="form-control select2bs4">
                      <option value="" disabled {{ old('jenis_tugas', $tugas->jenis_tugas) ? '' : 'selected' }}>Pilih Jenis...</option>
                      @foreach ($taskMaster as $jt)
                      <option value="{{ $jt->nama }}" @selected(old('jenis_tugas', $tugas->jenis_tugas)==$jt->nama)>{{ $jt->nama }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="tugas">Tugas</label>
                    <select name="tugas" id="tugas" class="form-control select2bs4" {{ old('tugas', $tugas->tugas) ? '' : 'disabled' }}>
                      <option value="">{{ old('tugas', $tugas->tugas) ? '' : 'Pilih Tugas...' }}</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <label>Pratinjau Pilihan Tugas</label>
                  <div id="task-preview"><span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat pratinjau.</span></div>
                </div>
              </div>

              <hr class="my-4">
              <div class="form-group">
                <label for="redaksi_pembuka">Redaksi Pembuka</label>
                <textarea name="redaksi_pembuka" id="redaksi_pembuka" class="form-control" rows="3" placeholder="Contoh: Sehubungan dengan akan diselenggarakannya acara ...">{{ old('redaksi_pembuka', $tugas->redaksi_pembuka) }}</textarea>
              </div>
              <div class="form-group">
                <label for="penutup">Redaksi Penutup</label>
                <textarea name="penutup" id="penutup" class="form-control" rows="3" placeholder="Contoh: Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.">{{ old('penutup', $tugas->penutup) }}</textarea>
              </div>
              <div class="form-group">
                <label for="detail_tugas_editor">Isi / Detail Rincian Tugas (Opsional)</label>
                <textarea name="detail_tugas" id="detail_tugas_editor">{{ old('detail_tugas', $tugas->detail_tugas) }}</textarea>
              </div>
            </div>

            {{-- TAB 3 --}}
            <div class="tab-pane fade" id="tab-pelaksanaan" role="tabpanel">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label for="waktu_mulai">Waktu Mulai</label>
                  <input type="datetime-local" id="waktu_mulai" name="waktu_mulai" class="form-control"
                    value="{{ old('waktu_mulai', $tugas->waktu_mulai ? $tugas->waktu_mulai->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6 form-group">
                  <label for="waktu_selesai">Waktu Selesai</label>
                  <input type="datetime-local" id="waktu_selesai" name="waktu_selesai" class="form-control"
                    value="{{ old('waktu_selesai', $tugas->waktu_selesai ? $tugas->waktu_selesai->format('Y-m-d\TH:i') : now()->addHours(2)->format('Y-m-d\TH:i')) }}">
                </div>
              </div>
              <div class="form-group">
                <label for="tempat">Tempat Pelaksanaan</label>
                <input type="text" id="tempat" name="tempat" class="form-control"
                  placeholder="Cth: Ruang Teater, Gedung Thomas Aquinas Lantai 3"
                  value="{{ old('tempat', $tugas->tempat) }}">
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
            <p class="text-muted text-center py-3" id="penerima-placeholder">Belum ada penerima dipilih.</p>
            <ul id="penerima-list" class="list-group list-group-flush"></ul>
          </div>
          <hr>
          <button type="button" class="btn btn-sm btn-info btn-block mb-2" data-toggle="modal" data-target="#penerimaModal">
            <i class="fas fa-user-check mr-2"></i> Pilih dari Pengguna Sistem
          </button>
          <button type="button" class="btn btn-sm btn-success btn-block" data-toggle="modal" data-target="#penerimaEksternalModal">
            <i class="fas fa-user-plus mr-2"></i> Tambah Penerima Luar (Manual)
          </button>
        </div>
      </div>

      <div class="card card-info card-outline position-sticky" style="top:80px">
        <div class="card-header">
          <h3 class="card-title font-weight-bold"><i class="fas fa-paper-plane mr-2"></i>Aksi & Persetujuan</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="penandatangan">Pilih Penandatangan</label>
            <select name="penandatangan" id="penandatangan" class="form-control select2bs4" required>
              @foreach ($pejabat as $p)
              <option value="{{ $p->id }}" @selected(old('penandatangan', $tugas->penandatangan) == $p->id)>{{ $p->nama_lengkap }} ({{ $p->peran->nama }})</option>
              @endforeach
            </select>
          </div>
          <hr>
          <button type="button" name="action" value="draft" class="btn btn-block btn-secondary mb-2"><i class="fas fa-save mr-2"></i>Simpan Draft</button>
          <button type="button" name="action" value="submit" class="btn btn-block btn-primary"><i class="fas fa-check-circle mr-2"></i>Ajukan</button>

          @if (request('mode') === 'koreksi')
          @can('edit-surat', $tugas)
          <div class="mt-2">
            <button type="submit" class="btn btn-block btn-warning mb-2"><i class="fas fa-pen mr-2"></i>Simpan Koreksi</button>
          </div>
          @endcan
          @endif
        </div>
      </div>
    </div>
  </div>
</form>

{{-- MODAL EKSTERNAL --}}
<div class="modal fade" id="penerimaEksternalModal" tabindex="-1" role="dialog" aria-labelledby="penerimaEksternalModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="penerimaEksternalModalLabel"><i class="fas fa-user-plus mr-2"></i>Tambah Penerima Manual</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <form id="form-penerima-eksternal" onsubmit="return false;">
          <div class="form-group">
            <label for="nama_eksternal">Nama Lengkap</label>
            <input type="text" id="nama_eksternal" class="form-control" placeholder="Masukkan nama lengkap" required>
          </div>
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

{{-- MODAL INTERNAL --}}
<div class="modal fade" id="penerimaModal" tabindex="-1" role="dialog" aria-labelledby="penerimaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="penerimaModalLabel"><i class="fas fa-user-check mr-2"></i>Pilih Penerima Tugas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times mr-2"></i>Batal</button>
        <button type="button" class="btn btn-primary" id="simpanPenerima"><i class="fas fa-check mr-2"></i>Simpan Pilihan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

<script>
  /* eslint-disable */ // @ts-nocheck
  $(function() {
    let isSubmitting = false; // ✅ SUDAH ADA

    // Select2
    $('.select2bs4').select2({
      theme: 'bootstrap4',
      width: '100%'
    });

    // ✅ SUDAH ADA: Date/time validation
    $('#waktu_selesai').on('change', function() {
      const mulai = $('#waktu_mulai').val();
      const selesai = $(this).val();

      if (mulai && selesai && new Date(selesai) < new Date(mulai)) {
        Swal.fire({
          icon: 'warning',
          title: 'Waktu Tidak Valid',
          text: 'Waktu selesai harus setelah waktu mulai',
          confirmButtonColor: '#3085d6'
        });
        $(this).val(''); // Reset
      }
    });

    // ✅ SUDAH ADA: Update waktu_selesai min when waktu_mulai changes
    $('#waktu_mulai').on('change', function() {
      $('#waktu_selesai').attr('min', $(this).val());
    });

    // DataTables
    var table = $('#penerima-table').DataTable({
      responsive: true,
      lengthChange: true,
      autoWidth: false,
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_-_END_ dari _TOTAL_",
        zeroRecords: "Tidak ditemukan",
        paginate: {
          next: ">>",
          previous: "<<"
        }
      },
      columnDefs: [{
        orderable: false,
        targets: 0
      }]
    });

    // ✅ SUDAH ADA: CKEditor dengan security config
    ClassicEditor.create(document.querySelector('#detail_tugas_editor'), {
      toolbar: {
        items: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList',
          '|', 'undo', 'redo'
        ],
        shouldNotGroupWhenFull: true
      },
      htmlSupport: {
        disallow: [{
            name: 'script'
          },
          {
            name: 'iframe'
          },
          {
            attributes: [{
              key: /^on.*$/,
              value: true
            }]
          }
        ]
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
    }).catch(error => {
      console.error('Error initializing CKEditor:', error);
    });

    // ===== Nomor surat (format create) =====
    function updateNomorSurat() {
      const noUrut = String($('#nomor_urut').val() || '').padStart(3, '0');
      const kode = $('#klasifikasi_surat').find(':selected').data('kode') || '...';
      const bulan = $('#bulan').val() || '...';
      const tahun = $('#tahun-nomor').val() || '....';
      const nomor = `${noUrut}/${kode}/TG/UNIKA/${bulan}/${tahun}`;
      $('#nomor_surat_lengkap_display').val(nomor);
      $('#nomor_surat_lengkap_hidden').val(nomor);
    }
    $('#nomor_urut, #klasifikasi_surat, #bulan, #tahun-nomor').on('change keyup input', updateNomorSurat);
    updateNomorSurat();

    // ===== Dropdown tugas & preview =====
    const taskData = @json($taskMaster);
    const $tugasPreview = $('#task-preview');
    const placeholderText = '<span class="placeholder-text text-center">Pilih jenis & tugas untuk melihat pratinjau.</span>';

    function updateTaskPreview() {
      const kategori = $('#jenis_tugas').val();
      const tugas = $('#tugas').val();
      if (kategori && tugas) {
        const html = `<div>
          <p class="mb-1 text-muted">Jenis Tugas:</p>
          <h5 class="preview-title mb-3"><i class="fas fa-layer-group mr-2"></i>${kategori}</h5>
          <p class="mb-1 text-muted">Tugas:</p>
          <p class="preview-content font-weight-bold">${tugas}</p>
        </div>`;
        $tugasPreview.html(html).addClass('has-content');
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

    const oldJenis = "{{ old('jenis_tugas', $tugas->jenis_tugas) }}";
    if (oldJenis) {
      populateSpecificTask(oldJenis, "{{ old('tugas', $tugas->tugas) }}");
    }

    // ============== TEMBUSAN (Tagify) ==============
    window._ = window._ || {};
    if (!_.escape) {
      _.escape = function(s) {
        return String(s).replace(/[&<>"'`=\/]/g, c => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;',
          '/': '&#x2F;',
          '=': '&#x3D;',
          '`': '&#x60;'
        } [c]));
      };
    }

    const tembusanPresets = @json($tembusanPresets ?? []);
    const tembusanInput = document.querySelector('#tembusan-input');
    let tagify;
    if (tembusanInput) {
      tagify = new Tagify(tembusanInput, {
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
          const needsYth = /^(Rektor|Wakil Rektor|Dekan|Kepala|Direktur|Ketua|Sekretaris)\b/i.test(v) && !/^Yth\.\s/i.test(v);
          if (needsYth) v = 'Yth. ' + v;
          t.value = v;
        }
      });

      const renderTembusanPreview = () => {
        const data = tagify.value.map(t => (t.value || '').trim()).filter(Boolean);
        const showTitle = $('#tembusanShowTitle').is(':checked');
        const $preview = $('#tembusanPreview');
        if (!data.length) {
          $preview.html('<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6><div class="text-muted">Belum ada tembusan. Tambahkan minimal satu.</div>');
          $('#tembusan_formatted').val('');
          return;
        }
        const titleHtml = showTitle ? '<div class="mb-2 font-weight-bold">Tembusan Yth:</div>' : '';
        const listHtml = '<ol class="mb-0">' + data.map((v) => `<li>${_.escape(v)}</li>`).join('') + '</ol>';
        $preview.html(`<h6 class="mb-2" style="font-weight:700;color:#3b5bdb"><i class="fas fa-eye mr-1"></i>Pratinjau</h6>${titleHtml}${listHtml}`);
        const plain = (showTitle ? 'Tembusan Yth:\n' : '') + data.map((v, i) => `${i+1}. ${v}`).join('\n');
        $('#tembusan_formatted').val(plain);
      };

      tagify.on('add', renderTembusanPreview).on('remove', renderTembusanPreview).on('edit:updated', renderTembusanPreview);
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
          Swal.fire('Tidak bisa mengakses clipboard', 'Izinkan akses atau tempel manual.', 'info');
        }
      });
      $(document).on('click', '#btnClearTembusan', function() {
        tagify.removeAllTags();
      });

      // Render awal
      setTimeout(renderTembusanPreview, 0);
    }

    // ===== Penerima (internal & eksternal) =====
    var allUsersData = @json($users->keyBy('id'));
    var initialInternal = @json(old('penerima_internal') ?: $tugas->penerima->whereNotNull('pengguna_id')->pluck('pengguna_id')->values());
    var initialEksternal = @json(old('penerima_eksternal') ?: $tugas->penerima->whereNull('pengguna_id')->map(fn($p) => ['nama' => $p->nama_penerima, 'jabatan' => $p->jabatan_penerima])->values());

    var penerimaState = {
      internal: {},
      eksternal: []
    };
    (initialInternal || []).forEach(function(id) {
      var u = allUsersData[id];
      if (u) {
        penerimaState.internal[id] = {
          nama: u.nama_lengkap,
          peran_id: u.peran_id
        };
      }
    });
    penerimaState.eksternal = Array.isArray(initialEksternal) ? initialEksternal : [];

    function updateStatusPenerima() {
      var $displayInput = $('#status_penerima_display');
      var $hiddenInput = $('#status_penerima_hidden');
      var jenisSet = new Set();

      Object.keys(penerimaState.internal).forEach(function(id) {
        var peranId = penerimaState.internal[id].peran_id;
        if (peranId == 1 || peranId == 6) jenisSet.add('Tendik');
        else jenisSet.add('Dosen');
      });
      penerimaState.eksternal.forEach(function(p) {
        var j = (p.jabatan || '').toLowerCase();
        if (j === 'mahasiswa') jenisSet.add('Mahasiswa');
        else if (j === 'dosen luar') jenisSet.add('Dosen');
        else if (j === 'umum') jenisSet.add('Umum');
      });

      var display = 'Belum ada penerima';
      if (jenisSet.size) {
        var arr = Array.from(jenisSet).sort();
        display = (arr.length === 1) ? arr[0] : (arr.length === 2 ? arr.join(' dan ') : (arr.slice(0, -1).join(', ') + ', dan ' + arr.slice(-1)));
      }
      $displayInput.val(display);

      var enumValue = '';
      if (jenisSet.has('Mahasiswa')) enumValue = 'mahasiswa';
      else if (jenisSet.has('Tendik')) enumValue = 'tendik';
      else if (jenisSet.has('Dosen')) enumValue = 'dosen';
      $hiddenInput.val(enumValue);
    }

    $('#select-all-penerima').on('change', function() {
      var checked = this.checked;
      $('#penerima-table').find('.penerima-checkbox').prop('checked', checked);
    });

    function renderPenerimaList() {
      var $list = $('#penerima-list');
      var $placeholder = $('#penerima-placeholder');
      $list.empty();
      $('input[name^="penerima_internal"],input[name^="penerima_eksternal"]').remove();

      var internalCount = Object.keys(penerimaState.internal).length;
      var eksternalCount = penerimaState.eksternal.length;

      if (!internalCount && !eksternalCount) {
        $placeholder.show();
      } else {
        $placeholder.hide();
        Object.keys(penerimaState.internal).forEach(function(id) {
          var d = penerimaState.internal[id];
          var item = '<li class="list-group-item d-flex justify-content-between align-items-center">' +
            '<div><i class="fas fa-user-tie mr-2 text-info"></i>' + d.nama + '</div>' +
            '<button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="internal" data-id="' + id + '"><i class="fas fa-times"></i></button></li>';
          $list.append(item);
          $('#tugasForm').append('<input type="hidden" name="penerima_internal[]" value="' + id + '">');
        });
        penerimaState.eksternal.forEach(function(p, i) {
          var item = '<li class="list-group-item d-flex justify-content-between align-items-center">' +
            '<div><i class="fas fa-user mr-2 text-success"></i>' + p.nama + ' <span class="eksternal-label">(' + p.jabatan + ')</span></div>' +
            '<button type="button" class="btn btn-xs btn-danger remove-penerima" data-type="eksternal" data-id="' + i + '"><i class="fas fa-times"></i></button></li>';
          $list.append(item);
          $('#tugasForm').append('<input type="hidden" name="penerima_eksternal[' + i + '][nama]" value="' + p.nama + '">');
          $('#tugasForm').append('<input type="hidden" name="penerima_eksternal[' + i + '][jabatan]" value="' + p.jabatan + '">');
        });
      }
      updateStatusPenerima();
    }

    $('#simpanPenerima').on('click', function() {
      penerimaState.internal = {};
      table.$('.penerima-checkbox:checked').each(function() {
        const id = $(this).val();
        if (allUsersData[id]) {
          penerimaState.internal[id] = {
            nama: allUsersData[id].nama_lengkap,
            peran_id: allUsersData[id].peran_id
          };
        }
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

    $('#penerima-list').on('click', '.remove-penerima', function() {
      var type = $(this).data('type'),
        id = $(this).data('id');
      if (type === 'internal') {
        delete penerimaState.internal[id];
        $('#penerima-table .penerima-checkbox[value="' + id + '"]').prop('checked', false);
      } else {
        penerimaState.eksternal.splice(id, 1);
      }
      renderPenerimaList();
    });

    $('#simpanPenerimaEksternal').on('click', function() {
      var nama = $('#nama_eksternal').val().trim();
      var jabatan = $('#jabatan_eksternal').val().trim();
      if (nama && jabatan) {
        penerimaState.eksternal.push({
          nama: nama,
          jabatan: jabatan
        });
        renderPenerimaList();
        $('#form-penerima-eksternal')[0].reset();
        $('#penerimaEksternalModal').modal('hide');
      } else {
        Swal.fire('Lengkapi Nama & Jabatan', '', 'warning');
      }
    });

    // ✅ SUDAH ADA: Form validation function
    function validateForm() {
      const errors = [];

      const namaUmum = $('#nama_umum').val().trim();
      if (!namaUmum || namaUmum.length < 10) {
        errors.push('Judul Umum Surat minimal 10 karakter');
      }

      const klasifikasi = $('#klasifikasi_surat').val();
      if (!klasifikasi) {
        errors.push('Klasifikasi surat wajib dipilih');
      }

      const penandatangan = $('#penandatangan').val();
      if (!penandatangan) {
        errors.push('Penandatangan wajib dipilih');
      }

      const waktuMulai = $('#waktu_mulai').val();
      const waktuSelesai = $('#waktu_selesai').val();
      if (waktuMulai && waktuSelesai && new Date(waktuSelesai) < new Date(waktuMulai)) {
        errors.push('Waktu selesai harus setelah waktu mulai');
      }

      if (errors.length > 0) {
        Swal.fire({
          icon: 'error',
          title: 'Validasi Gagal',
          html: '<ul style="text-align: left;">' +
            errors.map(e => '<li>' + e + '</li>').join('') +
            '</ul>'
        });
        return false;
      }
      return true;
    }

    // Aksi draft / submit
    var clickedAction = null;
    $('button[name="action"]').on('click', function(e) {
      e.preventDefault();
      clickedAction = $(this).val();
      var $form = $('#tugasForm');

      // ✅ SUDAH ADA: Validate penerima
      var internalCount = Object.keys(penerimaState.internal).length;
      var eksternalCount = penerimaState.eksternal.length;
      if (!internalCount && !eksternalCount) {
        Swal.fire('Peringatan', 'Anda harus memilih setidaknya satu penerima tugas.', 'warning');
        return;
      }

      // ✅ TAMBAHKAN INI: Validate form if submitting (not draft)
      if (clickedAction === 'submit') {
        if (!validateForm()) {
          return; // Stop if validation fails
        }
      }

      var isSubmit = (clickedAction === 'submit');
      Swal.fire({
        title: isSubmit ? 'Ajukan Surat Tugas?' : 'Simpan sebagai Draft?',
        text: isSubmit ? 'Surat akan dikirim untuk persetujuan. Lanjutkan?' : 'Anda dapat mengedit draft ini nanti. Simpan sekarang?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: isSubmit ? 'Ya, Ajukan' : 'Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(function(result) {
        if (result.isConfirmed) {
          $form.find('input[name="action"]').remove();
          $('<input type="hidden" name="action">').val(clickedAction).appendTo($form);
          
          Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: function() {
              // ✅ TAMBAHKAN INI: Prevent double submit
              if (isSubmitting) {
                return; // Stop if already submitting
              }
              isSubmitting = true; // Set flag
              
              Swal.showLoading();
              $form.find('button').prop('disabled', true);
              $form.submit();
            },
            showConfirmButton: false
          });
        }
      });
    });

    // Render awal
    renderPenerimaList();
  });
</script>
@endpush