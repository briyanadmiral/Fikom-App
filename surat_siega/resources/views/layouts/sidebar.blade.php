<aside class="main-sidebar elevation-0" style="display: flex; flex-direction: column; height: 100vh; position: fixed;">
    <a href="{{ url('/') }}" class="brand-link d-flex align-items-center justify-content-center py-4"
        style="border-bottom: 1px solid rgba(255, 255, 255, 0.7) !important;">
        <div class="brand-icon d-flex align-items-center justify-content-center mr-2" 
             style="width: 40px; height: 40px; background: rgba(138, 156, 204, 0.2); border-radius: 10px; border: 1px solid rgba(138, 156, 204, 0.3);">
            <i class="bi bi-envelope-paper text-primary" style="font-size: 1.4rem;"></i>
        </div>
        <span class="brand-text font-weight-bold" style="font-size: 1.2rem; tracking: 1px; color: #3a4252 !important;">Surat FIKOM</span>
    </a>

    <div class="sidebar" style="flex: 1; overflow-y: auto; overflow-x: hidden; width: 100%;">
        @auth
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center"
                style="background: rgba(255, 255, 255, 0.4); 
                    border-radius: 12px; 
                    padding: 12px !important;
                    margin: 0 10px 15px 10px !important;
                    border: 1px solid rgba(255, 255, 255, 0.7);">
                <div class="image pr-2">
                    <img src="{{ Auth::user()->foto_url }}"
                        class="img-circle" 
                        style="border: 2px solid rgba(138, 156, 204, 0.5) !important; width: 45px; height: 45px; object-fit: cover;"
                        alt="{{ Auth::user()->nama_lengkap }}">
                </div>
                <div class="info w-100 pl-2" style="line-height: 1.2;">
                    <a href="{{ route('account.settings') }}" class="d-block" title="Pengaturan Akun"
                        style="font-weight: 600; font-size: 0.95rem; color: #3a4252 !important; white-space: normal; word-wrap: break-word;">
                        {{ Auth::user()->nama_lengkap }}
                    </a>
                    <div class="mt-1 d-flex align-items-center">
                        <span class="badge badge-pill" 
                              style="font-size: 0.65rem; font-weight: 600; padding: 3px 8px; background-color: rgba(138, 156, 204, 0.15); color: #8a9ccc; border: 1px solid rgba(138, 156, 204, 0.2);">
                            {{ ucwords(str_replace('_', ' ', Auth::user()->peran->nama ?? 'User')) }}
                        </span>
                    </div>
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
                <li class="nav-header" style="color: #8a9ccc !important; font-weight: 700; font-size: 0.75rem;">MENU UTAMA</li>
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ $isRoute('home') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-house-door"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Notifikasi -->
                @auth
                <li class="nav-item">
                    <a href="{{ route('notifikasi.index') }}"
                        class="nav-link {{ $isRoute('notifikasi.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bell"></i>
                        <p>
                            Notifikasi
                            @php
                                $unreadCount = Auth::user()->notifikasi()->where('dibaca', false)->count();
                            @endphp
                            @if ($unreadCount > 0)
                                <span class="right badge badge-danger">{{ $unreadCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                @endauth

                <!-- CRUD Pengguna (Admin TU Only) -->
                @if ($peranId === 1)
                    <li class="nav-header" style="color: #8a9ccc !important; font-weight: 700; font-size: 0.75rem;">ADMINISTRASI</li>
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                            class="nav-link {{ $isRoute('users.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Kelola Pengguna</p>
                        </a>
                    </li>
                @endif

                <!-- =============== SURAT TUGAS =============== -->
                <li class="nav-header" style="color: #8a9ccc !important; font-weight: 700; font-size: 0.75rem;">SURAT & DOKUMEN</li>

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
                            <i class="nav-icon bi bi-file-earmark-text"></i>
                            <p>
                                Surat Tugas
                                <i class="right bi bi-chevron-left"></i>
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
                            <i class="nav-icon bi bi-journal-text"></i>
                            <p>
                                Surat Keputusan
                                <i class="right bi bi-chevron-left"></i>
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
                            <i class="nav-icon bi bi-gear"></i>
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
                            <i class="nav-icon bi bi-book"></i>
                            <p>
                                Library Konten
                                <i class="right bi bi-chevron-left"></i>
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
                            <i class="nav-icon bi bi-clock-history"></i>
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
                            <i class="nav-icon bi bi-file-earmark-arrow-up"></i>
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
                                <i class="nav-icon bi bi-pen"></i>
                                <p>Tanda Tangan Saya</p>
                            </a>
                        </li>
                    @endif

                    {{-- Notification Preferences (All) --}}
                    <li class="nav-item">
                        <a href="{{ route('notification_preferences.edit') }}"
                            class="nav-link {{ $isRoute('notification_preferences.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-bell-slash"></i>
                            <p>Preferensi Notifikasi</p>
                        </a>
                    </li>

                    {{-- Account Settings --}}
                    <li class="nav-item">
                        <a href="{{ route('account.settings') }}"
                            class="nav-link {{ $isRoute('account.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-gear"></i>
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
        /* Modern Glass Accent */
        aside.main-sidebar .nav-link:hover {
            background-color: rgba(138, 156, 204, 0.15) !important;
            transform: translateX(5px);
            transition: all 0.3s ease;
        }
 
        /* Active menu with glass highlight */
        aside.main-sidebar .nav-link.active {
            background: rgba(138, 156, 204, 0.2) !important;
            border-left: 4px solid #8a9ccc !important;
            color: #8a9ccc !important;
        }
 
        aside.main-sidebar .nav-link.active i {
            color: #8a9ccc !important;
        }
 
        /* Sidebar section headers */
        .nav-header {
            padding: 1rem 1rem 0.5rem 1.5rem !important;
            letter-spacing: 0.5px;
        }

        /* Clean separators */
        hr {
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            margin: 10px 15px;
        }
    </style>
@endpush
