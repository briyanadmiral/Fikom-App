{{-- resources/views/surat_keputusan/partials/_form.blade.php --}}
@csrf

{{-- ========================= --}}
{{-- 1) Data Utama             --}}
{{-- ========================= --}}
<div id="section-utama" class="card mb-4">
  <div id="h-utama" data-base="purple"
     class="card-header card-h card-h--purple d-flex align-items-center justify-content-between">
  <h6 class="section-title mb-0"><i class="fas fa-info-circle mr-2"></i>Data Utama</h6>
  <span class="section-sub">Lengkapi identitas SK</span>
</div>


  <div class="card-body">
    <div class="row">
      {{-- Nomor (readonly saat edit; create pakai autoNomor) --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Nomor SK</label>
        <input
          type="text"
          name="nomor"
          class="form-control @error('nomor') is-invalid @enderror"
          value="{{ old('nomor', $autoNomor ?? ($keputusan->nomor ?? '')) }}"
          {{ $mode === 'create' ? '' : 'readonly' }}
        >
        @error('nomor')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">Sistem menggunakan penomoran otomatis per tahun.</small>
      </div>

      {{-- Tanggal SK (asli/draft) --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Tanggal SK</label>
        <input
          type="date"
          name="tanggal_asli"
          class="form-control @error('tanggal_asli') is-invalid @enderror"
          required
          value="{{ old('tanggal_asli', optional($keputusan??null)->tanggal_asli?->format('Y-m-d') ?? date('Y-m-d')) }}"
        >
        @error('tanggal_asli')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Tentang / Perihal --}}
      <div class="col-md-12 mb-3">
        <label class="form-label">Tentang</label>
        <input
          type="text"
          name="tentang"
          class="form-control @error('tentang') is-invalid @enderror"
          required
          value="{{ old('tentang', $keputusan->tentang ?? '') }}"
          placeholder="Contoh: Penetapan Visi, Misi, Tujuan ..."
        >
        @error('tentang')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Penandatangan (Wajib saat submit approve) --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Penandatangan</label>
        <select
          name="penandatangan"
          class="custom-select @error('penandatangan') is-invalid @enderror"
        >
          <option value="">-- Pilih Pejabat (Dekan / Wakil Dekan) --</option>
          @foreach(($pejabat ?? collect()) as $p)
            <option value="{{ $p->id }}"
              {{ (string)old('penandatangan', $keputusan->penandatangan ?? '') === (string)$p->id ? 'selected' : '' }}>
              {{ $p->nama_lengkap }} ({{ $p->peran->deskripsi ?? 'Pejabat' }})
            </option>
          @endforeach
        </select>
        @error('penandatangan')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">Jika tombol “Update & Kirim” dipilih, kolom ini wajib diisi.</small>
      </div>

      {{-- Tembusan (chips) --}}
      <div class="col-md-6 mb-3">
        <label class="form-label">Tembusan (opsional)</label>
        <div id="tembusan-chips" class="form-control d-flex align-items-center flex-wrap" style="min-height:38px; gap:.35rem; padding:.35rem .45rem;">
          <input id="tembusan-input" type="text" class="border-0 flex-grow-1" style="min-width:160px; outline:0;" placeholder="Ketik lalu Enter / koma" />
        </div>
        <input type="hidden" name="tembusan" id="tembusan-hidden" value="{{ old('tembusan', $keputusan->tembusan ?? '') }}">
        @error('tembusan')
  <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror

        <small class="text-muted d-block mt-1">Pisahkan penerima dengan Enter/koma. Contoh: “Yth. Rektor”, “Yth. Ketua Prodi”.</small>
      </div>
    </div>
  </div>
</div>

{{-- ========================= --}}
{{-- 2) Menimbang             --}}
{{-- ========================= --}}
<div id="section-menimbang" class="card mb-4">
  <div id="h-menimbang" data-base="teal"
     class="card-header card-h card-h--teal d-flex align-items-center justify-content-between">
  <h6 class="section-title mb-0"><i class="fas fa-balance-scale mr-2"></i>Menimbang</h6>
  <span class="section-sub">Tambahkan butir a), b), c)...</span>
</div>


  <div class="card-body">
    <div id="menimbang-list">
      @php
        $menimbangOld = old('menimbang', $keputusan->menimbang ?? ['']);
        if (!is_array($menimbangOld) || empty($menimbangOld)) $menimbangOld = [''];
      @endphp

      @foreach($menimbangOld as $i => $val)
        <div class="input-group mb-2 menimbang-item">
          <span class="input-group-text">{{ chr(97 + $i) }})</span>
          <input
            type="text"
            name="menimbang[]"
            class="form-control @error("menimbang.$i") is-invalid @enderror"
            value="{{ $val }}"
            placeholder="Tulis poin pertimbangan..."
          >
          <div class="input-group-append">
            <button class="btn btn-outline-danger remove-row" type="button" {{ $i === 0 ? 'style=display:none' : '' }} title="Hapus">
              <i class="fas fa-times"></i>
            </button>
          </div>
          @error("menimbang.$i")
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
      @endforeach
    </div>

    @error('menimbang')
      <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    <button type="button" class="btn btn-sm btn-outline-primary" id="add-menimbang">
      <i class="fas fa-plus mr-1"></i>Tambah Butir
    </button>
  </div>
</div>

{{-- ========================= --}}
{{-- 3) Mengingat             --}}
{{-- ========================= --}}
<div id="section-mengingat" class="card mb-4">
  <div id="h-mengingat" data-base="blue"
     class="card-header card-h card-h--blue d-flex align-items-center justify-content-between">
  <h6 class="section-title mb-0"><i class="fas fa-book mr-2"></i>Mengingat</h6>
  <span class="section-sub">Tambahkan butir 1., 2., 3....</span>
</div>


  <div class="card-body">
    <div id="mengingat-list">
      @php
        $mengingatOld = old('mengingat', $keputusan->mengingat ?? ['']);
        if (!is_array($mengingatOld) || empty($mengingatOld)) $mengingatOld = [''];
      @endphp

      @foreach($mengingatOld as $i => $val)
        <div class="input-group mb-2 mengingat-item">
          <span class="input-group-text">{{ $i + 1 }}.</span>
          <input
            type="text"
            name="mengingat[]"
            class="form-control @error("mengingat.$i") is-invalid @enderror"
            value="{{ $val }}"
            placeholder="Tulis dasar hukum/rujukan..."
          >
          <div class="input-group-append">
            <button class="btn btn-outline-danger remove-row" type="button" {{ $i === 0 ? 'style=display:none' : '' }} title="Hapus">
              <i class="fas fa-times"></i>
            </button>
          </div>
          @error("mengingat.$i")
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
      @endforeach
    </div>

    @error('mengingat')
      <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    <button type="button" class="btn btn-sm btn-outline-primary" id="add-mengingat">
      <i class="fas fa-plus mr-1"></i>Tambah Butir
    </button>
  </div>
</div>

{{-- ========================= --}}
{{-- 4) MENETAPKAN (array)    --}}
{{-- ========================= --}}
<div id="section-menetapkan" class="card mb-4">
  <div id="h-menetapkan" data-base="amber"
     class="card-header card-h card-h--amber d-flex align-items-center justify-content-between">
  <h6 class="section-title mb-0"><i class="fas fa-gavel mr-2"></i>Menetapkan (Diktum)</h6>
  <span class="section-sub">Isi diktum KESATU, KEDUA, dst.</span>
</div>


  <div class="card-body">
    @php
      $labels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
      $menetapkanOld = old('menetapkan', $keputusan->menetapkan ?? [['judul' => 'KESATU', 'isi' => '']]);
      if (is_string($menetapkanOld)) {
        $tmp = json_decode($menetapkanOld, true);
        if (is_array($tmp)) { $menetapkanOld = $tmp; }
      }
      if (!is_array($menetapkanOld) || empty($menetapkanOld)) {
        $menetapkanOld = [['judul' => 'KESATU', 'isi' => '']];
      }
    @endphp

    <div id="menetapkan-list">
      @foreach($menetapkanOld as $i => $mt)
        <div class="menetapkan-item mb-3 border rounded p-3 bg-light">
          <div class="row align-items-start">
            <div class="col-md-2 mb-2">
              <label class="form-label small text-muted">Judul</label>
              <input
                type="text"
                class="form-control @error("menetapkan.$i.judul") is-invalid @enderror"
                name="menetapkan[{{ $i }}][judul]"
                value="{{ $mt['judul'] ?? ($labels[$i] ?? 'KETENTUAN') }}"
                readonly
              >
              @error("menetapkan.$i.judul")
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-9 mb-2">
              <label class="form-label small text-muted d-flex justify-content-between">
                <span>Isi Keputusan</span>
                <small class="text-muted">Gunakan list/penomoran seperlunya</small>
              </label>
              <textarea
                class="form-control wysiwyg @error("menetapkan.$i.isi") is-invalid @enderror"
                data-editor-id="menetapkan-{{ $i }}"
                name="menetapkan[{{ $i }}][isi]"
                rows="3"
                placeholder="Isi keputusan ...">{!! $mt['isi'] ?? '' !!}</textarea>
              @error("menetapkan.$i.isi")
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-1 text-right">
              <label class="form-label d-block invisible">.</label>
              <button type="button" class="btn btn-danger btn-remove-menetapkan" {{ $i === 0 ? 'style=display:none' : '' }} title="Hapus diktum">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @error('menetapkan')
      <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    <button type="button" class="btn btn-sm btn-outline-primary" id="add-menetapkan">
      <i class="fas fa-plus mr-1"></i>Tambah Diktum
    </button>
  </div>
</div>

@push('scripts')
<script>
(function () {
  /* ================= CKEditor loader (aman untuk double-load) ================= */
  const CK_SRC = 'https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js';

  function ensureCkReady() {
    return new Promise((resolve, reject) => {
      if (window.ClassicEditor) return resolve(window.ClassicEditor);
      const existing = document.querySelector('script[src*="ckeditor5"][src*="/classic/ckeditor.js"]');
      if (existing) {
        existing.addEventListener('load', () => resolve(window.ClassicEditor));
        existing.addEventListener('error', () => reject(new Error('Gagal memuat CKEditor (existing tag).')));
        return;
      }
      const s = document.createElement('script');
      s.src = CK_SRC; s.async = true;
      s.onload = () => resolve(window.ClassicEditor);
      s.onerror = () => reject(new Error('Gagal memuat CKEditor CDN.'));
      document.head.appendChild(s);
    });
  }

  /* ================= Reindex helpers ================= */
  function reindexMenimbang() {
    document.querySelectorAll('#menimbang-list .menimbang-item').forEach((el, i) => {
      const tag = el.querySelector('.input-group-text');
      if (tag) tag.textContent = String.fromCharCode(97 + i) + ')';
      const btn = el.querySelector('.remove-row');
      if (btn) btn.style.display = (i === 0 ? 'none' : '');
    });
  }
  function reindexMengingat() {
    document.querySelectorAll('#mengingat-list .mengingat-item').forEach((el, i) => {
      const tag = el.querySelector('.input-group-text');
      if (tag) tag.textContent = (i + 1) + '.';
      const btn = el.querySelector('.remove-row');
      if (btn) btn.style.display = (i === 0 ? 'none' : '');
    });
  }
  function reindexMenetapkan() {
    const labels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
    document.querySelectorAll('#menetapkan-list .menetapkan-item').forEach((wrap, i) => {
      wrap.querySelectorAll('input, textarea').forEach(inp => {
        const name = inp.getAttribute('name');
        if (name) inp.setAttribute('name', name.replace(/menetapkan\[\d+\]/, `menetapkan[${i}]`));
      });
      const judulEl = wrap.querySelector('input[name^="menetapkan["][name$="[judul]"]');
      if (judulEl) judulEl.value = labels[i] || 'KETENTUAN';
      const btn = wrap.querySelector('.btn-remove-menetapkan');
      if (btn) btn.style.display = (i === 0 ? 'none' : '');
      const ta = wrap.querySelector('textarea.wysiwyg');
      if (ta) ta.dataset.editorId = 'menetapkan-' + i;
    });
  }

  /* ================= CKEditor init ================= */
  const editors = {};
  function hasPlugin(ClassicEditor, name) {
    const list = ClassicEditor && ClassicEditor.builtinPlugins ? ClassicEditor.builtinPlugins : [];
    return list.some(p => p.pluginName === name);
  }
  function buildToolbar(ClassicEditor) {
    const items = ['undo','redo','|','heading','|','bold','italic','|','numberedList','bulletedList','|','link','blockQuote'];
    if (hasPlugin(ClassicEditor, 'Underline')) items.splice(6, 0, 'underline');
    if (hasPlugin(ClassicEditor, 'Alignment')) items.splice(items.indexOf('bulletedList') + 1, 0, 'alignment');
    if (hasPlugin(ClassicEditor, 'Indent')) { items.push('outdent','indent'); }
    if (hasPlugin(ClassicEditor, 'Font'))   { items.push('|','fontFamily','fontSize'); }
    return items;
  }
  function initEditor(textarea, ClassicEditor) {
    if (!textarea) return;
    const id = textarea.dataset.editorId || ('ed-' + Math.random().toString(36).slice(2));
    textarea.dataset.editorId = id;
    const config = { toolbar: { items: buildToolbar(ClassicEditor) }, placeholder: textarea.getAttribute('placeholder') || 'Isi keputusan …' };
    if (hasPlugin(ClassicEditor, 'Font')) {
      config.fontFamily = { options: ['default','Times New Roman, Times, serif','Georgia, serif','Arial, Helvetica, sans-serif','Calibri, sans-serif','Tahoma, sans-serif','Courier New, Courier, monospace'] };
      config.fontSize   = { options: ['tiny','small','default','big','huge'] };
    }
    return ClassicEditor.create(textarea, config)
      .then(instance => { editors[id] = instance; })
      .catch(err => { console.warn('[CKEditor]', err && err.message ? err.message : err); });
  }
  const ckReadyPromise = ensureCkReady().then(ClassicEditor => {
    document.querySelectorAll('textarea.wysiwyg').forEach(el => initEditor(el, ClassicEditor));
    return ClassicEditor;
  });

  /* ================= DOM actions (add/remove) ================= */
  document.addEventListener('click', function(e) {
    // Tambah Menimbang
    if (e.target && e.target.id === 'add-menimbang') {
      const w = document.querySelector('#menimbang-list');
      const n = w.querySelectorAll('.menimbang-item').length;
      const html = `<div class="input-group mb-2 menimbang-item">
        <span class="input-group-text">${String.fromCharCode(97 + n)})</span>
        <input type="text" name="menimbang[]" class="form-control" placeholder="Tulis poin pertimbangan...">
        <div class="input-group-append"><button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button></div>
      </div>`;
      w.insertAdjacentHTML('beforeend', html);
      reindexMenimbang();
    }

    // Tambah Mengingat
    if (e.target && e.target.id === 'add-mengingat') {
      const w = document.querySelector('#mengingat-list');
      const n = w.querySelectorAll('.mengingat-item').length + 1;
      const html = `<div class="input-group mb-2 mengingat-item">
        <span class="input-group-text">${n}.</span>
        <input type="text" name="mengingat[]" class="form-control" placeholder="Tulis dasar hukum/rujukan...">
        <div class="input-group-append"><button class="btn btn-outline-danger remove-row" type="button" title="Hapus"><i class="fas fa-times"></i></button></div>
      </div>`;
      w.insertAdjacentHTML('beforeend', html);
      reindexMengingat();
    }

    // Hapus baris menimbang/mengingat (kecuali index 0)
    if (e.target && (e.target.classList.contains('remove-row') || e.target.closest('.remove-row'))) {
      const btn = e.target.closest('.remove-row');
      const item = btn.closest('.input-group');
      if (!item) return;
      if (item.parentElement.id === 'menimbang-list') {
        const idx = [...item.parentElement.children].indexOf(item);
        if (idx > 0) item.remove();
        reindexMenimbang();
      }
      if (item.parentElement.id === 'mengingat-list') {
        const idx = [...item.parentElement.children].indexOf(item);
        if (idx > 0) item.remove();
        reindexMengingat();
      }
    }

    // Tambah Diktum Menetapkan
    if (e.target && e.target.id === 'add-menetapkan') {
      const list = document.getElementById('menetapkan-list');
      const idx  = list.querySelectorAll('.menetapkan-item').length;
      const labels = ['KESATU','KEDUA','KETIGA','KEEMPAT','KELIMA','KEENAM','KETUJUH','KEDELAPAN','KESEMBILAN','KESEPULUH'];
      const judul = labels[idx] || 'KETENTUAN';
      const html = `
        <div class="menetapkan-item mb-3 border rounded p-3 bg-light">
          <div class="row align-items-start">
            <div class="col-md-2 mb-2">
              <label class="form-label small text-muted">Judul</label>
              <input type="text" class="form-control" name="menetapkan[${idx}][judul]" value="${judul}" readonly>
            </div>
            <div class="col-md-9 mb-2">
              <label class="form-label small text-muted d-flex justify-content-between">
                <span>Isi Keputusan</span>
                <small class="text-muted">Gunakan list/penomoran seperlunya</small>
              </label>
              <textarea class="form-control wysiwyg" data-editor-id="menetapkan-${idx}" name="menetapkan[${idx}][isi]" rows="3" placeholder="Isi keputusan …"></textarea>
            </div>
            <div class="col-md-1 text-right">
              <label class="form-label d-block invisible">.</label>
              <button type="button" class="btn btn-danger btn-remove-menetapkan" title="Hapus diktum">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
        </div>`;
      list.insertAdjacentHTML('beforeend', html);
      reindexMenetapkan();
      ckReadyPromise.then(ClassicEditor => {
        const ta = list.querySelector('.menetapkan-item:last-child textarea.wysiwyg');
        initEditor(ta, ClassicEditor);
      });
    }

    // Hapus diktum Menetapkan (kecuali item pertama)
    if (e.target && (e.target.classList.contains('btn-remove-menetapkan') || e.target.closest('.btn-remove-menetapkan'))) {
      const list = document.getElementById('menetapkan-list');
      const wrap = e.target.closest('.menetapkan-item');
      if (!wrap) return;
      const idx = Array.from(list.children).indexOf(wrap);
      if (idx > 0) {
        const ta = wrap.querySelector('textarea.wysiwyg');
        try {
          if (ta && ta.dataset.editorId && window.editors && window.editors[ta.dataset.editorId]) {
            window.editors[ta.dataset.editorId].destroy().catch(()=>{}).finally(()=>{ delete window.editors[ta.dataset.editorId]; });
          }
        } catch(_) {}
        wrap.remove();
        reindexMenetapkan();
      }
    }
  });

  // Inisialisasi default indexing saat halaman siap
  document.addEventListener('DOMContentLoaded', function(){
    reindexMenimbang();
    reindexMengingat();
    reindexMenetapkan();
  });

  /* ================= TEMBUSAN: Chips input ================= */
  (function TembusanChips(){
    const hidden = document.getElementById('tembusan-hidden');
    const box = document.getElementById('tembusan-chips');
    const input = document.getElementById('tembusan-input');
    if (!hidden || !box || !input) return;

    function parseCSV(str) {
      return (str || '')
        .split(',')
        .map(s => s.trim())
        .filter(Boolean);
    }
    function renderChip(text) {
      const chip = document.createElement('span');
      chip.className = 'badge badge-light border';
      chip.style.padding = '.42rem .6rem';
      chip.style.borderRadius = '999px';
      chip.style.display = 'inline-flex';
      chip.style.alignItems = 'center';
      chip.style.gap = '.35rem';
      chip.innerHTML = `<i class="far fa-user mr-1"></i>${text} <a href="#" class="text-danger ml-1" aria-label="Hapus" title="Hapus">&times;</a>`;
      chip.querySelector('a').addEventListener('click', function(e){
        e.preventDefault();
        chip.remove();
        syncHidden();
      });
      box.insertBefore(chip, input);
    }
    function syncHidden() {
      const labels = [...box.querySelectorAll('.badge')].map(b => b.childNodes[0].textContent || b.textContent).map(s => s.replace('×','').trim());
      hidden.value = labels.join(', ');
    }
    function addFromInput() {
      const raw = input.value.trim();
      if (!raw) return;
      const parts = raw.split(',').map(s => s.trim()).filter(Boolean);
      parts.forEach(renderChip);
      input.value = '';
      syncHidden();
    }

    // Init from hidden
    parseCSV(hidden.value).forEach(renderChip);

    input.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addFromInput();
      } else if (e.key === 'Backspace' && !input.value) {
        const last = box.querySelector('.badge:last-of-type');
        if (last) last.remove();
        syncHidden();
      }
    });
    input.addEventListener('blur', addFromInput);
  })();

  /* Expose editors map globally (optional, used in delete) */
  window.editors = window.editors || {};
})();

// Map class dasar
  const BASE_CLASSES = ['purple','teal','blue','amber'];
  const ALL_HEADER_CLASSES = [
    'card-h--purple','card-h--teal','card-h--blue','card-h--amber','card-h--green','card-h--red'
  ];

  function setHeaderState(sectionId, state){ // 'base' | 'complete' | 'error'
    const header = document.querySelector('#h-' + sectionId);
    if(!header) return;
    // reset semua kelas status
    ALL_HEADER_CLASSES.forEach(c => header.classList.remove(c));
    const base = header.dataset.base || 'purple';
    if(state === 'complete') header.classList.add('card-h--green');
    else if(state === 'error') header.classList.add('card-h--red');
    else header.classList.add('card-h--' + base);
  }

  function setQuicknavState(sectionId, state){
    const link = document.querySelector('#quicknav a[href="#section-' + sectionId + '"]');
    if(!link) return;
    link.classList.remove('has-error','is-complete');
    if(state === 'complete') link.classList.add('is-complete');
    else if(state === 'error') link.classList.add('has-error');
  }

  // Heuristik kelengkapan & error per section
  function hasInvalidIn(sectionId){
    const sec = document.getElementById('section-' + sectionId);
    return !!(sec && sec.querySelector('.is-invalid, [aria-invalid="true"]'));
  }
  function isFilled(v){ return v && String(v).trim().length > 0; }

  function textFromHTML(html){
    const tmp = document.createElement('div'); tmp.innerHTML = html || '';
    // kosong khas CKEditor: <p>&nbsp;</p> atau <p><br></p>
    const t = tmp.textContent.replace(/\u00a0/g,' ').trim();
    return t;
  }

  function checkUtama(){
    const sec = document.getElementById('section-utama');
    if(!sec) return 'base';
    const tgl = sec.querySelector('input[name="tanggal_asli"]')?.value;
    const tentang = sec.querySelector('input[name="tentang"]')?.value;
    const hasErr = hasInvalidIn('utama');
    if(hasErr) return 'error';
    return (isFilled(tgl) && isFilled(tentang)) ? 'complete' : 'base';
  }

  function checkMenimbang(){
    const sec = document.getElementById('section-menimbang');
    if(!sec) return 'base';
    const hasErr = hasInvalidIn('menimbang');
    if(hasErr) return 'error';
    const items = [...sec.querySelectorAll('input[name="menimbang[]"]')].map(i=>i.value).filter(v=>isFilled(v));
    return items.length > 0 ? 'complete' : 'base';
  }

  function checkMengingat(){
    const sec = document.getElementById('section-mengingat');
    if(!sec) return 'base';
    const hasErr = hasInvalidIn('mengingat');
    if(hasErr) return 'error';
    const items = [...sec.querySelectorAll('input[name="mengingat[]"]')].map(i=>i.value).filter(v=>isFilled(v));
    return items.length > 0 ? 'complete' : 'base';
  }

  function checkMenetapkan(){
    const sec = document.getElementById('section-menetapkan');
    if(!sec) return 'base';
    const hasErr = hasInvalidIn('menetapkan');
    if(hasErr) return 'error';

    // Ambil dari CKEditor jika ada, fallback ke textarea.value
    let contents = [];
    if (window.editors && Object.keys(window.editors).length){
      for (const k of Object.keys(window.editors)){
        try { contents.push(window.editors[k].getData()); } catch(_){}
      }
    } else {
      contents = [...sec.querySelectorAll('textarea.wysiwyg')].map(t=>t.value);
    }
    const nonEmpty = contents.some(h => isFilled(textFromHTML(h)));
    return nonEmpty ? 'complete' : 'base';
  }

  function evaluate(){
    const s1 = checkUtama();      setHeaderState('utama', s1);      setQuicknavState('utama', s1);
    const s2 = checkMenimbang();  setHeaderState('menimbang', s2);  setQuicknavState('menimbang', s2);
    const s3 = checkMengingat();  setHeaderState('mengingat', s3);  setQuicknavState('mengingat', s3);
    const s4 = checkMenetapkan(); setHeaderState('menetapkan', s4); setQuicknavState('menetapkan', s4);
  }

  // Re-evaluate on input changes
  document.addEventListener('input', evaluate, true);
  document.addEventListener('change', evaluate, true);
  document.addEventListener('click', function(e){
    if (
      e.target.closest('#add-menimbang') || e.target.closest('#add-mengingat') ||
      e.target.closest('#add-menetapkan') || e.target.classList.contains('remove-row') ||
      e.target.closest('.btn-remove-menetapkan')
    ) { setTimeout(evaluate, 60); }
  });

  // Hook CKEditor changes (attach ketika editor sudah siap)
  function hookEditors(){
    if(!window.editors) return;
    Object.values(window.editors).forEach(ed=>{
      if(ed && !ed._skHooked){
        ed.model.document.on('change:data', ()=>{ evaluate(); });
        ed._skHooked = true;
      }
    });
  }
  setInterval(hookEditors, 600); // ringan; berhenti sendiri setelah hook

  document.addEventListener('DOMContentLoaded', evaluate);

</script>
@endpush
