@php
/**
 * PROPS:
 * - $modalId        : string unik untuk id modal (mis. 'modalKlasifikasi')
 * - $hiddenId       : id <input type="hidden"> untuk klasifikasi_surat_id
 * - $displayId      : id <input text readonly> untuk label terpilih
 * - $items          : Collection klasifikasi (id, kode, nama|deskripsi)
 * - (opsional) $kodeTargetId : id hidden untuk menaruh kode klasifikasi (mis. 'klasifikasi_kode')
 */
use Illuminate\Support\Str;

$normalized = $items->map(function ($x) {
    $label   = trim(($x->kode ?? '') . ' - ' . ($x->nama ?? ($x->deskripsi ?? '')));
    $initial = strtoupper(Str::substr($x->kode ?: $label, 0, 1));
    if (!preg_match('/[A-Z]/', $initial)) $initial = '#';
    return (object)[
        'id'      => $x->id,
        'kode'    => ($x->kode ?? ''),  // penting: simpan kode terpisah
        'label'   => $label,
        'initial' => $initial,
    ];
});

$groups  = $normalized->groupBy('initial')->sortKeys();
$letters = range('A','Z');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title" id="{{ $modalId }}Label">Pilih Klasifikasi Surat (A–Z)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-0">
        {{-- Pencarian --}}
        <div class="p-3 border-bottom modal-search">
          <input type="text" class="form-control" placeholder="Ketik untuk mencari… (kode / nama)" data-kt="search">
          <small class="d-block mt-1"><span data-kt="count"></span></small>
        </div>

        {{-- Navigasi huruf --}}
        <div class="px-3 pt-2">
          <div class="d-flex flex-wrap">
            @foreach($letters as $L)
              @if($groups->has($L))
                <a href="#{{ $modalId }}-sec-{{ $L }}" data-kt="jump" class="badge badge-light border mr-2 mb-2">{{ $L }}</a>
              @endif
            @endforeach
            @if($groups->has('#'))
              <a href="#{{ $modalId }}-sec-etc" data-kt="jump" class="badge badge-light border mr-2 mb-2">#</a>
            @endif
          </div>
        </div>

        {{-- Daftar per huruf --}}
        <div class="p-3">
          @foreach($letters as $L)
            @if($groups->has($L))
              <div id="{{ $modalId }}-sec-{{ $L }}" class="mb-3">
                <h6 class="mb-2">{{ $L }}</h6>
                <div class="row" data-group="{{ $L }}">
                  @foreach($groups[$L] as $row)
                    <div class="col-md-6 mb-2" data-kt="wrap">
                      <button type="button"
                              class="btn btn-sm btn-klasifikasi btn-block text-left"
                              data-kt="item"
                              data-id="{{ $row->id }}"
                              data-kode="{{ $row->kode }}"
                              data-label="{{ $row->label }}">
                        <span data-kt="txt">{{ $row->label }}</span>
                      </button>
                    </div>
                  @endforeach
                </div>
                <hr>
              </div>
            @endif
          @endforeach

          @if($groups->has('#'))
            <div id="{{ $modalId }}-sec-etc" class="mb-3">
              <h6 class="mb-2">#</h6>
              <div class="row" data-group="#">
                @foreach($groups['#'] as $row)
                  <div class="col-md-6 mb-2" data-kt="wrap">
                    <button type="button"
                            class="btn btn-sm btn-klasifikasi btn-block text-left"
                            data-kt="item"
                            data-id="{{ $row->id }}"
                            data-kode="{{ $row->kode }}"
                            data-label="{{ $row->label }}">
                      <span data-kt="txt">{{ $row->label }}</span>
                    </button>
                  </div>
                @endforeach
              </div>
              <hr>
            </div>
          @endif

          <div class="text-center text-muted py-4 d-none" data-kt="empty">
            Tidak ada hasil untuk kata kunci tersebut.
          </div>
        </div>
      </div>

      <div class="modal-footer py-2">
        <button type="button" class="btn btn-outline-danger" data-kt="clear">Kosongkan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
/* === Scoped ke modal ini saja === */
#{{ $modalId }} { color:#000; } /* font hitam */
#{{ $modalId }} .modal-title { font-weight:700; }
#{{ $modalId }} .modal-search {
  position: sticky; top:0; z-index: 1021; background:#fff;
  border-bottom: 2px solid #f0f2f5;
}
#{{ $modalId }} .modal-search input {
  color: #000 !important;
  font-weight: 500;
}
#{{ $modalId }} .badge[data-kt="jump"] {
  cursor: pointer;
  padding: .5rem .75rem;
  font-size: .9rem;
  transition: all 0.2s;
}
#{{ $modalId }} .badge[data-kt="jump"]:hover {
  background: #3b5bdb !important;
  color: #fff !important;
}
#{{ $modalId }} .badge { font-weight:600; }
#{{ $modalId }} .btn-klasifikasi{
  background:#fff;
  border:1px solid #e9ecef;
  border-radius:.5rem;
  padding:.6rem .75rem;
  line-height:1.25;
  color:#000; /* teks tombol item hitam */
  transition: box-shadow .15s ease, transform .05s ease;
  cursor: pointer;
}
#{{ $modalId }} .btn-klasifikasi:hover{
  background:#f0f2f5 !important;
  box-shadow:0 2px 8px rgba(0,0,0,.12);
  border-color: #3b5bdb;
}
#{{ $modalId }} .btn-klasifikasi:active{
  transform: translateY(1px);
}
#{{ $modalId }}.modal {
  z-index: 1070 !important; /* Di atas segalanya */
}
.modal-backdrop {
  z-index: 1065 !important;
}
#{{ $modalId }} .btn-klasifikasi span {
  pointer-events: none; /* Biar klik tembus ke button */
}
#{{ $modalId }} [data-kt="count"]{ color:#000; } /* info count hitam */
#{{ $modalId }} h6 { color:#000; font-weight:700; }
#{{ $modalId }} mark {
  padding:.1rem .2rem;
  background:#fff3cd;
  border-radius:.2rem;
}
/* Smooth scroll untuk konten modal */
#{{ $modalId }} .modal-body{ scroll-behavior:smooth; }
</style>
@endpush

@push('scripts')
<script>
(function($){
  var $modal   = $('#{{ $modalId }}');
  var $search  = $modal.find('[data-kt="search"]');
  var $count   = $modal.find('[data-kt="count"]');
  var $empty   = $modal.find('[data-kt="empty"]');
  var $wraps   = $modal.find('[data-kt="wrap"]');

  var $hidden  = $('#{{ $hiddenId }}');
  var $display = $('#{{ $displayId }}');

  var kodeTargetId = @json($kodeTargetId ?? null);
  var $kodeT = kodeTargetId ? $('#'+kodeTargetId) : $(); // <-- FIX: inisialisasi jQuery object utk kode

  function escapeHtml(s){
    return String(s).replace(/[&<>"'`=\/]/g, function(c){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','=':'&#x3D;','`':'&#x60;'}[c]);
    });
  }
  function escapeRegExp(s){ return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
  function highlight(text, q){
    if (!q) return escapeHtml(text);
    var rx = new RegExp('('+escapeRegExp(q)+')','ig');
    return escapeHtml(text).replace(rx, '<mark>$1</mark>');
  }

  function updateCount(){
    var visible = $wraps.filter(':visible').length;
    $count.text(visible ? (visible + ' pilihan tampil') : '');
    $empty.toggleClass('d-none', visible !== 0);
  }

  function filter(q){
    q = (q || '').trim();
    $wraps.each(function(){
      var $btn   = $(this).find('[data-kt="item"]');
      var label  = ($btn.data('label') || '') + '';
      var show   = !q || label.toLowerCase().indexOf(q.toLowerCase()) !== -1;
      $(this).toggle(show);

      // Highlight pada span[data-kt="txt"]
      var $txt = $btn.find('[data-kt="txt"]');
      $txt.html(highlight(label, q));
    });
    updateCount();
  }

  // Smooth scroll untuk navigasi huruf
  $modal.on('click','[data-kt="jump"]', function(e){
    e.preventDefault();
    var sel = $(this).attr('href');
    var $target = $modal.find(sel);
    if ($target.length) $target.get(0).scrollIntoView({behavior:'smooth', block:'start'});
  });

  $search.on('input', function(){ filter($(this).val()); });
  $modal.on('shown.bs.modal', function(){
    setTimeout(function(){ $search.trigger('focus'); }, 100);
    updateCount();
  });
  $modal.on('hidden.bs.modal', function(){
    $search.val('');
    // reset highlight ke label asli
    $wraps.find('[data-kt="item"]').each(function(){
      var $btn = $(this);
      var $txt = $btn.find('[data-kt="txt"]');
      $txt.text($btn.data('label') || '');
    });
    filter('');
  });

  // Pilih item
  $modal.on('click', '[data-kt="item"]', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    var $btn = $(this);
    var id   = $btn.data('id');
    var lbl  = $btn.data('label');
    var kode = $btn.data('kode');

    console.log('Klasifikasi dipilih:', {id, lbl, kode});

    if ($hidden.length)  $hidden.val(id).trigger('change');
    if ($display.length) $display.val(lbl);
    if ($kodeT.length)   $kodeT.val(kode).trigger('change'); 

    $modal.modal('hide');
  });

  // Kosongkan
  $modal.on('click','[data-kt="clear"]', function(){
    if ($hidden.length)  $hidden.val('').trigger('change');
    if ($display.length) $display.val('');
    if ($kodeT.length)   $kodeT.val('').trigger('change');
    $modal.modal('hide');
  });

  // init
  updateCount();
})(jQuery);
</script>
@endpush
