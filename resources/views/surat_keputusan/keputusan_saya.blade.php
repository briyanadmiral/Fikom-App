@extends('layouts.app')
@section('title', 'Surat Keputusan Saya')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
  body { background: #f7faff; }

  /* HEADER */
  .surat-header{
    background:#f3f6fa; padding:1.3rem 2.2rem 1.3rem 1.8rem; border-radius:1.1rem;
    margin-bottom:2.2rem; border:1px solid #e0e6ed; display:flex; align-items:center; gap:1.3rem;
  }
  .surat-header .icon{
    background:linear-gradient(135deg,#1498ff 0,#1fc8ff 100%); width:54px; height:54px;
    display:flex; align-items:center; justify-content:center; border-radius:50%;
    box-shadow:0 1px 10px #1498ff30; font-size:2rem; color:#fff;
  }
  .surat-header-title{ font-weight:bold; color:#0056b3; font-size:1.85rem; margin-bottom:.13rem; letter-spacing:-1px; }
  .surat-header-desc{ color:#636e7b; font-size:1.03rem; }
  @media (max-width:767.98px){
    .surat-header{ flex-direction:column; align-items:flex-start; padding:1.2rem 1rem; gap:.7rem; }
    .surat-header-title{ font-size:1.18rem; }
    .surat-header-desc{ font-size:.99rem; }
  }

  /* STAT */
  .stat-wrapper{
    display:flex; justify-content:center; flex-wrap:wrap; gap:1rem; margin-bottom:2rem;
  }
  .stat-card{ width:160px; border-radius:.75rem; border:none; background:#fff; }
  .stat-card .card-body{ text-align:center; padding:1.25rem 1rem; }
  .stat-card .icon{ font-size:2rem; margin-bottom:.5rem; }
  .stat-card .label{ color:#6c757d; font-size:.8rem; margin-bottom:.25rem; font-weight:600; text-transform:uppercase; }
  .stat-card .value{ font-size:2rem; font-weight:700; line-height:1; }

  /* TABEL */
  .table th, .table td{ vertical-align:middle!important; }
  .badge{ font-size:.8rem; padding:.4em .7em; }

  .dropdown-menu a.dropdown-item{ cursor:pointer; }
  .dropdown-menu .fa-fw{ margin-right:8px; }
  #quickViewModal .modal-body{ height:75vh; }

  .badge-info{ background:#0bb1e3!important; color:#fff; }
</style>
@endpush

@section('content_header')
<div class="surat-header mt-2 mb-3">
  <span class="icon"><i class="fas fa-user-check"></i></span>
  <span>
    <div class="surat-header-title">Surat Keputusan Saya</div>
    <div class="surat-header-desc">
      Daftar <b>Surat Keputusan</b> yang terkait dengan akun Anda. Lihat detail, unduh PDF, dan pantau statusnya di sini.
    </div>
  </span>
</div>
@endsection

@section('content')
<div class="container-fluid">

  {{-- Statistik --}}
  <div class="stat-wrapper">
    @foreach([
      'draft'     => ['icon'=>'fa-file-alt',       'label'=>'Draft',     'count'=>$stats['draft'] ?? 0,     'color'=>'secondary'],
      'pending'   => ['icon'=>'fa-hourglass-half', 'label'=>'Pending',   'count'=>$stats['pending'] ?? 0,   'color'=>'warning'],
      'disetujui' => ['icon'=>'fa-check-circle',   'label'=>'Disetujui', 'count'=>$stats['disetujui'] ?? 0, 'color'=>'success'],
    ] as $status => $info)
      <div class="stat-card card shadow-sm">
        <div class="card-body">
          <div class="icon text-{{ $info['color'] }}"><i class="fas {{ $info['icon'] }}"></i></div>
          <div class="label">{{ $info['label'] }}</div>
          <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Filter --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 font-weight-bold">
          <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
        </h5>
        {{-- Tidak ada tombol "Tambah SK" di halaman ini --}}
      </div>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 form-group">
          {{-- [CHANGED] hapus “penerima” dari placeholder --}}
          <input id="globalSearch" type="text" class="form-control" placeholder="Cari nomor, tentang, atau pembuat...">
        </div>
        <div class="col-md-3 form-group">
          <select id="statusFilter" class="form-control">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="disetujui">Disetujui</option>
          </select>
        </div>
        <div class="col-md-3 form-group">
          <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
            <i class="fas fa-redo mr-1"></i>Reset Filter
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel --}}
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="table-sk-saya" class="table table-hover" style="width:100%">
          <thead>
            <tr class="text-center">
              <th>No</th>
              <th>Nomor SK</th>
              <th>Tentang</th>
              <th>Tgl Surat</th>
              <th>Pembuat</th>
              {{-- [REMOVED] Penerima --}}
              <th>Status</th>
              <th>Berkas</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($list as $h)
              @php $tgl = $h->tanggal_surat ?? $h->tanggal_asli; @endphp
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $h->nomor ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($h->tentang, 60) }}</td>
                <td class="text-center" data-sort="{{ $tgl ? $tgl->timestamp : 0 }}">
                  {{ $tgl ? $tgl->format('d M Y') : '-' }}
                </td>
                <td>{{ $h->pembuat?->nama_lengkap ?? 'N/A' }}</td>

                {{-- [REMOVED] kolom penerima --}}

                <td class="text-center">
                  @php
                    $badgeMap=['draft'=>'secondary','pending'=>'warning','disetujui'=>'success','ditolak'=>'danger','terbit'=>'primary','arsip'=>'dark'];
                    $badge=$badgeMap[$h->status_surat] ?? 'secondary';
                  @endphp
                  <span class="badge badge-pill badge-{{ $badge }}">{{ ucfirst($h->status_surat) }}</span>
                </td>
                <td class="text-center">
                  @if(in_array($h->status_surat, ['disetujui','terbit','arsip']) && $h->signed_pdf_path)
                    <a href="{{ route('surat_keputusan.downloadPdf', $h->id) }}" class="btn btn-sm btn-danger" title="Download PDF" target="_blank">
                      <i class="fas fa-file-pdf"></i>
                    </a>
                  @else
                    -
                  @endif
                </td>
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-cog"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      {{-- Halaman detail (read-only atau sesuai policy) --}}
                      <a class="dropdown-item" href="{{ route('surat_keputusan.preview', $h->id) }}">
                        <i class="fas fa-fw fa-eye"></i> Lihat Detail
                      </a>

                      {{-- Download PDF bila ada --}}
                      @if(in_array($h->status_surat, ['disetujui','terbit','arsip']) && $h->signed_pdf_path)
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('surat_keputusan.downloadPdf', $h->id) }}" target="_blank">
                          <i class="fas fa-fw fa-download"></i> Download PDF
                        </a>
                      @endif
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Modal Quick View (opsional) --}}
  <div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quickViewModalLabel">Pratinjau Surat Keputusan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0" style="position:relative;">
          <div class="spinner-border text-primary quickview-spinner" style="position:absolute; top:48%; left:48%; display:none"></div>
          <iframe src="about:blank" style="width: 100%; border: none; min-height:70vh"></iframe>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  $(function(){
    const table = $('#table-sk-saya').DataTable({
      responsive: true,
      autoWidth: false,
      language: {
        url: "/assets/datatables/i18n/id.json",
        emptyTable: "Tidak ada Surat Keputusan untuk Anda."
      },
      columnDefs: [
        { targets: [6, 7], orderable: false, searchable: false } // [CHANGED] Berkas & Aksi setelah kolom Penerima dihapus
      ]
    });

    // Global search
    $('#globalSearch').on('keyup', function(){
      table.search(this.value).draw();
    });

    // Status filter (kolom 5 sekarang)
    $('#statusFilter').on('change', function(){
      const v = this.value;
      table.column(5).search(v ? '^'+v+'$' : '', true, false).draw();
    });

    // Reset
    $('#resetFilters').on('click', function(){
      $('#globalSearch').val('');
      $('#statusFilter').val('');
      table.search('').columns().search('').draw();
    });

    // Quick View (opsional)
    $('#table-sk-saya').on('click', '.quick-view', function(e){
      e.preventDefault();
      const url = $(this).data('url') || $(this).attr('href');
      const $modal = $('#quickViewModal');
      const $spinner = $modal.find('.quickview-spinner');
      const $iframe = $modal.find('iframe');

      $spinner.show();
      $iframe.off('load').on('load', function(){ $spinner.hide(); });
      $iframe.attr('src', url);
      $modal.modal('show');
    });

    $('#quickViewModal').on('hidden.bs.modal', function(){
      const $iframe = $(this).find('iframe');
      $iframe.off('load').attr('src','about:blank');
      $('.quickview-spinner').hide();
    });

    @if(session('success'))
      Swal.fire({ icon:'success', title:'Berhasil!', text:"{{ session('success') }}", timer:2500, showConfirmButton:false });
    @endif
  });
</script>
@endpush
