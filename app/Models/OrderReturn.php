<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'description',
        'photo_proof',
        'video_proof',
        'solution_type',
        'return_tracking_number',
        'biteship_order_id',
        'biteship_tracking_id',
        'return_shipping_receipt',
        'replacement_tracking_number',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'status',
        'admin_notes',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
