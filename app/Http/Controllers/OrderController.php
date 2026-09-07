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
        $query = \App\Models\Order::with('items');
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
        if ($dbOrders->isEmpty()) {
            $dbOrders = \App\Models\Order::with('items')->latest()->take(20)->get();
        }

        $userOrders = [];

        foreach ($dbOrders as $o) {
            $orderStatus = strtolower($o->status ?? 'belum_dibayar');
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

                return [
                    'id'       => $i->product_id,
                    'name'     => $i->product_name,
                    'variant'  => 'Varian Produk',
                    'price'    => (float)$i->price,
                    'qty'      => (int)$i->quantity,
                    'subtotal' => (float)($i->price * $i->quantity),
                    'image'    => $i->image ?? 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600',
                    'is_reviewed' => $isReviewed,
                ];
            })->toArray();

            $trackingData = \App\Services\CourierTrackingService::getTimeline(
                $orderStatus,
                $o->tracking_number,
                $o->courier ?: 'J&T Express',
                $o->created_at->toDateTimeString(),
                $o->recipient_name ?: ($user->name ?? 'Pelanggan')
            );

            $userOrders[] = [
                'id'                   => $o->invoice_number,
                'raw_id'               => 'INV/' . $o->created_at->format('Ymd') . '/TK/' . $o->id,
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
                    'name'    => strtoupper($o->courier ?: 'J&T') . ' Express',
                    'resi'    => $o->tracking_number ?: ($orderStatus === 'dikirim' || $orderStatus === 'selesai' ? 'Belum Diterbitkan' : 'Dalam Proses'),
                    'driver'  => 'Kurir Resmi (Budi Prasetyo - 085712349988)',
                    'etd'     => $orderStatus === 'dikirim' ? 'Est. Tiba 1-2 Hari' : ($orderStatus === 'selesai' ? 'Tiba di Lokasi' : ($orderStatus === 'belum_dibayar' ? 'Menunggu Pembayaran' : 'Sedang Dikemas')),
                ],
                'tracking_data' => $trackingData,
                'items'         => $itemsList,
            ];
        }

        // Sample Data Fallback if user has no DB orders
        if (empty($userOrders)) {
            $userOrders = [
                [
                    'id'            => 'INV-2026-001',
                    'raw_id'        => 'INV/20260807/TK/A1B2C3D4',
                    'date'          => '07 Agu 2026, 14:30 WIB',
                    'status'        => 'dikirim',
                    'status_label'  => 'Dalam Pengiriman',
                    'status_color'  => 'purple',
                    'step'          => 3,
                    'items_summary' => 'Batik Premium Pria Exclusive + 2 barang lainnya',
                    'total_amount'  => 485000,
                    'subtotal'      => 450000,
                    'shipping_cost' => 15000,
                    'discount'      => 20000,
                    'voucher_code'  => 'DISKON20K',
                    'recipient'     => [
                        'name'    => $user->name ?? 'Pelanggan',
                        'phone'   => $user->phone ?? '081234567890',
                        'address' => $user->address ?? 'Jl. Jendral Sudirman No. 45',
                        'city'    => $user->city ?? 'Jakarta Selatan',
                    ],
                    'courier'       => [
                        'name'    => 'J&T Express (Standard)',
                        'resi'    => 'JT8891203912',
                        'driver'  => 'Budi Prasetyo',
                        'etd'     => 'Est. Tiba 08 Agu 2026',
                    ],
                    'items'         => [
                        [
                            'name'     => 'Batik Premium Pria Exclusif Modern Motif Solo',
                            'variant'  => 'Ukuran: L • Warna: Navy Blue',
                            'price'    => 250000,
                            'qty'      => 1,
                            'subtotal' => 250000,
                            'image'    => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600',
                        ],
                    ]
                ],
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
            } elseif ($orderStatus === 'batal') {
                $step = 0;
                $statusLabel = 'Pesanan Dibatalkan';
                $statusColor = 'red';
            }

            // Check if DB orders already contains this order to prevent duplicates
            $alreadyInDb = collect($userOrders)->contains(function($item) use ($lastOrder) {
                return isset($item['raw_id']) && $item['raw_id'] === ($lastOrder['order_id'] ?? '');
            });

            if (!$alreadyInDb) {
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
                        'name'    => strtoupper($lastOrder['courier_id'] ?? 'JNE') . ' Express',
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
            'orders' => $userOrders,
            'user'   => $user,
        ]);
    }

    /**
     * Membatalkan pesanan yang belum dibayar.
     */
    public function cancel($id)
    {
        $order = \App\Models\Order::where('invoice_number', $id)->orWhere('id', $id)->first();
        
        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if (!in_array($order->status, ['belum_bayar', 'belum_dibayar'])) {
            return redirect()->back()->with('error', 'Hanya pesanan yang belum dibayar yang bisa dibatalkan.');
        }

        $order->status = 'batal';
        $order->save();

        // Restore stock and decrement sold count
        foreach ($order->items as $item) {
            $product = \App\Models\Product::find($item->product_id);
            if ($product) {
                $product->stock += $item->quantity;
                $product->sold = max(0, $product->sold - $item->quantity);
                $product->save();
            }
        }

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan! Stok dan angka terjual produk telah dikembalikan.');
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
            // Alternatively, can save it just as string if we want to manually move it.
            // But store('reviews', 'public') puts it in storage/app/public/reviews
        }

        $review = \App\Models\ProductReview::create([
            'user_id'      => Auth::id(),
            'order_id'     => $request->order_id,
            'product_id'   => $request->input('product_id', 1),
            'rating'       => (int) $request->rating,
            'comment'      => trim($request->comment),
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
     * Create a dummy order for testing review functionality.
     */
    public function setupDummyOrder()
    {
        $user = Auth::user();
        
        if (!$user && session()->has('user')) {
            $userId = session('user.id') ?? (is_array(session('user')) ? (session('user')['id'] ?? null) : null);
            if ($userId) {
                $user = \App\Models\User::find($userId);
            }
        }

        // If STILL no user, let's just grab the first user in DB for testing purposes so they don't get stuck!
        if (!$user) {
            $user = \App\Models\User::first();
        }

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $orderId = 'INV/' . date('Ymd') . '/TK/DUMMY' . rand(100, 999);
        
        $dbOrder = \App\Models\Order::create([
            'invoice_number'   => $orderId,
            'user_id'          => $user->id,
            'subtotal'         => 150000,
            'shipping_cost'    => 15000,
            'discount_amount'  => 0,
            'total'            => 165000,
            'courier'          => 'J&T Express',
            'payment_method'   => 'transfer',
            'status'           => 'selesai',
            'shipping_address' => $user->address ?? 'Alamat Dummy',
            'recipient_name'   => $user->name ?? 'Dummy User',
            'recipient_phone'  => $user->phone ?? '081234567890',
            'created_at'       => now()->subDays(3), // 3 days ago so it looks realistic
        ]);

        \App\Models\OrderItem::create([
            'order_id'     => $dbOrder->id,
            'product_id'   => 16,
            'product_name' => 'GOZEAL Kaos Polos Hitam',
            'quantity'     => 1,
            'price'        => 150000,
        ]);

        // Automatically update the sold count for the dummy product
        $dummyProduct = \App\Models\Product::find(16);
        if ($dummyProduct) {
            $dummyProduct->sold += 1;
            $dummyProduct->save();
        }

        return redirect()->route('orders')->with('success', 'Pesanan dummy berhasil ditambahkan. Silakan uji coba fitur ulasan dan cek angka penjualan produk bertambah.');
    }
}
