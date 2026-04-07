<?php
session_start();

// --- SATPAM SESSION DARI BRIDGE ---
// Cek apakah tiket 'mou_admin' sudah ada?
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    // Kalau belum punya tiket, tendang balik ke Bridge
    header("Location: ../mou.php");
    exit;
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_mou_eks = trim($_POST['no_mou_eks']); // Data baru
    $no_mou = trim($_POST['no_mou']);
    $nama_mou = trim($_POST['nama_mou']);
    $pihak_1 = trim($_POST['pihak_1']);
    $pihak_2 = trim($_POST['pihak_2']);
    $tingkat = trim($_POST['tingkat']);
    $tgl_mou = $_POST['tgl_mou'];
    $desk_mou = trim($_POST['desk_mou']);

    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    
    // Cek jika ada file yang diupload
    if (!empty($file_name)) {
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext != 'pdf') {
            echo "<script>alert('File harus berupa PDF!'); window.location.href='tambah_mou.php';</script>";
            exit();
        }

        $target_dir = "file_mou/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $clean_nama = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower(str_replace(' ', '_', $no_mou)));
        $clean_tgl = date('Ymd', strtotime($tgl_mou));
        $new_file_name = "mou_{$clean_nama}_{$clean_tgl}.pdf";
        $target_file = $target_dir . $new_file_name;

        if (!move_uploaded_file($file_tmp, $target_file)) {
            echo "<script>alert('Gagal mengunggah file!'); window.location.href='tambah_mou.php';</script>";
            exit();
        }
    } else {
        $new_file_name = null; // Tidak ada file yang diunggah
    }

    // Update query untuk memasukkan data baru
    $stmt = $conn->prepare("INSERT INTO mou (no_mou_eks, no_mou, nama_mou, pihak_1, pihak_2, tingkat, tgl_mou, desk_mou, file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $no_mou_eks, $no_mou, $nama_mou, $pihak_1, $pihak_2, $tingkat, $tgl_mou, $desk_mou, $new_file_name);
    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil ditambahkan!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data ke database!'); window.location.href='tambah_mou.php';</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah MOU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/glass.css?v=<?= time() ?>">
</head>
<body>
<div class="container-fluid">
    <div class="row min-vh-100">
        <?php include 'sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tambah MOU</h1>
            </div>

            <div class="card border-0 mb-5">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">No MOU Eksternal</label>
                            <input type="text" name="no_mou_eks" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No MOU Internal</label>
                            <input type="text" name="no_mou" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama MOU</label>
                            <input type="text" name="nama_mou" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pihak 1</label>
                            <input type="text" name="pihak_1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pihak 2</label>
                            <input type="text" name="pihak_2" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tingkat</label>
                            <select name="tingkat" class="form-select" required>
                                <option value="" selected disabled>Pilih Tingkat...</option>
                                <option value="Internasional">Internasional</option>
                                <option value="Nasional">Nasional</option>
                                <option value="Provinsi">Provinsi</option>
                                <option value="Lokal">Lokal</option>
                                <option value="Fakultas">Fakultas</option>
                                <option value="Universitas">Universitas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal MOU</label>
                            <input type="date" name="tgl_mou" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi MOU</label>
                            <textarea name="desk_mou" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload File (PDF)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf">
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-secondary me-2">Kembali</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>

