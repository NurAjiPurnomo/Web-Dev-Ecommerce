<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Voucher;
use App\Models\Affiliate;
use App\Models\Announcement;
use App\Models\ProductReview;
use App\Models\Banner;
use App\Models\StoreSetting;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show Admin Login Form at /admin or /admin/login
     */
    public function showLogin()
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Authenticate Admin User
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->has('remember'))) {
            $user = Auth::guard('admin')->user();
            if (!$user->is_admin) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Akun ini tidak memiliki hak akses Administrator.']);
            }
            $request->session()->regenerate();
            session(['admin_user' => $user->toArray()]);
            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang kembali, Administrator!');
        }

        return back()->withErrors(['email' => 'Email atau kata sandi admin salah.']);
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('info', 'Anda telah keluar dari Sistem Admin.');
    }

    /**
     * Dashboard Overview
     */
    /**
     * Dashboard Overview with Gross/Net Revenue Analytics & Monthly/Category Filters.
     */
    public function dashboard(Request $request)
    {
        $selectedMonth = $request->query('month', 'all'); // 'all', '1', '2', ..., '12'
        $selectedCategory = $request->query('category', 'all'); // 'all', 'Pakaian', 'Sepatu', ...

        // Base Orders Query
        $orderQuery = Order::where('status', '!=', 'batal');

        // Apply Month Filter
        if ($selectedMonth !== 'all') {
            $orderQuery->whereMonth('created_at', (int)$selectedMonth);
        }

        // Apply Category Filter
        if ($selectedCategory !== 'all') {
            $orderQuery->whereHas('items.product', function ($q) use ($selectedCategory) {
                $q->where('category', $selectedCategory);
            });
        }

        $filteredOrders = $orderQuery->get();

        // Calculate Gross Revenue & Net Revenue (85% Net Profit Margin)
        $grossRevenue = $filteredOrders->sum('total');
        $netRevenue   = round($grossRevenue * 0.85);
        $totalOrders  = $filteredOrders->count();
        $avgOrderVal  = $totalOrders > 0 ? round($grossRevenue / $totalOrders) : 0;

        $stats = [
            'gross_revenue'   => $grossRevenue,
            'net_revenue'     => $netRevenue,
            'total_orders'    => $totalOrders,
            'avg_order_val'   => $avgOrderVal,
            'total_customers' => User::where('is_admin', false)->count(),
            'total_products'  => Product::where('status', 'aktif')->count(),
        ];

        // 1. Category Breakdown Analysis
        $categoriesList = ['Pakaian', 'Sepatu', 'Aksesoris', 'Gadget', 'Rumah Tangga'];
        $categoryBreakdown = [];

        foreach ($categoriesList as $cat) {
            $catItems = OrderItem::whereHas('order', function ($q) use ($selectedMonth) {
                $q->where('status', '!=', 'batal');
                if ($selectedMonth !== 'all') {
                    $q->whereMonth('created_at', (int)$selectedMonth);
                }
            })->whereHas('product', function ($q) use ($cat) {
                $q->where('category', $cat);
            })->get();

            $catGross = $catItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            $catNet   = round($catGross * 0.85);
            $catQty   = $catItems->sum('quantity');

            $categoryBreakdown[] = [
                'category' => $cat,
                'quantity' => $catQty,
                'gross'    => $catGross,
                'net'      => $catNet,
                'pct'      => $grossRevenue > 0 ? round(($catGross / $grossRevenue) * 100, 1) : 0,
            ];
        }

        // 2. Monthly Revenue Trend Breakdown (12 Months)
        $monthlyTrend = [];
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($indonesianMonths as $num => $name) {
            $mOrders = Order::where('status', '!=', 'batal')
                ->whereMonth('created_at', $num)
                ->when($selectedCategory !== 'all', function ($q) use ($selectedCategory) {
                    $q->whereHas('items.product', function ($pq) use ($selectedCategory) {
                        $pq->where('category', $selectedCategory);
                    });
                })->get();

            $mGross = $mOrders->sum('total');
            $mNet   = round($mGross * 0.85);

            $monthlyTrend[] = [
                'month_num'  => $num,
                'month_name' => $name,
                'orders'     => $mOrders->count(),
                'gross'      => $mGross,
                'net'        => $mNet,
            ];
        }

        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'selectedMonth',
            'selectedCategory',
            'categoryBreakdown',
            'monthlyTrend',
            'indonesianMonths'
        ));
    }

    /**
     * Management Produk (Product Management)
     */
    public function products(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');

        $query = Product::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $products = $query->latest()->get();

        return view('admin.products', compact('products'));
    }

    /**
     * Store New Product
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'weight'         => 'nullable|integer|min:1',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'size_guide_image'=> 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description'    => 'nullable|string',
        ]);

        $validated['weight'] = !empty($validated['weight']) ? (int)$validated['weight'] : 1000;

        if (!empty($validated['original_price']) && (float)$validated['original_price'] > (float)$validated['price']) {
            $discountPct = round((((float)$validated['original_price'] - (float)$validated['price']) / (float)$validated['original_price']) * 100);
            $validated['discount'] = $discountPct . '%';
        } else {
            $validated['original_price'] = null;
            $validated['discount'] = null;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = '/storage/' . $path;
        } elseif (empty($validated['image'])) {
            $validated['image'] = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
        }

        if ($request->hasFile('size_guide_image')) {
            $path = $request->file('size_guide_image')->store('products/guides', 'public');
            $validated['size_guide_image'] = '/storage/' . $path;
        }

        // Parse Structured Variants (Warna, Stok, Ukuran, Link Gambar)
        $rawVariants = $request->variants;
        if (is_string($rawVariants)) {
            $rawVariants = json_decode($rawVariants, true);
        }
        
        if (!empty($rawVariants) && is_array($rawVariants)) {
            $parsedVariants = [];
            $allColors = [];
            $allSizes = [];
            $allImages = [];
            $variantImages = $request->file('variant_images', []);

            $totalStock = 0;
            foreach ($rawVariants as $index => $var) {
                $color = trim($var['color'] ?? '');
                
                $image = trim($var['existing_image'] ?? ($var['image'] ?? ($validated['image'] ?? '')));
                if (isset($variantImages[$index])) {
                    $path = $variantImages[$index]->store('products/variants', 'public');
                    $image = '/storage/' . $path;
                }

                if (isset($var['sizes']) && is_array($var['sizes'])) {
                    foreach ($var['sizes'] as $sz) {
                        $size = trim($sz['name'] ?? '');
                        $stock = max(0, (int) ($sz['stock'] ?? 0));
                        $price = max(0, (float) ($sz['price'] ?? 0));
                        
                        if (!empty($color) || !empty($size)) {
                            $parsedVariants[] = [
                                'color' => $color ?: 'Standar',
                                'stock' => $stock,
                                'price' => $price,
                                'size'  => $size,
                                'image' => $image ?: $validated['image'],
                            ];

                            $totalStock += $stock;

                            $colorFound = false;
                            foreach ($allColors as $c) {
                                if ($c['name'] === ($color ?: 'Standar')) {
                                    $colorFound = true; break;
                                }
                            }
                            if (!$colorFound) {
                                $allColors[] = [
                                    'name'  => $color ?: 'Standar',
                                    'image' => $image ?: $validated['image'],
                                ];
                            }

                            if (!empty($size) && !in_array($size, $allSizes)) $allSizes[] = $size;

                            if ($image && !in_array($image, $allImages)) $allImages[] = $image;
                        }
                    }
                }
            }

            if (!empty($parsedVariants)) {
                $validated['stock'] = $totalStock > 0 ? $totalStock : $validated['stock'];
                $validated['variants'] = $parsedVariants;
                $validated['colors']   = $allColors;
                if (!empty($allSizes)) $validated['sizes'] = $allSizes;
                if (!empty($allImages)) $validated['images'] = $allImages;
            }
        } else {
            // Fallback: Parse text sizes
            if (!empty($request->sizes)) {
                $validated['sizes'] = is_array($request->sizes) 
                    ? array_values(array_filter(array_map('trim', $request->sizes)))
                    : array_values(array_filter(array_map('trim', explode(',', $request->sizes))));
            }

            // Fallback: Parse text images
            if (!empty($request->images)) {
                $validated['images'] = is_array($request->images) 
                    ? array_values(array_filter(array_map('trim', $request->images)))
                    : array_values(array_filter(array_map('trim', explode("\n", $request->images))));
            }

            // Fallback: Parse text colors
            if (!empty($request->colors)) {
                if (is_array($request->colors)) {
                    $validated['colors'] = $request->colors;
                } else {
                    $colorLines = array_filter(array_map('trim', explode("\n", $request->colors)));
                    $parsedColors = [];
                    foreach ($colorLines as $line) {
                        $parts = explode('|', $line);
                        $cName = trim($parts[0] ?? '');
                        $cImg  = trim($parts[1] ?? '');
                        if (!empty($cName)) {
                            $parsedColors[] = [
                                'name'  => $cName,
                                'image' => $cImg ?: $validated['image'],
                            ];
                        }
                    }
                    $validated['colors'] = !empty($parsedColors) ? $parsedColors : null;
                }
            }
        }

        $validated['status'] = 'aktif';
        $validated['sold']   = 0;

        Product::create($validated);

        return redirect()->route('admin.products')->with('success', 'Produk baru "' . $validated['name'] . '" berhasil ditambahkan!');
    }

    /**
     * Update Product
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'weight'         => 'nullable|integer|min:1',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'size_guide_image'=> 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description'    => 'nullable|string',
            'status'         => 'required|string',
        ]);

        $validated['weight'] = !empty($validated['weight']) ? (int)$validated['weight'] : ($product->weight ?: 1000);

        if (!empty($validated['original_price']) && (float)$validated['original_price'] > (float)$validated['price']) {
            $discountPct = round((((float)$validated['original_price'] - (float)$validated['price']) / (float)$validated['original_price']) * 100);
            $validated['discount'] = $discountPct . '%';
        } else {
            $validated['original_price'] = null;
            $validated['discount'] = null;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = '/storage/' . $path;
        } else {
            // Keep existing image if no new file is uploaded
            $validated['image'] = $product->image;
        }

        if ($request->hasFile('size_guide_image')) {
            $path = $request->file('size_guide_image')->store('products/guides', 'public');
            $validated['size_guide_image'] = '/storage/' . $path;
        } else {
            $validated['size_guide_image'] = $product->size_guide_image;
        }

        // Parse Structured Variants (Warna, Stok, Ukuran, Link Gambar)
        $rawVariants = $request->variants;
        if (is_string($rawVariants)) {
            $rawVariants = json_decode($rawVariants, true);
        }
        
        if (!empty($rawVariants) && is_array($rawVariants)) {
            $parsedVariants = [];
            $allColors = [];
            $allSizes = [];
            $allImages = [];
            $variantImages = $request->file('variant_images', []);

            $totalStock = 0;
            foreach ($rawVariants as $index => $var) {
                $color = trim($var['color'] ?? '');
                
                $image = trim($var['existing_image'] ?? ($var['image'] ?? ($validated['image'] ?? $product->image)));
                if (isset($variantImages[$index])) {
                    $path = $variantImages[$index]->store('products/variants', 'public');
                    $image = '/storage/' . $path;
                }

                if (isset($var['sizes']) && is_array($var['sizes'])) {
                    foreach ($var['sizes'] as $sz) {
                        $size = trim($sz['name'] ?? '');
                        $stock = max(0, (int) ($sz['stock'] ?? 0));
                        $price = max(0, (float) ($sz['price'] ?? 0));
                        
                        if (!empty($color) || !empty($size)) {
                            $parsedVariants[] = [
                                'color' => $color ?: 'Standar',
                                'stock' => $stock,
                                'price' => $price,
                                'size'  => $size,
                                'image' => $image ?: ($validated['image'] ?? $product->image),
                            ];

                            $totalStock += $stock;

                            $colorFound = false;
                            foreach ($allColors as $c) {
                                if ($c['name'] === ($color ?: 'Standar')) {
                                    $colorFound = true; break;
                                }
                            }
                            if (!$colorFound) {
                                $allColors[] = [
                                    'name'  => $color ?: 'Standar',
                                    'image' => $image ?: ($validated['image'] ?? $product->image),
                                ];
                            }

                            if (!empty($size) && !in_array($size, $allSizes)) $allSizes[] = $size;

                            if ($image && !in_array($image, $allImages)) $allImages[] = $image;
                        }
                    }
                }
            }

            if (!empty($parsedVariants)) {
                $validated['stock'] = $totalStock > 0 ? $totalStock : $validated['stock'];
                $validated['variants'] = $parsedVariants;
                $validated['colors']   = $allColors;
                if (!empty($allSizes)) $validated['sizes'] = $allSizes;
                if (!empty($allImages)) $validated['images'] = $allImages;
            }
        } else {
            // Fallback: Parse text sizes
            if (!empty($request->sizes)) {
                $validated['sizes'] = is_array($request->sizes) 
                    ? array_values(array_filter(array_map('trim', $request->sizes)))
                    : array_values(array_filter(array_map('trim', explode(',', $request->sizes))));
            }

            // Fallback: Parse text images
            if (!empty($request->images)) {
                $validated['images'] = is_array($request->images) 
                    ? array_values(array_filter(array_map('trim', $request->images)))
                    : array_values(array_filter(array_map('trim', explode("\n", $request->images))));
            }

            // Fallback: Parse text colors
            if (!empty($request->colors)) {
                if (is_array($request->colors)) {
                    $validated['colors'] = $request->colors;
                } else {
                    $colorLines = array_filter(array_map('trim', explode("\n", $request->colors)));
                    $parsedColors = [];
                    foreach ($colorLines as $line) {
                        $parts = explode('|', $line);
                        $cName = trim($parts[0] ?? '');
                        $cImg  = trim($parts[1] ?? '');
                        if (!empty($cName)) {
                            $parsedColors[] = [
                                'name'  => $cName,
                                'image' => $cImg ?: ($validated['image'] ?? $product->image),
                            ];
                        }
                    }
                    $validated['colors'] = !empty($parsedColors) ? $parsedColors : null;
                }
            }
        }

        $product->update($validated);

        return redirect()->route('admin.products')->with('success', 'Data produk "' . $product->name . '" berhasil diperbarui!');
    }

    /**
     * Delete Product
     */
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $name = $product->name;

        try {
            // Delete child reviews associated with this product first
            ProductReview::where('product_id', $id)->delete();

            // Delete the product record
            $product->delete();

            return redirect()->route('admin.products')->with('success', 'Produk "' . $name . '" telah berhasil dihapus secara permanen dari katalog.');
        } catch (\Exception $e) {
            // Fallback if foreign key constraint restricts direct deletion
            $product->update(['status' => 'nonaktif', 'stock' => 0]);
            return redirect()->route('admin.products')->with('info', 'Produk "' . $name . '" diubah menjadi Nonaktif karena terdapat riwayat pesanan.');
        }
    }

    /**
     * Management User / Pelanggan
     */
    public function users(Request $request)
    {
        $search = $request->query('search');

        $query = User::where('is_admin', false);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return view('admin.users', compact('users'));
    }

    /**
     * Toggle User Status (active / suspended)
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = ($user->status === 'active' || empty($user->status)) ? 'suspended' : 'active';
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Status akun ' . $user->name . ' telah diubah menjadi ' . strtoupper($user->status) . '.');
    }

    /**
     * Management Transaksi / Penjualan
     */
    public function orders(Request $request)
    {
        $status = $request->query('status', 'semua');

        $query = Order::with(['user', 'items']);

        if ($status !== 'semua') {
            if ($status === 'belum_bayar' || $status === 'belum_dibayar') {
                $query->whereIn('status', ['belum_bayar', 'belum_dibayar']);
            } elseif ($status === 'dikemas' || $status === 'diproses') {
                $query->whereIn('status', ['dikemas', 'diproses']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->latest()->get();

        return view('admin.orders', compact('orders', 'status'));
    }

    /**
     * Export Laporan Penjualan (CSV Format)
     */
    public function exportOrders(Request $request)
    {
        $status = $request->query('status', 'semua');
        $query = Order::with(['user', 'items']);

        if ($status !== 'semua') {
            if ($status === 'belum_bayar' || $status === 'belum_dibayar') {
                $query->whereIn('status', ['belum_bayar', 'belum_dibayar']);
            } elseif ($status === 'dikemas' || $status === 'diproses') {
                $query->whereIn('status', ['dikemas', 'diproses']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->latest()->get();
        $filename = 'laporan-penjualan-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No. Invoice',
                'Tanggal Pesanan',
                'Nama Pembeli',
                'Telepon',
                'Alamat Pengiriman',
                'Ekspedisi / Kurir',
                'Nomor Resi',
                'Status Transaksi',
                'Subtotal',
                'Ongkos Kirim',
                'Diskon',
                'Total Tagihan',
            ]);

            foreach ($orders as $o) {
                fputcsv($file, [
                    $o->invoice_number,
                    $o->created_at ? $o->created_at->format('Y-m-d H:i') : '',
                    $o->recipient_name ?: ($o->user->name ?? 'Pelanggan'),
                    $o->recipient_phone ?: ($o->user->phone ?? '-'),
                    $o->shipping_address ?: ($o->user->address ?? '-'),
                    strtoupper($o->courier ?: 'J&T'),
                    $o->tracking_number ?: '-',
                    strtoupper($o->status ?: 'BELUM DIBAYAR'),
                    $o->subtotal ?? 0,
                    $o->shipping_cost ?? 0,
                    $o->discount_amount ?? 0,
                    $o->total ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * Update Order Status & Resi
     */
    public function updateOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status'          => 'required|string',
            'tracking_number' => 'nullable|string',
        ]);

        // Auto-generate resi/AWB code if empty when status is processing or shipping
        if (empty($validated['tracking_number']) && in_array($validated['status'], ['dikemas', 'diproses', 'dikirim'])) {
            $validated['tracking_number'] = 'JT' . date('Ymd') . rand(1000, 9999);
        }

        $order->update($validated);

        // Automatic System Broadcast Notification for status changes
        if ($validated['status'] === 'dikemas' || $validated['status'] === 'diproses') {
            Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '📦 Pesanan #' . $order->invoice_number . ' Sedang Dikemas',
                'content' => 'Pesanan Anda dengan Invoice ' . $order->invoice_number . ' sedang dikemas oleh tim gudang penjual dan siap diserahkan ke kurir.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        } elseif ($validated['status'] === 'dikirim') {
            $resiText = !empty($validated['tracking_number']) ? ' dengan No. Resi: ' . $validated['tracking_number'] : '';
            Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '🚚 Pesanan #' . $order->invoice_number . ' Dalam Pengiriman',
                'content' => 'Pesanan Anda dengan Invoice ' . $order->invoice_number . ' telah diserahkan ke kurir ' . strtoupper($order->courier ?: 'Ekspedisi') . $resiText . '. Silakan lacak pengiriman Anda.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        } elseif ($validated['status'] === 'selesai') {
            Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '✅ Pesanan #' . $order->invoice_number . ' Telah Selesai',
                'content' => 'Pesanan Anda dengan Invoice ' . $order->invoice_number . ' telah selesai. Terima kasih telah berbelanja di Toko Online!',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        }

        return redirect()->route('admin.orders')->with('success', 'Status pesanan ' . $order->invoice_number . ' telah berhasil diperbarui menjadi "' . strtoupper(str_replace('_', ' ', $validated['status'])) . '"!');
    }

    /**
     * Management Return / Komplain Pelanggan (Bukti Video Unboxing & Foto Cacat)
     */
    public function returns()
    {
        $returns = \App\Models\OrderReturn::with('order', 'user')->latest()->get();
        return view('admin.returns', compact('returns'));
    }

    /**
     * Process Return Request (Approve / Reject)
     */
    public function processReturn(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:approved,rejected,completed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $orderReturn = \App\Models\OrderReturn::with('order')->findOrFail($id);
        $orderReturn->status = $request->status;
        $orderReturn->admin_notes = trim($request->admin_notes);
        $orderReturn->save();

        // Update main order status if approved or completed
        if ($request->status === 'approved' || $request->status === 'completed') {
            $orderReturn->order->update(['status' => 'batal']);
        }

        // Send System Notification to User
        if ($orderReturn->user_id) {
            $statusText = $request->status === 'approved' ? 'DISETUJUI' : ($request->status === 'rejected' ? 'DITOLAK' : 'SELESAI');
            \App\Models\Announcement::create([
                'user_id' => $orderReturn->user_id,
                'title'   => '📦 Pengajuan Retur #' . $orderReturn->order->invoice_number . ' ' . $statusText,
                'content' => 'Pengajuan retur barang Anda telah ' . strtolower($statusText) . ' oleh admin. Catatan: ' . ($request->admin_notes ?: '-'),
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        }

        return redirect()->route('admin.returns')->with('success', 'Pengajuan retur berhasil diperbarui!');
    }

    /**
     * Management Voucher
     */
    public function vouchers()
    {
        $vouchers = Voucher::latest()->get();
        return view('admin.vouchers', compact('vouchers'));
    }

    /**
     * Store New Voucher
     */
    public function storeVoucher(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:vouchers,code',
            'type' => 'required|string',
            'discount_value' => 'required|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'expires_at' => 'required|date',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['status'] = 'aktif';

        Voucher::create($validated);

        return redirect()->route('admin.vouchers')->with('success', 'Voucher baru "' . $validated['code'] . '" berhasil diterbitkan!');
    }

    /**
     * Toggle Voucher Status
     */
    public function toggleVoucher($id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->status = $voucher->status === 'aktif' ? 'nonaktif' : 'aktif';
        $voucher->save();

        return redirect()->route('admin.vouchers')->with('success', 'Status voucher ' . $voucher->code . ' telah diubah.');
    }

    /**
     * Management Affiliate
     */
    public function affiliates()
    {
        $affiliates = Affiliate::with('user')->latest()->get();
        return view('admin.affiliates', compact('affiliates'));
    }

    /**
     * Payout Affiliate Commission
     */
    public function payoutAffiliate($id)
    {
        $affiliate = Affiliate::findOrFail($id);
        $amount = 'Rp ' . number_format($affiliate->commission_earned, 0, ',', '.');
        $affiliate->commission_earned = 0;
        $affiliate->save();

        return redirect()->route('admin.affiliates')->with('success', 'Pencairan komisi ' . $amount . ' untuk ' . ($affiliate->user->name ?? 'mitra') . ' telah berhasil disetujui!');
    }

    /**
     * Management Notifications / Broadcast Announcements
     */
    /**
     * Management Notifications / Automatic Order Status Notifications Log
     */
    public function notifications()
    {
        // Only fetch automatic order status notifications generated by system
        $notifications = Announcement::with('user')->where('type', 'notifikasi')->latest()->get();
        return view('admin.notifications', compact('notifications'));
    }

    /**
     * Store New Announcement / Broadcast Promo
     */
    public function storeNotification(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'type'    => 'required|string',
            'target'  => 'required|string',
            'content' => 'nullable|string',
        ]);

        $validated['status'] = 'ditayangkan';

        Announcement::create($validated);

        if ($request->has('redirect_to_promos')) {
            return redirect()->route('admin.promos')->with('success', 'Broadcast promo "' . $validated['title'] . '" berhasil diterbitkan!');
        }

        return redirect()->route('admin.notifications')->with('success', 'Pengumuman "' . $validated['title'] . '" berhasil diterbitkan!');
    }

    /**
     * Toggle Announcement Status
     */
    public function toggleNotification(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->status = $announcement->status === 'ditayangkan' ? 'selesai' : 'ditayangkan';
        $announcement->save();

        if ($request->has('redirect_to_promos')) {
            return redirect()->route('admin.promos')->with('info', 'Status broadcast promo berhasil diperbarui!');
        }

        return redirect()->route('admin.notifications')->with('info', 'Status pengumuman berhasil diperbarui!');
    }

    /**
     * Manajemen Penilaian & Ulasan Produk
     */
    public function reviews(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $rating = $request->query('rating');

        $query = ProductReview::with(['user', 'product']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($category && $category !== 'all') {
            $query->whereHas('product', function($pq) use ($category) {
                $pq->where('category', $category);
            });
        }

        if ($rating && $rating !== 'all') {
            $query->where('rating', (int)$rating);
        }

        $reviews = $query->latest()->get();

        $stats = [
            'total_reviews' => ProductReview::count(),
            'average_rating' => ProductReview::count() > 0 ? round(ProductReview::avg('rating'), 1) : 5.0,
            'five_star' => ProductReview::where('rating', 5)->count(),
            'low_rating' => ProductReview::whereIn('rating', [1, 2])->count(),
        ];

        return view('admin.reviews', compact('reviews', 'stats'));
    }

    /**
     * Delete Product Review
     */
    public function deleteReview($id)
    {
        $review = ProductReview::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews')->with('success', 'Ulasan & penilaian produk berhasil dihapus!');
    }

    /**
     * Manajemen Banner Promo Halaman Utama
     */
    public function banners()
    {
        $banners = Banner::orderBy('order_column', 'asc')->latest()->get();
        return view('admin.banners', compact('banners'));
    }

    /**
     * Simpan Banner Baru
     */
    public function storeBanner(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:255',
            'highlight_text' => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'image'          => 'required|url',
            'button_text'    => 'nullable|string|max:100',
            'button_url'     => 'nullable|string|max:255',
            'category_tag'   => 'nullable|string|max:100',
            'order_column'   => 'nullable|integer',
        ]);

        $validated['status'] = 'aktif';
        $validated['button_text'] = $validated['button_text'] ?: 'Belanja Sekarang';
        $validated['button_url']  = $validated['button_url'] ?: '/catalog';

        Banner::create($validated);

        return redirect()->route('admin.banners')->with('success', 'Banner promo "' . $validated['title'] . '" berhasil ditambahkan!');
    }

    /**
     * Update Banner Promo
     */
    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:255',
            'highlight_text' => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'image'          => 'required|url',
            'button_text'    => 'nullable|string|max:100',
            'button_url'     => 'nullable|string|max:255',
            'category_tag'   => 'nullable|string|max:100',
            'status'         => 'required|string',
            'order_column'   => 'nullable|integer',
        ]);

        $banner->update($validated);

        return redirect()->route('admin.banners')->with('success', 'Banner promo "' . $banner->title . '" berhasil diperbarui!');
    }

    /**
     * Toggle Status Banner (Aktif / Nonaktif)
     */
    public function toggleBannerStatus($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = $banner->status === 'aktif' ? 'nonaktif' : 'aktif';
        $banner->save();

        return redirect()->route('admin.banners')->with('info', 'Status banner promo berhasil diubah!');
    }

    /**
     * Delete Banner
     */
    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return redirect()->route('admin.banners')->with('success', 'Banner promo berhasil dihapus!');
    }

    /**
     * Halaman Khusus Manajemen Promo & Campaign Toko
     */
    public function promos()
    {
        $banners = Banner::latest()->get();
        $vouchers = Voucher::latest()->get();
        $promoProducts = Product::where('status', 'aktif')
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
                })->orWhere(function($sub) {
                    $sub->whereNotNull('discount')->where('discount', '!=', '');
                });
            })->latest()->get();
        $announcements = Announcement::where('type', '!=', 'notifikasi')->latest()->get();
        $settings = \App\Models\StoreSetting::getSettings();

        return view('admin.promos', compact('banners', 'vouchers', 'promoProducts', 'announcements', 'settings'));
    }

    /**
     * Update Pengaturan Flash Sale (Timer & Status)
     */
    public function updateFlashSale(Request $request)
    {
        $validated = $request->validate([
            'flash_sale_is_active' => 'required|boolean',
            'flash_sale_end_time'   => 'nullable|date',
        ]);

        $settings = \App\Models\StoreSetting::getSettings();
        $settings->update([
            'flash_sale_is_active' => (bool) $request->flash_sale_is_active,
            'flash_sale_end_time'   => $request->flash_sale_end_time ? $request->flash_sale_end_time : null,
        ]);

        return redirect()->route('admin.promos')->with('success', 'Pengaturan Flash Sale (Waktu & Status) berhasil diperbarui!');
    }

    /**
     * ==========================================
     * MANAJEMEN HALAMAN (CMS CUSTOM PAGES)
     * ==========================================
     */

    public function pages()
    {
        $pages = \App\Models\Page::latest()->get();
        return view('admin.pages', compact('pages'));
    }

    public function storePage(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'nullable|string',
            'template'       => 'nullable|string|max:50',
            'show_in_navbar' => 'nullable',
            'footer_column'  => 'nullable|string|max:100',
            'banner_image'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('pages', 'public');
            $validated['banner_image'] = '/storage/' . $path;
        }

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        $validated['show_in_navbar'] = $request->has('show_in_navbar') ? true : false;
        
        // Handle blocks JSON
        if ($request->has('page_blocks')) {
            $validated['blocks'] = json_decode($request->input('page_blocks'), true);
        }

        $validated['status'] = 'aktif';
        if (empty($validated['template'])) $validated['template'] = 'default';

        \App\Models\Page::create($validated);

        return redirect()->route('admin.pages')->with('success', 'Halaman "' . $validated['title'] . '" berhasil dibuat!');
    }

    public function updatePage(Request $request, $id)
    {
        $page = \App\Models\Page::findOrFail($id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'nullable|string',
            'template'       => 'nullable|string|max:50',
            'show_in_navbar' => 'nullable',
            'footer_column'  => 'nullable|string|max:100',
            'banner_image'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('pages', 'public');
            $validated['banner_image'] = '/storage/' . $path;
        }

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        $validated['show_in_navbar'] = $request->has('show_in_navbar') ? true : false;
        
        // Handle blocks JSON
        if ($request->has('page_blocks')) {
            $validated['blocks'] = json_decode($request->input('page_blocks'), true);
        }

        if (empty($validated['template'])) $validated['template'] = 'default';

        $page->update($validated);

        return redirect()->route('admin.pages')->with('success', 'Halaman "' . $page->title . '" berhasil diperbarui!');
    }

    public function togglePageStatus($id)
    {
        $page = \App\Models\Page::findOrFail($id);
        $page->status = $page->status === 'aktif' ? 'draft' : 'aktif';
        $page->save();

        return redirect()->route('admin.pages')->with('info', 'Status halaman berhasil diubah!');
    }

    public function deletePage($id)
    {
        $page = \App\Models\Page::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.pages')->with('success', 'Halaman berhasil dihapus!');
    }

    /**
     * ==========================================
     * MANAJEMEN ARTIKEL (BERITA / BLOG)
     * ==========================================
     */

    public function articles()
    {
        $articles = \App\Models\Article::latest()->get();
        return view('admin.articles', compact('articles'));
    }

    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'summary'   => 'nullable|string|max:1000',
            'content'   => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'status'    => 'required|in:draft,published',
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']) . '-' . time();

        \App\Models\Article::create($validated);

        return redirect()->route('admin.articles')->with('success', 'Artikel berhasil dibuat!');
    }

    public function updateArticle(Request $request, $id)
    {
        $article = \App\Models\Article::findOrFail($id);

        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'summary'   => 'nullable|string|max:1000',
            'content'   => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'status'    => 'required|in:draft,published',
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('articles', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }

        $article->update($validated);

        return redirect()->route('admin.articles')->with('success', 'Artikel berhasil diperbarui!');
    }

    public function toggleArticleStatus($id)
    {
        $article = \App\Models\Article::findOrFail($id);
        $article->status = $article->status === 'published' ? 'draft' : 'published';
        $article->save();

        return redirect()->route('admin.articles')->with('info', 'Status artikel berhasil diubah!');
    }

    public function deleteArticle($id)
    {
        $article = \App\Models\Article::findOrFail($id);
        $article->delete();

        return redirect()->route('admin.articles')->with('success', 'Artikel berhasil dihapus!');
    }

    /**
     * Store Settings Page (Lokasi Asal Toko & Pengaturan Kurir Aktif)
     */
    public function storeSettings()
    {
        $settings = StoreSetting::getSettings();
        return view('admin.store-settings', compact('settings'));
    }

    /**
     * Update Store Settings
     */
    public function updateStoreSettings(Request $request)
    {
        $settings = StoreSetting::getSettings();

        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:50',
            'address_detail' => 'required|string',
            'village' => 'required|string',
            'district' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'biteship_area_id' => 'nullable|string',
            'biteship_api_key' => 'nullable|string',
            'active_couriers' => 'nullable|array',
            'biteship_handling_fee' => 'nullable|numeric|min:0',
            'biteship_shipping_discount' => 'nullable|numeric|min:0',
            'min_order_for_discount' => 'nullable|numeric|min:0',
            'biteship_round_shipping' => 'nullable|string|in:none,up_1000,down_1000,nearest_1000',
        ]);

        $validated['active_couriers'] = $request->input('active_couriers', []);
        $validated['biteship_handling_fee'] = $validated['biteship_handling_fee'] ?? 0;
        $validated['biteship_shipping_discount'] = $validated['biteship_shipping_discount'] ?? 0;
        $validated['min_order_for_discount'] = $validated['min_order_for_discount'] ?? 0;
        $validated['biteship_round_shipping'] = $validated['biteship_round_shipping'] ?? 'none';

        $settings->update($validated);

        return redirect()->route('admin.storeSettings')->with('success', 'Pengaturan lokasi toko, kurir aktif & strategi tarif (Custom Rates) berhasil diperbarui!');
    }

    /**
     * Create Biteship Order manually for an order & generate A6 Shipping Label
     */
    public function createBiteshipOrder(Request $request, $id, BiteshipService $biteshipService)
    {
        $order = Order::findOrFail($id);
        $result = $biteshipService->processOrderPickup($order);

        return redirect()->route('admin.orders')->with('success', 'Pesanan berhasil dikirim ke Biteship! No. Resi (AWB): ' . $order->waybill_number);
    }

    /**
     * Render Printable A6 Thermal Shipping Label
     */
    public function shippingLabel($id)
    {
        $order = Order::with('items.product', 'user')->findOrFail($id);
        $store = StoreSetting::getSettings();
        return view('admin.shipping-label', compact('order', 'store'));
    }

    private function normalizeCourierCode(string $courierStr): string
    {
        return BiteshipService::normalizeCourierCode($courierStr);
    }

    private function normalizeCourierService(string $serviceStr): string
    {
        return BiteshipService::normalizeCourierService($serviceStr);
    }
}
