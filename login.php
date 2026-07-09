<?php
session_start();
require __DIR__ . '/config.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit();
}


if (isset($_POST['credential'])) {

    // === STEP 1: Verifikasi Token Google (TERPISAH dari DB) ===
    $id_token = $_POST['credential'];
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        header("Location: login.php?error=invalid_token");
        exit();
    }
    $token = json_decode($response, true);
    if (!$token || isset($token['error_description']) || $token['aud'] !== $_ENV['GOOGLE_CLIENT_ID']) {
        header("Location: login.php?error=invalid_token");
        exit();
    }

    // Token Google VALID — proses selanjutnya
    $email = $token['email'];
    $name = $token['name'];
    $picture = $token['picture'] ?? 'assets/img/default-avatar.png';
    $parts = explode('@', $email);
    $prefix = $parts[0];
    $domain = $parts[1];

    // === STEP 2: Koneksi Database (TERPISAH, tidak mempengaruhi validasi token) ===
    $conn = null;
    try {
        // Nonaktifkan exception mysqli agar tidak crash jika DB error
        mysqli_report(MYSQLI_REPORT_OFF);
        require_once __DIR__ . '/config.php'; // pastikan .env sudah dimuat
        $db_host = $_ENV['DB_HOST'] ?? 'localhost';
        $db_user = $_ENV['DB_USERNAME'] ?? 'root';
        $db_pass = $_ENV['DB_PASSWORD'] ?? '';
        $db_name = $_ENV['DB_DATABASE_APP'] ?? 'fike8938_fikom_app';
        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        if (!$conn) {
            // Catat error DB tapi jangan hentikan alur login
            error_log("[FIKOM LOGIN] DB connection failed: " . mysqli_connect_error());
        }
    } catch (Throwable $db_err) {
        error_log("[FIKOM LOGIN] DB exception: " . $db_err->getMessage());
        $conn = null;
    }

    try {
        $role = 'user';
        $program = null;
        // Koneksi sudah siap di $conn (bisa null jika DB gagal)

        // Cek apakah email ini terdaftar di tabel dosen sebagai "Jalur VIP"
        $is_registered_dosen = false;
        if ($conn) {
            $email_escaped = mysqli_real_escape_string($conn, $email);
            $check_dosen = mysqli_query($conn, "SELECT * FROM dosen WHERE email = '$email_escaped' LIMIT 1");
            if ($check_dosen) {
                $is_registered_dosen = (mysqli_num_rows($check_dosen) > 0);
            }
        }

        $superadmin_emails = ['briyanadmiral@gmail.com', 'magang.si@unika.ac.id'];
        if (in_array($email, $superadmin_emails)) {
            $role = 'superadmin';
            $_SESSION['role'] = $role;
            $_SESSION['logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $name;
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

            $siega = ['n1', 'n2', 'g4', 'n4'];
            $informatika = ['k1', 'k2', 'k3', 'k4', 'k5'];

            if (in_array($kode, $siega)) {
                $role = 'mahasiswa';
                $program = 'siega';
            } elseif (in_array($kode, $informatika)) {
                $role = 'mahasiswa';
                $program = 'informatika';
            } else {
                // Mahasiswa tapi bukan prodi yang diizinkan
                header("Location: logout.php?error=prodi_not_allowed");
                exit();
            }

            $_SESSION['nim'] = $prefix;
            $_SESSION['program'] = $program;
        }

        // E. BUKAN EMAIL KAMPUS & BUKAN DOSEN TERDAFTAR
        else {
            // Jika dia pakai email pribadi (Gmail/dll) dan TIDAK ada di tabel dosen
            header("Location: logout.php?error=wrong_domain");
            exit();
        }

        // --- JIKA LOLOS SEMUA CEK DI ATAS ---
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_picture'] = $picture;

        // Catat ke history login
        if ($conn) {
            $ip = $_SERVER['REMOTE_ADDR'];
            $nim_val = $_SESSION['nim'] ?? '-';
            $email_escaped = mysqli_real_escape_string($conn, $email);
            $name_escaped = mysqli_real_escape_string($conn, $name);
            $role_escaped = mysqli_real_escape_string($conn, $role);
            $nim_escaped = mysqli_real_escape_string($conn, $nim_val);
            $ip_escaped = mysqli_real_escape_string($conn, $ip);

            $sql = "INSERT INTO history_login (email, nama, role, nim, ip_address)
                    VALUES ('$email_escaped', '$name_escaped', '$role_escaped', '$nim_escaped', '$ip_escaped')
                    ON DUPLICATE KEY UPDATE
                        nama = VALUES(nama),
                        role = VALUES(role),
                        nim  = VALUES(nim),
                        ip_address = VALUES(ip_address)";

            mysqli_query($conn, $sql);
        }

        header("Location: index.php");
        exit();
    } catch (Throwable $e) {
        error_log("[FIKOM LOGIN] SSO exception: " . $e->getMessage());
        // Jika error adalah masalah DB, tampilkan pesan DB, bukan invalid_token
        $msg = $e->getMessage();
        if (stripos($msg, 'Access denied') !== false || stripos($msg, 'mysqli') !== false) {
            header("Location: login.php?error=db_error&msg=" . urlencode($msg));
        } else {
            header("Location: login.php?error=invalid_token&msg=" . urlencode($msg));
        }
        exit();
    }
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
            --primary: #8a9ccc;
            /* Subtle blue/purple accent */
            --primary-soft: rgba(255, 255, 255, 0.5);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            /* Fallback flat color */
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
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(58, 66, 82, 0.95), rgba(138, 156, 204, 0.4));
        }

        .branding-content {
            position: relative;
            z-index: 1;
        }

        .branding-content .unika-logo {
            width: 80px;
            margin-bottom: 24px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2));
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .branding-content h1 {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
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
            background: rgba(255, 255, 255, 0.2);
            /* Memberikan beda layer blur */
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

        .error-alert {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }



        .login-footer {
            text-align: center;
            margin-top: auto;
            /* Mendorong footer ke paling bawah container */
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
                <img src="assets/img/Lambang-Universitas-Katolik-Soegijapranata-Semarang.png" alt="Logo UNIKA"
                    class="unika-logo">
                <h1>Portal Terintegrasi</h1>
                <p>Satu akses untuk semua layanan akademik Fakultas Ilmu Komputer UNIKA Soegijapranata.</p>
            </div>
        </div>

        <div class="login-form">
            <img src="assets/img/fikom.png" alt="Logo FIKOM" class="fikom-logo">

            <h2>Selamat Datang!</h2>
            <p class="subtitle">Silakan masuk menggunakan akun email institusi Anda.</p>

            <?php if (isset($_GET['error'])): ?>
                <?php
                $error_msg = 'Terjadi kesalahan saat login. Silakan coba lagi.';
                if ($_GET['error'] === 'invalid_token') {
                    $error_msg = 'Token Google tidak valid atau kedaluwarsa. Silakan coba lagi.';
                    if (isset($_GET['msg'])) {
                        $error_msg .= ' Detail: ' . htmlspecialchars($_GET['msg']);
                    }
                } elseif ($_GET['error'] === 'db_error') {
                    $error_msg = 'Login berhasil diverifikasi oleh Google, namun sistem tidak dapat terhubung ke database. Hubungi administrator.';
                } elseif ($_GET['error'] === 'dosen_not_found') {
                    $error_msg = 'Email UNIKA Anda belum terdaftar sebagai Dosen di sistem. Silakan hubungi Superadmin.';
                } elseif ($_GET['error'] === 'prodi_not_allowed') {
                    $error_msg = 'Program studi Anda belum diizinkan untuk mengakses sistem ini.';
                } elseif ($_GET['error'] === 'wrong_domain') {
                    $error_msg = 'Akses ditolak. Harap masuk menggunakan email resmi UNIKA Soegijapranata (@unika.ac.id atau @student.unika.ac.id).';
                }
                ?>
                <div class="error-alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error_msg) ?></span>
                </div>
            <?php endif; ?>

            <div id="g_id_signin" style="display: flex; justify-content: center;"></div>

            <script>
                function handleCredentialResponse(response) {
                    // Buat form secara dinamis untuk mengirim credential ke login.php secara first-party.
                    // Menggunakan POST first-party menghindari isu cookie SameSite pada cross-site POST dari Google.
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'login.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'credential';
                    input.value = response.credential;
                    form.appendChild(input);

                    document.body.appendChild(form);
                    form.submit();
                }

                window.onload = function () {
                    google.accounts.id.initialize({
                        client_id: "<?= $_ENV['GOOGLE_CLIENT_ID'] ?>",
                        context: "signin",
                        callback: handleCredentialResponse,
                        auto_prompt: false
                    });

                    google.accounts.id.renderButton(
                        document.getElementById("g_id_signin"),
                        {
                            type: "standard",
                            shape: "rectangular",
                            theme: "outline",
                            text: "signin_with_google",
                            size: "large",
                            logo_alignment: "center"
                        }
                    );
                };
            </script>

            <div class="login-footer">
                &copy; <?php echo date("Y"); ?> Fakultas Ilmu Komputer, UNIKA Soegijapranata.
            </div>
        </div>
    </div>

</body>

</html>