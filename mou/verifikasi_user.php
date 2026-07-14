<?php
// mou/verifikasi_user.php - Verifikasi Dosen Baru MOU
session_start();

// Cek apakah tiket 'mou_admin' sudah ada?
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    header("Location: ../mou.php");
    exit;
}

include 'koneksi.php'; // Ini menset $conn ke database mou

// Hubungkan ke database utama (app) untuk mengelola t_mou
$conn_app = fikom_db('app');

$message = '';
$message_type = '';

// Tangani aksi persetujuan / penolakan
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($conn_app) {
        if ($action === 'approve') {
            $stmt = $conn_app->prepare("UPDATE t_mou SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Dosen berhasil disetujui untuk mengakses MOU.";
                $message_type = "success";
            } else {
                $message = "Gagal menyetujui dosen.";
                $message_type = "danger";
            }
            $stmt->close();
        } elseif ($action === 'decline') {
            $stmt = $conn_app->prepare("DELETE FROM t_mou WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Pengajuan akses dosen berhasil ditolak.";
                $message_type = "success";
            } else {
                $message = "Gagal menolak pengajuan.";
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// Ambil dosen pending & active dari t_mou di database utama (app)
$pending_users = [];
$active_users = [];

if ($conn_app) {
    // Pending
    $res_pending = mysqli_query($conn_app, "SELECT * FROM t_mou WHERE role = 'dosen' AND status = 'pending' AND deleted_at IS NULL ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($res_pending)) {
        $pending_users[] = $row;
    }

    // Active
    $res_active = mysqli_query($conn_app, "SELECT * FROM t_mou WHERE role = 'dosen' AND status = 'active' AND deleted_at IS NULL ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($res_active)) {
        $active_users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dosen - MOU FIKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/glass.css?v=<?= time() ?>">
    <style>
        .tab-btn {
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 2px solid transparent;
            border-radius: 0;
            background: none !important;
            border: none !important;
            padding: 10px 20px;
            box-shadow: none !important;
        }
        .tab-btn.active {
            color: var(--dark) !important;
            border-bottom: 2px solid var(--primary) !important;
        }
        .tab-content-panel {
            display: none;
        }
        .tab-content-panel.active {
            display: block;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active {
            background-color: #dcfce7;
            color: #16a34a;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-action-custom {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row min-vh-100">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Verifikasi Dosen Baru</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card mt-3">
                <div class="card-header d-flex gap-3">
                    <button class="tab-btn active" onclick="openTab(event, 'pending-tab')">Pending (<?= count($pending_users) ?>)</button>
                    <button class="tab-btn" onclick="openTab(event, 'active-tab')">Approved (<?= count($active_users) ?>)</button>
                </div>
                <div class="card-body">
                    <!-- Tab Pending -->
                    <div id="pending-tab" class="tab-content-panel active">
                        <div class="table-responsive">
                            <table class="table table-hover text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Dosen</th>
                                        <th>NIP / NIDN</th>
                                        <th>Email</th>
                                        <th>Jurusan</th>
                                        <th>Status</th>
                                        <th style="width: 220px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($pending_users) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-muted py-4">Tidak ada dosen menunggu verifikasi.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pending_users as $user): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($user['nama'] ?? 'Dosen') ?></td>
                                                <td><?= htmlspecialchars($user['nim'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['jurusan']) ?></td>
                                                <td><span class="badge-pending">Pending</span></td>
                                                <td>
                                                    <a href="?action=approve&id=<?= $user['id'] ?>" class="btn btn-success btn-action-custom" onclick="return confirm('Setujui dosen ini untuk mengakses MOU?');">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </a>
                                                    <a href="?action=decline&id=<?= $user['id'] ?>" class="btn btn-danger btn-action-custom ms-1" onclick="return confirm('Tolak dan hapus pengajuan akses dosen ini?');">
                                                        <i class="bi bi-x-lg"></i> Decline
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Active -->
                    <div id="active-tab" class="tab-content-panel">
                        <div class="table-responsive">
                            <table class="table table-hover text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Dosen</th>
                                        <th>NIP / NIDN</th>
                                        <th>Email</th>
                                        <th>Jurusan</th>
                                        <th>Status</th>
                                        <th style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($active_users) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="text-muted py-4">Belum ada dosen yang aktif.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($active_users as $user): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($user['nama'] ?? 'Dosen') ?></td>
                                                <td><?= htmlspecialchars($user['nim'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= htmlspecialchars($user['jurusan']) ?></td>
                                                <td><span class="badge-active">Approved</span></td>
                                                <td>
                                                    <a href="?action=decline&id=<?= $user['id'] ?>" class="btn btn-danger btn-action-custom" onclick="return confirm('Hapus akses dosen ini?');">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openTab(evt, tabId) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content-panel");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabId).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
</script>
</body>
</html>
