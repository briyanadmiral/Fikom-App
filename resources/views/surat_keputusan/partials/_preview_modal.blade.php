{{-- resources/views/surat_keputusan/partials/_preview_modal.blade.php --}}
<div class="modal fade" id="skPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header align-items-center">
        <h5 class="modal-title mb-0">
          <i class="fas fa-eye mr-2 text-primary"></i>Preview Surat Keputusan
        </h5>
        <div class="ml-auto">
          <button type="button" id="btn-print-preview" class="btn btn-outline-secondary btn-sm mr-2">
            <i class="fas fa-print mr-1"></i> Cetak
          </button>
          <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Tutup
          </button>
        </div>
      </div>

      <div class="modal-body">
        {{-- ====== AREA YANG DICETAK ====== --}}
        <div id="sk-preview-doc" class="sk-preview sheet">

          {{-- === KOP SURAT (pakai partial shared; fallback jika belum ada) === --}}
          @php use Illuminate\Support\Facades\View; @endphp

          @if(View::exists('shared._kop_surat'))
            {{-- pakai partial shared, context web --}}
            @include('shared._kop_surat', ['kop' => $kop ?? null, 'context' => 'web'])
          @else
            {{-- Fallback kop sederhana (ambil dari MasterKopSurat) --}}
            @php
              $kopModel = \App\Models\MasterKopSurat::first();
              $logoRight = $kopModel?->logo_kanan_path ? asset('storage/'.$kopModel->logo_kanan_path) : null;
            @endphp
            <div class="kop-wrap">
              <table style="width:100%; border-collapse:collapse;">
                <tr>
                  <td style="width: calc(100% - 130px);">
                    <div style="line-height:1.25; text-align:right;">
                      <div style="font-weight:800; font-size:21px; color:#6A2C8E;">
                        {{ strtoupper($kopModel->judul_atas ?? 'JUDUL INSTANSI') }}
                      </div>
                      <div style="font-weight:800; font-size:15px; margin-top:-2px; color:#6A2C8E;">
                        {{ strtoupper($kopModel->subjudul ?? 'SUBJUDUL') }}
                      </div>
                      <div style="font-size:11px; margin-top:6px; color:#111;">
                        {{ $kopModel->alamat ?? '' }}<br>
                        Telp. {{ $kopModel->telepon ?? '' }}@if(!empty($kopModel?->fax)) , Fax. {{ $kopModel->fax }} @endif<br>
                        email: {{ $kopModel->email ?? '' }} @if(!empty($kopModel?->website)) | {{ $kopModel->website }} @endif
                      </div>
                    </div>
                  </td>
                  <td style="width:130px; text-align:right; border-left:2px solid #000; padding-left:12px;">
                    @if($logoRight)<img src="{{ $logoRight }}" alt="Logo" style="width:92px; height:auto;">@endif
                  </td>
                </tr>
              </table>
            </div>
          @endif

          {{-- === KONTEN SURAT (dirender oleh sk-preview.js) === --}}
          <div id="sk-preview-root" class="sk-preview-content"></div>
        </div>
        {{-- ====== /AREA YANG DICETAK ====== --}}
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  /* Kanvas A4 untuk preview */
  .sheet{
    width:210mm; min-height:297mm; margin:8mm auto; background:#fff; position:relative;
    box-shadow:0 10px 30px rgba(0,0,0,.08); padding:40mm 15mm 25mm 15mm;
    font-family:"Times New Roman", Times, serif; color:#111; font-size:14.5px;
  }
  .sk-preview .skp-section-title{ font-weight:700; text-transform:uppercase; margin: 1rem 0 .4rem; }
  .sk-preview ol{ margin:0; padding-left:1.25rem; }
  .sk-preview .alpha{ list-style: lower-alpha; }

  /* Kop garis bawah bila perlu (partial shared bisa punya style sendiri) */
  .kop-wrap{ padding-bottom:8px; border-bottom:2px solid #000; margin-bottom:16px; }

  /* Cetak: hilangkan chrome modal, pertahankan warna */
  @media print {
    html, body { background:#fff !important; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .modal, .modal-dialog, .modal-content, .modal-header, .modal-body { position: static !important; box-shadow:none !important; }
    .sheet { margin:0 !important; box-shadow:none !important; }
  }
</style>
@endpush

@push('scripts')
<script>
  // Cetak hanya area #sk-preview-doc (kop + isi)
  (function(){
    const btn = document.getElementById('btn-print-preview');
    if(!btn) return;
    btn.addEventListener('click', function(){
      const node = document.getElementById('sk-preview-doc');
      if(!node) return;

      const w = window.open('', '_blank');
      if(!w) return;

      // Copy semua <link rel="stylesheet"> & <style> agar layout konsisten
      const headLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"], style'))
        .map(n => n.outerHTML).join('\n');

      w.document.write(`<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Preview Surat Keputusan</title>
${headLinks}
<style>
  @page { size: A4; margin: 10mm 12mm; }
  html, body { background:#fff; }
</style>
</head>
<body>
${node.outerHTML}
</body>
</html>`);
      w.document.close();
      w.focus();
      // beri sedikit waktu agar font/asset termuat
      setTimeout(()=>{ w.print(); w.close(); }, 250);
    });
  })();
</script>
@endpush
