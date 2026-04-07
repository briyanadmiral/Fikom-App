<?php
// login.php - Integration dengan sistem login teman
require_once 'config/database.php';

startSession();

// Jika sudah login, redirect ke dashboard
$user_info = getUserInfo();
if ($user_info['is_admin'] || $user_info['is_users']) {
    if ($user_info['is_admin']) {
        header("Location: admin/dashboard.php");
    } elseif ($user_info['is_users']) {
        header("Location: users/dashboard.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sentralisasi Ruangan FIKOM</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
    <style>
        .login-container {
            max-width: 400px;
            margin: 5rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .demo-login {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .demo-login h4 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .demo-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .demo-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .demo-btn:hover {
            background: #e9ecef;
        }
        
        .integration-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #856404;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">F</div>
                <h1>Sentralisasi Ruangan FIKOM</h1>
            </div>
            <a href="index.php" class="login-btn">Kembali</a>
        </div>
    </header>

    <div class="container">
        <div class="login-container">
            <h2 style="text-align: center; margin-bottom: 2rem;">🔐 Login Sistem</h2>
            
            <!-- Demo Login Buttons (untuk testing) -->
            <div class="demo-login">
                <h4>🧪 Demo Login (Testing)</h4>
                <div class="demo-buttons">
                    <button class="demo-btn" onclick="demoLogin('admin')">
                        👨‍💼 Login sebagai Admin
                    </button>
                    <button class="demo-btn" onclick="demoLogin('users')">
                        👨‍🎓 Login sebagai Users
                    </button>
                </div>
            </div>
            
            <!-- Integration Note -->
            <div class="integration-note">
                <strong>📝 Catatan Integrasi:</strong><br>
                Sistem ini akan terintegrasi dengan sistem login yang sudah dibuat oleh teman Anda. 
                Setelah login berhasil, session akan di-set dan user akan diarahkan ke dashboard sesuai role.
            </div>
            
            <!-- Form login placeholder (untuk integrasi dengan sistem teman) -->
            <form class="login-form" action="process_login.php" method="POST" style="display: none;">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-btn" style="width: 100%; margin: 0;">
                    Masuk
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p style="color: #666; font-size: 0.9rem;">
                    Belum punya akun? Hubungi admin untuk registrasi
                </p>
            </div>
        </div>
    </div>

    <script>
        // Demo login function (untuk testing)
        function demoLogin(role) {
            // Simulate setting session via AJAX
            fetch('demo_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ role: role })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect berdasarkan role
                    if (role === 'admin') {
                        window.location.href = 'admin/dashboard.php';
                    } else if (role === 'users') {
                        window.location.href = 'users/dashboard.php';
                    }
                } else {
                    alert('Login gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi error saat login');
            });
        }
        
        // Untuk integrasi dengan sistem login teman
        // Fungsi ini akan dipanggil setelah login berhasil di sistem teman
        function setUserSession(userData) {
            // Set session berdasarkan data user dari sistem login teman
            fetch('set_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect ke dashboard
                    window.location.href = data.redirect_url;
                }
            });
        }
    </script>
</body>
</html>