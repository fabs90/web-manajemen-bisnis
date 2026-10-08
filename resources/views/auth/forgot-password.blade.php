<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | TRANSDIGITAL</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_square.png') }}" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        integrity="sha384-j1ndA4nMXqtezvGFma32c4ToXDdi8OfZfldVwARuJ549AktWexKLmOmsnKcxaZbO" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}" />
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Lupa Password?</h1>
                <p>Jangan khawatir. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan untuk
                    mengatur ulang password.</p>
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
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}"
                        required autofocus placeholder="Masukkan email Anda">
                </div>

                <button type="submit" class="btn-primary" id="btn-submit">
                    <span id="btn-text">Kirim Tautan Reset</span>
                    <svg id="btn-spinner"
                        style="display: none; width: 20px; height: 20px; margin-left: 8px; animation: spin 1s linear infinite;"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
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
