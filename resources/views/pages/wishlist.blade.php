@extends('layouts.app')

@section('title', 'Wishlist - Toko Online')

@section('content')
<div class="bg-slate-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h1 class="text-2xl font-bold text-slate-800">Daftar Keinginan (Wishlist)</h1>
        </div>

        @if($wishlists->isEmpty())
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-12 text-center">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Wishlist Anda Kosong</h3>
                <p class="text-slate-600 mb-6">Anda belum menyimpan produk apapun ke dalam daftar keinginan.</p>
                <a href="{{ route('home') }}" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                    Mulai Belanja
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($wishlists as $wishlist)
                    @if($wishlist->product)
                        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden group relative">
                            <!-- Remove Button -->
                            <form action="{{ route('wishlist.toggle') }}" method="POST" class="absolute top-2 right-2 z-10">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $wishlist->product_id }}">
                                <button type="submit" class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 shadow-sm transition-colors" title="Hapus dari Wishlist">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </form>
                            
                            <a href="{{ route('product.detail', ['id' => $wishlist->product_id, 'slug' => \Illuminate\Support\Str::slug($wishlist->product->name)]) }}" class="block">
                                <div class="aspect-square bg-slate-100 overflow-hidden">
                                    <img src="{{ $wishlist->product->image }}" alt="{{ $wishlist->product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="p-4">
                                    <div class="text-xs font-medium text-slate-500 mb-1">{{ $wishlist->product->category }}</div>
                                    <h3 class="font-semibold text-slate-800 line-clamp-2 mb-2 group-hover:text-blue-700 transition-colors">{{ $wishlist->product->name }}</h3>
                                    <div class="font-bold text-blue-700">Rp {{ number_format($wishlist->product->price, 0, ',', '.') }}</div>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
