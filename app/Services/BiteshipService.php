<?php

namespace App\Services;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BiteshipService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $storeSettings = StoreSetting::getSettings();
        $this->apiKey = !empty($storeSettings->biteship_api_key) ? trim($storeSettings->biteship_api_key) : env('BITESHIP_API_KEY', '');
        $this->baseUrl = env('BITESHIP_BASE_URL', 'https://api.biteship.com/v1');
    }

    /**
     * Search areas in Indonesia by input query (City / District / Postal Code).
     */
    public function searchAreas(string $query)
    {
        if (empty($this->apiKey) || empty($query)) {
            return [];
        }

        $cacheKey = 'biteship_area_' . md5(strtolower($query));
        return Cache::remember($cacheKey, 86400, function () use ($query) {
            try {
                $response = Http::timeout(15)->withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->get($this->baseUrl . '/maps/areas', [
                    'countries' => 'ID',
                    'input' => $query,
                    'type' => 'single'
                ]);

                if ($response->successful()) {
                    return $response->json('areas') ?? [];
                }
                Log::warning('Biteship searchAreas failed: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('Biteship searchAreas error: ' . $e->getMessage());
            }
            return [];
        });
    }

    /**
     * Get shipping rates from origin to destination for selected couriers.
     */
    public function getRates(string $originAreaId, string $destinationAreaId, array $items, array $couriers = [])
    {
        if (empty($this->apiKey)) {
            return [
                'status' => false,
                'message' => 'API Key Biteship belum dikonfigurasi di .env (BITESHIP_API_KEY).'
            ];
        }

        if (empty($couriers)) {
            $storeSettings = StoreSetting::getSettings();
            $couriers = $storeSettings->active_couriers ?? ['jne', 'jnt', 'sicepat', 'pos', 'tiki', 'gosend', 'grabexpress'];
        }

        $courierString = implode(',', array_map('strtolower', $couriers));
        $cacheKey = 'biteship_rates_' . md5($originAreaId . '_' . $destinationAreaId . '_' . serialize($items) . '_' . $courierString);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $payload = [
                'origin_area_id' => $originAreaId,
                'destination_area_id' => $destinationAreaId,
                'couriers' => $courierString,
                'items' => $items,
            ];

            $response = Http::timeout(15)->withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/rates/couriers', $payload);

            if ($response->successful()) {
                $pricing = $response->json('pricing') ?? [];
                $result = [
                    'status' => true,
                    'data' => $pricing,
                ];
                Cache::put($cacheKey, $result, 3600);
                return $result;
            }

            $errorMsg = $response->json('error') ?? $response->json('message') ?? 'Gagal mengambil tarif ongkir dari Biteship.';
            return [
                'status' => false,
                'message' => $errorMsg,
            ];

        } catch (\Exception $e) {
            Log::error('Biteship getRates Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Koneksi ke Biteship error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create Shipping Order in Biteship and retrieve Waybill Number & PDF Shipping Label URL.
     */
    public function createOrder(array $orderData)
    {
        if (empty($this->apiKey)) {
            return [
                'status' => false,
                'message' => 'API Key Biteship belum dikonfigurasi.'
            ];
        }

        try {
            $store = StoreSetting::getSettings();

            $originAreaId = $store->biteship_area_id ?: 'IDNP6IDCU31IDD327';
            if (!str_starts_with($originAreaId, 'IDNP')) {
                $areas = $this->searchAreas($store->postal_code ?: ($store->city ?: 'Jakarta Pusat'));
                $originAreaId = !empty($areas) ? ($areas[0]['id'] ?? 'IDNP6IDCU31IDD327') : 'IDNP6IDCU31IDD327';
            }

            $destinationAreaId = !empty($orderData['destination_area_id']) ? $orderData['destination_area_id'] : 'IDNP6IDCU31IDD327';
            if (!str_starts_with($destinationAreaId, 'IDNP')) {
                $areas = $this->searchAreas($destinationAreaId);
                $destinationAreaId = (!empty($areas) && !empty($areas[0]['id'])) ? $areas[0]['id'] : 'IDNP6IDCU31IDD327';
            }

            $originAddressParts = array_filter([$store->address_detail, $store->village, $store->district, $store->city, $store->province]);
            $originAddress = !empty($originAddressParts) ? implode(', ', $originAddressParts) : ($store->address_detail ?: 'Jl. Garuda No. 12, Tegal');

            $payload = [
                'shipper_contact_name' => $store->sender_name ?: $store->store_name,
                'shipper_contact_phone' => $store->sender_phone ?: '081234567890',
                'shipper_contact_email' => 'store@toko-online.com',
                'origin_contact_name' => $store->sender_name ?: $store->store_name,
                'origin_contact_phone' => $store->sender_phone ?: '081234567890',
                'origin_address' => $originAddress,
                'origin_area_id' => $originAreaId,
                'destination_contact_name' => $orderData['recipient_name'],
                'destination_contact_phone' => $orderData['recipient_phone'],
                'destination_contact_email' => $orderData['recipient_email'] ?? 'customer@toko-online.com',
                'destination_address' => $orderData['shipping_address'],
                'destination_area_id' => $destinationAreaId,
                'courier_company' => strtolower($orderData['courier_code']),
                'courier_type' => strtolower($orderData['courier_service']),
                'delivery_type' => 'now',
                'items' => $orderData['items'],
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/orders', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $biteshipOrderId = $responseData['id'] ?? null;
                $courierTrackingId = $responseData['courier']['tracking_id'] ?? null;
                $waybillId = $responseData['courier']['waybill_id'] ?? $courierTrackingId;
                
                // Get PDF Label URL if available from response or fallback to Biteship label endpoint
                $pdfUrl = $responseData['courier']['waybill_pdf_url'] ?? null;
                if (!$pdfUrl && $biteshipOrderId) {
                    $pdfUrl = "https://biteship.com/orders/{$biteshipOrderId}/waybill"; // Default format or API link
                }

                return [
                    'status' => true,
                    'biteship_order_id' => $biteshipOrderId,
                    'waybill_number' => $waybillId,
                    'waybill_pdf_url' => $pdfUrl,
                    'raw_response' => $responseData,
                ];
            }

            $errMsg = $response->json('error') ?? $response->json('message') ?? 'Gagal membuat pesanan pengiriman di Biteship.';
            Log::error('Biteship createOrder Failed: ' . $response->body());
            return [
                'status' => false,
                'message' => $errMsg,
            ];

        } catch (\Exception $e) {
            Log::error('Biteship createOrder Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi Biteship: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get Live Tracking Status of an Order or Tracking ID.
     */
    public function getTracking(string $trackingIdOrWaybill, string $courierCode = '')
    {
        if (empty($this->apiKey)) {
            return [
                'status' => false,
                'message' => 'API Key Biteship belum dikonfigurasi.'
            ];
        }

        try {
            // First check if tracking ID is a Biteship Order ID (24 hex characters)
            if (strlen($trackingIdOrWaybill) === 24 && ctype_xdigit($trackingIdOrWaybill)) {
                $orderResponse = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->get($this->baseUrl . '/orders/' . $trackingIdOrWaybill);

                if ($orderResponse->successful()) {
                    $json = $orderResponse->json();
                    $rawHistory = $json['courier']['history'] ?? [];
                    $history = self::deduplicateHistory($rawHistory);
                    return [
                        'status' => true,
                        'data' => $history,
                        'courier' => $json['courier'] ?? [],
                        'current_status' => $json['status'] ?? null,
                        'link' => $json['courier']['link'] ?? null,
                        'raw' => $json,
                    ];
                }
            }

            $url = $this->baseUrl . '/trackings/' . $trackingIdOrWaybill;
            if (!empty($courierCode)) {
                $url .= '?courier=' . strtolower($courierCode);
            }

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $json = $response->json();
                $rawHistory = $json['history'] ?? [];
                $history = self::deduplicateHistory($rawHistory);
                return [
                    'status' => true,
                    'data' => $history,
                    'courier' => $json['courier'] ?? [],
                    'current_status' => $json['status'] ?? null,
                    'link' => $json['link'] ?? null,
                    'raw' => $json,
                ];
            }

            return [
                'status' => false,
                'message' => $response->json('message') ?? 'Tracking tidak ditemukan.',
            ];

        } catch (\Exception $e) {
            Log::error('Biteship getTracking Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Gagal mengambil data tracking: ' . $e->getMessage(),
            ];
        }
    }

    public static function normalizeCourierCode(string $courierStr): string
    {
        $str = strtolower($courierStr);
        if (str_contains($str, 'j&t') || str_contains($str, 'jnt')) return 'jnt';
        if (str_contains($str, 'jne')) return 'jne';
        if (str_contains($str, 'sicepat')) return 'sicepat';
        if (str_contains($str, 'pos')) return 'pos';
        if (str_contains($str, 'tiki')) return 'tiki';
        if (str_contains($str, 'anteraja')) return 'anteraja';
        if (str_contains($str, 'wahana')) return 'wahana';
        if (str_contains($str, 'ninja')) return 'ninja';
        if (str_contains($str, 'lion')) return 'lion';
        if (str_contains($str, 'spx') || str_contains($str, 'shopee')) return 'spx';
        if (str_contains($str, 'rpx')) return 'rpx';
        if (str_contains($str, 'paxel')) return 'paxel';
        if (str_contains($str, 'lalamove')) return 'lalamove';
        if (str_contains($str, 'borzo')) return 'borzo';
        if (str_contains($str, 'deliveree')) return 'deliveree';
        if (str_contains($str, 'gosend')) return 'gosend';
        if (str_contains($str, 'grab')) return 'grabexpress';
        return 'jne';
    }

    public static function normalizeCourierService(string $serviceStr): string
    {
        $str = strtolower($serviceStr);
        if (str_contains($str, 'gokil')) return 'gokil';
        if (str_contains($str, 'jtr')) return 'jtr';
        if (str_contains($str, 'halu')) return 'halu';
        if (str_contains($str, 'best')) return 'best';
        if (str_contains($str, 'yes')) return 'yes';
        if (str_contains($str, 'ez')) return 'ez';
        if (str_contains($str, 'reg')) return 'reg';
        if (str_contains($str, 'instant')) return 'instant';
        if (str_contains($str, 'kilat')) return 'kilat_khusus';
        return 'reg';
    }

    public static function getCourierLogoUrl(string $code): string
    {
        $code = self::normalizeCourierCode($code);
        $localPath = public_path("assets/couriers/{$code}.svg");
        if (file_exists($localPath)) {
            return asset("assets/couriers/{$code}.svg");
        }

        $logoMap = [
            'jnt'         => 'https://biteship.com/assets/images/courier-logo/jnt.png',
            'jne'         => 'https://biteship.com/assets/images/courier-logo/jne.png',
            'sicepat'     => 'https://biteship.com/assets/images/courier-logo/sicepat.png',
            'pos'         => 'https://biteship.com/assets/images/courier-logo/pos.png',
            'tiki'        => 'https://biteship.com/assets/images/courier-logo/tiki.png',
            'gosend'      => 'https://biteship.com/assets/images/courier-logo/gosend.png',
            'grabexpress' => 'https://biteship.com/assets/images/courier-logo/grabexpress.png',
        ];

        return $logoMap[$code] ?? asset('assets/couriers/jne.svg');
    }

    /**
     * Create Realtime Biteship Return Order (Reverse Logistics / Cashless Return Waybill).
     */
    public function createReturnOrder(\App\Models\OrderReturn $orderReturn): array
    {
        if (empty($this->apiKey)) {
            return [
                'status' => false,
                'message' => 'API Key Biteship belum dikonfigurasi.'
            ];
        }

        $orderReturn->loadMissing(['order.user', 'order.items.product']);
        $order = $orderReturn->order;
        $user = $orderReturn->user ?: ($order->user ?? null);
        $store = \App\Models\StoreSetting::getSettings();

        // Origin: Buyer Address (Collection point for return parcel)
        $originAreaId = $order->destination_area_id ?: ($user->biteship_area_id ?? 'IDNP6IDCU31IDD327');
        $originAddress = $order->shipping_address ?: 'Alamat Pembeli';

        // Destination: Store Warehouse Address
        $destinationAreaId = $store->biteship_area_id ?: 'IDNP6IDCU31IDD327';
        $destParts = array_filter([
            $store->address_detail,
            $store->district,
            $store->city,
            $store->province,
            $store->postal_code
        ]);
        $destinationAddress = !empty($destParts) ? implode(', ', $destParts) : 'Gudang Utama Toko Online, Tegal, Jawa Tengah';

        $courierCode = self::normalizeCourierCode($order->courier_code ?: $order->courier ?: 'sicepat');
        $courierService = self::normalizeCourierService($order->courier_service ?: $order->courier ?: 'reg');

        $items = [];
        if ($order && $order->items) {
            foreach ($order->items as $item) {
                $items[] = [
                    'name' => 'RETUR: ' . substr($item->product->name ?? 'Produk Retur', 0, 45),
                    'value' => (int) $item->price,
                    'weight' => 1000,
                    'quantity' => (int) $item->quantity,
                ];
            }
        }

        if (empty($items)) {
            $items[] = [
                'name' => 'Paket Retur Produk',
                'value' => 50000,
                'weight' => 1000,
                'quantity' => 1,
            ];
        }

        $payload = [
            'shipper_contact_name' => $store->store_name ?: 'Toko Online',
            'shipper_contact_phone' => $store->sender_phone ?: '081234567890',
            'shipper_contact_email' => 'store@toko-online.com',
            
            'origin_contact_name' => $user->name ?? $order->recipient_name ?? 'Pelanggan Retur',
            'origin_contact_phone' => $user->phone ?? $order->recipient_phone ?? '081234567890',
            'origin_contact_email' => $user->email ?? 'customer@toko-online.com',
            'origin_address' => $originAddress,
            'origin_area_id' => $originAreaId,
            'origin_collection_method' => 'drop_off',

            'destination_contact_name' => $store->sender_name ?: ($store->store_name ?: 'Admin Toko'),
            'destination_contact_phone' => $store->sender_phone ?: '081234567890',
            'destination_contact_email' => 'store@toko-online.com',
            'destination_address' => $destinationAddress,
            'destination_area_id' => $destinationAreaId,

            'courier_company' => $courierCode,
            'courier_type' => $courierService,
            'delivery_type' => 'now',
            'items' => $items,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/orders', $payload);

            if (!$response->successful() && $courierCode !== 'jne') {
                // Retry with JNE Reguler as high-availability standard courier
                $payload['courier_company'] = 'jne';
                $payload['courier_type'] = 'reg';
                $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/orders', $payload);
            }

            if ($response->successful()) {
                $data = $response->json();
                $biteshipOrderId = $data['id'] ?? null;
                $courierData = $data['courier'] ?? [];
                
                // Prioritaskan nomor resi fisik asli kurir (waybill_id) atau kode tracking resmi Biteship
                $waybill = $courierData['waybill_id'] 
                    ?? $courierData['tracking_id'] 
                    ?? ($data['waybill_number'] ?? null);

                if (empty($waybill) && !empty($biteshipOrderId)) {
                    $waybill = $biteshipOrderId;
                }

                if (!empty($waybill)) {
                    $orderReturn->update([
                        'return_tracking_number' => $waybill,
                        'biteship_order_id'      => $biteshipOrderId,
                        'biteship_tracking_id'   => $courierData['tracking_id'] ?? null,
                    ]);
                }

                Log::info("Biteship Realtime Return Order Created: Waybill {$waybill} (Biteship ID: {$biteshipOrderId}) for Return #{$orderReturn->id}");

                return [
                    'status' => true,
                    'waybill_number' => $waybill,
                    'biteship_order_id' => $biteshipOrderId,
                    'tracking_link' => $courierData['link'] ?? null,
                    'data' => $data
                ];
            }

            $errorMsg = $response->json('message') ?? $response->json('error') ?? 'Gagal membuat order retur ke Biteship.';
            Log::error("Biteship Return Order Failed (HTTP {$response->status()}): " . $response->body());

            return [
                'status' => false,
                'waybill_number' => null,
                'biteship_order_id' => null,
                'message' => $errorMsg
            ];
        } catch (\Exception $e) {
            Log::error("Biteship Return Order Exception: " . $e->getMessage());

            return [
                'status' => false,
                'waybill_number' => null,
                'biteship_order_id' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process Pickup Request for an Order (Serahkan ke Kurir Otomatis / Live Biteship API Booking)
     */
    public function processOrderPickup(\App\Models\Order $order): array
    {
        $order->loadMissing(['items.product', 'user']);

        $courierRaw = $order->courier ?: 'jnt';
        $courierCode = self::normalizeCourierCode($order->courier_code ?: $courierRaw);
        $courierService = self::normalizeCourierService($order->courier_service ?: $courierRaw);

        // Resolve Destination Area ID
        $destinationAreaId = $order->destination_area_id ?: ($order->user->biteship_area_id ?? null);
        if (empty($destinationAreaId) || !str_starts_with($destinationAreaId, 'IDNP')) {
            $searchQuery = $order->destination_district ?: ($order->user->city ?? ($order->shipping_address ?: 'Temanggung'));
            $areas = $this->searchAreas($searchQuery);
            $destinationAreaId = (!empty($areas) && !empty($areas[0]['id'])) ? $areas[0]['id'] : 'IDNP10IDNC463IDND5907IDZ56272';
            $order->update(['destination_area_id' => $destinationAreaId]);
        }

        $items = [];
        if ($order->items && $order->items->count() > 0) {
            foreach ($order->items as $item) {
                $items[] = [
                    'name'     => substr($item->product_name ?: ($item->product->name ?? 'Produk Pesanan'), 0, 45),
                    'value'    => (int) $item->price,
                    'weight'   => (int) ($item->product->weight ?? 500),
                    'quantity' => (int) $item->quantity,
                ];
            }
        }

        if (empty($items)) {
            $items[] = [
                'name'     => 'Paket Pesanan #' . $order->invoice_number,
                'value'    => (int) $order->total,
                'weight'   => 1000,
                'quantity' => 1,
            ];
        }

        $orderData = [
            'recipient_name'       => $order->recipient_name ?: ($order->user->name ?? 'Pelanggan'),
            'recipient_phone'      => $order->recipient_phone ?: ($order->user->phone ?? '081234567890'),
            'recipient_email'      => $order->user->email ?? 'customer@toko-online.com',
            'shipping_address'     => $order->shipping_address ?: 'Alamat Pengiriman Pelanggan',
            'destination_area_id'  => $destinationAreaId,
            'courier_code'         => $courierCode,
            'courier_service'      => $courierService,
            'items'                => $items,
        ];

        $result = $this->createOrder($orderData);

        if (!empty($result['status'])) {
            $waybill = $result['waybill_number'] ?: $result['biteship_order_id'];
            $pdfUrl = $result['waybill_pdf_url'] ?? ("https://biteship.com/orders/{$result['biteship_order_id']}/waybill");

            $order->update([
                'status'            => 'dikirim',
                'tracking_number'   => $waybill,
                'waybill_number'    => $waybill,
                'biteship_order_id' => $result['biteship_order_id'],
                'waybill_pdf_url'   => $pdfUrl,
            ]);

            // Kirim notifikasi email pesanan dikirim ke pembeli
            try {
                if ($order->user && $order->user->email) {
                    \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderShipped($order));
                }
            } catch (\Exception $e) {
                Log::warning('Gagal kirim email order shipped: ' . $e->getMessage());
            }

            return [
                'status'            => true,
                'waybill_number'    => $waybill,
                'biteship_order_id' => $result['biteship_order_id'],
                'waybill_pdf_url'   => $pdfUrl,
                'message'           => 'Permintaan pick up kurir berhasil! Nomor Resi: ' . $waybill,
            ];
        }

        return $result;
    }

    public static function deduplicateHistory(array $history): array
    {
        $clean = [];
        foreach ($history as $h) {
            $last = end($clean);
            $currStatus = strtolower(trim($h['status'] ?? ''));
            $lastStatus = $last ? strtolower(trim($last['status'] ?? '')) : null;
            $currNote   = trim($h['note'] ?? $h['description'] ?? '');
            $lastNote   = $last ? trim($last['note'] ?? $last['description'] ?? '') : null;
            $currTime   = trim($h['updated_at'] ?? '');

            if ($last && $currStatus === $lastStatus && ($currNote === $lastNote || !empty($currStatus))) {
                if (!empty($currTime)) {
                    $lastKey = array_key_last($clean);
                    $clean[$lastKey]['updated_at'] = $currTime;
                }
                continue;
            }

            $clean[] = $h;
        }
        return array_values($clean);
    }
}
