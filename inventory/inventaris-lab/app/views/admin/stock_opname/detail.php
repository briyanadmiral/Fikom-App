<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Detail Stock Opname</h5>
                <small>Tanggal: <?= date('d F Y', strtotime($data['header']['tanggal_opname'])); ?> | Oleh: <?= htmlspecialchars($data['header']['dilakukan_oleh']); ?></small>
            </div>

            <?php if ($data['header']['status'] == 'Selesai'): ?>
                <a href="<?= BASE_URL; ?>/stockopname/sesuaikanStok/<?= $data['header']['id_opname']; ?>" class="btn btn-success" onclick="return confirm('PERHATIAN: Aksi ini akan mengubah data stok sistem secara permanen sesuai hasil hitungan fisik. Lanjutkan?');">
                    <i class="bi bi-check-circle-fill me-2"></i>Sesuaikan Stok Sistem
                </a>
            <?php else: ?>
                <span class="badge bg-primary">Stok Sudah Disesuaikan</span>
            <?php endif; ?>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr class="text-center">
                            <th>Nama Barang</th>
                            <th>Jml. Sistem</th>
                            <th>Jml. Fisik</th>
                            <th>Selisih</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['detail'] as $item): ?>
                        <?php
                            $selisih_class = '';
                            if ($item['selisih'] < 0) $selisih_class = 'text-danger fw-bold';
                            if ($item['selisih'] > 0) $selisih_class = 'text-success fw-bold';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                            <td class="text-center"><?= $item['jumlah_sistem']; ?></td>
                            <td class="text-center"><?= $item['jumlah_fisik']; ?></td>
                            <td class="text-center <?= $selisih_class; ?>">
                                <?= ($item['selisih'] > 0) ? '+' : '' ?><?= $item['selisih']; ?>
                            </td>
                            <td><?= htmlspecialchars($item['catatan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>