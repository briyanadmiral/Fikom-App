@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@push('styles')
<style>
    /* Menggunakan kembali style header dan komponen dari halaman lain */
    .page-header {
        background: #f3f6fa; padding: 1.3rem 2.2rem; border-radius: 1.1rem;
        margin-bottom: 2.2rem; border: 1px solid #e0e6ed;
        display: flex; align-items: center; gap: 1.3rem;
    }
    .page-header .icon {
        background: linear-gradient(135deg,#6c757d 0,#343a40 100%);
        width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; box-shadow: 0 1px 10px #6c757d4d; font-size: 2rem;
    }
    .page-header-title { font-weight: bold; color: #343a40; font-size: 1.85rem; margin-bottom: 0.13rem; letter-spacing: -1px; }
    .page-header-desc { color: #636e7b; font-size: 1.03rem; }
    
    .card-settings {
        border: none; border-radius: .8rem;
        box-shadow: 0 4px 25px rgba(0,0,0, .05);
    }
    .card-settings .card-header {
        background-color: #fff; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem;
    }
    .form-control { border-radius: .5rem; height: calc(1.5em + .9rem + 2px); }
    .input-group-text { background-color: #e9ecef; border-color: #ced4da; }
    
    /* [BARU] Kartu Profil di Kanan */
    .profile-summary-card { text-align: center; }
    .profile-avatar-lg {
        width: 120px; height: 120px; border-radius: 50%;
        margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center;
        color: white; font-size: 3rem; font-weight: 600;
        box-shadow: 0 4px 15px rgba(0,0,0, .2);
    }
    .profile-name { font-size: 1.3rem; font-weight: 600; }
    .profile-role { color: #6c757d; font-weight: 500; }
</style>
@endpush

@section('content_header')
<div class="page-header mt-2 mb-3">
    <span class="icon"><i class="fas fa-user-cog text-white"></i></span>
    <span>
        <div class="page-header-title">Pengaturan Akun</div>
        <div class="page-header-desc">Perbarui informasi profil dan keamanan akun Anda.</div>
    </span>
</div>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Notifikasi Sukses --}}
    @if(session('success_profile') || session('success_password'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success_profile') ?? session('success_password') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI: FORM --}}
        <div class="col-lg-8">
            {{-- Kartu Profil --}}
            <div class="card card-settings mb-4">
                <div class="card-header"><h5 class="mb-0 font-weight-bold"><i class="fas fa-user-edit mr-2 text-primary"></i>Edit Profil</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('account.updateProfile') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div><input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" class="form-control @error('nama_lengkap') is-invalid @enderror" required></div>
                                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email (untuk login)</label>
                                <div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div><input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required></div>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                             <div class="form-group col-md-6">
                                <label for="npp">NPP (opsional)</label>
                                <div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-badge"></i></span></div><input type="text" id="npp" name="npp" value="{{ old('npp', $user->npp) }}" class="form-control @error('npp') is-invalid @enderror"></div>
                                @error('npp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="jabatan">Jabatan (opsional)</label>
                                <div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-briefcase"></i></span></div><input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" class="form-control @error('jabatan') is-invalid @enderror"></div>
                                @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Simpan Perubahan Profil</button>
                    </form>
                </div>
            </div>

            {{-- Kartu Password --}}
            <div class="card card-settings">
                <div class="card-header"><h5 class="mb-0 font-weight-bold"><i class="fas fa-key mr-2 text-warning"></i>Ubah Password</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('account.updatePassword') }}" autocomplete="off">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="new_password">Password Baru</label>
                                <input type="password" id="new_password" name="new_password" class="form-control @error('new_password', 'updatePassword') is-invalid @enderror" required>
                                @error('new_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <small class="form-text text-muted mb-3">Minimal 8 karakter dan harus berbeda dari password lama Anda.</small>
                        <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-sync-alt mr-2"></i>Perbarui Password</button>
                    </form>
                </div>
            </div>
        </div>
        
        {{-- KOLOM KANAN: PROFIL --}}
        <div class="col-lg-4">
            <div class="card card-settings profile-summary-card sticky-top" style="top:20px;">
                <div class="card-body">
                    <div class="profile-avatar-lg" style="background-color: {{ generate_color_from_string($user->nama_lengkap) }};">
                        {{ get_initials($user->nama_lengkap) }}
                    </div>
                    <h4 class="profile-name">{{ $user->nama_lengkap }}</h4>
                    <p class="profile-role">{{ optional($user->peran)->nama ?? 'Pengguna' }}</p>
                    <hr>
                    <div class="text-left text-muted small">
                        <p><i class="fas fa-envelope fa-fw mr-2"></i>{{ $user->email }}</p>
                        <p class="mb-0"><i class="fas fa-clock fa-fw mr-2"></i>Aktif {{ \Carbon\Carbon::parse($user->last_activity)->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
