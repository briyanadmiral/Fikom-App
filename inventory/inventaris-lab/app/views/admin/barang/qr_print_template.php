<!DOCTYPE html>
<html>
<head>
    <title>Cetak QR</title>
    <style>
        @media print {
            @page {
                size: 58mm 50mm; /* Lebar 58mm, tinggi bisa disesuaikan */
                margin: 2mm;
            }
            body {
                font-family: sans-serif;
                font-size: 8pt;
                text-align: center;
                color: black;
            }
            .qr-code {
                width: 150px; /* Ukuran QR code */
                height: 150px;
                margin: 0 auto;
            }
            p { margin: 2px 0; }
        }
    </style>
</head>
<body onload="window.print();">
    <?php
        require_once '../vendor/autoload.php';
        use Endroid\QrCode\QrCode;
        use Endroid\QrCode\Writer\PngWriter;

        $qrCode = QrCode::create($data['barang']['kode_inventaris'])->setSize(300)->setMargin(10);
        $writer = new PngWriter();
        $qrResult = $writer->write($qrCode);
        $qrCodeUri = $qrResult->getDataUri();
    ?>
    <img src="<?= $qrCodeUri; ?>" class="qr-code">
    <p><strong><?= htmlspecialchars($data['barang']['kode_inventaris']); ?></strong></p>
    <p><?= htmlspecialchars($data['barang']['nama_barang']); ?></p>
</body>
</html>