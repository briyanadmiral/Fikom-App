<?php
include 'koneksi.php';
$result = mysqli_query($conn, "DESCRIBE mou");
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
