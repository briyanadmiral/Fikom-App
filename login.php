<?php
session_start();
require __DIR__ . '/config.php';  // sudah load dotenv dan autoload

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);

$client->setRedirectUri('http://localhost/fikomapp/login.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $_SESSION['logged_in'] = true;
    header('Location: index.php');
    exit();
}

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $_SESSION['access_token'] = $token;

    // Ambil data user dari Google
    $google_oauth       = new Google_Service_Oauth2($client);
    $google_account     = $google_oauth->userinfo->get();
    $email              = $google_account->email;
    $name               = $google_account->name;
    $picture            = $google_account->picture;

    // simpan ke session basic info dulu
    $_SESSION['user_email']   = $email;
    $_SESSION['user_name']    = $name;
    $_SESSION['user_picture'] = $picture;

    // pisah prefix dan domain
    $parts   = explode('@', $email);
    $prefix  = $parts[0];               // contoh 23n10011
    $domain  = $parts[1];               // student.unika.ac.id atau unika.ac.id

   /* ------------- CEK ROLE ------------- */
$role    = 'user';
$program = null;   // khusus mahasiswa

// cek dulu superadmin
if ($email === '23g40012@student.unika.ac.id') {
    $role = 'superadmin';

    $_SESSION['role']         = $role;
    $_SESSION['logged_in']    = true;
    $_SESSION['user_email']   = $email;
    $_SESSION['user_name']    = $name;
    $_SESSION['user_picture'] = $picture;

    // langsung redirect ke halaman superadmin
    header("Location: superadmin/superadmin_home.php");
    exit();
}elseif (strpos($domain, 'unika.ac.id') !== false) {
    include 'koneksi.php';  // pastikan sudah ada koneksi

    // cek apakah email dosen ada di tabel dosen
    $check = mysqli_query($conn, "SELECT * FROM dosen WHERE email = '$email' LIMIT 1");

    if (mysqli_num_rows($check) > 0) {
        // email ditemukan di tabel dosen → valid
        $role = 'dosen';
    } else {
        // email tidak ada di tabel dosen → tidak boleh masuk
        header("Location: logout.php");
        exit();
    }
}elseif (strpos($domain, 'student.unika.ac.id') !== false) {

    // cek program SIEGA => n1,n2,g4,n4   dan teknik informatika => k1,k2,k3,k4,k5
    $kode = substr($prefix, 2, 2);  // ambil 2 huruf setelah 23 => contoh "n1"  atau "k4"

    // daftar prefix untuk siega
    $siega = ['n1','n2','g4','n4'];
    // daftar prefix untuk informatika
    $informatika = ['k1','k2','k3','k4','k5'];

    if (in_array($kode, $siega)) {
        $role    = 'mahasiswa';
        $program = 'siega';
        $_SESSION['nim']     = $prefix;
        $_SESSION['program'] = $program;

    } elseif (in_array($kode, $informatika)) {
        $role    = 'mahasiswa';
        $program = 'informatika';
        $_SESSION['nim']     = $prefix;
        $_SESSION['program'] = $program;
    }

    else {
        // selain kode di atas dianggap bukan user yang dikehendaki, maka logout paksa
        header("Location: logout.php");
        exit();
    }

} else {
    // jika bukan superadmin, mahasiswa, atau dosen
    header("Location: logout.php");
    exit();
}


    // role + flag login
    $_SESSION['role']      = $role;
    $_SESSION['logged_in'] = true;

    include 'koneksi.php';

    // simpan ke history
    $ip  = $_SERVER['REMOTE_ADDR'];
    $nim = $_SESSION['nim'] ?? '-';

    // Gunakan upsert agar tidak menumpuk data
    $sql = "INSERT INTO history_login (email, nama, role, nim, ip_address)
            VALUES ('$email', '$name', '$role', '$nim', '$ip')
            ON DUPLICATE KEY UPDATE
                nama = VALUES(nama),
                role = VALUES(role),
                nim  = VALUES(nim),
                ip_address = VALUES(ip_address)";

    // Eksekusi query
    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit();

}


$loginUrl = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FIKOM UNIKA Soegijapranata</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #fecb00; /* Warna kuning dari logo UNIKA */
            --dark-color: #2c3e50;
            --light-color: #ffffff;
            --text-color: #555;
            --body-bg: #f4f7fc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--body-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-wrapper {
            display: flex;
            width: 900px;
            max-width: 90%;
            min-height: 550px;
            background-color: var(--light-color);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Styling Sisi Kiri (Branding) */
        .login-branding {
            flex-basis: 45%;
            background: url('assets/img/bg-campus.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
        }

        .login-branding::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(44, 62, 80, 0.4));
        }

        .branding-content {
            position: relative;
            z-index: 1;
        }

        .branding-content .unika-logo {
            width: 80px;
            margin-bottom: 20px;
        }

        .branding-content h1 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.3;
        }

        .branding-content p {
            font-size: 15px;
            margin-top: 10px;
            opacity: 0.9;
        }

        /* Styling Sisi Kanan (Form Login) */
        .login-form {
            flex-basis: 55%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form .fikom-logo {
            max-width: 250px;
            margin-bottom: 20px;
            align-self: center;
        }

        .login-form h2 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 10px;
        }

        .login-form .subtitle {
            text-align: center;
            color: var(--text-color);
            margin-bottom: 40px;
            font-size: 15px;
        }

        .google-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px;
            background-color: #4285F4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            gap: 10px;
        }

        .google-login-btn:hover {
            background-color: #357ae8;
        }
        
        .google-login-btn .fab {
            font-size: 20px;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #999;
        }
        
        /* Responsif untuk Mobile */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                min-height: auto;
                width: 100%;
                max-width: 400px;
            }
            .login-branding {
                min-height: 250px;
                flex-basis: auto;
            }
            .login-form {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-branding">
        <div class="branding-content">
            <img src="assets/img/Lambang-Universitas-Katolik-Soegijapranata-Semarang.png" alt="Logo UNIKA" class="unika-logo">
            <h1>Portal Terintegrasi</h1>
            <p>Satu akses untuk semua layanan akademik Fakultas Ilmu Komputer UNIKA Soegijapranata.</p>
        </div>
    </div>

    <div class="login-form">
        <img src="assets/img/fikom.png" alt="Logo FIKOM" class="fikom-logo">
        
        <h2>Selamat Datang!</h2>
        <p class="subtitle">Silakan masuk menggunakan akun email institusi Anda.</p>
        
        <a href="<?= htmlspecialchars($loginUrl); ?>" class="google-login-btn">
            <i class="fab fa-google"></i>
            <span>Masuk dengan Akun Google Kampus</span>
        </a>

        <div class="login-footer">
            &copy; <?php echo date("Y"); ?> Fakultas Ilmu Komputer, UNIKA Soegijapranata.
        </div>
    </div>
</div>

</body>
</html>
