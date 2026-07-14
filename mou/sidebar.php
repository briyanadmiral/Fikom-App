<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['mou_admin']) && !isset($_SESSION['mou_user'])) {
    header("Location: ../mou.php");
    exit;
}

// Menentukan arah tombol "Kembali ke Menu Utama" berdasarkan role dari Main App
$url_kembali = '../index.php'; // Default untuk mahasiswa / dosen biasa

if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
    $url_kembali = '../superadmin/superadmin_home.php'; // Khusus untuk Superadmin
}
?>

<div class="col-md-2 mou-sidebar min-vh-100 d-flex flex-column align-items-center p-3">
  <img src="assets/img/logo.png" alt="Logo" class="mb-3" style="width: 150px; height: auto; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">

  <h5 class="text-center mb-0 fw-bold">MOU FIKOM</h5>
  <p class="small text-muted mb-4">Arsip Kerja Sama</p>

  <nav class="nav flex-column w-100">
    <?php 
    $dashboard_url = (isset($_SESSION['mou_admin']) && $_SESSION['mou_admin'] === true) ? 'index.php' : 'index_umum.php';
    ?>
    <a href="<?= $dashboard_url ?>" class="nav-link <?= (in_array(basename($_SERVER['PHP_SELF']), ['index.php', 'index_umum.php'])) ? 'active' : '' ?>">
      <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <?php if (isset($_SESSION['mou_admin']) && $_SESSION['mou_admin'] === true): ?>
    <a href="verifikasi_user.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'verifikasi_user.php') ? 'active' : '' ?>">
      <i class="bi bi-person-check me-2"></i> Verifikasi Dosen
    </a>
    <?php endif; ?>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'dosen' || $_SESSION['role'] === 'superadmin')): ?>
    <a href="evaluasi_kepuasan.php" class="nav-link <?= (in_array(basename($_SERVER['PHP_SELF']), ['evaluasi_kepuasan.php', 'evaluasi_analisis.php'])) ? 'active' : '' ?>">
      <i class="bi bi-patch-check me-2"></i> Evaluasi Kepuasan
    </a>
    <?php endif; ?>

    <hr class="my-3 border-secondary opacity-25">

    <a href="<?= $url_kembali; ?>" class="nav-link text-danger">
      <i class="bi bi-box-arrow-left me-2"></i> Kembali ke Menu
    </a>
  </nav>

  <div class="mt-auto text-center">
    <small>&copy; <?= date('Y') ?> FIKOM</small>
  </div>
</div>