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
                $response = Http::withHeaders([
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

            $response = Http::withHeaders([
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

            $destinationAreaId = $orderData['destination_area_id'] ?? 'IDNP6IDCU31IDD327';
            if (!str_starts_with($destinationAreaId, 'IDNP')) {
                $areas = $this->searchAreas($destinationAreaId);
                $destinationAreaId = !empty($areas) ? ($areas[0]['id'] ?? 'IDNP6IDCU31IDD327') : 'IDNP6IDCU31IDD327';
            }

            $payload = [
                'shipper_contact_name' => $store->sender_name ?: $store->store_name,
                'shipper_contact_phone' => $store->sender_phone ?: '081234567890',
                'shipper_contact_email' => 'store@toko-online.com',
                'origin_contact_name' => $store->sender_name ?: $store->store_name,
                'origin_contact_phone' => $store->sender_phone ?: '081234567890',
                'origin_address' => ($store->address_detail . ', ' . $store->village . ', ' . $store->district . ', ' . $store->city . ', ' . $store->province),
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
            $url = $this->baseUrl . '/trackings/' . $trackingIdOrWaybill;
            if (!empty($courierCode)) {
                $url .= '?courier=' . strtolower($courierCode);
            }

            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json('history') ?? [],
                    'courier' => $response->json('courier'),
                    'current_status' => $response->json('status'),
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

    /**
     * Process Order Pickup & Auto-Generate Waybill (AWB) for an Order instance.
     */
    public function processOrderPickup(\App\Models\Order $order): array
    {
        if (!empty($order->biteship_order_id) && !empty($order->waybill_number)) {
            return [
                'status' => true,
                'biteship_order_id' => $order->biteship_order_id,
                'waybill_number' => $order->waybill_number,
                'waybill_pdf_url' => $order->waybill_pdf_url,
            ];
        }

        $order->loadMissing(['items.product', 'user']);

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => substr($item->product->name ?? 'Produk', 0, 50),
                'value' => (int) $item->price,
                'weight' => 1000,
                'quantity' => (int) $item->quantity,
            ];
        }

        $courierCode = $this->normalizeCourierCode($order->courier_code ?: $order->courier ?: 'jne');
        $courierService = $this->normalizeCourierService($order->courier_service ?: $order->courier ?: 'reg');

        $orderData = [
            'recipient_name' => $order->recipient_name ?: ($order->user->name ?? 'Pelanggan'),
            'recipient_phone' => $order->recipient_phone ?: ($order->user->phone ?? '081234567890'),
            'recipient_email' => $order->user->email ?? 'customer@toko-online.com',
            'shipping_address' => $order->shipping_address,
            'destination_area_id' => $order->destination_area_id ?: 'IDNP6IDCU31IDD327',
            'courier_code' => $courierCode,
            'courier_service' => $courierService,
            'items' => $items,
        ];

        $result = $this->createOrder($orderData);

        if ($result['status']) {
            $order->update([
                'biteship_order_id' => $result['biteship_order_id'],
                'waybill_number' => $result['waybill_number'],
                'waybill_pdf_url' => $result['waybill_pdf_url'] ?: route('admin.orders.shippingLabel', $order->id),
                'tracking_number' => $result['waybill_number'] ?: $order->tracking_number,
                'status' => 'dikirim',
            ]);
            Log::info("Biteship Auto-Pickup: Success for Order #{$order->invoice_number}. Waybill: {$result['waybill_number']}");

            // Send SMTP Email Notification to Customer with Branded Tracking Link
            try {
                $recipientEmail = $order->user->email ?? null;
                if ($recipientEmail) {
                    \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\OrderShipped($order));
                    Log::info("SMTP Email: OrderShipped email sent successfully to {$recipientEmail}");
                }
            } catch (\Exception $e) {
                Log::error("SMTP Email Error (OrderShipped): " . $e->getMessage());
            }

            return $result;
        }

        Log::error("Biteship Auto-Pickup Failed for Order #{$order->invoice_number}: " . ($result['message'] ?? 'Gagal membuat order Biteship'));

        return [
            'status' => false,
            'message' => $result['message'] ?? 'Gagal menghubungi Biteship API untuk pembuatan resi pengiriman.',
        ];
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
}
