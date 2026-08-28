<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - Toko Online</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        .container {
            max-width: 520px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #1d4ed8;
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 12px;
            opacity: 0.85;
        }
        .body {
            padding: 32px 28px;
            text-align: center;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .otp-box {
            background-color: #eff6ff;
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 18px 24px;
            display: inline-block;
            margin-bottom: 24px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #1d4ed8;
            margin: 0;
        }
        .warning {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            margin-bottom: 24px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 16px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TokoOnline</h1>
            <p>Layanan Permintaan Reset Kata Sandi</p>
        </div>
        <div class="body">
            <div class="title">Kode Reset Kata Sandi Anda</div>
            <div class="subtitle">
                Kami menerima permintaan untuk mengatur ulang kata sandi akun Toko Online Anda. Gunakan kode 6-digit berikut:
            </div>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otpCode }}</div>
            </div>

            <div class="warning">
                ⏰ Kode ini berlaku selama <strong>5 menit</strong>.<br>
                🔒 Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini dan amankan akun Anda.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Toko Online Official Store. Semua Hak Dilindungi.
        </div>
    </div>
</body>
</html>
