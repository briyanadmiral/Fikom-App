{{-- 
    Halaman 403 Standalone - Tidak menggunakan layout aplikasi
    Untuk keamanan: orang yang mencoba akses folder terlarang tidak melihat struktur sidebar/menu
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | Surat FIKOM</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            background: linear-gradient(135deg, #1a1a2e 0%, #2d1b36 50%, #4a1942 100%);
            overflow: hidden;
            position: relative;
        }
        
        /* Animated shield background */
        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            opacity: 0.05;
        }
        
        .shield-pattern {
            position: absolute;
            width: 60px;
            height: 70px;
            animation: pulse-shield 4s ease-in-out infinite;
        }
        
        .shield-pattern:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .shield-pattern:nth-child(2) { top: 20%; left: 80%; animation-delay: 1s; }
        .shield-pattern:nth-child(3) { top: 70%; left: 15%; animation-delay: 2s; }
        .shield-pattern:nth-child(4) { top: 80%; left: 75%; animation-delay: 0.5s; }
        .shield-pattern:nth-child(5) { top: 40%; left: 5%; animation-delay: 1.5s; }
        .shield-pattern:nth-child(6) { top: 50%; left: 90%; animation-delay: 2.5s; }
        
        @keyframes pulse-shield {
            0%, 100% {
                transform: scale(1);
                opacity: 0.3;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.6;
            }
        }
        
        .container {
            text-align: center;
            z-index: 1;
            padding: 2rem;
            max-width: 600px;
        }
        
        /* Shield illustration */
        .illustration {
            margin: 1rem 0 2rem;
            position: relative;
        }
        
        .shield {
            width: 100px;
            height: 120px;
            margin: 0 auto;
            animation: float-shield 4s ease-in-out infinite;
        }
        
        @keyframes float-shield {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        /* Glowing 403 Number */
        .error-code {
            font-size: clamp(80px, 20vw, 160px);
            font-weight: 800;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 50%, #922b21 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            animation: pulse 3s ease-in-out infinite;
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-code::after {
            content: '403';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: blur(25px);
            opacity: 0.5;
            z-index: -1;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
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
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(231, 76, 60, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(231, 76, 60, 0.6);
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
        
        .btn svg {
            width: 18px;
            height: 18px;
        }
        
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
    <!-- Background Pattern -->
    <div class="background-pattern">
        <div class="shield-pattern">
            <svg viewBox="0 0 24 24" fill="currentColor" color="white">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div class="shield-pattern">
            <svg viewBox="0 0 24 24" fill="currentColor" color="white">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div class="shield-pattern">
            <svg viewBox="0 0 24 24" fill="currentColor" color="white">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div class="shield-pattern">
            <svg viewBox="0 0 24 24" fill="currentColor" color="white">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div class="shield-pattern">
            <svg viewBox="0 0 24 24" fill="currentColor" color="white">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
        <div class="shield-pattern">
            <svg viewBox="0 0 24 24" fill="currentColor" color="white">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </div>
    </div>
    
    <div class="container">
        <!-- Shield Illustration -->
        <div class="illustration">
            <div class="shield">
                <svg viewBox="0 0 100 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Shield base -->
                    <path d="M50 5L10 20v35c0 30 40 55 40 55s40-25 40-55V20L50 5z" 
                          fill="url(#shieldGradient)" stroke="#ffffff" stroke-width="2"/>
                    <!-- Lock icon -->
                    <rect x="38" y="55" width="24" height="20" rx="3" fill="#1a1a2e"/>
                    <path d="M42 55V45a8 8 0 1116 0v10" stroke="#1a1a2e" stroke-width="4" fill="none"/>
                    <circle cx="50" cy="65" r="3" fill="#e74c3c"/>
                    <defs>
                        <linearGradient id="shieldGradient" x1="10" y1="5" x2="90" y2="115">
                            <stop offset="0%" stop-color="#e74c3c"/>
                            <stop offset="100%" stop-color="#922b21"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>
        
        <!-- Error Code -->
        <div class="error-code">403</div>
        
        <!-- Title -->
        <h1 class="title">Akses Tidak Diizinkan</h1>
        
        <!-- Description -->
        <p class="description">
            Anda tidak memiliki izin untuk mengakses halaman ini.
            <br>Silakan login terlebih dahulu atau hubungi administrator.
        </p>
        
        <!-- Action Buttons -->
        <div class="actions">
            <a href="{{ route('login') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Login
            </a>
            <a href="{{ url('/') }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Beranda
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        &copy; {{ date('Y') }} Surat FIKOM. All rights reserved.
    </div>
</body>
</html>