<?php

class Flasher {
    public static function setFlash($pesan, $aksi, $tipe) {
        $_SESSION['flash'] = [
            'pesan' => $pesan,
            'aksi' => $aksi,
            'tipe' => $tipe // 'success', 'danger', dll (sesuai kelas alert bootstrap)
        ];
    }

    public static function flash() {
    if (isset($_SESSION['flash'])) {
        $tipe = $_SESSION['flash']['tipe'];
        // Tentukan ikon berdasarkan tipe notifikasi
        $icon = 'bi-info-circle-fill'; // default
        if ($tipe == 'success') $icon = 'bi-check-circle-fill';
        if ($tipe == 'danger') $icon = 'bi-exclamation-triangle-fill';

        echo '<div class="alert alert-' . $tipe . ' alert-dismissible fade show" role="alert">
                <i class="bi ' . $icon . ' me-2"></i>
                Data <strong>' . $_SESSION['flash']['pesan'] . '</strong> ' . $_SESSION['flash']['aksi'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['flash']);
    }
}
}