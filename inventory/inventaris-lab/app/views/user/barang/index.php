<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Daftar Barang yang Dapat Dipinjam
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama Barang</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($data['barang'] as $barang) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if (!empty($barang['foto_barang'])): ?>
                                    <img src="<?= BASE_URL; ?>/assets/uploads/barang/<?= htmlspecialchars($barang['foto_barang']); ?>" alt="Foto" width="70" class="img-thumbnail">
                                <?php else: ?>
                                    <div class="d-flex justify-content-center align-items-center bg-light text-secondary" style="width: 70px; height: 50px;"><i class="bi bi-image-alt fs-4"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($barang['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($barang['deskripsi']); ?></td>
                            <td><?= htmlspecialchars($barang['jumlah_tersedia']); ?></td>
                            
                            <td>
                                <?php if ($barang['jumlah_tersedia'] > 0): ?>
                                    <button class="btn btn-sm btn-primary tombol-pinjam" 
                                       data-bs-toggle="modal" data-bs-target="#pinjamModal"
                                       data-id="<?= $barang['id_barang']; ?>"
                                       data-nama="<?= htmlspecialchars($barang['nama_barang']); ?>"
                                       data-tersedia="<?= $barang['jumlah_tersedia']; ?>">
                                       Pinjam
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Stok Habis</button>
                                <?php endif; ?>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pinjamModal" tabindex="-1" aria-labelledby="pinjamModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pinjamModalLabel">Form Peminjaman Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL; ?>/peminjaman/ajukan" method="post">
        <div class="modal-body">
            <input type="hidden" name="id_barang" id="modal-id-barang">
            <div class="mb-3">
                <label class="form-label">Nama Barang</label>
                <input type="text" class="form-control" id="modal-nama-barang" readonly>
            </div>
            <div class="mb-3">
                <label for="no_telp" class="form-label">Nomor Telepon Aktif (WA)</label>
                <input type="tel" class="form-control" name="no_telp" placeholder="Contoh: 08123456789" 
                    required 
                    pattern="[0-9]{10,12}" 
                    title="Nomor telepon harus terdiri dari 10 hingga 12 digit angka.">
            </div>
            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah Pinjam</label>
                <input type="number" class="form-control" name="jumlah" id="modal-jumlah-pinjam" required min="1">
                <div class="form-text">Tersedia: <span id="modal-jumlah-tersedia"></span></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tgl_pinjam" class="form-label">Tanggal Pinjam</label>
                        <input type="date" class="form-control" name="tgl_pinjam" id="modal-tgl-pinjam" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tgl_kembali" class="form-label">Tanggal Pengembalian</label>
                        <input type="date" class="form-control" name="tgl_kembali" id="modal-tgl-kembali" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Ajukan Peminjaman</button>
        </div>
      </form>
    </div>
  </div>
</div>