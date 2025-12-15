@extends('layouts.app')
@section('title', 'Preferensi Notifikasi')

@push('styles')
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #f39c12 0%, #e74c3c 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(243,156,18,.13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #e67e22;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255,255,255,.15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(243,156,18,.13);
    }
    .header-title {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    .header-desc {
        font-size: 1.07rem;
        color: #fef9e7;
        font-weight: 400;
        margin-left: .1rem;
    }
    .card { border-radius: 1rem; }
    @media (max-width: 575.98px) {
        .custom-header-box { padding: 1.1rem; }
        .header-icon { width: 44px; height: 44px; font-size: 1.2rem; }
        .header-title { font-size: 1.2rem; }
        .header-desc { margin-left: 0; font-size: .98rem; }
    }
</style>
@endpush

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-bell fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Preferensi Notifikasi</div>
                <div class="header-desc mt-2">
                    Atur pengaturan <b>notifikasi email</b> sesuai kebutuhan Anda.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('notification_preferences.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-envelope mr-2"></i>Notifikasi Email</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Pilih jenis notifikasi yang ingin Anda terima melalui email.
                        </p>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="approvalNeeded" 
                                       name="email_on_approval_needed" value="1"
                                       {{ $preferences->email_on_approval_needed ? 'checked' : '' }}>
                                <label class="custom-control-label" for="approvalNeeded">
                                    <strong>Saat ada surat menunggu persetujuan</strong>
                                    <br><small class="text-muted">Notifikasi saat ada ST/SK baru yang perlu Anda setujui</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="approved"
                                       name="email_on_approved" value="1"
                                       {{ $preferences->email_on_approved ? 'checked' : '' }}>
                                <label class="custom-control-label" for="approved">
                                    <strong>Saat surat disetujui</strong>
                                    <br><small class="text-muted">Notifikasi saat ST/SK yang Anda buat telah disetujui</small>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="rejected"
                                       name="email_on_rejected" value="1"
                                       {{ $preferences->email_on_rejected ? 'checked' : '' }}>
                                <label class="custom-control-label" for="rejected">
                                    <strong>Saat surat ditolak</strong>
                                    <br><small class="text-muted">Notifikasi saat ST/SK yang Anda buat ditolak</small>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="digest"
                                       name="email_digest_weekly" value="1"
                                       {{ $preferences->email_digest_weekly ? 'checked' : '' }}>
                                <label class="custom-control-label" for="digest">
                                    <strong>Ringkasan mingguan</strong>
                                    <br><small class="text-muted">Terima email mingguan berisi rangkuman aktivitas surat</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Simpan Preferensi
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            {{-- User Info --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-2"></i>Akun</h3>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $user->nama_lengkap }}</strong></p>
                    <p class="mb-1 text-muted">{{ $user->email }}</p>
                    <p class="mb-0"><span class="badge badge-primary">{{ $user->role_name }}</span></p>
                </div>
            </div>

            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info</h3>
                </div>
                <div class="card-body small">
                    <p class="mb-2">Notifikasi email akan dikirim ke alamat email terdaftar Anda.</p>
                    <p class="mb-0">Ringkasan mingguan dikirim setiap hari Senin pagi.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
