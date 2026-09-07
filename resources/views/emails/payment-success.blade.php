<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembayaran Berhasil</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; background-color: #f1f5f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #f8fafc; padding: 30px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .header h1 { margin: 0; color: #0f172a; font-size: 24px; }
        .content { padding: 30px; }
        .success-icon { text-align: center; margin-bottom: 20px; }
        .success-icon span { display: inline-block; width: 60px; height: 60px; background-color: #22c55e; color: white; border-radius: 50%; font-size: 30px; line-height: 60px; font-weight: bold; }
        .greeting { font-size: 16px; margin-bottom: 20px; text-align: center; }
        .order-id { text-align: center; margin: 20px 0; padding: 15px; background-color: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1; }
        .order-id span { font-family: monospace; font-size: 18px; font-weight: bold; color: #0f172a; }
        .total-amount { font-size: 20px; font-weight: bold; color: #22c55e; margin-top: 10px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .details-table th { color: #64748b; font-weight: 600; background-color: #f8fafc; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; margin-top: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; margin-bottom: 15px;">
                <tr>
                    <td style="background-color: #1d4ed8; color: #ffffff; border-radius: 8px; width: 36px; height: 36px; text-align: center; vertical-align: middle; font-family: sans-serif; font-weight: bold; font-size: 16px;">
                        TO
                    </td>
                    <td style="padding-left: 10px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #0f172a;">
                        Toko<span style="color: #1d4ed8;">Online</span>
                    </td>
                </tr>
            </table>
            <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px; color: #22c55e; font-weight: bold;">Hore! Pembayaran Diterima</p>
        </div>
        
        <div class="content">
            <div class="success-icon">
                <span>✓</span>
            </div>

            <div class="greeting">
                Halo <strong>{{ $order->recipient_name ?? 'Pelanggan' }}</strong>,<br>
                Pembayaran Anda telah berhasil kami verifikasi. Pesanan Anda saat ini sedang dalam proses pengemasan!
            </div>

            <div class="order-id">
                <div>Nomor Invoice:</div>
                <span>{{ $order->invoice_number }}</span>
            </div>

            <table class="details-table">
                <tr>
                    <th colspan="2" style="text-align: center; background-color: #e2e8f0; color: #0f172a;">Ringkasan Pembayaran</th>
                </tr>
                <tr>
                    <th>Subtotal Produk</th>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Ongkos Kirim</th>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <th style="color: #16a34a;">Diskon ({{ $order->voucher_code ?? 'Promo' }})</th>
                    <td style="text-align: right; font-weight: bold; color: #16a34a;">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <th style="font-size: 16px; color: #2563eb;">Total Dibayar</th>
                    <td style="text-align: right; font-size: 16px; font-weight: bold; color: #2563eb;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            </table>

            <table class="details-table">
                <tr>
                    <th>Metode Pembayaran</th>
                    <td>{{ strtoupper(str_replace('_va', ' Virtual Account', $order->payment_method)) }}</td>
                </tr>
                <tr>
                    <th>Status Pesanan</th>
                    <td><span style="color: #2563eb; font-weight: bold;">Sedang Dikemas (Diproses)</span></td>
                </tr>
                <tr>
                    <th>Kurir Pengiriman</th>
                    <td>{{ $order->courier }}</td>
                </tr>
                <tr>
                    <th>Alamat Tujuan</th>
                    <td>{{ $order->shipping_address }}</td>
                </tr>
            </table>

            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 14px; color: #64748b; margin-bottom: 10px;">Anda dapat melacak status pesanan Anda secara langsung melalui website kami.</p>
                <a href="{{ url('/orders') }}" class="btn">Lacak Pesanan Saya</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Toko Online. All rights reserved.</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi tim dukungan kami.</p>
        </div>
    </div>
</body>
</html>
