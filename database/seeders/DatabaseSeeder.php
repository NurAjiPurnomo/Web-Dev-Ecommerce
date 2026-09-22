<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@toko.com'],
            [
                'name' => 'Super Admin',
                'phone' => '081234567890',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'is_admin' => true,
                'status' => 'active',
            ]
        );

        $budi = User::updateOrCreate(
            ['email' => 'budi.santoso@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081987654321',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'is_admin' => false,
                'status' => 'active',
                'address' => 'Jl. Jendral Sudirman No. 45, Kebayoran Baru',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
            ]
        );

        // Seed Products
        $products = [
            [
                'id' => 1,
                'name' => 'Kemeja Linen Casual Premium Slim Fit - Navy',
                'category' => 'Pakaian',
                'price' => 149000,
                'original_price' => 499000,
                'discount' => '70%',
                'stock' => 25,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80',
                'description' => 'Kemeja linen casual dengan bahan serat alami breathable, sangat adem dan nyaman digunakan seharian.',
                'status' => 'aktif'
            ],
            [
                'id' => 2,
                'name' => 'Sepatu Sneakers Running Lightweight Air Breathable',
                'category' => 'Sepatu',
                'price' => 215000,
                'original_price' => 650000,
                'discount' => '67%',
                'stock' => 18,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
                'description' => 'Sepatu sneakers olah raga lari sangat ringan dengan bantalan insole empuk anti slip.',
                'status' => 'aktif'
            ],
            [
                'id' => 3,
                'name' => 'Jam Tangan Chronograph Quartz Leather Strap Luxury',
                'category' => 'Aksesoris',
                'price' => 299000,
                'original_price' => 899000,
                'discount' => '66%',
                'stock' => 10,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80',
                'description' => 'Jam tangan mewah tipe analog chronograph dengan tali kulit asli elegan.',
                'status' => 'aktif'
            ],
            [
                'id' => 4,
                'name' => 'Wireless Noise Canceling Headphones Extra Bass',
                'category' => 'Gadget',
                'price' => 389000,
                'original_price' => 1200000,
                'discount' => '68%',
                'stock' => 8,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
                'description' => 'Headphone Bluetooth nirkabel fitur peredam bising dengan bass dalam dan jernih.',
                'status' => 'aktif'
            ],
            [
                'id' => 5,
                'name' => 'Ransel Laptop Waterproof Minimalis Modern 15.6 Inch',
                'category' => 'Aksesoris',
                'price' => 175000,
                'original_price' => 350000,
                'discount' => '50%',
                'stock' => 15,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&auto=format&fit=crop&q=80',
                'description' => 'Tas ransel kerja & sekolah tahan air dengan slot khusus laptop 15.6 inch.',
                'status' => 'aktif'
            ],
            [
                'id' => 6,
                'name' => 'Smartwatch Fitness Tracker Amoled Display Waterproof',
                'category' => 'Gadget',
                'price' => 450000,
                'original_price' => null,
                'discount' => null,
                'stock' => 12,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600&auto=format&fit=crop&q=80',
                'description' => 'Smartwatch dengan layar AMOLED tajam, pemantau detak jantung, dan 100+ mode olahraga.',
                'status' => 'aktif'
            ],
            [
                'id' => 7,
                'name' => 'Air Fryer Touchscreen Digital 4.5L Low Watt',
                'category' => 'Rumah Tangga',
                'price' => 599000,
                'original_price' => 1199000,
                'discount' => '50%',
                'stock' => 20,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1585515320310-259814833e62?w=600&auto=format&fit=crop&q=80',
                'description' => 'Penggoreng tanpa minyak hemat listrik dengan panel sentuh otomatis pintar.',
                'status' => 'aktif'
            ],
            [
                'id' => 8,
                'name' => 'Kacamata Polarized Anti UV Sunglasses Titanium Frame',
                'category' => 'Aksesoris',
                'price' => 120000,
                'original_price' => null,
                'discount' => null,
                'stock' => 30,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&auto=format&fit=crop&q=80',
                'description' => 'Kacamata gaya polarized pelindung radiasi UV dengan gagang titanium kokoh.',
                'status' => 'aktif'
            ],
            [
                'id' => 9,
                'name' => 'Botol Minum Termos Stainless Steel 1 Liter Tahan 24 Jam',
                'category' => 'Rumah Tangga',
                'price' => 89000,
                'original_price' => 160000,
                'discount' => '44%',
                'stock' => 45,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=600&auto=format&fit=crop&q=80',
                'description' => 'Termos es & air panas 1L mampu menjaga suhu cairan dingin/panas hingga 24 jam.',
                'status' => 'aktif'
            ],
            [
                'id' => 10,
                'name' => 'Lampu Meja Belajar LED Aesthetic Touch Sensor Dimmer',
                'category' => 'Rumah Tangga',
                'price' => 105000,
                'original_price' => null,
                'discount' => null,
                'stock' => 14,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1534353473418-4cfa6c56fd38?w=600&auto=format&fit=crop&q=80',
                'description' => 'Lampu baca meja belajar dengan pengatur tingkat kecerahan sentuh anti silau.',
                'status' => 'aktif'
            ],
            [
                'id' => 11,
                'name' => 'Jaket Hoodie Fleece Oversize Premium Distro - Off White',
                'category' => 'Pakaian',
                'price' => 185000,
                'original_price' => 370000,
                'discount' => '50%',
                'stock' => 22,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600&auto=format&fit=crop&q=80',
                'description' => 'Jaket hoodie fleece tebal hangat lembut di kulit cocok untuk outfit harian.',
                'status' => 'aktif'
            ],
            [
                'id' => 12,
                'name' => 'Mechanical Gaming Keyboard RGB Hot Swappable Switch',
                'category' => 'Gadget',
                'price' => 349000,
                'original_price' => null,
                'discount' => null,
                'stock' => 16,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80',
                'description' => 'Keyboard mekanik gaming lampu RGB dengan switch yang bisa dicopot pasang.',
                'status' => 'aktif'
            ],
            [
                'id' => 13,
                'name' => 'Tas Selempang Crossbody Kulit Casual Sling Bag',
                'category' => 'Aksesoris',
                'price' => 129000,
                'original_price' => 250000,
                'discount' => '48%',
                'stock' => 28,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=600&auto=format&fit=crop&q=80',
                'description' => 'Tas selempang pria & wanita bahan sintetis premium elastis dan tahan goresan.',
                'status' => 'aktif'
            ],
            [
                'id' => 14,
                'name' => 'Dompet Kulit Pria Slim Minimalis RFID Blocker',
                'category' => 'Aksesoris',
                'price' => 85000,
                'original_price' => null,
                'discount' => null,
                'stock' => 50,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&auto=format&fit=crop&q=80',
                'description' => 'Dompet lipat tipis pelindung chip kartu ATM dari pembajakan sinyal nirkabel.',
                'status' => 'aktif'
            ],
            [
                'id' => 15,
                'name' => 'Speaker Bluetooth Portable Extra Bass Waterproof IPX7',
                'category' => 'Gadget',
                'price' => 195000,
                'original_price' => 390000,
                'discount' => '50%',
                'stock' => 19,
                'sold' => 0,
                'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80',
                'description' => 'Speaker mini bluetooth tahan air outdoor suara nyaring dengan daya baterai tahan lama.',
                'status' => 'aktif'
            ],
            [
                'id' => 16,
                'name' => 'Sepatu Casual Canvas Slip-On Breathable Minimalis',
                'category' => 'Sepatu',
                'price' => 135000,
                'original_price' => 270000,
                'discount' => '50%',
                'stock' => 25,
                'sold' => 12,
                'image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600&auto=format&fit=crop&q=80',
                'description' => 'Sepatu slip-on santai bahan kanvas fleksibel adem dan fleksibel untuk dipakai harian.',
                'status' => 'aktif'
            ],
            [
                'id' => 17,
                'name' => 'Kemeja Batik Modern Slim Fit Lengan Panjang Premium',
                'category' => 'Pakaian',
                'price' => 165000,
                'original_price' => 330000,
                'discount' => '50%',
                'stock' => 18,
                'sold' => 8,
                'image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&auto=format&fit=crop&q=80',
                'description' => 'Kemeja batik motif elegan bahan katun primisima halus cocok untuk kantor dan pesta.',
                'status' => 'aktif'
            ],
            [
                'id' => 18,
                'name' => 'Mouse Gaming Wireless RGB Ergonomic 3200 DPI',
                'category' => 'Gadget',
                'price' => 115000,
                'original_price' => 230000,
                'discount' => '50%',
                'stock' => 30,
                'sold' => 15,
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=600&auto=format&fit=crop&q=80',
                'description' => 'Mouse tanpa kabel responsif tinggi dengan lampu RGB dan 6 tombol yang ergonomis.',
                'status' => 'aktif'
            ],
            [
                'id' => 19,
                'name' => 'Kipas Angin Portable USB Rechargeable Mini Fan Silent',
                'category' => 'Rumah Tangga',
                'price' => 65000,
                'original_price' => 130000,
                'discount' => '50%',
                'stock' => 40,
                'sold' => 20,
                'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=600&auto=format&fit=crop&q=80',
                'description' => 'Kipas angin genggam kecil suara halus baterai cas tahan lama siap dibawa kemana saja.',
                'status' => 'aktif'
            ],
            [
                'id' => 20,
                'name' => 'Topi Baseball Distro Premium Cotton Adjustable Strap',
                'category' => 'Aksesoris',
                'price' => 55000,
                'original_price' => 110000,
                'discount' => '50%',
                'stock' => 50,
                'sold' => 35,
                'image' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&auto=format&fit=crop&q=80',
                'description' => 'Topi pria wanita bahan katun twill tebal bordir rapi pengatur ukuran logam.',
                'status' => 'aktif'
            ],
            [
                'id' => 21,
                'name' => 'Powerbank 20000mAh Fast Charging 22.5W Dual Output',
                'category' => 'Gadget',
                'price' => 199000,
                'original_price' => 398000,
                'discount' => '50%',
                'stock' => 20,
                'sold' => 18,
                'image' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=600&auto=format&fit=crop&q=80',
                'description' => 'Pengisi daya portable kapsitas jumbo cas cepat HP hingga 4 kali pengisian.',
                'status' => 'aktif'
            ],
            [
                'id' => 22,
                'name' => 'Celana Chino Slim Fit Stretch Casual Formal Black',
                'category' => 'Pakaian',
                'price' => 145000,
                'original_price' => 290000,
                'discount' => '50%',
                'stock' => 25,
                'sold' => 14,
                'image' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=600&auto=format&fit=crop&q=80',
                'description' => 'Celana panjang chino bahan katun melar stretch empuk tidak kaku saat duduk.',
                'status' => 'aktif'
            ],
            [
                'id' => 23,
                'name' => 'Sandal Slide Casual Waterproof Anti-Slip Empuk',
                'category' => 'Sepatu',
                'price' => 75000,
                'original_price' => 150000,
                'discount' => '50%',
                'stock' => 35,
                'sold' => 22,
                'image' => 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=600&auto=format&fit=crop&q=80',
                'description' => 'Sandal selop santai bahan EVA impor empuk membal tidak licin dan tahan air.',
                'status' => 'aktif'
            ],
            [
                'id' => 24,
                'name' => 'Set Pisau Dapur Stainless Steel Sharp Knife Set 6 in 1',
                'category' => 'Rumah Tangga',
                'price' => 125000,
                'original_price' => 250000,
                'discount' => '50%',
                'stock' => 15,
                'sold' => 10,
                'image' => 'https://images.unsplash.com/photo-1593618998160-e34014e67546?w=600&auto=format&fit=crop&q=80',
                'description' => 'Set pisau dapur tajam antikarat plus gunting dan pengupas buah serbaguna.',
                'status' => 'aktif'
            ]
        ];

        foreach ($products as $p) {
            \App\Models\Product::updateOrCreate(['id' => $p['id']], $p);
        }

        // Seed Vouchers
        $vouchers = [
            [
                'code' => 'FREEONGKIR20',
                'type' => 'gratis_ongkir',
                'discount_value' => 20000,
                'min_spend' => 50000,
                'expires_at' => '2026-08-31',
                'status' => 'aktif'
            ],
            [
                'code' => 'DISKON50K',
                'type' => 'diskon_nominal',
                'discount_value' => 50000,
                'min_spend' => 200000,
                'expires_at' => '2026-08-31',
                'status' => 'aktif'
            ],
            [
                'code' => 'CASHBACK25K',
                'type' => 'diskon_nominal',
                'discount_value' => 25000,
                'min_spend' => 100000,
                'expires_at' => '2026-08-31',
                'status' => 'aktif'
            ],
            [
                'code' => 'DISKON10',
                'type' => 'diskon_persen',
                'discount_value' => 10,
                'min_spend' => 0,
                'max_discount' => 100000,
                'expires_at' => '2026-08-20',
                'status' => 'aktif'
            ]
        ];

        foreach ($vouchers as $v) {
            \App\Models\Voucher::updateOrCreate(['code' => $v['code']], $v);
        }

        // Seed Sample Orders
        $order = \App\Models\Order::updateOrCreate(
            ['invoice_number' => 'INV/20260810/001'],
            [
                'user_id' => $budi->id,
                'subtotal' => 364000,
                'shipping_cost' => 15000,
                'discount_amount' => 30000,
                'total' => 349000,
                'courier' => 'J&T Express',
                'payment_method' => 'BCA Virtual Account',
                'status' => 'diproses',
                'recipient_name' => 'Budi Santoso',
                'recipient_phone' => '081987654321',
                'shipping_address' => 'Jl. Jendral Sudirman No. 45, Kebayoran Baru, Jakarta Selatan',
            ]
        );

        \App\Models\OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_name' => 'Kemeja Linen Casual Premium Slim Fit - Navy'],
            ['product_id' => 1, 'quantity' => 1, 'price' => 149000]
        );

        \App\Models\OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_name' => 'Sepatu Sneakers Running Lightweight Air Breathable'],
            ['product_id' => 2, 'quantity' => 1, 'price' => 215000]
        );

        // Seed Affiliates
        \App\Models\Affiliate::updateOrCreate(
            ['user_id' => $budi->id],
            [
                'referral_code' => 'BUDI55',
                'commission_rate' => 5.00,
                'total_sales' => 14500000,
                'commission_earned' => 725000,
                'status' => 'aktif'
            ]
        );

        // Seed Announcements
        \App\Models\Announcement::updateOrCreate(
            ['title' => 'Promo Spesial Super August Sale Diskon s.d 70%'],
            [
                'content' => 'Gunakan kode voucher DISKON50K dan nikmati potongan harga spesial bulan ini!',
                'type' => 'banner',
                'target' => 'semua',
                'status' => 'ditayangkan'
            ]
        );
    }
}
