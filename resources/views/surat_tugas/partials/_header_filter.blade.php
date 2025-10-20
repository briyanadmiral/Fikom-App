@php
  /** Props:
   *  - string $mode: 'approve-list' | 'list' (default)
   *  - array  $stats: ['draft'=>int,'pending'=>int,'disetujui'=>int]
   *  - bool   $showButtons (opsional): paksa tampil/sembunyi tombol kanan
   */
  $mode        = $mode ?? 'list';
  $stats       = $stats ?? ['draft'=>0,'pending'=>0,'disetujui'=>0];
  $showButtons = isset($showButtons) ? (bool)$showButtons : ($mode !== 'approve-list');
@endphp

{{-- HEADER --}}
<div class="surat-header mt-2 mb-3">
  <span class="icon">
    <i class="fas fa-envelope-open-text text-white"></i>
  </span>
  <div>
    <div class="surat-header-title">
      {{ $mode === 'approve-list' ? 'Daftar Surat Menunggu Persetujuan Anda' : 'Daftar Surat Tugas' }}
    </div>
    <div class="surat-header-desc">
      @if ($mode === 'approve-list')
        Hanya menampilkan surat dengan status <b>pending</b> yang menunggu persetujuan Anda.
      @else
        Semua surat tugas <b>sekolah</b> — kelola, filter, cetak PDF, dan lacak statusnya di sini.
      @endif
    </div>
  </div>
</div>

{{-- STATISTIK --}}
<div class="d-flex justify-content-center w-100 mb-3">
  <div class="stat-wrapper py-1" style="width:100%;max-width:650px;">
    @foreach([
      'draft'     => ['icon'=>'fa-file-alt',       'label'=>'Draft',     'count'=>$stats['draft'] ?? 0,     'color'=>'secondary'],
      'pending'   => ['icon'=>'fa-hourglass-half', 'label'=>'Pending',   'count'=>$stats['pending'] ?? 0,   'color'=>'warning'],
      'disetujui' => ['icon'=>'fa-check-circle',   'label'=>'Disetujui', 'count'=>$stats['disetujui'] ?? 0, 'color'=>'success'],
    ] as $status => $info)
      <div class="stat-card card shadow-sm mx-2">
        <div class="card-body">
          <div class="icon text-{{ $info['color'] }}" data-toggle="tooltip" title="{{ $info['label'] }}">
            <i class="fas {{ $info['icon'] }}"></i>
          </div>
          <div class="label">{{ $info['label'] }}</div>
          <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- FILTER + TOMBOL --}}
<div class="card filter-card mb-4 shadow-sm">
  <div class="card-header bg-white border-0 py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
      <h5 class="mb-0 font-weight-bold">
        <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
      </h5>

      @if ($showButtons)
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ route('surat_tugas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Surat Tugas
          </a>
          <a href="{{ route('jenis_surat_tugas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-folder mr-2"></i>Jenis Surat Tugas
          </a>
          <a href="{{ route('klasifikasi_surat.index') }}" class="btn btn-outline-info">
            <i class="fas fa-folder-open mr-2"></i>Klasifikasi Surat
          </a>
        </div>
      @endif
    </div>
  </div>

  <div class="card-body">
    <form class="row">
      <div class="col-md-6 form-group mb-2">
        <input id="globalSearch" type="text" class="form-control"
               placeholder="Cari berdasarkan nomor, perihal, pembuat, atau penerima...">
      </div>
      <div class="col-md-3 form-group mb-2">
        <select id="statusFilter" class="form-control">
          <option value="">Semua Status</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="disetujui">Disetujui</option>
          <option value="ditolak">Ditolak</option>
        </select>
      </div>
      <div class="col-md-3 form-group mb-2">
        <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
          <i class="fas fa-redo mr-1"></i>Reset Filter
        </button>
      </div>
    </form>
  </div>
</div>
