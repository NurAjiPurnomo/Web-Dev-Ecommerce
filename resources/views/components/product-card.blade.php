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
    'variants' => []
])

<div x-data="{ 
        currentPrice: '{{ $price }}',
        activeSize: null,
        variants: {{ empty($variants) ? '{}' : json_encode($variants) }}
    }" 
    class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs hover:shadow-md hover:border-blue-700 transition-all duration-200 flex flex-col justify-between group relative h-full">

    <!-- Wrapper link untuk gambar agar tetap bisa di-klik ke detail -->
    <a href="{{ route('product.detail', ['id' => $id, 'slug' => \Illuminate\Support\Str::slug($title)]) }}" class="block">

    <!-- Discount Badge -->
    @if($discount && $originalPrice && !$rankBadge)
        <div class="absolute top-1.5 left-1.5 bg-blue-700 text-white font-extrabold text-[9px] sm:text-[10px] px-2 py-0.5 rounded shadow-2xs z-10">
            {{ str_contains($discount, 'Hemat') ? $discount : 'Hemat ' . $discount }}
        </div>
    @endif

    <div>
        <!-- Product Image Container -->
        <div class="relative w-full aspect-square overflow-hidden bg-slate-100">
            @if($rankBadge)
                <div class="absolute top-1.5 left-1.5 {{ $rankColor }} text-[9px] font-black px-1.5 py-0.5 rounded shadow-2xs z-10 uppercase tracking-wider">
                    {{ $rankBadge }}
                </div>
            @endif

            <img 
                src="{{ $image }}" 
                alt="{{ $title }}" 
                class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                loading="lazy"
            >
        </div>
    </a>

        <!-- Product Body -->
        <div class="p-2.5 sm:p-3 flex flex-col flex-1">
            
            <div class="flex flex-col flex-1 space-y-1.5">
                <!-- Title (Bisa diklik juga) -->
                <a href="{{ route('product.detail', ['id' => $id, 'slug' => \Illuminate\Support\Str::slug($title)]) }}">
                    <h3 class="text-xs font-semibold text-slate-800 line-clamp-2 group-hover:text-blue-700 transition-colors leading-snug min-h-[34px]">
                        {{ $title }}
                    </h3>
                </a>

                <!-- Price Info (Terhubung ke Alpine.js via x-text) -->
                <div>
                    <div class="text-xs sm:text-sm font-extrabold text-blue-700 tracking-tight" x-text="currentPrice">
                        {{ $price }}
                    </div>
                    <div class="flex items-center gap-1 mt-0.5 h-4">
                        @if($originalPrice)
                            <span class="text-[10px] text-slate-400 line-through">{{ $originalPrice }}</span>
                            @if($discount)
                                <span class="text-[9px] font-bold text-red-600 bg-red-50 px-1 rounded">{{ $discount }}</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rating & Sold Info -->
            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1.5 mt-2 border-t border-slate-100">
                <div class="flex items-center text-amber-500 font-bold">
                    <svg class="w-3 h-3 fill-current shrink-0" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="ml-0.5 text-slate-700 font-bold">{{ (float)$rating > 0 ? $rating : '0.0' }}</span>
                </div>
                
                @if((int) str_replace(['+', 'rb', 'k'], '', $sold) > 0)
                <span class="text-slate-400 font-medium">Terjual {{ $sold }}</span>
                @endif
            </div>
        </div>
    </div>

</div>

