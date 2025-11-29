<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun | Digitrans</title>
    <link rel="shortcut icon" href="{{ asset('./dist/assets/static/images/logo_web.png') }}" type="image/x-icon" />
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

    .otp-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 25px 0;
    }

    .otp-input {
        width: 48px;
        height: 55px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        text-align: center;
        font-size: 20px;
        outline: none;
        transition: border 0.2s ease, box-shadow 0.2s ease;
    }

    .otp-input:focus {
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
    }

    .btn-primary:hover {
        background: #1e40af;
    }

    .btn-primary:active {
        transform: scale(0.97);
    }

    .resend-text {
        text-align: center;
        margin-top: 18px;
        font-size: 14px;
        color: #4b5563;
    }

    .resend-text a {
        color: #3bb273;
        text-decoration: none;
        font-weight: 600;
    }

    .resend-text a:hover {
        text-decoration: underline;
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

    .btn-primary.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .spinner {
        width: 18px;
        height: 18px;
        border: 3px solid #ffffff;
        border-top: 3px solid transparent;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

</style>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Verifikasi Akun</h1>
                <p>Masukkan kode OTP 6 digit yang dikirim ke email Anda.</p>
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

            <form method="POST" action="{{ route('account-verification.store') }}">
                @csrf
                <div class="otp-container">
                    @for ($i = 1; $i <= 6; $i++)
                        <input type="text" maxlength="1" name="otp[]" class="otp-input" required>
                    @endfor
                </div>

                <button type="submit" class="btn-primary">Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('account-verification.resend') }}" style="margin-top: 18px; text-align:center;">
                @csrf
                <p class="resend-text">
                    Tidak menerima kode?
                    <button type="submit" class="btn-link">Kirim Ulang OTP</button>
                </p>
            </form>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll(".otp-input");
        inputs.forEach((input, index) => {
            input.addEventListener("input", (e) => {
                const value = e.target.value;
                if (value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        const formVerify = document.querySelector('form[action*="account-verification.store"]');
        const btnVerify = document.querySelector('.btn-primary');

        formVerify.addEventListener("submit", function () {
            btnVerify.classList.add("loading");
            btnVerify.disabled = true;
            btnVerify.innerHTML = `<span class="spinner"></span>Memverifikasi...`;
        });

        document.addEventListener("paste", function(e) {
                let activeElement = document.activeElement;
                let otpInputs = document.querySelectorAll(".otp-input");
                let isOtpField = Array.from(otpInputs).includes(activeElement);

                if (!isOtpField && !otpInputs[0].closest('form').contains(activeElement)) return;

                e.preventDefault(); // Mencegah paste default

                let pastedData = (e.clipboardData || window.clipboardData).getData('text');
                let otpDigits = pastedData.replace(/\D/g, '').slice(0, 6); // Ambil hanya angka, max 6
                if (otpDigits.length < 1) return;
                otpInputs.forEach((input, index) => {
                    if (otpDigits[index]) {
                        input.value = otpDigits[index];
                    } else {
                        input.value = ''; // Kosongkan yang tersisa
                    }
                });

                let lastFilledIndex = otpDigits.length - 1;
                if (lastFilledIndex < 5) {
                    otpInputs[lastFilledIndex].focus();
                } else {
                    // Kalau sudah 6 digit, langsung fokus ke tombol Verifikasi
                    document.querySelector(".btn-primary").focus();
                }
            });

            document.querySelectorAll(".otp-input").forEach(input => {
                input.addEventListener("keyup", function(e) {
                    if (e.key === "Enter") {
                        let allFilled = Array.from(document.querySelectorAll(".otp-input")).every(inp => inp.value.length === 1);
                        if (allFilled) {
                            document.querySelector(".btn-primary").click();
                        }
                    }
                });
            });
    </script>
</body>

</html>
