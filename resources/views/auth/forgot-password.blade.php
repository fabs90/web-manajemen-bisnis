<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | TRANSDIGITAL</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_square.png') }}" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
            min-height: 100vh;
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
            margin-bottom: 8px;
        }

        .auth-header p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
        }

        .alert {
            padding: 12px;
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
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

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
            transition: background 0.25s ease, transform 0.1s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-primary:hover {
            background: #1e40af;
        }

        .btn-primary:active {
            transform: scale(0.97);
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
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .opacity-25 { opacity: 0.25; }
        .opacity-75 { opacity: 0.75; }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Lupa Password?</h1>
                <p>Jangan khawatir. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.</p>
            </div>

            <!-- Notifikasi Session Sukses (Breeze) -->
            @if (session('status'))
                <div class="alert success">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Notifikasi Error -->
            @if ($errors->any())
                <div class="alert danger">
                    <ul style="padding-left: 20px; margin: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" onsubmit="showLoading()">
                @csrf
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="Masukkan email Anda">
                </div>

                <button type="submit" class="btn-primary" id="btn-submit">
                    <span id="btn-text">Kirim Tautan Reset</span>
                    <svg id="btn-spinner" style="display: none; width: 20px; height: 20px; margin-left: 8px; animation: spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                <a href="{{ route('login') }}" class="btn-secondary">Kembali ke Login</a>
            </form>
        </div>
    </div>
    
    <script>
        function showLoading() {
            var btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            document.getElementById('btn-text').innerText = 'Memproses...';
            document.getElementById('btn-spinner').style.display = 'block';
        }
    </script>
</body>

</html>
