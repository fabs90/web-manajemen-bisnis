<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - {{ config('app.name') }}</title>

    <!--<link rel="stylesheet" href="{{ asset('css/register.css') }}">-->
</head>
<style>
/* === Base === */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: "Poppins", sans-serif;
    background: #f7f9fb;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* === Wrapper === */
.auth-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 20px;
}

/* === Card === */
.auth-card {
    width: 420px;
    background: #ffffff;
    border-radius: 16px;
    padding: 40px 35px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.7s ease;
}

/* === Header === */
.auth-header {
    text-align: center;
    margin-bottom: 25px;
}

.auth-header h1 {
    font-size: 26px;
    color: #1e293b;
    margin-bottom: 6px;
}

.auth-header p {
    font-size: 14px;
    color: #6b7280;
}

/* === Alerts === */
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

/* === Form === */
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
input[type="password"] {
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

input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

/* === Options === */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.remember {
    font-size: 13px;
    color: #4b5563;
}

.forgot {
    font-size: 13px;
    text-decoration: none;
    color: #2563eb;
}

.forgot:hover {
    text-decoration: underline;
}

/* === Button === */
.btn-primary {
    width: 100%;
    background: #2563eb;
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
    background: #1e40af;
}

.btn-primary:active {
    transform: scale(0.97);
}

/* === Footer === */
.register-text {
    text-align: center;
    margin-top: 18px;
    font-size: 14px;
    color: #4b5563;
}

.register-text a {
    color: #2563eb;
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
    padding-right: 40px; /* buat ruang untuk icon mata */
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

/* === Animation === */
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
<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.textContent = isPassword ? "🙈" : "👁️";
}
</script>
<!--<script src="{{ asset('js/register.js') }}"></script>-->
</html>
