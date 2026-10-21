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
    public static function getTimeline($status, $trackingNumber = null, $courier = 'J&T Express', $createdAt = null, $recipientName = 'Pelanggan', $biteshipOrderId = null, $updatedAt = null)
    {
        $baseTime   = $createdAt ? strtotime($createdAt) : time();
        $updateTime = $updatedAt ? strtotime($updatedAt) : min(time(), $baseTime + 60);
        $nowTime    = time();

        $t1 = date('d M Y, H:i', $baseTime) . ' WIB';
        $t2 = date('d M Y, H:i', $updateTime) . ' WIB';
        $t3 = date('d M Y, H:i', min($nowTime, $baseTime + 180)) . ' WIB';
        $t4 = date('d M Y, H:i', min($nowTime, $baseTime + 1800)) . ' WIB';
        $t5 = date('d M Y, H:i', min($nowTime, $baseTime + 3600)) . ' WIB';
        $t6 = date('d M Y, H:i', min($nowTime, $baseTime + 7200)) . ' WIB';

        $rawCourier = trim($courier ?: 'J&T Express');
        $courierClean = strtolower($rawCourier);
        if (str_contains($courierClean, 'j&t') || str_contains($courierClean, 'jnt')) {
            $courierName = 'J&T Express' . (str_contains($courierClean, 'ez') ? ' (EZ)' : '');
        } elseif (str_contains($courierClean, 'jne')) {
            $courierName = 'JNE' . (str_contains($courierClean, 'reg') ? ' (Reguler)' : (str_contains($courierClean, 'yes') ? ' (YES)' : ''));
        } elseif (str_contains($courierClean, 'sicepat')) {
            $courierName = 'SiCepat' . (str_contains($courierClean, 'gokil') ? ' (GOKIL)' : ' (Reguler)');
        } elseif (str_contains($courierClean, 'anteraja')) {
            $courierName = 'AnterAja (Reguler)';
        } elseif (str_contains($courierClean, 'pos')) {
            $courierName = 'Pos Indonesia';
        } else {
            $courierName = ucwords($rawCourier);
        }
        $resi = $trackingNumber ?: 'Belum Terbit';

        $cleanResi = trim($trackingNumber ?? '');
        $isDummyOrEmpty = empty($cleanResi) || in_array($cleanResi, ['Belum Terbit', 'Belum Diterbitkan', 'Dalam Proses', '-']);
        $lookupId = $biteshipOrderId ?: (!$isDummyOrEmpty ? $cleanResi : null);
        if ($lookupId) {
            try {
                $biteship = new \App\Services\BiteshipService();
                $trackRes = $biteship->getTracking($lookupId, $courier);
                if ($trackRes['status'] && !empty($trackRes['data'])) {
                    // Deduplicate identical or consecutive duplicate tracking events from Biteship
                    $rawHistory = $trackRes['data'];
                    $biteshipHistory = [];
                    foreach ($rawHistory as $h) {
                        $last = end($biteshipHistory);
                        $currStatus = strtolower(trim($h['status'] ?? ''));
                        $lastStatus = $last ? strtolower(trim($last['status'] ?? '')) : null;
                        $currNote   = trim($h['note'] ?? $h['description'] ?? '');
                        $lastNote   = $last ? trim($last['note'] ?? $last['description'] ?? '') : null;
                        $currTime   = trim($h['updated_at'] ?? '');

                        // Skip consecutive duplicate status events from Biteship API
                        if ($last && $currStatus === $lastStatus && ($currNote === $lastNote || !empty($currStatus))) {
                            if (!empty($currTime)) {
                                $lastKey = array_key_last($biteshipHistory);
                                $biteshipHistory[$lastKey]['updated_at'] = $currTime;
                            }
                            continue;
                        }

                        $biteshipHistory[] = $h;
                    }
                    $biteshipHistory = array_values($biteshipHistory);

                    $timeline = [];

                    $timeline[] = [
                        'time'      => $t1,
                        'title'     => 'Pembayaran Dikonfirmasi',
                        'desc'      => 'Pembayaran terverifikasi aman oleh sistem.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'check'
                    ];

                    $timeline[] = [
                        'time'      => $t2,
                        'title'     => 'Penjual Sedang Mengemas Paket',
                        'desc'      => 'Pesanan telah selesai dikemas rapi oleh penjual & disiapkan untuk ekspedisi ' . $courierName . '.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'box'
                    ];

                    $count = count($biteshipHistory);
                    foreach ($biteshipHistory as $idx => $h) {
                        $isLatest = ($idx === $count - 1);
                        $hTime = !empty($h['updated_at']) ? date('d M Y, H:i', strtotime($h['updated_at'])) . ' WIB' : '-';
                        $rawNote = $h['note'] ?? $h['description'] ?? 'Pembaruan posisi paket dari ekspedisi.';
                        $hStatus = strtolower($h['status'] ?? '');

                        $hNote = self::formatProfessionalNote($rawNote, $courierName, $recipientName);

                        $titleMap = [
                            'confirmed'    => 'Penjemputan Paket oleh Kurir (' . $courierName . ')',
                            'allocated'    => 'Kurir Penjemput Telah Dialokasikan (' . $courierName . ')',
                            'picking_up'   => 'Kurir Dalam Perjalanan Penjemputan Paket',
                            'picked'       => 'Paket Berhasil Di-pickup oleh Kurir',
                            'in_transit'   => 'Paket Sedang Dalam Perjalanan Transit',
                            'dropping_off' => 'Paket Dalam Pengantaran Kurir ke Penerima',
                            'delivered'    => 'Paket Telah Tiba di Alamat Tujuan',
                            'on_hold'      => 'Pengiriman Tertahan Sementara',
                            'rejected'     => 'Pengiriman Mengalami Kendala / Ditolak',
                            'returned'     => 'Paket Dikembalikan ke Pengirim',
                            'cancelled'    => 'Pengiriman Dibatalkan',
                        ];

                        $hTitle = $titleMap[$hStatus] ?? (ucwords(str_replace('_', ' ', $hStatus)) ?: 'Update Ekspedisi');

                        $timeline[] = [
                            'time'      => $hTime,
                            'title'     => $hTitle,
                            'desc'      => $hNote,
                            'completed' => true,
                            'current'   => $isLatest,
                            'icon'      => $isLatest ? 'truck' : 'check'
                        ];
                    }

                    $latestStatus = strtolower(end($biteshipHistory)['status'] ?? $status);
                    $statusTitle = 'Dalam Pengiriman Kurir';
                    $rawStatusDesc = end($biteshipHistory)['note'] ?? 'Paket sedang diproses oleh kurir.';
                    $statusDesc = self::formatProfessionalNote($rawStatusDesc, $courierName, $recipientName);

                    if (in_array($latestStatus, ['delivered', 'completed'])) {
                        $statusTitle = 'Pesanan Selesai & Diterima';
                    } elseif ($latestStatus === 'picking_up' || $latestStatus === 'allocated') {
                        $statusTitle = 'Kurir Sedang Mengambil / Menjemput Paket';
                    } elseif ($latestStatus === 'on_hold') {
                        $statusTitle = 'Pengiriman Tertahan Sementara';
                    } elseif ($latestStatus === 'in_transit') {
                        $statusTitle = 'Paket Sedang Menuju Kota Tujuan';
                    }

                    return [
                        'current_step' => in_array($latestStatus, ['delivered', 'completed']) ? 4 : 3,
                        'total_steps'  => 4,
                        'status_title' => $statusTitle,
                        'status_desc'  => $statusDesc,
                        'timeline'     => $timeline
                    ];
                }
            } catch (\Exception $e) {
                // Fallback to default static timeline if API lookup fails
            }
        }

        if (in_array($status, ['belum_bayar', 'belum_dibayar'])) {
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
                        'title'     => 'Penjemputan oleh Kurir ' . $courierName,
                        'desc'      => 'Paket akan dijemput oleh kurir resmi ekspedisi di lokasi toko.',
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
                'status_title' => 'Penjemputan Paket Dijadwalkan',
                'status_desc'  => 'Admin telah menjadwalkan penjemputan paket oleh kurir ' . $courierName . '.',
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
                        'title'     => 'Penjual Sedang Mengemas Paket',
                        'desc'      => 'Tim gudang telah selesai mengemas paket & menyiapkan No. Resi ' . $resi . '.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'box'
                    ],
                    [
                        'time'      => $t3,
                        'title'     => 'Penjemputan Paket oleh Kurir (' . $courierName . ')',
                        'desc'      => 'Kurir ' . $courierName . ' dalam proses penjemputan paket ke lokasi toko (No. Resi: ' . $resi . ').',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'truck'
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

        if ($status === 'retur_proses') {
            return [
                'current_step' => 3,
                'total_steps'  => 4,
                'status_title' => 'Klaim Retur Disetujui (Menunggu Pengiriman Barang Fisik)',
                'status_desc'  => 'Admin telah menyetujui klaim retur. Silakan kirimkan fisik barang ke alamat toko dan masukkan Nomor Resi 1 di bawah.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pengajuan Retur Dikirim Pembeli',
                        'desc'      => 'Komplain beserta bukti foto & video unboxing telah berhasil dikirim.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'refresh'
                    ],
                    [
                        'time'      => $t2,
                        'title'     => 'Klaim Retur Disetujui Admin',
                        'desc'      => 'Tim admin toko telah menyetujui garansi retur Anda.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'check'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Pengiriman Fisik Barang Retur (Resi 1)',
                        'desc'      => 'Paket fisik retur sedang dikirim oleh pembeli menuju alamat toko.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Konfirmasi Penerimaan Gudang & Refund / Tukar Barang',
                        'desc'      => 'Admin akan mencairkan refund atau mengirim barang pengganti setelah fisik produk tiba di gudang toko.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'check-circle'
                    ],
                ]
            ];
        }

        if ($status === 'retur_disetujui') {
            return [
                'current_step' => 4,
                'total_steps'  => 4,
                'status_title' => 'Retur Selesai & Dana Dikembalikan',
                'status_desc'  => 'Barang retur telah diterima & diperiksa di gudang toko. Transaksi selesai dan dana berhasil dikembalikan ke pembeli.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pesanan Diterima Kurir / Pelanggan',
                        'desc'      => 'Paket telah sampai di lokasi pembeli.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                    [
                        'time'      => $t2,
                        'title'     => 'Pengajuan Retur Dikirim Pembeli',
                        'desc'      => 'Rincian komplain beserta bukti foto & video unboxing telah dikirim.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'refresh'
                    ],
                    [
                        'time'      => $t3,
                        'title'     => 'Peninjauan Bukti Unboxing Selesai',
                        'desc'      => 'Tim admin telah meninjau bukti foto & video unboxing.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'check'
                    ],
                    [
                        'time'      => $t4,
                        'title'     => 'Retur Selesai & Dana Dikembalikan',
                        'desc'      => 'Fisik barang tiba di toko. Stok produk dikembalikan dan dana transaksi berhasil dikembalikan ke pembeli.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'check-circle'
                    ],
                ]
            ];
        }

        if ($status === 'retur_ditolak') {
            return [
                'current_step' => 4,
                'total_steps'  => 4,
                'status_title' => 'Pengajuan Retur Ditolak',
                'status_desc'  => 'Pengajuan retur ditolak setelah proses peninjauan bukti oleh admin.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pesanan Diterima Kurir / Pelanggan',
                        'desc'      => 'Paket telah sampai di lokasi pembeli.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                    [
                        'time'      => $t2,
                        'title'     => 'Pengajuan Retur Dikirim Pembeli',
                        'desc'      => 'Rincian komplain dikirim oleh pembeli.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'refresh'
                    ],
                    [
                        'time'      => $t3,
                        'title'     => 'Peninjauan Admin Selesai',
                        'desc'      => 'Tim admin toko telah meninjau bukti pengajuan.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'clock'
                    ],
                    [
                        'time'      => $t4,
                        'title'     => 'Pengajuan Retur Ditolak',
                        'desc'      => 'Bukti unboxing/foto tidak memenuhi syarat klaim garansi toko.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'x-circle'
                    ],
                ]
            ];
        }

        if ($status === 'retur' || $status === 'komplain') {
            return [
                'current_step' => 3,
                'total_steps'  => 4,
                'status_title' => 'Dalam Pengajuan Retur / Komplain',
                'status_desc'  => 'Pengajuan pengembalian barang & dana sedang diproses oleh tim toko.',
                'timeline'     => [
                    [
                        'time'      => $t1,
                        'title'     => 'Pesanan Diterima Kurir / Pelanggan',
                        'desc'      => 'Paket telah sampai di lokasi pembeli.',
                        'completed' => true,
                        'current'   => false,
                        'icon'      => 'truck'
                    ],
                    [
                        'time'      => $t2,
                        'title'     => 'Pengajuan Retur Dikirim Pembeli',
                        'desc'      => 'Rincian komplain beserta bukti foto & video unboxing telah berhasil dikirim.',
                        'completed' => true,
                        'current'   => true,
                        'icon'      => 'refresh'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Peninjauan Bukti Unboxing oleh Admin',
                        'desc'      => 'Tim admin sedang memeriksa keabsahan bukti foto dan video unboxing Anda.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'clock'
                    ],
                    [
                        'time'      => '-',
                        'title'     => 'Persetujuan Retur & Pengembalian Dana',
                        'desc'      => 'Proses persetujuan klaim retur serta pengembalian dana ke rekening pembeli.',
                        'completed' => false,
                        'current'   => false,
                        'icon'      => 'check'
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

    /**
     * Generate Granular Shopee-Style Return Tracking Timeline powered by live Biteship API.
     *
     * @param \App\Models\OrderReturn|object $returnRecord
     * @param string|null $courier
     * @param string $sellerName
     * @return array
     */
    public static function getReturnTimeline($returnRecord, $courier = 'J&T Express', $sellerName = 'Gudang Toko')
    {
        $rawCourier = trim($courier ?: 'J&T Express');
        $courierClean = strtolower($rawCourier);
        if (str_contains($courierClean, 'j&t') || str_contains($courierClean, 'jnt')) {
            $courierName = 'J&T Express' . (str_contains($courierClean, 'ez') ? ' (EZ)' : '');
        } elseif (str_contains($courierClean, 'jne')) {
            $courierName = 'JNE' . (str_contains($courierClean, 'reg') ? ' (Reguler)' : (str_contains($courierClean, 'yes') ? ' (YES)' : ''));
        } elseif (str_contains($courierClean, 'sicepat')) {
            $courierName = 'SiCepat' . (str_contains($courierClean, 'gokil') ? ' (GOKIL)' : ' (Reguler)');
        } elseif (str_contains($courierClean, 'anteraja')) {
            $courierName = 'AnterAja (Reguler)';
        } elseif (str_contains($courierClean, 'pos')) {
            $courierName = 'Pos Indonesia';
        } else {
            $courierName = ucwords($rawCourier);
        }

        $createdAt = $returnRecord->created_at ?? null;
        $updatedAt = $returnRecord->updated_at ?? null;
        $t1 = $createdAt ? (is_string($createdAt) ? date('d M Y, H:i', strtotime($createdAt)) : $createdAt->format('d M Y, H:i')) . ' WIB' : '-';
        $t2 = $updatedAt ? (is_string($updatedAt) ? date('d M Y, H:i', strtotime($updatedAt)) : $updatedAt->format('d M Y, H:i')) . ' WIB' : '-';

        $returnStatus = strtolower($returnRecord->status ?? 'pending');
        $resi = $returnRecord->return_tracking_number ?? null;
        $cleanResi = trim($resi ?? '');
        $isDummyOrEmpty = empty($cleanResi) || in_array($cleanResi, ['Belum Terbit', 'Belum Diterbitkan', 'Dalam Proses', '-']);
        $lookupId = $returnRecord->biteship_order_id ?? ($returnRecord->biteship_tracking_id ?? (!$isDummyOrEmpty ? $cleanResi : null));

        $biteshipData = null;
        $biteshipHistory = [];
        $trackingLink = null;
        $latestStatus = null;

        if ($lookupId) {
            try {
                $biteship = new \App\Services\BiteshipService();
                $trackRes = $biteship->getTracking($lookupId, $courier);
                if (!empty($trackRes['status']) && !empty($trackRes['data'])) {
                    $biteshipData = $trackRes;
                    $trackingLink = $trackRes['courier']['link'] ?? ($trackRes['link'] ?? null);

                    $rawHistory = $trackRes['data'];
                    foreach ($rawHistory as $h) {
                        $last = end($biteshipHistory);
                        $currStatus = strtolower(trim($h['status'] ?? ''));
                        $lastStatus = $last ? strtolower(trim($last['status'] ?? '')) : null;
                        $currNote   = trim($h['note'] ?? $h['description'] ?? '');
                        $lastNote   = $last ? trim($last['note'] ?? $last['description'] ?? '') : null;
                        $currTime   = trim($h['updated_at'] ?? '');

                        if ($last && $currStatus === $lastStatus && ($currNote === $lastNote || !empty($currStatus))) {
                            if (!empty($currTime)) {
                                $lastKey = array_key_last($biteshipHistory);
                                $biteshipHistory[$lastKey]['updated_at'] = $currTime;
                            }
                            continue;
                        }

                        $biteshipHistory[] = $h;
                    }
                    $biteshipHistory = array_values($biteshipHistory);

                    if (!empty($biteshipHistory)) {
                        $latestStatus = strtolower(end($biteshipHistory)['status'] ?? '');
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Error fetching Biteship return tracking: " . $e->getMessage());
            }
        }

        // Base Milestones
        $timeline = [];

        // Milestone 1: Pengajuan Retur
        $timeline[] = [
            'time'      => $t1,
            'title'     => 'Pengajuan Komplain & Retur Dikirim',
            'desc'      => 'Pengajuan retur beserta rincian alasan dan bukti unboxing berhasil dikirim oleh pembeli.',
            'completed' => true,
            'current'   => ($returnStatus === 'pending'),
            'icon'      => 'refresh'
        ];

        // Milestone 2: Persetujuan Admin & Penerbitan Resi Cashless
        $isApproved = in_array($returnStatus, ['approved', 'completed']);
        $isRejected = ($returnStatus === 'rejected');

        if ($isRejected) {
            $timeline[] = [
                'time'      => $t2,
                'title'     => 'Pengajuan Retur Ditolak',
                'desc'      => 'Pengajuan retur ditolak oleh admin setelah pemeriksaan bukti. Catatan: ' . ($returnRecord->admin_notes ?: 'Bukti unboxing tidak memenuhi syarat komplain toko.'),
                'completed' => true,
                'current'   => true,
                'icon'      => 'x-circle'
            ];

            return [
                'current_step'   => 4,
                'total_steps'    => 4,
                'status_title'   => 'Pengajuan Retur Ditolak',
                'status_desc'    => 'Pengajuan retur tidak dapat disetujui. Silakan hubungi pusat bantuan kami jika Anda memiliki pertanyaan.',
                'timeline'       => $timeline,
                'tracking_link'  => null,
                'is_live'        => false,
            ];
        }

        $timeline[] = [
            'time'      => $t2,
            'title'     => 'Klaim Retur Disetujui Penjual',
            'desc'      => !empty($resi) 
                ? "Admin toko telah menyetujui garansi retur Anda. Nomor Resi Cashless ({$courierName}: {$resi}) telah diterbitkan otomatis."
                : "Admin toko telah menyetujui klaim retur. Menunggu penerbitan label ekspedisi.",
            'completed' => $isApproved,
            'current'   => ($isApproved && empty($biteshipHistory)),
            'icon'      => 'check'
        ];

        // Milestone 3+: Checkpoint Realtime dari Biteship
        if (!empty($biteshipHistory)) {
            $count = count($biteshipHistory);
            foreach ($biteshipHistory as $idx => $h) {
                $isLatest = ($idx === $count - 1 && $returnStatus !== 'completed');
                $hTime = !empty($h['updated_at']) ? date('d M Y, H:i', strtotime($h['updated_at'])) . ' WIB' : '-';
                $rawNote = $h['note'] ?? $h['description'] ?? 'Pembaruan ekspedisi retur.';
                $hStatus = strtolower($h['status'] ?? '');

                $hNote = self::formatReturnNote($rawNote, $courierName, $sellerName);

                $titleMap = [
                    'confirmed'    => "Nomor Resi Siap Digunakan (Drop-off di Gerai)",
                    'allocated'    => "Gerai {$courierName} Siap Menerima Paket",
                    'picking_up'   => "Menunggu Penyerahan Paket di Counter ({$courierName})",
                    'picked'       => "Paket Retur Diterima di Gerai ({$courierName})",
                    'in_transit'   => "Paket Retur Dalam Perjalanan Menuju Gudang Toko",
                    'dropping_off' => "Kurir Mengantar Paket ke Alamat Toko Penjual",
                    'delivered'    => "Paket Retur Telah Tiba di Gudang Toko Penjual",
                    'on_hold'      => "Pengiriman Retur Tertahan Sementara",
                    'returned'     => "Pengiriman Retur Mengalami Kendala",
                    'cancelled'    => "Pengiriman Retur Dibatalkan",
                ];

                $hTitle = $titleMap[$hStatus] ?? (ucwords(str_replace('_', ' ', $hStatus)) ?: 'Update Ekspedisi Retur');

                $timeline[] = [
                    'time'      => $hTime,
                    'title'     => $hTitle,
                    'desc'      => $hNote,
                    'completed' => true,
                    'current'   => $isLatest,
                    'icon'      => $isLatest ? 'truck' : 'check'
                ];
            }
        } elseif ($isApproved) {
            // Jika resi sudah ada tapi pembeli belum drop-off ke gerai
            $timeline[] = [
                'time'      => 'Menunggu Penyerahan',
                'title'     => 'Menyerahkan Paket ke Gerai Kurir ' . $courierName,
                'desc'      => "Bawa paket ke gerai {$courierName} terdekat dan tunjukkan Nomor Resi {$resi}. Sistem cashless otomatis, tanpa biaya tunai.",
                'completed' => false,
                'current'   => true,
                'icon'      => 'truck'
            ];
        }

        // Milestone Terakhir: Refund Selesai / Tukar Barang Selesai (Hanya muncul jika sudah selesai diverifikasi)
        if ($returnStatus === 'completed') {
            $solutionText = ($returnRecord->solution_type === 'replacement') 
                ? 'Paket retur telah diperiksa & produk pengganti baru telah dikirimkan ke pembeli.' 
                : 'Paket retur telah diterima di gudang toko. Transaksi selesai dan pengembalian dana telah berhasil ditransfer ke pembeli.';

            $timeline[] = [
                'time'      => $t2,
                'title'     => ($returnRecord->solution_type === 'replacement') ? 'Penggantian Barang Selesai' : 'Retur Selesai & Dana Dikembalikan',
                'desc'      => $solutionText,
                'completed' => true,
                'current'   => true,
                'icon'      => 'check-circle'
            ];
        }

        // Hitung Current Step dan Status Header
        $currentStep = 2; // Default jika sudah approved (tahap pembeli bawa paket ke gerai)
        $statusTitle = 'Menunggu Pembeli Menyerahkan Paket ke Gerai ' . $courierName;
        $statusDesc  = "Bawa paket retur ke counter/gerai {$courierName} terdekat dan tunjukkan Nomor Resi {$resi}. Bebas ongkos kirim (Cashless).";

        if ($returnStatus === 'pending') {
            $currentStep = 1;
            $statusTitle = 'Menunggu Peninjauan Admin';
            $statusDesc  = 'Bukti komplain & foto/video unboxing Anda sedang ditinjau oleh tim customer care kami.';
        } elseif ($returnStatus === 'completed') {
            $currentStep = 4;
            $statusTitle = 'Retur Selesai & Refund Berhasil';
            $statusDesc  = 'Barang telah diterima di gudang toko dan dana transaksi telah berhasil dikembalikan ke pembeli.';
        } elseif ($latestStatus === 'delivered') {
            $currentStep = 3;
            $statusTitle = 'Paket Retur Telah Tiba di Gudang Toko';
            $statusDesc  = 'Paket fisik telah sampai di gudang toko. Admin sedang memeriksa fisik produk sebelum menyelesaikan pengembalian dana.';
        } elseif (in_array($latestStatus, ['in_transit', 'dropping_off'])) {
            $currentStep = 3;
            $statusTitle = 'Paket Retur Dalam Perjalanan ke Gudang Penjual';
            $statusDesc  = "Paket telah diterima di gerai dan sedang dikirim oleh ekspedisi {$courierName} menuju alamat toko penjual.";
        } elseif ($latestStatus === 'picked') {
            $currentStep = 3;
            $statusTitle = 'Paket Retur Telah Diserahkan di Gerai ' . $courierName;
            $statusDesc  = "Paket retur telah berhasil diserahkan pembeli dan diterima oleh petugas counter {$courierName}. Paket sedang dipersiapkan untuk dikirim ke penjual.";
        } elseif (in_array($latestStatus, ['picking_up', 'allocated', 'confirmed'])) {
            $currentStep = 2;
            $statusTitle = 'Nomor Resi Cashless Aktif (Siap Drop-off ke Gerai)';
            $statusDesc  = "Nomor resi retur telah aktif resmi. Silakan bawa paket ke counter/gerai {$courierName} terdekat tanpa membayar ongkos kirim.";
        }

        return [
            'current_step'   => $currentStep,
            'total_steps'    => 4,
            'status_title'   => $statusTitle,
            'status_desc'    => $statusDesc,
            'timeline'       => $timeline,
            'tracking_link'  => $trackingLink,
            'is_live'        => !empty($biteshipHistory),
            'courier_name'   => $courierName,
            'waybill_number' => $resi,
            'latest_status'  => $latestStatus,
        ];
    }

    public static function formatReturnNote(string $note, string $courierName = '', string $sellerName = 'Gudang Toko'): string
    {
        $low = strtolower($note);

        if (str_contains($low, 'courier order is confirmed')) {
            return "Nomor resi retur telah terverifikasi resmi oleh sistem ekspedisi " . $courierName . ". Silakan bawa paket ke gerai/counter terdekat tanpa membayar ongkir tunai.";
        }

        if (str_contains($low, 'allocated and ready to pick up') || str_contains($low, 'courier is allocated')) {
            return "Gerai/counter " . $courierName . " telah siap menerima paket retur Anda. Silakan bawa paket dan tunjukkan nomor resi cashless ke petugas.";
        }

        if (str_contains($low, 'on the way to pick up') || str_contains($low, 'picking_up')) {
            return "Menunggu pembeli menyerahkan fisik barang retur ke counter/gerai " . $courierName . " terdekat.";
        }

        if (str_contains($low, 'picked by courier') || str_contains($low, 'has been picked')) {
            return "Paket retur telah berhasil diserahkan oleh pembeli dan diterima di gerai " . $courierName . ". Paket mulai diproses untuk dikirimkan ke gudang toko penjual.";
        }

        if (str_contains($low, 'on the way to destination') || str_contains($low, 'in transit')) {
            return "Paket retur sedang dalam perjalanan pengiriman antar kota menuju alamat gudang toko penjual ({$sellerName}).";
        }

        if (str_contains($low, 'dropping off item to destination') || str_contains($low, 'dropping off')) {
            return "Kurir " . $courierName . " sedang mengantarkan paket retur langsung ke alamat gudang penjual ({$sellerName}).";
        }

        if (str_contains($low, 'delivered') || str_contains($low, 'order has been delivered')) {
            return "Paket retur telah sukses diterima di lokasi gudang toko ({$sellerName}). Tim toko akan segera memeriksa kelengkapan barang retur.";
        }

        return preg_replace('/biteship/i', 'Layanan Ekspedisi', $note);
    }

    public static function formatProfessionalNote(string $note, string $courierName = '', string $recipientName = 'Pelanggan'): string
    {
        $low = strtolower($note);

        if (str_contains($low, 'on hold') || str_contains($low, 'shipment issue')) {
            return "Pengiriman paket tertahan sementara oleh pihak ekspedisi untuk verifikasi rute / kendala operasional sebelum diberangkatkan kembali.";
        }

        if (str_contains($low, 'courier order is confirmed')) {
            return "Pengiriman telah diverifikasi. Kurir " . $courierName . " dalam proses penjemputan paket ke lokasi toko.";
        }

        if (str_contains($low, 'allocated and ready to pick up') || str_contains($low, 'courier is allocated')) {
            return "Kurir pengirim " . $courierName . " telah ditugaskan dan siap mengambil paket dari gudang toko penjual.";
        }

        if (str_contains($low, 'on the way to pick up')) {
            return "Kurir " . $courierName . " sedang dalam perjalanan menuju lokasi toko penjual untuk mengambil paket.";
        }

        if (str_contains($low, 'picked by courier') || str_contains($low, 'has been picked')) {
            return "Paket telah sukses diserahkan ke kurir " . $courierName . " dan sedang dibawa menuju pusat transit ekspedisi.";
        }

        if (str_contains($low, 'on the way to destination') || str_contains($low, 'in transit')) {
            return "Paket telah selesai diproses di pusat transit dan sedang dalam perjalanan pengiriman ke kota tujuan.";
        }

        if (str_contains($low, 'dropping off item to destination') || str_contains($low, 'dropping off')) {
            return "Kurir " . $courierName . " sedang mengantarkan paket langsung ke alamat penerima ({$recipientName}).";
        }

        if (str_contains($low, 'delivered') || str_contains($low, 'order has been delivered')) {
            return "Paket telah sukses diserahterimakan di alamat tujuan ({$recipientName}). Silakan periksa produk Anda & klik 'Pesanan Diterima' di bawah.";
        }

        return preg_replace('/biteship/i', 'Layanan Ekspedisi', $note);
    }
}

