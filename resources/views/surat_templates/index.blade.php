@extends('layouts.app')

@section('title', 'Template Surat Tugas')

@push('styles')
<style>
    body { background: #f4f7fb; }
    
    /* === MODERN HEADER === */
    .page-header {
        background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
        color: #fff;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 40px rgba(253,126,20,0.2);
        position: relative;
        overflow: hidden;
    }
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .page-header .icon-box {
        width: 70px;
        height: 70px;
        background: rgba(255,255,255,0.2);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .page-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .page-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    /* === STATS CARDS === */
    .stats-row {
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .stat-icon.orange { background: rgba(253,126,20,0.1); color: #fd7e14; }
    .stat-icon.blue { background: rgba(78,115,223,0.1); color: #4e73df; }
    .stat-icon.green { background: rgba(28,200,138,0.1); color: #1cc88a; }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: #2d3436; }
    .stat-label { color: #b0b7c3; font-size: 0.85rem; }

    /* === FILTER SECTION === */
    .filter-section {
        background: #fff;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.04);
        margin-bottom: 1.5rem;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .search-input {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        padding: 0.8rem 1rem 0.8rem 2.8rem;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .search-input:focus {
        border-color: #fd7e14;
        box-shadow: 0 0 0 3px rgba(253,126,20,0.1);
    }
    .filter-select {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
    }
    .btn-filter {
        border-radius: 12px;
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        background: linear-gradient(135deg, #2d3436 0%, #1a1a1a 100%);
        border: none;
        color: #fff;
    }
    .btn-filter:hover {
        background: linear-gradient(135deg, #1a1a1a 0%, #000 100%);
        color: #fff;
    }
    .btn-create {
        border-radius: 12px;
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
        border: none;
        color: #fff;
        box-shadow: 0 4px 15px rgba(253,126,20,0.3);
    }
    .btn-create:hover {
        background: linear-gradient(135deg, #e8590c 0%, #d54400 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(253,126,20,0.4);
    }

    /* === TEMPLATE CARDS === */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1.5rem;
    }
    .template-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.03);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        flex-direction: column;
    }
    .template-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        border-color: rgba(253,126,20,0.2);
    }
    .template-card-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid #f8f9fc;
    }
    .template-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        background: linear-gradient(135deg, #fff4e6 0%, #ffe8cc 100%);
        color: #e8590c;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
    }
    .template-badge i { font-size: 0.6rem; }
    .sub-badge {
        background: linear-gradient(135deg, #e6f3ff 0%, #cce5ff 100%);
        color: #0056b3;
        margin-left: 0.5rem;
    }
    .template-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .template-card-body {
        padding: 1rem 1.5rem 1.5rem;
        flex-grow: 1;
    }
    .template-desc {
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .template-placeholders {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 1rem;
    }
    .placeholder-tag {
        background: #f8f9fc;
        color: #6c757d;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    .placeholder-more {
        color: #b0b7c3;
        font-size: 0.75rem;
        padding: 0.3rem 0;
    }
    .template-meta {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid #f8f9fc;
        font-size: 0.8rem;
        color: #adb5bd;
    }
    .template-meta i {
        margin-right: 0.35rem;
        color: #ced4da;
    }
    .template-card-footer {
        padding: 1rem 1.5rem;
        background: #fafbfc;
        display: flex;
        gap: 0.5rem;
    }
    .btn-action {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.65rem 0.75rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-action i { font-size: 0.8rem; }
    .btn-preview {
        background: #e8f4fd;
        color: #0d6efd;
    }
    .btn-preview:hover {
        background: #cfe2ff;
        color: #0a58ca;
        text-decoration: none;
    }
    .btn-edit {
        background: #fff8e6;
        color: #f59f00;
    }
    .btn-edit:hover {
        background: #ffec99;
        color: #e67700;
        text-decoration: none;
    }
    .btn-duplicate {
        background: #e6f9f1;
        color: #1cc88a;
    }
    .btn-duplicate:hover {
        background: #b2f0d8;
        color: #17a673;
        text-decoration: none;
    }
    .btn-delete {
        background: #fce8e8;
        color: #dc3545;
    }
    .btn-delete:hover {
        background: #f5c6cb;
        color: #bd2130;
    }

    /* === EMPTY STATE === */
    .empty-state {
        background: #fff;
        border-radius: 20px;
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .empty-state-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #fff4e6 0%, #ffe8cc 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        color: #fd7e14;
    }
    .empty-state h4 {
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 0.5rem;
    }
    .empty-state p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }

    /* === RESPONSIVE === */
    @media (max-width: 767.98px) {
        .page-header { padding: 1.5rem; }
        .page-header h1 { font-size: 1.35rem; }
        .template-grid { grid-template-columns: 1fr; }
        .template-card-footer { flex-wrap: wrap; }
        .btn-action { flex: 1 1 calc(50% - 0.25rem); }
    }
</style>
@endpush

@section('content_header')
    <div class="page-header mt-2">
        <div class="d-flex align-items-center">
            <div class="icon-box mr-3">
                <i class="fas fa-file-signature text-white"></i>
            </div>
            <div>
                <h1>Template Surat Tugas</h1>
                <p>Kelola template untuk mempercepat pembuatan Surat Tugas berulang</p>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid px-2">
    
    {{-- Stats Row --}}
    <div class="row stats-row">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $templates->total() }}</div>
                    <div class="stat-label">Total Template</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-folder"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $jenisTugasList->count() }}</div>
                    <div class="stat-label">Jenis Tugas</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $templates->count() }}</div>
                    <div class="stat-label">Template Aktif</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Actions --}}
    <div class="filter-section">
        <div class="row align-items-center">
            <div class="col-lg-8 order-2 order-lg-1">
                <form method="GET" action="{{ route('surat_templates.index') }}">
                    <div class="row">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <div class="position-relative">
                                <i class="fas fa-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%); z-index: 3;"></i>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       class="form-control search-input" placeholder="Cari nama template...">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <select name="jenis_tugas_id" class="form-control filter-select">
                                <option value="">Semua Jenis Tugas</option>
                                @foreach($jenisTugasList as $jenis)
                                    <option value="{{ $jenis->id }}" {{ request('jenis_tugas_id') == $jenis->id ? 'selected' : '' }}>
                                        {{ $jenis->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-filter btn-block">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4 order-1 order-lg-2 text-lg-right mb-3 mb-lg-0">
                <a href="{{ route('surat_templates.create') }}" class="btn btn-create">
                    <i class="fas fa-plus mr-1"></i> Buat Template Baru
                </a>
            </div>
        </div>
    </div>

    {{-- Templates Grid --}}
    @if($templates->count() > 0)
        <div class="template-grid">
            @foreach($templates as $template)
                <div class="template-card">
                    <div class="template-card-header">
                        <div class="d-flex flex-wrap">
                            @if($template->jenisTugas)
                                <span class="template-badge">
                                    <i class="fas fa-folder"></i> {{ $template->jenisTugas->nama }}
                                </span>
                            @endif
                            @if($template->subTugas)
                                <span class="template-badge sub-badge">
                                    <i class="fas fa-tag"></i> {{ $template->subTugas->nama }}
                                </span>
                            @endif
                        </div>
                        <h3 class="template-title" title="{{ $template->nama }}">
                            {{ $template->nama }}
                        </h3>
                    </div>
                    
                    <div class="template-card-body">
                        <p class="template-desc">
                            {{ $template->deskripsi ?? 'Tidak ada deskripsi untuk template ini.' }}
                        </p>
                        
                        {{-- Placeholder Tags --}}
                        @php $usedPlaceholders = $template->getUsedPlaceholders(); @endphp
                        @if(count($usedPlaceholders) > 0)
                            <div class="template-placeholders">
                                @foreach(array_slice($usedPlaceholders, 0, 4) as $ph)
                                    <span class="placeholder-tag">{{ $ph }}</span>
                                @endforeach
                                @if(count($usedPlaceholders) > 4)
                                    <span class="placeholder-more">+{{ count($usedPlaceholders) - 4 }} lainnya</span>
                                @endif
                            </div>
                        @endif

                        <div class="template-meta">
                            <span data-toggle="tooltip" title="Dibuat oleh">
                                <i class="fas fa-user"></i> {{ Str::limit($template->creator->nama_lengkap ?? 'System', 15) }}
                            </span>
                            <span data-toggle="tooltip" title="Tanggal dibuat">
                                <i class="far fa-calendar"></i> {{ $template->created_at->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="template-card-footer">
                        <button type="button" class="btn-action btn-preview" onclick="previewTemplate({{ $template->id }})" data-toggle="tooltip" title="Lihat Preview">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="{{ route('surat_templates.edit', $template) }}" class="btn-action btn-edit" data-toggle="tooltip" title="Edit Template">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('surat_templates.duplicate', $template) }}" method="POST" class="d-inline" style="flex: 1;">
                            @csrf
                            <button type="submit" class="btn-action btn-duplicate w-100" data-toggle="tooltip" title="Duplikasi Template">
                                <i class="fas fa-copy"></i>
                            </button>
                        </form>
                        <form action="{{ route('surat_templates.destroy', $template) }}" method="POST" class="d-inline delete-form" style="flex: 1;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete w-100" data-toggle="tooltip" title="Hapus Template">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4 mb-5">
            {{ $templates->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h4>Belum ada template</h4>
            <p>Mulai dengan membuat template surat tugas pertama Anda untuk mempercepat alur kerja.</p>
            <a href="{{ route('surat_templates.create') }}" class="btn btn-create btn-lg">
                <i class="fas fa-plus mr-2"></i> Buat Template Pertama
            </a>
        </div>
    @endif
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); color: #fff; border: none;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-eye mr-2"></i> Preview Template
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div id="previewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-3">Memuat preview...</p>
                </div>
                <div id="previewContent" class="d-none">
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-file-alt mr-1"></i> Isi Template:
                        </h6>
                        <div id="previewDetailTugas" class="border rounded p-3 bg-light" style="max-height: 350px; overflow-y: auto;"></div>
                    </div>
                    <div id="previewTembusanWrap" class="d-none">
                        <h6 class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-paper-plane mr-1"></i> Tembusan:
                        </h6>
                        <div id="previewTembusan" class="border rounded p-3 bg-light"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px;">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        Swal.fire({
            title: 'Hapus Template?',
            text: 'Template yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});

function previewTemplate(id) {
    const modal = $('#previewModal');
    const loading = $('#previewLoading');
    const content = $('#previewContent');
    
    loading.removeClass('d-none');
    content.addClass('d-none');
    modal.modal('show');
    
    $.ajax({
        url: `/surat_templates/${id}/preview`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#previewDetailTugas').html(response.preview.detail_tugas);
                
                if (response.preview.tembusan && response.preview.tembusan.trim()) {
                    $('#previewTembusan').html(response.preview.tembusan.replace(/\n/g, '<br>'));
                    $('#previewTembusanWrap').removeClass('d-none');
                } else {
                    $('#previewTembusanWrap').addClass('d-none');
                }
                
                loading.addClass('d-none');
                content.removeClass('d-none');
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat',
                text: 'Tidak dapat memuat preview template.',
                confirmButtonColor: '#fd7e14'
            });
            modal.modal('hide');
        }
    });
}
</script>
@endpush
