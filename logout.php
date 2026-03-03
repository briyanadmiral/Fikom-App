<?php 

session_start();

session_unset();
session_destroy();

header('Location: login.php');
exit();


// session_start();

// require_once 'vendor/autoload.php';

// $access_token = $_SESSION['access_token'];

// $client = new Google_Client();
// $client->revokeToken($access_token);

// session_destroy();
// header('location: tampilanlogin.php');

// session_start();

// // jika masih ada access_token (login via google), revoke dulu
// if (isset($_SESSION['access_token'])) {
//     require_once 'vendor/autoload.php';
//     $client = new Google_Client();
//     $client->setAccessToken($_SESSION['access_token']);
//     $client->revokeToken();
// }

// // hapus semua session
// session_unset();
// session_destroy();

// // redirect kembali ke halaman login
// header('Location: login.php');
// exit();
?>
