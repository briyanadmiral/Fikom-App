<div class="container-fluid">
    <div class="card">
        <div class="card-header">Catatan Perubahan Stok Barang</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tabel-log">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nama Barang</th>
                            <th>Aktivitas</th>
                            <th>Perubahan</th>
                            <th>Stok Akhir</th>
                            <th>Keterangan</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['logs'] as $log): ?>
                        <tr>
                            <td></td> <td data-order="<?= strtotime($log['waktu']); ?>">
                                <?= date('d M Y, H:i', strtotime($log['waktu'])); ?>
                            </td>
                            
                            <td><?= htmlspecialchars($log['nama_barang']); ?></td>
                            <td class="fw-bold <?= ($log['jumlah_ubah'] > 0) ? 'text-success' : 'text-danger'; ?>">
                                <?= ($log['jumlah_ubah'] > 0) ? '+' : '' ?><?= $log['jumlah_ubah']; ?>
                            </td>
                            <td><?= $log['stok_akhir']; ?></td>
                            <td><?= htmlspecialchars($log['keterangan']); ?></td>
                            <td><?= htmlspecialchars($log['email_user']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>