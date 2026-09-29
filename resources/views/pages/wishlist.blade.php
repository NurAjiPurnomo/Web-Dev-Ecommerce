@extends('layouts.app')

@section('title', 'Wishlist Saya - Toko Online')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    <!-- Page Header & Breadcrumb -->
    <div class="space-y-4">
        <div>
            <!-- Breadcrumb -->
            <nav class="text-xs text-gray-500 flex items-center gap-1.5 mb-1">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
                <span>&gt;</span>
                <span class="text-gray-900 font-medium">Wishlist</span>
            </nav>
            
            <!-- Page Title -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <svg class="w-7 h-7 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        Wishlist Saya
                    </h1>
                </div>
            </div>
        </div>

        <!-- Toolbar Indicator -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 text-xs sm:text-sm text-slate-600 font-medium">
            <span>Menampilkan <strong class="text-slate-900 font-bold">{{ $wishlists->count() }}</strong> Produk Simpanan</span>
            @if(!$wishlists->isEmpty())
                <a href="{{ route('catalog') }}" class="text-xs text-blue-700 hover:text-blue-800 font-bold transition-colors">
                    + Tambah Produk Lain
                </a>
            @endif
        </div>
    </div>

    @if($wishlists->isEmpty())
        <!-- Empty State (Style matching Catalog empty state) -->
        <div class="bg-white border border-slate-200 rounded-xl p-12 text-center space-y-3 max-w-md mx-auto my-8 shadow-2xs">
            <div class="w-14 h-14 rounded-full bg-red-50 text-red-400 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Wishlist Anda Masih Kosong</h3>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">
                Belum ada produk favorit yang disimpan. Tekan tombol <span class="text-red-500 font-bold">♥</span> saat melihat produk untuk menyimpannya di sini.
            </p>
            <div class="pt-2">
                <a href="{{ route('catalog') }}" class="inline-block text-xs bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2.5 rounded-lg transition-colors shadow-2xs">
                    Mulai Belanja Sekarang
                </a>
            </div>
        </div>
    @else
        <!-- Grid Products (Identical 6-column grid as Catalog & Home page) -->
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

                    <div class="relative group h-full">
                        <!-- Product Card Component -->
                        <x-product-card 
                            :id="$product->id"
                            :title="$product->name"
                            :price="$formattedPrice"
                            :originalPrice="$formattedOriginalPrice"
                            :discount="$discountText"
                            :image="$product->image"
                            :rating="$ratingVal"
                            :sold="$soldVal"
                        />

                        <!-- Remove Button Overlay (Top Right over image) -->
                        <form action="{{ route('wishlist.toggle') }}" method="POST" class="absolute top-1.5 right-1.5 z-20">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="w-7 h-7 bg-white/90 backdrop-blur-xs rounded-full flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 shadow-2xs border border-slate-200 transition-colors" title="Hapus dari Wishlist">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</div>
@endsection
