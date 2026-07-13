<?php
header('Content-Type: text/plain');

require_once __DIR__ . '/inventory/inventaris-lab/config/config.php';
require_once __DIR__ . '/inventory/inventaris-lab/app/core/Database.php';

$db = new Database();

// Ambil data barang untuk id_prodi = 1
$db->query("
    SELECT barang.*, jenis_barang.nama_jenis 
    FROM barang
    JOIN jenis_barang ON barang.id_jenis = jenis_barang.id_jenis
    WHERE barang.id_prodi = 1 AND barang.deleted_at IS NULL
");
$barang_list = $db->resultSet();

echo "Raw data retrieved:\n";
print_r($barang_list);

echo "\nRendered HTML table rows:\n";
$no = 1;
foreach ($barang_list as $barang) {
    echo "<tr>\n";
    echo "  <td>" . $no++ . "</td>\n";
    echo "  <td>" . (empty($barang['foto_barang']) ? '[No Photo]' : '[Photo: ' . $barang['foto_barang'] . ']') . "</td>\n";
    echo "  <td>" . htmlspecialchars($barang['kode_inventaris']) . "</td>\n";
    echo "  <td>" . htmlspecialchars($barang['nama_barang']) . "</td>\n";
    echo "  <td class='text-center'><strong>" . $barang['jumlah_total'] . "</strong></td>\n";
    echo "  <td class='text-center'><strong>" . $barang['jumlah_tersedia'] . "</strong></td>\n";
    echo "  <td>[Action buttons]</td>\n";
    echo "</tr>\n";
}
