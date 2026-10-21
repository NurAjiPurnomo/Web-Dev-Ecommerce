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
            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Nomor resi atau invoice belum terdaftar di Biteship API.',
                'order' => $orderData,
                'courier_name' => strtoupper($order ? ($order->courier ?: 'EKSPEDISI') : 'EKSPEDISI'),
                'waybill_number' => $trackingNumber,
                'history' => [],
                'biteship_link' => null,
            ], 404);
        }

        $formattedHistory = [];
        foreach (($result['data'] ?? []) as $h) {
            $rawNote = $h['note'] ?? $h['description'] ?? '';
            $h['note'] = \App\Services\CourierTrackingService::formatProfessionalNote($rawNote, $order->courier ?? 'Ekspedisi', $order->customer_name ?? 'Pelanggan');
            $h['status_label'] = ucwords(str_replace('_', ' ', $h['status'] ?? ''));
            $formattedHistory[] = $h;
        }

        return response()->json([
            'status' => 'success',
            'order' => $orderData,
            'courier_name' => $order ? ($order->courier ?: 'Ekspedisi') : 'Ekspedisi',
            'waybill_number' => $trackingNumber,
            'current_status' => $result['current_status'] ?? 'IN_TRANSIT',
            'history' => $formattedHistory,
            'biteship_link' => $result['link'] ?? null,
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
                $productId = $details['product_id'] ?? null;
                $dbProduct = $productId ? \App\Models\Product::find($productId) : null;
                $itemWeight = ($dbProduct && $dbProduct->weight > 0)
                    ? (int) $dbProduct->weight
                    : (isset($details['weight']) && (int) $details['weight'] > 0 ? (int) $details['weight'] : 1000);

                $items[] = [
                    'name' => substr($details['name'] ?? 'Produk', 0, 50),
                    'description' => 'Produk Toko',
                    'value' => (int) ($details['price'] ?? 10000),
                    'weight' => $itemWeight,
                    'quantity' => (int) ($details['quantity'] ?? $details['qty'] ?? 1),
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
    public function trackOrder(Request $request, $orderId)
    {
        $order = is_numeric($orderId) 
            ? Order::find($orderId) 
            : Order::where('invoice_number', $orderId)->first();

        if (!$order) {
            $order = Order::where('tracking_number', $orderId)
                ->orWhere('waybill_number', $orderId)
                ->first();
        }

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        // If requesting return tracking or order is in return status and has return tracking
        $isReturnTracking = $request->has('return') || in_array($order->status, ['retur', 'retur_proses', 'retur_disetujui']);
        $latestReturn = $order->returns ? $order->returns->last() : null;

        if ($isReturnTracking && $latestReturn && (!empty($latestReturn->biteship_order_id) || !empty($latestReturn->return_tracking_number))) {
            $trackingId = $latestReturn->biteship_order_id ?: $latestReturn->return_tracking_number;
            $courierCode = $order->courier_code ?: strtolower(explode(' ', $order->courier)[0] ?? 'jnt');
            $result = $this->biteshipService->getTracking($trackingId, $courierCode);

            if ($result['status'] && !empty($result['data'])) {
                $formattedHistory = [];
                foreach ($result['data'] as $h) {
                    $rawNote = $h['note'] ?? $h['description'] ?? '';
                    $h['note'] = \App\Services\CourierTrackingService::formatReturnNote($rawNote, $order->courier ?: 'Ekspedisi');
                    $h['status_label'] = ucwords(str_replace('_', ' ', $h['status'] ?? ''));
                    $formattedHistory[] = $h;
                }

                return response()->json([
                    'status' => 'success',
                    'is_return' => true,
                    'courier_name' => $order->courier ?: 'J&T Express',
                    'waybill_number' => $latestReturn->return_tracking_number,
                    'current_status' => $result['current_status'] ?? 'IN_TRANSIT',
                    'history' => $formattedHistory,
                    'biteship_link' => $result['courier']['link'] ?? ($result['link'] ?? null),
                ]);
            }
        }

        $trackingNumber = $order->waybill_number ?: $order->tracking_number;
        if (empty($trackingNumber)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor resi pengiriman belum tersedia untuk pesanan ini.',
            ], 400);
        }

        $courierCode = $order->courier_code ?: strtolower(explode(' ', $order->courier)[0] ?? '');

        $result = $this->biteshipService->getTracking($trackingNumber, $courierCode);

        if (!$result['status'] || empty($result['data'])) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Data tracking belum ditemukan atau belum terdaftar di sistem Biteship API.',
                'courier_name' => strtoupper($order->courier ?: 'EKSPEDISI'),
                'waybill_number' => $trackingNumber,
                'history' => [],
            ], 404);
        }

        $formattedHistory = [];
        foreach (($result['data'] ?? []) as $h) {
            $rawNote = $h['note'] ?? $h['description'] ?? '';
            $h['note'] = \App\Services\CourierTrackingService::formatProfessionalNote($rawNote, $order->courier ?: 'Ekspedisi', $order->recipient_name ?: ($order->user->name ?? 'Pelanggan'));
            $h['status_label'] = ucwords(str_replace('_', ' ', $h['status'] ?? ''));
            $formattedHistory[] = $h;
        }

        return response()->json([
            'status' => 'success',
            'courier_name' => $order->courier,
            'waybill_number' => $trackingNumber,
            'current_status' => $result['current_status'] ?? 'IN_TRANSIT',
            'history' => $formattedHistory,
            'biteship_link' => $result['link'] ?? null,
        ]);
    }
}
