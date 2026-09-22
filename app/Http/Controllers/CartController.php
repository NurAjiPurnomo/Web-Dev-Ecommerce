<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Tampilkan Halaman Keranjang Belanja.
     */
    public function index()
    {
        $cart = session()->get('cart', []);

        // Jika keranjang di session masih kosong, kita inisialisasi dengan data contoh default jika user sudah login
        if (empty($cart) && (session()->has('user') || auth()->check()) && !session()->has('cart_initialized')) {
            $cart = [
                '1_default' => [
                    'id'             => '1_default',
                    'product_id'     => 1,
                    'variant_id'     => 'v1',
                    'image_id'       => 'img1',
                    'name'           => 'Kemeja Linen Casual Premium Slim Fit - Navy',
                    'variant'        => 'Varian: Ukuran L, Warna Navy',
                    'color'          => 'Navy',
                    'size'           => 'L',
                    'price'          => 149000,
                    'original_price' => 249000,
                    'discount'       => '40%',
                    'qty'            => 1,
                    'selected'       => true,
                    'image'          => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80',
                ],
                '2_default' => [
                    'id'             => '2_default',
                    'product_id'     => 2,
                    'variant_id'     => 'v2',
                    'image_id'       => 'img2',
                    'name'           => 'Sepatu Sneakers Running Lightweight Air Breathable',
                    'variant'        => 'Varian: Ukuran 42, Warna Red Chili',
                    'color'          => 'Red Chili',
                    'size'           => '42',
                    'price'          => 215000,
                    'original_price' => 350000,
                    'discount'       => '39%',
                    'qty'            => 1,
                    'selected'       => true,
                    'image'          => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
                ],
            ];
            session()->put('cart', $cart);
            session()->put('cart_initialized', true);
        }

        $user = auth()->user();
        if ($user) {
            $dbVouchers = $user->vouchers()
                ->wherePivot('is_used', false)
                ->where('status', 'aktif')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                })
                ->get();
        } else {
            $dbVouchers = collect([]);
        }

        $dbShippingVouchers = [];
        $dbDiscountVouchers = [];

        foreach ($dbVouchers as $v) {
            if ($v->type === 'gratis_ongkir') {
                $dbShippingVouchers[] = [
                    'id'            => 'v_' . $v->id,
                    'code'          => $v->code,
                    'category'      => 'shipping',
                    'title'         => 'Gratis Ongkir s.d. Rp ' . number_format($v->discount_value, 0, ',', '.'),
                    'minSpend'      => (int) $v->min_spend,
                    'discountType'  => 'shipping',
                    'discountValue' => (int) $v->discount_value,
                    'description'   => 'Min. belanja Rp ' . number_format($v->min_spend, 0, ',', '.') . ' khusus potongan ongkir',
                    'expiry'        => $v->expires_at ? 'Berlaku s.d. ' . $v->expires_at->format('d M Y') : 'Berlaku terbatas',
                    'badge'         => 'GRATIS ONGKIR',
                    'badgeBg'       => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                ];
            } else {
                $isPercent = ($v->type === 'diskon_persen');
                $dbDiscountVouchers[] = [
                    'id'            => 'v_' . $v->id,
                    'code'          => $v->code,
                    'category'      => 'discount',
                    'title'         => $isPercent ? ('Diskon ' . $v->discount_value . '% (s.d. Rp ' . number_format($v->max_discount ?: 100000, 0, ',', '.') . ')') : ('Potongan Harga Rp ' . number_format($v->discount_value, 0, ',', '.')),
                    'minSpend'      => (int) $v->min_spend,
                    'discountType'  => $isPercent ? 'percent' : 'fixed',
                    'discountValue' => (int) $v->discount_value,
                    'maxDiscount'   => $v->max_discount ? (int) $v->max_discount : null,
                    'description'   => 'Min. belanja Rp ' . number_format($v->min_spend, 0, ',', '.') . ' khusus potongan harga produk',
                    'expiry'        => $v->expires_at ? 'Berlaku s.d. ' . $v->expires_at->format('d M Y') : 'Berlaku terbatas',
                    'badge'         => $isPercent ? 'DISKON PERSEN' : 'POTONGAN HARGA',
                    'badgeBg'       => $isPercent ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-blue-100 text-blue-800 border-blue-200'
                ];
            }
        }

        return view('pages.cart', [
            'cartItems'          => array_values($cart),
            'dbShippingVouchers' => $dbShippingVouchers,
            'dbDiscountVouchers' => $dbDiscountVouchers,
        ]);
    }

    /**
     * Tambahkan Produk ke Keranjang.
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');
        $imageId   = $request->input('image_id');
        $name      = $request->input('name', 'Produk Pilihan');
        $price     = (int) str_replace(['Rp', '.', ' ', ','], '', $request->input('price', '100000'));
        $image     = $request->input('image', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80');
        $variant   = $request->input('variant', 'Varian Standar');
        $color     = $request->input('color', null);
        $size      = $request->input('size', null);
        $qty       = max(1, (int) $request->input('qty', 1));

        $dbProduct = \App\Models\Product::find($productId);
        $originalPrice = $dbProduct ? $dbProduct->original_price : (int) str_replace(['Rp', '.', ' ', ','], '', $request->input('original_price', 0));
        $discount = $dbProduct ? $dbProduct->discount : $request->input('discount');

        // Resolve specific variant image if available
        $image = $this->resolveVariantImage($dbProduct, $color, $size, $variantId, $image);

        // Buat kunci unik per kombinasi produk & varian
        $keyParts = [
            $productId,
            $variantId ?? '',
            $imageId ?? '',
            $color ?? '',
            $size ?? '',
            $variant ?? '',
            $image ?? ''
        ];
        $cartKey = $productId . '_' . substr(md5(implode('|', $keyParts)), 0, 10);

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $qty;
        } else {
            $cart[$cartKey] = [
                'id'             => $cartKey,
                'product_id'     => (int) $productId,
                'variant_id'     => $variantId,
                'image_id'       => $imageId,
                'name'           => $name,
                'variant'        => $variant,
                'color'          => $color,
                'size'           => $size,
                'price'          => $price,
                'original_price' => ($originalPrice && $originalPrice > $price) ? $originalPrice : null,
                'discount'       => ($originalPrice && $originalPrice > $price) ? $discount : null,
                'qty'            => $qty,
                'selected'       => true,
                'image'          => $image,
            ];
        }

        session()->put('cart', $cart);
        session()->put('cart_initialized', true);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Produk berhasil ditambahkan ke keranjang!',
                'cartCount' => array_sum(array_column($cart, 'qty')),
                'cart'      => array_values($cart),
                'product'   => [
                    'name'  => $name,
                    'image' => $image,
                    'qty'   => $qty,
                    'price' => $price,
                ]
            ]);
        }

        return redirect()->back()->with('toast_added', [
            'name'  => $name,
            'image' => $image,
            'qty'   => $qty,
        ]);
    }

    /**
     * Beli Sekarang (Langsung ke Ringkasan Checkout & Pembayaran).
     */
    public function buyNow(Request $request)
    {
        $productId = $request->input('product_id', 1);
        $variantId = $request->input('variant_id');
        $imageId   = $request->input('image_id');
        $name      = $request->input('name', 'Produk Pilihan');
        $price     = (int) str_replace(['Rp', '.', ' ', ','], '', $request->input('price', '100000'));
        $image     = $request->input('image');
        $variant   = $request->input('variant', 'Varian Standar');
        $color     = $request->input('color', null);
        $size      = $request->input('size', null);
        $qty       = max(1, (int) $request->input('qty', 1));

        $dbProduct = \App\Models\Product::find($productId);
        $image     = $this->resolveVariantImage($dbProduct, $color, $size, $variantId, $image);

        $keyParts = [
            $productId,
            $variantId ?? '',
            $imageId ?? '',
            $color ?? '',
            $size ?? '',
            $variant ?? '',
            $image ?? ''
        ];
        $cartKey = $productId . '_' . substr(md5(implode('|', $keyParts)), 0, 10);

        $buyNowItem = [
            'id'         => $cartKey,
            'product_id' => (int) $productId,
            'variant_id' => $variantId,
            'image_id'   => $imageId,
            'name'       => $name,
            'variant'    => $variant,
            'color'      => $color,
            'size'       => $size,
            'price'      => $price,
            'qty'        => $qty,
            'selected'   => true,
            'image'      => $image,
        ];

        session()->put('buy_now_item', $buyNowItem);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'      => 'success',
                'redirectUrl' => route('checkout', ['direct' => 1]),
            ]);
        }

        return redirect()->route('checkout', ['direct' => 1]);
    }

    /**
     * Update Jumlah (Quantity) Produk di Keranjang.
     */
    public function update(Request $request)
    {
        $cartKey = $request->input('cart_key') ?? $request->input('id') ?? $request->input('product_id');
        $qty     = max(1, (int) $request->input('qty', 1));

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] = $qty;
            session()->put('cart', $cart);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Jumlah produk diperbarui.',
                'cartCount' => array_sum(array_column($cart, 'qty')),
                'cart'      => array_values($cart),
            ]);
        }

        return redirect()->back()->with('success', 'Jumlah produk diperbarui.');
    }

    /**
     * Hapus Satu Produk dari Keranjang.
     */
    public function remove(Request $request)
    {
        $cartKey = $request->input('cart_key') ?? $request->input('id') ?? $request->input('product_id');

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put('cart', $cart);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Produk telah dihapus dari keranjang.',
                'cartCount' => array_sum(array_column($cart, 'qty')),
                'cart'      => array_values($cart),
            ]);
        }

        return redirect()->back()->with('success', 'Produk telah dihapus dari keranjang.');
    }

    /**
     * Hapus Produk yang Dipilih dari Keranjang.
     */
    public function removeSelected(Request $request)
    {
        $cartKeys = $request->input('cart_keys') ?? $request->input('product_ids') ?? $request->input('ids') ?? [];

        $cart = session()->get('cart', []);

        foreach ($cartKeys as $id) {
            if (isset($cart[$id])) {
                unset($cart[$id]);
            }
        }

        session()->put('cart', $cart);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Produk yang dipilih berhasil dihapus.',
                'cartCount' => array_sum(array_column($cart, 'qty')),
                'cart'      => array_values($cart),
            ]);
        }

        return redirect()->back()->with('success', 'Produk yang dipilih berhasil dihapus.');
    }

    /**
     * Update status centang (selected) item keranjang.
     */
    public function updateSelected(Request $request)
    {
        $items = $request->input('items', []);
        $cart = session()->get('cart', []);

        if (is_array($items)) {
            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                if ($id && isset($cart[$id])) {
                    $cart[$id]['selected'] = (bool) ($item['selected'] ?? true);
                }
            }
            session()->put('cart', $cart);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'cart'   => array_values($cart),
            ]);
        }

        return redirect()->back();
    }

    /**
     * Kosongkan Seluruh Keranjang.
     */
    public function clear(Request $request)
    {
        session()->forget('cart');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Keranjang berhasil dikosongkan.',
                'cartCount' => 0,
                'cart'      => [],
            ]);
        }

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan.');
    }

    /**
     * Resolve image URL for selected product variant.
     */
    private function resolveVariantImage($dbProduct, $color, $size, $variantId, $fallbackImage = null)
    {
        if ($dbProduct && !empty($dbProduct->variants) && is_array($dbProduct->variants)) {
            foreach ($dbProduct->variants as $var) {
                $matchColor = !empty($color) && strtolower(trim($var['color'] ?? '')) === strtolower(trim($color));
                $matchSize  = !empty($size) && strtolower(trim($var['size'] ?? '')) === strtolower(trim($size));
                $matchId    = !empty($variantId) && (($var['id'] ?? null) == $variantId || ($var['variant_id'] ?? null) == $variantId);

                if (($matchColor && $matchSize) || $matchId || ($matchColor && empty($size))) {
                    if (!empty($var['image'])) {
                        $img = $var['image'];
                        if (!str_starts_with($img, 'http') && !str_starts_with($img, 'assets/')) {
                            return asset(ltrim($img, '/'));
                        }
                        return $img;
                    }
                }
            }
        }

        if ($dbProduct && !empty($dbProduct->colors) && is_array($dbProduct->colors) && !empty($color)) {
            foreach ($dbProduct->colors as $c) {
                if (is_array($c) && !empty($c['name']) && strtolower(trim($c['name'])) === strtolower(trim($color))) {
                    if (!empty($c['image'])) {
                        $img = $c['image'];
                        if (!str_starts_with($img, 'http') && !str_starts_with($img, 'assets/')) {
                            return asset(ltrim($img, '/'));
                        }
                        return $img;
                    }
                }
            }
        }

        if (!empty($fallbackImage)) {
            if (!str_starts_with($fallbackImage, 'http') && !str_starts_with($fallbackImage, 'assets/')) {
                return asset(ltrim($fallbackImage, '/'));
            }
            return $fallbackImage;
        }

        if ($dbProduct && !empty($dbProduct->image)) {
            $img = $dbProduct->image;
            if (!str_starts_with($img, 'http') && !str_starts_with($img, 'assets/')) {
                return asset(ltrim($img, '/'));
            }
            return $img;
        }

        return 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80';
    }
}
