{{-- resources/views/surat_keputusan/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Detail Surat Keputusan')

@section('content_header')
    <h1>Detail Surat Keputusan</h1>
@endsection

@section('content')
<div class="container-fluid">
    @php
        // Decode JSON jika kolom masih string
        $menimbang  = is_array($keputusan->menimbang) ? $keputusan->menimbang : (json_decode($keputusan->menimbang, true) ?? []);
        $mengingat  = is_array($keputusan->mengingat) ? $keputusan->mengingat : (json_decode($keputusan->mengingat, true) ?? []);
        // memutuskan disimpan sebagai HTML string yang sudah disanitasi di controller
        $memutuskanHtml = (string) ($keputusan->memutuskan ?? '');
    @endphp

    {{-- Flash Message --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('surat_keputusan.index') }}">Daftar Surat Keputusan</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>

    {{-- Tombol Preview Surat (PDF.js) --}}
    <div class="mb-3 d-flex gap-2">
        <button class="btn btn-primary" id="btn-preview-surat">
            <i class="fas fa-eye mr-1"></i> Preview Surat (PDF)
        </button>
    </div>

    {{-- Modal Preview PDF ala Google Drive --}}
    <div class="modal fade" id="modalPreviewSurat" tabindex="-1" aria-labelledby="modalPreviewSuratLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;">
            <div class="modal-content p-0 bg-dark bg-opacity-75 shadow-lg border-0" style="border-radius:16px;">

                <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-dark text-white"
                     style="border-top-left-radius:16px; border-top-right-radius:16px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-file-pdf fa-2x me-2 text-danger"></i>
                        <span class="fw-bold fs-5">{{ $keputusan->nomor ?? 'Preview Surat Keputusan' }}.pdf</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" id="btn-open-tab" title="Buka Tab Baru">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                        <button class="btn btn-success btn-sm" id="btn-download-pdf" title="Download PDF">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div style="height:80vh; background:rgba(30,30,30,0.9);">
                    <iframe id="pdf-js-viewer"
                            src=""
                            style="width:100%;
                                   height:100%;
                                   border:none;
                                   border-bottom-left-radius:16px;
                                   border-bottom-right-radius:16px;
                                   overflow:auto;"
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- Metadata --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-purple text-white">
            <i class="fas fa-info-circle mr-2"></i>Metadata
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-md-3">Nomor</dt>
                <dd class="col-md-9">{{ $keputusan->nomor }}</dd>

                <dt class="col-md-3">Tentang</dt>
                <dd class="col-md-9">{{ $keputusan->tentang ?? '-' }}</dd>

                <dt class="col-md-3">Status</dt>
                <dd class="col-md-9">
                    @php
                        switch($keputusan->status_surat) {
                            case 'draft':     $cls = 'secondary'; break;
                            case 'pending':   $cls = 'warning text-dark'; break;
                            case 'disetujui': $cls = 'success'; break;
                            default:          $cls = 'secondary'; break;
                        }
                    @endphp
                    <span class="badge bg-{{ $cls }}">{{ ucfirst($keputusan->status_surat) }}</span>
                </dd>

                <dt class="col-md-3">Pembuat</dt>
                <dd class="col-md-9">{{ $keputusan->pembuat->nama_lengkap ?? '-' }}</dd>

                <dt class="col-md-3">Penandatangan</dt>
                <dd class="col-md-9">{{ $keputusan->penandatanganUser->nama_lengkap ?? '-' }}</dd>

                <dt class="col-md-3">Tanggal SK (Asli)</dt>
                <dd class="col-md-9">{{ optional($keputusan->tanggal_asli)->format('d-m-Y') ?? '-' }}</dd>

                <dt class="col-md-3">Tanggal Surat (Final)</dt>
                <dd class="col-md-9">{{ optional($keputusan->tanggal_surat)->format('d-m-Y') ?? '-' }}</dd>

                <dt class="col-md-3">Tanggal Dibuat</dt>
                <dd class="col-md-9">{{ $keputusan->created_at ? $keputusan->created_at->format('d-m-Y H:i') : '-' }}</dd>
            </dl>
        </div>
    </div>

    {{-- Detail Surat Keputusan --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-purple text-white">
            <i class="fas fa-file-alt mr-2"></i>Detail Surat Keputusan
        </div>
        <div class="card-body">
            {{-- Menimbang --}}
            <h5 class="mb-2 mt-2">Menimbang</h5>
            @if(!empty($menimbang))
                <ol type="a" style="padding-left: 24px;">
                    @foreach($menimbang as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ol>
            @else
                <div class="text-muted"><em>Belum ada data menimbang.</em></div>
            @endif

            {{-- Mengingat --}}
            <h5 class="mb-2 mt-3">Mengingat</h5>
            @if(!empty($mengingat))
                <ol type="1" style="padding-left: 24px;">
                    @foreach($mengingat as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ol>
            @else
                <div class="text-muted"><em>Belum ada data mengingat.</em></div>
            @endif

            {{-- Memutuskan (HTML siap cetak) --}}
            <h5 class="mb-2 mt-3">Memutuskan</h5>
            @if(trim($memutuskanHtml) !== '')
                <div>{!! $memutuskanHtml !!}</div>
            @else
                <div class="text-muted"><em>Belum ada keputusan.</em></div>
            @endif

            {{-- Tembusan jika ada --}}
            @if(!empty($keputusan->tembusan))
                <hr>
                <strong>Tembusan:</strong>
                <ul style="padding-left: 24px;">
                    @foreach(explode(',', $keputusan->tembusan) as $t)
                        <li>{{ trim($t) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Riwayat Versi --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-purple text-white">
            <i class="fas fa-history mr-2"></i>Riwayat Versi
        </div>
        <div class="card-body">
            @if($versList->isEmpty())
                <div><em>Tidak ada versi riwayat.</em></div>
            @else
                <div class="accordion" id="historyAccordion">
                    @foreach($versList as $index => $v)
                        @php
                            $konten       = is_string($v->konten_json) ? json_decode($v->konten_json, true) : $v->konten_json;
                            $menimbangV   = $konten['menimbang']  ?? [];
                            $mengingatV   = $konten['mengingat']  ?? [];
                            $menetapkanV  = $konten['menetapkan'] ?? []; // struktur judul+isi
                        @endphp

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $v->versi }}">
                                <button class="accordion-button {{ $index>0 ? 'collapsed' : '' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $v->versi }}"
                                        aria-expanded="{{ $index===0 ? 'true' : 'false' }}"
                                        aria-controls="collapse{{ $v->versi }}">
                                    Versi #{{ $v->versi }} ({{ $v->dibuat_pada }}) {{ $v->is_final ? '— FINAL' : '' }}
                                </button>
                            </h2>
                            <div id="collapse{{ $v->versi }}"
                                 class="accordion-collapse collapse {{ $index===0 ? 'show' : '' }}"
                                 data-bs-parent="#historyAccordion">
                                <div class="accordion-body">
                                    <strong>Menimbang:</strong>
                                    <ul>
                                        @foreach($menimbangV as $mi)
                                            <li>{{ $mi }}</li>
                                        @endforeach
                                    </ul>
                                    <strong>Mengingat:</strong>
                                    <ul>
                                        @foreach($mengingatV as $mi)
                                            <li>{{ $mi }}</li>
                                        @endforeach
                                    </ul>
                                    <strong>Memutuskan:</strong>
                                    <ul>
                                        @foreach($menetapkanV as $mi)
                                            <li>
                                                <strong>{{ $mi['judul'] ?? 'Keputusan' }}:</strong>
                                                <div>{!! nl2br(e($mi['isi'] ?? '')) !!}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tombol Edit (tanpa Hapus) --}}
    @php
        $peranId    = auth()->user()->peran_id;
        $isEditable = ($keputusan->status_surat === 'draft' && (int)$keputusan->dibuat_oleh === (int)auth()->id())
            || ($keputusan->status_surat === 'pending'
                && in_array((int)$peranId, [2,3], true)
                && (int)$keputusan->penandatangan === (int)auth()->id());
    @endphp

    @if($isEditable)
        <div class="card mb-4 shadow-sm">
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('surat_keputusan.edit', $keputusan->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i>Edit Surat
                </a>
            </div>
        </div>
    @endif

    {{-- Tombol Kembali --}}
    <div class="text-end mb-4">
        <a href="{{ route('surat_keputusan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>
</div>
@endsection

@push('css')
<style>
    #modalPreviewSurat .modal-content {
        box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
        backdrop-filter: blur(6px);
        border-radius: 16px;
        border: none;
    }
    #btn-download-pdf { opacity: 0.9; }
    #btn-download-pdf:hover { opacity: 1; }
    .bg-purple { background: #6f42c1 !important; color: #fff !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlDownloadPdf = @json(url('surat_keputusan/' . $keputusan->id . '/download-pdf'));
        const pdfViewerUrl   = "/pdfjs/web/viewer.html?file=" + encodeURIComponent(urlDownloadPdf);

        const modalEl = document.getElementById('modalPreviewSurat');
        const modal   = new bootstrap.Modal(modalEl);

        document.getElementById('btn-preview-surat').addEventListener('click', function () {
            document.getElementById('pdf-js-viewer').setAttribute('src', pdfViewerUrl);
            modal.show();
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('pdf-js-viewer').setAttribute('src', '');
        });

        document.getElementById('btn-download-pdf').addEventListener('click', function () {
            window.open(urlDownloadPdf, '_blank');
        });

        document.getElementById('btn-open-tab').addEventListener('click', function () {
            window.open(pdfViewerUrl, '_blank');
        });

        // SweetAlert2 flash (otomatis hilang 5 detik)
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: @json(session('success')),
                icon: 'success',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: @json(session('error')),
                icon: 'error',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        @endif
    });
</script>
@endpush
