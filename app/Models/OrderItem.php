<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'image',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Dapatkan gambar item pesanan yang akurat sesuai varian yang dipilih
     */
    public function getImageAttribute($value)
    {
        if (!empty($value)) {
            return $this->formatImageUrl($value);
        }

        if (!$this->product) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
        }

        $product = $this->product;
        $productName = $this->product_name ?? '';

        // 1. Cek apakah ada varian yang sesuai di data variants produk
        if (!empty($product->variants) && is_array($product->variants)) {
            // Prioritas 1: Cocokkan Warna DAN Ukuran
            foreach ($product->variants as $var) {
                if (!empty($var['image'])) {
                    $color = !empty($var['color']) ? trim($var['color']) : '';
                    $size  = !empty($var['size']) ? trim($var['size']) : '';

                    if ($color !== '' && $size !== '') {
                        if (
                            stripos($productName, $color) !== false &&
                            (stripos($productName, 'Ukuran: ' . $size) !== false || stripos($productName, $size) !== false)
                        ) {
                            return $this->formatImageUrl($var['image']);
                        }
                    }
                }
            }

            // Prioritas 2: Cocokkan Warna saja
            foreach ($product->variants as $var) {
                if (!empty($var['image'])) {
                    $color = !empty($var['color']) ? trim($var['color']) : '';
                    if ($color !== '' && stripos($productName, $color) !== false) {
                        return $this->formatImageUrl($var['image']);
                    }
                }
            }
        }

        // 2. Fallback ke gambar utama produk atau gambar pertama galeri
        if (!empty($product->image)) {
            return $this->formatImageUrl($product->image);
        }

        if (!empty($product->images) && is_array($product->images) && !empty($product->images[0])) {
            return $this->formatImageUrl($product->images[0]);
        }

        return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80';
    }

    /**
     * Format URL gambar agar selalu valid dan dapat diakses browser
     */
    protected function formatImageUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/storage/') || str_starts_with($url, '/')) {
            return $url;
        }

        return '/storage/' . ltrim($url, '/');
    }
}
