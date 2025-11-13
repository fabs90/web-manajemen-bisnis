<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Digitrans - Pengelolaan Administrasi dan Transaksi Bisnis</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_web.png') }}" type="image/x-icon" />

    <!--<link rel="stylesheet" href="{{ asset('css/login.css') }}"> Nanti Pakai ini -->
</head>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: "Poppins", sans-serif;
        background: #e6f3ec;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .auth-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 20px;
    }

    .auth-card {
        width: 420px;
        background: #ffffff;
        border-radius: 16px;
        padding: 40px 35px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        animation: fadeIn 0.7s ease;
    }

    .auth-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .auth-header h1 {
        font-size: 26px;
        color: #3bb273;
        margin-bottom: 6px;
    }

    .auth-header p {
        font-size: 14px;
        color: #6b7280;
    }

    .alert {
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .alert.success {
        background-color: #ecfdf5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert.danger {
        background-color: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .form-group {
        margin-bottom: 18px;
    }

    label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    input[type="email"],
    input[type="text"],
    input[type="password"],
   select {
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        outline: none;
        font-size: 14px;
        transition:
            border 0.2s ease,
            box-shadow 0.2s ease;
        background: #fff;
    }

    input:focus,
    select:focus{
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .forgot {
        font-size: 13px;
        text-decoration: none;
        color: #3bb273;
    }

    .forgot:hover {
        text-decoration: underline;
    }

    .btn-primary {
        width: 100%;
        background: #1b3a5a;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        font-size: 15px;
        transition:
            background 0.25s ease,
            transform 0.1s ease;
    }

    .btn-primary:hover {
        background: #264d78;
    }

    .btn-primary:active {
        transform: scale(0.97);
    }

    .register-text {
        text-align: center;
        margin-top: 18px;
        font-size: 14px;
        color: #4b5563;
    }

    .register-text a {
        color: #3bb273;
        text-decoration: none;
        font-weight: 600;
    }

    .register-text a:hover {
        text-decoration: underline;
    }

    .password-wrapper {
        position: relative;
    }

    .password-wrapper input {
        width: 100%;
        padding-right: 40px;
        /* buat ruang untuk icon mata */
    }

    .toggle-password {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #9ca3af;
        transition: color 0.2s ease;
        user-select: none;
    }

    .toggle-password:hover {
        color: #2563eb;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

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
                    <input id="email" type="email" name="email" placeholder="contoh@email.com"
                        value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="role">Pilih Jenis Akun</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Pilih...</option>
                        <option value="ukm" {{ old('role') == 'ukm' ? 'selected' : '' }}>UKM</option>
                        <option value="nelayan" {{ old('role') == 'nelayan' ? 'selected' : '' }}>Nelayan</option>
                        <option value="koperasi" {{ old('role') == 'koperasi' ? 'selected' : '' }}>Koperasi</option>
                    </select>
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
    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            const isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            icon.textContent = isPassword ? "🙈" : "👁️";
        }
    </script>
    <!--<script src="{{ asset('js/login.js') }}"></script>-->
</body>

</html>
