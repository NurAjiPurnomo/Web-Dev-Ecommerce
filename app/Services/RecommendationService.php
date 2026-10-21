<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Catat aktivitas melihat produk ke session (untuk real-time affinity baik tamu maupun user login).
     *
     * @param int $productId
     * @param string|null $category
     * @return void
     */
    public function recordProductView(int $productId, ?string $category = null): void
    {
        try {
            // Catat ID produk
            $viewedIds = session()->get('recent_viewed_product_ids', []);
            $viewedIds = array_diff($viewedIds, [$productId]); // hapus duplikat lama
            array_unshift($viewedIds, $productId);
            session()->put('recent_viewed_product_ids', array_slice($viewedIds, 0, 15));

            // Catat Kategori produk
            if (!empty($category)) {
                $viewedCats = session()->get('recent_viewed_categories', []);
                $viewedCats = array_diff($viewedCats, [$category]);
                array_unshift($viewedCats, $category);
                session()->put('recent_viewed_categories', array_slice($viewedCats, 0, 5));
            }
        } catch (\Throwable $e) {
            // Fail gracefully jika session terkendala
        }
    }

    /**
     * Dapatkan skor afinitas (ketertarikan) kategori user berdasarkan riwayat belanja, wishlist, dan riwayat klik.
     *
     * @param int|null $userId
     * @return array [ 'Kategori A' => skor_bobot, 'Kategori B' => skor_bobot, ... ]
     */
    public function getUserAffinityCategories(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        $categoryScores = [];

        // 1. Sinyal dari Riwayat Pesanan (Bobot Tertinggi: 3 poin per item)
        if ($userId) {
            $orderProductIds = OrderItem::whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('status', '!=', 'cancelled');
            })
            ->pluck('product_id')
            ->toArray();

            if (!empty($orderProductIds)) {
                $purchasedCategories = Product::whereIn('id', $orderProductIds)
                    ->pluck('category')
                    ->filter()
                    ->toArray();

                foreach ($purchasedCategories as $cat) {
                    $categoryScores[$cat] = ($categoryScores[$cat] ?? 0) + 3;
                }
            }

            // 2. Sinyal dari Wishlist / Favorit (Bobot Sedang: 2 poin per item)
            $wishlistProductIds = Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();

            if (!empty($wishlistProductIds)) {
                $wishlistCategories = Product::whereIn('id', $wishlistProductIds)
                    ->pluck('category')
                    ->filter()
                    ->toArray();

                foreach ($wishlistCategories as $cat) {
                    $categoryScores[$cat] = ($categoryScores[$cat] ?? 0) + 2;
                }
            }
        }

        // 3. Sinyal dari Aktivitas Real-time Session (Bobot Real-time: 1.5 poin)
        $sessionCategories = session()->get('recent_viewed_categories', []);
        if (is_array($sessionCategories)) {
            foreach ($sessionCategories as $cat) {
                if (!empty($cat)) {
                    $categoryScores[$cat] = ($categoryScores[$cat] ?? 0) + 1.5;
                }
            }
        }

        // Urutkan kategori berdasarkan skor tertinggi
        arsort($categoryScores);

        return $categoryScores;
    }

    /**
     * Hitung Skor Kualitas Produk (Weighted Quality Score)
     * Mengkombinasikan: Rating (35%), Penjualan (30%), Promo/Diskon (15%), Kebaruan (20%)
     *
     * @param Product $product
     * @return float Skor ternormalisasi (~20 - 100)
     */
    public function calculateProductQualityScore(Product $product): float
    {
        // 1. Rating Score (0 - 100)
        // Jika ada ulasan, hitung persentase dari rating 5.0. Jika belum ada ulasan, beri skor netral (70)
        $avgRating = $product->attributes['reviews_avg_rating'] ?? null;
        if ($avgRating !== null && $avgRating > 0) {
            $ratingScore = min(100, ((float) $avgRating / 5.0) * 100);
        } else {
            $ratingScore = 70.0; // Netral untuk produk baru tanpa ulasan
        }

        // 2. Sales Score (0 - 100) menggunakan skala logaritmik agar fair
        // log10(sold + 1) * 35: 0 terjual => 0 | 10 terjual => 36.4 | 100 terjual => 70.1 | 500+ terjual => ~95+
        $sold = (int) ($product->sold ?? 0);
        $salesScore = min(100, log10($sold + 1) * 35);

        // 3. Discount / Promo Score (0 - 100)
        $discountScore = 0.0;
        if (!empty($product->discount)) {
            $discountScore = 75.0;
        } elseif ($product->original_price && $product->original_price > $product->price) {
            $pct = round((($product->original_price - $product->price) / $product->original_price) * 100);
            $discountScore = min(100, max(50, $pct * 2.5));
        }

        // 4. Recency Score (0 - 100)
        // Produk < 7 hari = 100, meluruh bertahap hingga minimum 25 untuk produk lama
        $daysOld = $product->created_at ? $product->created_at->diffInDays(now()) : 30;
        if ($daysOld <= 7) {
            $recencyScore = 100.0;
        } elseif ($daysOld <= 30) {
            $recencyScore = max(50.0, 100.0 - (($daysOld - 7) * 2.1));
        } else {
            $recencyScore = max(25.0, 50.0 - (($daysOld - 30) * 0.3));
        }

        // Bobot Komposit Standar Industri
        // Rating: 35%, Terjual: 30%, Diskon: 15%, Kebaruan: 20%
        return ($ratingScore * 0.35) +
               ($salesScore * 0.30) +
               ($discountScore * 0.15) +
               ($recencyScore * 0.20);
    }

    /**
     * Dapatkan daftar produk rekomendasi hibrida (Hybrid Recommendations)
     *
     * @param int $limit Total jumlah produk yang diinginkan (default 18)
     * @param array $excludeIds ID produk yang ingin dilewati (misal sudah ada di flash sale)
     * @return array Array produk yang siap dirender di blade component <x-product-card>
     */
    public function getHybridRecommendations(int $limit = 18, array $excludeIds = []): array
    {
        // Ambil semua produk aktif lengkap dengan rata-rata rating
        $baseQuery = Product::where('status', 'aktif')
            ->withAvg('reviews', 'rating');

        if (!empty($excludeIds)) {
            $baseQuery->whereNotIn('id', $excludeIds);
        }

        $allProducts = $baseQuery->get();

        if ($allProducts->isEmpty()) {
            return [];
        }

        // Hitung skor kualitas untuk setiap produk
        $allProducts->each(function ($product) {
            $product->computed_score = $this->calculateProductQualityScore($product);
        });

        // Cek Minat Kategori User
        $affinityScores = $this->getUserAffinityCategories();
        $preferredCategories = array_keys(array_filter($affinityScores, fn($score) => $score > 0));

        $selectedProducts = collect();
        $selectedIds = [];

        if (!empty($preferredCategories)) {
            // SKENARIO 1: USER MEMILIKI MINAT KATEGORI (HYBRID PERSONALIZED)
            // Alokasi: 60% (~11 item) dari kategori minat, 40% (~7 item) dari tren kategori lain
            $personalQuota = (int) ceil($limit * 0.6);
            $diversityQuota = $limit - $personalQuota;

            // Produk Kategori Minat
            $personalizedCandidates = $allProducts->filter(function ($p) use ($preferredCategories) {
                return in_array($p->category, $preferredCategories);
            })->sortByDesc('computed_score');

            $pickedPersonal = $personalizedCandidates->take($personalQuota);
            foreach ($pickedPersonal as $p) {
                $selectedProducts->push($p);
                $selectedIds[] = $p->id;
            }

            // Produk Kategori Lain (Eksplorasi & Diversitas)
            $otherCandidates = $allProducts->filter(function ($p) use ($selectedIds) {
                return !in_array($p->id, $selectedIds);
            })->sortByDesc('computed_score');

            // Tambahkan kuota eksplorasi (ditambah sisa jika produk kategori minat kurang dari kuotanya)
            $remainingNeeded = $limit - $selectedProducts->count();
            $pickedOthers = $otherCandidates->take($remainingNeeded);
            foreach ($pickedOthers as $p) {
                $selectedProducts->push($p);
                $selectedIds[] = $p->id;
            }

            // Jika masih kurang karena total produk toko sedikit, ambil sisanya yang belum terpilih
            if ($selectedProducts->count() < $limit) {
                $filler = $allProducts->whereNotIn('id', $selectedIds)->take($limit - $selectedProducts->count());
                foreach ($filler as $p) {
                    $selectedProducts->push($p);
                    $selectedIds[] = $p->id;
                }
            }

            // Interleave (selang-seling halus) agar kategori minat tidak kaku menumpuk di depan saja
            $finalList = $this->blendProducts($pickedPersonal, $pickedOthers, $limit);
        } else {
            // SKENARIO 2: TAMU / USER BARU (COLD START - WEIGHTED DIVERSITY)
            // Urutkan semua produk berdasarkan skor kualitas tertinggi
            $sortedByScore = $allProducts->sortByDesc('computed_score');

            // Diversifikasi: kelompokkan berdasarkan kategori agar tidak 1 kategori mendominasi
            $finalList = $this->diversifyProducts($sortedByScore, $limit);
        }

        // Format hasil akhir sesuai ekspektasi x-product-card
        return $finalList->map(function ($p) {
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
                'variants'      => $p->formatted_variants,
            ];
        })->values()->all();
    }

    /**
     * Selang-seling antara produk personalisasi (60%) dan produk penemuan kategori lain (40%)
     */
    protected function blendProducts(Collection $personalized, Collection $discovery, int $limit): Collection
    {
        $result = collect();
        $pItems = $personalized->values();
        $dItems = $discovery->values();

        $pIndex = 0;
        $dIndex = 0;

        while ($result->count() < $limit && ($pIndex < $pItems->count() || $dIndex < $dItems->count())) {
            // Beri 2 produk personalisasi
            if ($pIndex < $pItems->count()) {
                $result->push($pItems->get($pIndex++));
            }
            if ($pIndex < $pItems->count() && $result->count() < $limit) {
                $result->push($pItems->get($pIndex++));
            }
            // Diselingi 1 produk penemuan/eksplorasi
            if ($dIndex < $dItems->count() && $result->count() < $limit) {
                $result->push($dItems->get($dIndex++));
            }
        }

        return $result;
    }

    /**
     * Diversifikasi produk lintas kategori untuk tamu (Cold Start) menggunakan Round-Robin
     */
    protected function diversifyProducts(Collection $products, int $limit): Collection
    {
        // Kelompokkan produk berdasarkan kategori
        $byCategory = [];
        foreach ($products as $p) {
            $cat = $p->category ?: 'Lainnya';
            $byCategory[$cat][] = $p;
        }

        $result = collect();
        $hasMore = true;

        // Ambil 1 produk dari tiap kategori secara bergiliran (Round Robin)
        while ($hasMore && $result->count() < $limit) {
            $hasMore = false;
            foreach ($byCategory as $cat => &$items) {
                if (!empty($items)) {
                    $result->push(array_shift($items));
                    $hasMore = true;
                    if ($result->count() >= $limit) {
                        break 2;
                    }
                }
            }
        }

        // Jika setelah round robin masih ada kuota yang belum terpenuhi, isi dari sisa produk tertinggi
        if ($result->count() < $limit) {
            $existingIds = $result->pluck('id')->toArray();
            $remaining = $products->whereNotIn('id', $existingIds)->take($limit - $result->count());
            $result = $result->concat($remaining);
        }

        return $result;
    }
}
