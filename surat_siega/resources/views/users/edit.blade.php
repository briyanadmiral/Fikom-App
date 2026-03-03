{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Pengguna')

@push('styles')
<style>
    /* Menggunakan kembali style header dari halaman index untuk konsistensi */
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#ffc107 0,#ff9800 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #ffc1074d; font-size: 2rem;
    }
    .page-header-title {
        font-weight: bold; color: #785300; font-size: 1.85rem;
        margin-bottom: 0.13rem; letter-spacing: -1px;
    }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }

    /* Style khusus untuk halaman edit */
    .card-profile {
        text-align: center;
        padding: 1.5rem;
    }
    .profile-avatar {
        width: 100px; height: 100px;
        border-radius: 50%;
        margin: 0 auto 1rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0,0,0, .2);
    }
    .profile-name { font-size: 1.2rem; font-weight: 600; }
    .profile-email { color: #6c757d; font-size: .9rem; }
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
    .btn-primary {
        box-shadow: 0 2px 6px #0069d966;
    }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon">
        <i class="fas fa-user-edit text-white"></i>
    </span>
    <span>
        <div class="page-header-title">Edit Pengguna</div>
        <div class="page-header-desc">
            Ubah detail data untuk pengguna <b>{{ $user->nama_lengkap }}</b>.
        </div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- KOLOM KIRI: Informasi Utama & Profil --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <i class="fas fa-id-card-alt mr-2"></i>Informasi Pribadi
                    </div>
                    <div class="card-body">
                        {{-- Nama Lengkap --}}
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                                <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required autofocus>
                            </div>
                            @error('nama_lengkap')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- NPP & Jabatan (Sejajar) --}}
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="npp">NPP</label>
                                 <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-badge"></i></span></div>
                                    <input type="text" name="npp" id="npp" class="form-control @error('npp') is-invalid @enderror" value="{{ old('npp', $user->npp ?? '') }}" placeholder="Contoh: 1987xxxx">
                                </div>
                                @error('npp')<small class="text-danger">{{ $message }}</small>@else<small class="text-muted">Opsional. Harus unik bila diisi.</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="jabatan">Jabatan</label>
                                 <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-briefcase"></i></span></div>
                                    <input type="text" name="jabatan" id="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $user->jabatan) }}">
                                </div>
                                @error('jabatan')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                             <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="email">
                            </div>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Akses, Peran, & Password --}}
            <div class="col-lg-4">
                {{-- Kartu Profil --}}
                <div class="card card-profile mb-4">
                    @if($user->foto_path && Storage::disk('public')->exists($user->foto_path))
                        <img src="{{ asset('storage/' . $user->foto_path) }}" alt="Foto Profile" 
                             style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; box-shadow: 0 4px 15px rgba(0,0,0,.2);">
                    @else
                        <div class="profile-avatar" style="background-color: {{ generate_color_from_string($user->nama_lengkap) }};">
                            {{ get_initials($user->nama_lengkap) }}
                        </div>
                    @endif
                    <div class="profile-name">{{ $user->nama_lengkap }}</div>
                    <div class="profile-email">{{ $user->email }}</div>
                </div>

                {{-- Kartu Akses & Peran --}}
                <div class="card mb-4">
                    <div class="card-header bg-light"><i class="fas fa-user-cog mr-2"></i>Akses & Peran</div>
                    <div class="card-body">
                         {{-- Peran --}}
                        <div class="form-group">
                            <label for="peran_id">Peran <span class="text-danger">*</span></label>
                            <select name="peran_id" id="peran_id" class="custom-select @error('peran_id') is-invalid @enderror" required>
                                <option value="">Pilih Peran</option>
                                @foreach($peran as $p)
                                <option value="{{ $p->id }}" {{ old('peran_id', $user->peran_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }}
                                </option>
                                @endforeach
                            </select>
                            @error('peran_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        {{-- Status --}}
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="custom-select @error('status') is-invalid @enderror" required>
                                <option value="aktif" {{ old('status', $user->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="tidak_aktif" {{ old('status', $user->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>

                {{-- Kartu Ubah Password --}}
                <div class="card">
                     <div class="card-header bg-light"><i class="fas fa-key mr-2"></i>Ubah Password</div>
                     <div class="card-body">
                        <div class="form-group">
                            <label for="password">Password Baru</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Isi untuk mengubah" autocomplete="new-password">
                            @error('password')<small class="text-danger">{{ $message }}</small>@else<small class="text-muted">Kosongkan jika tidak ingin mengubah.</small>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru" autocomplete="new-password">
                        </div>
                     </div>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="row mt-3">
            <div class="col">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Script untuk format NPP, tidak diubah --}}
<script>
    function formatNppLive(v) {
        const d = (v || '').replace(/\D+/g, '');
        if (d.length === 0) return '';
        // Coba format yang lebih umum
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
