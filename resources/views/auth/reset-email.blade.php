<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Email | TRANSDIGITAL - Pengelolaan Administrasi dan Transaksi Bisnis</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_square.png') }}" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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

    input[type="email"] {
        width: 100%;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        outline: none;
        font-size: 14px;
        transition: border 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
    }

    input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
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
        transition: background 0.25s ease, transform 0.1s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }

    .btn-primary:hover {
        background: #264d78;
    }

    .btn-primary:active {
        transform: scale(0.97);
    }

    .btn-primary:disabled {
        opacity: 0.8;
        cursor: not-allowed;
    }

    .btn-secondary {
        width: 100%;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        font-size: 15px;
        transition: background 0.25s ease, transform 0.1s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 12px;
        text-decoration: none;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-secondary:active {
        transform: scale(0.97);
    }

    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top: 2px solid #ffffff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
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
                <h1>Ubah Alamat Email</h1>
                <p>Silakan masukkan alamat email baru untuk akun Anda.</p>
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

            @if (session('status'))
                <div class="alert success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('account-verification.reset-email.store') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="email">Alamat Email Baru</label>
                    <input id="email" type="email" name="email" placeholder="contoh@email.com"
                        value="{{ old('email') }}" required autofocus>
                </div>

                <button id="btn-submit" type="submit" class="btn-primary">Simpan Email Baru</button>
            </form>
        </div>
    </div>

    <script>
        const form = document.querySelector(".auth-form");
        const btnSubmit = document.getElementById("btn-submit");

        form.addEventListener("submit", function() {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `
            <span class="spinner"></span>
            <span>Memproses...</span>
        `;
        });
    </script>
</body>

</html>
