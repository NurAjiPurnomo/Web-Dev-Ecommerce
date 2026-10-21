@extends('layouts.app')

@section('title', 'Wishlist Saya - Toko Online')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    <!-- Page Header & Breadcrumb -->
    <div class="space-y-4">
        <div>
            <!-- Breadcrumb -->
            <nav class="text-xs text-slate-500 flex items-center gap-1.5 mb-1">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-medium">Wishlist Saya</span>
            </nav>
            
            <!-- Page Title -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <svg class="w-7 h-7 text-red-500 fill-current" viewBox="0 0 24 24">
                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        Wishlist & Produk Favorit
                    </h1>
                </div>
            </div>
        </div>

        <!-- Toolbar Indicator & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 pb-3.5 gap-3">
            <div class="text-xs sm:text-sm text-slate-500">
                Menampilkan <strong class="text-slate-900 font-semibold">{{ $wishlists->count() }}</strong> produk favorit
            </div>
            @if(!$wishlists->isEmpty())
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <a 
                        href="{{ route('catalog') }}" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 hover:border-slate-400 rounded-lg shadow-2xs transition-colors"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Cari Produk Lain</span>
                    </a>

                    <form action="{{ route('wishlist.moveAll') }}" method="POST" class="inline-block m-0">
                        @csrf
                        <button 
                            type="submit" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs transition-colors cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <span>Pindahkan Semua ke Keranjang</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @if($wishlists->isEmpty())
        <!-- Empty State -->
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-3 max-w-md mx-auto my-8 shadow-2xs">
            <div class="w-14 h-14 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-900">Wishlist Anda Masih Kosong</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">
                Belum ada produk favorit yang disimpan. Klik tombol love <span class="text-red-500 font-bold">♥</span> pada produk yang Anda sukai untuk menyimpannya di sini.
            </p>
            <div class="pt-2">
                <a href="{{ route('catalog') }}" class="inline-block text-xs bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2.5 rounded-xl transition-colors shadow-2xs">
                    Jelajahi Katalog Produk
                </a>
            </div>
        </div>
    @else
        <!-- Grid Products (Strict 6-column Grid) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
            @foreach($wishlists as $wishlist)
                @if($wishlist->product)
                    @php
                        $product = $wishlist->product;
                        $hasDiscount = $product->original_price && $product->original_price > $product->price;
                        $discountText = $hasDiscount ? round((($product->original_price - $product->price) / $product->original_price) * 100) . '%' : null;
                        $formattedPrice = 'Rp ' . number_format($product->price, 0, ',', '.');
                        $formattedOriginalPrice = $hasDiscount ? 'Rp ' . number_format($product->original_price, 0, ',', '.') : null;
                        $ratingVal = number_format($product->reviews_avg_rating ?? ($product->rating ?? 4.8), 1);
                        $soldVal = (string)($product->total_sales ?? ($product->sold ?? rand(10, 150)));
                    @endphp

                    <x-product-card 
                        :id="$product->id"
                        :title="$product->name"
                        :price="$formattedPrice"
                        :originalPrice="$formattedOriginalPrice"
                        :discount="$discountText"
                        :image="$product->image"
                        :rating="$ratingVal"
                        :sold="$soldVal"
                        :isWishlisted="true"
                        :showWishlistActions="true"
                    />
                @endif
            @endforeach
        </div>
    @endif

</div>
@endsection
