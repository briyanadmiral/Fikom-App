<?php 
    // Logika URL
    $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri_segments = explode('/', $uri_path);
    $controller_name = $uri_segments[3] ?? 'index'; 
    $method_name = $uri_segments[4] ?? 'index';

    // Cek apakah Login sebagai Admin (SI atau TI)
    $isAdmin = !empty($_SESSION['admin_siega']) || !empty($_SESSION['admin_ti']);
    
    // Cek apakah Login sebagai User (SI atau TI)
    $isUser  = !empty($_SESSION['users_siega']) || !empty($_SESSION['users_ti']);

    // Data Profil (Prioritas pakai Session biar tidak error kalau $data['user'] kosong)
    $namaUser = $_SESSION['user_name'] ?? $data['user']['nama'] ?? 'User';
    $emailUser = $_SESSION['user_email'] ?? $data['user']['email'] ?? '-';
    $prodiUser = $data['user']['nama_prodi'] ?? 'Inventaris Lab';

    // --- LOGIKA URL KEMBALI DINAMIS ---
    // Menentukan arah tombol keluar berdasarkan role dari Main App
    $url_keluar = '/fikomapp/index.php'; // Default untuk Dosen / Mahasiswa

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
        $url_keluar = '/fikomapp/superadmin/superadmin_home.php'; // Khusus Superadmin
    }
?>

<div class="d-flex">

    <div class="d-none d-lg-flex flex-column flex-shrink-0 p-3 sidebar-desktop vh-100" style="width: 280px;">
        <a href="<?= BASE_URL; ?>" class="d-flex align-items-center mb-3 text-decoration-none">
            <span class="fs-4 fw-bold text-dark">Inventaris Lab</span>
        </a>
        <hr>
        
        <ul class="nav nav-pills flex-column mb-auto">
            
            <?php if ($isAdmin): ?>
                
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'admin' || $controller_name == 'index') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/admin">
                        <i class="bi bi-house-door me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'barang') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/barang">
                        <i class="bi bi-box-seam me-2"></i>Data Barang
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'jenisbarang') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/jenisbarang">
                        <i class="bi bi-tags me-2"></i>Jenis Barang
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'peminjaman' && $method_name == 'index') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/peminjaman">
                        <i class="bi bi-patch-check me-2"></i>ACC Peminjaman
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'peminjaman' && $method_name == 'riwayat') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/peminjaman/riwayat">
                        <i class="bi bi-clock-history me-2"></i>Riwayat Peminjaman
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'stockopname') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/stockopname">
                        <i class="bi bi-clipboard-check me-2"></i>Stock Opname
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'laporan') ? 'active' : ''; ?>" data-bs-toggle="collapse" href="#laporanSubmenu" role="button">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan
                    </a>
                    <div class="collapse" id="laporanSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/laporan/stok">Laporan Stok</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/laporan/peminjaman">Lap. Peminjaman</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'log') ? 'active' : ''; ?>" data-bs-toggle="collapse" href="#logSubmenu" role="button">
                        <i class="bi bi-body-text me-2"></i>Log Sistem
                    </a>
                    <div class="collapse" id="logSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/log/aktivitas">Log Aktivitas</a></li>
                            <li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/log/stok">Log Stok</a></li>
                        </ul>
                    </div>
                </li>

            <?php elseif ($isUser): ?>
                
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($controller_name == 'user' || $controller_name == 'index') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/user">
                        <i class="bi bi-house-door me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($method_name == 'lihatBarang') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/user/lihatBarang">
                        <i class="bi bi-box-seam me-2"></i>Lihat Barang
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link <?= ($method_name == 'saya') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/peminjaman/saya">
                        <i class="bi bi-card-checklist me-2"></i>Peminjaman Saya
                    </a>
                </li>
            
            <?php endif; ?>
        </ul>
        <hr>
        <div class="mt-auto">
             <a class="btn btn-danger w-100 fw-bold" href="<?= $url_keluar; ?>">
                 <i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Menu Utama
             </a>
        </div>
    </div>


    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMobile" aria-labelledby="sidebarMobileLabel">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title fw-bold" id="sidebarMobileLabel">Inventaris Lab</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="nav nav-pills flex-column mb-auto">
                
                <?php if ($isAdmin): ?>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'admin' || $controller_name == 'index') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/admin"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'barang') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/barang"><i class="bi bi-box-seam me-2"></i>Data Barang</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'jenisbarang') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/jenisbarang"><i class="bi bi-tags me-2"></i>Jenis Barang</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'peminjaman' && $method_name == 'index') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/peminjaman"><i class="bi bi-patch-check me-2"></i>ACC Peminjaman</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'peminjaman' && $method_name == 'riwayat') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/peminjaman/riwayat"><i class="bi bi-clock-history me-2"></i>Riwayat Peminjaman</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'stockopname') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/stockopname"><i class="bi bi-clipboard-check me-2"></i>Stock Opname</a></li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= ($controller_name == 'laporan') ? 'active' : ''; ?>" data-bs-toggle="collapse" href="#laporanSubmenuMobile" role="button"><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan</a>
                        <div class="collapse" id="laporanSubmenuMobile"><ul class="nav flex-column ms-3"><li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/laporan/stok">Laporan Stok</a></li><li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/laporan/peminjaman">Lap. Peminjaman</a></li></ul></div>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= ($controller_name == 'log') ? 'active' : ''; ?>" data-bs-toggle="collapse" href="#logSubmenuMobile" role="button"><i class="bi bi-body-text me-2"></i>Log Sistem</a>
                        <div class="collapse" id="logSubmenuMobile"><ul class="nav flex-column ms-3"><li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/log/aktivitas">Log Aktivitas</a></li><li class="nav-item"><a class="nav-link py-1" href="<?= BASE_URL; ?>/log/stok">Log Stok</a></li></ul></div>
                    </li>
                
                <?php elseif ($isUser): ?>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($controller_name == 'user' || $controller_name == 'index') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/user"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($method_name == 'lihatBarang') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/user/lihatBarang"><i class="bi bi-box-seam me-2"></i>Lihat Barang</a></li>
                    <li class="nav-item mb-2"><a class="nav-link <?= ($method_name == 'saya') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>/peminjaman/saya"><i class="bi bi-card-checklist me-2"></i>Peminjaman Saya</a></li>
                <?php endif; ?>
            </ul>
            <hr>
            <a class="btn btn-danger w-100 fw-bold" href="<?= $url_keluar; ?>">
                <i class="bi bi-arrow-left-circle me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="w-100">
        <nav class="navbar navbar-expand-lg border-bottom mb-4 p-3">
            <div class="container-fluid">
                <button class="btn btn-light border d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile"><i class="bi bi-list"></i></button>
                <h5 class="m-0 d-none d-lg-block fw-bold text-dark"><?= htmlspecialchars($data['judul']); ?></h5>
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="me-2 d-none d-lg-inline fw-semibold"><?= htmlspecialchars($namaUser); ?></span>
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #2563eb;">
                                <strong><?= strtoupper(substr($namaUser, 0, 1)); ?></strong>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-small shadow border-0" aria-labelledby="dropdownUser">
                            <li><span class="dropdown-item-text"><b><?= htmlspecialchars($prodiUser); ?></b></span></li>
                            <li><span class="dropdown-item-text text-muted"><?= htmlspecialchars($emailUser); ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-bold" href="<?= $url_keluar; ?>"><i class="bi bi-box-arrow-right me-2"></i>Kembali ke Menu Utama</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <main class="container-fluid">