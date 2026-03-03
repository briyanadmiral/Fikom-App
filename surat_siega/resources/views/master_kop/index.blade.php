@extends('layouts.app')
@section('title', 'Kelola Template Kop Surat')

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
        background: linear-gradient(135deg, #007bff 0, #0056b3 100%);
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 1px 10px rgba(0,123,255,0.3);
        font-size: 1.8rem
    }
    .surat-header-title {
        font-weight: bold;
        color: #0056b3;
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
            <i class="fas fa-file-alt text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">Template Kop Surat</div>
            <div class="surat-header-desc">Kelola template <b>kop surat</b> untuk berbagai unit dan fakultas.</div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Daftar Template Kop</h3>
            <div class="card-tools">
                <a href="{{ route('kop.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-1"></i>Tambah Kop
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Nama Kop</th>
                            <th>Unit</th>
                            <th>Fakultas</th>
                            <th width="100">Status</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kops as $index => $kop)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $kop->nama_kop ?? 'Kop #' . $kop->id }}</strong>
                                </td>
                                <td>{{ $kop->unit_code ?? '-' }}</td>
                                <td>{{ Str::limit($kop->nama_fakultas, 30) }}</td>
                                <td>
                                    @if($kop->is_default)
                                        <span class="badge badge-success"><i class="fas fa-star"></i> Default</span>
                                    @else
                                        <span class="badge badge-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('kop.edit', $kop->id) }}" class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$kop->is_default)
                                        <form action="{{ route('kop.setDefault', $kop->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-info" title="Jadikan Default">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('kop.destroy', $kop->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin hapus template ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Belum ada template kop.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
