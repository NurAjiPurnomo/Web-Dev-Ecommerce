@extends('layouts.app')

@section('content')
<div x-data="catalogApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    <!-- Page Header -->
    <div class="space-y-4">
        <div>
            <!-- Breadcrumb -->
            <nav class="text-xs text-gray-500 flex items-center gap-1.5 mb-1">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
                <span>&gt;</span>
                <span class="text-gray-900 font-medium">Katalog Produk</span>
            </nav>
            
            <!-- Massive Bold Title -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Katalog Produk
            </h1>
        </div>

        <!-- Category Pills -->
        @if(isset($categories) && count($categories) > 0)
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <a href="{{ route('catalog') }}" 
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition-colors shadow-2xs border
               {{ !isset($selectedCategory) || $selectedCategory == 'all' || empty($selectedCategory) ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('catalog', ['category' => $cat]) }}" 
                   class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition-colors shadow-2xs border
                   {{ isset($selectedCategory) && $selectedCategory === $cat ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Top Toolbar (Flex-between) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2 pb-1 border-b border-slate-200">
        <!-- Left Side: Total Products Text & Search Indicator -->
        <div class="text-xs sm:text-sm text-slate-600 font-medium flex items-center gap-2 flex-wrap">
            <span>Menampilkan <strong class="text-slate-900 font-bold" x-text="items.length"></strong> Produk</span>
            @if(request('search'))
                <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs px-2.5 py-0.5 rounded-md font-bold flex items-center gap-1">
                    Pencarian: "{{ request('search') }}"
                    <a href="{{ route('catalog') }}" class="hover:text-blue-900 font-bold ml-1 cursor-pointer">✕</a>
                </span>
            @endif
        </div>

        <!-- Right Side: Local Search & Sort Dropdown -->
        <div class="flex items-center gap-3">
            <!-- Local Search Input with Icon -->
            <form action="{{ route('catalog') }}" method="GET" class="relative flex items-center flex-1 sm:flex-none">
                <input 
                    type="text" 
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari di katalog..." 
                    class="w-full sm:w-64 bg-white border border-gray-300 rounded-lg pl-9 pr-8 py-2 text-xs sm:text-sm text-slate-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-700/20 focus:border-blue-700 transition-all"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                    <a href="{{ route('catalog') }}" class="absolute right-2.5 text-gray-400 hover:text-gray-600 font-bold text-xs cursor-pointer">✕</a>
                @endif
            </form>

            <!-- Sort Select Dropdown -->
            <form action="{{ route('catalog') }}" method="GET" class="m-0">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <select name="sort" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-lg px-3.5 py-2 text-xs sm:text-sm text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-blue-700/20 focus:border-blue-700 transition-all cursor-pointer">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Urutkan: Terbaru</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Urutkan: Harga Termurah</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Urutkan: Harga Termahal</option>
                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Urutkan: Terpopuler</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Product Grid (Full Width: Strict 5-Column Grid, NO sidebars) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 pt-2">
        <template x-for="item in items" :key="item.id">
            <a :href="'/product/' + item.id + '/' + item.slug" class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-200 group relative flex flex-col justify-between block">
                <div>
                    <!-- Top Square Image Placeholder -->
                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                        <!-- Solid Blue DISKON Badge (Absolute top-left) -->
                        <template x-if="item.hasDiscount">
                            <div class="absolute top-0 left-0 bg-blue-700 text-white font-bold text-[10px] sm:text-xs px-2.5 py-1 rounded-br-lg shadow-2xs z-10 uppercase">
                                DISKON
                            </div>
                        </template>
                        <img 
                            :src="item.image" 
                            :alt="item.title" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        >
                    </div>

                    <!-- Content Body -->
                    <div class="p-3.5 space-y-1.5">
                        <!-- Store Badge (Single Store) -->
                        <div class="flex items-center gap-1 text-[10px] text-blue-700 font-semibold">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>TokoOnline Official</span>
                        </div>

                        <!-- Product Title -->
                        <h3 class="text-xs sm:text-sm font-semibold text-slate-800 line-clamp-2 min-h-[2.25rem] group-hover:text-blue-700 transition-colors leading-snug" x-text="item.title">
                        </h3>

                        <!-- Price Info -->
                        <div>
                            <template x-if="item.originalPrice">
                                <span class="text-[11px] text-gray-400 line-through font-medium block" x-text="item.originalPrice">
                                </span>
                            </template>
                            <div class="text-sm sm:text-base font-extrabold text-blue-700 tracking-tight" x-text="item.price">
                            </div>
                        </div>

                        <!-- Rating & Sold Info -->
                        <div class="flex items-center gap-1 text-[11px] text-slate-500 pt-1.5 border-t border-slate-100 mt-1">
                            <div class="flex items-center text-amber-500 font-bold">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="ml-0.5 text-slate-700 font-semibold" x-text="item.rating"></span>
                            </div>
                            <span class="text-slate-300">•</span>
                            <span class="truncate" x-text="'Terjual ' + item.sold"></span>
                        </div>

                    </div>
                </div>
            </a>
        </template>
    </div>

    <!-- Empty Search Results State -->
    <div x-show="items.length === 0" class="bg-white border border-slate-200 rounded-xl p-12 text-center space-y-3">
        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800">Tidak ada produk ditemukan</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto">
            @if(request('search'))
                Maaf, kami tidak menemukan produk dengan kata kunci "<span class="font-bold text-slate-700">{{ request('search') }}</span>". Coba gunakan kata kunci lain.
            @else
                Maaf, belum ada produk di katalog kami.
            @endif
        </p>
        <a href="{{ route('catalog') }}" class="inline-block text-xs bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-lg transition-colors cursor-pointer">
            Lihat Semua Produk
        </a>
    </div>

    <!-- Pagination -->
    @if ($catalogProducts->hasPages())
    <div class="flex justify-center items-center pt-8 pb-4">
        <div class="inline-flex items-center space-x-1 sm:space-x-1.5 text-xs sm:text-sm">
            
            <!-- Previous Box -->
            @if ($catalogProducts->onFirstPage())
                <span class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-100 border border-gray-200 rounded-lg text-gray-400 flex items-center justify-center font-bold cursor-not-allowed" aria-label="Previous Page">
                    &lt;
                </span>
            @else
                <a href="{{ $catalogProducts->previousPageUrl() }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-50 flex items-center justify-center font-bold transition-colors cursor-pointer" aria-label="Previous Page">
                    &lt;
                </a>
            @endif

            <!-- Pagination Elements -->
            @foreach ($catalogProducts->onEachSide(1)->links()->elements as $element)
                <!-- "Three Dots" Separator -->
                @if (is_string($element))
                    <span class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center text-gray-400 font-bold select-none">
                        {{ $element }}
                    </span>
                @endif

                <!-- Array Of Links -->
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $catalogProducts->currentPage())
                            <span class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-700 border border-blue-700 text-white font-extrabold rounded-lg flex items-center justify-center shadow-2xs">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border border-gray-300 rounded-lg text-slate-700 hover:bg-gray-50 font-bold transition-colors flex items-center justify-center cursor-pointer">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <!-- Next Box (Selanjutnya >) -->
            @if ($catalogProducts->hasMorePages())
                <a href="{{ $catalogProducts->nextPageUrl() }}" class="h-9 sm:h-10 px-3.5 bg-white border border-gray-300 rounded-lg text-slate-700 hover:bg-gray-50 font-bold transition-colors flex items-center gap-1 cursor-pointer">
                    <span>Selanjutnya</span>
                    <span>&gt;</span>
                </a>
            @else
                <span class="h-9 sm:h-10 px-3.5 bg-gray-100 border border-gray-200 rounded-lg text-gray-400 flex items-center gap-1 font-bold cursor-not-allowed">
                    <span>Selanjutnya</span>
                    <span>&gt;</span>
                </span>
            @endif

        </div>
    </div>
    @endif

</div>

<!-- Alpine.js Catalog Logic -->
<script>
function catalogApp() {
    return {
        items: @json($catalogProducts->items() ?? [])
    }
}
</script>
@endsection
