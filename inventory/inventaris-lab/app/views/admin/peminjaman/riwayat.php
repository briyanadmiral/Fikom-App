<div class="container-fluid">
    <?php Flasher::flash(); ?>
    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Seluruh Riwayat Peminjaman</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="data-table">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Nama Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali Seharusnya</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['peminjaman'] as $pinjam) : ?>
                            <?php
                                // Logika untuk memberi warna pada baris yang telat
                                $row_class = '';
                                $today = strtotime(date('Y-m-d'));
                                $tgl_kembali = strtotime($pinjam['tgl_kembali']);
                                if ($pinjam['status'] == 'Disetujui' && $tgl_kembali < $today) {
                                    $row_class = 'table-danger';
                                }
                            ?>
                            <tr class="<?= $row_class; ?>">
                                <td>
                                    <?= htmlspecialchars($pinjam['email_peminjam']); ?>
                                    
                                    <?php if (!empty($pinjam['no_telp_peminjam'])): ?>
                                        <?php
                                            $no_wa = preg_replace('/^0/', '62', htmlspecialchars($pinjam['no_telp_peminjam']));
                                            $nama_barang = htmlspecialchars($pinjam['nama_barang']);
                                            $tgl_kembali_formatted = date('d F Y', $tgl_kembali);
                                            $pesan = "Halo, kami dari Laboratorium FTDI Unika. Mengingatkan bahwa peminjaman barang '" . $nama_barang . "' Anda dijadwalkan untuk dikembalikan pada tanggal " . $tgl_kembali_formatted . ". Mohon untuk dapat mengembalikan tepat waktu. Terima kasih.";
                                            $pesan_encoded = urlencode($pesan);
                                        ?>
                                        <a href="https://wa.me/<?= $no_wa; ?>?text=<?= $pesan_encoded; ?>" target="_blank" class="btn btn-sm btn-success ms-2" title="Kirim Pengingat WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($pinjam['nama_barang']); ?></td>
                                <td><?= date('d M Y', strtotime($pinjam['tgl_pinjam'])); ?></td>
                                <td><?= date('d M Y', $tgl_kembali); ?></td>
                                <td>
                                    <?php
                                        $status = $pinjam['status'];
                                        $badge_class = 'bg-secondary';
                                        if ($status == 'Diajukan') $badge_class = 'bg-warning text-dark';
                                        elseif ($status == 'Disetujui') $badge_class = 'bg-success';
                                        elseif ($status == 'Ditolak') $badge_class = 'bg-danger';
                                        elseif ($status == 'Selesai') $badge_class = 'bg-info';
                                    ?>
                                    <span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($pinjam['catatan_pinjam'])): ?>
                                        <small><strong>Pinjam:</strong> <?= htmlspecialchars($pinjam['catatan_pinjam']); ?></small><br>
                                    <?php endif; ?>
                                    <?php if(!empty($pinjam['catatan_kembali'])): ?>
                                        <small><strong>Kembali:</strong> <?= htmlspecialchars($pinjam['catatan_kembali']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($pinjam['status'] == 'Disetujui'): ?>
                                        <button type="button" class="btn btn-sm btn-primary tombol-kembali" data-bs-toggle="modal" data-bs-target="#kembaliModal" data-id="<?= $pinjam['id_peminjaman']; ?>">
                                            Konfirmasi Pengembalian
                                        </button>
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

<div class="modal fade" id="kembaliModal" tabindex="-1" aria-labelledby="kembaliModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kembaliModalLabel">Konfirmasi Pengembalian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= BASE_URL; ?>/peminjaman/konfirmasiKembali" method="post">
        <div class="modal-body">
            <input type="hidden" name="id_peminjaman" id="modal-id-kembali">
            <div class="mb-3">
                <label for="catatan_kembali" class="form-label">Catatan Kondisi Akhir (Opsional)</label>
                <textarea class="form-control" name="catatan_kembali" rows="3" placeholder="Contoh: Barang dikembalikan dalam kondisi baik / ada kerusakan."></textarea>
            </div>
             <p>Dengan menekan "Konfirmasi", stok barang akan dikembalikan ke sistem.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Ya, Konfirmasi</button>
        </div>
      </form>
    </div>
  </div>
</div>