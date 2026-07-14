<div class="container-fluid">
    <h3 class="mb-4 text-gray-800">Student Management</h3>

    <div class="card shadow mb-4">
        <div class="card-body">
            <!-- Navigasi Tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-dark" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        Pending (<?= count($data['pending']); ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-muted" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                        Approved (<?= count($data['approved']); ?>)
                    </button>
                </li>
            </ul>

            <!-- Isi Tabel dalam Tabs -->
            <div class="tab-content mt-3" id="myTabContent">
                
                <!-- TAB PENDING -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Email</th>
                                    <th>WhatsApp</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['pending'] as $user) : ?>
                                <tr>
                                    <td><?= $user['nama']; ?></td>
                                    <td><?= $user['nim']; ?></td>
                                    <td><?= $user['email']; ?></td>
                                    <td><?= $user['whatsapp']; ?></td>
                                    <td>
                                        <div class="d-flex justify-content-start" style="gap: 5px;">
                                            <!-- Tombol Verify (Hijau) -->
                                            <a href="<?= BASE_URL; ?>/admin/setujui_user/<?= $user['id_user']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Verifikasi mahasiswa ini?');">
                                                <i class="fas fa-check"></i> Verify
                                            </a>
                                            
                                            <!-- Tombol Decline (Merah) -->
                                            <a href="<?= BASE_URL; ?>/admin/tolak_user/<?= $user['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak dan hapus pengajuan akses mahasiswa ini?');">
                                                <i class="fas fa-times"></i> Decline
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB APPROVED -->
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th>Email</th>
                                    <th>WhatsApp</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['approved'] as $user) : ?>
                                <tr>
                                    <td><?= $user['nama']; ?></td>
                                    <td><?= $user['nim']; ?></td>
                                    <td><?= $user['email']; ?></td>
                                    <td><?= $user['whatsapp']; ?></td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>