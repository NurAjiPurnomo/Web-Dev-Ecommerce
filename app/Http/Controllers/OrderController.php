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
     * Konfirmasi Pesanan Diterima oleh Pelanggan (Status: dikirim -> selesai).
     */
    public function complete($id)
    {
        $order = \App\Models\Order::where('invoice_number', $id)
            ->orWhere('id', $id)
            ->orWhere('invoice_number', str_replace('/', '-', $id))
            ->first();

        if ($order) {
            $order->status = 'selesai';
            $order->save();
        }

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
     * Submit Return / Complaint request with Photo & Video Unboxing proof.
     */
    public function submitReturn(Request $request)
    {
        $request->validate([
            'order_id'    => 'required|string',
            'reason'      => 'required|string|in:barang_cacat,barang_kurang,salah_kirim,lainnya',
            'description' => 'required|string|max:2000',
            'photo_proof' => 'required|file|mimes:jpeg,jpg,png,webp|max:5120', // 5MB Max Photo
            'video_proof' => 'required|file|mimes:mp4,mov,avi,mkv,webm|max:51200', // 50MB Max Video
        ]);

        $order = \App\Models\Order::where('invoice_number', $request->order_id)
            ->orWhere('id', $request->order_id)
            ->first();

        if (!$order) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
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
            'order_id'    => $order->id,
            'user_id'     => Auth::id() ?? $order->user_id,
            'reason'      => $request->reason,
            'description' => trim($request->description),
            'photo_proof' => $photoPath,
            'video_proof' => $videoPath,
            'status'      => 'pending',
        ]);

        // Create System Announcement for User
        if (Auth::id() || $order->user_id) {
            \App\Models\Announcement::create([
                'user_id' => Auth::id() ?? $order->user_id,
                'title'   => '📦 Pengajuan Retur Pesanan #' . $order->invoice_number . ' Terkirim',
                'content' => 'Pengajuan retur & komplain barang Anda telah kami terima dengan bukti video unboxing. Tim admin toko akan melakukan peninjauan dalam 1x24 jam.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan retur & komplain berhasil dikirim! Tim Admin Toko akan meninjau bukti foto & video unboxing Anda.',
                'data'    => $orderReturn
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan retur & komplain berhasil dikirim! Tim Admin Toko akan meninjau bukti foto & video unboxing Anda.');
    }
}

