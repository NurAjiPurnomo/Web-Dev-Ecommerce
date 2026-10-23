<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\RecommendationService;

class HomeController extends Controller
{
    /**
     * Display the Home Page with live products from Database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Inisialisasi Hybrid Recommendation Service
        $recommendationService = app(RecommendationService::class);

        // Calculate Automatic Weekly Best Sellers (Top Products Sold in Last 7 Days)
        $weeklySales = \App\Models\OrderItem::where('created_at', '>=', now()->subDays(7))
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as weekly_sold'))
            ->groupBy('product_id')
            ->orderByDesc('weekly_sold')
            ->pluck('weekly_sold', 'product_id')
            ->toArray();

        if (!empty($weeklySales)) {
            $weeklyIds = array_keys($weeklySales);
            $bestSellerModels = Product::where('status', 'aktif')
                ->withAvg('reviews', 'rating')
                ->whereIn('id', $weeklyIds)
                ->get()
                ->sortByDesc(fn($p) => $weeklySales[$p->id] ?? 0)
                ->take(6);

            if ($bestSellerModels->count() < 6) {
                $needed = 6 - $bestSellerModels->count();
                $existingIds = $bestSellerModels->pluck('id')->toArray();
                $additional = Product::where('status', 'aktif')
                    ->withAvg('reviews', 'rating')
                    ->whereNotIn('id', $existingIds)
                    ->orderByDesc('sold')
                    ->orderByDesc('updated_at')
                    ->take($needed)
                    ->get();
                $bestSellerModels = $bestSellerModels->concat($additional);
            }
        } else {
            $bestSellerModels = Product::where('status', 'aktif')
                ->withAvg('reviews', 'rating')
                ->orderByDesc('sold')
                ->orderByDesc('updated_at')
                ->take(6)
                ->get();
        }

        $bestSellerProducts = $bestSellerModels->values()->map(function($p, $index) {
            return [
                'id'            => $p->id,
                'title'         => $p->name,
                'category'      => $p->category,
                'price'         => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'discount'      => $p->discount,
                'image'         => $p->image,
                'rating'        => $p->average_rating,
                'sold'          => $p->formatted_sold,
                'rankBadge'     => $index < 3 ? 'TOP ' . ($index + 1) : 'BESTSELLER',
                'rankColor'     => $index === 0 ? 'bg-amber-500 text-white' : ($index === 1 ? 'bg-slate-400 text-white' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-blue-600 text-white')),
                'variants'      => $p->formatted_variants,
            ];
        })->all();

        $categories = [
            [
                'name'    => 'Pakaian & Fashion',
                'slug'    => 'Pakaian',
                'image'   => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name'    => 'Sepatu & Sneakers',
                'slug'    => 'Sepatu',
                'image'   => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name'    => 'Aksesoris',
                'slug'    => 'Aksesoris',
                'image'   => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name'    => 'Gadget',
                'slug'    => 'Gadget',
                'image'   => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name'    => 'Rumah Tangga',
                'slug'    => 'Rumah Tangga',
                'image'   => 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=300&auto=format&fit=crop&q=80',
            ]
        ];

        // Otomatis gunakan gambar produk asli terlaris dari katalog jika tersedia
        foreach ($categories as &$cat) {
            $rep = Product::where('status', 'aktif')
                ->where(function($q) use ($cat) {
                    $q->where('category', $cat['slug'])
                      ->orWhere('category', 'like', $cat['slug'] . '%');
                })
                ->whereNotNull('image')
                ->orderByDesc('sold')
                ->first();

            if ($rep && !empty($rep->image)) {
                $cat['image'] = $rep->image;
            }
        }
        unset($cat);

        $storeSettings     = \App\Models\StoreSetting::getSettings();
        $flashSaleIsActive = $storeSettings->flash_sale_is_active ?? true;

        if ($storeSettings->flash_sale_end_time && $storeSettings->flash_sale_end_time->isFuture()) {
            $flashSaleEndTime = $storeSettings->flash_sale_end_time->format('Y-m-d\TH:i:s');
        } else {
            $flashSaleEndTime = now()->endOfDay()->format('Y-m-d\TH:i:s');
        }

        // FLASH SALE PRODUCTS:
        // 1. Ambil produk manual yang diatur oleh Admin jika ada
        $selectedFlashSaleIds = $storeSettings->flash_sale_product_ids ?? [];
        if (!is_array($selectedFlashSaleIds)) {
            $selectedFlashSaleIds = json_decode($selectedFlashSaleIds, true) ?: [];
        }

        if (!empty($selectedFlashSaleIds) && $flashSaleIsActive) {
            $promoModels = Product::where('status', 'aktif')
                ->withAvg('reviews', 'rating')
                ->whereIn('id', $selectedFlashSaleIds)
                ->get()
                ->sortBy(function($p) use ($selectedFlashSaleIds) {
                    return array_search($p->id, $selectedFlashSaleIds);
                });
        } else {
            $promoModels = collect();
        }

        // 2. Fallback Otomatis: jika produk manual belum diatur atau kurang dari 6, isi dengan produk diskon aktif
        if ($promoModels->count() < 6 && $flashSaleIsActive) {
            $needed = 6 - $promoModels->count();
            $existingIds = $promoModels->pluck('id')->toArray();

            $autoDiscounted = Product::where('status', 'aktif')
                ->withAvg('reviews', 'rating')
                ->whereNotIn('id', $existingIds)
                ->where(function($q) {
                    $q->where(function($sub) {
                        $sub->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
                    })->orWhere(function($sub) {
                        $sub->whereNotNull('discount')->where('discount', '!=', '');
                    });
                })
                ->latest('updated_at')
                ->take($needed)
                ->get();

            // Jika masih kurang dari 6, tambahkan produk aktif terpopuler
            if ($promoModels->count() + $autoDiscounted->count() < 6) {
                $stillNeeded = 6 - ($promoModels->count() + $autoDiscounted->count());
                $usedIds = array_merge($existingIds, $autoDiscounted->pluck('id')->toArray());
                $filler = Product::where('status', 'aktif')
                    ->withAvg('reviews', 'rating')
                    ->whereNotIn('id', $usedIds)
                    ->latest()
                    ->take($stillNeeded)
                    ->get();
                $autoDiscounted = $autoDiscounted->concat($filler);
            }

            $promoModels = $promoModels->concat($autoDiscounted);
        }

        $flashSaleProducts = $promoModels->take(6)->values()->map(function($p) {
            return [
                'id'            => $p->id,
                'title'         => $p->name,
                'category'      => $p->category,
                'price'         => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'discount'      => $p->discount ?: '20%',
                'image'         => $p->image,
                'rating'        => $p->average_rating,
                'sold'          => $p->formatted_sold,
                'variants'      => $p->formatted_variants,
            ];
        })->all();

        // Dapatkan 18 Rekomendasi Hibrida (60% Minat Kategori User + 40% Kualitas/Tren Global)
        $recommendedProducts = $recommendationService->getHybridRecommendations(18);
        $banners             = \App\Models\Banner::where('status', 'aktif')->orderBy('order_column', 'asc')->get();

        // Fetch New Arrivals (Produk Terbaru)
        $newArrivalModels = Product::where('status', 'aktif')
            ->withAvg('reviews', 'rating')
            ->latest('created_at')
            ->take(6)
            ->get();

        $newArrivalProducts = $newArrivalModels->values()->map(function($p) {
            return [
                'id'            => $p->id,
                'title'         => $p->name,
                'category'      => $p->category,
                'price'         => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'discount'      => $p->discount,
                'image'         => $p->image,
                'rating'        => $p->average_rating,
                'sold'          => $p->formatted_sold,
                'rankBadge'     => 'BARU',
                'rankColor'     => 'bg-blue-700 text-white',
                'variants'      => $p->formatted_variants,
            ];
        })->all();

        // Fetch Latest Articles (Berita / Artikel Toko)
        $latestArticles = \App\Models\Article::where('status', 'published')
            ->latest()
            ->take(3)
            ->get();

        return view('pages.home', compact(
            'banners', 
            'flashSaleProducts', 
            'bestSellerProducts', 
            'newArrivalProducts', 
            'recommendedProducts', 
            'categories', 
            'latestArticles', 
            'flashSaleEndTime', 
            'flashSaleIsActive'
        ));
    }

    /**
     * Display Catalog Page from Database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function catalog(Request $request)
    {
        $search = trim($request->query('search', ''));
        $selectedCategory = $request->query('category');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $statusFilter = $request->query('status_filter'); // 'diskon', 'ready_stock'
        $sort = $request->query('sort', 'latest');

        $query = Product::where('status', 'aktif')->withAvg('reviews', 'rating');

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                // Cari berdasarkan SKU persis atau partial
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('id', $search);

                // Memecah kata pencarian berdasarkan spasi untuk pencarian nama produk
                $searchWords = explode(' ', $search);
                $q->orWhere(function($subQ) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        if (!empty($word)) {
                            $subQ->where('name', 'like', "%{$word}%");
                        }
                    }
                });
            });
        }

        if ($selectedCategory && $selectedCategory !== 'all') {
            $query->where(function($q) use ($selectedCategory) {
                $q->where('category', $selectedCategory)
                  ->orWhere('category', 'like', explode(' ', $selectedCategory)[0] . '%');
            });
        }

        // Filter Rentang Harga (Min - Max)
        if (is_numeric($minPrice)) {
            $query->where('price', '>=', (float) $minPrice);
        }
        if (is_numeric($maxPrice)) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        // Filter Status Produk (Diskon, Ready Stock)
        if ($statusFilter === 'diskon') {
            $query->where(function($q) {
                $q->whereNotNull('discount')->where('discount', '!=', '')
                  ->orWhere(function($sub) {
                      $sub->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
                  });
            });
        } elseif ($statusFilter === 'ready_stock') {
            $query->where('stock', '>', 0);
        }

        // Sorting Dropdown
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderBy('sold', 'desc');
        } elseif ($sort === 'rating_desc') {
            $query->orderByDesc('reviews_avg_rating');
        } else {
            $query->latest();
        }

        $paginatedProducts = $query->paginate(20)->withQueryString();

        $paginatedProducts->getCollection()->transform(function($p) {
            return [
                'id' => $p->id,
                'sku' => $p->sku ?: ('SKU-' . str_pad($p->id, 5, '0', STR_PAD_LEFT)),
                'title' => $p->name,
                'slug' => \Illuminate\Support\Str::slug($p->name),
                'category' => $p->category,
                'price' => $p->formatted_price,
                'raw_price' => $p->price,
                'originalPrice' => $p->formatted_original_price,
                'hasDiscount' => !empty($p->discount),
                'discount' => $p->discount,
                'image' => $p->image,
                'rating' => $p->average_rating,
                'sold' => $p->formatted_sold,
                'stock' => $p->stock,
                'variants' => $p->formatted_variants,
            ];
        });

        $catalogProducts = $paginatedProducts;

        $categories = Product::where('status', 'aktif')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values()
            ->all();

        return view('pages.catalog', compact(
            'catalogProducts', 
            'categories', 
            'selectedCategory', 
            'minPrice', 
            'maxPrice', 
            'statusFilter', 
            'sort'
        ));
    }

    /**
     * Display Product Detail Page from Database.
     *
     * @param  int|null  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function productDetail($id = 1, $slug = null)
    {
        $dbProduct = Product::withAvg('reviews', 'rating')->withCount('reviews')->find($id) 
            ?: Product::withAvg('reviews', 'rating')->withCount('reviews')->first();

        if (!$dbProduct) {
            abort(404, 'Produk tidak ditemukan');
        }

        // Catat aktivitas melihat produk untuk rekomendasi hibrida real-time
        app(RecommendationService::class)->recordProductView($dbProduct->id, $dbProduct->category);

        $userId = \Illuminate\Support\Facades\Auth::id();
        $sessionId = session()->getId();

        $rawReviews = \App\Models\ProductReview::with(['user', 'likes'])
            ->where('product_id', $dbProduct->id)
            ->latest()
            ->get();

        $ratingCounts = [
            5 => $rawReviews->where('rating', 5)->count(),
            4 => $rawReviews->where('rating', 4)->count(),
            3 => $rawReviews->where('rating', 3)->count(),
            2 => $rawReviews->where('rating', 2)->count(),
            1 => $rawReviews->where('rating', 1)->count(),
        ];

        $reviews = $rawReviews->map(function($r) use ($userId, $sessionId) {
            $name = $r->display_name;
            $userAvatarPath = $r->user->avatar ?? null;
            if ($userAvatarPath) {
                $avatar = str_starts_with($userAvatarPath, 'http') ? $userAvatarPath : asset('storage/' . ltrim($userAvatarPath, '/'));
            } else {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D8ABC&color=fff';
            }
            $imgUrl = $r->image ? (str_starts_with($r->image, 'http') ? $r->image : asset($r->image)) : null;

            $isLikedByMe = false;
            if ($userId) {
                $isLikedByMe = $r->likes->where('user_id', $userId)->isNotEmpty();
            } else {
                $isLikedByMe = $r->likes->where('session_id', $sessionId)->isNotEmpty();
            }

            return [
                'id'          => $r->id,
                'userName'    => $name,
                'userAvatar'  => $avatar,
                'rating'      => (int) $r->rating,
                'comment'     => $r->comment ?: 'Tidak ada ulasan tertulis.',
                'date'        => $r->created_at ? $r->created_at->format('d M Y') : 'Baru saja',
                'created_at'  => $r->created_at ? $r->created_at->toIso8601String() : null,
                'variant'     => $r->variant ?: 'Variasi Standar',
                'image'       => $imgUrl,
                'images'      => $imgUrl ? [$imgUrl] : [],
                'isVerified'  => true,
                'likes'       => (int) ($r->likes_count ?? 0),
                'isLiked'     => $isLikedByMe,
                'user'        => [
                    'name'   => $name,
                    'avatar' => $avatar,
                ],
            ];
        })->toArray();
        
        $reviewImages = $rawReviews->pluck('image')
            ->filter(fn($img) => !empty($img))
            ->map(fn($img) => str_starts_with($img, 'http') ? $img : asset($img))
            ->values()
            ->all();



        // Dynamic product images (main image + all variant images)
        $productImages = [$dbProduct->image];
        
        // Add images from variants
        if (!empty($dbProduct->variants) && is_array($dbProduct->variants)) {
            foreach ($dbProduct->variants as $var) {
                if (!empty($var['image']) && !in_array($var['image'], $productImages)) {
                    $productImages[] = $var['image'];
                }
            }
        }

        // Add images from colors
        if (!empty($dbProduct->colors) && is_array($dbProduct->colors)) {
            foreach ($dbProduct->colors as $col) {
                $cImg = is_array($col) ? ($col['image'] ?? '') : '';
                if (!empty($cImg) && !in_array($cImg, $productImages)) {
                    $productImages[] = $cImg;
                }
            }
        }

        // Dynamic sizes
        $productSizes = (!empty($dbProduct->sizes) && is_array($dbProduct->sizes))
            ? $dbProduct->sizes
            : [];
        if (empty($productSizes) && !empty($dbProduct->variants) && is_array($dbProduct->variants)) {
            $productSizes = array_values(array_unique(array_filter(array_map(function($v) {
                return $v['size'] ?? null;
            }, $dbProduct->variants))));
        }
        if (empty($productSizes)) {
            $productSizes = ['S', 'M', 'L', 'XL'];
        }

        // Dynamic colors
        $productColors = (!empty($dbProduct->colors) && is_array($dbProduct->colors))
            ? array_map(function($c) {
                return is_array($c) ? ($c['name'] ?? 'Varian') : $c;
            }, $dbProduct->colors)
            : [];
        if (empty($productColors) && !empty($dbProduct->variants) && is_array($dbProduct->variants)) {
            $productColors = array_values(array_unique(array_filter(array_map(function($v) {
                return $v['color'] ?? null;
            }, $dbProduct->variants))));
        }
        if (empty($productColors)) {
            $productColors = ['Navy Blue', 'Hitam', 'Silver / White'];
        }

        $product = [
            'id'            => $dbProduct->id,
            'title'         => $dbProduct->name,
            'category'      => $dbProduct->category,
            'price'         => $dbProduct->formatted_price,
            'originalPrice' => $dbProduct->formatted_original_price,
            'discount'      => $dbProduct->discount,
            'image'         => $dbProduct->image,
            'size_guide_image' => $dbProduct->size_guide_image,
            'images'        => $productImages,
            'sizes'         => $productSizes,
            'colors'        => $productColors,
            'colorVariants' => $dbProduct->colors ?: [],
            'variantsList'  => $dbProduct->variants ?: [],
            'rating'        => $dbProduct->average_rating,
            'reviewCount'   => $dbProduct->review_count,
            'sold'          => $dbProduct->formatted_sold,
            'stock'         => $dbProduct->stock,
            'description'   => $dbProduct->description ?: 'Produk berkualitas tinggi dari Toko Online Resmi kami.',
            'reviews'       => $reviews,
            'ratingCounts'  => $ratingCounts,
            'reviewImages'  => $reviewImages,
            'specs'         => (function() use ($dbProduct) {
                $storeSetting = \App\Models\StoreSetting::first();
                $storeName = $storeSetting && !empty($storeSetting->store_name) ? $storeSetting->store_name : 'Beranda';
                $sentFrom = $storeSetting && !empty($storeSetting->city) ? strtoupper($storeSetting->city) : 'TEGAL';
                $dbSpecs = is_array($dbProduct->specs) ? $dbProduct->specs : [];

                $specs = [
                    'Kategori' => [
                        'type' => 'breadcrumb',
                        'items' => [
                            ['name' => $storeName, 'url' => route('home')],
                            ['name' => $dbProduct->category ?: 'Katalog', 'url' => route('catalog', ['category' => $dbProduct->category ?: 'all'])],
                            ['name' => $dbProduct->name, 'url' => null],
                        ]
                    ],
                ];

                // Merek (Brand - Plain text, tidak berupa link)
                $brand = !empty($dbSpecs['Merek']) ? $dbSpecs['Merek'] : (!empty($dbSpecs['Brand']) ? $dbSpecs['Brand'] : 'No Brand');
                $specs['Merek'] = $brand;

                // Asal Produk
                $specs['Asal Produk'] = !empty($dbSpecs['Asal Produk']) ? $dbSpecs['Asal Produk'] : 'Indonesia';

                // Masukkan spesifikasi yang diisi dari database (jika ada)
                if (!empty($dbSpecs)) {
                    foreach ($dbSpecs as $key => $val) {
                        if (in_array($key, ['Merek', 'Brand', 'Asal Produk', 'Stok', 'Berat Produk', 'Dikirim Dari', 'Kategori'])) {
                            continue;
                        }
                        if (!empty($val)) {
                            $specs[$key] = $val;
                        }
                    }
                } else {
                    // Fallback default berdasarkan kategori jika admin belum mengisi spesifikasi khusus
                    $catLower = strtolower(($dbProduct->category ?? '') . ' ' . ($dbProduct->name ?? ''));
                    if (str_contains($catLower, 'sepatu') || str_contains($catLower, 'sandal')) {
                        $specs['Tipe Ujung Sepatu'] = 'Ujung Bulat';
                        $specs['Tipe Pengikat'] = 'Slip-On / Tali';
                    } elseif (str_contains($catLower, 'jaket') || str_contains($catLower, 'hoodie')) {
                        $specs['Bahan Pakaian'] = 'Fleece Cotton Premium';
                        $specs['Model / Potongan'] = 'Regular Fit Unisex';
                        $specs['Tipe Lengan'] = 'Lengan Panjang';
                    } elseif (str_contains($catLower, 'baju') || str_contains($catLower, 'kaos')) {
                        $specs['Bahan Pakaian'] = 'Katun Combed 30s';
                        $specs['Model / Potongan'] = 'Regular Fit';
                    }
                }

                // Field Dinamis & Real-time Toko
                $specs['Stok'] = $dbProduct->stock . ' buah';
                $specs['Berat Produk'] = ($dbProduct->weight ?: 1000) . ' gram';
                $specs['Kondisi'] = !empty($dbSpecs['Kondisi']) ? $dbSpecs['Kondisi'] : 'Baru (100% Original)';
                $specs['Garansi'] = !empty($dbSpecs['Garansi']) ? $dbSpecs['Garansi'] : 'Garansi Resmi Toko';
                $specs['Dikirim Dari'] = $sentFrom;

                return $specs;
            })(),
        ];

        $relatedProducts = Product::where('status', 'aktif')
            ->withAvg('reviews', 'rating')
            ->where('id', '!=', $dbProduct->id)
            ->latest()
            ->take(6)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->name,
                    'category' => $p->category,
                    'price' => $p->formatted_price,
                    'originalPrice' => $p->formatted_original_price,
                    'discount' => $p->discount,
                    'image' => $p->image,
                    'images' => [$p->image],
                    'rating' => $p->average_rating,
                    'sold' => $p->formatted_sold,
                    'variants' => $p->formatted_variants,
                ];
            });

        $isWishlisted = false;
        if (\Illuminate\Support\Facades\Auth::check()) {
            $isWishlisted = \App\Models\Wishlist::where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->where('product_id', $dbProduct->id)
                ->exists();
        }

        return view('pages.product-detail', compact('product', 'relatedProducts', 'isWishlisted'));
    }

    /**
     * Realtime toggle like / helpful on a product review.
     */
    public function toggleReviewLike($id)
    {
        $review = \App\Models\ProductReview::findOrFail($id);
        $userId = \Illuminate\Support\Facades\Auth::id();
        $sessionId = session()->getId();
        $ip = request()->ip();

        $query = \App\Models\ReviewLike::where('review_id', $review->id);
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            $isLiked = false;
        } else {
            \App\Models\ReviewLike::create([
                'review_id' => $review->id,
                'user_id' => $userId,
                'session_id' => $sessionId,
                'ip_address' => $ip,
            ]);
            $isLiked = true;
        }

        $totalLikes = \App\Models\ReviewLike::where('review_id', $review->id)->count();
        $review->update(['likes_count' => $totalLikes]);

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'likes_count' => $totalLikes,
            'message' => $isLiked ? 'Ulasan dinilai membantu!' : 'Penilaian ulasan dibatalkan.',
        ]);
    }

    /**
     * Display Promo Page.
     */
    public function promo(\Illuminate\Http\Request $request)
    {
        $selectedCategory = $request->query('category', 'all');

        $query = Product::where('status', 'aktif')
            ->withAvg('reviews', 'rating')
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
                })->orWhere(function($sub) {
                    $sub->whereNotNull('discount')->where('discount', '!=', '');
                });
            })
            ->latest('updated_at');

        if ($selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }

        $paginatedPromo = $query->paginate(20)->withQueryString();

        if ($paginatedPromo->isEmpty() && $selectedCategory === 'all') {
            $paginatedPromo = Product::where('status', 'aktif')->withAvg('reviews', 'rating')->latest()->paginate(20)->withQueryString();
        }

        $paginatedPromo->getCollection()->transform(function($p) {
            return [
                'id' => $p->id,
                'title' => $p->name,
                'category' => $p->category,
                'price' => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'discount' => $p->discount,
                'image' => $p->image,
                'rating' => $p->average_rating,
                'sold' => $p->formatted_sold,
                'variants' => $p->formatted_variants,
            ];
        });

        $allProducts = $paginatedPromo;

        $user = \Illuminate\Support\Facades\Auth::user();

        $dbVouchers = \App\Models\Voucher::where('status', 'aktif')
            ->where(function($q) use ($user) {
                $q->where('target', 'semua')
                  ->orWhereNull('target');
                if ($user) {
                    $q->orWhere(function($sub) use ($user) {
                        $sub->where('target', 'pelanggan')->where('user_id', $user->id);
                    });
                }
            })
            ->get();

        $claimedVoucherIds = $user ? $user->vouchers()->pluck('voucher_id')->toArray() : [];

        $vouchers = $dbVouchers->filter(function($v) use ($claimedVoucherIds) {
            return !in_array($v->id, $claimedVoucherIds);
        })->map(function($v) {
            return [
                'id' => $v->id,
                'code' => $v->code,
                'title' => $v->type === 'gratis_ongkir' ? 'Gratis Ongkir ' . $v->formatted_discount : 'Diskon ' . $v->formatted_discount,
                'desc' => 'Min. belanja ' . $v->formatted_min_spend,
                'badge' => strtoupper(str_replace('_', ' ', $v->type)),
                'expiry' => $v->expires_at ? 'Berlaku s/d ' . \Carbon\Carbon::parse($v->expires_at)->format('d M Y') : 'Berlaku Selamanya',
                'is_claimed' => false
            ];
        })->toArray();

        // Dummy vouchers removed as requested

        $heroBanner = \App\Models\Banner::where('status', 'aktif')->orderBy('order_column', 'asc')->first();

        return view('pages.promo', [
            'heroBanner'       => $heroBanner,
            'promoProducts'    => $allProducts,
            'vouchers'         => $vouchers,
            'selectedCategory' => $selectedCategory
        ]);
    }

    /**
     * Display a dynamic page created from CMS.
     */
    public function dynamicPage($slug)
    {
        $page = \App\Models\Page::where('slug', $slug)->where('status', 'aktif')->firstOrFail();

        $template = $page->template ?? 'default';
        
        if (view()->exists('pages.templates.' . $template)) {
            return view('pages.templates.' . $template, compact('page'));
        }

        return view('pages.templates.default', compact('page'));
    }

    /**
     * Display the list of articles (Berita/Blog).
     */
    public function articlesIndex(Request $request)
    {
        $search = $request->query('q');
        $tag    = $request->query('tag');

        $query = \App\Models\Article::where('status', 'published');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($tag && $tag !== 'semua') {
            $query->where(function($q) use ($tag) {
                $q->where('title', 'like', "%{$tag}%")
                  ->orWhere('summary', 'like', "%{$tag}%");
            });
        }

        $articles = $query->latest()->paginate(12)->withQueryString();

        return view('articles.index', compact('articles', 'search', 'tag'));
    }

    /**
     * Display a specific article detail.
     */
    public function articleDetail($slug)
    {
        $article = \App\Models\Article::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedArticles = \App\Models\Article::where('status', 'published')
            ->where('id', '!=', $article->id)
            ->latest()
            ->take(3)
            ->get();

        return view('articles.show', compact('article', 'relatedArticles'));
    }

    /**
     * Display the Notification Center Page.
     */
    public function notificationsIndex(Request $request)
    {
        $userId = auth()->id() ?? (session('user.id') ?? null);
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silakan masuk untuk melihat notifikasi.');
        }

        $filter = $request->query('filter', 'semua');

        $baseQuery = \App\Models\Announcement::where('status', 'ditayangkan')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('user_id')
                           ->where('type', '!=', 'notifikasi');
                  });
            });

        $query = clone $baseQuery;

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'orders') {
            $query->where(function($q) {
                $q->where('title', 'like', '%pesanan%')
                  ->orWhere('title', 'like', '%retur%')
                  ->orWhere('title', 'like', '%refund%')
                  ->orWhere('title', 'like', '%batal%')
                  ->orWhere('title', 'like', '%inv%')
                  ->orWhere('content', 'like', '%pesanan%')
                  ->orWhere('content', 'like', '%invoice%')
                  ->orWhere('content', 'like', '%retur%')
                  ->orWhere('content', 'like', '%resi%');
            });
        } elseif ($filter === 'promo') {
            $query->where(function($q) {
                $q->where('title', 'like', '%promo%')
                  ->orWhere('title', 'like', '%diskon%')
                  ->orWhere('title', 'like', '%voucher%')
                  ->orWhere('content', 'like', '%promo%')
                  ->orWhere('content', 'like', '%diskon%')
                  ->orWhere('content', 'like', '%voucher%');
            });
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'semua' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->where('is_read', false)->count(),
            'orders' => (clone $baseQuery)->where(function($q) {
                $q->where('title', 'like', '%pesanan%')
                  ->orWhere('title', 'like', '%retur%')
                  ->orWhere('title', 'like', '%refund%')
                  ->orWhere('title', 'like', '%batal%')
                  ->orWhere('title', 'like', '%inv%')
                  ->orWhere('content', 'like', '%pesanan%')
                  ->orWhere('content', 'like', '%invoice%')
                  ->orWhere('content', 'like', '%retur%')
                  ->orWhere('content', 'like', '%resi%');
            })->count(),
            'promo' => (clone $baseQuery)->where(function($q) {
                $q->where('title', 'like', '%promo%')
                  ->orWhere('title', 'like', '%diskon%')
                  ->orWhere('title', 'like', '%voucher%')
                  ->orWhere('content', 'like', '%promo%')
                  ->orWhere('content', 'like', '%diskon%')
                  ->orWhere('content', 'like', '%voucher%');
            })->count(),
        ];

        return view('pages.notifications', compact('notifications', 'filter', 'counts'));
    }

    /**
     * AJAX/Form: Mark single notification as read.
     */
    public function markNotificationAsRead($id)
    {
        $userId = auth()->id() ?? (session('user.id') ?? null);
        $ann = \App\Models\Announcement::where('id', $id)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->first();

        if ($ann) {
            $ann->update(['is_read' => true]);
        }

        $unreadCount = \App\Models\Announcement::where('status', 'ditayangkan')
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere(function($subQ) {
                    $subQ->whereNull('user_id')->where('type', '!=', 'notifikasi');
                });
            })
            ->where('is_read', false)
            ->count();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'announcement' => $ann
            ]);
        }

        return back()->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Mark a specific notification as read and redirect to target page or notifications list.
     */
    public function readNotification($id)
    {
        $ann = \App\Models\Announcement::find($id);
        if ($ann) {
            $ann->update(['is_read' => true]);

            $text = $ann->title . ' ' . $ann->content;

            // Check if invoice number is present, e.g. #INV-20261013-TK-A2A28426 or INV/20261008/TK/7E44A19D
            if (preg_match('/(INV[-\/][A-Za-z0-9\-\/]+)/i', $text, $matches)) {
                $invoice = $matches[1];
                return redirect()->route('orders', ['search' => $invoice]);
            }

            // Check if waybill / resi number is present
            if (preg_match('/(?:Resi|waybill)[:\s]+([A-Za-z0-9\-]+)/i', $text, $matches)) {
                $waybill = $matches[1];
                return redirect()->route('tracking.public', ['waybill' => $waybill]);
            }

            $searchStr = strtolower($text);
            if (str_contains($searchStr, 'promo') || str_contains($searchStr, 'diskon') || str_contains($searchStr, 'voucher') || str_contains($searchStr, 'vocer')) {
                return redirect()->route('promo');
            } elseif (str_contains($searchStr, 'pesanan') || str_contains($searchStr, 'order') || str_contains($searchStr, 'dikemas') || str_contains($searchStr, 'dikirim') || str_contains($searchStr, 'retur')) {
                return redirect()->route('orders');
            }
        }

        return redirect()->route('notifications.index');
    }

    /**
     * Mark all active notifications for current user as read.
     */
    public function readAllNotifications()
    {
        $userId = auth()->id() ?? (session('user.id') ?? null);
        if ($userId) {
            \App\Models\Announcement::where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereNull('user_id');
            })->where('is_read', false)->update(['is_read' => true]);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'message' => 'Semua notifikasi telah ditandai sebagai dibaca.'
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
