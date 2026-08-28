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
            return response()->json([
                'success' => true,
                'status' => 'removed',
                'message' => 'Produk dihapus dari wishlist.'
            ]);
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
            return response()->json([
                'success' => true,
                'status' => 'added',
                'message' => 'Produk ditambahkan ke wishlist!'
            ]);
        }
    }
}
