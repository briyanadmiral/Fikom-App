<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Laporan Peminjaman</h6></div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL; ?>/laporan/peminjaman">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="tgl_mulai" class="form-label">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= $_POST['tgl_mulai'] ?? date('Y-m-01'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="tgl_akhir" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" class="form-control" value="<?= $_POST['tgl_akhir'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="submit_peminjaman" class="btn btn-primary">Tampilkan Laporan</button>
                    </div>
                </div>
            </form>

            <?php if (isset($data['laporan_peminjaman'])): ?>
            <hr>
            <h6 class="mt-4">Hasil Laporan Peminjaman (<?= date('d M Y', strtotime($_POST['tgl_mulai'])); ?> - <?= date('d M Y', strtotime($_POST['tgl_akhir'])); ?>)</h6>
            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Status</th>
                            <th>Keterangan Kembali</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['laporan_peminjaman'])): ?>
                            <tr><td colspan="6" class="text-center">Tidak ada data peminjaman pada rentang tanggal ini.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($data['laporan_peminjaman'] as $pinjam): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($pinjam['email_peminjam']); ?></td>
                                <td><?= htmlspecialchars($pinjam['nama_barang']); ?></td>
                                <td><?= date('d M Y', strtotime($pinjam['tgl_pinjam'])); ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($pinjam['status']); ?></span></td>
                                <td><?= htmlspecialchars($pinjam['catatan_kembali']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Statistik Barang Terpopuler</h6></div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL; ?>/laporan/peminjaman">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Pilih Bulan</label>
                        <select name="bulan" class="form-select" required>
                            <?php 
                            $bulan_unik = [];
                            foreach ($data['periode_tersedia'] as $periode) {
                                $bulan_unik[$periode['bulan']] = date('F', mktime(0,0,0, $periode['bulan'], 1));
                            }
                            ksort($bulan_unik);
                            ?>
                            <?php foreach ($bulan_unik as $nomor => $nama): ?>
                                <option value="<?= $nomor; ?>" <?= (($data['filter_statistik']['bulan'] ?? date('n')) == $nomor) ? 'selected' : ''; ?>>
                                    <?= $nama; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tahun" class="form-label">Pilih Tahun</label>
                        <select name="tahun" class="form-select" required>
                             <?php 
                             $tahun_unik = array_unique(array_column($data['periode_tersedia'], 'tahun'));
                             ?>
                             <?php foreach ($tahun_unik as $tahun): ?>
                                <option value="<?= $tahun; ?>" <?= (($data['filter_statistik']['tahun'] ?? date('Y')) == $tahun) ? 'selected' : ''; ?>>
                                    <?= $tahun; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="submit_statistik" class="btn btn-primary">Tampilkan Statistik</button>
                    </div>
                </div>
            </form>

            <?php if (isset($data['statistik_barang'])): ?>
            <hr>
            <h6 class="mt-4">Hasil Statistik: 5 Barang Paling Sering Dipinjam</h6>
            <div class="table-responsive mt-3">
                 <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Nama Barang</th>
                            <th>Total Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['statistik_barang'])): ?>
                             <tr><td colspan="3" class="text-center">Tidak ada data peminjaman pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach($data['statistik_barang'] as $stat): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($stat['nama_barang']); ?></td>
                                <td><?= $stat['total_dipinjam']; ?> kali</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>