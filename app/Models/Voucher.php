<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'discount_value',
        'min_spend',
        'max_discount',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function getFormattedDiscountAttribute()
    {
        if ($this->type === 'diskon_persen') {
            return $this->discount_value . '%';
        }
        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }

    public function getFormattedMinSpendAttribute()
    {
        return 'Rp ' . number_format($this->min_spend, 0, ',', '.');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
                    ->withPivot('is_used', 'used_at')
                    ->withTimestamps();
    }
}
