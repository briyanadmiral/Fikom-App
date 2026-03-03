<div class="alert alert-success mb-4">
    Selamat datang, <strong><?= htmlspecialchars($data['user']['nama']); ?>!</strong> Gunakan sistem ini untuk meminjam fasilitas laboratorium.
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Barang Sedang Dipinjam</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_dipinjam']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-up-right-circle-fill fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Menunggu Persetujuan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_diajukan']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-patch-question-fill fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Barang yang Sedang Anda Pinjam</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Wajib Kembali Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['peminjaman_aktif'])): ?>
                        <tr>
                            <td colspan="4" class="text-center">Anda sedang tidak meminjam barang apapun.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['peminjaman_aktif'] as $pinjam): ?>
                            <?php
                                // Logika untuk memberi warna jika mendekati atau lewat deadline
                                $row_class = '';
                                $today = strtotime(date('Y-m-d'));
                                $tgl_kembali = strtotime($pinjam['tgl_kembali']);
                                $selisih_hari = ($tgl_kembali - $today) / (60 * 60 * 24);

                                if ($selisih_hari < 0) {
                                    $row_class = 'table-danger'; // Terlambat
                                } elseif ($selisih_hari <= 2) {
                                    $row_class = 'table-warning'; // Mendekati deadline
                                }
                            ?>
                            <tr class="<?= $row_class; ?>">
                                <td><?= htmlspecialchars($pinjam['nama_barang']); ?></td>
                                <td><?= htmlspecialchars($pinjam['jumlah']); ?></td>
                                <td><?= date('d M Y', strtotime($pinjam['tgl_pinjam'])); ?></td>
                                <td>
                                    <strong><?= date('d M Y', $tgl_kembali); ?></strong>
                                    <?php if($selisih_hari < 0): ?>
                                        <span class="ms-2 badge bg-danger">Terlambat!</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-center">
            <a href="<?= BASE_URL; ?>/user/lihatBarang" class="btn btn-primary">Lihat & Pinjam Barang Lain</a>
            <a href="<?= BASE_URL; ?>/peminjaman/saya" class="btn btn-secondary">Lihat Semua Riwayat</a>
        </div>
    </div>
</div>