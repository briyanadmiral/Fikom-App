<?php
$koneksi = mysqli_connect('localhost', 'root', '', 'fikomapp') 
    or die ('Koneksi database gagal');

// cek apakah id_dosen ada di parameter
if (!isset($_GET['id_dosen'])) {
    echo "
    <script>
        alert('ID Dosen tidak ditemukan!');
        window.location.href='superadmin_home.php';
    </script>";
    exit;
}

$id_dosen = $_GET['id_dosen'];

// ambil data dosen berdasarkan id
$stmt = mysqli_prepare($koneksi, "SELECT * FROM dosen WHERE id_dosen = ?");
mysqli_stmt_bind_param($stmt, "i", $id_dosen);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dosen = mysqli_fetch_assoc($result);

if (!$dosen) {
    echo "
    <script>
        alert('Data dosen tidak ditemukan!');
        window.location.href='superadmin_home.php';
    </script>";
    exit;
}

// proses update data
if (isset($_POST['submit'])) {
    $nip     = $_POST['nip'];
    $nama    = $_POST['nama'];
    $jurusan = $_POST['jurusan'];
    $email   = $_POST['email'];

    $update = mysqli_prepare($koneksi, 
        "UPDATE dosen SET nip = ?, nama = ?, jurusan = ?, email = ? WHERE id_dosen = ?");
    mysqli_stmt_bind_param($update, "ssssi", $nip, $nama, $jurusan, $email, $id_dosen);
    mysqli_stmt_execute($update);

    if (mysqli_stmt_affected_rows($update) > 0) {
        echo "
        <script>
            alert('✅ Data berhasil diupdate');
            window.location.href='superadmin_home.php';
        </script>";
    } else {
        echo "
        <script>
            alert('❌ Data gagal diupdate atau tidak ada perubahan');
            window.location.href='superadmin_home.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Data Dosen</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    header {
      background: #2c3e50;
      color: white;
      padding: 15px 30px;
      text-align: center;
    }
    .container {
      padding: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .card {
      background: white;
      padding: 25px;
      border-radius: 10px;
      width: 400px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .card h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #3498db;
    }
    label {
      font-weight: bold;
      display: block;
      margin-top: 10px;
      margin-bottom: 5px;
    }
    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 15px;
    }
    .btn {
      padding: 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }
    .btn-submit {
      background: #27ae60;
      color: white;
      font-weight: bold;
    }
    .btn-back {
      background: #e74c3c;
      color: white;
      margin-top: 10px;
    }
    .btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <header>
    <h1>Dashboard Superadmin</h1>
    <p>Edit Data Dosen</p>
  </header>

  <div class="container">
    <div class="card">
      <h2>Form Edit Dosen</h2>
      <form action="" method="post">
        <label for="id_dosen">ID Dosen</label>
        <input type="text" name="id_dosen" id="id_dosen" 
               value="<?= htmlspecialchars($dosen['id_dosen']); ?>" readonly>

        <label for="nip">NIP</label>
        <input type="text" name="nip" id="nip" 
               value="<?= htmlspecialchars($dosen['nip']); ?>" required>

        <label for="nama">Nama</label>
        <input type="text" name="nama" id="nama" 
               value="<?= htmlspecialchars($dosen['nama']); ?>" required>

        <label for="jurusan">Jurusan</label>
        <input type="text" name="jurusan" id="jurusan" 
               value="<?= htmlspecialchars($dosen['jurusan']); ?>">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" 
               value="<?= htmlspecialchars($dosen['email']); ?>" required>

        <button type="submit" name="submit" class="btn btn-submit">💾 Simpan Perubahan</button>
        <a href="superadmin_home.php"><button type="button" class="btn btn-back">⬅ Kembali</button></a>
      </form>
    </div>
  </div>
</body>
</html>
