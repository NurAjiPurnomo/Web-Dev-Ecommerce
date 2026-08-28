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
                
                if (isset($variant['sizes']) && is_array($variant['sizes'])) {
                    foreach ($variant['sizes'] as $size) {
                        // Simpan harga dengan format Rupiah untuk setiap ukuran
                        $result[$size] = $formattedPrice;
                    }
                }
            }
        }
        return $result;
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
