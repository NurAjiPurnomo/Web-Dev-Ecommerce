<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Tampilkan halaman Wishlist
     */
    public function index()
    {
        $wishlists = Wishlist::with('product')->where('user_id', Auth::id())->latest()->get();
        return view('pages.wishlist', compact('wishlists'));
    }

    /**
     * Tambah/Hapus dari Wishlist
     */
    public function toggle(Request $request)
    {
        $productId = $request->product_id;
        $userId = Auth::id();

        $wishlist = Wishlist::where('user_id', $userId)
                            ->where('product_id', $productId)
                            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $count = Wishlist::where('user_id', $userId)->count();
            return response()->json([
                'success' => true,
                'status' => 'removed',
                'message' => 'Produk dihapus dari wishlist.',
                'wishlist_count' => $count
            ]);
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            $count = Wishlist::where('user_id', $userId)->count();
            return response()->json([
                'success' => true,
                'status' => 'added',
                'message' => 'Produk ditambahkan ke wishlist!',
                'wishlist_count' => $count
            ]);
        }
    }

    /**
     * Pindahkan semua item di Wishlist ke Keranjang Belanja
     */
    public function moveAllToCart(Request $request)
    {
        $userId = Auth::id();
        $wishlists = Wishlist::with('product')->where('user_id', $userId)->get();
        
        $cart = session()->get('cart', []);

        foreach ($wishlists as $item) {
            if ($item->product) {
                $p = $item->product;
                $cartKey = 'product_' . $p->id;
                if (isset($cart[$cartKey])) {
                    $cart[$cartKey]['qty'] += 1;
                } else {
                    $cart[$cartKey] = [
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'image' => $p->image,
                        'qty' => 1,
                        'variant' => 'Standar'
                    ];
                }
            }
        }

        session()->put('cart', $cart);
        Wishlist::where('user_id', $userId)->delete();

        return redirect()->route('cart')->with('success', 'Semua produk di Wishlist berhasil dipindahkan ke Keranjang!');
    }
}
