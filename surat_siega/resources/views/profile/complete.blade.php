<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data - FIKOM UNIKA Soegijapranata</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #8a9ccc;
            --primary-hover: #7587b8;
            --primary-soft: rgba(138, 156, 204, 0.15);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
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
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.6s ease-out;
        }

        .card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 40px 30px;
            text-align: center;
        }

        .header {
            margin-bottom: 25px;
        }

        .logo-text {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: 2px;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .alert-box {
            background-color: rgba(0, 123, 255, 0.08);
            border: 1px solid rgba(0, 123, 255, 0.2);
            color: #0056b3;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            text-align: left;
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .alert-box i {
            margin-top: 3px;
            font-size: 1.1rem;
        }

        /* Role Switch Toggle */
        .role-toggle-container {
            display: flex;
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 25px;
            gap: 5px;
        }

        .toggle-btn {
            flex: 1;
            padding: 11px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
        }

        .toggle-btn.active {
            background: white;
            color: var(--dark);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s;
            color: var(--text-main);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }

        .form-control:disabled {
            background: rgba(230, 235, 245, 0.4);
            color: var(--text-muted);
            cursor: not-allowed;
        }

        select.form-control {
            padding-left: 45px;
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg fill='%233a4252' height='24' viewBox='0 0 24 24' width='24' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/><path d='M0 0h24v24H0z' fill='none'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(138, 156, 204, 0.2);
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 156, 204, 0.3);
        }

        .btn-exit {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.2s;
            margin-top: 25px;
            display: inline-block;
        }

        .btn-exit:hover {
            color: #ef4444;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 5px;
            font-weight: 500;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="logo-text">FIKOM</div>
            <div class="subtitle">Lengkapi Data Pengguna</div>
        </div>

        <div class="alert-box">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Lengkapi Data Anda</strong><br>
                Silakan pilih kategori Anda dan lengkapi data yang diperlukan untuk mengajukan akses.
            </div>
        </div>

        @php
            $activeType = old('user_type', $suggestedType);
        @endphp

        <!-- Switcher -->
        <div class="role-toggle-container">
            <button type="button" class="toggle-btn" id="btn-mahasiswa" onclick="selectUserType('mahasiswa')">
                <i class="fas fa-graduation-cap"></i> Mahasiswa
            </button>
            <button type="button" class="toggle-btn" id="btn-dosen-tendik" onclick="selectUserType('dosen_tendik')">
                <i class="fas fa-user-tie"></i> Dosen / Tendik
            </button>
        </div>

        <form action="{{ route('profile.complete.store') }}" method="POST">
            @csrf

            <!-- Hidden Input for type -->
            <input type="hidden" name="user_type" id="user_type" value="{{ $activeType }}">

            <!-- Shared read-only info -->
            <div class="form-group">
                <label>Nama Lengkap</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" value="{{ $user->nama_lengkap }}" disabled>
                </div>
            </div>

            <!-- SECTION MAHASISWA -->
            <div id="section-mahasiswa" style="display: none;">
                <div class="form-group">
                    <label>Email Student</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nim">NIM (Nomor Induk Mahasiswa)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="nim" id="nim" class="form-control @error('nim') is-invalid @enderror" 
                               value="{{ old('nim', $suggestedNim) }}" placeholder="Contoh: 23.G4.0004">
                    </div>
                    @error('nim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="whatsapp">Nomor WhatsApp Aktif</label>
                    <div class="input-wrapper">
                        <i class="fab fa-whatsapp"></i>
                        <input type="text" name="whatsapp" id="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" 
                               value="{{ old('whatsapp') }}" placeholder="Contoh: 081229789910">
                    </div>
                    @error('whatsapp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- SECTION DOSEN / TENDIK -->
            <div id="section-dosen-tendik" style="display: none;">
                <div class="form-group">
                    <label for="role_type">Peran Pekerjaan</label>
                    <div class="input-wrapper">
                        <i class="fas fa-users-cog"></i>
                        <select name="role_type" id="role_type" class="form-control @error('role_type') is-invalid @enderror">
                            <option value="dosen" {{ old('role_type') === 'dosen' ? 'selected' : '' }}>Dosen Pengajar</option>
                            <option value="tendik" {{ old('role_type') === 'tendik' ? 'selected' : '' }}>Tenaga Kependidikan (Staff)</option>
                        </select>
                    </div>
                    @error('role_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="npp">NPP (Nomor Pokok Pegawai)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-badge"></i>
                        <input type="text" name="npp" id="npp" class="form-control @error('npp') is-invalid @enderror" 
                               value="{{ old('npp', $user->npp) }}" placeholder="Contoh: 058.1.2002.255">
                    </div>
                    @error('npp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email_input">Email Kampus / Terdaftar</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email_input" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $user->email) }}" placeholder="dosen@unika.ac.id">
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password (Untuk Login Sistem)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Min. 6 Karakter">
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-shield-alt"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                               placeholder="Ulangi Password">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Kirim Permohonan Akses
            </button>
        </form>

        <form action="{{ route('external.exit') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn-exit">
                <i class="fas fa-right-from-bracket mr-1"></i> Kembali ke Portal Login
            </button>
        </form>
    </div>
</div>

<script>
    function selectUserType(type) {
        document.getElementById('user_type').value = type;
        
        if (type === 'mahasiswa') {
            document.getElementById('btn-mahasiswa').classList.add('active');
            document.getElementById('btn-dosen-tendik').classList.remove('active');
            
            document.getElementById('section-mahasiswa').style.display = 'block';
            document.getElementById('section-dosen-tendik').style.display = 'none';
            
            // Set required fields for validation
            document.getElementById('nim').required = true;
            document.getElementById('whatsapp').required = true;
            
            document.getElementById('role_type').required = false;
            document.getElementById('npp').required = false;
            document.getElementById('email_input').required = false;
            document.getElementById('password').required = false;
            document.getElementById('password_confirmation').required = false;
        } else {
            document.getElementById('btn-mahasiswa').classList.remove('active');
            document.getElementById('btn-dosen-tendik').classList.add('active');
            
            document.getElementById('section-mahasiswa').style.display = 'none';
            document.getElementById('section-dosen-tendik').style.display = 'block';
            
            // Set required fields for validation
            document.getElementById('nim').required = false;
            document.getElementById('whatsapp').required = false;
            
            document.getElementById('role_type').required = true;
            document.getElementById('npp').required = true;
            document.getElementById('email_input').required = true;
            document.getElementById('password').required = true;
            document.getElementById('password_confirmation').required = true;
        }
    }

    // Initialize layout based on PHP active type
    document.addEventListener('DOMContentLoaded', function() {
        selectUserType('{{ $activeType }}');
    });
</script>

</body>
</html>
