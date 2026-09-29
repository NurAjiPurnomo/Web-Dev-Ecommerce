<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RecentPurchasesService
{
    /**
     * Get list of recent purchases for marquee running ticker.
     * Merges real DB orders with realistic dynamic fallbacks if needed.
     */
    public static function getRecentPurchases(int $limit = 15): array
    {
        $purchases = [];

        try {
            $dbOrders = Order::with('items', 'user')
                ->latest()
                ->take($limit)
                ->get();

            foreach ($dbOrders as $order) {
                $rawName = $order->recipient_name ?? ($order->user->name ?? 'Pelanggan');
                $nameFormatted = static::maskName($rawName);

                $location = $order->destination_district ?? null;
                if (!$location && !empty($order->shipping_address)) {
                    $parts = array_filter(array_map('trim', explode(',', $order->shipping_address)));
                    if (!empty($parts)) {
                        $location = end($parts);
                    }
                }
                if (!$location || strlen($location) > 22) {
                    $location = static::getRandomCity();
                }

                $firstItem = $order->items->first();
                if ($firstItem) {
                    $product = null;
                    if (!empty($firstItem->product_id)) {
                        $product = Product::find($firstItem->product_id);
                    }
                    if (!$product) {
                        $product = Product::where('name', 'like', '%' . Str::limit($firstItem->product_name, 15, '') . '%')->first();
                    }

                    $productUrl = $product ? route('product.detail', $product->id) : route('catalog');
                    $productImage = $product->image ?? null;

                    $timeAgo = static::formatTimeAgo($order->created_at);

                    $purchases[] = [
                        'name' => $nameFormatted,
                        'location' => $location,
                        'product_name' => $firstItem->product_name ?? 'Produk Pilihan',
                        'quantity' => $firstItem->quantity ?? 1,
                        'time_ago' => $timeAgo,
                        'product_url' => $productUrl,
                        'product_image' => $productImage,
                        'is_real' => true,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silence any DB error fallback
        }

        // If less than $limit, supplement with realistic dynamic purchase entries using actual store products
        if (count($purchases) < $limit) {
            $needed = $limit - count($purchases);
            $activeProducts = Product::where('status', 'aktif')->orWhereNull('status')->get();
            if ($activeProducts->isEmpty()) {
                $activeProducts = Product::all();
            }

            $sampleFullNames = [
                'Budi Santoso', 'Siti Aminah', 'Rizky Pratama', 'Dewi Lestari', 'Eko Wijaya', 
                'Fitri Nuraini', 'Rian Maulana', 'Anisa Rahma', 'Fikri Ramadhan', 'Nadia Kusuma', 
                'Bayu Triatmo', 'Dwi Agustina', 'Ahmad Fauzi', 'Indah Permata', 'Rizal Kurniawan',
                'Hendra Budiman', 'Maya Syafira', 'Gilang Ramadan', 'Laras Tyas', 'Dimas Vidianto'
            ];

            $sampleCities = [
                'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 
                'Yogyakarta', 'Makassar', 'Palembang', 'Denpasar', 'Malang',
                'Tangerang', 'Bekasi', 'Depok', 'Bogor', 'Solo', 'Balikpapan'
            ];

            $sampleTimes = ['Baru saja', '2 menit lalu', '5 menit lalu', '12 menit lalu', '25 menit lalu', '45 menit lalu', '1 jam lalu', '3 jam lalu', '6 jam lalu', '12 jam lalu', '18 jam lalu', '22 jam lalu'];

            for ($i = 0; $i < $needed; $i++) {
                $product = $activeProducts->isNotEmpty() ? $activeProducts[$i % $activeProducts->count()] : null;
                $pName = $product ? $product->name : 'Produk Best Seller';
                $pUrl = $product ? route('product.detail', $product->id) : route('catalog');
                $pImage = $product ? $product->image : null;

                $rawName = $sampleFullNames[$i % count($sampleFullNames)];
                $name = static::maskName($rawName);
                $city = $sampleCities[($i * 3) % count($sampleCities)];
                $time = $sampleTimes[$i % count($sampleTimes)];

                $purchases[] = [
                    'name' => $name,
                    'location' => $city,
                    'product_name' => $pName,
                    'quantity' => rand(1, 2),
                    'time_ago' => $time,
                    'product_url' => $pUrl,
                    'product_image' => $pImage,
                    'is_real' => false,
                ];
            }
        }

        return $purchases;
    }

    /**
     * Format time ago safely as integer hours/minutes (max 24 hours per day)
     */
    private static function formatTimeAgo($createdAt): string
    {
        try {
            $carbon = Carbon::parse($createdAt);
            $now = Carbon::now();

            $diffMins = (int) max(1, floor($carbon->diffInMinutes($now)));
            
            if ($diffMins < 60) {
                return $diffMins . ' menit lalu';
            }

            $diffHours = (int) max(1, floor($carbon->diffInHours($now)));
            
            // Normalize hours to max 24 hours per day
            if ($diffHours >= 24) {
                $diffHours = ($diffHours % 23) + 1;
            }

            return $diffHours . ' jam lalu';
        } catch (\Throwable $e) {
            return 'Baru saja';
        }
    }

    /**
     * Mask sensitive customer names (e.g. Budi Santoso -> B**i S******o / J******ana)
     */
    private static function maskName(string $fullName): string
    {
        $fullName = trim($fullName);
        if (empty($fullName)) {
            return 'P******n';
        }

        $parts = array_filter(explode(' ', $fullName));
        $maskedParts = [];

        foreach ($parts as $part) {
            $len = mb_strlen($part);
            if ($len <= 2) {
                $maskedParts[] = mb_substr($part, 0, 1) . '*';
            } elseif ($len <= 4) {
                $maskedParts[] = mb_substr($part, 0, 1) . '**' . mb_substr($part, -1);
            } else {
                $asterisks = str_repeat('*', min(6, $len - 2));
                $maskedParts[] = mb_substr($part, 0, 1) . $asterisks . mb_substr($part, -1);
            }
        }

        return implode(' ', $maskedParts);
    }

    private static function getRandomCity(): string
    {
        $cities = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Yogyakarta', 'Makassar', 'Bali'];
        return $cities[array_rand($cities)];
    }
}
