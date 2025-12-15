@extends('layouts.app')

@section('title', 'Template Surat Tugas')

@push('styles')
<style>
    body { background: #f7faff }
    .surat-header {
        background: #f3f6fa;
        padding: 1.3rem 2.2rem 1.3rem 1.8rem;
        border-radius: 1.1rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e0e6ed;
        display: flex;
        align-items: center;
        gap: 1.3rem
    }
    .surat-header .icon {
        background: linear-gradient(135deg, #fd7e14 0, #e8590c 100%);
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px rgba(253,126,20,0.3);
        font-size: 1.8rem
    }
    .surat-header-title {
        font-weight: bold;
        color: #d63600;
        font-size: 1.6rem;
        margin-bottom: .13rem;
        letter-spacing: -0.5px
    }
    .surat-header-desc {
        color: #636e7b;
        font-size: 1rem
    }
    .card { border-radius: 1rem; }
    @media (max-width:767.98px) {
        .surat-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.2rem 1rem;
            gap: .7rem
        }
        .surat-header-title { font-size: 1.18rem }
    }
</style>
@endpush

@section('content_header')
    <div class="surat-header mt-2">
        <span class="icon">
            <i class="fas fa-copy text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">Template Surat Tugas</div>
            <div class="surat-header-desc">Kelola <b>template</b> untuk mempercepat pembuatan Surat Tugas berulang.</div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Header & Actions --}}
            <div class="card mb-4">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-0 text-muted">
                                Kelola template untuk mempercepat pembuatan Surat Tugas.
                                Buat template standar untuk kegiatan yang berulang.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <a href="{{ route('surat_templates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Buat Template Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Filter & Pencarian</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('surat_templates.index') }}">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="input-group">
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           class="form-control" placeholder="Cari nama template...">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <select name="jenis_tugas_id" class="form-control">
                                    <option value="">-- Semua Jenis Tugas --</option>
                                    @foreach($jenisTugasList as $jenis)
                                        <option value="{{ $jenis->id }}" {{ request('jenis_tugas_id') == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Templates Grid --}}
            @if($templates->count() > 0)
                <div class="row">
                    @foreach($templates as $template)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title text-truncate w-75" title="{{ $template->nama }}">
                                        {{ $template->nama }}
                                    </h3>
                                    <div class="card-tools">
                                        @if($template->jenisTugas)
                                            <span class="badge badge-info">{{ $template->jenisTugas->nama }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($template->deskripsi)
                                        <p class="card-text text-muted small mb-3">
                                            {{ Str::limit($template->deskripsi, 100) }}
                                        </p>
                                    @endif

                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-user mr-1"></i> {{ $template->creator->nama_lengkap ?? 'N/A' }}<br>
                                            <i class="fas fa-clock mr-1"></i> {{ $template->created_at->translatedFormat('d M Y') }}
                                        </small>
                                    </div>

                                    {{-- Placeholder badges --}}
                                    @php $usedPlaceholders = $template->getUsedPlaceholders(); @endphp
                                    @if(count($usedPlaceholders) > 0)
                                        <div class="mb-2">
                                            @foreach(array_slice($usedPlaceholders, 0, 3) as $ph)
                                                <span class="badge badge-light border mb-1">{{ $ph }}</span>
                                            @endforeach
                                            @if(count($usedPlaceholders) > 3)
                                                <span class="badge badge-light border mb-1">+{{ count($usedPlaceholders) - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-white border-top-0 d-flex justify-content-between pt-0">
                                    <a href="{{ route('surat_templates.edit', $template) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    <form action="{{ route('surat_templates.destroy', $template) }}" method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $templates->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <h5><i class="icon fas fa-info"></i> Belum ada template!</h5>
                    Belum ada template surat tugas yang dibuat. Silakan buat template baru.
                    <div class="mt-3">
                        <a href="{{ route('surat_templates.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat Sekarang
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
