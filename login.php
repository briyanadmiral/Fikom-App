<?php
session_start();
require __DIR__ . '/config.php';  // load dotenv dan autoload

$googleClientId = trim($_ENV['GOOGLE_CLIENT_ID'] ?? '');
$appEnv = strtolower(trim($_ENV['APP_ENV'] ?? 'production'));
$appBaseUrl = rtrim($_ENV['APP_BASE_URL'] ?? 'http://localhost/Fikom-App', '/');
$devLoginEnabled = filter_var(
    $_ENV['DEV_LOGIN_ENABLED'] ?? ($appEnv === 'local' ? 'true' : 'false'),
    FILTER_VALIDATE_BOOL
);

$client = class_exists('Google_Client') ? new Google_Client() : null;
if ($client && $googleClientId !== '') {
    $client->setClientId($googleClientId);
}

// 1. Cek jika sudah login
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit();
}

// 2. Proses callback dari Google Identity Services (GIS)
if ($devLoginEnabled && isset($_POST['dev_login_account'])) {
    $account = $_POST['dev_login_account'];

    $devAccounts = [
        'superadmin' => [
            'email' => 'briyanadmiral@gmail.com',
            'name' => 'Superadmin Local',
            'picture' => 'https://ui-avatars.com/api/?name=Superadmin+Local&background=8a9ccc&color=fff',
            'role' => 'superadmin',
            'program' => null,
        ],
        'dosen' => [
            'email' => 'dev.dosen@unika.ac.id',
            'name' => 'Dosen Local',
            'picture' => 'https://ui-avatars.com/api/?name=Dosen+Local&background=8a9ccc&color=fff',
            'role' => 'dosen',
            'program' => null,
        ],
        'mahasiswa_siega' => [
            'email' => '23n10001@student.unika.ac.id',
            'name' => 'Mahasiswa SIEGA Local',
            'picture' => 'https://ui-avatars.com/api/?name=Mahasiswa+SIEGA&background=8a9ccc&color=fff',
            'role' => 'mahasiswa',
            'program' => 'siega',
            'nim' => '23n10001',
        ],
    ];

    if (! isset($devAccounts[$account])) {
        header("Location: login.php?error=invalid_dev_account");
        exit();
    }

    $selected = $devAccounts[$account];

    $_SESSION['role'] = $selected['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['user_email'] = $selected['email'];
    $_SESSION['user_name'] = $selected['name'];
    $_SESSION['user_picture'] = $selected['picture'];

    if (! empty($selected['program'])) {
        $_SESSION['program'] = $selected['program'];
    }

    if (! empty($selected['nim'])) {
        $_SESSION['nim'] = $selected['nim'];
    }

    header('Location: index.php');
    exit();
}

if (isset($_POST['credential']) && $client && $googleClientId !== '') {
    $token = $client->verifyIdToken($_POST['credential']);
    
    if (!$token) {
        header("Location: login.php?error=invalid_token");
        exit();
    }

    // Ambil data user dari payload JWT
    $email          = $token['email'];
    $name           = $token['name'];
    $picture        = $token['picture'];

    // Pisah prefix dan domain
    $parts   = explode('@', $email);
    $prefix  = $parts[0];               
    $domain  = $parts[1];               

/* ------------- CEK ROLE ------------- */
    $role    = 'user';
    $program = null;

    // Panggil koneksi di awal agar bisa mengecek database sebelum cek domain
    include 'koneksi.php'; 

    // Cek apakah email ini terdaftar di tabel dosen sebagai "Jalur VIP"
    $check_dosen = mysqli_query($conn, "SELECT * FROM dosen WHERE email = '$email' LIMIT 1");
    $is_registered_dosen = (mysqli_num_rows($check_dosen) > 0);

    // A. CEK SUPERADMIN
    if ($email === 'briyanadmiral@gmail.com') {
        $role = 'superadmin';
        $_SESSION['role']         = $role;
        $_SESSION['logged_in']    = true;
        $_SESSION['user_email']   = $email;
        $_SESSION['user_name']    = $name;
        $_SESSION['user_picture'] = $picture;
        header("Location: superadmin/superadmin_home.php");
        exit();
    } 
    
    // B. CEK DOSEN (Prioritas Tabel Database)
    elseif ($is_registered_dosen) {
        // Jika email (apapun domainnya) ada di tabel dosen, langsung lolos!
        $role = 'dosen';
    } 
    
    // C. JIKA BUKAN DOSEN TERDAFTAR, TAPI PAKAI EMAIL @unika.ac.id
    elseif (strpos($domain, 'unika.ac.id') !== false && strpos($domain, 'student') === false) {
        // Berarti dia punya email kampus, tapi belum didaftarkan di sistem oleh Superadmin
        header("Location: logout.php?error=dosen_not_found");
        exit();
    } 
    
    // D. CEK MAHASISWA
    elseif (strpos($domain, 'student.unika.ac.id') !== false) {
        // strtolower supaya NIM huruf besar tetap terbaca (ex: 23N1 -> n1)
        $kode = strtolower(substr($prefix, 2, 2)); 

        $siega = ['n1','n2','g4','n4'];
        $informatika = ['k1','k2','k3','k4','k5'];

        if (in_array($kode, $siega)) {
            $role    = 'mahasiswa';
            $program = 'siega';
        } elseif (in_array($kode, $informatika)) {
            $role    = 'mahasiswa';
            $program = 'informatika';
        } else {
            // Mahasiswa tapi bukan prodi yang diizinkan
            header("Location: logout.php?error=prodi_not_allowed");
            exit();
        }
        
        $_SESSION['nim']     = $prefix;
        $_SESSION['program'] = $program;
    } 
    
    // E. BUKAN EMAIL KAMPUS & BUKAN DOSEN TERDAFTAR
    else {
        // Jika dia pakai email pribadi (Gmail/dll) dan TIDAK ada di tabel dosen
        header("Location: logout.php?error=wrong_domain");
        exit();
    }

    // --- JIKA LOLOS SEMUA CEK DI ATAS ---
    $_SESSION['role']      = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['user_email']   = $email;
    $_SESSION['user_name']    = $name;
    $_SESSION['user_picture'] = $picture;

    // Catat ke history login
    $ip  = $_SERVER['REMOTE_ADDR'];
    $nim_val = $_SESSION['nim'] ?? '-';

    $sql = "INSERT INTO history_login (email, nama, role, nim, ip_address)
            VALUES ('$email', '$name', '$role', '$nim_val', '$ip')
            ON DUPLICATE KEY UPDATE
                nama = VALUES(nama),
                role = VALUES(role),
                nim  = VALUES(nim),
                ip_address = VALUES(ip_address)";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FIKOM UNIKA Soegijapranata</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <style>
        /* === TEMA GLASSMORPHISM (GREY UI/UX) === */
        :root {
            --primary: #8a9ccc; /* Subtle blue/purple accent */
            --primary-soft: rgba(255, 255, 255, 0.5);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec; /* Fallback flat color */
            --bg-card: rgba(255, 255, 255, 0.4);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%);
            background-attachment: fixed;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
        }

        .login-wrapper {
            display: flex;
            width: 950px;
            max-width: 90%;
            min-height: 550px;
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
            position: relative;
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

        /* Gradient overlay menyesuaikan warna Glassmorphism Grey/Subtle Accent */
        .login-branding::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to top, rgba(58, 66, 82, 0.95), rgba(138, 156, 204, 0.4));
        }

        .branding-content {
            position: relative;
            z-index: 1;
        }

        .branding-content .unika-logo {
            width: 80px;
            margin-bottom: 24px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.4);
        }

        .branding-content h1 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .branding-content p {
            font-size: 15px;
            margin-top: 12px;
            color: #e2e8f0; 
            font-weight: 400;
        }

        /* Styling Sisi Kanan (Form) */
        .login-form {
            flex-basis: 55%;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2); /* Memberikan beda layer blur */
        }

        .login-form .fikom-logo {
            max-width: 220px;
            margin-bottom: 24px;
            align-self: center;
        }

        .login-form h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            letter-spacing: -0.02em;
        }

        .login-form .subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-top: 8px;
            margin-bottom: 40px;
            font-size: 15px;
        }



        .login-footer {
            text-align: center;
            margin-top: auto; /* Mendorong footer ke paling bawah container */
            padding-top: 40px;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 400;
        }
        
        /* Responsif untuk Mobile */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                min-height: auto;
                width: 100%;
                max-width: 450px;
            }
            .login-branding {
                min-height: 200px;
                padding: 30px;
            }
            .branding-content .unika-logo {
                width: 60px;
                margin-bottom: 15px;
            }
            .branding-content h1 {
                font-size: 22px;
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

        <?php if ($devLoginEnabled): ?>
        <div style="margin-bottom: 24px; padding: 16px; border-radius: 14px; background: rgba(255,255,255,0.45); border: 1px solid rgba(255,255,255,0.7);">
            <div style="font-weight: 700; color: var(--dark); margin-bottom: 8px;">Login Local Development</div>
            <p style="margin: 0 0 12px 0; color: var(--text-muted); font-size: 14px;">
                Gunakan akun lokal berikut untuk testing tanpa Google GIS.
            </p>
            <form method="POST" style="display: grid; gap: 10px;">
                <button type="submit" name="dev_login_account" value="superadmin" style="padding: 12px 14px; border: 1px solid rgba(255,255,255,0.8); border-radius: 10px; background: rgba(255,255,255,0.8); cursor: pointer; font-weight: 600;">
                    Masuk sebagai Superadmin
                </button>
                <button type="submit" name="dev_login_account" value="dosen" style="padding: 12px 14px; border: 1px solid rgba(255,255,255,0.8); border-radius: 10px; background: rgba(255,255,255,0.8); cursor: pointer; font-weight: 600;">
                    Masuk sebagai Dosen
                </button>
                <button type="submit" name="dev_login_account" value="mahasiswa_siega" style="padding: 12px 14px; border: 1px solid rgba(255,255,255,0.8); border-radius: 10px; background: rgba(255,255,255,0.8); cursor: pointer; font-weight: 600;">
                    Masuk sebagai Mahasiswa SIEGA
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ($googleClientId !== ''): ?>
        <div id="g_id_onload"
             data-client_id="<?= htmlspecialchars($googleClientId) ?>"
             data-context="signin"
             data-ux_mode="popup"
             data-login_uri="<?= htmlspecialchars($appBaseUrl . '/login.php') ?>"
             data-auto_prompt="false">
        </div>

        <div class="g_id_signin"
             data-type="standard"
             data-shape="rectangular"
             data-theme="outline"
             data-text="signin_with_google"
             data-size="large"
             data-logo_alignment="center">
        </div>
        <?php endif; ?>

        <div class="login-footer">
            &copy; <?php echo date("Y"); ?> Fakultas Ilmu Komputer, UNIKA Soegijapranata.
        </div>
    </div>
</div>

</body>
</html>
