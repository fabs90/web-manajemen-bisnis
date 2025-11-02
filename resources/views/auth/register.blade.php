<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - {{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ public_path('css/register.css') }}">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Buat Akun Baru</h1>
                <p>Daftar untuk mulai menggunakan aplikasi.</p>
            </div>

            {{-- Notifikasi Error --}}
            @if ($errors->any())
                <div class="alert danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="name">Nama UMKM</label>
                    <input id="name" type="text" name="name" placeholder="Nama UMKM/Perusahaan Kamu" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input id="email" type="email" name="email" placeholder="contoh@email.com" value="{{ old('email') }}" required>
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
                <div class="form-group password-group">
                    <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                    <div class="password-wrapper">
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password" required>
                        <span class="toggle-password" onclick="togglePassword('password_confirmation', this)">👁️</span>
                    </div>
                </div>


                <button type="submit" class="btn-primary">Daftar</button>

                <p class="register-text">
                    Sudah punya akun?
                    <a href="{{ route('login') }}">Masuk di sini</a>
                </p>
            </form>
        </div>
    </div>

</body>
<script src="{{ public_path('js/register.js') }}"></script>
</html>
