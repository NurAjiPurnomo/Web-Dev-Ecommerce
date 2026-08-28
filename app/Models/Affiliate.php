<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
        'commission_rate',
        'total_sales',
        'commission_earned',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
