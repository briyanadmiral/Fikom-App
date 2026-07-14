<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Login - Localhost Only</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dev-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 2rem;
            max-width: 800px;
            width: 100%;
        }
        .dev-badge {
            background: #ff4444;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .user-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s;
        }
        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .user-info h5 {
            margin: 0;
            font-size: 1rem;
            color: #333;
        }
        .user-info small {
            color: #666;
        }
        .badge-role {
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .btn-login {
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
        }
        .quick-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="dev-container">
        <div class="dev-badge">🔧 LOCALHOST DEV MODE</div>
        <h2 class="mb-1">Dev Login Bypass</h2>
        <p class="text-muted mb-4">Click user untuk login tanpa password. Hanya aktif di local environment.</p>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h5 class="mb-3">Available Users:</h5>

        @forelse($users as $user)
        <div class="user-card">
            <div class="user-info">
                <h5>{{ $user->nama_lengkap }}</h5>
                <small>{{ $user->email }}</small>
                @if($user->nim)
                <small class="d-block">NIM: {{ $user->nim }}</small>
                @endif
                <span class="badge badge-role badge-{{ $user->peran_id === 1 ? 'danger' : ($user->peran_id === 2 ? 'primary' : 'secondary') }}">
                    {{ $user->peran->nama ?? 'N/A' }}
                </span>
                @if($user->approval_status === 'pending')
                <span class="badge badge-warning ml-1">Pending</span>
                @elseif($user->approval_status === 'rejected')
                <span class="badge badge-secondary ml-1">Rejected</span>
                @else
                <span class="badge badge-success ml-1">Approved</span>
                @endif
            </div>
            <form action="{{ route('dev.loginAs') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt mr-1"></i>Login
                </button>
            </form>
        </div>
        @empty
        <div class="alert alert-warning">Tidak ada user di database. Run migration + seeder dulu.</div>
        @endforelse

        <div class="quick-buttons">
            <strong class="w-100 mb-2">Quick Login by Role:</strong>
            <a href="{{ route('dev.quick', ['peran_id' => 1]) }}" class="btn btn-danger btn-sm">
                Admin TU
            </a>
            <a href="{{ route('dev.quick', ['peran_id' => 2]) }}" class="btn btn-primary btn-sm">
                Dekan
            </a>
            <a href="{{ route('dev.quick', ['peran_id' => 3]) }}" class="btn btn-info btn-sm">
                Wakil Dekan
            </a>
            <a href="{{ route('dev.quick', ['peran_id' => 5]) }}" class="btn btn-success btn-sm">
                Dosen
            </a>
        </div>

        <div class="mt-4 text-center">
            <small class="text-muted">
                ENV: {{ config('app.env') }} |
                <a href="{{ route('login') }}">Normal Login</a>
            </small>
        </div>
    </div>
</body>
</html>
