<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
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
        $selectedPeriod = $request->query('period', 'all'); // 'all', 'today', '7days', '30days', 'this_month'
        $selectedMonth = $request->query('month', 'all'); // 'all', '1', '2', ..., '12'
        $selectedCategory = $request->query('category', 'all'); // 'all', 'Pakaian', 'Sepatu', ...

        // Base Orders Query (Non-cancelled)
        $orderQuery = Order::where('status', '!=', 'batal');

        // Apply Period Preset Filter
        if ($selectedPeriod === 'today') {
            $orderQuery->whereDate('created_at', now()->today());
        } elseif ($selectedPeriod === '7days') {
            $orderQuery->where('created_at', '>=', now()->subDays(7));
        } elseif ($selectedPeriod === '30days') {
            $orderQuery->where('created_at', '>=', now()->subDays(30));
        } elseif ($selectedPeriod === 'this_month') {
            $orderQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

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

        // Calculate Gross Revenue & Subtotal Revenue from real orders
        $grossRevenue    = $filteredOrders->sum('total');
        $subtotalRevenue = $filteredOrders->sum('subtotal');
        $shippingRevenue = $filteredOrders->sum('shipping_cost');
        $totalOrders     = $filteredOrders->count();
        $avgOrderVal     = $totalOrders > 0 ? round($grossRevenue / $totalOrders) : 0;

        // Total Items Sold (pcs)
        $itemsSold = OrderItem::whereHas('order', function ($q) use ($selectedMonth, $selectedPeriod) {
            $q->where('status', '!=', 'batal');
            if ($selectedPeriod === 'today') {
                $q->whereDate('orders.created_at', now()->today());
            } elseif ($selectedPeriod === '7days') {
                $q->where('orders.created_at', '>=', now()->subDays(7));
            } elseif ($selectedPeriod === '30days') {
                $q->where('orders.created_at', '>=', now()->subDays(30));
            } elseif ($selectedPeriod === 'this_month') {
                $q->whereMonth('orders.created_at', now()->month)->whereYear('orders.created_at', now()->year);
            }
            if ($selectedMonth !== 'all') {
                $q->whereMonth('orders.created_at', (int)$selectedMonth);
            }
        })->when($selectedCategory !== 'all', function ($q) use ($selectedCategory) {
            $q->whereHas('product', function ($pq) use ($selectedCategory) {
                $pq->where('category', $selectedCategory);
            });
        })->sum('quantity');

        $stats = [
            'gross_revenue'    => $grossRevenue,
            'subtotal_revenue' => $subtotalRevenue,
            'shipping_revenue' => $shippingRevenue,
            'total_orders'     => $totalOrders,
            'items_sold'       => $itemsSold,
            'avg_order_val'    => $avgOrderVal,
            'total_customers'  => User::where('is_admin', false)->count(),
            'total_products'   => Product::where('status', 'aktif')->count(),
        ];

        // 1. Task Counters (Shopee / Tokopedia Seller Centre To-Do List)
        // Hitung stok menipis secara presisi sampai ke tingkat varian & ukuran
        $allActiveProducts = Product::where('status', 'aktif')->get();
        $lowStockProductsList = [];

        foreach ($allActiveProducts as $p) {
            $hasLowVariant = false;
            $lowVariants = [];

            if (!empty($p->variants) && is_array($p->variants)) {
                foreach ($p->variants as $var) {
                    $vColor = $var['color'] ?? 'Standar';
                    $vSizes = isset($var['sizes']) && is_array($var['sizes']) ? $var['sizes'] : null;

                    if ($vSizes && isset($vSizes[0]) && is_array($vSizes[0])) {
                        // Jika ukuran berupa object dengan stok tersendiri: [['name' => 'S', 'stock' => 2], ...]
                        foreach ($vSizes as $sz) {
                            $szStock = isset($sz['stock']) ? (int)$sz['stock'] : 0;
                            if ($szStock <= 5) {
                                $hasLowVariant = true;
                                $lowVariants[] = [
                                    'name'  => $vColor . ' (' . ($sz['name'] ?? 'All') . ')',
                                    'stock' => $szStock,
                                ];
                            }
                        }
                    } else {
                        // Stok tersimpan di level varian utama ($var['stock'])
                        $vStock = isset($var['stock']) ? (int)$var['stock'] : (int)$p->stock;
                        if ($vStock <= 5) {
                            $hasLowVariant = true;
                            $varName = trim($vColor . (!empty($var['size']) ? ' ' . $var['size'] : ''));
                            $lowVariants[] = [
                                'name'  => $varName ?: 'Standar',
                                'stock' => $vStock,
                            ];
                        }
                    }
                }
            } else {
                if ($p->stock <= 5) {
                    $hasLowVariant = true;
                }
            }

            if ($hasLowVariant) {
                $p->low_variants_list = $lowVariants;
                $lowStockProductsList[] = $p;
            }
        }

        $tasks = [
            'to_ship'      => Order::whereIn('status', ['diproses', 'dikemas'])->count(),
            'unpaid'       => Order::where('status', 'belum_dibayar')->count(),
            'shipping'     => Order::where('status', 'dikirim')->count(),
            'completed'    => Order::where('status', 'selesai')->count(),
            'returns'      => OrderReturn::whereIn('status', ['pending', 'proses'])->count(),
            'low_stock'    => count($lowStockProductsList),
            'out_of_stock' => Product::where('status', 'aktif')->where('stock', '<=', 0)->count(),
        ];

        // 2. Category Breakdown Analysis (Dinamis dari kategori produk aktif)
        $dbCategories = Product::where('status', 'aktif')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray();
        $categoriesList = !empty($dbCategories) ? $dbCategories : ['Pakaian', 'Sepatu', 'Aksesoris', 'Gadget', 'Rumah Tangga'];
        $categoryBreakdown = [];

        foreach ($categoriesList as $cat) {
            $catItems = OrderItem::whereHas('order', function ($q) use ($selectedMonth, $selectedPeriod) {
                $q->where('status', '!=', 'batal');
                if ($selectedPeriod === 'today') {
                    $q->whereDate('orders.created_at', now()->today());
                } elseif ($selectedPeriod === '7days') {
                    $q->where('orders.created_at', '>=', now()->subDays(7));
                } elseif ($selectedPeriod === '30days') {
                    $q->where('orders.created_at', '>=', now()->subDays(30));
                } elseif ($selectedPeriod === 'this_month') {
                    $q->whereMonth('orders.created_at', now()->month)->whereYear('orders.created_at', now()->year);
                }
                if ($selectedMonth !== 'all') {
                    $q->whereMonth('orders.created_at', (int)$selectedMonth);
                }
            })->whereHas('product', function ($q) use ($cat) {
                $q->where('category', $cat);
            })->get();

            $catGross = $catItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            $catQty   = $catItems->sum('quantity');

            $categoryBreakdown[] = [
                'category' => $cat,
                'quantity' => $catQty,
                'gross'    => $catGross,
                'pct'      => $grossRevenue > 0 ? round(($catGross / $grossRevenue) * 100, 1) : 0,
            ];
        }

        // 3. Monthly Revenue Trend Breakdown (Tahun Berjalan)
        $monthlyTrend = [];
        $currentYear = (int)date('Y');
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($indonesianMonths as $num => $name) {
            $mOrders = Order::where('status', '!=', 'batal')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $num)
                ->when($selectedCategory !== 'all', function ($q) use ($selectedCategory) {
                    $q->whereHas('items.product', function ($pq) use ($selectedCategory) {
                        $pq->where('category', $selectedCategory);
                    });
                })->get();

            $mGross = $mOrders->sum('total');

            $monthlyTrend[] = [
                'month_num'  => $num,
                'month_name' => $name,
                'orders'     => $mOrders->count(),
                'gross'      => $mGross,
            ];
        }

        // 4. Top Selling Products (Sinkron dengan periode yang dipilih)
        $topItemsQuery = OrderItem::whereHas('order', function ($q) use ($selectedMonth, $selectedPeriod) {
            $q->where('status', '!=', 'batal');
            if ($selectedPeriod === 'today') {
                $q->whereDate('orders.created_at', now()->today());
            } elseif ($selectedPeriod === '7days') {
                $q->where('orders.created_at', '>=', now()->subDays(7));
            } elseif ($selectedPeriod === '30days') {
                $q->where('orders.created_at', '>=', now()->subDays(30));
            } elseif ($selectedPeriod === 'this_month') {
                $q->whereMonth('orders.created_at', now()->month)->whereYear('orders.created_at', now()->year);
            }
            if ($selectedMonth !== 'all') {
                $q->whereMonth('orders.created_at', (int)$selectedMonth);
            }
        })
        ->when($selectedCategory !== 'all', function ($q) use ($selectedCategory) {
            $q->whereHas('product', function ($pq) use ($selectedCategory) {
                $pq->where('category', $selectedCategory);
            });
        })
        ->whereNotNull('product_id')
        ->selectRaw('product_id, SUM(quantity) as period_sold, SUM(quantity * price) as period_revenue')
        ->groupBy('product_id')
        ->orderByDesc('period_sold')
        ->take(5)
        ->get();

        $topProducts = [];
        foreach ($topItemsQuery as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->period_sold = (int)$item->period_sold;
                $product->period_revenue = (float)$item->period_revenue;
                $topProducts[] = $product;
            }
        }

        // Fallback jika periode 'all' dan riwayat order kosong tapi ada kolom sold di tabel products
        if (empty($topProducts) && $selectedPeriod === 'all' && $selectedMonth === 'all') {
            $topProducts = Product::where('status', 'aktif')
                ->where('sold', '>', 0)
                ->orderByDesc('sold')
                ->take(5)
                ->get();
        }

        // 5. Low Stock Alert Products (Stok <= 5 pcs baik produk utama maupun varian/ukuran)
        $lowStockProducts = array_slice($lowStockProductsList, 0, 6);

        // 6. Recent Orders
        $recentOrders = Order::with('user')->latest()->take(6)->get();

        // 7. Store Settings & Couriers
        $storeSetting = StoreSetting::first();

        return view('admin.dashboard', compact(
            'stats',
            'tasks',
            'topProducts',
            'lowStockProducts',
            'recentOrders',
            'storeSetting',
            'selectedPeriod',
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
        $search   = $request->query('search');
        $category = $request->query('category', 'all');
        $status   = $request->query('status', 'all'); // 'all', 'active', 'low_stock', 'out_of_stock', 'inactive'

        $query = Product::query();

        if ($search) {
            $cleanSearch = trim($search);
            $cleanId = ltrim(preg_replace('/[^0-9]/', '', $cleanSearch), '0');
            $query->where(function($q) use ($cleanSearch, $cleanId) {
                $q->where('name', 'like', "%{$cleanSearch}%");
                if (!empty($cleanId)) {
                    $q->orWhere('id', (int)$cleanId);
                }
            });
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($status === 'active') {
            $query->where('status', 'aktif')->where('stock', '>', 0);
        } elseif ($status === 'low_stock') {
            $query->where('status', 'aktif')->where('stock', '<=', 5)->where('stock', '>', 0);
        } elseif ($status === 'out_of_stock') {
            $query->where('status', 'aktif')->where('stock', '<=', 0);
        } elseif ($status === 'inactive') {
            $query->where('status', 'nonaktif');
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        // Status Tab Counters (Shopee / Tokopedia Seller Centre)
        $tabCounts = [
            'all'          => Product::count(),
            'active'       => Product::where('status', 'aktif')->where('stock', '>', 0)->count(),
            'low_stock'    => Product::where('status', 'aktif')->where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out_of_stock' => Product::where('status', 'aktif')->where('stock', '<=', 0)->count(),
            'inactive'     => Product::where('status', 'nonaktif')->count(),
        ];

        // Overall KPI Metrics
        $totalProductsCount  = $tabCounts['all'];
        $activeProductsCount = $tabCounts['active'];
        $totalStockCount     = (int)Product::sum('stock');
        $totalSoldCount     = (int)Product::sum('sold');

        // Dynamic Categories
        $categories = Product::distinct()->whereNotNull('category')->pluck('category')->filter()->values()->toArray();
        if (empty($categories)) {
            $categories = ['Pakaian', 'Sepatu', 'Aksesoris', 'Gadget', 'Rumah Tangga'];
        }

        return view('admin.products', compact(
            'products',
            'status',
            'category',
            'search',
            'tabCounts',
            'totalProductsCount',
            'activeProductsCount',
            'totalStockCount',
            'totalSoldCount',
            'categories'
        ));
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
                $validated['stock'] = $totalStock;
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

        // Parse Specifications (Spesifikasi Produk)
        $specs = [];
        if (!empty($request->specs) && is_array($request->specs)) {
            foreach ($request->specs as $k => $v) {
                $cleanKey = trim($k);
                $cleanVal = trim($v);
                if (!empty($cleanKey) && !empty($cleanVal)) {
                    $specs[$cleanKey] = $cleanVal;
                }
            }
        }
        $validated['specs'] = !empty($specs) ? $specs : null;

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
                $validated['stock'] = $totalStock;
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

        // Parse Specifications (Spesifikasi Produk)
        $specs = [];
        if (!empty($request->specs) && is_array($request->specs)) {
            foreach ($request->specs as $k => $v) {
                $cleanKey = trim($k);
                $cleanVal = trim($v);
                if (!empty($cleanKey) && !empty($cleanVal)) {
                    $specs[$cleanKey] = $cleanVal;
                }
            }
        }
        $validated['specs'] = !empty($specs) ? $specs : null;

        $product->update($validated);

        return redirect()->route('admin.products')->with('success', 'Data produk "' . $product->name . '" berhasil diperbarui!');
    }

    /**
     * Toggle Product Status (Aktif / Nonaktifkan Arsip)
     */
    public function toggleProductStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = ($product->status === 'aktif') ? 'nonaktif' : 'aktif';
        $product->save();

        $pesan = $product->status === 'aktif'
            ? 'Produk "' . $product->name . '" berhasil diaktifkan kembali ke etalase toko.'
            : 'Produk "' . $product->name . '" berhasil dinonaktifkan (diarsipkan) dari etalase toko.';

        return redirect()->back()->with('success', $pesan);
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
        $status = $request->query('status', 'semua');
        $sort   = $request->query('sort', 'latest');

        // Base Query: Non-admin users
        $baseQuery = User::where('is_admin', false);

        // KPI Counts
        $kpi = [
            'total'     => (clone $baseQuery)->count(),
            'active'    => (clone $baseQuery)->where(function($q) {
                               $q->where('status', 'active')->orWhereNull('status')->orWhere('status', '');
                           })->count(),
            'buyers'    => (clone $baseQuery)->has('orders')->count(),
            'suspended' => (clone $baseQuery)->where('status', 'suspended')->count(),
        ];

        $query = (clone $baseQuery)->withCount('orders')
            ->withSum(['orders as total_spent' => function($q) {
                $q->whereIn('status', ['diproses', 'dikemas', 'dikirim', 'selesai']);
            }], 'total')
            ->with(['orders' => function($q) {
                $q->latest()->limit(5)->with('items.product');
            }]);

        // 1. Search Filter (Nama, Email, Telepon, Kota, Alamat)
        if ($search) {
            $cleanSearch = trim($search);
            $query->where(function($q) use ($cleanSearch) {
                $q->where('name', 'like', "%{$cleanSearch}%")
                  ->orWhere('email', 'like', "%{$cleanSearch}%")
                  ->orWhere('phone', 'like', "%{$cleanSearch}%")
                  ->orWhere('city', 'like', "%{$cleanSearch}%")
                  ->orWhere('district', 'like', "%{$cleanSearch}%")
                  ->orWhere('address', 'like', "%{$cleanSearch}%");
            });
        }

        // 2. Status Tab Filter
        if ($status === 'active') {
            $query->where(function($q) {
                $q->where('status', 'active')->orWhereNull('status')->orWhere('status', '');
            });
        } elseif ($status === 'suspended') {
            $query->where('status', 'suspended');
        } elseif ($status === 'buyer') {
            $query->has('orders');
        }

        // 3. Sorting
        if ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'most_orders') {
            $query->orderByDesc('orders_count');
        } elseif ($sort === 'highest_spend') {
            $query->orderByDesc('total_spent');
        } elseif ($sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users', compact('users', 'kpi', 'status', 'search', 'sort'));
    }

    /**
     * Export Customers to CSV
     */
    public function exportUsers(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'semua');

        $query = User::where('is_admin', false)
            ->withCount('orders')
            ->withSum(['orders as total_spent' => function($q) {
                $q->whereIn('status', ['diproses', 'dikemas', 'dikirim', 'selesai']);
            }], 'total');

        if ($search) {
            $cleanSearch = trim($search);
            $query->where(function($q) use ($cleanSearch) {
                $q->where('name', 'like', "%{$cleanSearch}%")
                  ->orWhere('email', 'like', "%{$cleanSearch}%")
                  ->orWhere('phone', 'like', "%{$cleanSearch}%")
                  ->orWhere('city', 'like', "%{$cleanSearch}%");
            });
        }

        if ($status === 'active') {
            $query->where(function($q) {
                $q->where('status', 'active')->orWhereNull('status')->orWhere('status', '');
            });
        } elseif ($status === 'suspended') {
            $query->where('status', 'suspended');
        } elseif ($status === 'buyer') {
            $query->has('orders');
        }

        $users = $query->latest()->get();

        $filename = 'pelanggan_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nama Pelanggan', 'Email', 'No. Telepon / WA', 'Kota', 'Alamat Lengkap', 'Total Pesanan', 'Total Belanja (Rp)', 'Status Akun', 'Tanggal Daftar']);

            foreach ($users as $u) {
                fputcsv($file, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->phone ?: '-',
                    $u->city ?: '-',
                    $u->address ?: ($u->address_detail ?: '-'),
                    $u->orders_count ?? 0,
                    $u->total_spent ?? 0,
                    ($u->status === 'suspended') ? 'Suspended' : 'Aktif',
                    $u->created_at ? $u->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
        $status  = $request->query('status', 'semua');
        $search  = $request->query('search');
        $courier = $request->query('courier', 'all');
        $period  = $request->query('period', 'all');

        $query = Order::with(['user', 'items.product', 'returns']);

        // 1. Search Filter (No. Invoice, Nama Penerima, Telepon, No. Resi, User)
        if ($search) {
            $cleanSearch = trim($search);
            $query->where(function($q) use ($cleanSearch) {
                $q->where('invoice_number', 'like', "%{$cleanSearch}%")
                  ->orWhere('recipient_name', 'like', "%{$cleanSearch}%")
                  ->orWhere('recipient_phone', 'like', "%{$cleanSearch}%")
                  ->orWhere('tracking_number', 'like', "%{$cleanSearch}%")
                  ->orWhereHas('user', function($uq) use ($cleanSearch) {
                      $uq->where('name', 'like', "%{$cleanSearch}%")
                         ->orWhere('phone', 'like', "%{$cleanSearch}%")
                         ->orWhere('email', 'like', "%{$cleanSearch}%");
                  });
            });
        }

        // 2. Courier Filter
        if ($courier && $courier !== 'all') {
            $query->where('courier', 'like', "%{$courier}%");
        }

        // 3. Time Period Filter
        if ($period && $period !== 'all') {
            if ($period === 'today') {
                $query->whereDate('created_at', now()->today());
            } elseif ($period === '7days') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($period === '30days') {
                $query->where('created_at', '>=', now()->subDays(30));
            } elseif ($period === 'this_month') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        }

        // 4. Status Tab Filter
        if ($status !== 'semua') {
            if ($status === 'belum_bayar' || $status === 'belum_dibayar') {
                $query->whereIn('status', ['belum_bayar', 'belum_dibayar']);
            } elseif ($status === 'dikemas' || $status === 'diproses') {
                $query->whereIn('status', ['dikemas', 'diproses']);
            } elseif ($status === 'dikirim') {
                $query->where('status', 'dikirim');
            } elseif ($status === 'selesai') {
                $query->where('status', 'selesai');
            } elseif ($status === 'pengajuan_batal') {
                $query->where('status', 'pengajuan_batal');
            } elseif ($status === 'batal') {
                $query->where('status', 'batal');
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        // 5. Accurate Global Tab Counts (Independen dari filter pencarian/status saat ini)
        $tabCounts = [
            'semua'           => Order::count(),
            'belum_bayar'     => Order::whereIn('status', ['belum_bayar', 'belum_dibayar'])->count(),
            'dikemas'         => Order::whereIn('status', ['dikemas', 'diproses'])->count(),
            'dikirim'         => Order::where('status', 'dikirim')->count(),
            'selesai'         => Order::where('status', 'selesai')->count(),
            'pengajuan_batal' => Order::where('status', 'pengajuan_batal')->count(),
            'batal'           => Order::where('status', 'batal')->count(),
        ];

        // 6. KPI Summary Metrics
        $totalOrdersCount      = $tabCounts['semua'];
        $unpaidOrdersCount     = $tabCounts['belum_bayar'];
        $processingOrdersCount = $tabCounts['dikemas'];
        $shippingOrdersCount   = $tabCounts['dikirim'];
        $completedOrdersCount  = $tabCounts['selesai'];
        $returnsCount          = \App\Models\OrderReturn::where('status', 'pending')->count();

        // 7. List of Available Couriers for Dropdown
        $dbCouriers = Order::whereNotNull('courier')->distinct()->pluck('courier')->filter()->values()->toArray();
        $courierList = !empty($dbCouriers) 
            ? array_values(array_unique(array_merge($dbCouriers, ['J&T Express', 'JNE Express', 'SiCepat Express']))) 
            : ['J&T Express', 'JNE Express', 'SiCepat Express', 'Anteraja', 'GoSend', 'GrabExpress'];

        $storeSetting = \App\Models\StoreSetting::getSettings();

        return view('admin.orders', compact(
            'orders',
            'status',
            'search',
            'courier',
            'period',
            'tabCounts',
            'totalOrdersCount',
            'unpaidOrdersCount',
            'processingOrdersCount',
            'shippingOrdersCount',
            'completedOrdersCount',
            'returnsCount',
            'courierList',
            'storeSetting'
        ));
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
                    strtoupper($o->courier ?: '-'),
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
            'waybill_number'  => 'nullable|string',
            'courier'         => 'nullable|string',
            'courier_code'    => 'nullable|string',
        ]);

        if (!empty($validated['tracking_number'])) {
            $validated['tracking_number'] = trim($validated['tracking_number']);
            $validated['waybill_number']  = trim($validated['tracking_number']);
        }

        if (!empty($validated['courier'])) {
            $courierName = trim($validated['courier']);
            $validated['courier'] = $courierName;
            $codeMap = [
                'j&t express' => 'jnt', 'jne express' => 'jne', 'sicepat express' => 'sicepat',
                'tiki' => 'tiki', 'pos indonesia' => 'pos', 'gosend' => 'gosend', 'grabexpress' => 'grabexpress'
            ];
            $validated['courier_code'] = $codeMap[strtolower($courierName)] ?? strtolower(explode(' ', $courierName)[0] ?? 'jne');
        }

        $oldStatus = $order->status;
        $order->update(array_filter($validated, fn($val) => !is_null($val)));

        // Automatic System Broadcast Notification for status changes
        if ($validated['status'] === 'batal') {
            // Restore stock & decrement sold count if transitioning to batal
            if ($oldStatus !== 'batal') {
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
            }

            Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '❌ Pesanan #' . $order->invoice_number . ' Dibatalkan',
                'content' => $oldStatus === 'pengajuan_batal'
                    ? 'Pengajuan pembatalan untuk Pesanan #' . $order->invoice_number . ' telah DISETUJUI oleh Admin. Transaksi berhasil dibatalkan.'
                    : 'Pesanan Anda dengan Invoice ' . $order->invoice_number . ' telah dibatalkan.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        } elseif ($oldStatus === 'pengajuan_batal' && in_array($validated['status'], ['dikemas', 'diproses'])) {
            Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '⚠️ Pengajuan Pembatalan #' . $order->invoice_number . ' Ditolak',
                'content' => 'Pengajuan pembatalan untuk Pesanan #' . $order->invoice_number . ' DITOLAK oleh Admin. Pesanan Anda akan tetap diproses dan dikirimkan.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        } elseif ($validated['status'] === 'dikemas' || $validated['status'] === 'diproses') {
            Announcement::create([
                'user_id' => $order->user_id,
                'title'   => '📦 Pesanan #' . $order->invoice_number . ' Sedang Dikemas',
                'content' => 'Pesanan Anda dengan Invoice ' . $order->invoice_number . ' sedang dikemas oleh tim gudang penjual dan siap diserahkan ke kurir.',
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        } elseif ($validated['status'] === 'dikirim') {
            if (empty($order->biteship_order_id)) {
                try {
                    $biteshipService = app(\App\Services\BiteshipService::class);
                    $biteshipService->processOrderPickup($order);
                    $order->refresh();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Biteship pickup creation failed on admin status update: " . $e->getMessage());
                }
            }
            $resiText = !empty($order->waybill_number ?: $order->tracking_number) ? ' dengan No. Resi: ' . ($order->waybill_number ?: $order->tracking_number) : '';
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
    public function returns(Request $request)
    {
        $status   = $request->query('status', 'all');
        $search   = $request->query('search');
        $solution = $request->query('solution', 'all');
        $period   = $request->query('period', 'all');

        $query = \App\Models\OrderReturn::with(['order.items.product', 'user'])->latest();

        // 1. Search Filter (Invoice, Resi 1, Resi 2, No Rekening, Nama Rekening, Pembeli, Kontak)
        if ($search) {
            $cleanSearch = trim($search);
            $query->where(function ($q) use ($cleanSearch) {
                $q->where('return_tracking_number', 'like', "%{$cleanSearch}%")
                  ->orWhere('replacement_tracking_number', 'like', "%{$cleanSearch}%")
                  ->orWhere('bank_account_number', 'like', "%{$cleanSearch}%")
                  ->orWhere('bank_account_name', 'like', "%{$cleanSearch}%")
                  ->orWhereHas('order', function ($oq) use ($cleanSearch) {
                      $oq->where('invoice_number', 'like', "%{$cleanSearch}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($cleanSearch) {
                      $uq->where('name', 'like', "%{$cleanSearch}%")
                        ->orWhere('phone', 'like', "%{$cleanSearch}%")
                        ->orWhere('email', 'like', "%{$cleanSearch}%");
                  });
            });
        }

        // 2. Status Filter
        if ($status !== 'all') {
            if ($status === 'process') {
                $query->whereIn('status', ['approved', 'approved_pending_shipment']);
            } else {
                $query->where('status', $status);
            }
        }

        // 3. Solution Filter
        if ($solution !== 'all') {
            $query->where('solution_type', $solution);
        }

        // 4. Period Filter
        if ($period !== 'all') {
            if ($period === 'today') {
                $query->whereDate('created_at', now()->today());
            } elseif ($period === '7days') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($period === '30days') {
                $query->where('created_at', '>=', now()->subDays(30));
            } elseif ($period === 'this_month') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        }

        $returns = $query->paginate(10)->withQueryString();

        // Calculate accurate global counts for tabs & KPI cards
        $allCount       = \App\Models\OrderReturn::count();
        $pendingCount   = \App\Models\OrderReturn::where('status', 'pending')->count();
        $processCount   = \App\Models\OrderReturn::whereIn('status', ['approved', 'approved_pending_shipment'])->count();
        $completedCount = \App\Models\OrderReturn::where('status', 'completed')->count();
        $rejectedCount  = \App\Models\OrderReturn::where('status', 'rejected')->count();

        $storeSetting = \App\Models\StoreSetting::getSettings();

        return view('admin.returns', compact(
            'returns',
            'status',
            'search',
            'solution',
            'period',
            'allCount',
            'pendingCount',
            'processCount',
            'completedCount',
            'rejectedCount',
            'storeSetting'
        ));
    }

    /**
     * Export Laporan Retur & Komplain CSV Format
     */
    public function exportReturns(Request $request)
    {
        $status   = $request->query('status', 'all');
        $solution = $request->query('solution', 'all');
        $period   = $request->query('period', 'all');

        $query = \App\Models\OrderReturn::with(['order', 'user'])->latest();

        if ($status !== 'all') {
            if ($status === 'process') {
                $query->whereIn('status', ['approved', 'approved_pending_shipment']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($solution !== 'all') {
            $query->where('solution_type', $solution);
        }

        if ($period !== 'all') {
            if ($period === 'today') {
                $query->whereDate('created_at', now()->today());
            } elseif ($period === '7days') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($period === '30days') {
                $query->where('created_at', '>=', now()->subDays(30));
            } elseif ($period === 'this_month') {
                $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        }

        $returns = $query->get();
        $filename = 'laporan-retur-komplain-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($returns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'ID Klaim',
                'No. Invoice',
                'Tanggal Pengajuan',
                'Nama Pembeli',
                'No. Telepon',
                'Jenis Solusi',
                'Alasan Retur',
                'Status Klaim',
                'Rekening Refund',
                'Resi Pengembalian',
                'Resi Pengganti Baru',
                'Catatan Admin',
            ]);

            foreach ($returns as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->order->invoice_number ?? ('Order #' . $r->order_id),
                    $r->created_at ? $r->created_at->format('Y-m-d H:i') : '',
                    $r->user->name ?? 'Pelanggan',
                    $r->user->phone ?? '-',
                    $r->solution_type === 'exchange' ? 'TUKAR BARANG' : 'REFUND DANA',
                    str_replace('_', ' ', strtoupper($r->reason ?? '-')),
                    strtoupper($r->status ?? '-'),
                    $r->bank_account_number ? ($r->bank_name . ' - ' . $r->bank_account_number . ' a.n ' . $r->bank_account_name) : '-',
                    $r->return_tracking_number ?: '-',
                    $r->replacement_tracking_number ?: '-',
                    $r->admin_notes ?: '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process Return Request (Approve / Reject / Generate Resi 2 Replacement)
     */
    public function processReturn(Request $request, $id)
    {
        $request->validate([
            'status'                      => 'required|string|in:approved,rejected,completed,generate_resi2',
            'admin_notes'                 => 'nullable|string|max:1000',
            'replacement_tracking_number' => 'nullable|string|max:100',
        ]);

        $orderReturn = \App\Models\OrderReturn::with('order.items')->findOrFail($id);

        if ($request->status === 'generate_resi2') {
            $resi2 = trim($request->replacement_tracking_number ?: '');
            if (empty($resi2)) {
                $resi2 = 'EXC-' . strtoupper(substr(md5(time() . $orderReturn->id), 0, 10));
            }
            $orderReturn->replacement_tracking_number = $resi2;
            $orderReturn->status = 'completed';
            $orderReturn->admin_notes = trim($request->admin_notes ?: 'Produk pengganti baru telah dikirimkan ke pembeli.');
            $orderReturn->save();

            if ($orderReturn->order) {
                $orderReturn->order->status = 'retur_disetujui';
                $orderReturn->order->save();
            }

            if ($orderReturn->user_id) {
                \App\Models\Announcement::create([
                    'user_id' => $orderReturn->user_id,
                    'title'   => '🚚 Produk Pengganti Baru Pesanan #' . ($orderReturn->order->invoice_number ?? $id) . ' Dikirim',
                    'content' => 'Produk pengganti baru Anda telah diserahkan ke ekspedisi dengan Nomor Resi Baru (Resi 2): ' . $resi2 . '. Silakan lacak pengantaran di web.',
                    'type'    => 'notifikasi',
                    'target'  => 'pelanggan',
                    'status'  => 'ditayangkan'
                ]);
            }

            return redirect()->route('admin.returns')->with('success', 'Produk pengganti dengan Resi (' . $resi2 . ') berhasil diproses!');
        }

        $orderReturn->status = $request->status;
        $orderReturn->admin_notes = trim($request->admin_notes ?: '');
        $orderReturn->save();

        if ($request->status === 'approved') {
            try {
                $biteshipService = app(\App\Services\BiteshipService::class);
                $biteshipService->createReturnOrder($orderReturn);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::info("Biteship return pickup info: " . $e->getMessage());
            }
            $orderReturn->refresh();

            if ($orderReturn->order) {
                $orderReturn->order->status = 'retur_proses';
                $orderReturn->order->save();
            }
        } elseif ($request->status === 'completed') {
            if ($orderReturn->order) {
                $orderReturn->order->status = 'retur_disetujui';
                $orderReturn->order->save();

                // Restore stock on completed refund, but preserve sold count to retain sales history
                foreach ($orderReturn->order->items as $item) {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $product->stock += $item->quantity;
                        $product->save();
                    }
                }
            }
        } elseif ($request->status === 'rejected') {
            if ($orderReturn->order) {
                $orderReturn->order->status = 'retur_ditolak';
                $orderReturn->order->save();
            }
        }

        // Send System Notification to User
        if ($orderReturn->user_id) {
            $statusText = $request->status === 'approved' ? 'DISETUJUI (SILAKAN KIRIM BARANG)' : ($request->status === 'rejected' ? 'DITOLAK' : 'SELESAI');
            
            if ($request->status === 'approved') {
                if (!empty($orderReturn->return_tracking_number)) {
                    $contentMsg = "Pengajuan retur Anda telah DISETUJUI. Nomor Resi Pengembalian Otomatis (Biteship): {$orderReturn->return_tracking_number}. Silakan bawa paket retur ke counter kurir terdekat dan tunjukkan nomor resi ini (Cashless / Drop-off bebas biaya tunai).";
                } else {
                    $contentMsg = 'Pengajuan retur Anda telah DISETUJUI oleh admin. Silakan kirimkan paket fisik barang ke alamat gudang toko kami dan input Nomor Resi pengembalian pada halaman pesanan Anda.';
                }
            } elseif ($request->status === 'rejected') {
                $contentMsg = 'Pengajuan retur barang Anda DITOLAK oleh admin. Catatan: ' . ($request->admin_notes ?: 'Bukti tidak memenuhi syarat garansi toko.');
            } else {
                $contentMsg = 'Pengajuan retur & pengembalian dana transaksi Anda telah SELESAI diproses.';
            }

            \App\Models\Announcement::create([
                'user_id' => $orderReturn->user_id,
                'title'   => 'Pengajuan Retur #' . ($orderReturn->order->invoice_number ?? $orderReturn->order_id) . ' ' . $statusText,
                'content' => $contentMsg,
                'type'    => 'notifikasi',
                'target'  => 'pelanggan',
                'status'  => 'ditayangkan'
            ]);
        }

        $successMsg = 'Pengajuan retur berhasil diperbarui menjadi status ' . strtoupper($request->status) . '!';
        if ($request->status === 'approved' && !empty($orderReturn->return_tracking_number)) {
            $successMsg .= ' Resi retur Biteship (' . $orderReturn->return_tracking_number . ') berhasil diterbitkan otomatis untuk pembeli!';
        }

        return redirect()->route('admin.returns')->with('success', $successMsg);
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
            $cleanSearch = trim($search);
            $query->where(function($q) use ($cleanSearch) {
                $q->where('comment', 'like', "%{$cleanSearch}%")
                  ->orWhere('order_id', 'like', "%{$cleanSearch}%")
                  ->orWhere('variant', 'like', "%{$cleanSearch}%")
                  ->orWhereHas('user', function($uq) use ($cleanSearch) {
                      $uq->where('name', 'like', "%{$cleanSearch}%");
                  })
                  ->orWhereHas('product', function($pq) use ($cleanSearch) {
                      $pq->where('name', 'like', "%{$cleanSearch}%");
                  });
            });
        }

        if ($category && $category !== 'all') {
            $query->whereHas('product', function($pq) use ($category) {
                $pq->where('category', $category);
            });
        }

        if ($rating && $rating !== 'all') {
            if ($rating === 'low') {
                $query->whereIn('rating', [1, 2]);
            } elseif ($rating === 'with_media') {
                $query->whereNotNull('image')->where('image', '!=', '');
            } elseif (in_array((int)$rating, [1, 2, 3, 4, 5])) {
                $query->where('rating', (int)$rating);
            }
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        $totalReviews = ProductReview::count();
        $avgRating = $totalReviews > 0 ? round(ProductReview::avg('rating'), 1) : 0.0;

        $stats = [
            'total_reviews'  => $totalReviews,
            'average_rating' => $avgRating,
            'five_star'      => ProductReview::where('rating', 5)->count(),
            'four_star'      => ProductReview::where('rating', 4)->count(),
            'three_star'     => ProductReview::where('rating', 3)->count(),
            'low_rating'     => ProductReview::whereIn('rating', [1, 2])->count(),
            'with_media'     => ProductReview::whereNotNull('image')->where('image', '!=', '')->count(),
        ];

        // Dynamic categories from existing catalog
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values()
            ->toArray();

        return view('admin.reviews', compact('reviews', 'stats', 'categories', 'search', 'category', 'rating'));
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
        $allActiveProducts = Product::where('status', 'aktif')->orderBy('name', 'asc')->get();
        $announcements = Announcement::where('type', '!=', 'notifikasi')->latest()->get();
        $settings = \App\Models\StoreSetting::getSettings();

        return view('admin.promos', compact('banners', 'vouchers', 'promoProducts', 'allActiveProducts', 'announcements', 'settings'));
    }

    /**
     * Update Pengaturan Flash Sale (Timer, Status & Pilihan Produk Manual)
     */
    public function updateFlashSale(Request $request)
    {
        $validated = $request->validate([
            'flash_sale_is_active'   => 'required|boolean',
            'flash_sale_end_time'     => 'nullable|date',
            'flash_sale_product_ids' => 'nullable|array',
        ]);

        $productIds = !empty($request->flash_sale_product_ids)
            ? array_values(array_unique(array_filter(array_map('intval', $request->flash_sale_product_ids))))
            : [];

        $settings = \App\Models\StoreSetting::getSettings();
        $settings->update([
            'flash_sale_is_active'   => (bool) $request->flash_sale_is_active,
            'flash_sale_end_time'     => $request->flash_sale_end_time ? $request->flash_sale_end_time : null,
            'flash_sale_product_ids' => $productIds,
        ]);

        return redirect()->route('admin.promos')->with('success', 'Pengaturan Flash Sale (Produk Terpilih, Waktu & Status) berhasil diperbarui!');
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

        if (!empty($result['status'])) {
            $resi = $order->fresh()->waybill_number ?: $order->tracking_number;
            return redirect()->route('admin.orders')->with('success', 'Paket berhasil diserahkan ke kurir ' . strtoupper($order->courier ?: 'Ekspedisi') . '! Resi resmi (' . $resi . ') telah terbit otomatis.');
        }

        return redirect()->route('admin.orders')->with('error', 'Gagal serahkan ke kurir: ' . ($result['message'] ?? 'Periksa koneksi Biteship.'));
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
