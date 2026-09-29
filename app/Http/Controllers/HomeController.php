<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

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
        $query = Product::where('status', 'aktif')->withAvg('reviews', 'rating')->latest();

        $allProducts = $query->get()->values()->map(function($p) {
            return [
                'id'            => $p->id,
                'title'         => $p->name,
                'category'      => $p->category,
                'price'         => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'discount'      => $p->discount,
                'image'         => $p->image,
                'rating'        => $p->average_rating,
                'sold'          => $p->sold > 1000 ? round($p->sold / 1000, 1) . 'rb+' : ($p->sold > 0 ? $p->sold . '+' : '0'),
                'variants'      => $p->formatted_variants,
            ];
        });

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
                'sold'          => $p->sold > 1000 ? round($p->sold / 1000, 1) . 'rb+' : ($p->sold > 0 ? $p->sold . '+' : '0'),
                'rankBadge'     => $index < 3 ? 'TOP ' . ($index + 1) : 'BESTSELLER',
                'rankColor'     => $index === 0 ? 'bg-amber-500 text-white' : ($index === 1 ? 'bg-slate-400 text-white' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-blue-600 text-white')),
                'variants'      => $p->formatted_variants,
            ];
        })->all();

        $categories = [
            [
                'name'    => 'Pakaian & Fashion',
                'slug'    => 'Pakaian',
                'icon'    => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                'emoji'   => '👕',
                'bgLight' => 'bg-blue-50 text-blue-700 border-blue-200 group-hover:bg-blue-700 group-hover:text-white group-hover:border-blue-700 shadow-2xs'
            ],
            [
                'name'    => 'Sepatu & Sneakers',
                'slug'    => 'Sepatu',
                'icon'    => 'M3 17a3 3 0 003 3h12a3 3 0 003-3v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3zM14 7l-4 5m0-5l4 5',
                'emoji'   => '👟',
                'bgLight' => 'bg-emerald-50 text-emerald-700 border-emerald-200 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 shadow-2xs'
            ],
            [
                'name'    => 'Aksesoris',
                'slug'    => 'Aksesoris',
                'icon'    => 'M2 10a4 4 0 014-4h2a4 4 0 014 4v1a4 4 0 01-4 4H6a4 4 0 01-4-4v-1zm10 0a4 4 0 014-4h2a4 4 0 014 4v1a4 4 0 01-4 4h-2a4 4 0 01-4-4v-1zm-2 0h2',
                'emoji'   => '🕶️',
                'bgLight' => 'bg-purple-50 text-purple-700 border-purple-200 group-hover:bg-purple-600 group-hover:text-white group-hover:border-purple-600 shadow-2xs'
            ],
            [
                'name'    => 'Gadget',
                'slug'    => 'Gadget',
                'icon'    => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
                'emoji'   => '📱',
                'bgLight' => 'bg-amber-50 text-amber-700 border-amber-200 group-hover:bg-amber-500 group-hover:text-white group-hover:border-amber-500 shadow-2xs'
            ],
            [
                'name'    => 'Rumah Tangga',
                'slug'    => 'Rumah Tangga',
                'icon'    => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'emoji'   => '🏠',
                'bgLight' => 'bg-rose-50 text-rose-700 border-rose-200 group-hover:bg-rose-600 group-hover:text-white group-hover:border-rose-600 shadow-2xs'
            ]
        ];

        // AUTOMATIC FLASH SALE PRODUCTS: Fetch active products that have a discount or original_price set
        $promoModels = Product::where('status', 'aktif')
            ->withAvg('reviews', 'rating')
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
                })->orWhere(function($sub) {
                    $sub->whereNotNull('discount')->where('discount', '!=', '');
                });
            })
            ->latest('updated_at')
            ->get();

        // If there are fewer than 6 promo products, fill up with latest active products
        if ($promoModels->count() < 6) {
            $needed = 6 - $promoModels->count();
            $existingIds = $promoModels->pluck('id')->toArray();
            $additionalPromo = Product::where('status', 'aktif')
                ->withAvg('reviews', 'rating')
                ->whereNotIn('id', $existingIds)
                ->latest()
                ->take($needed)
                ->get();
            $promoModels = $promoModels->concat($additionalPromo);
        } else {
            $promoModels = $promoModels->take(6);
        }

        $flashSaleProducts = $promoModels->values()->map(function($p) {
            return [
                'id'            => $p->id,
                'title'         => $p->name,
                'category'      => $p->category,
                'price'         => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'discount'      => $p->discount,
                'image'         => $p->image,
                'rating'        => $p->average_rating,
                'sold'          => $p->sold > 1000 ? round($p->sold / 1000, 1) . 'rb+' : ($p->sold > 0 ? $p->sold . '+' : '0'),
                'variants'      => $p->formatted_variants,
            ];
        })->all();

        $recommendedProducts = $allProducts->take(18)->values()->all();
        $banners             = \App\Models\Banner::where('status', 'aktif')->orderBy('order_column', 'asc')->get();

        $storeSettings     = \App\Models\StoreSetting::getSettings();
        $flashSaleEndTime  = $storeSettings->flash_sale_end_time ? $storeSettings->flash_sale_end_time->format('Y-m-d\TH:i:s') : null;
        $flashSaleIsActive = $storeSettings->flash_sale_is_active ?? true;

        return view('pages.home', compact('banners', 'flashSaleProducts', 'bestSellerProducts', 'recommendedProducts', 'categories', 'flashSaleEndTime', 'flashSaleIsActive'));
    }

    /**
     * Display Catalog Page from Database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function catalog(Request $request)
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('category');
        $sort = $request->query('sort', 'latest');

        $query = Product::where('status', 'aktif')->withAvg('reviews', 'rating');

        if ($search) {
            // Memecah kata pencarian berdasarkan spasi (contoh: "jaket negeri" jadi "jaket" dan "negeri")
            $searchWords = explode(' ', $search);
            foreach ($searchWords as $word) {
                if (!empty($word)) {
                    // Cari masing-masing kata (harus ada semua kata tersebut di nama produk, urutan bebas)
                    $query->where('name', 'like', "%{$word}%");
                }
            }
        }

        if ($selectedCategory && $selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }

        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'popular') {
            $query->orderBy('sold', 'desc');
        } else {
            $query->latest();
        }
        $paginatedProducts = $query->paginate(18)->withQueryString();

        $paginatedProducts->getCollection()->transform(function($p) {
            return [
                'id' => $p->id,
                'title' => $p->name,
                'slug' => \Illuminate\Support\Str::slug($p->name),
                'category' => $p->category,
                'price' => $p->formatted_price,
                'originalPrice' => $p->formatted_original_price,
                'hasDiscount' => !empty($p->discount),
                'discount' => $p->discount,
                'image' => $p->image,
                'rating' => $p->average_rating,
                'sold' => $p->sold > 1000 ? round($p->sold / 1000, 1) . 'rb+' : ($p->sold > 0 ? $p->sold . '+' : '0'),
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

        return view('pages.catalog', compact('catalogProducts', 'categories', 'selectedCategory'));
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

        $rawReviews = \App\Models\ProductReview::with('user')
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

        $reviews = $rawReviews->map(function($r) {
            $name = $r->is_anonymous ? 'Pengguna Anonim' : ($r->user->name ?? 'Pembeli Setia');
            $avatar = $r->user->avatar ?? null;
            if (!$avatar) {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D8ABC&color=fff';
            }
            return [
                'id'          => $r->id,
                'userName'    => $name,
                'userAvatar'  => $avatar,
                'rating'      => (int) $r->rating,
                'comment'     => $r->comment ?: 'Tidak ada ulasan tertulis.',
                'date'        => $r->created_at ? $r->created_at->format('d M Y') : 'Baru saja',
                'variant'     => 'Variasi Standar',
                'images'      => $r->image ? [asset($r->image)] : [],
                'isVerified'  => true,
                'likes'       => rand(1, 5),
            ];
        })->toArray();
        
        $reviewImages = $rawReviews->whereNotNull('image')->pluck('image')->map(fn($img) => asset($img))->all();



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
            'sold'          => $dbProduct->sold > 1000 ? round($dbProduct->sold / 1000, 1) . 'rb+' : ($dbProduct->sold > 0 ? $dbProduct->sold . '+' : '0'),
            'stock'         => $dbProduct->stock,
            'description'   => $dbProduct->description ?: 'Produk berkualitas tinggi dari Toko Online Resmi kami.',
            'reviews'       => $reviews,
            'ratingCounts'  => $ratingCounts,
            'reviewImages'  => $reviewImages,
            'specs'         => [
                'Kategori'      => $dbProduct->category,
                'Stok Tersedia' => $dbProduct->stock . ' buah',
                'Kondisi'       => 'Baru (100% Original)',
                'Garansi'       => 'Garansi Resmi Toko 1 Tahun',
            ],
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
                    'sold' => $p->sold > 1000 ? round($p->sold / 1000, 1) . 'rb+' : ($p->sold > 0 ? $p->sold . '+' : '0'),
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

        $paginatedPromo = $query->paginate(18)->withQueryString();

        if ($paginatedPromo->isEmpty() && $selectedCategory === 'all') {
            $paginatedPromo = Product::where('status', 'aktif')->withAvg('reviews', 'rating')->latest()->paginate(18)->withQueryString();
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
                'sold' => $p->sold > 1000 ? round($p->sold / 1000, 1) . 'rb+' : ($p->sold > 0 ? $p->sold . '+' : '0'),
                'variants' => $p->formatted_variants,
            ];
        });

        $allProducts = $paginatedPromo;

        $dbVouchers = \App\Models\Voucher::where('status', 'aktif')->get();

        $user = \Illuminate\Support\Facades\Auth::user();
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
        $articles = \App\Models\Article::where('status', 'published')
            ->latest()
            ->paginate(12);

        return view('articles.index', compact('articles'));
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
     * Mark a specific notification as read and redirect to target page.
     */
    public function readNotification($id)
    {
        $ann = \App\Models\Announcement::find($id);
        if ($ann) {
            $ann->update(['is_read' => true]);

            $searchStr = strtolower($ann->title . ' ' . $ann->content);
            if (str_contains($searchStr, 'promo') || str_contains($searchStr, 'diskon') || str_contains($searchStr, 'voucher') || str_contains($searchStr, 'vocer')) {
                return redirect()->route('promo');
            } elseif (str_contains($searchStr, 'pesanan') || str_contains($searchStr, 'order') || str_contains($searchStr, 'dikemas') || str_contains($searchStr, 'dikirim')) {
                return redirect()->route('orders');
            }
        }

        return redirect()->route('home');
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

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
