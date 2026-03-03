<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Laporan Stok Barang Keseluruhan</h6>
            <div>
                <a href="<?= BASE_URL; ?>/laporan/exportExcel" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export ke Excel
                </a>
                <a href="<?= BASE_URL; ?>/laporan/exportPdf" class="btn btn-sm btn-danger" target="_blank">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export ke PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <p>Laporan ini menampilkan daftar semua barang inventaris yang terdaftar di sistem untuk Prodi <?= htmlspecialchars($data['user']['nama_prodi']); ?>.</p>
            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Inventaris</th>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Total</th>
                            <th>Tersedia</th>
                            <th>Kondisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($data['barang'] as $barang) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($barang['kode_inventaris']); ?></td>
                            <td><?= htmlspecialchars($barang['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($barang['nama_jenis']); ?></td>
                            <td><?= htmlspecialchars($barang['jumlah_total']); ?></td>
                            <td><?= htmlspecialchars($barang['jumlah_tersedia']); ?></td>
                            <td><?= htmlspecialchars($barang['status_kondisi']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>