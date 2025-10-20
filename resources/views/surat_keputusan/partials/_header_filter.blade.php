@php
  // mode: 'admin' | 'user' | 'approve-list'
  $mode = $mode ?? 'admin';
  $isUser = $mode === 'user';
  $isApproveList = $mode === 'approve-list';
@endphp

<div class="surat-header mt-2 mb-3">
  <span class="icon">
    <i class="fas {{ $isUser ? 'fa-user-check' : 'fa-gavel' }} text-white"></i>
  </span>
  <div>
    <div class="surat-header-title">
      @switch($mode)
        @case('approve-list') Daftar SK Menunggu Persetujuan Anda @break
        @case('user')         Surat Keputusan Saya @break
        @default              Daftar Surat Keputusan
      @endswitch
    </div>
    <div class="surat-header-desc">
      @if ($isApproveList)
        Hanya menampilkan SK berstatus <b>pending</b> yang menunggu persetujuan Anda.
      @elseif ($isUser)
        Daftar <b>Surat Keputusan</b> yang terkait dengan akun Anda.
      @else
        Semua <b>Surat Keputusan</b> — kelola, filter, unduh PDF, dan lacak statusnya di sini.
      @endif
    </div>
  </div>
</div>

{{-- Statistik --}}
<div class="d-flex justify-content-center w-100 mb-3">
  <div class="stat-wrapper py-1" style="width:100%;max-width:650px;">
    @foreach([
      'draft'     => ['icon'=>'fa-file-alt',        'label'=>'Draft',     'color'=>'secondary'],
      'pending'   => ['icon'=>'fa-hourglass-half',  'label'=>'Pending',   'color'=>'warning'],
      'disetujui' => ['icon'=>'fa-check-circle',    'label'=>'Disetujui', 'color'=>'success'],
    ] as $status => $info)
      <div class="stat-card card shadow-sm mx-2">
        <div class="card-body">
          <div class="icon text-{{ $info['color'] }}" data-toggle="tooltip" title="{{ $info['label'] }}">
            <i class="fas {{ $info['icon'] }}"></i>
          </div>
          <div class="label">{{ $info['label'] }}</div>
          <div class="value text-{{ $info['color'] }}">{{ $stats[$status] ?? 0 }}</div>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- Filter & Tombol Tambah --}}
<div class="card filter-card mb-4 shadow-sm">
  <div class="card-header bg-white border-0 py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
      <h5 class="mb-0 font-weight-bold">
        <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
      </h5>
      @if(!$isUser && !$isApproveList)
        @can('create', App\Models\KeputusanHeader::class)
          <a href="{{ route('surat_keputusan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah SK
          </a>
        @endcan
      @endif
    </div>
  </div>
  <div class="card-body">
    <form class="row">
      <div class="col-md-6 form-group mb-2">
        <input id="globalSearch" type="text" class="form-control" placeholder="Cari nomor, tentang, atau pembuat...">
      </div>
      <div class="col-md-3 form-group mb-2">
        <select id="statusFilter" class="form-control">
          <option value="">Semua Status</option>
          <option value="draft">Draft</option>
          <option value="pending">Pending</option>
          <option value="disetujui">Disetujui</option>
          <option value="ditolak">Ditolak</option>
          <option value="terbit">Terbit</option>
          <option value="arsip">Arsip</option>
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
