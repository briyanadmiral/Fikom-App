<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    Tambah Jenis Barang Baru
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL; ?>/jenisbarang/store" method="post">
                        <div class="mb-3">
                            <label for="nama_jenis" class="form-label">Nama Jenis</label>
                            <input type="text" class="form-control" name="nama_jenis" required>
                        </div>
                        <div class="mb-3">
                            <label for="kode_jenis" class="form-label">Kode Jenis (Maks. 10 Karakter)</label>
                            <input type="text" class="form-control" name="kode_jenis" maxlength="10" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <?php Flasher::flash(); ?>
            <div class="card">
                <div class="card-header">
                    Daftar Jenis Barang
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jenis</th>
                                <th>Kode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; foreach($data['jenis_barang'] as $jenis): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($jenis['nama_jenis']); ?></td>
                                <td><?= htmlspecialchars($jenis['kode_jenis']); ?></td>
                                <td>
                                    <a href="<?= BASE_URL; ?>/jenisbarang/destroy/<?= $jenis['id_jenis']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>