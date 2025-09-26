{{-- resources/views/surat_keputusan/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Buat Surat Keputusan')

@push('styles')
<style>
  /* ====== Header Halaman ====== */
  .page-header {
    background: #f3f6fa;
    padding: 1.3rem 2.2rem;
    border-radius: 1.1rem;
    margin-bottom: 1.6rem;
    border: 1px solid #e0e6ed;
    display: flex;
    align-items: center;
    gap: 1.3rem;
  }

  .page-header .icon {
    background: linear-gradient(135deg, #6f42c1 0, #9a6ee5 100%);
    width: 54px;
    height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    box-shadow: 0 1px 10px #6f42c14d;
    font-size: 1.6rem;
    color: #fff;
  }

  .page-header-title {
    font-weight: 700;
    color: #412674;
    font-size: 1.7rem;
    letter-spacing: -.2px;
    margin: 0;
  }

  .page-header-desc {
    color: #636e7b;
    font-size: .98rem;
    margin: .1rem 0 0;
  }

  /* ====== Cards & Inputs ====== */
  .card-settings {
    border: none;
    border-radius: .9rem;
    box-shadow: 0 10px 28px rgba(28, 28, 28, .06);
  }

  .card-settings .card-header {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
  }

  .form-control,
  .custom-select {
    border-radius: .55rem;
  }

  .input-group-text {
    background: #eef1f6;
    border-color: #dfe5ec;
  }

  /* ====== Section headers base ====== */
  .card-h {
    border-bottom: 0;
    color: #fff;
    padding: .85rem 1.1rem;
    border-top-left-radius: .9rem;
    border-top-right-radius: .9rem;
  }

  .card-h .section-title {
    color: #fff;
  }

  .card-h .section-sub {
    color: rgba(255, 255, 255, .85);
  }

  .card-h i {
    color: #fff;
  }

  /* Variasi warna dasar per section */
  .card-h--purple {
    background: linear-gradient(135deg, #6f42c1 0%, #9a6ee5 100%);
  }

  .card-h--teal {
    background: linear-gradient(135deg, #0ab39c 0%, #41d6c3 100%);
  }

  .card-h--blue {
    background: linear-gradient(135deg, #3f8cff 0%, #6aa6ff 100%);
  }

  .card-h--amber {
    background: linear-gradient(135deg, #f59f00 0%, #f7b733 100%);
  }

  /* Status dinamis */
  .card-h--green {
    background: linear-gradient(135deg, #16a34a 0%, #34d399 100%);
  }

  .card-h--red {
    background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
  }

  /* Quicknav */
  .list-quicknav .list-group-item {
    border: 0;
    padding: .55rem .75rem;
    border-radius: .6rem;
    display: flex;
    align-items: center;
    gap: .55rem;
  }

  .list-quicknav .active {
    background: #f1eaff;
    color: #5a33b8;
    font-weight: 600;
  }

  .list-quicknav a:not(.active).is-complete {
    background: #eaf9f0;
    color: #146c2e;
    border-left: 4px solid #22c55e;
  }

  .list-quicknav a:not(.active).has-error {
    background: #fdecec;
    color: #b42318;
    border-left: 4px solid #ef4444;
  }

  .text-purple { color:#6f42c1 !important; }
  .btn-ghost   { background:#f6f7fb; border:1px solid #eef0f4; }

  /* Mobile sticky action bar */
  .action-bar {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 998;
    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(6px);
    border-top: 1px solid #eaeef4;
    padding: .75rem;
  }

  @media (min-width: 992px) {
    .action-bar {
      display: none;
    }
  }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2">
  <span class="icon"><i class="fas fa-gavel"></i></span>
  <div>
    <h1 class="page-header-title">Buat Surat Keputusan</h1>
    <p class="page-header-desc mb-0">Mulai susun SK baru. Nomor akan diisikan otomatis jika tersedia.</p>
  </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

  {{-- Error validasi --}}
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

  <form id="skForm" action="{{ route('surat_keputusan.store') }}" method="POST" autocomplete="off">
    @csrf
    <div class="row">
      {{-- KIRI: FORM --}}
      <div class="col-lg-8 mb-3">
        @include('surat_keputusan.partials._form', [
        'mode' => 'create',
        'keputusan' => null,
        'autoNomor' => $autoNomor ?? null,
        'pejabat' => $pejabat ?? collect(),
        'users' => $users ?? collect(),
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
                <i class="far fa-id-card"></i>Data Utama
              </a>
              <a href="#section-menimbang" class="list-group-item list-group-item-action">
                <i class="fas fa-balance-scale"></i>Menimbang <span class="badge badge-secondary ml-1" id="badge-menimbang">0</span>
              </a>
              <a href="#section-mengingat" class="list-group-item list-group-item-action">
                <i class="fas fa-book"></i>Mengingat <span class="badge badge-secondary ml-1" id="badge-mengingat">0</span>
              </a>
              <a href="#section-menetapkan" class="list-group-item list-group-item-action">
                <i class="fas fa-gavel"></i>Menetapkan <span class="badge badge-secondary ml-1" id="badge-menetapkan">0</span>
              </a>
            </div>
          </div>
        </div>

        <div class="card card-settings sticky-top" style="top:20px;">
          <div class="card-header">
            <h5 class="mb-0 font-weight-bold"><i class="fas fa-save mr-2 text-primary"></i>Aksi & Simpan</h5>
          </div>
          <div class="card-body">
            <p class="text-muted small mb-3">
              Pilih tombol di bawah ini untuk menyimpan perubahan Anda.
              <br>Shortcut: <code>Ctrl+S</code> = Draft, <code>Ctrl+Enter</code> = Submit ke Penandatangan
            </p>
            <div class="d-grid gap-2">
              <button type="button" id="mb-preview" class="btn btn-outline-primary btn-block mr-2 mb-3">
                <i class="fas fa-eye mr-1"></i>Preview
              </button>
              <button id="btn-submit-approve" type="submit" name="mode" value="terkirim" class="btn btn-success mb-2">
                <i class="fas fa-paper-plane mr-1"></i>Submit ke Penandatangan
              </button>
              <button id="btn-submit-draft" type="submit" name="mode" value="draft" class="btn btn-secondary mb-2">
                <i class="fas fa-save mr-1"></i>Simpan Draft
              </button>
              <a href="{{ route('surat_keputusan.index') }}" class="btn btn-dark mb-2 ">
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
  @include('surat_keputusan.partials._preview_modal')
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/sk-preview.js') }}"></script>
<script>
  // ====== Guard penandatangan saat submit ke penandatangan
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('skForm');
    const btnApprove = document.getElementById('btn-submit-approve');
    const mbApprove = document.getElementById('mb-approve');

    function requireSigner(e) {
      const signer = form.querySelector('select[name="penandatangan"]');
      if (!signer || !signer.value) {
        e.preventDefault();
        if (window.Swal) {
          Swal.fire({
            icon: 'warning',
            title: 'Penandatangan Belum Dipilih',
            text: 'Silakan pilih penandatangan sebelum mengirim untuk persetujuan.'
          });
        } else {
          alert('Silakan pilih Penandatangan terlebih dahulu sebelum mengirim untuk persetujuan.');
        }
        signer && signer.focus();
        return false;
      }
      return true;
    }
    btnApprove && btnApprove.addEventListener('click', requireSigner);
    mbApprove && mbApprove.addEventListener('click', requireSigner);
  });

  // ====== QuickNav aktif saat scroll
  (function() {
    const links = [].slice.call(document.querySelectorAll('#quicknav a'));
    const sections = links.map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);

    function onScroll() {
      const y = window.scrollY + 110;
      let current = sections[0];
      for (const sec of sections)
        if (sec.offsetTop <= y) current = sec;
      links.forEach(a => a.classList.remove('active'));
      const active = links.find(a => a.getAttribute('href') === '#' + current.id);
      active && active.classList.add('active');
    }
    window.addEventListener('scroll', onScroll, {
      passive: true
    });
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
    if (e.target.closest('#add-menimbang') || e.target.closest('#add-mengingat') || e.target.closest('#add-menetapkan') ||
      e.target.classList.contains('remove-row') || e.target.closest('.btn-remove-menetapkan')) {
      setTimeout(updateBadges, 50);
    }
  });
  document.addEventListener('DOMContentLoaded', updateBadges);

  // ====== Unsaved changes guard
  (function() {
    const form = document.getElementById('skForm');
    let dirty = false;
    form.addEventListener('change', () => dirty = true, true);
    form.addEventListener('input', () => dirty = true, true);
    window.addEventListener('beforeunload', function(e) {
      if (!dirty) return;
      const confirmationMessage = 'Perubahan belum disimpan. Yakin meninggalkan halaman?';
      (e || window.event).returnValue = confirmationMessage;
      return confirmationMessage;
    });
    form.addEventListener('submit', () => {
      dirty = false;
    });
  })();

  // ====== Keyboard shortcuts
  (function() {
    document.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('btn-submit-draft')?.click();
      }
      if ((e.ctrlKey || e.metaKey) && (e.key === 'Enter')) {
        e.preventDefault();
        document.getElementById('btn-submit-approve')?.click();
      }
    });
  })();

  // ====== Status warna dinamis header & quicknav (hijau/lengkap, merah/error)
  (function() {
    const ALL_HEADER_CLASSES = ['card-h--purple', 'card-h--teal', 'card-h--blue', 'card-h--amber', 'card-h--green', 'card-h--red'];

    function setHeaderState(id, state) {
      const header = document.querySelector('#h-' + id);
      if (!header) return;
      ALL_HEADER_CLASSES.forEach(c => header.classList.remove(c));
      const base = header?.dataset?.base || 'purple';
      if (state === 'complete') header.classList.add('card-h--green');
      else if (state === 'error') header.classList.add('card-h--red');
      else header.classList.add('card-h--' + base);
    }

    function setQuicknavState(id, state) {
      const link = document.querySelector('#quicknav a[href="#section-' + id + '"]');
      if (!link) return;
      link.classList.remove('has-error', 'is-complete');
      if (state === 'complete') link.classList.add('is-complete');
      else if (state === 'error') link.classList.add('has-error');
    }
    const isFilled = v => v && String(v).trim().length > 0;
    const hasInvalidIn = id => !!document.querySelector('#section-' + id + ' .is-invalid, #section-' + id + ' [aria-invalid="true"]');

    function textFromHTML(html) {
      const d = document.createElement('div');
      d.innerHTML = html || '';
      return d.textContent.replace(/\u00a0/g, ' ').trim();
    }

    function checkUtama() {
      const tgl = document.querySelector('#section-utama input[name="tanggal_asli"]')?.value;
      const tentang = document.querySelector('#section-utama input[name="tentang"]')?.value;
      if (hasInvalidIn('utama')) return 'error';
      return (isFilled(tgl) && isFilled(tentang)) ? 'complete' : 'base';
    }

    function checkMenimbang() {
      if (hasInvalidIn('menimbang')) return 'error';
      const items = [...document.querySelectorAll('#section-menimbang input[name="menimbang[]"]')].map(i => i.value).filter(isFilled);
      return items.length > 0 ? 'complete' : 'base';
    }

    function checkMengingat() {
      if (hasInvalidIn('mengingat')) return 'error';
      const items = [...document.querySelectorAll('#section-mengingat input[name="mengingat[]"]')].map(i => i.value).filter(isFilled);
      return items.length > 0 ? 'complete' : 'base';
    }

    function checkMenetapkan() {
      if (hasInvalidIn('menetapkan')) return 'error';
      let contents = [];
      if (window.editors && Object.keys(window.editors).length) {
        for (const k of Object.keys(window.editors)) {
          try {
            contents.push(window.editors[k].getData());
          } catch (_) {}
        }
      } else {
        contents = [...document.querySelectorAll('#section-menetapkan textarea.wysiwyg')].map(t => t.value);
      }
      return contents.some(h => isFilled(textFromHTML(h))) ? 'complete' : 'base';
    }

    function evaluate() {
      const s1 = checkUtama();
      setHeaderState('utama', s1);
      setQuicknavState('utama', s1);
      const s2 = checkMenimbang();
      setHeaderState('menimbang', s2);
      setQuicknavState('menimbang', s2);
      const s3 = checkMengingat();
      setHeaderState('mengingat', s3);
      setQuicknavState('mengingat', s3);
      const s4 = checkMenetapkan();
      setHeaderState('menetapkan', s4);
      setQuicknavState('menetapkan', s4);
    }
    document.addEventListener('input', evaluate, true);
    document.addEventListener('change', evaluate, true);
    document.addEventListener('click', function(e) {
      if (e.target.closest('#add-menimbang') || e.target.closest('#add-mengingat') ||
        e.target.closest('#add-menetapkan') || e.target.classList.contains('remove-row') ||
        e.target.closest('.btn-remove-menetapkan')) {
        setTimeout(evaluate, 60);
      }
    });

    function hookEditors() {
      if (!window.editors) return;
      Object.values(window.editors).forEach(ed => {
        if (ed && !ed._skHooked) {
          ed.model.document.on('change:data', () => evaluate());
          ed._skHooked = true;
        }
      });
    }
    setInterval(hookEditors, 600);
    document.addEventListener('DOMContentLoaded', evaluate);
  })();
</script>
@endpush