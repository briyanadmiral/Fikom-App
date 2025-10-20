@extends('layouts.app')
@section('title', 'Daftar Surat Keputusan')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
  body{background:#f7faff}
  .stat-wrapper{display:flex;justify-content:flex-start;gap:1.2rem;margin-bottom:2.1rem;flex-wrap:wrap}
  .stat-card{width:170px;border-radius:.85rem;border:none;background:#fff}
  .stat-card .card-body{text-align:center;padding:1.15rem 1rem}
  .stat-card .icon{font-size:2.3rem;margin-bottom:.5rem}
  .stat-card .label{color:#6c757d;font-size:.83rem;margin-bottom:.25rem;font-weight:600;text-transform:uppercase;letter-spacing:1px}
  .stat-card .value{font-size:2.1rem;font-weight:700;line-height:1.1}
  .card.data-card{border-radius:1rem}
  .card.data-card .card-body{padding-top:1.2rem}
  .table th,.table td{vertical-align:middle!important}
  .table{background:#fff}

  /* Dropdown item colors */
  .dropdown-menu .dropdown-item{cursor:pointer;padding:.5rem 1rem;transition:.2s}
  .dropdown-menu .dropdown-item i{width:20px;text-align:center;margin-right:8px}
  .dropdown-item.text-info:hover{background:#17a2b8;color:#fff!important}
  .dropdown-item.text-warning:hover{background:#ffc107;color:#fff!important}
  .dropdown-item.text-success:hover{background:#28a745;color:#fff!important}
  .dropdown-item.text-danger:hover{background:#dc3545;color:#fff!important}
  .dropdown-item.text-primary:hover{background:#007bff;color:#fff!important}
  .dropdown-item.text-dark:hover{background:#343a40;color:#fff!important}
  .dropdown-item.text-secondary:hover{background:#6c757d;color:#fff!important}

  .badge-pill{padding:.45rem .85rem;font-size:.85rem;font-weight:600;letter-spacing:.3px}

  @media (max-width:767.98px){
    .stat-card{width:100%}
    .card.data-card{border-radius:.6rem}
  }
</style>
@endpush

@section('content_header')
@php
  $mode = $mode ?? (request()->routeIs('surat_keputusan.approveList') ? 'approve-list' : 'admin');
@endphp
@include('surat_keputusan.partials._header_filter', [
  'mode'  => $mode,
  'stats' => $stats ?? ['draft'=>0,'pending'=>0,'disetujui'=>0]
])
@endsection

@section('content')
<div class="container-fluid px-2">

  {{-- Tabel Utama (kolom penting) --}}
  <div class="card data-card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="table-sk" class="table table-hover" style="width:100%">
          <thead>
            <tr class="text-center">
              <th>No</th>
              <th>Nomor SK</th>
              <th>Tentang / Perihal</th>
              <th>Tgl Surat</th>
              <th>Pembuat</th>
              <th>Penandatangan</th>
              <th>Disetujui</th>
              <th>Status</th>
              <th style="width:80px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($list as $h)
              @php 
                $tglSurat = $h->tanggal_surat;
                $tglApproved = $h->approved_at ?? null;
              @endphp
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $h->nomor ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($h->tentang, 60) }}</td>

                <td class="text-center" data-sort="{{ $tglSurat ? $tglSurat->timestamp : 0 }}">
                  {{ $tglSurat ? $tglSurat->format('d M Y') : '-' }}
                  @if($tglSurat)
                    <br><small class="text-muted"><i class="far fa-clock"></i> {{ $tglSurat->diffForHumans() }}</small>
                  @endif
                </td>

                <td>{{ $h->pembuat?->nama_lengkap ?? 'N/A' }}</td>
                <td>{{ $h->penandatanganUser?->nama_lengkap ?? '—' }}</td>

                <td class="text-center" data-sort="{{ $tglApproved ? $tglApproved->timestamp : 0 }}">
                  @if($tglApproved)
                    {{ $tglApproved->format('d M Y H:i') }}
                    <br><small class="text-muted"><i class="far fa-check-circle"></i> {{ $tglApproved->diffForHumans() }}</small>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>

                <td class="text-center">
                  @php
                    $badgeMap = ['draft'=>'secondary','pending'=>'warning','disetujui'=>'success','ditolak'=>'danger','terbit'=>'info','arsip'=>'dark'];
                    $badge = $badgeMap[$h->status_surat] ?? 'secondary';
                  @endphp
                  <span class="badge badge-pill badge-{{ $badge }}">{{ ucfirst($h->status_surat) }}</span>
                </td>

                {{-- Dropdown Aksi (termasuk Download PDF) --}}
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu aksi">
                      <i class="fas fa-cog"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">

                      {{-- 1. Lihat Detail --}}
                      <a class="dropdown-item text-info" href="{{ route('surat_keputusan.show', $h->id) }}">
                        <i class="fas fa-eye"></i> Lihat Detail
                      </a>

                      {{-- 2. Edit + Ajukan (hanya draft/ditolak) --}}
                      @if(in_array($h->status_surat, ['draft', 'ditolak']))
                        @can('update', $h)
                          <a class="dropdown-item text-warning" href="{{ route('surat_keputusan.edit', $h->id) }}">
                            <i class="fas fa-edit"></i> Edit Draft
                          </a>
                        @endcan

                        @if($h->status_surat === 'draft')
                          @can('submit', $h)
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('surat_keputusan.submit', $h->id) }}" method="POST" style="display:inline;">
                              @csrf
                              <button type="button" class="dropdown-item text-success w-100 text-left btn-submit-sk"
                                      data-nomor="{{ $h->nomor ?? '—' }}">
                                <i class="fas fa-paper-plane"></i> Ajukan untuk Persetujuan
                              </button>
                            </form>
                          @endcan
                        @endif
                        <div class="dropdown-divider"></div>
                      @endif

                      {{-- 3. Approve / Reject / Reopen (pending) --}}
                      @if($h->status_surat === 'pending')
                        @can('approve', $h)
                          <a class="dropdown-item text-success" href="{{ route('surat_keputusan.approveForm', $h->id) }}">
                            <i class="fas fa-check-circle"></i> Tinjau & Setujui
                          </a>
                          @can('reject', $h)
                            <a href="#" class="dropdown-item text-danger btn-reject"
                               data-action="{{ route('surat_keputusan.reject', $h->id) }}"
                               data-nomor="{{ $h->nomor ?? '—' }}">
                              <i class="fas fa-times"></i> Tolak / Minta Revisi
                            </a>
                          @endcan
                          <div class="dropdown-divider"></div>
                        @endcan

                        @can('reopen', $h)
                          <a href="#" class="dropdown-item text-secondary btn-reopen"
                             data-url="{{ route('surat_keputusan.reopen', $h->id) }}"
                             data-nomor="{{ $h->nomor ?? '—' }}">
                            <i class="fas fa-undo"></i> Tarik ke Draft
                          </a>
                          <div class="dropdown-divider"></div>
                        @endcan
                      @endif

                      {{-- 4. Download PDF (pindah ke Aksi) --}}
                      @if(in_array($h->status_surat, ['disetujui', 'terbit', 'arsip']) && $h->signed_pdf_path)
                        <a class="dropdown-item text-danger" href="{{ route('surat_keputusan.downloadPdf', $h->id) }}" target="_blank">
                          <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                        <div class="dropdown-divider"></div>
                      @endif

                      {{-- 5. Terbitkan --}}
                      @if($h->status_surat === 'disetujui')
                        @can('publish', $h)
                          <form action="{{ route('surat_keputusan.terbitkan', $h->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="button" class="dropdown-item text-primary w-100 text-left btn-terbitkan-sk"
                                    data-nomor="{{ $h->nomor ?? '—' }}">
                              <i class="fas fa-share-square"></i> Terbitkan SK
                            </button>
                          </form>
                          <div class="dropdown-divider"></div>
                        @endcan
                      @endif

                      {{-- 6. Arsipkan --}}
                      @if($h->status_surat === 'terbit')
                        @can('archive', $h)
                          <form action="{{ route('surat_keputusan.arsipkan', $h->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="button" class="dropdown-item text-dark w-100 text-left btn-arsipkan-sk"
                                    data-nomor="{{ $h->nomor ?? '—' }}">
                              <i class="fas fa-archive"></i> Arsipkan SK
                            </button>
                          </form>
                          <div class="dropdown-divider"></div>
                        @endcan
                      @endif

                      {{-- 7. Hapus Draft --}}
                      @if($h->status_surat === 'draft')
                        @can('delete', $h)
                          <form action="{{ route('surat_keputusan.destroy', $h->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="button" class="dropdown-item text-danger w-100 text-left btn-hapus-sk"
                                    data-nomor="{{ $h->nomor ?? '—' }}">
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

  // helpers
  const debounce = (fn, d=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
  const confirmAction = async (o) =>
    Swal.fire(Object.assign({
      icon:'question', showCancelButton:true,
      confirmButtonColor:'#007bff', cancelButtonColor:'#6c757d',
      confirmButtonText:'Lanjut', cancelButtonText:'Batal'
    }, o)).then(r=>r.isConfirmed);

  // datatable init
  const $table = $('#table-sk');
  const headers = $table.find('thead th').map((i,th)=>$(th).text().trim().toLowerCase()).get();
  const statusIdx = headers.indexOf('status');
  const aksiIdx   = headers.length - 1;

  const table = $table.DataTable({
    responsive: true,
    autoWidth: false,
    language: { url: "/assets/datatables/i18n/id.json", emptyTable: emptyMsg },
    order: [[ headers.indexOf('tgl surat') !== -1 ? headers.indexOf('tgl surat') : 0, 'desc' ]],
    columnDefs: [
      { targets: [aksiIdx], orderable:false, searchable:false }
    ]
  }).on('draw', function(){ $('[data-toggle="tooltip"]').tooltip(); });

  // filters (pakai input/select dari partial)
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

  // flash messages
  @if(session('success'))
    Swal.fire({ icon:'success', title:'Berhasil!', text:"{{ session('success') }}", timer:3000, showConfirmButton:false });
  @endif
  @if(session('error'))
    Swal.fire({ icon:'error', title:'Gagal!', text:"{{ session('error') }}", timer:3000, showConfirmButton:false });
  @endif

  // ACTIONS
  $(document).on('click', '.btn-submit-sk', async function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    const ok = await confirmAction({
      title:'Ajukan SK untuk Persetujuan?',
      html:`SK <b>${nomor}</b> akan dikirim ke penandatangan untuk disetujui.`,
      icon:'question', confirmButtonColor:'#28a745',
      confirmButtonText:'<i class="fas fa-paper-plane"></i> Ya, Ajukan Sekarang'
    });
    if (ok) $form.trigger('submit');
  });

  $(document).on('click', '.btn-terbitkan-sk', async function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    const ok = await confirmAction({
      title:'Terbitkan SK?',
      html:`SK <b>${nomor}</b> akan diterbitkan dan dibagikan ke penerima.`,
      icon:'info', confirmButtonColor:'#007bff',
      confirmButtonText:'<i class="fas fa-share-square"></i> Ya, Terbitkan'
    });
    if (ok) $form.trigger('submit');
  });

  $(document).on('click', '.btn-arsipkan-sk', async function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    const ok = await confirmAction({
      title:'Arsipkan SK?',
      html:`SK <b>${nomor}</b> akan dipindahkan ke arsip.`,
      icon:'warning', confirmButtonColor:'#343a40',
      confirmButtonText:'<i class="fas fa-archive"></i> Ya, Arsipkan'
    });
    if (ok) $form.trigger('submit');
  });

  $(document).on('click', '.btn-hapus-sk', async function(e){
    e.preventDefault();
    const $form = $(this).closest('form');
    const nomor = $(this).data('nomor') || '—';
    const ok = await confirmAction({
      title:'Hapus Draft SK?',
      html:`SK <b>${nomor}</b> akan dihapus secara permanen.`,
      icon:'error', confirmButtonColor:'#dc3545',
      confirmButtonText:'<i class="fas fa-trash"></i> Ya, Hapus',
      footer:'<small class="text-muted">Aksi ini tidak dapat dibatalkan!</small>'
    });
    if (ok) $form.trigger('submit');
  });

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

  $(document).on('click', '.btn-reopen', async function(e){
    e.preventDefault();
    const url = $(this).data('url');
    const nomor = $(this).data('nomor') || '—';
    const ok = await confirmAction({
      title:'Tarik ke Draft?',
      html:`SK <b>${nomor}</b> akan dikembalikan ke status <b>Draft</b> untuk direvisi.`,
      icon:'question', confirmButtonColor:'#6c757d',
      confirmButtonText:'<i class="fas fa-undo"></i> Ya, Tarik Sekarang'
    });
    if (!ok) return;
    const form = $('<form>', { method:'POST', action:url, style:'display:none' })
      .append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }));
    $('body').append(form); form.trigger('submit');
  });

});
</script>
@endpush
