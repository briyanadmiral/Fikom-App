<?php
session_start();

if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 1;
} else {
    $_SESSION['test_counter']++;
}

echo "<div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
echo "<h2 style='color: #2c3e50;'>🧪 PHP Session Diagnostic Test</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Counter:</strong> <span style='font-size: 1.5em; font-weight: bold; color: #e74c3c;'>" . $_SESSION['test_counter'] . "</span></p>";
echo "<hr style='border: 0; border-top: 1px dashed #eee;'>";
echo "<p style='color: #7f8c8d; font-size: 0.95em; line-height: 1.5;'>";
echo "Silakan <strong>Refresh / Muat Ulang</strong> halaman ini.<br><br>";
echo "• Jika <strong>Counter bertambah</strong> (menjadi 2, 3, dst.), berarti PHP Session di hosting Anda berfungsi normal.<br>";
echo "• Jika <strong>Counter tetap di angka 1</strong> setelah di-refresh, berarti konfigurasi PHP Session atau penyimpanan cookies di hosting Anda bermasalah (misalnya folder session temp di hosting tidak writable atau masalah SSL cookie).";
echo "</p>";
echo "</div>";
