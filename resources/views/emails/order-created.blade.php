<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pesanan & Instruksi Pembayaran</title>
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
            max-width: 580px;
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
        }
        .status-badge {
            display: inline-block;
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .headline {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }
        .message-body {
            font-size: 14px;
            color: #475569;
            margin: 0 0 24px 0;
        }
        .payment-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .payment-header {
            font-size: 12px;
            font-weight: 700;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .payment-label {
            color: #64748b;
        }
        .payment-val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .va-number {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1.5px;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin: 12px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .details-table th, .details-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .details-table th {
            color: #64748b;
            font-weight: 600;
            text-align: left;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0 12px 0;
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
                <div class="status-badge">Menunggu Pembayaran</div>

                <h1 class="headline">Pesanan Anda Berhasil Dibuat</h1>
                <p class="message-body">
                    Halo <strong>{{ $order->recipient_name ?? ($order->user->name ?? 'Pelanggan') }}</strong>,<br>
                    Pesanan Anda dengan nomor invoice <strong>{{ $order->invoice_number }}</strong> telah tercatat di sistem kami. Silakan selesaikan pembayaran ke rekening berikut.
                </p>

                <!-- INSTRUKSI REKENING PEMBAYARAN FORMAL -->
                <div class="payment-box">
                    <div class="payment-header">Instruksi Rekening Pembayaran</div>
                    
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px; margin-bottom: 12px;">
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">Metode Pembayaran:</td>
                            <td style="color: #0f172a; font-weight: 700; text-align: right; padding: 4px 0;">
                                {{ strtoupper(str_replace('_va', ' Virtual Account', $order->payment_method)) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">Atas Nama Rekening:</td>
                            <td style="color: #0f172a; font-weight: 700; text-align: right; padding: 4px 0;">
                                PT Toko Online Indonesia
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">Batas Waktu Pembayaran:</td>
                            <td style="color: #dc2626; font-weight: 700; text-align: right; padding: 4px 0;">
                                24 Jam Dari Sekarang
                            </td>
                        </tr>
                    </table>

                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 10px; text-align: center;">
                        Nomor Rekening / Kode VA Pembayaran:
                    </div>
                    <div class="va-number">
                        {{ $order->payment_code ?: '8801234567890' }}
                    </div>

                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 14px; margin-top: 10px;">
                        <tr>
                            <td style="color: #0f172a; font-weight: 800;">Total Nominal Transfer:</td>
                            <td style="color: #1d4ed8; font-weight: 800; font-size: 18px; text-align: right;">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>

                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 12px;">Rincian Barang Dipesan</h3>
                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td style="color: #0f172a; font-weight: 600;">{{ $item->product_name }}</td>
                            <td style="text-align: center; color: #64748b;">{{ $item->quantity }}</td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" style="color: #64748b;">Subtotal Produk</td>
                            <td style="text-align: right; font-weight: 700;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="color: #64748b;">Ongkos Kirim</td>
                            <td style="text-align: right; font-weight: 700;">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="2" style="color: #16a34a;">Diskon ({{ $order->voucher_code ?? 'Promo' }})</td>
                            <td style="text-align: right; font-weight: 700; color: #16a34a;">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="2" style="font-size: 15px; font-weight: 800; color: #0f172a;">Grand Total Tagihan</td>
                            <td style="text-align: right; font-size: 16px; font-weight: 800; color: #1d4ed8;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- EXPLICIT INLINE CONTRAST BUTTON -->
                <div class="btn-container">
                    <a href="{{ route('checkout.success') }}?order_id={{ $order->invoice_number }}" style="background-color: #1d4ed8; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 800; font-size: 14px; display: inline-block; text-align: center;">
                        <span style="color: #ffffff !important; font-weight: 800; text-decoration: none;">Bayar / Konfirmasi Pembayaran &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Toko Online. Hak cipta dilindungi.<br>
                Pemberitahuan transaksi resmi ini dikirimkan ke {{ $order->user->email ?? 'email Anda' }}.
            </div>
        </div>
    </div>
</body>
</html>
