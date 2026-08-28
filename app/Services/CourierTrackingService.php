<?php

namespace App\Services;

class CourierTrackingService
{
    /**
     * Generate Granular Shopee-Style Tracking Timeline for an order.
     *
     * @param string $status
     * @param string|null $trackingNumber
     * @param string|null $courier
     * @param string|null $createdAt
     * @param string|null $recipientName
     * @return array
     */
    public static function getTimeline($status, $trackingNumber = null, $courier = 'J&T Express', $createdAt = null, $recipientName = 'Pelanggan')
    {
        $baseTime = $createdAt ? strtotime($createdAt) : time();

        $t1 = date('d M Y, H:i', $baseTime) . ' WIB';
        $t2 = date('d M Y, H:i', $baseTime + 900) . ' WIB';
        $t3 = date('d M Y, H:i', $baseTime + 3600) . ' WIB';
        $t4 = date('d M Y, H:i', $baseTime + 14400) . ' WIB';
        $t5 = date('d M Y, H:i', $baseTime + 43200) . ' WIB';
        $t6 = date('d M Y, H:i', $baseTime + 86400) . ' WIB';

        $courierName = strtoupper($courier ?: 'J&T Express');
        $resi = $trackingNumber ?: 'Belum Terbit';

        if ($status === 'belum_bayar') {
            return [
                'current_step' => 1,
                'total_steps'  => 4,
                'status_title' => 'Menunggu Pembayaran',
                'status_desc'  => 'Silakan selesaikan pembayaran sebelum batas waktu berakhir.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pesanan Dibuat',
                        'desc'      => 'Menunggu verifikasi pembayaran dari bank / merchant.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'card'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Pembayaran Dikonfirmasi',
                        'desc'      => 'Sistem akan secara otomatis memverifikasi pembayaran Anda.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'check'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Penjual Mengemas Paket',
                        'desc'      => 'Pesanan akan disiapkan oleh tim gudang penjual.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'box'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Pengiriman Kurir',
                        'desc'      => 'Paket diserahkan ke jasa pengiriman.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                ]
            ];
        }

        if ($status === 'dikemas' || $status === 'diproses') {
            return [
                'current_step' => 2,
                'total_steps'  => 4,
                'status_title' => 'Sedang Dikemas oleh Penjual',
                'status_desc'  => 'Penjual sedang menyiapkan barang dan mencetak label pengiriman.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pesanan Dibuat & Dibayar',
                        'desc'      => 'Pembayaran sebesar nominal transaksi berhasil diverifikasi.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'card'
                    ],
                    [
                        'time'      => $t2,
                        'title'     => 'Pembayaran Dikonfirmasi',
                        'desc'      => 'Pesanan diteruskan ke sistem gudang penjual.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'check'
                    ],
                    [
                        'time'      => $t3,
                        'title'     => 'Penjual Sedang Mengemas Paket',
                        'desc'      => 'Produk sedang dikemas rapi & menunggu penjemputan kurir ' . $courierName . '.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'box'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Penyerahan ke Kurir ' . $courierName,
                        'desc'      => 'Paket akan dijemput oleh kurir resmi ekspedisi.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                ]
            ];
        }

        if ($status === 'dikirim') {
            return [
                'current_step' => 3,
                'total_steps'  => 4,
                'status_title' => 'Dalam Pengiriman Kurir',
                'status_desc'  => 'Paket Anda sedang dalam perjalanan ke alamat tujuan.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pembayaran Dikonfirmasi',
                        'desc'      => 'Pembayaran terverifikasi aman oleh sistem.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'check'
                    ],
                    [
                        'time'      => $t2,
                        'title'     => 'Paket Selesai Dikemas',
                        'desc'      => 'Tim gudang selesai mengemas paket & mencetak resi ' . $resi . '.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'box'
                    ],
                    [
                        'time'      => $t3,
                        'title'     => 'Paket Diserahkan ke ' . $courierName,
                        'desc'      => 'Paket diterima di Drop Point / Agen Resmi ' . $courierName . '.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                    [
                        'time'      => $t4,
                        'title'     => 'Transit di Sorting Hub Gateway',
                        'desc'      => 'Paket sedang dipilah di pusat transit sortir ekspedisi.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'building'
                    ],
                    [
                        'time'      => $t5,
                        'title'     => 'Paket Dibawa Kurir Pengirim',
                        'desc'      => 'Paket dibawa oleh Kurir (Budi Prasetyo - 0857123499) menuju alamat tujuan.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'courier'
                    ],
                ]
            ];
        }

        if ($status === 'selesai') {
            return [
                'current_step' => 4,
                'total_steps'  => 4,
                'status_title' => 'Pesanan Selesai & Diterima',
                'status_desc'  => 'Paket telah berhasil diterima di alamat tujuan.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pembayaran Dikonfirmasi',
                        'desc'      => 'Pembayaran diverifikasi oleh sistem.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'check'
                    ],
                    [
                        'time'      => $t3,
                        'title'     => 'Paket Diserahkan ke ' . $courierName,
                        'desc'      => 'Resi ' . $resi . ' aktif dan diserahkan ke kurir.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                    [
                        'time'      => $t5,
                        'title'     => 'Dalam Pengantaran Kurir',
                        'desc'      => 'Kurir mengantarkan paket ke lokasi penerima.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'courier'
                    ],
                    [
                        'time'      => $t6,
                        'title'     => 'Paket Diterima oleh ' . $recipientName,
                        'desc'      => 'Paket diterima dalam kondisi baik. Terima kasih telah berbelanja!',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'check-circle'
                    ],
                ]
            ];
        }

        // Batal
        return [
            'current_step' => 0,
            'total_steps'  => 4,
            'status_title' => 'Pesanan Dibatalkan',
            'status_desc'  => 'Transaksi ini telah dibatalkan.',
            'timeline'     => [
                [
                    'time'      => $t1,
                    'title'     => 'Pesanan Dibuat',
                    'desc'      => 'Pesanan telah dibuat oleh pengguna.',
                    'completed' => true,
                    'current'   => false,
                    'icon'      => 'card'
                ],
                [
                    'time'      => $t2,
                    'title'     => 'Pesanan Dibatalkan',
                    'desc'      => 'Pesanan dibatalkan oleh pengguna atau admin.',
                    'completed' => true,
                    'current'   => true,
                    'icon'      => 'x-circle'
                ],
            ]
        ];
    }
}
