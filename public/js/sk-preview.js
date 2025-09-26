/* global $, Swal */
(function(){
  // Helper tanggal Indonesia
  function formatDateID(yyyy_mm_dd){
    if(!yyyy_mm_dd) return '';
    const [y,m,d] = yyyy_mm_dd.split('-').map(Number);
    if(!y || !m || !d) return yyyy_mm_dd;
    const bln = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return `${d} ${bln[m-1]} ${y}`;
  }

  // Ambil data dari form
  function collectData(){
    const form = document.getElementById('skForm');
    const get = sel => form.querySelector(sel);
    const getAll = sel => Array.from(form.querySelectorAll(sel));

    // Data utama
    const nomor   = get('input[name="nomor"]')?.value || '';
    const tgl     = get('input[name="tanggal_asli"]')?.value || '';
    const tentang = get('input[name="tentang"]')?.value || '';
    const signerEl= get('select[name="penandatangan"]');
    const signer  = signerEl ? (signerEl.selectedOptions[0]?.text || '') : '';
    const tembus  = (get('#tembusan-hidden')?.value || '').split(',')
                    .map(s=>s.trim()).filter(Boolean);

    // Menimbang
    const menimbang = getAll('input[name="menimbang[]"]')
      .map(i=>i.value.trim()).filter(Boolean);

    // Mengingat
    const mengingat = getAll('input[name="mengingat[]"]')
      .map(i=>i.value.trim()).filter(Boolean);

    // Menetapkan (judul + isi/CKEditor)
    const items = getAll('#menetapkan-list .menetapkan-item').map(item=>{
      const judul = item.querySelector('input[name$="[judul]"]')?.value || '';
      const ta    = item.querySelector('textarea.wysiwyg');
      let isiHtml = '';
      if(ta && ta.dataset.editorId && window.editors && window.editors[ta.dataset.editorId]){
        try{ isiHtml = window.editors[ta.dataset.editorId].getData(); }catch(e){}
      }else{
        isiHtml = ta?.value || '';
      }
      return { judul, isiHtml };
    });

    return { nomor, tgl, tentang, signer, tembus, menimbang, mengingat, items };
  }

  // Render HTML preview
  function renderHTML(d){
    const nomorLine = d.nomor ? `Nomor: ${d.nomor}` : 'Nomor: (ditentukan sistem)';
    const tentangUpper = (d.tentang || '').toUpperCase();

    const menimbangHTML = d.menimbang.length
      ? `<ol class="alpha">${d.menimbang.map(x=>`<li>${x}</li>`).join('')}</ol>`
      : '<div class="text-muted">- belum ada butir -</div>';

    const mengingatHTML = d.mengingat.length
      ? `<ol>${d.mengingat.map(x=>`<li>${x}</li>`).join('')}</ol>`
      : '<div class="text-muted">- belum ada butir -</div>';

    const menetapkanHTML = d.items.length
      ? d.items.map(it => `
          <div class="skp-diktum">
            <div class="judul">${it.judul}</div>
            <div class="isi">${it.isiHtml || '<span class="text-muted">- belum diisi -</span>'}</div>
          </div>
        `).join('')
      : '<div class="text-muted">- belum ada diktum -</div>';

    const tembusanHTML = d.tembus.length
      ? `<div class="skp-tembusan"><strong>Tembusan:</strong><ul>${d.tembus.map(t=>`<li>${t}</li>`).join('')}</ul></div>`
      : '';

    const signerBlock = d.signer
      ? `<div class="skp-ttd"><div class="box">
            <div class="role">Penandatangan,<br>${d.signer}</div>
            <div class="nama" style="font-weight:700; text-transform:uppercase;">( _________ )</div>
          </div></div>`
      : `<div class="skp-ttd text-muted"><em>Penandatangan belum dipilih.</em></div>`;

    return `
      <div class="skp-header">
        <div class="skp-title">
          <span class="line1">SURAT KEPUTUSAN</span>
          <span class="line2">TENTANG</span>
          <span class="line3">${tentangUpper}</span>
        </div>
        <div class="mt-2">${nomorLine}</div>
      </div>

      <div class="skp-kolom">
        <div class="label">Tanggal</div><div class="value">: ${formatDateID(d.tgl)}</div>
        <div class="label">Tentang</div><div class="value">: ${d.tentang || '-'}</div>
      </div>

      <div class="skp-section">
        <div class="skp-section-title">Menimbang</div>
        <div class="skp-list">${menimbangHTML}</div>
      </div>

      <div class="skp-section">
        <div class="skp-section-title">Mengingat</div>
        <div class="skp-list">${mengingatHTML}</div>
      </div>

      <div class="skp-section">
        <div class="skp-section-title">Memutuskan</div>
        <div class="skp-list">
          <div class="judul" style="text-transform:none; font-weight:600; margin-bottom:.25rem;">Menetapkan:</div>
          ${menetapkanHTML}
        </div>
      </div>

      ${signerBlock}
      ${tembusanHTML}
    `;
  }

  // Cetak: buka jendela baru dan print
  function printHTML(html){
    const win = window.open('', '_blank', 'width=900,height=700');
    const css = `
      <style>
        body{ font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif; color:#111; }
        ${document.querySelector('style[data-preview-inline]')?.innerHTML || ''}
      </style>`;
    win.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">${css}</head><body>${html}</body></html>`);
    win.document.close();
    win.focus();
    win.print();
    setTimeout(()=>win.close(), 300);
  }

  // Public: open preview
  function openPreview(){
    const data = collectData();
    const html = renderHTML(data);
    const root = document.getElementById('sk-preview-root');
    if(root){ root.innerHTML = html; }
    $('#skPreviewModal').modal('show');
  }

  // Wire buttons
  document.addEventListener('DOMContentLoaded', function(){
    const btn1 = document.getElementById('btn-preview');
    const btn2 = document.getElementById('mb-preview');
    btn1 && btn1.addEventListener('click', openPreview);
    btn2 && btn2.addEventListener('click', openPreview);

    const btnPrint = document.getElementById('btn-print-preview');
    btnPrint && btnPrint.addEventListener('click', function(){
      const html = document.getElementById('sk-preview-root')?.innerHTML || '';
      printHTML(html);
    });
  });

  // Expose if needed
  window.SKPreview = { open: openPreview };
})();
