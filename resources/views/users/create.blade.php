{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@push('styles')
<style>
    /* Menggunakan kembali style header dan form dari halaman sebelumnya untuk konsistensi */
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        /* Warna hijau untuk aksi 'create' */
        background: linear-gradient(135deg,#28a745 0,#20c997 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #28a7454d; font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold; color: #1a5928; font-size: 1.85rem;
        margin-bottom: 0.13rem; letter-spacing: -1px;
    }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }
    .card {
        border: none;
        border-radius: .8rem;
        box-shadow: 0 4px 25px rgba(0,0,0, .07);
        transition: all .3s;
    }
    .card-header.bg-light {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }
    .form-control, .custom-select {
        border-radius: .5rem;
        height: calc(1.5em + .75rem + 5px);
    }
    .input-group-text {
        background-color: #e9ecef;
        border-radius: .5rem 0 0 .5rem;
    }
    .btn-success {
        box-shadow: 0 2px 6px #21883866;
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon">
        <i class="fas fa-user-plus text-white"></i>
    </span>
    <span>
        <div class="page-header-title">Tambah Pengguna Baru</div>
        <div class="page-header-desc">
            Isi formulir di bawah ini untuk mendaftarkan pengguna baru ke dalam sistem.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-10 mx-auto">
                {{-- KARTU 1: INFORMASI PRIBADI --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <i class="fas fa-id-card-alt mr-2"></i>Data Diri Pengguna
                    </div>
                    <div class="card-body">
                        {{-- Nama Lengkap & NPP (Sejajar) --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                    <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap') }}" required autofocus>
                                </div>
                                @error('nama_lengkap')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="npp">NPP</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-badge"></i></span></div>
                                    <input type="text" name="npp" id="npp" class="form-control @error('npp') is-invalid @enderror" value="{{ old('npp') }}" placeholder="Contoh: 1987xxxx">
                                </div>
                                @error('npp')<small class="text-danger">{{ $message }}</small>@else<small class="text-muted">Opsional. Harus unik bila diisi.</small>@enderror
                            </div>
                        </div>

                        {{-- Jabatan --}}
                        <div class="form-group">
                            <label for="jabatan">Jabatan</label>
                            <div class="input-group">
                               <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-briefcase"></i></span></div>
                               <input type="text" name="jabatan" id="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan') }}" placeholder="Contoh: Staf Akademik">
                           </div>
                           @error('jabatan')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                            </div>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>

                {{-- KARTU 2: AKUN & KEAMANAN --}}
                <div class="card">
                    <div class="card-header bg-light">
                        <i class="fas fa-user-shield mr-2"></i>Akun & Keamanan
                    </div>
                    <div class="card-body">
                        {{-- Peran & Status (Sejajar) --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="peran_id">Peran <span class="text-danger">*</span></label>
                                <select name="peran_id" id="peran_id" class="custom-select @error('peran_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Pilih Peran</option>
                                    @foreach($peran as $p)
                                    <option value="{{ $p->id }}" {{ old('peran_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('peran_id')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Status Akun <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="custom-select @error('status') is-invalid @enderror" required>
                                    <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        {{-- Password & Konfirmasi (Sejajar) --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                @error('password')<small class="text-danger">{{ $message }}</small>@else<small class="text-muted">Minimal 6 karakter.</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i>Simpan Pengguna
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Script untuk format NPP, sama seperti sebelumnya --}}
<script>
    function formatNppLive(v) {
        const d = (v || '').replace(/\D+/g, '');
        if (d.length === 0) return '';
        let formatted = d.replace(/(\d{3})(?=\d)/g, '$1.');
        return formatted.replace(/\.$/, '');
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'npp') {
            const val = e.target.value;
            const formattedVal = formatNppLive(val);
            if (val !== formattedVal) {
                e.target.value = formattedVal;
            }
        }
    });
</script>
@endpush
