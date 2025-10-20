@extends('layouts.app')
@section('title', 'Surat Keputusan Saya')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
  body{background:#f7faff}
  .card.data-card{border-radius:1rem}
  .card.data-card .card-body{padding-top:1.2rem}
  .table th,.table td{vertical-align:middle!important}
  .table{background:#fff}
  .badge-pill{padding:.45rem .85rem;font-size:.85rem;font-weight:600;letter-spacing:.3px}
  @media (max-width:767.98px){ .card.data-card{border-radius:.6rem} }
</style>
@endpush

@section('content_header')
  {{-- header + statistik + filter (mode user) --}}
  @include('surat_keputusan.partials._header_filter', [
    'mode'  => 'user',
    'stats' => $stats ?? ['draft'=>0,'pending'=>0,'disetujui'=>0]
  ])
@endsection

@section('content')
<div class="container-fluid px-2">
  <div class="card data-card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="table-sk-saya" class="table table-hover" style="width:100%">
          <thead>
            <tr class="text-center">
              <th>No</th>
              <th>Nomor SK</th>
              <th>Tentang / Perihal</th>
              <th>Tgl Surat</th>
              <th>Penandatangan</th>
              <th>Disetujui</th>
              <th>Status</th>
              <th style="width:80px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($list as $h)
              @php
                $tgl = $h->tanggal_surat ?? $h->tanggal_asli;
                $approved = $h->approved_at ?? null;
              @endphp
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $h->nomor ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($h->tentang, 70) }}</td>

                <td class="text-center" data-sort="{{ $tgl ? $tgl->timestamp : 0 }}">
                  {{ $tgl ? $tgl->format('d M Y') : '-' }}
                </td>

                <td>{{ $h->penandatanganUser?->nama_lengkap ?? '—' }}</td>

                <td class="text-center" data-sort="{{ $approved ? $approved->timestamp : 0 }}">
                  @if($approved)
                    {{ $approved->format('d M Y H:i') }}
                    <br><small class="text-muted"><i class="far fa-check-circle"></i> {{ $approved->diffForHumans() }}</small>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>

                <td class="text-center">
                  @php
                    $badgeMap=['draft'=>'secondary','pending'=>'warning','disetujui'=>'success','ditolak'=>'danger','terbit'=>'info','arsip'=>'dark'];
                    $badge=$badgeMap[$h->status_surat] ?? 'secondary';
                  @endphp
                  <span class="badge badge-pill badge-{{ $badge }}">{{ ucfirst($h->status_surat) }}</span>
                </td>

                {{-- Aksi: hanya lihat & unduh (jika tersedia) --}}
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-cog"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <a class="dropdown-item quick-view" href="{{ route('surat_keputusan.preview', $h->id) }}">
                        <i class="fas fa-fw fa-eye"></i> Lihat Detail
                      </a>
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

  {{-- Quick View (opsional) --}}
  <div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quickViewModalLabel">Pratinjau Surat Keputusan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
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
  const debounce = (fn, d=200) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };

  const $table = $('#table-sk-saya');
  const th = $table.find('thead th').map((i,el)=>$(el).text().trim().toLowerCase()).get();
  const statusIdx = th.indexOf('status');
  const aksiIdx   = th.length - 1;

  const table = $table.DataTable({
    responsive: true,
    autoWidth: false,
    language: { url: "/assets/datatables/i18n/id.json", emptyTable: "Tidak ada Surat Keputusan untuk Anda." },
    order: [[ th.indexOf('tgl surat') !== -1 ? th.indexOf('tgl surat') : 0, 'desc' ]],
    columnDefs: [
      { targets: [aksiIdx], orderable:false, searchable:false }
    ]
  });

  // filter dari partial
  $('#globalSearch').on('keyup', debounce(function(){ table.search(this.value).draw(); }, 200));
  $('#statusFilter').on('change', function(){
    if (statusIdx === -1) return;
    const v = this.value;
    table.column(statusIdx).search(v ? '^'+v+'$' : '', true, false).draw();
  });
  $('#resetFilters').on('click', function(e){
    e.preventDefault();
    $('#globalSearch').val(''); $('#statusFilter').val('');
    table.search('').columns().search('').draw();
  });

  // Quick View
  $table.on('click', '.quick-view', function(e){
    e.preventDefault();
    const url = $(this).data('url') || $(this).attr('href');
    const $m = $('#quickViewModal');
    const $sp = $m.find('.quickview-spinner');
    const $if = $m.find('iframe');
    $sp.show();
    $if.off('load').on('load', ()=> $sp.hide());
    $if.attr('src', url);
    $m.modal('show');
  });
  $('#quickViewModal').on('hidden.bs.modal', function(){
    const $if = $(this).find('iframe'); $if.off('load').attr('src','about:blank');
    $('.quickview-spinner').hide();
  });

  @if(session('success'))
    Swal.fire({ icon:'success', title:'Berhasil!', text:"{{ session('success') }}", timer:2500, showConfirmButton:false });
  @endif
});
</script>
@endpush
