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
                $user = Auth::user();

                if (!isset($unreadCount) || !isset($recentNotifs)) {
                    $unreadCount  = $user->notifikasi()->where('dibaca', false)->count();
                    $recentNotifs = $user->notifikasi()
                                        ->where('dibaca', false)
                                        ->orderByDesc('dibuat_pada')
                                        ->limit(5)
                                        ->get();
                }
            @endphp

            <!-- Notifikasi: ikon lonceng + dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link position-relative" data-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    @if(($unreadCount ?? 0) > 0)
                        <span class="badge badge-warning position-absolute" 
                              style="top: 5px; right: 5px; font-size: 0.65rem; padding: 2px 5px; border-radius: 10px;">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right animate__animated animate__fadeIn" 
                     style="max-height: 400px; overflow-y: auto; min-width: 320px;">
                    <span class="dropdown-header bg-light py-2">
                        <strong>{{ $unreadCount ?? 0 }}</strong> Notifikasi Belum Dibaca
                    </span>
                    <div class="dropdown-divider m-0"></div>

                    @forelse(($recentNotifs ?? collect()) as $notif)
                        <a href="{{ route('notifikasi.read', $notif->id) }}"
                           class="dropdown-item py-2 {{ !$notif->dibaca ? 'bg-light font-weight-bold' : '' }}">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-envelope mt-1 mr-2 text-primary"></i>
                                <div class="flex-grow-1">
                                    <p class="mb-1 text-sm">{{ Str::limit($notif->pesan, 50) }}</p>
                                    <span class="text-muted text-xs">
                                        <i class="far fa-clock"></i>
                                        {{ ($notif->dibuat_pada instanceof \Illuminate\Support\Carbon)
                                            ? $notif->dibuat_pada->diffForHumans()
                                            : \Carbon\Carbon::parse($notif->dibuat_pada)->diffForHumans()
                                        }}
                                    </span>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider m-0"></div>
                    @empty
                        <div class="dropdown-item text-center text-muted py-3">
                            <i class="far fa-bell-slash fa-2x mb-2 d-block"></i>
                            Tidak ada notifikasi
                        </div>
                        <div class="dropdown-divider m-0"></div>
                    @endforelse

                    <a href="{{ route('notifikasi.index') }}" class="dropdown-item dropdown-footer text-center py-2 bg-light">
                        <strong>Lihat Semua Notifikasi</strong>
                    </a>
                </div>
            </li>

            <!-- User Account Menu -->
            <li class="nav-item dropdown">
                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-toggle="dropdown" aria-expanded="false">
                    <img src="{{ $user->foto_url }}"
                         class="rounded-circle mr-2" alt="avatar" width="32" height="32" style="object-fit: cover;">
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
                    {{-- ✅ GANTI: Dari route('logout') ke route('external.exit') --}}
                    <form id="logout-form" action="{{ route('external.exit') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        @else
            <li class="nav-item">
                {{-- ✅ REVISI: Hapus route login karena login eksternal --}}
                <a class="nav-link" href="#" onclick="alert('Silakan login melalui Dashboard Menu');">
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
