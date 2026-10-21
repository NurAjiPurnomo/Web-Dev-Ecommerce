<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 40px 0;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background-color: #ffffff;
            padding: 24px 32px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        .brand-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-decoration: none;
        }
        .brand-blue {
            color: #1d4ed8;
        }
        .content {
            padding: 32px;
            text-align: center;
        }
        .headline {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 10px 0;
        }
        .subtitle {
            font-size: 14px;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            margin-bottom: 24px;
        }
        .otp-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #1d4ed8;
            margin: 0;
        }
        .warning {
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background-color: #1d4ed8; color: #ffffff; border-radius: 6px; width: 32px; height: 32px; text-align: center; vertical-align: middle; font-weight: bold; font-size: 14px;">
                            TO
                        </td>
                        <td style="padding-left: 10px;">
                            <span class="brand-name">Toko<span class="brand-blue">Online</span></span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="content">
                <h1 class="headline">Kode Verifikasi OTP Anda</h1>
                <div class="subtitle">
                    Gunakan kode 6-digit di bawah ini untuk menyelesaikan proses autentikasi akun Anda.
                </div>
                
                <div class="otp-box">
                    <div class="otp-code">{{ $otpCode }}</div>
                </div>

                <div class="warning">
                    Kode ini berlaku selama <strong>5 menit</strong>.<br>
                    Demi keamanan akun, jangan berikan kode ini kepada siapa pun.
                </div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Toko Online. Hak cipta dilindungi.
            </div>
        </div>
    </div>
</body>
</html>
