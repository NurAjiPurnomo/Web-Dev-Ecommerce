<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'sender_name',
        'sender_phone',
        'address_detail',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'biteship_area_id',
        'biteship_api_key',
        'active_couriers',
        'biteship_handling_fee',
        'biteship_shipping_discount',
        'min_order_for_discount',
        'biteship_round_shipping',
        'flash_sale_end_time',
        'flash_sale_is_active',
    ];

    protected $casts = [
        'active_couriers' => 'array',
        'flash_sale_end_time' => 'datetime',
        'flash_sale_is_active' => 'boolean',
    ];

    /**
     * Get or create default store settings.
     */
    public static function getSettings()
    {
        $setting = self::first();
        if (!$setting) {
            $setting = self::create([
                'store_name' => 'Toko Online Official',
                'sender_name' => 'Admin Toko',
                'sender_phone' => '081234567890',
                'address_detail' => 'Jl. Merdeka No. 123, RT 01 / RW 02',
                'village' => 'Gambir',
                'district' => 'Gambir',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'postal_code' => '10110',
                'biteship_area_id' => 'IDNP6IDCU31IDD327', // Default area ID if needed
                'active_couriers' => ['jne', 'jnt', 'sicepat', 'pos', 'tiki', 'gosend', 'grabexpress'],
            ]);
        }
        return $setting;
    }

    /**
     * Apply Custom Rates strategy (Handling fee, Subsidi ongkir, Pembulatan).
     */
    public static function applyCustomRates($originalPrice, $cartSubtotal = 0, self $store = null): int
    {
        if (!$store) {
            $store = self::getSettings();
        }

        $price = (float) $originalPrice;

        // 1. Biaya Extra Packing / Handling Fee
        if (!empty($store->biteship_handling_fee) && $store->biteship_handling_fee > 0) {
            $price += (float) $store->biteship_handling_fee;
        }

        // 2. Subsidi / Diskon Ongkir Toko
        if (!empty($store->biteship_shipping_discount) && $store->biteship_shipping_discount > 0) {
            $minOrder = (float) ($store->min_order_for_discount ?? 0);
            if ($cartSubtotal >= $minOrder) {
                $price -= (float) $store->biteship_shipping_discount;
            }
        }

        // Jangan sampai ongkir bernilai negatif
        if ($price < 0) {
            $price = 0;
        }

        // 3. Pembulatan Nominal Ongkir
        $roundMode = $store->biteship_round_shipping ?? 'none';
        if ($roundMode === 'up_1000') {
            $price = ceil($price / 1000) * 1000;
        } elseif ($roundMode === 'down_1000') {
            $price = floor($price / 1000) * 1000;
        } elseif ($roundMode === 'nearest_1000') {
            $price = round($price / 1000) * 1000;
        }

        return (int) max(0, $price);
    }
}
