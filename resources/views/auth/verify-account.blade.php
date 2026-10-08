<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun | TRANSDIGITAL</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_square.png') }}" type="image/x-icon" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        integrity="sha384-j1ndA4nMXqtezvGFma32c4ToXDdi8OfZfldVwARuJ549AktWexKLmOmsnKcxaZbO" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/verify-account.css') }}">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Verifikasi Akun</h1>
                <p>Masukkan kode OTP 6 digit yang dikirim ke email Anda ({{ $user->email }}).</p>
            </div>

            {{-- Notifikasi Error --}}
            @if ($errors->any())
                <div class="alert danger">
                    <ul style="padding-left: 20px; margin: 0;">
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

            <form method="POST" action="{{ route('account-verification.store') }}">
                @csrf
                <div class="otp-container">
                    @for ($i = 1; $i <= 6; $i++)
                        <input type="text" maxlength="1" name="otp[]" class="otp-input" required>
                    @endfor
                </div>

                <button type="submit" class="btn-primary">Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('account-verification.resend') }}">
                @csrf
                <p class="resend-text">
                    Tidak menerima kode?
                    <button type="submit" class="btn-link">Kirim Ulang OTP</button>
                </p>
            </form>

            <div class="divider">
                <span>Atau</span>
            </div>

            <div class="change-email-wrapper">
                <a href="{{ route('account-verification.reset-email') }}" class="btn-secondary">Salah ketik email? Ubah
                    Email</a>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/verify-email.js') }}"></script>
</body>

</html>
