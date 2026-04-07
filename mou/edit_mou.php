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

// Ambil data MOU berdasarkan ID
$id = $_GET['id'] ?? 0;
$query = "SELECT * FROM mou WHERE id_mou = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    echo "<script>alert('Data MOU tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $no_mou_eks = trim($_POST['no_mou_eks']);
    $no_mou = trim($_POST['no_mou']);
    $nama_mou = trim($_POST['nama_mou']);
    $pihak_1 = trim($_POST['pihak_1']);
    $pihak_2 = trim($_POST['pihak_2']);
    $tingkat = trim($_POST['tingkat']);
    $tgl_mou = $_POST['tgl_mou'];
    $desk_mou = trim($_POST['desk_mou']);
    $current_file = $data['file'];

    // Jika upload file baru
    if (!empty($_FILES['file']['name'])) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext != 'pdf') {
            echo "<script>alert('File harus berupa PDF!'); window.location.href='edit_mou.php?id=$id';</script>";
            exit();
        }

        $target_dir = "file_mou/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        // Hapus file lama
        if (!empty($current_file) && file_exists($target_dir . $current_file)) {
            unlink($target_dir . $current_file);
        }

        $clean_nama = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower(str_replace(' ', '_', $no_mou)));
        $clean_tgl = date('Ymd', strtotime($tgl_mou));
        $new_file_name = "mou_{$clean_nama}_{$clean_tgl}.pdf";

        if (!move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
            echo "<script>alert('Gagal mengunggah file!'); window.location.href='edit_mou.php?id=$id';</script>";
            exit();
        }

        $current_file = $new_file_name;
    }

    // Update data
    $stmt = $conn->prepare("UPDATE mou SET 
        no_mou_eks=?, no_mou=?, nama_mou=?, pihak_1=?, pihak_2=?, tingkat=?,
        tgl_mou=?, desk_mou=?, file=? WHERE id_mou=?");

    $stmt->bind_param("sssssssssi",
        $no_mou_eks, $no_mou, $nama_mou, $pihak_1, $pihak_2, $tingkat,
        $tgl_mou, $desk_mou, $current_file, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!'); window.location.href='edit_mou.php?id=$id';</script>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit MOU</title>
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
                <h1 class="h2">Edit MOU</h1>
            </div>

            <div class="card border-0 mb-5">
                <div class="card-body p-4">

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">No MOU Eksternal</label>
                            <input type="text" name="no_mou_eks" class="form-control"
                                   value="<?= htmlspecialchars($data['no_mou_eks']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No MOU Internal</label>
                            <input type="text" name="no_mou" class="form-control"
                                   value="<?= htmlspecialchars($data['no_mou']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama MOU</label>
                            <input type="text" name="nama_mou" class="form-control"
                                   value="<?= htmlspecialchars($data['nama_mou']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pihak 1</label>
                            <input type="text" name="pihak_1" class="form-control"
                                   value="<?= htmlspecialchars($data['pihak_1']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pihak 2</label>
                            <input type="text" name="pihak_2" class="form-control"
                                   value="<?= htmlspecialchars($data['pihak_2']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tingkat</label>
                            <input type="text" name="tingkat" class="form-control"
                                   value="<?= htmlspecialchars($data['tingkat']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal MOU</label>
                            <input type="date" name="tgl_mou" class="form-control"
                                   value="<?= htmlspecialchars($data['tgl_mou']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi MOU</label>
                            <textarea name="desk_mou" class="form-control" rows="3"><?= htmlspecialchars($data['desk_mou']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload File (PDF)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf">
                            <?php if (!empty($data['file'])): ?>
                                <div class="mt-2">
                                    <small>File saat ini: <?= htmlspecialchars($data['file']) ?></small><br>
                                    <a href="file_mou/<?= htmlspecialchars($data['file']) ?>" target="_blank"
                                       class="btn btn-outline-primary btn-sm mt-1">
                                        <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="index.php" class="btn btn-secondary me-2">Kembali</a>
                            <button type="submit" class="btn btn-primary">Perbarui</button>
                        </div>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
