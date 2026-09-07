<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pesanan</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #2563eb; color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .order-id { text-align: center; margin-bottom: 30px; }
        .order-id span { display: inline-block; background-color: #eff6ff; color: #1d4ed8; padding: 8px 15px; border-radius: 20px; font-weight: bold; font-family: monospace; font-size: 14px; }
        .payment-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 30px; text-align: center; }
        .payment-title { font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .va-number { font-size: 24px; font-weight: bold; color: #0f172a; letter-spacing: 2px; font-family: monospace; margin: 10px 0; }
        .total-amount { font-size: 20px; font-weight: bold; color: #2563eb; margin-top: 10px; }
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
            <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Terima kasih atas pesanan Anda!</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Halo <strong>{{ $order->recipient_name ?? 'Pelanggan' }}</strong>,<br>
                Pesanan Anda telah kami terima dan sedang menunggu pembayaran.
            </div>

            <div class="order-id">
                <span>{{ $order->invoice_number }}</span>
            </div>

            @if($order->payment_method !== 'cod' && $order->payment_code)
            <div class="payment-box">
                <div class="payment-title">Menunggu Pembayaran ({{ strtoupper(str_replace('_va', ' Virtual Account', $order->payment_method)) }})</div>
                <div style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Silakan transfer ke nomor rekening berikut:</div>
                <div class="va-number">{{ $order->payment_code }}</div>
                <div class="total-amount">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
            </div>
            @endif

            <h3 style="font-size: 16px; margin-bottom: 10px; color: #1e293b;">Rincian Pesanan</h3>
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" style="text-align: right;">Subtotal Produk</th>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th colspan="2" style="text-align: right;">Ongkos Kirim</th>
                        <td style="text-align: right; font-weight: bold;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                    <tr>
                        <th colspan="2" style="text-align: right; color: #16a34a;">Diskon ({{ $order->voucher_code ?? 'Promo' }})</th>
                        <td style="text-align: right; font-weight: bold; color: #16a34a;">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th colspan="2" style="text-align: right; font-size: 16px; color: #2563eb;">Total Tagihan</th>
                        <td style="text-align: right; font-size: 16px; font-weight: bold; color: #2563eb;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/') }}" class="btn">Kembali ke Toko</a>
            </div>
        </div>

        <div class="footer">
            Email ini dibuat secara otomatis. Harap tidak membalas email ini.<br>
            &copy; {{ date('Y') }} Toko Online. All rights reserved.
        </div>
    </div>
</body>
</html>
