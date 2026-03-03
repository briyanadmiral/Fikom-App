<div class="container-fluid">
    <?php Flasher::flash(); ?>
    <div class="card">
        <div class="card-header">
            Daftar Pengajuan Peminjaman Menunggu Persetujuan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Nama Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['peminjaman'] as $pinjam) : ?>
                        <tr>
                            <td><?= htmlspecialchars($pinjam['email_peminjam']); ?></td>
                            <td><?= htmlspecialchars($pinjam['nama_barang']); ?></td>
                            <td><?= date('d M Y', strtotime($pinjam['tgl_pinjam'])); ?></td>
                            <td><?= date('d M Y', strtotime($pinjam['tgl_kembali'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success tombol-setujui" data-bs-toggle="modal" data-bs-target="#setujuiModal" data-id="<?= $pinjam['id_peminjaman']; ?>">
                                        Setujui
                                </button>                                
                                <button type="button" class="btn btn-sm btn-warning tombol-ubah-tanggal" 
                                data-bs-toggle="modal" 
                                data-bs-target="#ubahTanggalModal" 
                                data-id="<?= $pinjam['id_peminjaman']; ?>" 
                                data-tgl="<?= $pinjam['tgl_kembali']; ?>"
                                data-tglpinjam="<?= $pinjam['tgl_pinjam']; ?>"> Ubah Tanggal
                                </button>

                                <a href="<?= BASE_URL; ?>/peminjaman/tolak/<?= $pinjam['id_peminjaman']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Anda yakin ingin menolak peminjaman ini?');">Tolak</a>                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ubahTanggalModal" tabindex="-1" aria-labelledby="ubahTanggalModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ubahTanggalModalLabel">Ubah Tanggal Pengembalian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL; ?>/peminjaman/updateTanggal" method="post">
        <div class="modal-body">
            <input type="hidden" name="id_peminjaman" id="modal-id-peminjaman-tanggal">
            <div class="mb-3">
                <label for="tgl_kembali_baru" class="form-label">Tanggal Pengembalian Baru</label>
                <input type="date" class="form-control" name="tgl_kembali_baru" id="modal-tgl-kembali-baru" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="setujuiModal" tabindex="-1" aria-labelledby="setujuiModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="setujuiModalLabel">Setujui Peminjaman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL; ?>/peminjaman/setujui" method="post">
        <div class="modal-body">
            <input type="hidden" name="id_peminjaman" id="modal-id-setujui">
            <div class="mb-3">
                <label for="catatan_pinjam" class="form-label">Catatan Kondisi Awal (Opsional)</label>
                <textarea class="form-control" name="catatan_pinjam" rows="3" placeholder="Contoh: Barang dalam kondisi baik, ada sedikit goresan di bodi."></textarea>
            </div>
            <p>Dengan menekan "Setuju", stok barang akan dikurangi.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Ya, Setuju</button>
        </div>
      </form>
    </div>
  </div>
</div>