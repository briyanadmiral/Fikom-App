@extends('layouts.app')
@section('title', 'Edit Surat Keputusan')

@push('styles')
<style>
  /* ====== Header & Tone ====== */
  .page-header {
    background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
    margin-bottom: 1.6rem; border: 1px solid #e0e6ed;
    display: flex; align-items: center; gap: 1.3rem;
  }
  .page-header .icon {
    background: linear-gradient(135deg,#6f42c1 0,#9a6ee5 100%);
    width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; box-shadow: 0 1px 10px #6f42c14d; font-size: 1.6rem; color:#fff;
  }
  .page-header-title { font-weight: 700; color: #412674; font-size: 1.7rem; letter-spacing: -0.2px; margin: 0; }
  .page-header-desc { color: #636e7b; font-size: .98rem; margin: .1rem 0 0; }

  /* ====== Cards & Controls ====== */
  .card-settings {
    border: none; border-radius: .9rem; box-shadow: 0 10px 28px rgba(28,28,28,.06);
  }
  .card-settings .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; }
  .form-control, .custom-select { border-radius: .55rem; }
  .input-group-text { background-color: #eef1f6; border-color: #dfe5ec; }

  /* ====== Section header ====== */
  .section-title {
    display:flex; align-items:center; gap:.5rem; margin:0;
    font-weight:700; color:#3e2a74; font-size:1.02rem;
  }
  .section-sub { color:#6c757d; font-size:.86rem; margin-top:.25rem; }

  /* ====== Utilities ====== */
  .bg-purple { background: linear-gradient(135deg,#6f42c1 0,#9a6ee5 100%) !important; }
  .text-purple { color:#6f42c1 !important; }
  .btn-ghost { background:#f6f7fb; border:1px solid #eef0f4; }
  .list-quicknav .list-group-item { border:0; padding:.55rem .75rem; border-radius:.6rem; }
  .list-quicknav .active { background:#f1eaff; color:#5a33b8; font-weight:600; }

  /* ====== Mobile sticky bar ====== */
  .action-bar {
    position: sticky; bottom:0; left:0; right:0; z-index: 998;
    background: rgba(255,255,255,.92); backdrop-filter: blur(6px);
    border-top:1px solid #eaeef4; padding:.75rem;
  }
  @media (min-width: 992px) {
    .action-bar { display:none; }
  }

  /* ====== Card headers berwarna per section ====== */
.card-h{
  border-bottom: 0;
  color: #fff;
  padding: .85rem 1.1rem;
  border-top-left-radius: .9rem;
  border-top-right-radius: .9rem;
}
.card-h .section-title{ color:#fff; }
.card-h .section-sub{ color:rgba(255,255,255,.85); }
.card-h i{ color:#fff; }

/* Variasi warna */
.card-h--purple{ background: linear-gradient(135deg,#6f42c1 0%, #9a6ee5 100%); }
.card-h--teal{   background: linear-gradient(135deg,#0ab39c 0%, #41d6c3 100%); }
.card-h--blue{   background: linear-gradient(135deg,#3f8cff 0%, #6aa6ff 100%); }
.card-h--amber{  background: linear-gradient(135deg,#f59f00 0%, #f7b733 100%); }

/* ====== Card headers berwarna per section (sudah ada) ====== */
.card-h{ border-bottom:0; color:#fff; padding:.85rem 1.1rem; border-top-left-radius:.9rem; border-top-right-radius:.9rem; }
.card-h .section-title{ color:#fff; }
.card-h .section-sub{ color:rgba(255,255,255,.85); }
.card-h i{ color:#fff; }

/* Variasi warna dasar (sudah ada sebagian) */
.card-h--purple{ background: linear-gradient(135deg,#6f42c1 0%, #9a6ee5 100%); }
.card-h--teal{   background: linear-gradient(135deg,#0ab39c 0%, #41d6c3 100%); }
.card-h--blue{   background: linear-gradient(135deg,#3f8cff 0%, #6aa6ff 100%); }
.card-h--amber{  background: linear-gradient(135deg,#f59f00 0%, #f7b733 100%); }

/* ====== Status dinamis ====== */
.card-h--green{  background: linear-gradient(135deg,#16a34a 0%, #34d399 100%); }
.card-h--red{    background: linear-gradient(135deg,#ef4444 0%, #f87171 100%); }

/* Quicknav status */
.list-quicknav a{ display:flex; align-items:center; gap:.55rem; }
.list-quicknav a:not(.active).is-complete{
  background:#eaf9f0; color:#146c2e; border-left:4px solid #22c55e;
}
.list-quicknav a:not(.active).has-error{
  background:#fdecec; color:#b42318; border-left:4px solid #ef4444;
}

</style>
@endpush

@section('content_header')
<div class="page-header mt-2">
  <span class="icon"><i class="fas fa-gavel"></i></span>
  <div>
    <h1 class="page-header-title">Edit Surat Keputusan</h1>
    <p class="page-header-desc mb-0">Perbarui detail untuk surat nomor <b>{{ $sk->nomor }}</b>.</p>
  </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible shadow-sm">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h5 class="mb-1"><i class="icon fas fa-ban"></i> Gagal Menyimpan!</h5>
      <small>Mohon periksa kembali isian Anda:</small>
      <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form id="skForm" action="{{ route('surat_keputusan.update', $sk->id) }}" method="POST" autocomplete="off">
    @csrf
    @method('PUT')
    <div class="row">
      {{-- KIRI: FORM --}}
      <div class="col-lg-8 mb-3">
        @include('surat_keputusan.partials._form', [
          'mode'      => 'edit',
          'keputusan' => $sk,
          'pejabat'   => $pejabat,
          'users'     => $users,
        ])
      </div>

      {{-- KANAN: QUICK NAV + AKSI --}}
      <div class="col-lg-4">
        <div class="card card-settings sticky-top mb-3" style="top:20px;">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0 font-weight-bold text-purple"><i class="fas fa-list-ul mr-2"></i>Navigasi Cepat</h5>
            <span class="badge badge-light border">Form</span>
          </div>
          <div class="card-body py-2">
            <div class="list-group list-quicknav" id="quicknav">
              <a href="#section-utama" class="list-group-item list-group-item-action active">
                <i class="far fa-id-card mr-2"></i>Data Utama
              </a>
              <a href="#section-menimbang" class="list-group-item list-group-item-action">
                <i class="fas fa-balance-scale mr-2"></i>Menimbang <span class="badge badge-secondary ml-1" id="badge-menimbang">0</span>
              </a>
              <a href="#section-mengingat" class="list-group-item list-group-item-action">
                <i class="fas fa-book mr-2"></i>Mengingat <span class="badge badge-secondary ml-1" id="badge-mengingat">0</span>
              </a>
              <a href="#section-menetapkan" class="list-group-item list-group-item-action">
                <i class="fas fa-gavel mr-2"></i>Menetapkan <span class="badge badge-secondary ml-1" id="badge-menetapkan">0</span>
              </a>
            </div>
          </div>
        </div>

        <div class="card card-settings sticky-top" style="top:20px;">
          <div class="card-header"><h5 class="mb-0 font-weight-bold"><i class="fas fa-save mr-2 text-primary"></i>Aksi & Simpan</h5></div>
          <div class="card-body">
            <p class="text-muted small mb-3">
              Pilih tombol di bawah ini untuk menyimpan perubahan Anda.
              <br>Shortcut: <code>Ctrl+S</code> = Draft, <code>Ctrl+Enter</code> = Update & Kirim
            </p>
            <div class="d-grid gap-2">
              <button id="btn-submit-approve" type="submit" name="mode" value="terkirim" class="btn btn-lg btn-success mb-2">
                <i class="fas fa-paper-plane mr-2"></i>Update & Kirim
              </button>
              <button id="btn-submit-draft" type="submit" name="mode" value="draft" class="btn btn-secondary mb-2">
                <i class="fas fa-save mr-1"></i>Simpan sebagai Draft
              </button>
              <a href="{{ route('surat_keputusan.show', $sk->id) }}" class="btn btn-light">
                <i class="fas fa-times mr-1"></i>Batal
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Mobile sticky action bar --}}
    <div class="action-bar d-lg-none">
      <div class="d-flex align-items-center">
        <button id="mb-approve" type="submit" name="mode" value="terkirim" class="btn btn-success btn-block mr-2">
          <i class="fas fa-paper-plane mr-1"></i>Kirim
        </button>
        <button id="mb-draft" type="submit" name="mode" value="draft" class="btn btn-ghost btn-block">
          <i class="fas fa-save mr-1"></i>Draft
        </button>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  // ====== SweetAlert guard untuk penandatangan saat "Update & Kirim"
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('skForm');
    const btnApprove = document.getElementById('btn-submit-approve');
    const mbApprove = document.getElementById('mb-approve');

    function requireSigner(e) {
      const signer = form.querySelector('select[name="penandatangan"]');
      if (!signer || !signer.value) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'Penandatangan Belum Dipilih',
          text: 'Silakan pilih penandatangan sebelum mengirim surat untuk persetujuan.',
        });
        signer && signer.focus();
        return false;
      }
      return true;
    }

    if (btnApprove) btnApprove.addEventListener('click', requireSigner);
    if (mbApprove)  mbApprove .addEventListener('click', requireSigner);
  });

  // ====== QuickNav aktif saat scroll
  (function () {
    const links = [].slice.call(document.querySelectorAll('#quicknav a'));
    const sections = links.map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);

    function onScroll() {
      const y = window.scrollY + 110; // offset header
      let current = sections[0];
      for (const sec of sections) {
        if (sec.offsetTop <= y) current = sec;
      }
      links.forEach(a => a.classList.remove('active'));
      const active = links.find(a => a.getAttribute('href') === '#' + current.id);
      active && active.classList.add('active');
    }
    window.addEventListener('scroll', onScroll, { passive:true });
    onScroll();
  })();

  // ====== Counter badge dinamis
  function updateBadges() {
    const b1 = document.getElementById('badge-menimbang');
    const b2 = document.getElementById('badge-mengingat');
    const b3 = document.getElementById('badge-menetapkan');
    b1 && (b1.textContent = document.querySelectorAll('#menimbang-list .menimbang-item').length);
    b2 && (b2.textContent = document.querySelectorAll('#mengingat-list .mengingat-item').length);
    b3 && (b3.textContent = document.querySelectorAll('#menetapkan-list .menetapkan-item').length);
  }
  document.addEventListener('click', function(e) {
    if (e.target.closest('#add-menimbang') || e.target.closest('#add-mengingat') || e.target.closest('#add-menetapkan') || e.target.classList.contains('remove-row') || e.target.closest('.btn-remove-menetapkan')) {
      setTimeout(updateBadges, 50);
    }
  });
  document.addEventListener('DOMContentLoaded', updateBadges);

  // ====== Unsaved changes guard
  (function () {
    const form = document.getElementById('skForm');
    let dirty = false;
    form.addEventListener('change', () => dirty = true, true);
    form.addEventListener('input',  () => dirty = true, true);
    window.addEventListener('beforeunload', function (e) {
      if (!dirty) return;
      const confirmationMessage = 'Perubahan belum disimpan. Yakin meninggalkan halaman?';
      (e || window.event).returnValue = confirmationMessage;
      return confirmationMessage;
    });
    form.addEventListener('submit', () => { dirty = false; });
  })();

  // ====== Keyboard shortcuts: Ctrl+S (Draft), Ctrl+Enter (Approve)
  (function () {
    const form = document.getElementById('skForm');
    document.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-draft');
        if (btn) { btn.click(); }
      }
      if ((e.ctrlKey || e.metaKey) && (e.key === 'Enter')) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-approve');
        if (btn) { btn.click(); }
      }
    });
  })();
</script>
@endpush
