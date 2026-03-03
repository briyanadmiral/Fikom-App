<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Barang</title>
    <style>
        body { font-family: sans-serif; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Laporan Stok Barang</h1>
    <p>Program Studi: <?= htmlspecialchars($data['prodi']); ?></p>
    <p>Tanggal Cetak: <?= date('d F Y'); ?></p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Inventaris</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Total</th>
                <th>Tersedia</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($data['barang'] as $barang) : ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($barang['kode_inventaris']); ?></td>
                <td><?= htmlspecialchars($barang['nama_barang']); ?></td>
                <td><?= htmlspecialchars($barang['nama_jenis']); ?></td>
                <td><?= htmlspecialchars($barang['jumlah_total']); ?></td>
                <td><?= htmlspecialchars($barang['jumlah_tersedia']); ?></td>
                <td><?= htmlspecialchars($barang['status_kondisi']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>