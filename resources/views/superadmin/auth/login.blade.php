<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis</title>
    <link rel="shortcut icon" href="{{ asset('dist/assets/static/images/logo_square.png') }}" type="image/x-icon" />

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Masuk ke Admin</h1>
                <p>Selamat datang kembali! Silakan login untuk melanjutkan.</p>
            </div>

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert danger">
                    <ul style="padding-left: 20px; margin: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('superadmin.store') }}" class="auth-form">
                @csrf
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input id="email" type="email" name="email" placeholder="contoh@email.com"
                        value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group password-group">
                    <label for="password">Kata Sandi</label>
                    <div class="password-wrapper">
                        <input id="password" type="password" name="password" placeholder="••••••••" required>
                        <span class="toggle-password" onclick="togglePassword('password', this)">
                            👁️
                        </span>
                    </div>
                </div>
                <input type="hidden" name="role" value="superadmin">
                <button type="submit" class="btn-primary">Masuk</button>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/login-admin.js') }}"></script>
</body>

</html>
