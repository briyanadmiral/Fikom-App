<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0 d-none d-lg-block">Daftar Barang Inventaris</h4>
        
        <div class="ms-auto">
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-excel-fill me-2"></i>Import Excel
            </button>
            <a href="<?= BASE_URL; ?>/barang/tambah" class="btn btn-primary">
                <i class="bi bi-plus-circle-fill me-2"></i>Tambah Barang Baru
            </a>
        </div>

    </div>
    <hr/>
    
    <?php Flasher::flash(); ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Kode Inventaris</th>
                            <th>Nama Barang</th>
                            <th>Total</th>
                            <th>Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($data['barang'])): ?>
                          <tr>
                              <td colspan="7" class="text-center p-5">
                                  <img src="https://i.imgur.com/OC9yZ8Q.png" style="width: 150px; opacity: 0.7;">
                                  <h5 class="mt-3">Belum Ada Data Barang</h5>
                                  <p class="text-muted">Silakan tambahkan barang baru untuk memulai.</p>
                                  <a href="<?= BASE_URL; ?>/barang/tambah" class="btn btn-primary mt-2">
                                      <i class="bi bi-plus-circle-fill me-2"></i>Tambah Barang Baru
                                  </a>
                              </td>
                          </tr>
                      <?php else: ?>
                          <?php $no = 1; foreach ($data['barang'] as $barang) : ?>
                              <tr>
                                  <td><?= $no++; ?></td>
                                  <td>
                                      <?php if (!empty($barang['foto_barang'])): ?>
                                          <img src="<?= BASE__URL; ?>/assets/uploads/barang/<?= htmlspecialchars($barang['foto_barang']); ?>" alt="Foto" width="70" class="img-thumbnail">
                                      <?php else: ?>
                                          <div class="d-flex justify-content-center align-items-center bg-light text-secondary" style="width: 70px; height: 50px;">
                                              <i class="bi bi-image-alt fs-4"></i>
                                          </div>
                                      <?php endif; ?>
                                  </td>
                                  <td><?= htmlspecialchars($barang['kode_inventaris']); ?></td>
                                  <td><?= htmlspecialchars($barang['nama_barang']); ?></td>
                                  <td class="text-center"><strong><?= $barang['jumlah_total']; ?></strong></td>
                                  <td class="text-center"><strong><?= $barang['jumlah_tersedia']; ?></strong></td>
                                  <td>
                                      <div class="btn-group" role="group">
                                          <a href="#" class="btn btn-sm btn-info tombol-detail" 
                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                            data-kode="<?= htmlspecialchars($barang['kode_inventaris']); ?>"
                                            data-nama="<?= htmlspecialchars($barang['nama_barang']); ?>"
                                            data-jenis="<?= htmlspecialchars($barang['nama_jenis']); ?>"
                                            data-deskripsi="<?= htmlspecialchars($barang['deskripsi']); ?>"
                                            data-foto="<?= htmlspecialchars($barang['foto_barang']); ?>"
                                            data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="bi bi-eye-fill"></i>
                                          </a>
                                          <a href="<?= BASE_URL; ?>/barang/edit/<?= $barang['id_barang']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit Detail Barang">
                                              <i class="bi bi-pencil-fill"></i>
                                          </a>
                                          <button type="button" class="btn btn-sm btn-primary tombol-ubah-stok" data-bs-toggle="modal" data-bs-target="#ubahStokModal" data-id="<?= $barang['id_barang']; ?>" data-nama="<?= htmlspecialchars($barang['nama_barang']); ?>" data-total="<?= $barang['jumlah_total']; ?>" data-bs-toggle="tooltip" title="Ubah Stok">
                                              <i class="bi bi-box-fill"></i>
                                          </button>
                                          <a href="<?= BASE_URL; ?>/barang/destroy/<?= $barang['id_barang']; ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus Barang" onclick="return confirm('Yakin?');">
                                              <i class="bi bi-trash-fill"></i>
                                          </a>
                                          <a href="<?= BASE_URL; ?>/barang/cetakQR/<?= $barang['id_barang']; ?>" class="btn btn-sm btn-secondary" target="_blank" data-bs-toggle="tooltip" title="Cetak QR Code">
                                              <i class="bi bi-qr-code"></i>
                                          </a>
                                      </div>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">Detail Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-4">
                <img id="detail-foto" src="" class="img-fluid rounded mb-3" alt="Foto Barang">
            </div>
            <div class="col-md-8">
                <table class="table table-striped">
                    <tr><th>Kode Inventaris</th><td id="detail-kode"></td></tr>
                    <tr><th>Nama Barang</th><td id="detail-nama"></td></tr>
                    <tr><th>Jenis Barang</th><td id="detail-jenis"></td></tr>
                    <tr><th>Deskripsi</th><td id="detail-deskripsi"></td></tr>
                </table>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ubahStokModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ubah Stok: <span id="nama-barang-stok"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= BASE_URL; ?>/barang/ubahStok" method="post">
        <div class="modal-body">
            <input type="hidden" name="id_barang" id="id-barang-stok">
            <div class="mb-3">
                <label>Jumlah Total Saat Ini</label>
                <input type="number" class="form-control" id="jumlah-total-lama" readonly>
            </div>
            <div class="mb-3">
                <label for="jumlah_total_baru" class="form-label">Jumlah Total Baru</label>
                <input type="number" class="form-control" name="jumlah_total_baru" required min="0">
            </div>
             <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan Perubahan</label>
                <input type="text" class="form-control" name="keterangan" placeholder="Contoh: Penambahan stok baru" required>
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

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Import Data Barang dari Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL; ?>/barang/uploadExcel" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <p>Silakan unduh template di bawah ini dan isi sesuai format yang ditentukan.</p>
          
          <a href="<?= BASE_URL; ?>/barang/downloadTemplate" class="btn btn-link px-0 mb-3">
            <i class="bi bi-download me-1"></i> Unduh Template Excel
          </a>
          <hr>
          
          <div class="mb-3">
            <label for="excelFile" class="form-label">Pilih file Excel (.xlsx) untuk diunggah</label>
            <input class="form-control" type="file" name="excelFile" id="excelFile" accept=".xlsx" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Upload dan Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>