@extends('adminlte::auth.login')

@push('css')
<style>
    body.login-page {
        background: linear-gradient(135deg, #2980ff 0%, #536dfe 100%);
        min-height: 100vh;
    }
    .fikom-logo {
        font-family: 'Arial Black', 'Arial Bold', Arial, sans-serif;
        font-size: 2.6rem;
        color: #22223b;
        letter-spacing: 2px;
        text-shadow:
            2px 2px 8px #5376ff,
            0 0 2px #16213e;
        margin-bottom: 0.3em;
        font-weight: bold;
    }
    .login-box, .card {
        border-radius: 18px !important;
        box-shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
        background: #fff !important;
    }
    .btn-primary, .btn-block {
        background: #1440d0 !important;
        border: none;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .login-logo img {
        max-height: 54px;
        margin-bottom: 6px;
    }
    .input-group-text.bg-light {
        background: #e3f0fc !important;
    }
    .login-box-msg {
        color: #222;
        font-weight: 600;
        letter-spacing: .01em;
    }
    .login-logo {
    display: none !important;
}
</style>
@endpush

@section('auth_header')
    <div class="text-center mb-2">
        <div class="fikom-logo mb-1">FIKOM</div>
        {{-- <img src="{{ asset('vendor/adminlte/dist/img/Logo_Siega.png') }}" alt="Logo" height="44" class="mb-2"> --}}
        <h2 class="font-weight-bold mb-1" style="font-size:1.25rem;">Login Sistem Arsip Surat</h2>
        <div class="text-muted small">Silakan login menggunakan akun yang telah didaftarkan.</div>
    </div>
@endsection

@section('auth_body')
    <form action="{{ route('login') }}" method="post" autocomplete="off">
        @csrf

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></div>
            </div>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Email" required autofocus>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <div class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></div>
            </div>
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Password" required>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" id="toggle-password" tabindex="-1">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="row mb-2">
            <div class="col-6">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Ingat Saya</label>
                </div>
            </div>
            <div class="col-6 text-right">
                @if (Route::has('password.request'))
                    <a class="text-primary small" href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                @endif
            </div>
        </div>

        <button type="submit" class="btn btn-block btn-primary mt-2">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </button>
    </form>
@endsection

@section('auth_footer')
    <div class="text-center text-muted small mt-3 mb-2">
        &copy; {{ date('Y') }} <b>Universitas Katolik Soegijapranata</b>. All rights reserved.
    </div>
@endsection

@push('js')
<script>
document.getElementById('toggle-password').addEventListener('click', function(){
    let pwd = document.getElementById('password');
    let icon = this.querySelector('i');
    if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>
@if(session('error'))
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '{{ session('error') }}'
    });
</script>
@endif
@endpush
