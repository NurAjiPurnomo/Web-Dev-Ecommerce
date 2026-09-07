<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'original_price',
        'discount',
        'stock',
        'sold',
        'image',
        'description',
        'size_guide_image',
        'sizes',
        'colors',
        'images',
        'variants',
        'status',
    ];

    protected $casts = [
        'sizes' => 'array',
        'colors' => 'array',
        'images' => 'array',
        'variants' => 'array',
    ];

    /**
     * Helper to get formatted variants mapping for Alpine JS
     */
    public function getFormattedVariantsAttribute()
    {
        $result = [];
        if (is_array($this->variants)) {
            foreach ($this->variants as $variant) {
                // Gunakan harga varian jika ada, atau fallback ke harga dasar
                $price = !empty($variant['price']) ? $variant['price'] : $this->price;
                $formattedPrice = 'Rp ' . number_format($price, 0, ',', '.');
                
                if (!empty($variant['size'])) {
                    $result[$variant['size']] = $formattedPrice;
                } elseif (isset($variant['sizes']) && is_array($variant['sizes'])) {
                    // Backward compatibility for old data
                    foreach ($variant['sizes'] as $size) {
                        $result[$size] = $formattedPrice;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Helper to get variants list with guaranteed unique IDs for each variant item
     */
    public function getVariantsWithIdsAttribute()
    {
        $list = $this->variants ?? [];
        if (!is_array($list)) return [];

        $index = 0;
        return array_map(function ($var) use (&$index) {
            if (is_array($var)) {
                if (empty($var['id'])) {
                    $var['id'] = 'var_' . $this->id . '_' . $index;
                }
                if (empty($var['variant_id'])) {
                    $var['variant_id'] = $var['id'];
                }
                if (!empty($var['image']) && empty($var['image_id'])) {
                    $var['image_id'] = 'img_' . $this->id . '_' . md5($var['image']);
                }
            }
            $index++;
            return $var;
        }, $list);
    }

    /**
     * Helper to format price as Rupiah string
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Helper to format original price as Rupiah string
     */
    public function getFormattedOriginalPriceAttribute()
    {
        return ($this->original_price && $this->original_price > $this->price) 
            ? 'Rp ' . number_format($this->original_price, 0, ',', '.') 
            : null;
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get dynamic average rating from reviews (default 0 if no reviews)
     */
    public function getAverageRatingAttribute()
    {
        if (array_key_exists('reviews_avg_rating', $this->attributes) && $this->attributes['reviews_avg_rating'] !== null) {
            return number_format((float) $this->attributes['reviews_avg_rating'], 1);
        }
        $avg = $this->reviews()->avg('rating');
        return $avg ? number_format($avg, 1) : '0';
    }

    /**
     * Get total review count
     */
    public function getReviewCountAttribute()
    {
        if (array_key_exists('reviews_count', $this->attributes) && $this->attributes['reviews_count'] !== null) {
            return (int) $this->attributes['reviews_count'];
        }
        return $this->reviews()->count();
    }
}
