<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Surat FIKOM UNIKA</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* === PREMIUM LIGHT GLASSMORPHISM TEMA === */
        :root {
            --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
            --font-display: "Space Grotesk", sans-serif;
            --primary: #4f46e5;
            --primary-glow: rgba(99, 102, 241, 0.08);
            --dark: #0f172a;
            --text-main: #334155;
            --text-muted: #64748b;
            --border: rgba(255, 255, 255, 0.6);
            --bg-card: rgba(255, 255, 255, 0.4);
            --glass-blur: blur(32px);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-sans);
            background-color: #FDFCFB;
            /* Glowing background blobs */
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 245, 230, 0.65) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(230, 230, 250, 0.75) 0%, transparent 40%);
            background-attachment: fixed;
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
        }

        /* Subtle noise texture overlay */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.012;
            pointer-events: none;
            z-index: -1;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-card {
            width: 440px;
            max-width: 90%;
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 32px;
            padding: 45px 35px;
            border: 1px solid var(--border);
            box-shadow: 0 32px 64px -16px rgba(0, 0, 0, 0.08);
            text-align: center;
            z-index: 10;
            opacity: 0;
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .logos-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }

        .fikom-logo {
            height: 54px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }

        .login-card h2 {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .login-card .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 35px;
            font-weight: 500;
        }

        /* Input group styling */
        .input-group {
            position: relative;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid rgba(0, 0, 0, 0.03);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-group:focus-within {
            background: rgba(255, 255, 255, 0.85);
            border-color: rgba(99, 102, 241, 0.2);
            box-shadow: 
                inset 0 2px 4px rgba(0, 0, 0, 0.01),
                0 0 0 4px var(--primary-glow);
        }

        .input-icon {
            padding: 12px 18px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        .form-control {
            flex: 1;
            border: none;
            background: transparent;
            padding: 14px 14px 14px 0;
            font-family: var(--font-sans);
            font-size: 15px;
            color: var(--dark);
            outline: none;
            font-weight: 500;
        }

        .form-control::placeholder {
            color: #cbd5e1;
        }

        .btn-toggle-pwd {
            background: transparent;
            border: none;
            padding: 0 18px;
            color: #94a3b8;
            cursor: pointer;
            outline: none;
            transition: color 0.2s;
        }

        .btn-toggle-pwd:hover {
            color: var(--dark);
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 13px;
            margin-top: -15px;
            margin-bottom: 15px;
            padding-left: 5px;
            display: block;
            text-align: left;
            font-weight: 500;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            border: none;
            border-radius: 14px;
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            font-family: var(--font-sans);
            font-size: 15px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.7);
            color: var(--dark);
            border-color: rgba(0, 0, 0, 0.1);
        }

        .login-footer {
            margin-top: 35px;
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logos-container">
        <img src="../../assets/img/fikom.png" alt="Logo FIKOM" class="fikom-logo">
    </div>

    <h2>Sistem Arsip Surat</h2>
    <p class="subtitle">Silakan masukkan NPP/Email dan password Anda.</p>

    <form action="{{ route('login') }}" method="post" autocomplete="off">
        @csrf

        <div class="input-group">
            <span class="input-icon"><i class="fas fa-user"></i></span>
            <input type="text" name="email" class="form-control"
                   value="{{ old('email') }}" placeholder="Email atau NPP" required autofocus>
        </div>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="input-group">
            <span class="input-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="password"
                   class="form-control" placeholder="Password" required>
            <button class="btn-toggle-pwd" type="button" id="toggle-password" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn-submit">
            <span>Masuk Ke Sistem</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>

    <a href="https://apps.fikom.id" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali ke Portal
    </a>

    <div class="login-footer">
        &copy; {{ date("Y") }} Fakultas Ilmu Komputer, UNIKA Soegijapranata.
    </div>
</div>

<script>
document.getElementById('toggle-password').addEventListener('click', function(){
    let pwd = document.getElementById('password');
    let icon = this.querySelector('i');
    if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

@if(session('error'))
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '{{ session('error') }}'
    });
</script>
@endif

</body>
</html>
