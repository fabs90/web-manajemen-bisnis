<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode Verifikasi Akun</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; padding: 20px;">
    <div style="max-width: 500px; margin: auto; background: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #111827;">Halo, {{ $name }} - {{$email}}👋</h2>

        <p>Terima kasih telah mendaftar di website Digitrans. Berikut adalah kode verifikasi (OTP) untuk akun Anda:</p>

        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #2563eb;">
                {{ $otp }}
            </span>
        </div>

        <p>Kode ini akan kadaluarsa dalam <strong>30 menit</strong>.</p>

        <p>Jika Anda tidak meminta kode ini, abaikan saja email ini.</p>

        <br>
        <p>Salam,</p>
        <p><strong>Tim IT Support💖</strong></p>
    </div>
</body>
</html>
