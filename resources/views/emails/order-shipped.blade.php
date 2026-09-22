<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesanan Dikirim</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; background-color: #f1f5f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #f8fafc; padding: 30px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .content { padding: 30px; }
        .shipped-icon { text-align: center; margin-bottom: 20px; }
        .shipped-icon span { display: inline-block; width: 64px; height: 64px; background-color: #2563eb; color: white; border-radius: 50%; font-size: 32px; line-height: 64px; font-weight: bold; }
        .greeting { font-size: 16px; margin-bottom: 20px; text-align: center; }
        .waybill-box { text-align: center; margin: 25px 0; padding: 20px; background-color: #eff6ff; border-radius: 12px; border: 2px dashed #93c5fd; }
        .waybill-label { font-size: 12px; color: #1e40af; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .waybill-code { font-family: monospace; font-size: 22px; font-weight: bold; color: #1d4ed8; margin-top: 5px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .details-table th, .details-table td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .details-table th { color: #64748b; font-weight: 600; background-color: #f8fafc; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 10px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto; margin-bottom: 12px;">
                <tr>
                    <td style="background-color: #1d4ed8; color: #ffffff; border-radius: 8px; width: 36px; height: 36px; text-align: center; vertical-align: middle; font-family: sans-serif; font-weight: bold; font-size: 16px;">
                        TO
                    </td>
                    <td style="padding-left: 10px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #0f172a;">
                        Toko<span style="color: #1d4ed8;">Online</span>
                    </td>
                </tr>
            </table>
            <p style="margin: 0; font-size: 14px; color: #2563eb; font-weight: bold;">🚚 Paket Anda Dalam Pengiriman!</p>
        </div>
        
        <div class="content">
            <div class="shipped-icon">
                <span>📦</span>
            </div>

            <div class="greeting">
                Halo <strong>{{ $order->recipient_name ?? ($order->user->name ?? 'Pelanggan') }}</strong>,<br>
                Kabar gembira! Pesanan Anda dengan invoice <strong>{{ $order->invoice_number }}</strong> telah kami serahkan ke kurir dan sedang dalam perjalanan.
            </div>

            <div class="waybill-box">
                <div class="waybill-label">Nomor Resi Pengiriman (AWB):</div>
                <div class="waybill-code">{{ $order->waybill_number ?: ($order->tracking_number ?: 'Dalam Proses') }}</div>
            </div>

            <table class="details-table">
                <tr>
                    <th colspan="2" style="text-align: center; background-color: #f1f5f9; color: #0f172a;">Rincian Pengiriman</th>
                </tr>
                <tr>
                    <th>Kurir Ekspedisi</th>
                    <td style="font-weight: bold; color: #0f172a;">{{ strtoupper($order->courier) }}</td>
                </tr>
                <tr>
                    <th>Alamat Tujuan</th>
                    <td>{{ $order->shipping_address }}</td>
                </tr>
                <tr>
                    <th>Total Item</th>
                    <td>{{ $order->items->count() ?? 1 }} Produk</td>
                </tr>
            </table>

            <div style="text-align: center; margin: 30px 0 10px 0;">
                <p style="font-size: 14px; color: #64748b; margin-bottom: 15px;">Lacak rute & posisi kendaraan kurir secara langsung di halaman khusus toko kami:</p>
                <a href="{{ route('tracking.public', $order->waybill_number ?: $order->invoice_number) }}" class="btn">
                    🗺️ Lacak Paket (Live GPS Map)
                </a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Toko Online Official. All rights reserved.</p>
            <p>Pemberitahuan resmi ini dikirimkan via SMTP Email ke {{ $order->user->email ?? 'email Anda' }}.</p>
        </div>
    </div>
</body>
</html>
