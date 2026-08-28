<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * Tampilkan Halaman Ringkasan Checkout & Pembayaran.
     */
    public function index(Request $request)
    {
        $isDirectBuy = $request->has('direct') || session()->has('buy_now_item');

        // Jika Beli Sekarang -> tampilkan HANYA 1 produk yang dibeli langsung
        if ($isDirectBuy && session()->has('buy_now_item')) {
            $checkoutItems = [ session('buy_now_item') ];
        } else {
            // Jika dari Halaman Keranjang -> tampilkan produk yang dicentang di keranjang
            $cart = session()->get('cart', []);
            $checkoutItems = array_filter($cart, function ($item) {
                return !isset($item['selected']) || $item['selected'] == true;
            });

            if (empty($checkoutItems)) {
                $checkoutItems = $cart;
            }
        }

        if (empty($checkoutItems)) {
            return redirect()->route('catalog')
                ->with('info', 'Belum ada produk yang dipilih untuk checkout. Silakan pilih produk terlebih dahulu.');
        }

        $user = Auth::user();

        // Data Lokasi Asal Toko / Pengirim (Configurable via RAJAONGKIR_ORIGIN_CITY di .env)
        $originCityId = config('services.rajaongkir.origin_city', '152');
        $storeOrigin = [
            'city_id'   => $originCityId,
            'city_name' => 'Kota Jakarta Pusat',
            'province'  => 'DKI Jakarta',
            'label'     => 'Gudang Utama TokoOnline (Jakarta Pusat)',
        ];

        // Data Alamat Pengiriman (Diambil otomatis dari database User)
        $address = [
            'recipient_name' => $user->name ?? 'Budi Santoso',
            'phone'          => $user->phone ?? '081234567890',
            'address_label'  => 'Rumah Utama Pembeli',
            'full_address'   => $user->address ?: 'Jl. Jendral Sudirman No. 45, RT 03 / RW 05',
            'city_province'  => ($user->city ? $user->city : 'Jakarta Selatan, DKI Jakarta'),
            'city_id'        => $user->city_id ?? '153', // Default Jakarta Selatan (ID 153)
            'postal_code'    => $user->postal_code ?: '12190',
        ];

        // Hitung total berat estimasi produk (dalam gram)
        $totalWeight = 0;
        foreach ($checkoutItems as $item) {
            $weight = $item['weight'] ?? 500; // default 500 gram per barang jika tidak diset
            $totalWeight += ($weight * max(1, (int) ($item['qty'] ?? 1)));
        }
        $totalWeight = max(1000, $totalWeight); // minimal 1000 gram (1 kg)

        // Pilihan Kurir Default (JNE, POS, TIKI)
        $couriers = [
            [
                'id'          => 'jne_reg',
                'code'        => 'jne',
                'service'     => 'REG',
                'name'        => 'JNE Express (Reguler)',
                'description' => 'Layanan Reguler JNE',
                'etd'         => '1-2 Hari',
                'price'       => 15000,
                'logo'        => 'assets/jne.png',
            ],
            [
                'id'          => 'pos_kilat',
                'code'        => 'pos',
                'service'     => 'Pos Kilat Khusus',
                'name'        => 'POS Indonesia (Kilat Khusus)',
                'description' => 'Layanan Kilat Khusus POS',
                'etd'         => '2-3 Hari',
                'price'       => 14000,
                'logo'        => 'assets/jne.png',
            ],
            [
                'id'          => 'tiki_reg',
                'code'        => 'tiki',
                'service'     => 'REG',
                'name'        => 'TIKI (Reguler)',
                'description' => 'Layanan Reguler TIKI',
                'etd'         => '2-3 Hari',
                'price'       => 16000,
                'logo'        => 'assets/jne.png',
            ],
        ];

        // Pilihan Metode Pembayaran
        $paymentMethods = [
            [
                'category' => 'Virtual Account / Transfer Bank',
                'methods'  => [
                    ['id' => 'bca_va', 'name' => 'BCA Virtual Account', 'logo' => 'assets/bca.png', 'fee' => 0],
                    ['id' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'logo' => 'assets/mandiri.png', 'fee' => 0],
                    ['id' => 'bri_va', 'name' => 'BRI Virtual Account', 'logo' => 'assets/qris.png', 'fee' => 0],
                ],
            ],
            [
                'category' => 'E-Wallet & QRIS Instant',
                'methods'  => [
                    ['id' => 'qris', 'name' => 'QRIS (GoPay, OVO, ShopeePay, DANA, LinkAja)', 'logo' => 'assets/qris.png', 'fee' => 0],
                ],
            ],
            [
                'category' => 'Kartu Kredit / Debit',
                'methods'  => [
                    ['id' => 'visa_master', 'name' => 'Kartu Kredit / Debit (Visa & Mastercard)', 'logo' => 'assets/visa.png', 'fee' => 0],
                ],
            ],
            [
                'category' => 'Bayar di Tempat',
                'methods'  => [
                    ['id' => 'cod', 'name' => 'COD (Bayar Tunai Saat Kurir Tiba)', 'logo' => 'assets/jne.png', 'fee' => 2500],
                ],
            ],
        ];

        // Fetch active DB Vouchers
        $dbVouchers = \App\Models\Voucher::where('status', 'aktif')->get();
        $dbShippingVouchers = [];
        $dbDiscountVouchers = [];

        foreach ($dbVouchers as $v) {
            if ($v->type === 'gratis_ongkir') {
                $dbShippingVouchers[] = [
                    'id'            => 'v_' . $v->id,
                    'code'          => $v->code,
                    'category'      => 'shipping',
                    'title'         => 'Gratis Ongkir s.d. Rp ' . number_format($v->discount_value, 0, ',', '.'),
                    'minSpend'      => (int) $v->min_spend,
                    'discountType'  => 'shipping',
                    'discountValue' => (int) $v->discount_value,
                    'description'   => 'Min. belanja Rp ' . number_format($v->min_spend, 0, ',', '.') . ' khusus potongan ongkir',
                    'expiry'        => $v->expires_at ? 'Berlaku s.d. ' . $v->expires_at->format('d M Y') : 'Berlaku terbatas',
                    'badge'         => 'GRATIS ONGKIR',
                    'badgeBg'       => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                ];
            } else {
                $isPercent = ($v->type === 'diskon_persen');
                $dbDiscountVouchers[] = [
                    'id'            => 'v_' . $v->id,
                    'code'          => $v->code,
                    'category'      => 'discount',
                    'title'         => $isPercent ? ('Diskon ' . $v->discount_value . '% (s.d. Rp ' . number_format($v->max_discount ?: 100000, 0, ',', '.') . ')') : ('Potongan Harga Rp ' . number_format($v->discount_value, 0, ',', '.')),
                    'minSpend'      => (int) $v->min_spend,
                    'discountType'  => $isPercent ? 'percent' : 'fixed',
                    'discountValue' => (int) $v->discount_value,
                    'maxDiscount'   => $v->max_discount ? (int) $v->max_discount : null,
                    'description'   => 'Min. belanja Rp ' . number_format($v->min_spend, 0, ',', '.') . ' khusus potongan harga produk',
                    'expiry'        => $v->expires_at ? 'Berlaku s.d. ' . $v->expires_at->format('d M Y') : 'Berlaku terbatas',
                    'badge'         => $isPercent ? 'DISKON PERSEN' : 'POTONGAN HARGA',
                    'badgeBg'       => $isPercent ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-blue-100 text-blue-800 border-blue-200'
                ];
            }
        }

        return view('pages.checkout', [
            'checkoutItems'      => array_values($checkoutItems),
            'storeOrigin'        => $storeOrigin,
            'address'            => $address,
            'totalWeight'        => $totalWeight,
            'couriers'           => $couriers,
            'paymentMethods'     => $paymentMethods,
            'dbShippingVouchers' => $dbShippingVouchers,
            'dbDiscountVouchers' => $dbDiscountVouchers,
        ]);
    }

    /**
     * Hitung Ongkos Kirim Real-Time RajaOngkir (JNE, POS, TIKI) via AJAX.
     */
    public function calculateShipping(Request $request)
    {
        $destinationCityId = $request->input('destination_city_id', '153');
        $weight = max(1000, (int) $request->input('weight', 1000));
        $originCityId = config('services.rajaongkir.origin_city', '152');
        $apiKey = config('services.rajaongkir.api_key') ?: env('RAJAONGKIR_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'status' => 'fallback',
                'message' => 'API Key RajaOngkir belum dikonfigurasi di file .env. Menggunakan tarif standar.',
                'couriers' => $this->getFallbackCouriers($destinationCityId, $weight)
            ]);
        }

        $courierCodes = ['jne', 'pos', 'tiki'];
        $results = [];

        foreach ($courierCodes as $code) {
            try {
                $response = Http::withoutVerifying()
                    ->connectTimeout(1)
                    ->timeout(1.5)
                    ->withHeaders([
                        'key' => $apiKey,
                    ])
                    ->asForm()
                    ->post('https://api.rajaongkir.com/starter/cost', [
                        'origin'      => $originCityId,
                        'destination' => $destinationCityId,
                        'weight'      => $weight,
                        'courier'     => $code,
                    ]);

                if ($response->successful()) {
                    $courierData = $response->json('rajaongkir.results.0');
                    if ($courierData && !empty($courierData['costs'])) {
                        $courierName = $courierData['name'] ?? strtoupper($code);

                        foreach ($courierData['costs'] as $c) {
                            $serviceCode = $c['service'];
                            $description = $c['description'] ?? $serviceCode;
                            $costData    = $c['cost'][0] ?? [];
                            $price       = (int) ($costData['value'] ?? 15000);
                            $etd         = isset($costData['etd']) ? str_replace(['HARI', 'hari'], '', $costData['etd']) . ' Hari' : '1-3 Hari';

                            $results[] = [
                                'id'          => $code . '_' . strtolower($serviceCode),
                                'code'        => $code,
                                'service'     => $serviceCode,
                                'name'        => strtoupper($code) . ' (' . $serviceCode . ')',
                                'description' => $description,
                                'etd'         => $etd,
                                'price'       => $price,
                                'logo'        => 'assets/jne.png',
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("RajaOngkir calculateShipping ({$code}) Error: " . $e->getMessage());
            }
        }

        // Jika API berhasil mendapatkan hasil
        if (!empty($results)) {
            return response()->json([
                'status'   => 'success',
                'source'   => 'api',
                'couriers' => $results
            ]);
        }

        // Jika API tidak memberikan hasil atau timeout, kembalikan data fallback
        return response()->json([
            'status'   => 'fallback',
            'message'  => 'Menggunakan estimasi tarif lokal.',
            'couriers' => $this->getFallbackCouriers($destinationCityId, $weight)
        ]);
    }

    /**
     * Tarif kurir dinamis berbasis jarak kota tujuan dan berat jika API RajaOngkir offline / timeout.
     */
    private function getFallbackCouriers($destinationCityId, $weight): array
    {
        $kg = max(1, (int) ceil($weight / 1000));

        // Matrix Tarif Dasar per KG berdasarkan ID Kota Tujuan (Origin: Jakarta Pusat ID 152)
        $distanceRates = [
            // Jabodetabek (Jakarta, Depok, Bekasi, Bogor) -> Jarak sangat dekat
            '151' => ['jne' => 9000,  'pos' => 8000,  'tiki' => 9500,  'etd_jne' => '1 Hari',   'etd_pos' => '1-2 Hari', 'etd_tiki' => '1 Hari'],
            '152' => ['jne' => 9000,  'pos' => 8000,  'tiki' => 9500,  'etd_jne' => '1 Hari',   'etd_pos' => '1-2 Hari', 'etd_tiki' => '1 Hari'],
            '153' => ['jne' => 9000,  'pos' => 8000,  'tiki' => 9500,  'etd_jne' => '1 Hari',   'etd_pos' => '1-2 Hari', 'etd_tiki' => '1 Hari'],
            '154' => ['jne' => 9000,  'pos' => 8000,  'tiki' => 9500,  'etd_jne' => '1 Hari',   'etd_pos' => '1-2 Hari', 'etd_tiki' => '1 Hari'],
            '155' => ['jne' => 9000,  'pos' => 8000,  'tiki' => 9500,  'etd_jne' => '1 Hari',   'etd_pos' => '1-2 Hari', 'etd_tiki' => '1 Hari'],
            '54'  => ['jne' => 10000, 'pos' => 9000,  'tiki' => 10000, 'etd_jne' => '1-2 Hari', 'etd_pos' => '1-2 Hari', 'etd_tiki' => '1-2 Hari'],
            '78'  => ['jne' => 10000, 'pos' => 9000,  'tiki' => 10000, 'etd_jne' => '1-2 Hari', 'etd_pos' => '1-2 Hari', 'etd_tiki' => '1-2 Hari'],
            '115' => ['jne' => 10000, 'pos' => 9000,  'tiki' => 10000, 'etd_jne' => '1-2 Hari', 'etd_pos' => '1-2 Hari', 'etd_tiki' => '1-2 Hari'],

            // Jawa Barat (Bandung, dll) -> Jarak dekat
            '22'  => ['jne' => 12000, 'pos' => 11000, 'tiki' => 12500, 'etd_jne' => '1-2 Hari', 'etd_pos' => '2 Hari',   'etd_tiki' => '1-2 Hari'],
            '23'  => ['jne' => 12000, 'pos' => 11000, 'tiki' => 12500, 'etd_jne' => '1-2 Hari', 'etd_pos' => '2 Hari',   'etd_tiki' => '1-2 Hari'],

            // Jawa Tengah & Yogyakarta (Semarang, Solo, Jogja) -> Jarak menengah
            '399' => ['jne' => 16000, 'pos' => 15000, 'tiki' => 16500, 'etd_jne' => '2-3 Hari', 'etd_pos' => '2-3 Hari', 'etd_tiki' => '2-3 Hari'],
            '427' => ['jne' => 16000, 'pos' => 15000, 'tiki' => 16500, 'etd_jne' => '2-3 Hari', 'etd_pos' => '2-3 Hari', 'etd_tiki' => '2-3 Hari'],
            '501' => ['jne' => 16000, 'pos' => 15000, 'tiki' => 16500, 'etd_jne' => '2-3 Hari', 'etd_pos' => '2-3 Hari', 'etd_tiki' => '2-3 Hari'],

            // Jawa Timur (Surabaya, Malang) -> Jarak jauh antarkota Jawa
            '444' => ['jne' => 19000, 'pos' => 18000, 'tiki' => 19500, 'etd_jne' => '2-3 Hari', 'etd_pos' => '3 Hari',   'etd_tiki' => '2-3 Hari'],
            '256' => ['jne' => 20000, 'pos' => 19000, 'tiki' => 20500, 'etd_jne' => '2-3 Hari', 'etd_pos' => '3 Hari',   'etd_tiki' => '2-3 Hari'],

            // Bali (Denpasar) -> Luar Pulau (Jarak jauh)
            '114' => ['jne' => 25000, 'pos' => 23000, 'tiki' => 25500, 'etd_jne' => '3-4 Hari', 'etd_pos' => '3-4 Hari', 'etd_tiki' => '3-4 Hari'],

            // Sumatera (Medan) -> Luar Pulau (Jarak sangat jauh)
            '278' => ['jne' => 34000, 'pos' => 32000, 'tiki' => 35000, 'etd_jne' => '3-5 Hari', 'etd_pos' => '4-5 Hari', 'etd_tiki' => '3-5 Hari'],

            // Sulawesi (Makassar) -> Luar Pulau (Jarak sangat jauh)
            '254' => ['jne' => 41000, 'pos' => 38000, 'tiki' => 42000, 'etd_jne' => '3-5 Hari', 'etd_pos' => '4-5 Hari', 'etd_tiki' => '3-5 Hari'],
        ];

        // Hitung rumus jika kota tidak ada di matrix khusus (berdasarkan selisih ID kota)
        $defaultRate = $distanceRates[$destinationCityId] ?? [
            'jne'      => 15000 + (abs((int)$destinationCityId - 152) * 50),
            'pos'      => 14000 + (abs((int)$destinationCityId - 152) * 45),
            'tiki'     => 15500 + (abs((int)$destinationCityId - 152) * 50),
            'etd_jne'  => '2-4 Hari',
            'etd_pos'  => '3-5 Hari',
            'etd_tiki' => '2-4 Hari',
        ];

        return [
            [
                'id'          => 'jne_reg',
                'code'        => 'jne',
                'service'     => 'REG',
                'name'        => 'JNE Express (Reguler)',
                'description' => 'Layanan Reguler JNE',
                'etd'         => $defaultRate['etd_jne'],
                'price'       => $defaultRate['jne'] * $kg,
                'logo'        => 'assets/jne.png',
            ],
            [
                'id'          => 'pos_kilat',
                'code'        => 'pos',
                'service'     => 'Pos Kilat Khusus',
                'name'        => 'POS Indonesia (Kilat Khusus)',
                'description' => 'Layanan Kilat Khusus POS',
                'etd'         => $defaultRate['etd_pos'],
                'price'       => $defaultRate['pos'] * $kg,
                'logo'        => 'assets/jne.png',
            ],
            [
                'id'          => 'tiki_reg',
                'code'        => 'tiki',
                'service'     => 'REG',
                'name'        => 'TIKI (Reguler)',
                'description' => 'Layanan Reguler TIKI',
                'etd'         => $defaultRate['etd_tiki'],
                'price'       => $defaultRate['tiki'] * $kg,
                'logo'        => 'assets/jne.png',
            ],
        ];
    }

    /**
     * Proses Pembuatan Pesanan & Pembayaran.
     */
    public function process(Request $request)
    {
        $request->validate([
            'courier_id'     => 'required|string',
            'payment_method' => 'required|string',
        ], [
            'courier_id.required'     => 'Pilih metode pengiriman terlebih dahulu.',
            'payment_method.required' => 'Pilih metode pembayaran terlebih dahulu.',
        ]);

        $isDirectBuy = session()->has('buy_now_item');

        if ($isDirectBuy) {
            $checkoutItems = [ session('buy_now_item') ];
        } else {
            $cart = session()->get('cart', []);
            $checkoutItems = array_filter($cart, function ($item) {
                return !isset($item['selected']) || $item['selected'] == true;
            });
        }

        if (empty($checkoutItems)) {
            return redirect()->route('catalog');
        }

        // Buat Order ID Unik
        $orderId = 'INV/' . date('Ymd') . '/TK/' . strtoupper(substr(md5(uniqid()), 0, 8));

        // Hitung total tagihan
        $subtotal = 0;
        foreach ($checkoutItems as $item) {
            $subtotal += ($item['price'] * $item['qty']);
        }

        $shippingVoucherCode = strtoupper(trim($request->input('shipping_voucher', '')));
        $discountVoucherCode = strtoupper(trim($request->input('discount_voucher', '')));
        $generalVoucherCode  = strtoupper(trim($request->input('voucher_code', '')));

        $shippingCost   = max(0, (int) $request->input('shipping_cost', 15000));
        $courierName    = $request->input('courier_name', $request->courier_id);

        $shippingDiscountAmount = 0;
        $productDiscountAmount  = 0;

        $user = Auth::user();
        $userActiveVouchers = $user ? $user->vouchers()
            ->wherePivot('is_used', false)
            ->where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->pluck('code')->toArray() : [];

        // 1. Process Shipping Voucher (Gratis Ongkir - Max 1)
        if (!empty($shippingVoucherCode)) {
            if (in_array($shippingVoucherCode, $userActiveVouchers)) {
                $sVoucher = \App\Models\Voucher::where('code', $shippingVoucherCode)->where('status', 'aktif')->first();
                if ($sVoucher && $sVoucher->type === 'gratis_ongkir' && $subtotal >= $sVoucher->min_spend) {
                    $shippingDiscountAmount = min((int) $sVoucher->discount_value, $shippingCost);
                }
            } else {
                $shippingVoucherCode = ''; // Invalid/unclaimed voucher
            }
        }

        // 2. Process Product Discount Voucher (Diskon Produk - Max 1)
        if (!empty($discountVoucherCode)) {
            if (in_array($discountVoucherCode, $userActiveVouchers)) {
                $dVoucher = \App\Models\Voucher::where('code', $discountVoucherCode)->where('status', 'aktif')->first();
                if ($dVoucher && in_array($dVoucher->type, ['diskon_nominal', 'diskon_persen']) && $subtotal >= $dVoucher->min_spend) {
                    if ($dVoucher->type === 'diskon_nominal') {
                        $productDiscountAmount = min((int) $dVoucher->discount_value, $subtotal);
                    } else {
                        $perc = (int) $dVoucher->discount_value;
                        $calc = (int) round(($subtotal * $perc) / 100);
                        $productDiscountAmount = min($calc, $dVoucher->max_discount ?: 999999999);
                    }
                }
            } else {
                $discountVoucherCode = ''; // Invalid/unclaimed voucher
            }
        }

        // Fallback for single general voucher input
        if (empty($shippingDiscountAmount) && empty($productDiscountAmount) && !empty($generalVoucherCode)) {
            if (in_array($generalVoucherCode, $userActiveVouchers)) {
                $gVoucher = \App\Models\Voucher::where('code', $generalVoucherCode)->where('status', 'aktif')->first();
                if ($gVoucher && $subtotal >= $gVoucher->min_spend) {
                    if ($gVoucher->type === 'gratis_ongkir') {
                        $shippingDiscountAmount = min((int) $gVoucher->discount_value, $shippingCost);
                        $shippingVoucherCode = $generalVoucherCode;
                    } else if ($gVoucher->type === 'diskon_nominal') {
                        $productDiscountAmount = min((int) $gVoucher->discount_value, $subtotal);
                        $discountVoucherCode = $generalVoucherCode;
                    } else if ($gVoucher->type === 'diskon_persen') {
                        $calc = (int) round(($subtotal * $gVoucher->discount_value) / 100);
                        $productDiscountAmount = min($calc, $gVoucher->max_discount ?: 999999999);
                        $discountVoucherCode = $generalVoucherCode;
                    }
                }
            } else {
                $generalVoucherCode = ''; // Invalid/unclaimed voucher
            }
        }

        $discountAmount = $shippingDiscountAmount + $productDiscountAmount;
        $totalAmount    = max(0, ($subtotal + $shippingCost) - $discountAmount);

        $appliedCodes = array_filter([$shippingVoucherCode, $discountVoucherCode]);
        $appliedVoucherLabel = !empty($appliedCodes) ? implode(' + ', $appliedCodes) : ($generalVoucherCode ?: null);

        // Tentukan status awal pesanan berdasarkan Metode Pembayaran
        // COD -> langsung 'diproses' (Sedang Dikemas)
        // Non-COD (QRIS, Transfer Bank, E-Wallet) -> 'belum_dibayar' (Menunggu Pembayaran)
        $initialStatus = ($request->payment_method === 'cod') ? 'diproses' : 'belum_dibayar';

        // Simpan data pesanan di session untuk invoice/success page
        $order = [
            'order_id'                 => $orderId,
            'user_name'                => Auth::user()->name ?? 'Budi Santoso',
            'user_email'               => Auth::user()->email ?? 'budi@gmail.com',
            'user_phone'               => Auth::user()->phone ?? '081234567890',
            'courier_id'               => $request->courier_id,
            'courier_name'             => $courierName,
            'payment_method'           => $request->payment_method,
            'status'                   => $initialStatus,
            'voucher_code'             => $appliedVoucherLabel,
            'shipping_voucher'         => $shippingVoucherCode,
            'discount_voucher'         => $discountVoucherCode,
            'shipping_discount_amount' => $shippingDiscountAmount,
            'product_discount_amount'  => $productDiscountAmount,
            'discount_amount'          => $discountAmount,
            'subtotal'                 => $subtotal,
            'shipping_cost'            => $shippingCost,
            'total_amount'             => $totalAmount,
            'created_at'               => now()->format('d M Y, H:i') . ' WIB',
            'created_at_timestamp'     => time(),
            'items'                    => array_values($checkoutItems),
        ];

        session()->put('last_order', $order);

        // Simpan Data Pesanan & Item ke Database
        $userId = Auth::id() ?? (session('user.id') ?? null);
        if ($userId) {
            $dbOrder = \App\Models\Order::create([
                'invoice_number'   => $orderId,
                'user_id'          => $userId,
                'subtotal'         => $subtotal,
                'shipping_cost'    => $shippingCost,
                'discount_amount'  => $discountAmount,
                'total'            => $totalAmount,
                'courier'          => $courierName,
                'payment_method'   => $request->payment_method,
                'status'           => $initialStatus,
                'shipping_address' => Auth::user()->address ?? 'Alamat Pengiriman',
                'recipient_name'   => Auth::user()->name ?? 'Pelanggan',
                'recipient_phone'  => Auth::user()->phone ?? '-',
            ]);

            foreach ($checkoutItems as $item) {
                \App\Models\OrderItem::create([
                    'order_id'     => $dbOrder->id,
                    'product_id'   => $item['id'] ?? null,
                    'product_name' => $item['name'] ?? 'Produk Pesanan',
                    'quantity'     => max(1, (int) ($item['qty'] ?? 1)),
                    'price'        => (int) ($item['price'] ?? 0),
                ]);
            }

            // Mark vouchers as used
            $usedCodes = array_filter([$shippingVoucherCode, $discountVoucherCode]);
            if (!empty($usedCodes)) {
                $usedVoucherIds = \App\Models\Voucher::whereIn('code', $usedCodes)->pluck('id')->toArray();
                if (!empty($usedVoucherIds) && $user) {
                    foreach ($usedVoucherIds as $vid) {
                        $user->vouchers()->updateExistingPivot($vid, [
                            'is_used' => true,
                            'used_at' => now()
                        ]);
                    }
                }
            }
        }

        // Update Stok & Terjual Produk secara otomatis di Database
        foreach ($checkoutItems as $item) {
            $productId = $item['id'] ?? null;
            $qty = max(1, (int) ($item['qty'] ?? 1));

            if ($productId) {
                $dbProduct = \App\Models\Product::find($productId);
                if ($dbProduct) {
                    $newStock = max(0, $dbProduct->stock - $qty);
                    $newSold  = $dbProduct->sold + $qty;

                    $dbProduct->update([
                        'stock' => $newStock,
                        'sold'  => $newSold,
                    ]);
                }
            }
        }

        // Hapus status pesanan yang telah dibayar dari keranjang
        if ($isDirectBuy) {
            session()->forget('buy_now_item');
        } else {
            $cart = session()->get('cart', []);
            foreach ($checkoutItems as $item) {
                if (isset($cart[$item['id']])) {
                    unset($cart[$item['id']]);
                }
            }
            session()->put('cart', $cart);
        }

        return redirect()->route('checkout.success', ['order_id' => str_replace('/', '-', $orderId)])
            ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
    }

    /**
     * Halaman Sukses / Invoice Pesanan.
     */
    public function success($order_id = null)
    {
        $order = session('last_order');
        $queryOrderId = request('order_id') ?? $order_id;

        if (!$order && $queryOrderId) {
            $dbOrder = \App\Models\Order::with('items')->where('invoice_number', $queryOrderId)->first();
            if ($dbOrder && \Illuminate\Support\Facades\Auth::check() && $dbOrder->user_id == \Illuminate\Support\Facades\Auth::id()) {
                $order = [
                    'order_id'                 => $dbOrder->invoice_number,
                    'user_name'                => $dbOrder->recipient_name,
                    'user_phone'               => $dbOrder->recipient_phone,
                    'courier_name'             => $dbOrder->courier,
                    'payment_method'           => $dbOrder->payment_method,
                    'status'                   => $dbOrder->status,
                    'subtotal'                 => $dbOrder->subtotal,
                    'shipping_cost'            => $dbOrder->shipping_cost,
                    'discount_amount'          => $dbOrder->discount_amount,
                    'total_amount'             => $dbOrder->total,
                    'created_at'               => $dbOrder->created_at->format('d M Y, H:i') . ' WIB',
                    'created_at_timestamp'     => $dbOrder->created_at->timestamp,
                    'items'                    => $dbOrder->items->map(function($item) {
                        return [
                            'name'  => $item->product_name,
                            'qty'   => $item->quantity,
                            'price' => $item->price,
                            'image' => \App\Models\Product::find($item->product_id)?->image ?? 'assets/placeholder.jpg',
                            'variant' => ''
                        ];
                    })->toArray(),
                ];
            }
        }

        if (!$order) {
            return redirect()->route('home');
        }

        if (empty($order['created_at_timestamp'])) {
            $cleanDate = isset($order['created_at']) ? str_replace(' WIB', '', $order['created_at']) : '';
            $order['created_at_timestamp'] = strtotime($cleanDate) ?: time();
            session()->put('last_order', $order);
        }

        return view('pages.checkout-success', [
            'order' => $order,
        ]);
    }

    /**
     * Update & Simpan Alamat Pengiriman Pengguna ke Database.
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:100',
            'phone'          => 'required|string|max:20',
            'full_address'   => 'required|string|max:500',
            'city_province'  => 'required|string|max:100',
            'postal_code'    => 'required|string|max:10',
        ]);

        $user = Auth::user();
        if ($user) {
            $user->update([
                'name'        => trim($request->recipient_name),
                'phone'       => trim($request->phone),
                'address'     => trim($request->full_address),
                'city'        => trim($request->city_province),
                'postal_code' => trim($request->postal_code),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Alamat pengiriman berhasil disimpan ke database!',
                'address' => [
                    'recipient_name' => $request->recipient_name,
                    'phone'          => $request->phone,
                    'full_address'   => $request->full_address,
                    'city_province'  => $request->city_province,
                    'postal_code'    => $request->postal_code,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Alamat pengiriman berhasil diperbarui!');
    }

    /**
     * Konfirmasi Pembayaran Pesanan (Merubah status dari 'belum_dibayar' menjadi 'diproses').
     */
    public function confirmPayment(Request $request)
    {
        $orderId = $request->input('order_id');
        if (empty($orderId)) {
            $lastOrder = session('last_order');
            $orderId = $lastOrder['order_id'] ?? null;
        }

        if ($orderId) {
            $formattedId = str_replace('-', '/', $orderId);
            $dbOrder = \App\Models\Order::where('invoice_number', $formattedId)
                ->orWhere('invoice_number', $orderId)
                ->first();

            if ($dbOrder) {
                $dbOrder->update(['status' => 'diproses']);
            }

            if (session()->has('last_order')) {
                $last = session('last_order');
                $last['status'] = 'diproses';
                session()->put('last_order', $last);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran Berhasil Dikonfirmasi! Pesanan Anda sedang dikemas.'
            ]);
        }

        return redirect()->route('orders')->with('success', 'Pembayaran Berhasil! Pesanan Anda sedang dikemas oleh penjual.');
    }
}
