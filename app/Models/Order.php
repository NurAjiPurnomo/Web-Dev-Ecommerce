<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'total',
        'courier',
        'courier_code',
        'courier_service',
        'payment_method',
        'status',
        'tracking_number',
        'biteship_order_id',
        'waybill_number',
        'waybill_pdf_url',
        'shipping_address',
        'recipient_name',
        'recipient_phone',
        'destination_area_id',
        'destination_postal_code',
        'destination_district',
        'destination_village',
        'destination_rt_rw',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->tracking_number)) {
                $courierPrefix = 'JT';
                if (!empty($order->courier) && str_contains(strtolower($order->courier), 'jne')) {
                    $courierPrefix = 'JNE';
                } elseif (!empty($order->courier) && str_contains(strtolower($order->courier), 'sicepat')) {
                    $courierPrefix = 'REG';
                }
                $order->tracking_number = $courierPrefix . date('Ymd') . rand(10000, 99999);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}
