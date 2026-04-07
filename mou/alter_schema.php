<?php
include 'koneksi.php';
$sql = "ALTER TABLE mou MODIFY COLUMN tingkat VARCHAR(100)";
if (mysqli_query($conn, $sql)) {
    echo "Schema updated successfully";
} else {
    echo "Error updating schema: " . mysqli_error($conn);
}
?>
