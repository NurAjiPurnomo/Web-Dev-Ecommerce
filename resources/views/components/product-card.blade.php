@props([
    'id' => 1,
    'title' => 'Nama Produk',
    'price' => 'Rp 0',
    'originalPrice' => null,
    'discount' => null,
    'image' => 'https://via.placeholder.com/300',
    'rating' => '0',
    'sold' => '0',
    'rankBadge' => null,
    'rankColor' => 'bg-blue-700 text-white',
    'variants' => [],
    'isWishlisted' => null,
    'showCartButton' => false,
    'showWishlistActions' => false
])

@php
    if ($isWishlisted === null) {
        $isWishlisted = auth()->check() ? auth()->user()->wishlists()->where('product_id', $id)->exists() : false;
    }

    $cleanDiscount = null;
    if ($discount) {
        $val = trim(str_ireplace(['hemat', 'diskon', 'promo', ' '], '', $discount));
        if ($val !== '') {
            $cleanDiscount = str_starts_with($val, '-') ? $val : '-' . $val;
        }
    }
@endphp

<div x-data="{ 
        currentPrice: '{{ $price }}',
        activeSize: null,
        isFav: @js($isWishlisted),
        loadingFav: false,
        variants: {{ empty($variants) ? '{}' : json_encode($variants) }},
        async toggleWishlist(e) {
            e.preventDefault();
            e.stopPropagation();
            @if(!auth()->check())
                window.location.href = '{{ route("login") }}';
                return;
            @endif
            if (this.loadingFav) return;
            
            const btnEl = e ? e.currentTarget : null;
            const isAdding = !this.isFav;

            if (isAdding && window.flyToElement && btnEl) {
                window.flyToElement(btnEl, 'wishlist', @js($image));
            }

            this.loadingFav = true;
            try {
                let res = await fetch('{{ route("wishlist.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ product_id: {{ $id }} })
                });
                let data = await res.json();
                if (data.success) {
                    this.isFav = (data.status === 'added');
                    if (data.wishlist_count !== undefined) {
                        window.dispatchEvent(new CustomEvent('wishlist-count-updated', { detail: { count: data.wishlist_count } }));
                    }
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.loadingFav = false;
            }
        },
        async addToCart(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const btnEl = e ? e.currentTarget : null;
            if (window.flyToElement && btnEl) {
                window.flyToElement(btnEl, 'cart', @js($image));
            }
            try {
                let res = await fetch('{{ route("cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_id: {{ $id }},
                        name: @js($title),
                        price: @js($price),
                        image: @js($image),
                        qty: 1
                    })
                });
                let data = await res.json();
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        title: @js($title),
                        image: @js($image),
                        qty: 1
                    }
                }));
                if (data.cartCount !== undefined) {
                    window.dispatchEvent(new CustomEvent('cart-count-updated', {
                        detail: { 
                            count: data.cartCount,
                            cart: data.cart,
                            product: data.product 
                        }
                    }));
                }
            } catch(err) {
                console.error(err);
            }
        }
    }" 
    class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs hover:shadow-md hover:border-blue-700 transition-all duration-200 flex flex-col justify-between group relative h-full">

    <!-- Top Part: Image & Badges -->
    <div class="relative w-full aspect-square overflow-hidden bg-slate-100">
        <!-- Promo Discount Badge (Pojok Kiri Atas selaras tema website biru) -->
        @if($cleanDiscount && !$rankBadge)
            <div class="absolute top-0 left-0 bg-blue-50 text-blue-700 font-bold text-[10px] sm:text-[11px] px-2 py-0.5 rounded-br-lg shadow-2xs z-10 leading-normal tracking-tight border-b border-r border-blue-200 select-none">
                {{ $cleanDiscount }}
            </div>
        @endif

        @if($rankBadge)
            <div class="absolute top-1.5 left-1.5 {{ $rankColor }} text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs z-10 uppercase tracking-wider">
                {{ $rankBadge }}
            </div>
        @endif

        <!-- AJAX Wishlist Toggle Heart Button (Top-Right Overlay) -->
        <button 
            type="button" 
            @click="toggleWishlist($event)" 
            class="absolute top-1.5 right-1.5 w-7 h-7 bg-white/90 backdrop-blur-xs rounded-full flex items-center justify-center shadow-2xs border border-slate-200/80 hover:bg-white transition-all z-20 cursor-pointer"
            :class="isFav ? 'text-red-500' : 'text-slate-400 hover:text-red-500'"
            :title="isFav ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist'"
        >
            <svg class="w-4 h-4 transition-transform duration-200 active:scale-125" :fill="isFav ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>

        <a href="{{ route('product.detail', ['id' => $id, 'slug' => \Illuminate\Support\Str::slug($title)]) }}" class="block w-full h-full">
            <img 
                src="{{ $image }}" 
                alt="{{ $title }}" 
                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=600&auto=format&fit=crop&q=80';"
            >
        </a>
    </div>

    <!-- Product Body -->
    <div class="p-2.5 flex flex-col justify-between flex-1">
        <div class="flex flex-col space-y-1">
            <!-- Title -->
            <a href="{{ route('product.detail', ['id' => $id, 'slug' => \Illuminate\Support\Str::slug($title)]) }}">
                <h3 class="text-[11px] sm:text-xs font-semibold text-slate-800 line-clamp-2 group-hover:text-blue-700 transition-colors leading-tight min-h-[28px] sm:min-h-[32px]">
                    {{ $title }}
                </h3>
            </a>

            <!-- Price Info -->
            <div class="pt-0.5">
                <div class="text-xs sm:text-sm font-extrabold text-blue-700 tracking-tight" x-text="currentPrice">
                    {{ $price }}
                </div>
                @if($originalPrice)
                    <div class="flex items-center gap-1 mt-0.5">
                        <span class="text-[10px] text-slate-400 line-through leading-none">{{ $originalPrice }}</span>
                        @if($discount)
                            <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1 py-0.2 rounded leading-none border border-blue-200">{{ $cleanDiscount ?? $discount }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div>
            <!-- Rating, Sold Info -->
            <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] pt-1.5 mt-1.5 border-t border-slate-100 text-slate-500">
                <div class="flex items-center text-amber-500">
                    <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="ml-0.5 text-slate-600 font-medium">{{ (float)$rating > 0 ? $rating : '0.0' }}</span>
                </div>
                
                @if($sold !== '0')
                    <span class="text-slate-300 font-bold text-[8px]">•</span>
                    <span class="text-slate-500">{{ $sold }} terjual</span>
                @endif
            </div>

            @if($showWishlistActions || $showCartButton)
                <div class="pt-2 mt-1.5 border-t border-slate-100 flex items-center gap-1.5">
                    @if($showWishlistActions)
                        <button 
                            type="button" 
                            @click="toggleWishlist($event)"
                            class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg transition-colors cursor-pointer shrink-0 flex items-center justify-center shadow-2xs"
                            title="Hapus dari Wishlist"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>

                        <button 
                            type="button" 
                            @click="addToCart($event)"
                            class="flex-1 bg-blue-700 hover:bg-blue-800 text-white font-bold text-[11px] py-1.5 px-2 rounded-lg transition-colors shadow-2xs flex items-center justify-center gap-1 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <span>+ Keranjang</span>
                        </button>
                    @elseif($showCartButton)
                        <button 
                            type="button" 
                            @click="addToCart($event)"
                            class="flex-1 bg-blue-700 hover:bg-blue-800 text-white font-bold text-[11px] py-1.5 px-2 rounded-lg transition-colors shadow-2xs flex items-center justify-center gap-1 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <span>+ Keranjang</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>
