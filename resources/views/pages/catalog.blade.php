@extends('layouts.app')

@section('content')
<div x-data="{ showMobileFilter: false }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    <!-- Page Header & Breadcrumb -->
    <div class="space-y-4">
        <div>
            <nav class="text-xs text-slate-500 flex items-center gap-1.5 mb-1">
                <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
                <span>&gt;</span>
                <span class="text-slate-900 font-medium">Katalog Produk</span>
            </nav>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Katalog & Search Engine Produk
                </h1>
                
                <!-- Toggle Mobile Filter Button -->
                <button 
                    type="button" 
                    @click="showMobileFilter = !showMobileFilter"
                    class="lg:hidden inline-flex items-center justify-center gap-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-2xs cursor-pointer"
                >
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>Filter Parameter</span>
                </button>
            </div>
        </div>

        <!-- Horizontal Category Quick Pills -->
        @if(isset($categories) && count($categories) > 0)
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <a href="{{ route('catalog', request()->except(['category', 'page'])) }}" 
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition-colors shadow-2xs border
               {{ !isset($selectedCategory) || $selectedCategory == 'all' || empty($selectedCategory) ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('catalog', array_merge(request()->except(['page']), ['category' => strtolower($cat)])) }}" 
                   class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition-colors shadow-2xs border
                   {{ isset($selectedCategory) && strcasecmp($selectedCategory, $cat) === 0 ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Active Filters Summary Chips -->
    @if(request('search') || request('category') || request('min_price') || request('max_price') || request('status_filter'))
    <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-3 flex items-center justify-between flex-wrap gap-2 text-xs">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="font-bold text-blue-900">Filter Aktif:</span>
            
            @if(request('search'))
                <span class="bg-white text-blue-800 border border-blue-300 px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1">
                    Pencarian: "{{ request('search') }}"
                    <a href="{{ route('catalog', request()->except(['search', 'page'])) }}" class="text-blue-600 hover:text-blue-900 font-bold ml-1">✕</a>
                </span>
            @endif

            @if(request('category'))
                <span class="bg-white text-blue-800 border border-blue-300 px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1">
                    Kategori: {{ request('category') }}
                    <a href="{{ route('catalog', request()->except(['category', 'page'])) }}" class="text-blue-600 hover:text-blue-900 font-bold ml-1">✕</a>
                </span>
            @endif

            @if(request('min_price') || request('max_price'))
                <span class="bg-white text-blue-800 border border-blue-300 px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1">
                    Harga: Rp{{ number_format((float)request('min_price', 0), 0, ',', '.') }} - Rp{{ request('max_price') ? number_format((float)request('max_price'), 0, ',', '.') : '∞' }}
                    <a href="{{ route('catalog', request()->except(['min_price', 'max_price', 'page'])) }}" class="text-blue-600 hover:text-blue-900 font-bold ml-1">✕</a>
                </span>
            @endif

            @if(request('status_filter'))
                <span class="bg-white text-blue-800 border border-blue-300 px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1">
                    Status: {{ request('status_filter') == 'diskon' ? 'Diskon Promo' : 'Ready Stock' }}
                    <a href="{{ route('catalog', request()->except(['status_filter', 'page'])) }}" class="text-blue-600 hover:text-blue-900 font-bold ml-1">✕</a>
                </span>
            @endif
        </div>

        <a href="{{ route('catalog') }}" class="text-xs text-blue-700 hover:text-blue-900 font-bold hover:underline">
            Reset Semua Filter
        </a>
    </div>
    @endif

    <!-- Main Grid Layout (Sidebar Filter + Product Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- DESKTOP FILTER SIDEBAR (3 Columns) -->
        <aside class="hidden lg:block lg:col-span-3 bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-6 sticky top-24">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h3 class="font-extrabold text-sm text-slate-900 tracking-tight uppercase">Filter Katalog</h3>
                </div>
                <a href="{{ route('catalog') }}" class="text-[11px] text-blue-700 font-bold hover:underline">Reset</a>
            </div>

            <form action="{{ route('catalog') }}" method="GET" class="space-y-5">
                
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif

                <!-- 1. FILTER STATUS PRODUK -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-800">Status Produk</label>
                    <div class="space-y-1.5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
                            <input type="radio" name="status_filter" value="" {{ !request('status_filter') ? 'checked' : '' }} class="text-blue-700 focus:ring-blue-700">
                            <span>Semua Status</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
                            <input type="radio" name="status_filter" value="diskon" {{ request('status_filter') == 'diskon' ? 'checked' : '' }} class="text-blue-700 focus:ring-blue-700">
                            <span>Sedang Diskon Promo</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
                            <input type="radio" name="status_filter" value="ready_stock" {{ request('status_filter') == 'ready_stock' ? 'checked' : '' }} class="text-blue-700 focus:ring-blue-700">
                            <span>Ready Stock</span>
                        </label>
                    </div>
                </div>

                <!-- 2. FILTER RENTANG HARGA (MIN - MAX) -->
                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-800">Rentang Harga (Rp)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input 
                                type="number" 
                                name="min_price" 
                                value="{{ request('min_price') }}"
                                placeholder="Min (Rp)" 
                                min="0"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-700/20 focus:border-blue-700 transition-all"
                            >
                        </div>
                        <div>
                            <input 
                                type="number" 
                                name="max_price" 
                                value="{{ request('max_price') }}"
                                placeholder="Max (Rp)" 
                                min="0"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-700/20 focus:border-blue-700 transition-all"
                            >
                        </div>
                    </div>
                </div>

                <!-- SUBMIT FILTER BUTTON -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold py-2.5 rounded-xl transition-colors shadow-2xs cursor-pointer flex items-center justify-center gap-1.5"
                >
                    <span>Terapkan Filter</span>
                </button>

            </form>
        </aside>

        <!-- MAIN PRODUCT SECTION (9 Columns) -->
        <main class="lg:col-span-9 space-y-4">
            
            <!-- Top Toolbar Bar -->
            <div class="bg-white border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
                
                <!-- Left: Product Count Summary -->
                <div class="text-xs sm:text-sm text-slate-600 font-medium">
                    Menampilkan <strong class="text-slate-900 font-extrabold">{{ $catalogProducts->count() }}</strong> dari <strong class="text-slate-900 font-extrabold">{{ $catalogProducts->total() }}</strong> Produk
                </div>

                <!-- Right: Sorting Dropdown -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Urutkan:</label>
                    <form action="{{ route('catalog') }}" method="GET" class="m-0">
                        @foreach(request()->except(['sort', 'page']) as $k => $v)
                            @if(is_array($v))
                                @foreach($v as $val)
                                    <input type="hidden" name="{{ $k }}[]" value="{{ $val }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        
                        <select 
                            name="sort" 
                            onchange="this.form.submit()" 
                            class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-slate-800 font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-700/20 focus:border-blue-700 transition-all cursor-pointer"
                        >
                            <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Paling Sesuai (Terbaru)</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Terpopuler & Terlaris</option>
                            <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Rating Tertinggi</option>
                        </select>
                    </form>
                </div>

            </div>

            <!-- Product Grid (Strict 5 Columns Layout using Blade SSR for 100% Instant Product Visibility) -->
            @if($catalogProducts->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                    @foreach($catalogProducts as $product)
                        <x-product-card 
                            :id="$product['id']"
                            :title="$product['title']"
                            :price="$product['price']"
                            :originalPrice="$product['originalPrice']"
                            :discount="$product['discount']"
                            :image="$product['image']"
                            :rating="$product['rating']"
                            :sold="$product['sold']"
                            :variants="$product['variants'] ?? []"
                        />
                    @endforeach
                </div>
            @else
                <!-- Empty Search Results State -->
                <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center space-y-3 shadow-2xs">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Produk Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Maaf, tidak ada produk yang sesuai dengan kriteria pencarian / filter Anda. Coba sesuaikan rentang harga atau kata kunci.
                    </p>
                    <a href="{{ route('catalog') }}" class="inline-block text-xs bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-xl transition-colors cursor-pointer shadow-2xs">
                        Reset Filter Catalog
                    </a>
                </div>
            @endif

            <!-- Pagination Bar -->
            @if ($catalogProducts->hasPages())
            <div class="flex justify-center items-center pt-6 pb-4">
                <div class="inline-flex items-center space-x-1 text-xs sm:text-sm">
                    
                    @if ($catalogProducts->onFirstPage())
                        <span class="w-9 h-9 bg-slate-100 border border-slate-200 rounded-lg text-slate-400 flex items-center justify-center font-bold cursor-not-allowed">
                            &lt;
                        </span>
                    @else
                        <a href="{{ $catalogProducts->previousPageUrl() }}" class="w-9 h-9 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 flex items-center justify-center font-bold transition-colors">
                            &lt;
                        </a>
                    @endif

                    @foreach ($catalogProducts->onEachSide(1)->links()->elements as $element)
                        @if (is_string($element))
                            <span class="w-9 h-9 flex items-center justify-center text-slate-400 font-bold">
                                {{ $element }}
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $catalogProducts->currentPage())
                                    <span class="w-9 h-9 bg-blue-700 border border-blue-700 text-white font-extrabold rounded-lg flex items-center justify-center shadow-2xs">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="w-9 h-9 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-bold transition-colors flex items-center justify-center">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($catalogProducts->hasMorePages())
                        <a href="{{ $catalogProducts->nextPageUrl() }}" class="h-9 px-3.5 bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 font-bold transition-colors flex items-center gap-1">
                            <span>Selanjutnya</span>
                            <span>&gt;</span>
                        </a>
                    @else
                        <span class="h-9 px-3.5 bg-slate-100 border border-slate-200 rounded-lg text-slate-400 flex items-center gap-1 font-bold cursor-not-allowed">
                            <span>Selanjutnya</span>
                            <span>&gt;</span>
                        </span>
                    @endif

                </div>
            </div>
            @endif

        </main>
    </div>

    <!-- MOBILE FILTER SLIDE-OVER MODAL -->
    <div 
        x-show="showMobileFilter" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-hidden lg:hidden"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showMobileFilter = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-xs bg-white shadow-xl flex flex-col justify-between p-6 overflow-y-auto">
                
                <div class="space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="font-extrabold text-sm text-slate-900 uppercase">Filter Katalog</h3>
                        <button type="button" @click="showMobileFilter = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
                    </div>

                    <form action="{{ route('catalog') }}" method="GET" class="space-y-5">
                        
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800">Status Produk</label>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input type="radio" name="status_filter" value="" {{ !request('status_filter') ? 'checked' : '' }} class="text-blue-700">
                                    <span>Semua Status</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input type="radio" name="status_filter" value="diskon" {{ request('status_filter') == 'diskon' ? 'checked' : '' }} class="text-blue-700">
                                    <span>Sedang Diskon Promo</span>
                                </label>
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input type="radio" name="status_filter" value="ready_stock" {{ request('status_filter') == 'ready_stock' ? 'checked' : '' }} class="text-blue-700">
                                    <span>Ready Stock</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800">Rentang Harga (Rp)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min (Rp)" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs">
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max (Rp)" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs">
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold py-2.5 rounded-xl transition-colors shadow-2xs cursor-pointer"
                        >
                            Terapkan Filter
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection
