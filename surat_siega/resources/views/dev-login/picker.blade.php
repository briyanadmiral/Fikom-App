@php
    $roleColors = [
        'admin_tu' => '#0ea5e9',
        'dekan' => '#7c3aed',
        'wakil_dekan' => '#a855f7',
        'kaprodi' => '#0d9488',
        'dosen' => '#2563eb',
        'tendik' => '#475569',
    ];
    $roleIcons = [
        'admin_tu' => '🏛️',
        'dekan' => '👑',
        'wakil_dekan' => '🎖️',
        'kaprodi' => '🎓',
        'dosen' => '📚',
        'tendik' => '🛠️',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Login Picker - Surat SIEGA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e3a8a 100%);
            min-height: 100vh;
            color: #f1f5f9;
            padding: 24px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { margin-bottom: 32px; text-align: center; }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            color: #fca5a5;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(to right, #f1f5f9, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle { color: #94a3b8; font-size: 15px; max-width: 640px; margin: 0 auto; }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            backdrop-filter: blur(8px);
        }
        .alert-success { background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); color: #86efac; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
        .current-session {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(8px);
        }
        .current-session-info { font-size: 14px; }
        .current-session-info strong { color: #fff; }
        .current-session-info small { color: #94a3b8; display: block; margin-top: 2px; }
        .btn-logout {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.4); color: #fff; }
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .role-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(12px);
            transition: all 0.2s;
        }
        .role-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        .role-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .role-icon {
            width: 44px; height: 44px;
            display: grid; place-items: center;
            border-radius: 10px;
            font-size: 22px;
        }
        .role-meta { flex: 1; }
        .role-name { font-size: 16px; font-weight: 700; color: #fff; text-transform: capitalize; }
        .role-desc { font-size: 12px; color: #94a3b8; margin-top: 2px; }
        .quick-login {
            display: block;
            width: 100%;
            padding: 10px 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            text-decoration: none;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            margin-bottom: 14px;
            transition: all 0.2s;
        }
        .quick-login:hover { box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); transform: translateY(-1px); }
        .users-list { display: flex; flex-direction: column; gap: 6px; max-height: 240px; overflow-y: auto; }
        .users-list::-webkit-scrollbar { width: 6px; }
        .users-list::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 3px; }
        .user-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 8px;
            text-decoration: none;
            color: #cbd5e1;
            font-size: 12px;
            transition: all 0.15s;
        }
        .user-link:hover {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            color: #fff;
        }
        .user-name { font-weight: 500; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-id { color: #64748b; font-family: 'JetBrains Mono', monospace; font-size: 11px; padding-left: 8px; }
        .empty { color: #64748b; font-size: 12px; text-align: center; padding: 12px; font-style: italic; }
        .api-docs {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            margin-top: 16px;
            backdrop-filter: blur(12px);
        }
        .api-docs h2 { font-size: 16px; margin-bottom: 14px; color: #f1f5f9; display: flex; align-items: center; gap: 8px; }
        .api-docs code {
            display: block;
            background: rgba(0, 0, 0, 0.4);
            padding: 10px 14px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            color: #86efac;
            margin: 6px 0;
            border-left: 3px solid #22c55e;
        }
        .login-card-manual {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            backdrop-filter: blur(12px);
        }
        .login-card-manual .form-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-row-custom {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
        }
        .form-group-custom {
            flex: 1;
            min-width: 240px;
        }
        .form-group-custom.button-group {
            flex: 0 0 auto;
            min-width: auto;
        }
        .form-group-custom label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-group-custom input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition: all 0.2s;
        }
        .form-group-custom input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        .btn-submit-manual {
            padding: 11px 24px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn-submit-manual:hover {
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">⚡ DEV ENVIRONMENT ONLY</div>
            <h1>Dev Login Picker</h1>
            <p class="subtitle">Pilih role untuk login langsung ke Surat SIEGA tanpa Google OAuth. Aktif hanya saat APP_ENV=local|testing.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <!-- FORM LOGIN MANUAL -->
        <div class="login-card-manual">
            <h3 class="form-title">🗝️ Login via NPP / NIM / Email & Password</h3>
            <form action="{{ route('dev.login.manual') }}" method="POST">
                @csrf
                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label for="npp_nim">NPP / NIM / Email</label>
                        <input type="text" name="npp_nim" id="npp_nim" placeholder="Masukkan NPP, NIM atau Email" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Masukkan Password" required>
                    </div>
                    <div class="form-group-custom button-group">
                        <button type="submit" class="btn-submit-manual">Masuk</button>
                    </div>
                </div>
            </form>
        </div>

        @if ($currentUser)
            <div class="current-session">
                <div class="current-session-info">
                    <strong>Sedang login sebagai: {{ $currentUser->nama_lengkap }}</strong>
                    <small>{{ $currentUser->email }} · role: {{ $currentUser->peran->nama ?? '-' }} · id: {{ $currentUser->id }}</small>
                </div>
                <a href="{{ route('dev.login.logout') }}" class="btn-logout">Logout</a>
            </div>
        @endif

        <div class="roles-grid">
            @foreach ($roles as $peran)
                @php
                    $color = $roleColors[$peran->nama] ?? '#6366f1';
                    $icon = $roleIcons[$peran->nama] ?? '👤';
                    $usersForRole = $usersByRole[$peran->id] ?? collect();
                @endphp
                <div class="role-card">
                    <div class="role-header">
                        <div class="role-icon" style="background: {{ $color }}33; color: {{ $color }};">{{ $icon }}</div>
                        <div class="role-meta">
                            <div class="role-name">{{ str_replace('_', ' ', $peran->nama) }}</div>
                            <div class="role-desc">{{ $peran->deskripsi ?? '—' }}</div>
                        </div>
                    </div>

                    @if ($usersForRole->isNotEmpty())
                        <a href="{{ route('dev.login.role', $peran->nama) }}" class="quick-login">
                            ⚡ Quick Login (user pertama)
                        </a>
                        <div class="users-list">
                            @foreach ($usersForRole as $u)
                                <a href="{{ route('dev.login.user', $u->id) }}" class="user-link" title="{{ $u->email }}">
                                    <span class="user-name">{{ $u->nama_lengkap }}</span>
                                    <span class="user-id">#{{ $u->id }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="empty">Tidak ada user aktif untuk role ini</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="api-docs">
            <h2>📡 API untuk AI Agent (Playwright/Puppeteer)</h2>
            <p>Gunakan endpoint berikut sebagai single GET request untuk auto-login:</p>
            <code>GET {{ url('/dev-login/role/admin_tu') }}</code>
            <code>GET {{ url('/dev-login/role/dekan') }}</code>
            <code>GET {{ url('/dev-login/role/wakil_dekan') }}</code>
            <code>GET {{ url('/dev-login/role/kaprodi') }}</code>
            <code>GET {{ url('/dev-login/role/dosen') }}</code>
            <code>GET {{ url('/dev-login/role/tendik') }}</code>
            <p>Atau login sebagai user spesifik:</p>
            <code>GET {{ url('/dev-login/user/{id}') }}</code>
            <p>Setelah hit, cookie session akan ter-set dan otomatis redirect ke <strong>/home</strong>. Switch role cukup hit URL role lain — session lama otomatis di-flush.</p>
        </div>
    </div>
</body>
</html>
