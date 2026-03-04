<?php 
// Catatan: Pengecekan session sebaiknya ada di file induk (seperti index.php), 
// tapi jika kamu menaruhnya di sini pastikan sidebar di-include PALING ATAS sebelum HTML apapun.
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    header("Location: ../mou.php");
    exit;
}

// include 'koneksi.php'; // Biasanya koneksi sudah di-include di file induk, jadi ini opsional di sini agar tidak double.
?>

<div class="col-md-2 bg-dark text-white min-vh-100 d-flex flex-column align-items-center p-3">
  <img src="assets\img\logo.png" alt="Logo" class="mb-3" style="width: 180px; height: auto;">

  <h5 class="text-center mb-0">MOU FIKOM</h5>
  <small class="text-muted mb-4">Fakultas Ilmu Komputer</small>

  <nav class="nav flex-column w-100">
    <a href="index.php" class="nav-link text-white bg-primary rounded px-3 py-2 mb-2">
      <i class="bi bi-house-door-fill me-2"></i> Dashboard
    </a>

    <a href="../index.php" class="nav-link text-white bg-danger rounded px-3 py-2 mb-2">
      <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Menu Utama
    </a>
  </nav>

  <div class="mt-auto text-center">
    <small>&copy; <?= date('Y') ?> FIKOM</small>
  </div>
</div>