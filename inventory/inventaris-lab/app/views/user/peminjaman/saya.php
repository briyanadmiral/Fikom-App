<div class="container-fluid">
    <?php Flasher::flash(); ?>
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Status dan Riwayat Peminjaman Barang Anda</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($data['peminjaman'] as $pinjam) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($pinjam['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($pinjam['jumlah']); ?></td>
                            <td><?= date('d M Y', strtotime($pinjam['tgl_pinjam'])); ?></td>
                            <td><?= date('d M Y', strtotime($pinjam['tgl_kembali'])); ?></td>
                            <td>
                                <?php
                                    $status = $pinjam['status'];
                                    $badge_class = 'bg-secondary'; // Default
                                    if ($status == 'Diajukan') {
                                        $badge_class = 'bg-warning text-dark';
                                    } elseif ($status == 'Disetujui') {
                                        $badge_class = 'bg-success';
                                    } elseif ($status == 'Ditolak') {
                                        $badge_class = 'bg-danger';
                                    } elseif ($status == 'Selesai') {
                                        $badge_class = 'bg-info';
                                    }
                                ?>
                                <span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span>
                            </td>
                            <td>
                                <?php if(!empty($pinjam['catatan_pinjam'])): ?>
                                    <small><strong>Awal:</strong> <?= htmlspecialchars($pinjam['catatan_pinjam']); ?></small><br>
                                <?php endif; ?>
                                <?php if(!empty($pinjam['catatan_kembali'])): ?>
                                    <small><strong>Akhir:</strong> <?= htmlspecialchars($pinjam['catatan_kembali']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pinjam['status'] == 'Disetujui'): ?>
                                    <span class="fw-bold text-success">SEDANG DIPINJAM</span>
                                <?php elseif ($pinjam['status'] == 'Diajukan'): ?>
                                    <span class="text-muted">Menunggu ACC</span>
                                <?php else: ?>
                                    -
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