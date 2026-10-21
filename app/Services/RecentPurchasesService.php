<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RecentPurchasesService
{
    /**
     * Get list of 100% REAL recent purchases created in the last 24 hours.
     * ZERO dummy or generated fallback entries.
     * ONLY includes orders where payment has been completed / confirmed (not pending or cancelled).
     */
    public static function getRecentPurchases(int $limit = 15): array
    {
        $purchases = [];

        try {
            // Only fetch REAL paid/confirmed orders created in the last 24 hours
            // Excludes unpaid ('belum_dibayar', 'belum_bayar') and cancelled ('batal') orders
            $dbOrders = Order::with('items', 'user')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->whereNotIn('status', ['belum_dibayar', 'belum_bayar', 'batal'])
                ->latest()
                ->take($limit)
                ->get();

            foreach ($dbOrders as $order) {
                $rawName = $order->recipient_name ?: ($order->user->name ?? 'Pelanggan');
                $nameFormatted = ProductReview::maskName($rawName);

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

                    // Use updated_at (when payment/confirmation was processed) or created_at
                    $timestamp = $order->updated_at ?? $order->created_at;
                    $timeAgo = static::formatTimeAgo($timestamp);

                    $purchases[] = [
                        'name'          => $nameFormatted,
                        'product_name'  => $firstItem->product_name ?? 'Produk Pilihan',
                        'quantity'      => $firstItem->quantity ?? 1,
                        'time_ago'      => $timeAgo,
                        'product_url'   => $productUrl,
                        'product_image' => $productImage,
                        'is_real'       => true,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silence errors
        }

        return $purchases;
    }

    /**
     * Format time ago accurately (within the 24-hour window).
     */
    private static function formatTimeAgo($timestamp): string
    {
        try {
            $carbon = Carbon::parse($timestamp);
            $now = Carbon::now();

            $diffSecs = (int) max(0, $carbon->diffInSeconds($now));
            
            if ($diffSecs < 60) {
                return 'Baru saja';
            }

            $diffMins = (int) floor($diffSecs / 60);
            if ($diffMins < 60) {
                return $diffMins . ' menit lalu';
            }

            $diffHours = (int) floor($diffMins / 60);
            if ($diffHours < 24) {
                return $diffHours . ' jam lalu';
            }

            return '1 hari lalu';
        } catch (\Throwable $e) {
            return 'Baru saja';
        }
    }
}
