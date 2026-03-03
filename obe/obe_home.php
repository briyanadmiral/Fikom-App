<?php
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: index.php'); exit;
}

$name  = $_SESSION['user_name'];

// contoh penggunaan session spesifik
/*
   if(isset($_SESSION['mahasiswa_siega'])) { ... }
   if(isset($_SESSION['admin_siega']))    { ... }
*/

?>
<!DOCTYPE html>
<html>
<head>
   <title>obe - Home</title>
</head>
<body>
    <h2>Selamat datang di sistem OBE, <?php echo $name; ?></h2>

    <!-- kamu bisa buat menu khusus berdasarkan role disini -->
    <?php if(isset($_SESSION['mahasiswa_siega'])): ?>
       <p>Fitur Mahasiswa Siega</p>
    <?php endif; ?>

    <?php if(isset($_SESSION['admin_siega'])): ?>
       <p>Fitur Admin Siega</p>
    <?php endif; ?>

</body>
</html>
