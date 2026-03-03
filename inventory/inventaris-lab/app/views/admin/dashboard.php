<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jenis Barang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_barang']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam-fill fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Barang Dipinjam</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_dipinjam']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-up-right-circle-fill fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pengajuan Baru</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_diajukan']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-patch-question-fill fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Peminjaman Terlambat</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $data['total_terlambat']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock-history fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Pengajuan Peminjaman Terbaru</h6>
        <a href="<?= BASE_URL; ?>/peminjaman">Lihat Semua &rarr;</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Nama Barang</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['pengajuan_terbaru'])): ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada pengajuan baru.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($data['pengajuan_terbaru'], 0, 5) as $pinjam): ?>
                            <tr>
                                <td><?= htmlspecialchars($pinjam['email_peminjam']); ?></td>
                                <td><?= htmlspecialchars($pinjam['nama_barang']); ?></td>
                                <td><?= date('d M Y', strtotime($pinjam['tgl_pinjam'])); ?></td>
                                <td><?= date('d M Y', strtotime($pinjam['tgl_kembali'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>