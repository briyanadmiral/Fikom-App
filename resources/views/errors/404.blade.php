{{--
Halaman 404 Standalone - Tidak menggunakan layout aplikasi
Untuk keamanan: orang yang belum login tidak melihat struktur sidebar/menu
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | Surat FIKOM</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            overflow: hidden;
            position: relative;
        }

        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        .particle:nth-child(1) {
            left: 10%;
            animation-delay: 0s;
            animation-duration: 12s;
        }

        .particle:nth-child(2) {
            left: 20%;
            animation-delay: 2s;
            animation-duration: 15s;
        }

        .particle:nth-child(3) {
            left: 30%;
            animation-delay: 4s;
            animation-duration: 18s;
        }

        .particle:nth-child(4) {
            left: 40%;
            animation-delay: 0s;
            animation-duration: 14s;
        }

        .particle:nth-child(5) {
            left: 50%;
            animation-delay: 3s;
            animation-duration: 16s;
        }

        .particle:nth-child(6) {
            left: 60%;
            animation-delay: 5s;
            animation-duration: 13s;
        }

        .particle:nth-child(7) {
            left: 70%;
            animation-delay: 1s;
            animation-duration: 17s;
        }

        .particle:nth-child(8) {
            left: 80%;
            animation-delay: 4s;
            animation-duration: 19s;
        }

        .particle:nth-child(9) {
            left: 90%;
            animation-delay: 2s;
            animation-duration: 11s;
        }

        .particle:nth-child(10) {
            left: 15%;
            animation-delay: 6s;
            animation-duration: 20s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        .container {
            text-align: center;
            z-index: 1;
            padding: 2rem;
            max-width: 600px;
        }

        /* Glowing 404 Number */
        .error-code {
            font-size: clamp(100px, 25vw, 200px);
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            animation: pulse 3s ease-in-out infinite;
            line-height: 1;
            margin-bottom: 1rem;
            text-shadow: 0 0 60px rgba(102, 126, 234, 0.3);
        }

        .error-code::after {
            content: '404';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: blur(30px);
            opacity: 0.5;
            z-index: -1;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }
        }

        /* Astronaut illustration */
        .illustration {
            margin: 2rem 0;
            position: relative;
        }

        .astronaut {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            animation: float-astronaut 6s ease-in-out infinite;
        }

        .astronaut svg {
            width: 100%;
            height: 100%;
        }

        @keyframes float-astronaut {

            0%,
            100% {
                transform: translateY(0) rotate(-5deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        .title {
            color: #ffffff;
            font-size: clamp(1.25rem, 4vw, 1.75rem);
            font-weight: 600;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .description {
            color: rgba(255, 255, 255, 0.7);
            font-size: clamp(0.875rem, 2.5vw, 1rem);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            border-radius: 50px;
            font-size: 0.9375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        /* Icon SVG */
        .btn svg {
            width: 18px;
            height: 18px;
        }

        /* Footer copyright */
        .footer {
            position: absolute;
            bottom: 1.5rem;
            left: 0;
            right: 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.75rem;
            z-index: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Animated particles background -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <!-- Floating Astronaut Illustration -->
        <div class="illustration">
            <div class="astronaut">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Helmet -->
                    <circle cx="50" cy="40" r="28" fill="#E8E8E8" stroke="#CCCCCC" stroke-width="2" />
                    <circle cx="50" cy="40" r="20" fill="#1a1a2e" />
                    <ellipse cx="45" cy="35" rx="6" ry="8" fill="rgba(102, 126, 234, 0.3)" />
                    <!-- Body -->
                    <rect x="35" y="65" width="30" height="25" rx="10" fill="#E8E8E8" />
                    <!-- Arms -->
                    <rect x="20" y="68" width="18" height="8" rx="4" fill="#E8E8E8" transform="rotate(-15 25 72)" />
                    <rect x="62" y="68" width="18" height="8" rx="4" fill="#E8E8E8" transform="rotate(15 75 72)" />
                    <!-- Legs -->
                    <rect x="38" y="87" width="8" height="12" rx="4" fill="#E8E8E8" />
                    <rect x="54" y="87" width="8" height="12" rx="4" fill="#E8E8E8" />
                    <!-- Backpack -->
                    <rect x="38" y="60" width="24" height="8" rx="2" fill="#CCCCCC" />
                </svg>
            </div>
        </div>

        <!-- Error Code -->
        <div class="error-code">404</div>

        <!-- Title -->
        <h1 class="title">Sepertinya Anda Tersesat di Luar Angkasa</h1>

        <!-- Description -->
        <p class="description">
            Halaman yang Anda cari tidak ditemukan atau mungkin telah dipindahkan.
            <br>Mari kembali ke jalur yang benar.
        </p>

        <!-- Action Buttons -->
        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Beranda
            </a>
            <a href="{{ route('login') }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Login
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} Surat FIKOM. All rights reserved.
    </div>
</body>

</html>