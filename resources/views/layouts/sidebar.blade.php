<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('/') }}" class="brand-link"
        style="background-color: #4b0082 !important; /* Ungu gelap solid */
               border-bottom: 4px solid #8B5CF6 !important;
               text-align: center;
               padding: 1.5rem 1rem 2rem 1rem;
               box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
        <span
            style="color: transparent;
                     font-weight: 700;
                     font-size: 1.8rem;
                     letter-spacing: 6px;
                     text-transform: uppercase;
                     font-family: 'Arial Black', sans-serif;
                     display: block;
                     margin-bottom: -0.5rem;
                     -webkit-text-stroke: 1px #ffffff;
                     text-shadow: 0 0 100px #fff,
                                  0 0 40px #fff,
                                  0 0 20px #fff; ">
            Surat
        </span>

        <span
            style="color: transparent;
                     font-weight: 900;
                     font-size: 3.5rem;
                     letter-spacing: 14px;
                     text-transform: uppercase;
                     font-family: 'Impact', 'Arial Black', sans-serif;
                     display: block;
                     -webkit-text-stroke: 1px #ffffff;
                     text-shadow: 0 0 100px #fff,
                                  0 0 40px #fff,
                                  0 0 30px #fff; ">
            SIEGA
        </span>
    </a>

    <div class="sidebar">
        @auth
            <div class="user-panel mt-3 pb-3 mb-3 d-flex"
                style="background: rgba(139, 92, 246, 0.08); 
                    border-radius: 10px; 
                    padding: 1rem !important;
                    margin: 0.5rem 0.5rem 1rem 0.5rem !important;">
                <div class="image">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama_lengkap) }}&background=8B5CF6&color=fff&size=128"
                        class="img-circle elevation-2" style="border: 3px solid #8B5CF6 !important;"
                        alt="{{ Auth::user()->nama_lengkap }}">
                </div>
                <div class="info">
                    <a href="{{ route('account.settings') }}" class="d-block" title="Pengaturan Akun"
                        style="color: #ffffff;
                          font-weight: 600;
                          text-shadow: 0 0 2px rgba(255, 255, 255, 0.5);">
                        {{ Str::limit(Auth::user()->nama_lengkap, 20) }}
                    </a>
                    <small class="d-block" style="color: #a0aec0;
                              margin-top: 0.2rem;">
                        <i class="fas fa-circle text-success"
                            style="font-size: 0.6rem; margin-right: 0.3rem; color: #34d399 !important;"></i>
                        {{ Auth::user()->peran->nama ?? 'User' }}
                    </small>
                </div>
            </div>
        @endauth

        @php
            $peranId = (int) optional(Auth::user())->peran_id;
            $isRoute = fn(...$names) => request()->routeIs(...$names);
        @endphp

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-header" style="color: #a78bfa !important;">MENU UTAMA</li>
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ $isRoute('home') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Notifikasi -->
                @auth
                <li class="nav-item">
                    <a href="{{ route('notifikasi.index') }}"
                        class="nav-link {{ $isRoute('notifikasi.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>
                            Notifikasi
                            @php
                                $unreadCount = Auth::user()->notifikasi()->where('dibaca', false)->count();
                            @endphp
                            @if ($unreadCount > 0)
                                <span class="right badge badge-warning">{{ $unreadCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                @endauth

                <!-- CRUD Pengguna (Admin TU Only) -->
                @if ($peranId === 1)
                    <li class="nav-header" style="color: #a78bfa !important;">ADMINISTRASI</li>
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                            class="nav-link {{ $isRoute('users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Kelola Pengguna</p>
                        </a>
                    </li>
                @endif

                <!-- =============== SURAT TUGAS =============== -->
                <li class="nav-header" style="color: #a78bfa !important;">SURAT & DOKUMEN</li>

                {{-- Admin TU Surat Tugas --}}
                @if ($peranId === 1)
                    @php
                        $stAdminRoutes = [
                            'surat_tugas.all',
                            'surat_tugas.mine',
                            'surat_tugas.show',
                            'surat_tugas.edit',
                            'surat_tugas.update',
                            'surat_tugas.create',
                            'surat_tugas.arsipList',
                        ];
                        $stAdminOpen = $isRoute(...$stAdminRoutes);
                    @endphp
                    <li class="nav-item {{ $stAdminOpen ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $stAdminOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>
                                Surat Tugas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.all') }}"
                                    class="nav-link {{ $isRoute('surat_tugas.all', 'surat_tugas.create', 'surat_tugas.edit') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-warning"></i>
                                    <p>Input Surat Tugas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.mine') }}"
                                    class="nav-link {{ $isRoute('surat_tugas.mine') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Surat Tugas Saya</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.arsipList') }}"
                                    class="nav-link {{ $isRoute('surat_tugas.arsipList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-secondary"></i>
                                    <p>Arsip Surat Tugas</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Approver Surat Tugas --}}
                @if (in_array($peranId, [2, 3], true))
                    @php
                        $stApproverRoutes = ['surat_tugas.approveList', 'surat_tugas.mine'];
                        $stApproverOpen = $isRoute(...$stApproverRoutes);
                    @endphp
                    <li class="nav-item {{ $stApproverOpen ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $stApproverOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>
                                Surat Tugas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.approveList') }}"
                                    class="nav-link {{ $isRoute('surat_tugas.approveList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Approve Surat Tugas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('surat_tugas.mine') }}"
                                    class="nav-link {{ $isRoute('surat_tugas.mine') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Surat Tugas Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Pengguna biasa Surat Tugas --}}
                @if (in_array($peranId, [4, 5, 6], true))
                    <li class="nav-item">
                        <a href="{{ route('surat_tugas.mine') }}"
                            class="nav-link {{ $isRoute('surat_tugas.mine') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Surat Tugas Saya</p>
                        </a>
                    </li>
                @endif

                {{-- =============== SURAT KEPUTUSAN =============== --}}

                {{-- Admin TU Surat Keputusan --}}
                @if ($peranId === 1)
                    @php
                        $skAdminRoutes = [
                            'surat_keputusan.index',
                            'surat_keputusan.mine',
                            'surat_keputusan.show',
                            'surat_keputusan.edit',
                            'surat_keputusan.update',
                            'surat_keputusan.create',
                            'surat_keputusan.terbitList',
                            'surat_keputusan.arsipList',
                        ];
                        $skAdminOpen = $isRoute(...$skAdminRoutes);
                    @endphp
                    <li class="nav-item {{ $skAdminOpen ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $skAdminOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Surat Keputusan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Ruang kerja SK (draft/pending/disetujui/ditolak) --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.index') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.index', 'surat_keputusan.create', 'surat_keputusan.edit') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-warning"></i>
                                    <p>Ruang Kerja SK</p>
                                </a>
                            </li>

                            {{-- SK Terbit --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.terbitList') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.terbitList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>SK Terbit (Berlaku)</p>
                                </a>
                            </li>

                            {{-- Arsip SK --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.arsipList') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.arsipList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-secondary"></i>
                                    <p>Arsip SK</p>
                                </a>
                            </li>

                            {{-- SK yang mencantumkan user sebagai penerima --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.mine') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.mine') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Surat Keputusan Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Approver Surat Keputusan (Dekan/Wakil) --}}
                @if (in_array($peranId, [2, 3], true))
                    @php
                        $skApproverRoutes = [
                            'surat_keputusan.approveList',
                            'surat_keputusan.mine',
                            'surat_keputusan.show',
                            'surat_keputusan.approveForm',
                            'surat_keputusan.approvePreview',
                            'surat_keputusan.terbitList',
                        ];
                        $skApproverOpen = $isRoute(...$skApproverRoutes);
                    @endphp
                    <li class="nav-item {{ $skApproverOpen ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $skApproverOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Surat Keputusan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- Approve SK (pending) --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.approveList') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.approveList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Approve Surat Keputusan</p>
                                </a>
                            </li>

                            {{-- SK Terbit --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.terbitList') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.terbitList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>SK Terbit (Berlaku)</p>
                                </a>
                            </li>

                            {{-- SK Saya (sebagai penerima) --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.mine') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.mine') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Surat Keputusan Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Pengguna biasa Surat Keputusan (hanya SK Terbit + SK Saya) --}}
                @if (in_array($peranId, [4, 5, 6], true))
                    @php
                        $skUserRoutes = ['surat_keputusan.terbitList', 'surat_keputusan.mine'];
                        $skUserOpen = $isRoute(...$skUserRoutes);
                    @endphp
                    <li class="nav-item {{ $skUserOpen ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $skUserOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Surat Keputusan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            {{-- SK Terbit (semua SK yang sedang berlaku) --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.terbitList') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.terbitList') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>SK Terbit (Berlaku)</p>
                                </a>
                            </li>

                            {{-- SK yang ditujukan ke user ini --}}
                            <li class="nav-item">
                                <a href="{{ route('surat_keputusan.mine') }}"
                                    class="nav-link {{ $isRoute('surat_keputusan.mine') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Surat Keputusan Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Pengaturan (Admin & Approver) -->
                @if (in_array($peranId, [1, 2, 3], true))
                    <li class="nav-header" style="color: #a78bfa !important;">PENGATURAN</li>
                @endif

                {{-- Pengaturan Kop Surat (Admin TU) --}}
                @if ($peranId === 1)
                    <li class="nav-item">
                        <a href="{{ route('kop.index') }}" class="nav-link {{ $isRoute('kop.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Kop Surat</p>
                        </a>
                    </li>
                @endif

                {{-- TTD Saya moved to AKUN SAYA section --}}

                {{-- =============== LIBRARY KONTEN (Admin Only) =============== --}}
                @if ($peranId === 1)
                    @php
                        $libraryRoutes = ['menimbang_library.*', 'mengingat_library.*', 'surat_templates.*'];
                        $libraryOpen = $isRoute(...$libraryRoutes);
                    @endphp
                    <li class="nav-item {{ $libraryOpen ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $libraryOpen ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book-open"></i>
                            <p>
                                Library Konten
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('menimbang_library.index') }}"
                                    class="nav-link {{ $isRoute('menimbang_library.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Library Menimbang</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mengingat_library.index') }}"
                                    class="nav-link {{ $isRoute('mengingat_library.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-warning"></i>
                                    <p>Library Mengingat</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('surat_templates.index') }}"
                                    class="nav-link {{ $isRoute('surat_templates.*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Template Surat</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- =============== LAPORAN & MONITORING (Admin Only) =============== --}}
                @if ($peranId === 1)
                    <li class="nav-header" style="color: #a78bfa !important;">LAPORAN</li>

                    <li class="nav-item">
                        <a href="{{ route('audit_logs.index') }}"
                            class="nav-link {{ $isRoute('audit_logs.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Audit Log</p>
                        </a>
                    </li>
                @endif

                {{-- =============== TOOLS (Admin Only) =============== --}}
                @if ($peranId === 1)
                    <li class="nav-header" style="color: #a78bfa !important;">TOOLS</li>
                    <li class="nav-item">
                        <a href="{{ route('import.penerima.index') }}"
                            class="nav-link {{ $isRoute('import.penerima.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-import"></i>
                            <p>Import Penerima ST</p>
                        </a>
                    </li>
                @endif

                {{-- =============== AKUN SAYA (All Users) =============== --}}
                @auth
                    <li class="nav-header" style="color: #a78bfa !important;">AKUN SAYA</li>
                    
                    {{-- Signature (Dekan/Wakil Only) --}}
                    @if (in_array($peranId, [2, 3], true))
                        <li class="nav-item">
                            <a href="{{ route('signature.edit') }}"
                                class="nav-link {{ $isRoute('signature.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-signature"></i>
                                <p>Tanda Tangan Saya</p>
                            </a>
                        </li>
                    @endif

                    {{-- Notification Preferences (All) --}}
                    <li class="nav-item">
                        <a href="{{ route('notification_preferences.edit') }}"
                            class="nav-link {{ $isRoute('notification_preferences.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-bell-slash"></i>
                            <p>Preferensi Notifikasi</p>
                        </a>
                    </li>

                    {{-- Account Settings --}}
                    <li class="nav-item">
                        <a href="{{ route('account.settings') }}"
                            class="nav-link {{ $isRoute('account.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>Pengaturan Akun</p>
                        </a>
                    </li>
                @endauth

            </ul>
        </nav>
    </div>
</aside>

@push('css')
    <style>
        /* Purple accent on hover */
        aside.main-sidebar .nav-link:hover {
            background-color: rgba(139, 92, 246, 0.15) !important;
        }

        /* Active menu with purple border */
        aside.main-sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.25) 0%, rgba(139, 92, 246, 0.1) 100%) !important;
            border-left: 4px solid #8B5CF6 !important;
        }

        aside.main-sidebar .nav-link.active i {
            color: #a78bfa !important;
        }

        /* User panel hover effect */
        aside.main-sidebar .user-panel:hover {
            background: rgba(139, 92, 246, 0.12) !important;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
            cursor: pointer;
        }

        /* User panel info text */
        aside.main-sidebar .user-panel .info a {
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        aside.main-sidebar .user-panel .info a:hover {
            color: #e0e0e0;
        }

        aside.main-sidebar .user-panel .info small {
            color: #a0aec0;
            font-size: 0.8rem;
            margin-top: 0.2rem;
        }

        aside.main-sidebar .user-panel .info small .fa-circle.text-success {
            color: #34d399 !important;
        }

        /* Search input focus */
        .form-control-sidebar:focus {
            border-color: #8B5CF6 !important;
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.4) !important;
        }
    </style>
@endpush
