<div class="container-fluid">
    <div class="card">
        <div class="card-header">Form Edit Data Barang</div>
        <div class="card-body">
            <form action="<?= BASE_URL; ?>/barang/update" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_barang" value="<?= $data['barang']['id_barang']; ?>">
                <input type="hidden" name="foto_lama" value="<?= $data['barang']['foto_barang']; ?>">

                <div class="mb-3">
                    <label for="kode_inventaris" class="form-label">Kode Inventaris</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="kode_inventaris" id="kode_inventaris_input" value="<?= htmlspecialchars($data['barang']['kode_inventaris']); ?>" readonly required>
                        <button class="btn btn-outline-secondary" type="button" id="tombol_edit_kode">Edit</button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="nama_barang" class="form-label">Nama Barang</label>
                    <input type="text" class="form-control" name="nama_barang" value="<?= htmlspecialchars($data['barang']['nama_barang']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="id_jenis" class="form-label">Jenis Barang</label>
                    <select class="form-select" name="id_jenis" required>
                        <?php foreach($data['jenis_barang'] as $jenis): ?>
                            <option value="<?= $jenis['id_jenis']; ?>" <?= ($jenis['id_jenis'] == $data['barang']['id_jenis']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($jenis['nama_jenis']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label for="jumlah_total" class="form-label">Jumlah Total</label>
                        <p class="form-control-plaintext"><strong><?= $data['barang']['jumlah_total']; ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <label for="jumlah_tersedia" class="form-label">Jumlah Tersedia</label>
                        <p class="form-control-plaintext"><strong><?= $data['barang']['jumlah_tersedia']; ?></strong></p>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label for="status_kondisi" class="form-label">Kondisi Barang</label>
                    <select class="form-select" name="status_kondisi">
                        <option value="Baik" <?= ($data['barang']['status_kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                        <option value="Rusak Ringan" <?= ($data['barang']['status_kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                        <option value="Rusak Berat" <?= ($data['barang']['status_kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($data['barang']['deskripsi']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <p class="mb-1">Foto Saat Ini:</p>
                    <?php if (!empty($data['barang']['foto_barang'])): ?>
                        <img src="<?= BASE_URL; ?>/assets/uploads/barang/<?= htmlspecialchars($data['barang']['foto_barang']); ?>" width="150" class="img-thumbnail mb-2">
                    <?php endif; ?>
                    <label for="foto_barang" class="form-label d-block">Ganti Foto (Opsional)</label>
                    <input class="form-control" type="file" name="foto_barang" accept="image/jpeg, image/png">
                </div>

                <button type="submit" class="btn btn-primary">Update Data</button>
                <a href="<?= BASE_URL; ?>/barang" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tombolEdit = document.getElementById('tombol_edit_kode');
    const inputKode = document.getElementById('kode_inventaris_input');

    tombolEdit.addEventListener('click', function() {
        // Toggle (mengubah bolak-balik) status readonly
        inputKode.readOnly = !inputKode.readOnly;

        if (!inputKode.readOnly) {
            // Jika statusnya sekarang TIDAK readonly (bisa diedit)
            inputKode.focus(); // Langsung fokuskan kursor ke input
            this.textContent = 'Kunci'; // Ubah teks tombol
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-success');
        } else {
            // Jika statusnya sekarang readonly (dikunci)
            this.textContent = 'Edit'; // Kembalikan teks tombol
            this.classList.add('btn-outline-secondary');
            this.classList.remove('btn-success');
        }
    });
});
</script>