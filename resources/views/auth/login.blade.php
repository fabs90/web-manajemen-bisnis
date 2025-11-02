<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ public_path('css/login.css') }}">
</head>
<body>


    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Masuk ke Akun</h1>
                <p>Selamat datang kembali! Silakan login untuk melanjutkan.</p>
            </div>

            {{-- Notifikasi --}}
            @if (session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input id="email" type="email" name="email" placeholder="contoh@email.com" value="{{ old('email') }}" required autofocus>
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


                <div class="form-options">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-primary">Masuk</button>

                <p class="register-text">
                    Belum punya akun?
                    <a href="{{ route('register') }}">Daftar sekarang</a>
                </p>
            </form>
        </div>
    </div>
    <script src="{{ public_path('js/login.js') }}"></script>
</body>
</html>
