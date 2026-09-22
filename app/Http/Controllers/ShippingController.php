<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ShippingController extends Controller
{
    protected BiteshipService $biteshipService;

    public function __construct(BiteshipService $biteshipService)
    {
        $this->biteshipService = $biteshipService;
    }

    /**
     * Render Custom Branded Public Tracking Page.
     */
    public function publicTrackingPage(Request $request, $waybill = null)
    {
        $waybillNumber = trim($waybill ?: $request->input('waybill', ''));
        $store = StoreSetting::getSettings();
        
        $order = null;
        if (!empty($waybillNumber)) {
            $order = Order::where('waybill_number', $waybillNumber)
                ->orWhere('tracking_number', $waybillNumber)
                ->orWhere('invoice_number', $waybillNumber)
                ->first();
        }

        return view('pages.tracking', compact('waybillNumber', 'order', 'store'));
    }

    /**
     * Public JSON API for tracking lookup by waybill / invoice.
     */
    public function publicTrackApi(Request $request)
    {
        $waybillNumber = trim($request->input('waybill', ''));

        if (empty($waybillNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan masukkan nomor resi atau nomor invoice yang valid.',
            ], 400);
        }

        $order = Order::where('waybill_number', $waybillNumber)
            ->orWhere('tracking_number', $waybillNumber)
            ->orWhere('invoice_number', $waybillNumber)
            ->first();

        $courierCode = 'jne';
        if ($order) {
            $trackingNumber = $order->waybill_number ?: ($order->tracking_number ?: $order->invoice_number);
            $courierCode = $order->courier_code ?: strtolower(explode(' ', $order->courier)[0] ?? 'jne');
        } else {
            $trackingNumber = $waybillNumber;
        }

        $result = $this->biteshipService->getTracking($trackingNumber, $courierCode);

        $orderData = $order ? [
            'invoice_number' => $order->invoice_number,
            'customer_name' => $order->customer_name ?: ($order->user->name ?? 'Pelanggan'),
            'courier' => strtoupper($order->courier ?: 'Ekspedisi'),
            'waybill_number' => $order->waybill_number ?: ($order->tracking_number ?: '-'),
            'shipping_address' => $order->shipping_address,
            'city' => $order->city,
            'province' => $order->province,
            'order_status' => $order->status,
            'created_at' => $order->created_at ? $order->created_at->format('d M Y H:i') : null,
        ] : null;

        if (!$result['status']) {
            if ($order) {
                // If order exists in DB (e.g., in Sandbox Testing Mode), return order data so GPS map renders smoothly
                return response()->json([
                    'status' => 'success',
                    'order' => $orderData,
                    'courier_name' => strtoupper($order->courier ?: 'EKSPEDISI'),
                    'waybill_number' => $trackingNumber,
                    'current_status' => ($order->status == 'selesai') ? 'DELIVERED' : 'IN_TRANSIT',
                    'history' => [
                        [
                            'note' => 'Paket telah terdaftar di sistem pengiriman ' . strtoupper($order->courier ?: 'Ekspedisi') . '.',
                            'service_type' => 'REG',
                            'updated_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                        ]
                    ],
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Nomor resi atau invoice tidak ditemukan di sistem.',
                'order' => null,
                'history' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'order' => $orderData,
            'courier_name' => strtoupper($order ? ($order->courier ?: 'EKSPEDISI') : 'EKSPEDISI'),
            'waybill_number' => $trackingNumber,
            'current_status' => $result['current_status'] ?? 'IN_TRANSIT',
            'history' => $result['data'],
        ]);
    }

    /**
     * AJAX Search for Area ID (City/District/Postal code).
     */
    public function searchArea(Request $request)
    {
        $query = $request->input('query', '');
        if (strlen($query) < 3) {
            return response()->json(['status' => 'error', 'data' => []]);
        }

        $areas = $this->biteshipService->searchAreas($query);

        return response()->json([
            'status' => 'success',
            'data' => $areas,
        ]);
    }

    /**
     * Calculate Shipping Cost based on Destination Area ID & Cart Items.
     */
    public function calculateCost(Request $request)
    {
        $request->validate([
            'destination_area_id' => 'required|string',
        ]);

        $storeSettings = StoreSetting::getSettings();
        $originAreaId = $storeSettings->biteship_area_id ?: 'IDNP6IDCU31IDD327'; // Fallback default area

        // Prepare items from Cart Session or Input
        $cart = Session::get('cart', []);
        $items = [];

        if (!empty($cart)) {
            foreach ($cart as $id => $details) {
                $items[] = [
                    'name' => substr($details['name'] ?? 'Produk', 0, 50),
                    'description' => 'Produk Toko',
                    'value' => (int) ($details['price'] ?? 10000),
                    'weight' => (int) ($details['weight'] ?? 1000),
                    'quantity' => (int) ($details['quantity'] ?? 1),
                ];
            }
        } else {
            // Default sample item for testing
            $items[] = [
                'name' => 'Pesanan Toko',
                'description' => 'Barang Toko Online',
                'value' => 50000,
                'weight' => 1000,
                'quantity' => 1,
            ];
        }

        $activeCouriers = $storeSettings->active_couriers ?? ['jne', 'jnt', 'sicepat', 'pos', 'tiki', 'gosend', 'grabexpress'];

        $result = $this->biteshipService->getRates($originAreaId, $request->destination_area_id, $items, $activeCouriers);

        if (!$result['status']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'origin' => [
                'store_name' => $storeSettings->store_name,
                'district' => $storeSettings->district,
                'city' => $storeSettings->city,
            ],
            'pricing' => $result['data'],
        ]);
    }

    /**
     * Track Order Status Real-Time.
     */
    public function trackOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        $trackingNumber = $order->waybill_number ?: $order->tracking_number;
        if (empty($trackingNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor resi pengiriman belum tersedia untuk pesanan ini.',
            ], 400);
        }

        $courierCode = $order->courier_code ?: strtolower(explode(' ', $order->courier)[0] ?? '');

        $result = $this->biteshipService->getTracking($trackingNumber, $courierCode);

        if (!$result['status']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Data tracking belum tersedia di sistem Biteship API.',
                'courier_name' => strtoupper($order->courier ?: 'EKSPEDISI'),
                'waybill_number' => $trackingNumber,
                'history' => [],
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'courier_name' => strtoupper($order->courier),
            'waybill_number' => $trackingNumber,
            'current_status' => $result['current_status'] ?? 'IN_TRANSIT',
            'history' => $result['data'],
        ]);
    }
}
