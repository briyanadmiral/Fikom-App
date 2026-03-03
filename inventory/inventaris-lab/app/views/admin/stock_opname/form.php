<div class="container-fluid">
    <form action="<?= BASE_URL; ?>/stockopname/simpan" method="post">
        <div class="card">
            <div class="card-header">
                Formulir Stock Opname - <?= date('d M Y'); ?>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Perhatian!</strong> Masukkan jumlah **FISIK** barang yang ada di lab saat ini.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Kode Inventaris</th>
                                <th class="bg-light">Jml. Sistem</th>
                                <th class="bg-primary text-white" style="width: 15%;">Jml. Fisik</th>
                                <th>Catatan (Opsional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['barang'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?= htmlspecialchars($item['kode_inventaris']); ?></td>
                                <td class="bg-light text-center">
                                    <strong><?= $item['jumlah_total']; ?></strong>
                                </td>
                                <td>
                                    <input type="number" name="jumlah_fisik[<?= $item['id_barang']; ?>]" class="form-control" required min="0">
                                    <input type="hidden" name="jumlah_sistem[<?= $item['id_barang']; ?>]" value="<?= $item['jumlah_total']; ?>">
                                </td>
                                <td>
                                    <input type="text" name="catatan[<?= $item['id_barang']; ?>]" class="form-control" placeholder="Kondisi, hilang, dll.">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </div>
            <div class="card-footer text-end">
                <a href="<?= BASE_URL; ?>/stockopname" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin ingin menyimpan hasil stock opname ini?');">Simpan Hasil Opname</button>
            </div>
        </div>
    </form>
</div>