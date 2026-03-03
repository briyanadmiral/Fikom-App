<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Form Tambah Data Barang
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL; ?>/barang/store" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="id_jenis" class="form-label">Jenis Barang</label>
                            <select class="form-select" id="id_jenis" name="id_jenis" required>
                                <option value="" selected disabled>-- Pilih Jenis Barang --</option>
                                <?php foreach($data['jenis_barang'] as $jenis): ?>
                                    <option value="<?= $jenis['id_jenis']; ?>" data-kode="<?= $jenis['kode_jenis']; ?>">
                                        <?= htmlspecialchars($jenis['nama_jenis']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="bulan_perolehan" class="form-label">Bulan Perolehan</label>
                            <input type="number" class="form-control" id="bulan_perolehan" name="bulan_perolehan" placeholder="MM" min="1" max="12" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                         <div class="mb-3">
                            <label for="tahun_perolehan" class="form-label">Tahun Perolehan</label>
                            <input type="number" class="form-control" id="tahun_perolehan" name="tahun_perolehan" placeholder="YYYY" min="2000" max="<?= date('Y'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="kode_inventaris" class="form-label">Kode Inventaris</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="kode_inventaris" name="kode_inventaris" readonly required>
                            <button class="btn btn-outline-secondary" type="button" id="unlock-kode">Edit</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nama_barang" class="form-label">Nama Barang</label>
                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jumlah_total" class="form-label">Jumlah Total</label>
                            <input type="number" class="form-control" id="jumlah_total" name="jumlah_total" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status_kondisi" class="form-label">Kondisi Barang</label>
                            <select class="form-select" id="status_kondisi" name="status_kondisi">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi / Spesifikasi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="foto_barang" class="form-label">Foto Barang (Opsional)</label>
                    <input class="form-control" type="file" id="foto_barang" name="foto_barang" accept="image/jpeg, image/png">
                    <div class="form-text">Format yang diizinkan: JPG, PNG. Ukuran maks: 2MB.</div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="<?= BASE_URL; ?>/barang" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisSelect = document.getElementById('id_jenis');
    const bulanInput = document.getElementById('bulan_perolehan');
    const tahunInput = document.getElementById('tahun_perolehan');
    const kodeInventarisInput = document.getElementById('kode_inventaris');
    const unlockButton = document.getElementById('unlock-kode');
    
    function toRoman(num) {
        const roman = {M:1000,CM:900,D:500,CD:400,C:100,XC:90,L:50,XL:40,X:10,IX:9,V:5,IV:4,I:1};
        let str = '';
        for (let i of Object.keys(roman)) {
            let q = Math.floor(num / roman[i]);
            num -= q * roman[i];
            str += i.repeat(q);
        }
        return str;
    }

    function generateCode() {
        const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];
        const kodeJenis = selectedOption.getAttribute('data-kode');
        const bulan = parseInt(bulanInput.value, 10);
        const tahun = tahunInput.value;

        if (!kodeJenis || !bulan || !tahun || tahun.length < 4) {
            kodeInventarisInput.value = '';
            return;
        }

        const kodeProdi = "<?= $_SESSION['app_user']['id_prodi'] == 1 ? 'SI' : 'TI' ?>";
        const bulanRomawi = toRoman(bulan);
        
        // Placeholder untuk nomor urut. Implementasi ideal menggunakan AJAX.
        const nomorBarang = '001'; 

        const generatedCode = `${kodeJenis}/${kodeProdi}/${bulanRomawi}/${tahun}/${nomorBarang}`;
        kodeInventarisInput.value = generatedCode;
    }

    // Event listeners untuk generate kode otomatis
    jenisSelect.addEventListener('change', generateCode);
    bulanInput.addEventListener('input', generateCode);
    tahunInput.addEventListener('input', generateCode);

    unlockButton.addEventListener('click', function() {
        kodeInventarisInput.readOnly = !kodeInventarisInput.readOnly;
        if (!kodeInventarisInput.readOnly) {
            this.textContent = 'Kunci';
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-success');
            kodeInventarisInput.focus();
        } else {
            this.textContent = 'Edit';
            this.classList.add('btn-outline-secondary');
            this.classList.remove('btn-success');
        }
    });
});
</script>