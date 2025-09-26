<nav class="main-header navbar navbar-expand navbar-white navbar-light shadow-sm">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link"><i class="fas fa-home"></i> Home</a>
        </li>
    </ul>

    <!-- Center: Optional flash message -->
    @if(session('flash'))
        <span class="ml-3 text-success font-weight-bold" style="letter-spacing:1px;">
            <i class="fas fa-check-circle"></i> {{ session('flash') }}
        </span>
    @endif

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        @auth
            @php
                // SELALU definisikan $user agar tidak undefined
                $user = Auth::user();

                // Fallback isi notifikasi jika belum di-provide dari controller/composer
                if (!isset($unreadCount) || !isset($recentNotifs)) {
                    $unreadCount  = $user->notifikasi()->where('dibaca', false)->count();
                    $recentNotifs = $user->notifikasi()
                                        ->orderByDesc('dibuat_pada')
                                        ->limit(5)
                                        ->get();
                }
            @endphp

            <!-- Notifikasi: ikon lonceng + dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    @if(($unreadCount ?? 0) > 0)
                        <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right animate__animated animate__fadeIn">
                    <span class="dropdown-header">{{ $unreadCount ?? 0 }} Notifikasi Belum Dibaca</span>
                    <div class="dropdown-divider"></div>

                    @forelse(($recentNotifs ?? collect()) as $notif)
                        <a href="{{ route('notifikasi.read', $notif->id) }}"
                           class="dropdown-item {{ !$notif->dibaca ? 'font-weight-bold' : '' }}">
                            <i class="fas fa-envelope mr-2"></i>
                            {{ Str::limit($notif->pesan, 40) }}
                            <span class="float-right text-muted text-sm">
                                {{-- Robust: parse ke Carbon kalau masih string --}}
                                {{ ($notif->dibuat_pada instanceof \Illuminate\Support\Carbon)
                                    ? $notif->dibuat_pada->diffForHumans()
                                    : \Carbon\Carbon::parse($notif->dibuat_pada)->diffForHumans()
                                }}
                            </span>
                        </a>
                        <div class="dropdown-divider"></div>
                    @empty
                        <span class="dropdown-item text-center text-muted">Tidak ada notifikasi</span>
                        <div class="dropdown-divider"></div>
                    @endforelse

                    <a href="{{ route('notifikasi.index') }}" class="dropdown-item dropdown-footer">
                        Lihat Semua Notifikasi
                    </a>
                </div>
            </li>

            <!-- User Account Menu -->
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->nama_lengkap) }}&background=1E88E5&color=fff&size=32"
                         class="rounded-circle mr-2" alt="avatar" width="32" height="32">
                    <span class="d-none d-md-inline">{{ $user->nama_lengkap }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right animate__animated animate__fadeIn" aria-labelledby="navbarDropdown" style="min-width:250px;">
                    <div class="dropdown-item-text small text-muted">
                        <div><b>{{ $user->nama_lengkap }}</b></div>
                        <div><i class="fas fa-envelope"></i> {{ $user->email }}</div>
                        <div><i class="fas fa-user-tag"></i>
                            {{ $user->peran->deskripsi ?? $user->peran->nama ?? '-' }}
                        </div>
                        <div class="text-success mt-1"><i class="fas fa-circle"></i> Online</div>
                        <div class="text-muted small">
                            <i class="fas fa-clock"></i>
                            {{ $user->last_activity
                                ? 'Aktif ' . \Carbon\Carbon::parse($user->last_activity)->diffForHumans()
                                : 'Baru login'
                            }}
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('account.settings') }}">
    <i class="fas fa-user-cog"></i> Pengaturan Akun
</a>
                    <a class="dropdown-item text-danger" href="#" id="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </li>
        @endauth
    </ul>
</nav>

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    // Konfirmasi Logout
    $('#logout-link').on('click', function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Logout?',
            text: 'Anda yakin ingin keluar dari sesi saat ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Logout',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $('#logout-form').submit();
            }
        });
    });
});
</script>
@endpush
