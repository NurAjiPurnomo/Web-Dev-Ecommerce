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

        return view('pages.cart', [
            'cartItems' => array_values($cart),
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
}
