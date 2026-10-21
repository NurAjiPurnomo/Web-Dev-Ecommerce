<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'product_id',
        'rating',
        'comment',
        'variant',
        'image',
        'is_anonymous',
        'likes_count',
    ];

    public function likes()
    {
        return $this->hasMany(ReviewLike::class, 'review_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Mask real name with asterisks Shopee/Tokopedia style (e.g. "Nur Aji Purnomo" -> "N***o").
     */
    public static function maskName(?string $name): string
    {
        $name = trim($name ?? '');
        if (empty($name)) {
            return 'P***a';
        }

        $len = mb_strlen($name);
        if ($len <= 2) {
            return mb_substr($name, 0, 1) . '*';
        }

        $first = mb_substr($name, 0, 1);
        $last  = mb_substr($name, -1);

        return $first . '***' . $last;
    }

    /**
     * Accessor for review display name based on is_anonymous preference.
     */
    public function getDisplayNameAttribute(): string
    {
        $realName = $this->user->name ?? 'Pembeli Setia';
        return $this->is_anonymous ? self::maskName($realName) : $realName;
    }
}
