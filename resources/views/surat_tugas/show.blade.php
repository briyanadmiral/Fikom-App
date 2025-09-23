{{-- === GUARD VISIBILITAS TTD/CAP (SHOW) === --}}
@php
  if (!isset($showSigns)) {
    $showSigns = isset($tugas)
      ? (($tugas->status_surat ?? null) === 'disetujui' && !empty($tugas->signed_at ?? null))
      : false;
  }
@endphp


@extends('layouts.app')

@section('title', 'Detail Surat Tugas: ' . $tugas->nomor)

@section('content_header')
<div class="row mb-2">
  <div class="col-sm-6"><h1>Detail Surat Tugas</h1></div>
  <div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
      <li class="breadcrumb-item"><a href="{{ route('surat_tugas.index') }}">Surat Tugas</a></li>
      <li class="breadcrumb-item active">Detail</li>
    </ol>
  </div>
</div>
@endsection

@section('content')
<div class="row">
  {{-- ===== Kolom Kiri: Pratinjau Surat ===== --}}
  <div class="col-lg-8" id="preview-container">
    {{--
      Logika Tampilan:
      - Jika surat sudah disetujui, tampilkan preview final.
      - Jika belum dan user adalah approver, tampilkan preview interaktif untuk approval.
      - Jika belum dan user biasa, tampilkan preview standar.
    --}}
    @if ($tugas->status_surat === 'disetujui')
      {{-- Tampilan final untuk semua orang setelah surat disetujui --}}
      @include('surat_tugas.preview', [
        'tugas' => $tugas,
        'kop' => $kop,
        'ttdW' => $tugas->ttd_w_mm,
        'capW' => $tugas->cap_w_mm,
        'capOpacity' => $tugas->cap_opacity,
        'ttdImageB64' => $preview['ttd_image_b64'],
        'capImageB64' => $preview['cap_image_b64'],
      ])
    @elseif (Gate::allows('approve', $tugas))
      {{-- Tampilan interaktif khusus untuk approver --}}
      @include('surat_tugas.partials.approve-preview', [
        'tugas' => $tugas,
        'kop' => $kop,
        'preview' => $preview,
      ])
    @else
      {{-- Tampilan standar untuk user lain jika surat belum disetujui --}}
      @include('surat_tugas.preview', compact('tugas', 'kop'))
    @endif
  </div>

  {{-- ===== Kolom Kanan: Informasi & Aksi ===== --}}
  <div class="col-lg-4">
    <div class="card sticky-top" style="top: 20px;">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-info-circle mr-2"></i>Informasi & Aksi
        </h3>
      </div>
      <div class="card-body">

        {{-- Panel Approval hanya muncul untuk user yang berhak & surat yang 'pending' --}}
        @if (Gate::allows('approve', $tugas))
            <div class="mb-4 p-3 bg-light rounded">
                <h5 class="mb-3">Panel Persetujuan</h5>
                <p class="text-muted small">Sesuaikan ukuran dan opasitas TTD/Cap jika perlu. Pratinjau di sebelah kiri akan diperbarui secara otomatis.</p>
                @include('surat_tugas.partials.approve-controls', [
                    'tugas'      => $tugas,
                    'ttdW'       => $preview['ttd_w_mm'],
                    'capW'       => $preview['cap_w_mm'],
                    'capOpacity' => $preview['cap_opacity'],
                ])
            </div>
            <hr>
        @endif

        {{-- Tombol Aksi Umum --}}
        <div class="d-grid gap-2">
          <a href="{{ route('surat_tugas.downloadPdf', $tugas->id) }}" class="btn btn-danger btn-block" target="_blank">
            <i class="fas fa-file-pdf mr-2"></i>Download PDF
          </a>
          <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
          </a>
        </div>

        <hr>

        {{-- Detail Informasi Surat --}}
        <dl>
          <dt>Status</dt>
          <dd><span class="badge badge-pill badge-{{ $tugas->status_surat == 'disetujui' ? 'success' : ($tugas->status_surat == 'pending' ? 'warning' : 'secondary') }}">{{ Str::ucfirst($tugas->status_surat) }}</span></dd>
          <dt>Dibuat oleh</dt>
          <dd>{{ optional($tugas->pembuat)->nama_lengkap ?? '-' }}</dd>
          <dt>Tgl Diajukan</dt>
          <dd>{{ optional($tugas->submitted_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
           <dt>Disetujui pada</dt>
          <dd>{{ optional($tugas->signed_at)->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
        </dl>
      </div>
    </div>
  </div>
</div>

{{-- Script untuk Live Preview (jika ada panel approval) --}}
@if (Gate::allows('approve', $tugas))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ttdWInput = document.querySelector('input[name="ttd_w_mm"]');
    const capWInput = document.querySelector('input[name="cap_w_mm"]');
    const capOpacityInput = document.querySelector('input[name="cap_opacity"]');
    const previewContainer = document.getElementById('preview-container');
    const baseUrl = "{{ route('surat_tugas.show', $tugas->id) }}";

    // Fungsi debounce untuk mencegah terlalu banyak request saat input diubah
    function debounce(func, timeout = 300){
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }

    const updatePreview = debounce(() => {
        const params = new URLSearchParams({
            ttd_w_mm: ttdWInput.value,
            cap_w_mm: capWInput.value,
            cap_opacity: capOpacityInput.value,
            partial: 'true' // Flag untuk controller
        });

        fetch(`${baseUrl}?${params.toString()}`)
            .then(response => response.text())
            .then(html => {
                previewContainer.innerHTML = html;
            })
            .catch(error => console.error('Error updating preview:', error));
    });

    [ttdWInput, capWInput, capOpacityInput].forEach(input => {
        if(input) {
            input.addEventListener('input', updatePreview);
        }
    });
});
</script>
@endif
@endsection