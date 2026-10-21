<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Pengiriman Pesanan</title>
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
            max-width: 560px;
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
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
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
            margin: 0 0 12px 0;
            line-height: 1.3;
        }
        .message-body {
            font-size: 14px;
            color: #475569;
            margin: 0 0 24px 0;
        }
        .waybill-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .waybill-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .waybill-number {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 20px;
            font-weight: 800;
            color: #1d4ed8;
            letter-spacing: 1px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .details-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .details-table .label {
            color: #64748b;
            font-weight: 500;
            width: 40%;
        }
        .details-table .value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
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
                <div class="status-badge">Pesanan Dalam Pengiriman</div>

                <h1 class="headline">Paket Anda Telah Dikirim</h1>
                <p class="message-body">
                    Halo <strong>{{ $order->recipient_name ?? ($order->user->name ?? 'Pelanggan') }}</strong>,<br>
                    Pesanan Anda dengan nomor invoice <strong>{{ $order->invoice_number }}</strong> telah diserahkan kepada pihak kurir ekspedisi dan sedang dalam perjalanan.
                </p>

                <div class="waybill-card">
                    <div class="waybill-label">Nomor Resi Pengiriman (AWB)</div>
                    <div class="waybill-number">{{ $order->waybill_number ?: ($order->tracking_number ?: 'Dalam Proses') }}</div>
                </div>

                <table class="details-table">
                    <tr>
                        <td class="label">Kurir Ekspedisi</td>
                        <td class="value">{{ strtoupper($order->courier) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat Pengiriman</td>
                        <td class="value" style="font-weight: 500;">{{ $order->shipping_address }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jumlah Produk</td>
                        <td class="value">{{ $order->items->count() ?? 1 }} Barang</td>
                    </tr>
                </table>

                <div class="btn-container">
                    <a href="{{ route('tracking.public', $order->waybill_number ?: $order->invoice_number) }}" style="background-color: #1d4ed8; color: #ffffff !important; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 800; font-size: 14px; display: inline-block; text-align: center;">
                        <span style="color: #ffffff !important; font-weight: 800; text-decoration: none;">Lacak Lokasi Paket Saya &rarr;</span>
                    </a>
                </div>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} Toko Online. Hak cipta dilindungi.<br>
                Pemberitahuan resmi transaksi ini dikirimkan ke {{ $order->user->email ?? 'email Anda' }}.
            </div>
        </div>
    </div>
</body>
</html>
