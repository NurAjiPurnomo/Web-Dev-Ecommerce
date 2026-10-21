<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Tampilkan Halaman Manajer Pesanan (Order Management Dashboard).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = Auth::id() ?? session('user.id');

        // Query DB orders for logged-in user or recent fallback
        $query = \App\Models\Order::with(['items', 'returns']);
        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            });
        }
        
        $dbOrders = $query->latest()->get();

        // Check for expired orders (24 hours) and auto-cancel them
        foreach ($dbOrders as $o) {
            if (in_array($o->status, ['belum_bayar', 'belum_dibayar'])) {
                $expiryTime = $o->created_at->copy()->addHours(24);
                if (now()->greaterThan($expiryTime)) {
                    $o->status = 'batal';
                    $o->save();
                    
                    // Restore stock
                    foreach ($o->items as $item) {
                        $product = \App\Models\Product::find($item->product_id);
                        if ($product) {
                            $product->stock += $item->quantity;
                            $product->sold = max(0, $product->sold - $item->quantity);
                            $product->save();
                        }
                    }
                }
            }
        }

        // Fallback: If no orders for specific user ID, load recent DB orders
        // Fetch Store Settings dynamically for store address and WhatsApp contact
        $storeSettings = \App\Models\StoreSetting::first();
        $storeName = $storeSettings->store_name ?? 'Toko Online Official';
        $storePhone = $storeSettings->sender_phone ?? '081234567890';
        $waNumber = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $storePhone));
        if (empty($waNumber)) {
            $waNumber = '6281234567890';
        }

        $storeAddressParts = array_filter([
            $storeSettings->address_detail ?? 'Jl. Tanjung, RT 01 / RW 02',
            $storeSettings->village ?? 'Tanjungharja',
            $storeSettings->district ? 'Kec. ' . $storeSettings->district : 'Kec. Kramat',
            $storeSettings->city ? 'Kab./Kota ' . $storeSettings->city : 'Kab. Tegal',
            $storeSettings->province ?? 'Jawa Tengah',
            $storeSettings->postal_code ?? '52181'
        ]);
        $storeAddress = implode(', ', $storeAddressParts);

        $userOrders = [];

        foreach ($dbOrders as $o) {
            $orderStatus = strtolower($o->status ?? 'belum_dibayar');

            $rawCourier = trim($o->courier ?? 'J&T Express');
            $courierClean = strtolower($rawCourier);
            if (str_contains($courierClean, 'j&t') || str_contains($courierClean, 'jnt')) {
                $formattedCourierName = 'J&T Express' . (str_contains($courierClean, 'ez') ? ' (EZ)' : '');
            } elseif (str_contains($courierClean, 'jne')) {
                $formattedCourierName = 'JNE' . (str_contains($courierClean, 'reg') ? ' (Reguler)' : (str_contains($courierClean, 'yes') ? ' (YES)' : ''));
            } elseif (str_contains($courierClean, 'sicepat')) {
                $formattedCourierName = 'SiCepat' . (str_contains($courierClean, 'gokil') ? ' (GOKIL)' : ' (Reguler)');
            } elseif (str_contains($courierClean, 'anteraja')) {
                $formattedCourierName = 'AnterAja (Reguler)';
            } elseif (str_contains($courierClean, 'pos')) {
                $formattedCourierName = 'Pos Indonesia';
            } else {
                $formattedCourierName = ucwords($rawCourier);
            }

            $trackingData = \App\Services\CourierTrackingService::getTimeline(
                $orderStatus,
                $o->tracking_number,
                $formattedCourierName,
                $o->created_at->toDateTimeString(),
                $o->recipient_name ?: ($user->name ?? 'Pelanggan'),
                $o->biteship_order_id,
                $o->updated_at ? $o->updated_at->toDateTimeString() : null
            );

            // Keep tracking data intact without forcing automatic status override to DB

            $step = 1;
            $statusLabel = 'Menunggu Pembayaran';
            $statusColor = 'yellow';

            if ($orderStatus === 'dikemas' || $orderStatus === 'diproses') {
                $step = 2;
                $statusLabel = 'Sedang Dikemas oleh Penjual';
                $statusColor = 'blue';
            } elseif ($orderStatus === 'dikirim') {
                $step = 3;
                $statusLabel = 'Dalam Pengiriman Kurir';
                $statusColor = 'purple';
            } elseif ($orderStatus === 'selesai') {
                $step = 4;
                $statusLabel = 'Pesanan Selesai';
                $statusColor = 'green';
            } elseif ($orderStatus === 'retur' || $orderStatus === 'komplain') {
                $step = 3;
                $statusLabel = 'Dalam Pengajuan Retur / Komplain';
                $statusColor = 'amber';
            } elseif ($orderStatus === 'retur_proses') {
                $step = 3;
                $statusLabel = 'Klaim Retur Disetujui (Menunggu Fisik Barang)';
                $statusColor = 'blue';
            } elseif ($orderStatus === 'retur_disetujui') {
                $step = 4;
                $statusLabel = 'Retur Selesai & Dana Dikembalikan';
                $statusColor = 'emerald';
            } elseif ($orderStatus === 'retur_ditolak') {
                $step = 4;
                $statusLabel = 'Pengajuan Retur Ditolak';
                $statusColor = 'rose';
            } elseif ($orderStatus === 'pengajuan_batal') {
                $step = 1;
                $statusLabel = 'Menunggu Konfirmasi Pembatalan Admin';
                $statusColor = 'amber';
            } elseif ($orderStatus === 'batal') {
                $step = 0;
                $statusLabel = 'Pesanan Dibatalkan';
                $statusColor = 'red';
            }

            $itemsList = $o->items->map(function($i) use ($o, $user) {
                $isReviewed = \App\Models\ProductReview::where('order_id', $o->invoice_number)
                    ->where('product_id', $i->product_id)
                    ->where('user_id', $user->id ?? null)
                    ->exists();

                $prod = $i->product_id ? \App\Models\Product::find($i->product_id) : null;
                $img = null;

                if ($prod && !empty($prod->variants) && is_array($prod->variants)) {
                    foreach ($prod->variants as $var) {
                        $varColor = $var['color'] ?? '';
                        $varSize  = $var['size'] ?? '';

                        if (!empty($varColor) && stripos($i->product_name, $varColor) !== false) {
                            if (!empty($varSize) && stripos($i->product_name, $varSize) !== false) {
                                if (!empty($var['image'])) {
                                    $img = $var['image'];
                                    break;
                                }
                            } else if (!empty($var['image'])) {
                                $img = $var['image'];
                            }
                        }
                    }
                }

                if (!$img && $prod && !empty($prod->colors) && is_array($prod->colors)) {
                    foreach ($prod->colors as $c) {
                        $cName = is_array($c) ? ($c['name'] ?? '') : $c;
                        $cImg  = is_array($c) ? ($c['image'] ?? '') : '';
                        if (!empty($cName) && !empty($cImg) && stripos($i->product_name, $cName) !== false) {
                            $img = $cImg;
                            break;
                        }
                    }
                }

                if (!$img) {
                    $img = $i->image ?? ($prod?->image ?? null);
                }

                if (!$img) {
                    $img = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
                }

                if ($img && !str_starts_with($img, 'http') && !str_starts_with($img, 'assets/')) {
                    $img = asset(ltrim($img, '/'));
                }

                return [
                    'id'       => $i->product_id,
                    'name'     => $i->product_name,
                    'variant'  => 'Varian Standar',
                    'price'    => (float)$i->price,
                    'qty'      => (int)$i->quantity,
                    'subtotal' => (float)($i->price * $i->quantity),
                    'image'    => $img,
                    'is_reviewed' => $isReviewed,
                ];
            })->toArray();

            $rawCourier = trim($o->courier ?? 'J&T Express');
            $courierClean = strtolower($rawCourier);
            if (str_contains($courierClean, 'j&t') || str_contains($courierClean, 'jnt')) {
                $formattedCourierName = 'J&T Express' . (str_contains($courierClean, 'ez') ? ' (EZ)' : '');
            } elseif (str_contains($courierClean, 'jne')) {
                $formattedCourierName = 'JNE' . (str_contains($courierClean, 'reg') ? ' (Reguler)' : (str_contains($courierClean, 'yes') ? ' (YES)' : ''));
            } elseif (str_contains($courierClean, 'sicepat')) {
                $formattedCourierName = 'SiCepat' . (str_contains($courierClean, 'gokil') ? ' (GOKIL)' : ' (Reguler)');
            } elseif (str_contains($courierClean, 'anteraja')) {
                $formattedCourierName = 'AnterAja (Reguler)';
            } elseif (str_contains($courierClean, 'pos')) {
                $formattedCourierName = 'Pos Indonesia';
            } else {
                $formattedCourierName = ucwords($rawCourier);
            }

            $trackingData = \App\Services\CourierTrackingService::getTimeline(
                $orderStatus,
                $o->tracking_number,
                $formattedCourierName,
                $o->created_at->toDateTimeString(),
                $o->recipient_name ?: ($user->name ?? 'Pelanggan'),
                $o->biteship_order_id,
                $o->updated_at ? $o->updated_at->toDateTimeString() : null
            );

            $latestReturn = $o->returns ? $o->returns->last() : null;
            $returnTrackingData = null;
            if ($latestReturn) {
                $returnTrackingData = \App\Services\CourierTrackingService::getReturnTimeline(
                    $latestReturn,
                    $o->courier ?? 'J&T Express',
                    $storeSettings->store_name ?? 'Gudang Toko'
                );
            }

            $userOrders[] = [
                'id'                   => $o->invoice_number,
                'raw_id'               => 'INV-' . $o->created_at->format('Ymd') . '-TK-' . $o->id,
                'date'                 => $o->created_at->format('d M Y, H:i') . ' WIB',
                'created_at_timestamp' => $o->created_at ? $o->created_at->timestamp : time(),
                'status'               => $orderStatus,
                'status_label'  => $statusLabel,
                'status_color'  => $statusColor,
                'step'          => $step,
                'items_summary' => count($itemsList) > 0 ? ($itemsList[0]['name'] . (count($itemsList) > 1 ? ' + ' . (count($itemsList) - 1) . ' barang lainnya' : '')) : 'Detail Pesanan',
                'total_amount'  => (float)$o->total,
                'subtotal'      => (float)$o->subtotal,
                'shipping_cost' => (float)$o->shipping_cost,
                'discount'      => (float)$o->discount_amount,
                'voucher_code'  => null,
                'recipient'     => [
                    'name'    => $o->recipient_name ?: ($user->name ?? 'Pelanggan'),
                    'phone'   => $o->recipient_phone ?: ($user->phone ?? '-'),
                    'address' => $o->shipping_address ?: ($user->address ?? 'Alamat Pengiriman'),
                    'city'    => $user->city ?? 'Indonesia',
                ],
                'courier'       => [
                    'name'    => $formattedCourierName,
                    'resi'    => $o->waybill_number ?: ($o->tracking_number ?: 'Belum Diterbitkan'),
                    'driver'  => 'Kurir Resmi (' . $formattedCourierName . ')',
                    'etd'     => $orderStatus === 'dikirim' ? 'Est. Tiba 1-2 Hari' : ($orderStatus === 'selesai' ? 'Tiba di Lokasi' : ($orderStatus === 'belum_dibayar' ? 'Menunggu Pembayaran' : 'Sedang Dikemas')),
                ],
                'return_data'   => $latestReturn ? [
                    'id'                         => $latestReturn->id,
                    'status'                     => $latestReturn->status,
                    'solution_type'              => $latestReturn->solution_type,
                    'return_tracking_number'     => $latestReturn->return_tracking_number,
                    'biteship_order_id'          => $latestReturn->biteship_order_id,
                    'biteship_tracking_id'       => $latestReturn->biteship_tracking_id,
                    'return_shipping_receipt'    => $latestReturn->return_shipping_receipt ? asset($latestReturn->return_shipping_receipt) : null,
                    'replacement_tracking_number'=> $latestReturn->replacement_tracking_number,
                    'bank_name'                  => $latestReturn->bank_name,
                    'bank_account_number'        => $latestReturn->bank_account_number,
                    'bank_account_name'          => $latestReturn->bank_account_name,
                ] : null,
                'return_tracking_data' => $returnTrackingData,
                'tracking_data' => $trackingData,
                'items'         => $itemsList,
            ];
        }



        // Add last order from session if exists
        $lastOrder = session('last_order');
        if ($lastOrder) {
            $orderStatus = $lastOrder['status'] ?? 'belum_dibayar';
            $step = 1;
            $statusLabel = 'Menunggu Pembayaran';
            $statusColor = 'yellow';

            if ($orderStatus === 'dikemas' || $orderStatus === 'diproses') {
                $step = 2;
                $statusLabel = 'Sedang Dikemas oleh Penjual';
                $statusColor = 'blue';
            } elseif ($orderStatus === 'dikirim') {
                $step = 3;
                $statusLabel = 'Dalam Pengiriman Kurir';
                $statusColor = 'purple';
            } elseif ($orderStatus === 'selesai') {
                $step = 4;
                $statusLabel = 'Pesanan Selesai';
                $statusColor = 'green';
            } elseif ($orderStatus === 'retur' || $orderStatus === 'komplain') {
                $step = 3;
                $statusLabel = 'Dalam Pengajuan Retur / Komplain';
                $statusColor = 'amber';
            } elseif ($orderStatus === 'pengajuan_batal') {
                $step = 1;
                $statusLabel = 'Menunggu Konfirmasi Pembatalan Admin';
                $statusColor = 'amber';
            } elseif ($orderStatus === 'batal') {
                $step = 0;
                $statusLabel = 'Pesanan Dibatalkan';
                $statusColor = 'red';
            }

            // Check if DB orders already contains this order to prevent duplicates
            $targetSessionId = str_replace('-', '/', $lastOrder['order_id'] ?? '');

            $matchingDbOrder = collect($userOrders)->first(function($item) use ($targetSessionId) {
                $itemId = str_replace('-', '/', $item['id'] ?? '');
                $itemRawId = str_replace('-', '/', $item['raw_id'] ?? '');
                return ($itemId && $itemId === $targetSessionId) || ($itemRawId && $itemRawId === $targetSessionId);
            });

            if ($matchingDbOrder) {
                // Synchronize session last_order status with live DB status so it never gets stale
                $lastOrder['status'] = $matchingDbOrder['status'];
                session()->put('last_order', $lastOrder);
            } else {
                $lastOrderTs = !empty($lastOrder['created_at_timestamp'])
                    ? (int)$lastOrder['created_at_timestamp']
                    : (isset($lastOrder['created_at']) ? (strtotime(str_replace(' WIB', '', $lastOrder['created_at'])) ?: time()) : time());

                $formattedLastOrder = [
                    'id'                   => $lastOrder['order_id'] ?? ('INV-' . time()),
                    'raw_id'               => $lastOrder['order_id'] ?? ('INV-' . time()),
                    'date'                 => $lastOrder['created_at'] ?? now()->format('d M Y, H:i') . ' WIB',
                    'created_at_timestamp' => $lastOrderTs,
                    'status'               => $orderStatus,
                    'status_label'  => $statusLabel,
                    'status_color'  => $statusColor,
                    'step'          => $step,
                    'items_summary' => ($lastOrder['items'][0]['name'] ?? 'Produk Pesanan') . (count($lastOrder['items']) > 1 ? ' + ' . (count($lastOrder['items']) - 1) . ' barang lainnya' : ''),
                    'total_amount'  => (float)($lastOrder['total_amount'] ?? 0),
                    'subtotal'      => (float)($lastOrder['subtotal'] ?? 0),
                    'shipping_cost' => (float)($lastOrder['shipping_cost'] ?? 0),
                    'discount'      => (float)($lastOrder['discount_amount'] ?? 0),
                    'voucher_code'  => $lastOrder['voucher_code'] ?? null,
                    'recipient'     => [
                        'name'    => $lastOrder['user_name'] ?? ($user->name ?? 'Pelanggan'),
                        'phone'   => $lastOrder['user_phone'] ?? ($user->phone ?? '-'),
                        'address' => $user->address ?? 'Jl. Jendral Sudirman No. 45',
                        'city'    => $user->city ?? 'Jakarta Selatan',
                    ],
                    'courier'       => [
                        'name'    => ucwords(strtolower($lastOrder['courier_id'] ?? 'J&T Express')),
                        'resi'    => 'Dalam Proses',
                        'driver'  => 'Kurir Resmi',
                        'etd'     => $step === 1 ? 'Menunggu Pembayaran' : 'Sedang Dikemas',
                    ],
                    'items'         => array_map(function($i) {
                        return [
                            'name'     => $i['name'] ?? 'Produk',
                            'variant'  => $i['variant'] ?? 'Varian Standar',
                            'price'    => (float)($i['price'] ?? 0),
                            'qty'      => (int)($i['qty'] ?? 1),
                            'subtotal' => (float)(($i['price'] ?? 0) * ($i['qty'] ?? 1)),
                            'image'    => $i['image'] ?? 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600',
                        ];
                    }, $lastOrder['items'] ?? []),
                ];

                array_unshift($userOrders, $formattedLastOrder);
            }
        }

        return view('pages.orders', [
            'orders'        => $userOrders,
            'user'          => $user,
            'storeAddress'  => $storeAddress,
            'storePhone'    => $storePhone,
            'waNumber'      => $waNumber,
            'storeSettings' => $storeSettings,
        ]);
    }

    /**
     * Membatalkan pesanan yang belum dibayar.
    /**
     * Membatalkan pesanan.
     * Jika BELUM DIBAYAR -> Otomatis batal tanpa konfirmasi admin.
     * Jika SUDAH DIBAYAR/DIPROSES -> Kirim pengajuan pembatalan ke admin (pengajuan_batal).
     */
    public function cancel($id)
    {
        $order = $this->findOrderByIdOrInvoice($id);
        
        if (!$order) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if (!in_array($order->status, ['belum_bayar', 'belum_dibayar', 'diproses', 'dikemas'])) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Pesanan tidak dapat dibatalkan.'], 400);
            }
            return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan.');
        }

        // CASE 1: BELUM DIBAYAR -> Otomatis batal tanpa konfirmasi admin
        if (in_array($order->status, ['belum_bayar', 'belum_dibayar'])) {
            $order->status = 'batal';
            $order->save();

            // Restore stock & decrement sold count
            foreach ($order->items as $item) {
                $product = \App\Models\Product::find($item->product_id);
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->sold = max(0, $product->sold - $item->quantity);

                    // Kembalikan juga stok spesifik varian (warna & ukuran) jika ada
                    if (!empty($product->variants) && is_array($product->variants)) {
                        $variants = $product->variants;
                        $varUpdated = false;
                        foreach ($variants as &$var) {
                            $vColor = $var['color'] ?? '';
                            $vSize  = $var['size'] ?? '';
                            if ($vColor && stripos($item->product_name, $vColor) !== false) {
                                if (!$vSize || stripos($item->product_name, $vSize) !== false) {
                                    $var['stock'] = (int)($var['stock'] ?? 0) + $item->quantity;
                                    $varUpdated = true;
                                    break;
                                }
                            }
                        }
                        if ($varUpdated) {
                            $product->variants = $variants;
                        }
                    }

                    $product->save();
                }
            }

            $msg = 'Pesanan belum dibayar berhasil dibatalkan secara otomatis.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => $msg
                ]);
            }

            return redirect()->back()->with('success', $msg);
        }

        // CASE 2: SUDAH DIBAYAR / DIPROSES -> Wajib menunggu konfirmasi admin (pengajuan_batal)
        $order->status = 'pengajuan_batal';
        $order->save();

        // Broadcast System Notification to Admin / User
        if ($order->user_id) {
            \App\Models\Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '⚠️ Pengajuan Pembatalan Pesanan #' . $order->invoice_number,
                'content' => 'Pengajuan pembatalan untuk pesanan Anda telah dikirim dan sedang menunggu peninjauan/konfirmasi dari Admin.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        }

        $msg = 'Pengajuan pembatalan pesanan Anda telah dikirim! Harap menunggu konfirmasi/persetujuan dari Admin.';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Simpan Ulasan & Rating Produk dari Pengguna (Shopee Style).
     */
    public function submitReview(Request $request)
    {
        $request->validate([
            'order_id'   => 'required|string',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB Max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        $variant = trim($request->input('variant', ''));
        if (empty($variant)) {
            // Try to extract variant from order items
            $order = \App\Models\Order::where('invoice_number', $request->order_id)
                ->orWhere('id', $request->order_id)
                ->first();
            if ($order && !empty($order->items)) {
                $productId = $request->input('product_id', 1);
                foreach ($order->items as $item) {
                    $itemProdId = is_array($item) ? ($item['product_id'] ?? 1) : ($item->product_id ?? 1);
                    if ($itemProdId == $productId) {
                        $pName = is_array($item) ? ($item['product_name'] ?? '') : ($item->product_name ?? '');
                        if (preg_match('/\((.*?)\)/', $pName, $m)) {
                            $variant = $m[1];
                        } elseif (!empty(is_array($item) ? ($item['variant'] ?? '') : ($item->variant ?? ''))) {
                            $variant = is_array($item) ? $item['variant'] : $item->variant;
                        }
                        break;
                    }
                }
            }
        }

        $review = \App\Models\ProductReview::create([
            'user_id'      => Auth::id(),
            'order_id'     => $request->order_id,
            'product_id'   => $request->input('product_id', 1),
            'rating'       => (int) $request->rating,
            'comment'      => trim($request->comment),
            'variant'      => $variant ?: 'Variasi Standar',
            'image'        => $imagePath ? '/storage/' . $imagePath : null,
            'is_anonymous' => $request->boolean('is_anonymous') || $request->input('is_anonymous') === 'true',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Terima kasih! Ulasan & rating produk Anda berhasil dikirim.',
                'review'  => $review,
            ]);
        }

        return redirect()->back()->with('success', 'Ulasan & rating produk berhasil dikirim!');
    }

    /**
     * Konfirmasi Pesanan Diterima oleh Pelanggan (Status: dikirim -> selesai).
     */
    public function complete($id)
    {
        $order = $this->findOrderByIdOrInvoice($id);

        if (!$order) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $order->status = 'selesai';
        $order->save();

        // Also check last_order in session
        $lastOrder = session('last_order');
        if ($lastOrder) {
            $lastOrder['status'] = 'selesai';
            session()->put('last_order', $lastOrder);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Terima kasih! Pesanan berhasil diselesaikan.',
            ]);
        }

        return redirect()->back()->with('success', 'Status pesanan diperbarui menjadi Selesai.');
    }

    /**
     * Helper to find Order by ID or Invoice string.
     */
    private function findOrderByIdOrInvoice($orderIdInput)
    {
        $orderIdInput = trim($orderIdInput);
        if (empty($orderIdInput)) return null;

        // 1. Try numeric ID at the end (e.g., INV/20260915/TK/46 or 46)
        if (preg_match('/(\d+)$/', $orderIdInput, $matches)) {
            $order = \App\Models\Order::find($matches[1]);
            if ($order) return $order;
        }

        // 2. Try exact invoice number match
        $order = \App\Models\Order::where('invoice_number', $orderIdInput)
            ->orWhere('id', $orderIdInput)
            ->orWhere('invoice_number', str_replace('-', '/', $orderIdInput))
            ->orWhere('invoice_number', str_replace('/', '-', $orderIdInput))
            ->first();
        if ($order) return $order;

        // 3. Try partial invoice match
        $order = \App\Models\Order::where('invoice_number', 'LIKE', '%' . $orderIdInput . '%')->first();
        if ($order) return $order;

        // 4. Fallback to authenticated user's latest order
        if (Auth::id()) {
            $order = \App\Models\Order::where('user_id', Auth::id())->latest()->first();
            if ($order) return $order;
        }

        // 5. Ultimate fallback to latest order in system
        return \App\Models\Order::latest()->first();
    }

    /**
     * Submit Return / Complaint request with Photo & Video Unboxing proof.
     */
    public function submitReturn(Request $request)
    {
        $request->validate([
            'order_id'            => 'required|string',
            'solution_type'       => 'nullable|string|in:refund,exchange,replacement',
            'reason'              => 'required|string',
            'description'         => 'required|string|max:2000',
            'bank_name'           => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name'   => 'nullable|string|max:100',
            'photo_proof'         => 'required|file|max:20480', // 20MB Max Photo
            'video_proof'         => 'nullable|file|max:102400', // 100MB Max Video
        ]);

        $solutionType = $request->input('solution_type', 'refund');
        $order = $this->findOrderByIdOrInvoice($request->order_id);

        if (!$order) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan di database.'], 404);
            }
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan di database.');
        }

        // Update order status to retur in system
        $order->status = 'retur';
        $order->save();

        // Also sync last_order session if present
        $lastOrder = session('last_order');
        if ($lastOrder) {
            $lastOrder['status'] = 'retur';
            session()->put('last_order', $lastOrder);
        }

        $photoPath = null;
        if ($request->hasFile('photo_proof')) {
            $photoPath = '/storage/' . $request->file('photo_proof')->store('returns/photos', 'public');
        }

        $videoPath = null;
        if ($request->hasFile('video_proof')) {
            $videoPath = '/storage/' . $request->file('video_proof')->store('returns/videos', 'public');
        }

        $orderReturn = \App\Models\OrderReturn::create([
            'order_id'            => $order->id,
            'user_id'             => Auth::id() ?? $order->user_id ?? 1,
            'reason'              => $request->reason,
            'description'         => trim($request->description),
            'photo_proof'         => $photoPath,
            'video_proof'         => $videoPath,
            'solution_type'       => $solutionType,
            'bank_name'           => $request->input('bank_name'),
            'bank_account_number' => $request->input('bank_account_number'),
            'bank_account_name'   => $request->input('bank_account_name'),
            'status'              => 'pending',
        ]);

        // Create System Announcement for User
        $targetUserId = Auth::id() ?? $order->user_id;
        if ($targetUserId) {
            $solutionLabel = $solutionType === 'exchange' ? 'Tukar Barang Baru' : 'Pengembalian Dana (Refund)';
            \App\Models\Announcement::create([
                'user_id' => $targetUserId,
                'title'   => 'Pengajuan Retur #' . $order->invoice_number . ' Terkirim',
                'content' => 'Pengajuan ' . $solutionLabel . ' Anda telah kami terima dengan bukti video unboxing. Tim admin toko akan melakukan peninjauan dalam 1x24 jam.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan pengembalian barang berhasil dikirim! Tim layanan pelanggan kami akan meninjau bukti foto & video unboxing Anda maksimal 1x24 jam.',
                'data'    => $orderReturn
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan pengembalian barang berhasil dikirim! Tim layanan pelanggan kami akan meninjau bukti foto & video unboxing Anda maksimal 1x24 jam.');
    }

    /**
     * Submit Return Tracking Number (Resi 1) & Shipping Receipt from Buyer.
     */
    public function submitReturnTracking(Request $request)
    {
        $request->validate([
            'order_id'                => 'required|string',
            'return_tracking_number'  => 'required|string|max:100',
            'return_shipping_receipt' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:10240',
        ]);

        $order = $this->findOrderByIdOrInvoice($request->order_id);

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan di database.'], 404);
        }

        $orderReturn = \App\Models\OrderReturn::where('order_id', $order->id)->latest()->first();

        // If no OrderReturn record exists yet but order status is retur/komplain, auto-create it
        if (!$orderReturn && in_array(strtolower($order->status), ['retur', 'komplain', 'retur_disetujui'])) {
            $orderReturn = \App\Models\OrderReturn::create([
                'order_id'      => $order->id,
                'user_id'       => Auth::id() ?? $order->user_id ?? 1,
                'reason'        => 'barang_cacat',
                'description'   => 'Pengajuan retur pesanan ' . $order->invoice_number,
                'solution_type' => 'refund',
                'status'        => 'pending',
            ]);
        }

        if (!$orderReturn) {
            return response()->json(['status' => 'error', 'message' => 'Pengajuan retur untuk pesanan ini tidak ditemukan.'], 404);
        }

        $receiptPath = null;
        if ($request->hasFile('return_shipping_receipt')) {
            $receiptPath = '/storage/' . $request->file('return_shipping_receipt')->store('returns/receipts', 'public');
        }

        $orderReturn->return_tracking_number = trim($request->return_tracking_number);
        if ($receiptPath) {
            $orderReturn->return_shipping_receipt = $receiptPath;
        }
        $orderReturn->status = 'approved';
        $orderReturn->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Nomor resi pengembalian (' . $orderReturn->return_tracking_number . ') berhasil disimpan! Tim admin akan memantau kedatangan paket Anda.',
                'data'    => $orderReturn
            ]);
        }

        return redirect()->back()->with('success', 'Nomor resi pengembalian berhasil disimpan!');
    }
}

