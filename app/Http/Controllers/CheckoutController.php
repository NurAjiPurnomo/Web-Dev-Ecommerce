<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreated;
use App\Services\BiteshipService;

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

        $storeSetting = \App\Models\StoreSetting::getSettings();
        $storeOrigin = $storeSetting->city ?? 'Jakarta Pusat';

        // Wajib melengkapi alamat sebelum checkout
        if (empty($user->address) || empty($user->city_id)) {
            return redirect()->route('profile')->with('error', 'Silakan lengkapi Alamat Tujuan (termasuk Provinsi & Kota) Anda di menu Profil sebelum melakukan checkout.');
        }

        // Data Alamat Tujuan Pengiriman (Diambil asli dari database User)
        $address = [
            'recipient_name' => $user->name,
            'phone'          => $user->phone ?? '-',
            'address_label'  => 'Alamat Rumah Anda',
            'full_address'   => $user->address,
            'city_province'  => $user->city ?? '',
            'city_id'        => $user->biteship_area_id ?: ($user->city_id ?: 'IDNP6IDCU31IDD327'),
            'postal_code'    => $user->postal_code ?? '-',
        ];

        // Hitung total berat estimasi produk (dalam gram)
        $totalWeight = 0;
        foreach ($checkoutItems as $item) {
            $weight = $item['weight'] ?? 500; // default 500 gram per barang jika tidak diset
            $totalWeight += ($weight * max(1, (int) ($item['qty'] ?? 1)));
        }
        $totalWeight = max(1000, $totalWeight); // minimal 1000 gram (1 kg)

        // Pilihan Kurir (Awalnya kosong, akan diisi via AJAX dari Biteship API)
        $couriers = [];

        // Pilihan Metode Pembayaran Lengkap
        $paymentMethods = [
            [
                'id' => 'va',
                'category' => 'Virtual Account (Transfer Bank)',
                'badge' => 'Otomatis',
                'badgeBg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'methods' => [
                    ['id' => 'bca_va', 'name' => 'BCA Virtual Account', 'logo' => 'assets/Bca.png', 'desc' => 'Bayar via BCA Mobile, KlikBCA, atau ATM BCA', 'badge' => 'Bebas Biaya'],
                    ['id' => 'mandiri_va', 'name' => 'Mandiri Virtual Account', 'logo' => 'assets/Mandiri.png', 'desc' => 'Bayar via Livin\' by Mandiri atau ATM Mandiri', 'badge' => 'Bebas Biaya'],
                    ['id' => 'bri_va', 'name' => 'BRI Virtual Account (BRIVA)', 'logo' => 'assets/bri.svg', 'desc' => 'Bayar via BRImo, Internet Banking, atau ATM BRI', 'badge' => 'Bebas Biaya'],
                    ['id' => 'bni_va', 'name' => 'BNI Virtual Account', 'logo' => 'assets/bni.svg', 'desc' => 'Bayar via BNI Mobile Banking atau ATM BNI', 'badge' => 'Bebas Biaya'],
                    ['id' => 'permata_va', 'name' => 'Permata Virtual Account', 'logo' => 'assets/permata.svg', 'desc' => 'Bayar via PermataMobile X atau ATM Permata', 'badge' => 'Bebas Biaya'],
                    ['id' => 'cimb_va', 'name' => 'CIMB Niaga Virtual Account', 'logo' => 'assets/cimb.svg', 'desc' => 'Bayar via OCTO Mobile atau ATM CIMB Niaga', 'badge' => 'Bebas Biaya'],
                    ['id' => 'danamon_va', 'name' => 'Danamon Virtual Account', 'logo' => 'assets/danamon.svg', 'desc' => 'Bayar via D-Bank PRO atau ATM Danamon', 'badge' => 'Bebas Biaya'],
                    ['id' => 'bsi_va', 'name' => 'BSI Virtual Account', 'logo' => 'assets/bsi.svg', 'desc' => 'Bayar via BSI Mobile atau ATM BSI', 'badge' => 'Bebas Biaya'],
                ],
            ],
            [
                'id' => 'qris_ewallet',
                'category' => 'E-Wallet & QRIS',
                'badge' => 'Instan',
                'badgeBg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'methods' => [
                    ['id' => 'qris', 'name' => 'QRIS (Semua Bank & E-Wallet)', 'logo' => 'assets/Qris.png', 'desc' => 'Scan via GoPay, OVO, ShopeePay, DANA, BCA, Livin, BRImo, dll.', 'badge' => 'Populer'],
                    ['id' => 'ovo', 'name' => 'OVO', 'logo' => 'assets/ovo.svg', 'desc' => 'Notifikasi pembayaran langsung dikirim ke aplikasi OVO Anda', 'badge' => 'Instan'],
                    ['id' => 'shopeepay', 'name' => 'ShopeePay', 'logo' => 'assets/shopeepay.svg', 'desc' => 'Bayar praktis menggunakan saldo ShopeePay', 'badge' => 'Instan'],
                    ['id' => 'dana', 'name' => 'DANA', 'logo' => 'assets/dana.svg', 'desc' => 'Bayar langsung via aplikasi DANA', 'badge' => 'Instan'],
                    ['id' => 'linkaja', 'name' => 'LinkAja', 'logo' => 'assets/linkaja.svg', 'desc' => 'Bayar cepat dengan saldo LinkAja Anda', 'badge' => 'Instan'],
                ],
            ],
            [
                'id' => 'ritel',
                'category' => 'Gerai Ritel (Minimarket)',
                'badge' => 'Tunai Kasir',
                'badgeBg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'methods' => [
                    ['id' => 'alfamart', 'name' => 'Alfamart / Lawson / Dan+Dan', 'logo' => 'assets/alfamart.svg', 'desc' => 'Tunjukkan kode pembayaran ke kasir Alfamart terdekat', 'badge' => 'Tunai'],
                    ['id' => 'indomaret', 'name' => 'Indomaret / Ceriamart', 'logo' => 'assets/indomaret.svg', 'desc' => 'Tunjukkan kode pembayaran ke kasir Indomaret terdekat', 'badge' => 'Tunai'],
                ],
            ],
            [
                'id' => 'paylater',
                'category' => 'PayLater & Cicilan',
                'badge' => 'Cicilan',
                'badgeBg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'methods' => [
                    ['id' => 'kredivo', 'name' => 'Kredivo PayLater', 'logo' => 'assets/kredivo.svg', 'desc' => 'Bayar dalam 30 hari atau cicilan s.d. 12 bulan', 'badge' => 'Bunga 0%'],
                    ['id' => 'akulaku', 'name' => 'Akulaku PayLater', 'logo' => 'assets/akulaku.svg', 'desc' => 'Cicilan tanpa kartu kredit via Akulaku', 'badge' => 'Mudah'],
                    ['id' => 'indodana', 'name' => 'Indodana PayLater', 'logo' => 'assets/indodana.svg', 'desc' => 'Cicilan praktis & aman terdaftar OJK', 'badge' => 'OJK'],
                ],
            ],
            [
                'id' => 'card',
                'category' => 'Kartu Kredit / Debit Online',
                'badge' => 'Kartu Kredit',
                'badgeBg' => 'bg-sky-50 text-sky-700 border-sky-200',
                'methods' => [
                    ['id' => 'credit_card', 'name' => 'Kartu Kredit / Debit (Visa & Mastercard)', 'logo' => 'assets/Visa.png', 'desc' => 'Pembayaran aman dengan enkripsi 3D Secure OTP', 'badge' => 'Aman'],
                ],
            ],
            [
                'id' => 'cod_group',
                'category' => 'Bayar di Tempat (COD)',
                'badge' => 'Bayar Ditempat',
                'badgeBg' => 'bg-slate-100 text-slate-700 border-slate-200',
                'methods' => [
                    ['id' => 'cod', 'name' => 'COD (Cash On Delivery)', 'logo' => 'assets/cod.svg', 'desc' => 'Bayar tunai ke kurir saat barang tiba di rumah Anda', 'badge' => 'Tunai'],
                ],
            ],
        ];

        // Fetch active DB Vouchers for current user (only claimed and not used)
        if ($user) {
            $dbVouchers = $user->vouchers()
                ->wherePivot('is_used', false)
                ->where('status', 'aktif')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                })
                ->get();
        } else {
            $dbVouchers = collect([]);
        }

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
     * Hitung Ongkos Kirim Real-Time Biteship API (JNE, J&T, SiCepat, POS, TIKI, GoSend, Grab) via AJAX.
     */
    public function calculateShipping(Request $request)
    {
        $store = \App\Models\StoreSetting::getSettings();
        $biteshipService = app(\App\Services\BiteshipService::class);

        $originAreaId = $store->biteship_area_id ?: 'IDNP6IDCU31IDD327';
        if (!str_starts_with($originAreaId, 'IDNP')) {
            $searchQuery = $store->postal_code ?: ($store->city ?: 'Jakarta Pusat');
            $areas = $biteshipService->searchAreas($searchQuery);
            $originAreaId = !empty($areas) ? ($areas[0]['id'] ?? 'IDNP6IDCU31IDD327') : 'IDNP6IDCU31IDD327';
        }

        $destinationAreaId = $request->input('destination_area_id') ?: $request->input('destination_city_id');
        $user = Auth::user();

        if (empty($destinationAreaId) && $user) {
            $destinationAreaId = $user->biteship_area_id ?: $user->city_id;
        }

        // Resolve destination area ID if empty or not in IDNP format
        if (empty($destinationAreaId) || !str_starts_with($destinationAreaId, 'IDNP')) {
            $searchQuery = 'Jakarta Pusat';
            if ($user) {
                $searchQuery = $user->postal_code ?: (($user->district ?: $user->city) ?: 'Jakarta Pusat');
            }
            $areas = $biteshipService->searchAreas($searchQuery);
            if (!empty($areas)) {
                $destinationAreaId = $areas[0]['id'] ?? 'IDNP6IDCU31IDD327';
            } else {
                $destinationAreaId = 'IDNP6IDCU31IDD327';
            }
        }

        // Get Cart Items
        $cart = session()->get('cart', []);
        $items = [];
        $weight = (int) $request->input('weight', 1000);

        if (!empty($cart)) {
            foreach ($cart as $details) {
                $items[] = [
                    'name' => substr($details['name'] ?? 'Produk', 0, 50),
                    'value' => (int) ($details['price'] ?? 10000),
                    'weight' => (int) ($details['weight'] ?? 1000),
                    'quantity' => (int) ($details['qty'] ?? 1),
                ];
            }
        } else {
            $items[] = [
                'name' => 'Pesanan Toko',
                'value' => 50000,
                'weight' => $weight,
                'quantity' => 1,
            ];
        }

        // Calculate Cart Subtotal for Min Order Discount Check
        $subtotal = 0;
        if (!empty($cart)) {
            foreach ($cart as $details) {
                $subtotal += ($details['price'] ?? 0) * ($details['qty'] ?? 1);
            }
        }

        $activeCouriers = $store->active_couriers ?? ['jne', 'jnt', 'sicepat', 'pos', 'tiki', 'gosend', 'grabexpress'];
        $result = $biteshipService->getRates($originAreaId, $destinationAreaId, $items, $activeCouriers);

        $couriers = [];

        if ($result['status'] && !empty($result['data'])) {
            foreach ($result['data'] as $p) {
                $courierCompany = strtoupper($p['company'] ?? '');
                $courierCode = strtolower($p['company'] ?? '');
                $courierType = strtoupper($p['type'] ?? $p['service_type'] ?? '');
                $rawPrice = (int) ($p['price'] ?? 15000);
                $price = \App\Models\StoreSetting::applyCustomRates($rawPrice, $subtotal, $store);
                $duration = $p['duration'] ?? ($p['etd'] ?? '1-3 Hari');
                $logoUrl = $p['courier_logo_url'] ?? $p['logo'] ?? $this->getCourierLogoUrl($courierCode);

                $couriers[] = [
                    'id'          => $courierCode . '_' . strtolower($p['type'] ?? 'reg'),
                    'code'        => $courierCode,
                    'service'     => strtolower($p['type'] ?? 'reg'),
                    'name'        => $courierCompany . ' (' . $courierType . ')',
                    'description' => $p['courier_name'] ?? ($courierCompany . ' ' . $courierType),
                    'etd'         => $duration,
                    'price'       => $price,
                    'logo'        => $logoUrl,
                ];
            }

            return response()->json([
                'status'   => 'success',
                'source'   => 'api',
                'couriers' => $couriers,
            ]);
        }

        return response()->json([
            'status'   => 'error',
            'message'  => $result['message'] ?? 'Gagal mengambil tarif pengiriman dari Biteship. Silakan periksa kembali alamat pengiriman Anda.',
            'couriers' => []
        ], 400);
    }

    private function getCourierLogoUrl(string $code): string
    {
        return BiteshipService::getCourierLogoUrl($code);
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
        // Ambil daftar kode voucher yang SUDAH pernah dipakai oleh user ini
        $userUsedVouchers = $user ? $user->vouchers()
            ->wherePivot('is_used', true)
            ->pluck('code')->toArray() : [];
        // 1. Process Shipping Voucher (Gratis Ongkir - Max 1)
        if (!empty($shippingVoucherCode)) {
            if (!in_array($shippingVoucherCode, $userUsedVouchers)) {
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
            if (!in_array($discountVoucherCode, $userUsedVouchers)) {
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
            if (!in_array($generalVoucherCode, $userUsedVouchers)) {
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
                $prodId = $item['product_id'] ?? $item['id'] ?? null;
                $varText = !empty($item['variant']) ? ' (' . str_replace('Varian: ', '', $item['variant']) . ')' : '';
                \App\Models\OrderItem::create([
                    'order_id'     => $dbOrder->id,
                    'product_id'   => is_numeric($prodId) ? (int)$prodId : null,
                    'product_name' => ($item['name'] ?? 'Produk Pesanan') . $varText,
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
                        $exists = $user->vouchers()->where('voucher_id', $vid)->exists();
                        if ($exists) {
                            $user->vouchers()->updateExistingPivot($vid, [
                                'is_used' => true,
                                'used_at' => now()
                            ]);
                        } else {
                            $user->vouchers()->attach($vid, [
                                'is_used' => true,
                                'used_at' => now()
                            ]);
                        }
                    }
                }
            }
        }

        // Update Stok & Terjual Produk secara otomatis di Database
        foreach ($checkoutItems as $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $qty = max(1, (int) ($item['qty'] ?? 1));

            if ($productId && is_numeric($productId)) {
                $dbProduct = \App\Models\Product::find($productId);
                if ($dbProduct) {
                    $newStock = max(0, $dbProduct->stock - $qty);
                    $newSold  = $dbProduct->sold + $qty;

                    $variants = $dbProduct->variants;
                    if (is_array($variants) && isset($item['color']) && isset($item['size'])) {
                        $updated = false;
                        foreach ($variants as &$var) {
                            if (
                                strcasecmp($var['color'] ?? '', $item['color'] ?? '') === 0 &&
                                strcasecmp($var['size'] ?? '', $item['size'] ?? '') === 0
                            ) {
                                $var['stock'] = max(0, (int)($var['stock'] ?? 0) - $qty);
                                $updated = true;
                                break;
                            }
                        }
                        if ($updated) {
                            $dbProduct->variants = $variants;
                        }
                    }

                    $dbProduct->update([
                        'stock' => $newStock,
                        'sold'  => $newSold,
                        'variants' => $dbProduct->variants
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
                $cartKey = $item['id'] ?? null;
                if ($cartKey && isset($cart[$cartKey])) {
                    unset($cart[$cartKey]);
                }
            }
            session()->put('cart', $cart);
        }

        // --- DOKU PAYMENT INTEGRATION (DIRECT API) ---
        if ($request->payment_method !== 'cod') {
            $clientId = config('services.doku.client_id') ?: env('DOKU_CLIENT_ID', 'DOKU-DUMMY-CLIENT-ID');
            $secretKey = config('services.doku.secret_key') ?: env('DOKU_SECRET_KEY', 'DOKU-DUMMY-SECRET-KEY');
            $isProduction = config('services.doku.is_production') ?: env('DOKU_IS_PRODUCTION', false);
            
            $baseUrl = $isProduction ? 'https://api.doku.com' : 'https://api-sandbox.doku.com';
            
            // Tentukan Target Path DOKU API berdasarkan pilihan metode pembayaran
            $requestTarget = '';
            $method = $request->payment_method;

            if (str_ends_with($method, '_va')) {
                $bankName = str_replace('_va', '', $method);
                $requestTarget = "/{$bankName}-virtual-account/v2/payment-code";
            } elseif ($method === 'qris') {
                $requestTarget = '/qris/v2/generate-code';
            } elseif (in_array($method, ['ovo', 'shopeepay', 'dana', 'linkaja'])) {
                $requestTarget = "/{$method}-ewallet/v2/payment";
            } elseif (in_array($method, ['alfamart', 'indomaret'])) {
                $requestTarget = "/{$method}-online-to-offline/v2/payment-code";
            } elseif (in_array($method, ['kredivo', 'akulaku', 'indodana'])) {
                $requestTarget = "/{$method}-peer-to-peer/v2/payment";
            } elseif ($method === 'credit_card') {
                $requestTarget = '/credit-card/v2/payment';
            } else {
                $requestTarget = '/bca-virtual-account/v2/payment-code';
            }

            // Mock Payment Code Generator jika dalam Mode Simulasi / Key Dummy
            $dummyPaymentCode = null;
            $randNum = rand(100000, 999999);
            if ($method === 'bca_va') $dummyPaymentCode = '88001' . $randNum;
            elseif ($method === 'mandiri_va') $dummyPaymentCode = '88002' . $randNum;
            elseif ($method === 'bri_va') $dummyPaymentCode = '88003' . $randNum;
            elseif ($method === 'bni_va') $dummyPaymentCode = '88004' . $randNum;
            elseif ($method === 'permata_va') $dummyPaymentCode = '88005' . $randNum;
            elseif ($method === 'cimb_va') $dummyPaymentCode = '88006' . $randNum;
            elseif ($method === 'danamon_va') $dummyPaymentCode = '88007' . $randNum;
            elseif ($method === 'bsi_va') $dummyPaymentCode = '88008' . $randNum;
            elseif ($method === 'alfamart') $dummyPaymentCode = 'DOKU-ALFA-' . rand(10000, 99999);
            elseif ($method === 'indomaret') $dummyPaymentCode = 'DOKU-INDO-' . rand(10000, 99999);
            elseif ($method === 'qris') $dummyPaymentCode = '00020101021226580016ID.DOKU.WWW.01189360001100000001';
            else $dummyPaymentCode = strtoupper($method) . '-' . rand(100000, 999999);

            $url = $baseUrl . $requestTarget;
            
            $requestId = uniqid();
            $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");

            $dokuInvoiceNumber = str_replace('/', '-', $orderId);

            $payload = [
                'order' => [
                    'invoice_number' => $dokuInvoiceNumber,
                    'amount' => (int) $totalAmount,
                ],
                'virtual_account_info' => [
                    'expired_time' => 60, // 60 menit
                    'reusable_status' => false,
                ],
                'customer' => [
                    'name' => Auth::user()->name ?? 'Pelanggan',
                    'email' => Auth::user()->email ?? 'customer@example.com',
                ]
            ];


            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $digest = base64_encode(hash('sha256', $jsonPayload, true));

            $signature = $this->generateDokuSignature(
                $clientId, 
                $requestId, 
                $requestTimestamp, 
                $requestTarget, 
                $digest, 
                $secretKey
            );

            try {
                $paymentCode = $dummyPaymentCode;

                if ($clientId !== 'DOKU-DUMMY-CLIENT-ID') {
                    $response = Http::withHeaders([
                        'Client-Id' => $clientId,
                        'Request-Id' => $requestId,
                        'Request-Timestamp' => $requestTimestamp,
                        'Signature' => $signature,
                        'Content-Type' => 'application/json'
                    ])->withBody($jsonPayload, 'application/json')->post($url);

                    if ($response->successful()) {
                        $responseData = $response->json();
                        $paymentCode = $responseData['virtual_account_info']['virtual_account_number'] 
                            ?? $responseData['qris_info']['qr_content']
                            ?? $responseData['payment']['payment_code'] 
                            ?? $dummyPaymentCode;
                    }
                }
                
                // Simpan kode pembayaran ke database & session
                if (isset($dbOrder)) {
                    $dbOrder->update(['payment_code' => $paymentCode]);
                }
                
                $lastOrder = session('last_order');
                if ($lastOrder) {
                    $lastOrder['payment_code'] = $paymentCode;
                    session()->put('last_order', $lastOrder);
                }

                // Kirim Email Invoice
                try {
                    $emailTo = Auth::user()->email;
                    if ($emailTo && isset($dbOrder)) {
                        Mail::to($emailTo)->send(new OrderCreated($dbOrder));
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim email: ' . $e->getMessage());
                }

                return redirect()->route('checkout.success', ['order_id' => str_replace('/', '-', $orderId)])
                    ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran sesuai petunjuk.');

            } catch (\Exception $e) {
                Log::error('DOKU Connection Exception: ' . $e->getMessage());
                return redirect()->route('checkout.success', ['order_id' => str_replace('/', '-', $orderId)])
                    ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
            }
        }

        return redirect()->route('checkout.success', ['order_id' => str_replace('/', '-', $orderId)])
            ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
    }

    /**
     * Live Polling API untuk mengecek secara otomatis (langsung ke DOKU API & DB) apakah pembayaran sudah berhasil.
     */
    public function checkStatus($order_id)
    {
        $cleanInvoice = str_replace('-', '/', $order_id);
        $dashInvoice = str_replace('/', '-', $order_id);

        $order = \App\Models\Order::where('invoice_number', $order_id)
            ->orWhere('invoice_number', $cleanInvoice)
            ->orWhere('invoice_number', $dashInvoice)
            ->first();

        if (!$order) {
            return response()->json(['status' => 'not_found', 'is_paid' => false]);
        }

        $isPaid = in_array($order->status, ['diproses', 'dikirim', 'selesai']);

        if (!$isPaid) {
            try {
                $clientId = config('services.doku.client_id') ?: env('DOKU_CLIENT_ID');
                $secretKey = config('services.doku.secret_key') ?: env('DOKU_SECRET_KEY');
                $isProduction = config('services.doku.is_production') ?: env('DOKU_IS_PRODUCTION', false);
                $baseUrl = $isProduction ? 'https://api.doku.com' : 'https://api-sandbox.doku.com';

                $dokuInvoice = str_replace('/', '-', $order->invoice_number);
                $requestTarget = '/orders/v1/status/' . $dokuInvoice;
                $url = $baseUrl . $requestTarget;

                $requestId = uniqid();
                $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");

                $componentSignature = "Client-Id:" . $clientId . "\n" .
                                      "Request-Id:" . $requestId . "\n" .
                                      "Request-Timestamp:" . $requestTimestamp . "\n" .
                                      "Request-Target:" . $requestTarget;

                $signature = "HMACSHA256=" . base64_encode(hash_hmac('sha256', $componentSignature, $secretKey, true));

                $response = Http::withHeaders([
                    'Client-Id' => $clientId,
                    'Request-Id' => $requestId,
                    'Request-Timestamp' => $requestTimestamp,
                    'Signature' => $signature,
                ])->get($url);

                if ($response->successful()) {
                    $dokuStatus = $response->json('transaction.status');
                    if (in_array(strtoupper($dokuStatus), ['SUCCESS', 'PAID'])) {
                        $order->update(['status' => 'diproses']);
                        $isPaid = true;

                        // Auto-Trigger Biteship Order API: Generasi Resi AWB & Permintaan Pickup Kurir
                        try {
                            app(\App\Services\BiteshipService::class)->processOrderPickup($order);
                        } catch (\Exception $e) {
                            Log::error("checkStatus Biteship Auto-Pickup Error: " . $e->getMessage());
                        }

                        try {
                            $userEmail = $order->user->email ?? null;
                            if ($userEmail) {
                                Mail::to($userEmail)->send(new \App\Mail\PaymentSuccess($order));
                                Log::info("checkStatus DOKU API: Order {$dokuInvoice} status SUCCESS. Email sent to {$userEmail}");
                            }
                        } catch (\Exception $e) {
                            Log::error("checkStatus DOKU API: Gagal kirim email: " . $e->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("checkStatus DOKU API exception: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'       => 'success',
            'order_status' => $order->status,
            'is_paid'      => $isPaid,
        ]);
    }


    /**
     * Halaman Sukses / Invoice Pesanan.
     */
    public function success($order_id = null)

    {
        $order = session('last_order');
        $queryOrderId = request('order_id') ?? $order_id;
        
        $searchInvoice = $queryOrderId ?: ($order['order_id'] ?? null);
        if ($searchInvoice) {
            $normalizedInvoice = str_replace('-', '/', $searchInvoice);
            $dbOrder = \App\Models\Order::with('items')
                ->where('invoice_number', $searchInvoice)
                ->orWhere('invoice_number', $normalizedInvoice)
                ->first();

            if ($dbOrder) {
                if ($order) {
                    $order['status'] = $dbOrder->status;
                    $order['payment_code'] = $dbOrder->payment_code ?: ($order['payment_code'] ?? null);
                    session()->put('last_order', $order);
                } else {
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
                        'payment_code'             => $dbOrder->payment_code,
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
                if (in_array($dbOrder->status, ['belum_bayar', 'belum_dibayar'])) {
                    $dbOrder->update(['status' => 'diproses']);

                    try {
                        $userEmail = $dbOrder->user->email ?? null;
                        if ($userEmail) {
                            \Illuminate\Support\Facades\Mail::to($userEmail)->send(new \App\Mail\PaymentSuccess($dbOrder));
                            \Illuminate\Support\Facades\Log::info("confirmPayment: Email PaymentSuccess berhasil dikirim ke {$userEmail}");
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("confirmPayment: Gagal mengirim email PaymentSuccess: " . $e->getMessage());
                    }
                }
            }

            if (session()->has('last_order')) {
                $last = session('last_order');
                if (isset($dbOrder)) {
                    $last['status'] = $dbOrder->status;
                } else {
                    $last['status'] = 'diproses';
                }
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

    /**
     * Helper: Generate DOKU Signature HMAC-SHA256
     */
    private function generateDokuSignature($clientId, $requestId, $requestTimestamp, $requestTarget, $digest, $secretKey)
    {
        $componentSignature = "Client-Id:" . $clientId . "\n" .
                              "Request-Id:" . $requestId . "\n" .
                              "Request-Timestamp:" . $requestTimestamp . "\n" .
                              "Request-Target:" . $requestTarget . "\n" .
                              "Digest:" . $digest;
                              
        $signature = base64_encode(hash_hmac('sha256', $componentSignature, $secretKey, true));
        
        return "HMACSHA256=" . $signature;
    }
}
