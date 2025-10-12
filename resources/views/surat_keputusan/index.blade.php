@extends('layouts.app')
@section('title', 'Daftar Surat Keputusan')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
  body{background:#f7faff}
  .surat-header{background:#f3f6fa;padding:1.3rem 2.2rem 1.3rem 1.8rem;border-radius:1.1rem;margin-bottom:2.2rem;border:1px solid #e0e6ed;display:flex;align-items:center;gap:1.3rem}
  .surat-header .icon{background:linear-gradient(135deg,#1498ff 0,#1fc8ff 100%);width:54px;height:54px;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 1px 10px #1498ff30;font-size:2rem}
  .surat-header-title{font-weight:bold;color:#0056b3;font-size:1.85rem;margin-bottom:.13rem;letter-spacing:-1px}
  .surat-header-desc{color:#636e7b;font-size:1.03rem}
  .stat-wrapper{display:flex;justify-content:flex-start;gap:1.2rem;margin-bottom:2.1rem;flex-wrap:wrap}
  .stat-card{width:170px;border-radius:.85rem;border:none;background:#fff}
  .stat-card .card-body{text-align:center;padding:1.15rem 1rem}
  .stat-card .icon{font-size:2.3rem;margin-bottom:.5rem}
  .stat-card .label{color:#6c757d;font-size:.83rem;margin-bottom:.25rem;font-weight:600;text-transform:uppercase;letter-spacing:1px}
  .stat-card .value{font-size:2.1rem;font-weight:700;line-height:1.1}
  .card.filter-card{margin-bottom:2.2rem;border-radius:1rem}
  .card.filter-card .card-header{background:#f8fafc;border-radius:1rem 1rem 0 0;border:none}
  .card.filter-card .card-body{padding-bottom:.7rem}
  .card.data-card{border-radius:1rem}
  .card.data-card .card-body{padding-top:1.2rem}
  .table th,.table td{vertical-align:middle!important}
  .table{background:#fff}
  
  /* ✅ Dropdown dengan warna berbeda */
  .dropdown-menu .dropdown-item {
    cursor: pointer;
    padding: 0.5rem 1rem;
    transition: all 0.2s;
  }
  .dropdown-menu .dropdown-item i {
    width: 20px;
    text-align: center;
    margin-right: 8px;
  }
  
  /* Warna untuk setiap aksi */
  .dropdown-item.text-info:hover { background-color: #17a2b8; color: white !important; }
  .dropdown-item.text-warning:hover { background-color: #ffc107; color: white !important; }
  .dropdown-item.text-success:hover { background-color: #28a745; color: white !important; }
  .dropdown-item.text-danger:hover { background-color: #dc3545; color: white !important; }
  .dropdown-item.text-primary:hover { background-color: #007bff; color: white !important; }
  .dropdown-item.text-dark:hover { background-color: #343a40; color: white !important; }
  .dropdown-item.text-secondary:hover { background-color: #6c757d; color: white !important; }
  
  .badge-pill{
    padding:0.45rem 0.85rem;
    font-size:0.85rem;
    font-weight:600;
    letter-spacing:0.3px;
  }
  
  @media (max-width:767.98px){
    .surat-header{flex-direction:column;align-items:flex-start;padding:1.2rem 1rem;gap:.7rem}
    .stat-wrapper{flex-direction:column;gap:.8rem}
    .stat-card{width:100%}
    .surat-header-title{font-size:1.18rem}
    .surat-header-desc{font-size:.99rem}
    .card.filter-card,.card.data-card{border-radius:.6rem}
  }
</style>
@endpush

@section('content_header')
@php
  $mode = $mode ?? (request()->routeIs('surat_keputusan.approveList') ? 'approve-list' : 'list');
@endphp
<div class="surat-header mt-2 mb-3">
  <span class="icon">
    <i class="fas fa-gavel text-white"></i>
  </span>
  <div>
    <div class="surat-header-title">
      {{ $mode === 'approve-list' ? 'Daftar SK Menunggu Persetujuan Anda' : 'Daftar Surat Keputusan' }}
    </div>
    <div class="surat-header-desc">
      @if ($mode === 'approve-list')
        Hanya menampilkan SK berstatus <b>pending</b> yang menunggu persetujuan Anda.
      @else
        Semua <b>Surat Keputusan</b> — kelola, filter, unduh PDF, dan lacak statusnya di sini.
      @endif
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="container-fluid px-2">
  {{-- Statistik --}}
  <div class="d-flex justify-content-center w-100 mb-3">
    <div class="stat-wrapper py-1" style="width:100%;max-width:650px;">
      @foreach([
        'draft' => ['icon'=>'fa-file-alt', 'label'=>'Draft', 'count'=>$stats['draft'] ?? 0, 'color'=>'secondary'],
        'pending' => ['icon'=>'fa-hourglass-half', 'label'=>'Pending', 'count'=>$stats['pending'] ?? 0, 'color'=>'warning'],
        'disetujui' => ['icon'=>'fa-check-circle', 'label'=>'Disetujui', 'count'=>$stats['disetujui'] ?? 0, 'color'=>'success'],
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

  {{-- Filter dan Tombol --}}
  <div class="card filter-card mb-4 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100">
        <h5 class="mb-0 font-weight-bold">
          <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
        </h5>
        @if ($mode !== 'approve-list')
          <div class="d-flex gap-2">
            @can('create', App\Models\KeputusanHeader::class)
              <a href="{{ route('surat_keputusan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>Tambah SK
              </a>
            @endcan
          </div>
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

  {{-- Tabel Utama --}}
  <div class="card data-card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="table-sk" class="table table-hover" style="width:100%">
          <thead>
            <tr class="text-center">
              <th>No</th>
              <th>Nomor SK</th>
              <th>Tentang</th>
              <th>Tgl Surat</th>
              <th>Pembuat</th>
              <th>Status</th>
              <th>Berkas</th>
              <th style="width:80px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($list as $h)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td>{{ $h->nomor ?? '—' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($h->tentang, 60) }}</td>

              @php 
                $tgl = $h->tanggal_surat; 
              @endphp
              <td class="text-center" data-sort="{{ $tgl ? $tgl->timestamp : 0 }}">
                {{ $tgl ? $tgl->format('d M Y') : '-' }}
                @if($tgl)
                  <br><small class="text-muted"><i class="far fa-clock"></i> {{ $tgl->diffForHumans() }}</small>
                @endif
              </td>

              <td>{{ $h->pembuat?->nama_lengkap ?? 'N/A' }}</td>

              <td class="text-center">
                @php
                  $badgeMap = [
                    'draft'=>'secondary','pending'=>'warning','disetujui'=>'success',
                    'ditolak'=>'danger','terbit'=>'info','arsip'=>'dark'
                  ];
                  $badge = $badgeMap[$h->status_surat] ?? 'secondary';
                @endphp
                <span class="badge badge-pill badge-{{ $badge }}">{{ ucfirst($h->status_surat) }}</span>
              </td>

              <td class="text-center">
                @if(in_array($h->status_surat, ['disetujui','terbit','arsip']) && $h->signed_pdf_path)
                  <a href="{{ route('surat_keputusan.downloadPdf', $h->id) }}" 
                     class="btn btn-sm btn-danger" 
                     title="Download PDF" 
                     target="_blank">
                    <i class="fas fa-file-pdf"></i>
                  </a>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>

              {{-- ✅ DROPDOWN MENU DENGAN SWEETALERT2 --}}
<td class="text-center">
  <div class="dropdown">
    <button class="btn btn-sm btn-secondary dropdown-toggle" 
            type="button" 
            data-toggle="dropdown" 
            aria-haspopup="true" 
            aria-expanded="false" 
            title="Menu aksi">
      <i class="fas fa-cog"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
      
      {{-- 1. LIHAT DETAIL (Info/Biru) --}}
      <a class="dropdown-item text-info" href="{{ route('surat_keputusan.show', $h->id) }}">
        <i class="fas fa-eye"></i> Lihat Detail
      </a>

      {{-- 2. EDIT (Warning/Kuning) - Hanya draft/ditolak --}}
      @if(in_array($h->status_surat, ['draft', 'ditolak']))
        @can('update', $h)
          <a class="dropdown-item text-warning" href="{{ route('surat_keputusan.edit', $h->id) }}">
            <i class="fas fa-edit"></i> Edit Draft
          </a>
        @endcan

        {{-- ✅ AJUKAN (Success/Hijau) - Hanya untuk draft --}}
        @if($h->status_surat === 'draft')
          @can('submit', $h)
            <div class="dropdown-divider"></div>
            <form action="{{ route('surat_keputusan.submit', $h->id) }}" method="POST" style="display:inline;">
              @csrf
              <button type="button" 
                      class="dropdown-item text-success w-100 text-left btn-submit-sk" 
                      data-nomor="{{ $h->nomor ?? '—' }}"
                      style="border:none;background:transparent;cursor:pointer">
                <i class="fas fa-paper-plane"></i> Ajukan untuk Persetujuan
              </button>
            </form>
          @endcan
        @endif
        <div class="dropdown-divider"></div>
      @endif

      {{-- 3. APPROVE (Success/Hijau) - Hanya pending oleh approver --}}
      @if($h->status_surat === 'pending')
        @can('approve', $h)
          <a class="dropdown-item text-success" href="{{ route('surat_keputusan.approveForm', $h->id) }}">
            <i class="fas fa-check-circle"></i> Tinjau & Setujui
          </a>
          
          {{-- Tolak/Reject (tetap pakai modal) --}}
          @can('reject', $h)
            <a href="#" class="dropdown-item text-danger btn-reject"
               data-action="{{ route('surat_keputusan.reject', $h->id) }}"
               data-nomor="{{ $h->nomor ?? '—' }}">
              <i class="fas fa-times"></i> Tolak / Minta Revisi
            </a>
          @endcan
          <div class="dropdown-divider"></div>
        @endcan

        {{-- TARIK KE DRAFT (Secondary/Abu-abu) --}}
        @can('reopen', $h)
          <a href="#" class="dropdown-item text-secondary btn-reopen"
             data-url="{{ route('surat_keputusan.reopen', $h->id) }}"
             data-nomor="{{ $h->nomor ?? '—' }}">
            <i class="fas fa-undo"></i> Tarik ke Draft
          </a>
          <div class="dropdown-divider"></div>
        @endcan
      @endif

      {{-- 4. PDF (Danger/Merah) --}}
      @if(in_array($h->status_surat, ['disetujui', 'terbit', 'arsip']) && $h->signed_pdf_path)
        <a class="dropdown-item text-danger" 
           href="{{ route('surat_keputusan.downloadPdf', $h->id) }}" 
           target="_blank">
          <i class="fas fa-file-pdf"></i> Download PDF
        </a>
        <div class="dropdown-divider"></div>
      @endif

      {{-- 5. TERBITKAN (Primary/Biru) --}}
      @if($h->status_surat === 'disetujui')
        @can('publish', $h)
          <form action="{{ route('surat_keputusan.terbitkan', $h->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="button" 
                    class="dropdown-item text-primary w-100 text-left btn-terbitkan-sk" 
                    data-nomor="{{ $h->nomor ?? '—' }}"
                    style="border:none;background:transparent;cursor:pointer">
              <i class="fas fa-share-square"></i> Terbitkan SK
            </button>
          </form>
          <div class="dropdown-divider"></div>
        @endcan
      @endif

      {{-- 6. ARSIPKAN (Dark/Hitam) --}}
      @if($h->status_surat === 'terbit')
        @can('archive', $h)
          <form action="{{ route('surat_keputusan.arsipkan', $h->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="button" 
                    class="dropdown-item text-dark w-100 text-left btn-arsipkan-sk" 
                    data-nomor="{{ $h->nomor ?? '—' }}"
                    style="border:none;background:transparent;cursor:pointer">
              <i class="fas fa-archive"></i> Arsipkan SK
            </button>
          </form>
          <div class="dropdown-divider"></div>
        @endcan
      @endif

      {{-- 7. DELETE (Outline Danger) --}}
      @if($h->status_surat === 'draft')
        @can('delete', $h)
          <form action="{{ route('surat_keputusan.destroy', $h->id) }}" method="POST" style="display:inline;">
            @csrf 
            @method('DELETE')
            <button type="button" 
                    class="dropdown-item text-danger w-100 text-left btn-hapus-sk" 
                    data-nomor="{{ $h->nomor ?? '—' }}"
                    style="border:none;background:transparent;cursor:pointer">
              <i class="fas fa-trash"></i> Hapus Draft
            </button>
          </form>
        @endcan
      @endif
    </div>
  </div>
</td>

            </tr>
            @empty
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Modal Tolak / Minta Revisi --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="rejectForm" method="POST" action="#">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tolak / Minta Revisi</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-light border">
            Menolak SK <b class="sk-info">—</b> akan mengembalikan dokumen ke pembuat.
          </div>
          <div class="form-group">
            <label>Catatan ke pembuat (opsional)</label>
            <textarea name="note" class="form-control" rows="4" placeholder="Contoh: Mohon perbaiki redaksi KESATU dan lengkapi dasar hukum butir 3."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-times"></i> Tolak & Kembalikan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();

  const MODE = "{{ $mode }}";
  const emptyMsg = MODE === 'approve-list'
    ? "Tidak ada SK yang perlu Anda setujui."
    : "Tidak ada data Surat Keputusan.";

  const table = $('#table-sk').DataTable({
    responsive: true,
    autoWidth: false,
    language: { 
      url: "/assets/datatables/i18n/id.json", 
      emptyTable: emptyMsg 
    },
    order: [[3, 'desc']],
    columnDefs: [
      { targets: [6, 7], orderable: false, searchable: false }
    ]
  });

  $('#globalSearch').on('keyup', function(){ 
    table.search(this.value).draw(); 
  });

  $('#statusFilter').on('change', function(){
    const status = this.value;
    if (status) {
      table.column(5).search('^' + status + '$', true, false).draw();
    } else {
      table.column(5).search('').draw();
    }
  });

  $('#resetFilters').on('click', function(e){
    e.preventDefault();
    $('#globalSearch, #statusFilter').val('');
    table.search('').columns().search('').draw();
  });

  // ✅ Flash Messages dengan SweetAlert2
  @if(session('success'))
  Swal.fire({ 
    icon: 'success',
    title: 'Berhasil!',
    text: "{{ session('success') }}",
    timer: 3000,
    showConfirmButton: false
  });
  @endif

  @if(session('error'))
  Swal.fire({ 
    icon: 'error',
    title: 'Gagal!',
    text: "{{ session('error') }}",
    timer: 3000,
    showConfirmButton: false
  });
  @endif

  // ✅ AJUKAN SK - SweetAlert2
  $(document).on('click', '.btn-submit-sk', function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    
    Swal.fire({
      title: 'Ajukan SK untuk Persetujuan?',
      html: `SK <b>${nomor}</b> akan dikirim ke penandatangan untuk disetujui.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Ajukan Sekarang',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $form.trigger('submit');
      }
    });
  });

  // ✅ TERBITKAN SK - SweetAlert2
  $(document).on('click', '.btn-terbitkan-sk', function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    
    Swal.fire({
      title: 'Terbitkan SK?',
      html: `SK <b>${nomor}</b> akan diterbitkan dan dibagikan ke penerima.`,
      icon: 'info',
      showCancelButton: true,
      confirmButtonColor: '#007bff',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fas fa-share-square"></i> Ya, Terbitkan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $form.trigger('submit');
      }
    });
  });

  // ✅ ARSIPKAN SK - SweetAlert2
  $(document).on('click', '.btn-arsipkan-sk', function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    
    Swal.fire({
      title: 'Arsipkan SK?',
      html: `SK <b>${nomor}</b> akan dipindahkan ke arsip.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#343a40',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fas fa-archive"></i> Ya, Arsipkan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $form.trigger('submit');
      }
    });
  });

  // ✅ HAPUS SK - SweetAlert2
  $(document).on('click', '.btn-hapus-sk', function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    
    Swal.fire({
      title: 'Hapus Draft SK?',
      html: `SK <b>${nomor}</b> akan dihapus secara permanen.`,
      icon: 'error',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
      cancelButtonText: 'Batal',
      footer: '<small class="text-muted">Aksi ini tidak dapat dibatalkan!</small>'
    }).then((result) => {
      if (result.isConfirmed) {
        $form.trigger('submit');
      }
    });
  });

  // ✅ TOLAK/REJECT - Modal tetap (untuk input catatan)
  $(document).on('click', '.btn-reject', function(e){
    e.preventDefault();
    const action = $(this).data('action');
    const nomor  = $(this).data('nomor') || '—';
    const $m = $('#rejectModal');
    $('#rejectForm').attr('action', action);
    $m.find('.sk-info').text(nomor);
    $m.find('textarea[name="note"]').val('');
    $m.modal('show');
  });

  // ✅ TARIK KE DRAFT - SweetAlert2
  $(document).on('click', '.btn-reopen', function(e){
    e.preventDefault();
    const url = $(this).data('url');
    const nomor = $(this).data('nomor') || '—';
    
    Swal.fire({
      title: 'Tarik ke Draft?',
      html: `SK <b>${nomor}</b> akan dikembalikan ke status <b>Draft</b> untuk direvisi.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#6c757d',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fas fa-undo"></i> Ya, Tarik Sekarang',
      cancelButtonText: 'Batal'
    }).then(result => {
      if (result.isConfirmed) {
        const form = $('<form>', { 
          method: 'POST', 
          action: url, 
          style: 'display:none' 
        }).append($('<input>', { 
          type: 'hidden', 
          name: '_token', 
          value: '{{ csrf_token() }}' 
        }));
        $('body').append(form);
        form.trigger('submit');
      }
    });
  });
});
</script>
@endpush