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
                ->take(5);

            if ($bestSellerModels->count() < 5) {
                $needed = 5 - $bestSellerModels->count();
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
                ->take(5)
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
                'name'    => 'Pakaian',
                'slug'    => 'Pakaian',
                'icon'    => 'M9 2a1 1 0 00-.894.553L6.382 6H3a1 1 0 00-1 1v4a1 1 0 001 1h2v8a1 1 0 001 1h12a1 1 0 001-1v-8h2a1 1 0 001-1V7a1 1 0 00-1-1h-3.382l-1.724-3.447A1 1 0 0015 2H9z',
                'emoji'   => '👕',
                'bgLight' => 'bg-gradient-to-br from-blue-50 to-indigo-100 text-blue-700 border-blue-200 group-hover:from-blue-600 group-hover:to-indigo-700 group-hover:text-white group-hover:border-blue-600 shadow-2xs'
            ],
            [
                'name'    => 'Sepatu',
                'slug'    => 'Sepatu',
                'icon'    => 'M3 17a3 3 0 003 3h12a3 3 0 003-3v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3zM14 7l-4 5m0-5l4 5',
                'emoji'   => '👟',
                'bgLight' => 'bg-gradient-to-br from-emerald-50 to-teal-100 text-emerald-700 border-emerald-200 group-hover:from-emerald-600 group-hover:to-teal-700 group-hover:text-white group-hover:border-emerald-600 shadow-2xs'
            ],
            [
                'name'    => 'Aksesoris',
                'slug'    => 'Aksesoris',
                'icon'    => 'M2 10a4 4 0 014-4h2a4 4 0 014 4v1a4 4 0 01-4 4H6a4 4 0 01-4-4v-1zm10 0a4 4 0 014-4h2a4 4 0 014 4v1a4 4 0 01-4 4h-2a4 4 0 01-4-4v-1zm-2 0h2',
                'emoji'   => '🕶️',
                'bgLight' => 'bg-gradient-to-br from-purple-50 to-pink-100 text-purple-700 border-purple-200 group-hover:from-purple-600 group-hover:to-pink-700 group-hover:text-white group-hover:border-purple-600 shadow-2xs'
            ],
            [
                'name'    => 'Gadget',
                'slug'    => 'Gadget',
                'icon'    => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
                'emoji'   => '📱',
                'bgLight' => 'bg-gradient-to-br from-amber-50 to-orange-100 text-amber-700 border-amber-200 group-hover:from-amber-500 group-hover:to-orange-600 group-hover:text-white group-hover:border-amber-500 shadow-2xs'
            ],
            [
                'name'    => 'Rumah Tangga',
                'slug'    => 'Rumah Tangga',
                'icon'    => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'emoji'   => '🏠',
                'bgLight' => 'bg-gradient-to-br from-rose-50 to-red-100 text-rose-700 border-rose-200 group-hover:from-rose-600 group-hover:to-red-700 group-hover:text-white group-hover:border-rose-600 shadow-2xs'
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

        // If there are fewer than 5 promo products, fill up with latest active products
        if ($promoModels->count() < 5) {
            $needed = 5 - $promoModels->count();
            $existingIds = $promoModels->pluck('id')->toArray();
            $additionalPromo = Product::where('status', 'aktif')
                ->withAvg('reviews', 'rating')
                ->whereNotIn('id', $existingIds)
                ->latest()
                ->take($needed)
                ->get();
            $promoModels = $promoModels->concat($additionalPromo);
        } else {
            $promoModels = $promoModels->take(5);
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

        $recommendedProducts = $allProducts->values()->all();
        $banners             = \App\Models\Banner::where('status', 'aktif')->orderBy('order_column', 'asc')->get();

        return view('pages.home', compact('banners', 'flashSaleProducts', 'bestSellerProducts', 'recommendedProducts', 'categories'));
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
            $query->where('name', 'like', "%{$search}%");
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

        $paginatedProducts = $query->paginate(12)->withQueryString();

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

        $reviews = \App\Models\ProductReview::with('user')
            ->where('product_id', $dbProduct->id)
            ->latest()
            ->get();

        $ratingCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];
        
        $reviewImages = $reviews->whereNotNull('image')->pluck('image')->all();


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
            : ['S', 'M', 'L', 'XL'];

        // Dynamic colors
        $productColors = (!empty($dbProduct->colors) && is_array($dbProduct->colors))
            ? array_map(function($c) {
                return is_array($c) ? ($c['name'] ?? 'Varian') : $c;
            }, $dbProduct->colors)
            : ['Navy Blue', 'Hitam', 'Silver / White'];

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
            ->take(5)
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
    public function promo()
    {
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

        if ($promoModels->isEmpty()) {
            $promoModels = Product::where('status', 'aktif')->withAvg('reviews', 'rating')->latest()->take(8)->get();
        }

        $allProducts = $promoModels->map(function($p) {
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
            'heroBanner'    => $heroBanner,
            'promoProducts' => $allProducts,
            'vouchers'      => $vouchers,
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
}
