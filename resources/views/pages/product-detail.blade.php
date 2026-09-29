@extends('layouts.app')

@section('content')
<div x-data="productDetailApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-8">

    <!-- Breadcrumb -->
    <nav class="text-xs sm:text-sm text-gray-500 flex items-center gap-1.5 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
        <span>&gt;</span>
        <a href="{{ route('catalog') }}" class="hover:text-blue-700 transition-colors">Kategori</a>
        <span>&gt;</span>
        <a href="{{ route('home', ['category' => $product['category']]) }}" class="hover:text-blue-700 font-semibold text-blue-700 transition-colors">{{ $product['category'] }}</a>

        <span>&gt;</span>
        <span class="text-gray-900 font-semibold truncate">{{ $product['title'] }}</span>
    </nav>

    <!-- Top Section (Grid 2 Cols) -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Product Images -->
            <div class="lg:col-span-5 space-y-4">
                <!-- 1 Large Square Image -->
                <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden border border-slate-200 relative group">
                    <img 
                        :src="activeImage" 
                        alt="{{ $product['title'] }}" 
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    >
                    <span class="absolute top-3 left-3 bg-blue-700 text-white text-[10px] font-bold px-2.5 py-1 rounded shadow-2xs">
                        GARANSI RESMI
                    </span>
                </div>

                <!-- 4 Small Square Thumbnails Grid -->
                <div class="grid grid-cols-4 gap-3">
                    <template x-for="(img, index) in images" :key="index">
                        <button 
                            type="button"
                            @click="selectImage(img)"
                            class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 transition-all p-0.5 focus:outline-none"
                            :class="activeImage === img ? 'border-blue-700 ring-2 ring-blue-700/20' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <img :src="img" alt="Thumbnail" class="w-full h-full object-cover rounded-md">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="lg:col-span-7 space-y-5">
                
                <!-- Title & Rating -->
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug tracking-tight">
                        {{ $product['title'] }}
                    </h1>
                    
                    @if((float) $product['rating'] > 0 || (int) str_replace(['+', 'rb', 'k'], '', $product['sold']) > 0)
                    <div class="flex items-center gap-4 text-xs sm:text-sm text-gray-500 mt-3 pb-3 border-b border-slate-100">
                        @if((float) $product['rating'] > 0)
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-900 font-extrabold text-base underline underline-offset-2">{{ $product['rating'] }}</span>
                            <div class="flex text-yellow-400">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                        </div>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-700 font-medium"><strong>{{ $product['reviewCount'] }}</strong> Penilaian</span>
                        @if((int) str_replace(['+', 'rb', 'k'], '', $product['sold']) > 0)
                        <span class="text-slate-300">|</span>
                        @endif
                        @endif
                        
                        @if((int) str_replace(['+', 'rb', 'k'], '', $product['sold']) > 0)
                        <span class="text-slate-700 font-medium"><strong>{{ $product['sold'] }}</strong> Terjual</span>
                        @endif
                    </div>
                    @else
                    <div class="mt-3 pb-3 border-b border-slate-100"></div>
                    @endif
                </div>

                <!-- Price Box (bg-gray-50) -->
                <div class="bg-gray-50 p-4 sm:p-5 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5 flex-wrap">
                        @if($product['originalPrice'])
                            <span class="text-sm sm:text-base text-gray-400 line-through font-medium" x-text="formattedOriginalTotalPrice"></span>
                        @endif
                        <span class="text-2xl sm:text-3xl font-extrabold text-blue-700" x-text="formattedTotalPrice"></span>
                        @if($product['discount'])
                            <span class="bg-red-100 text-red-700 text-xs font-extrabold px-2.5 py-1 rounded shadow-2xs">
                                Diskon {{ $product['discount'] }}
                            </span>
                        @endif
                    </div>

                    <!-- Dynamic Subtotal Info / Per-unit price badge when Qty > 1 -->
                    <div class="text-xs text-slate-500 font-medium border-t sm:border-t-0 pt-2 sm:pt-0 sm:border-l border-slate-200 sm:pl-4">
                        <template x-if="qty > 1">
                            <span class="text-blue-700 font-bold bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-md inline-block">
                                Subtotal (<span x-text="qty"></span> barang) @ <span x-text="formattedUnitPrice"></span>
                            </span>
                        </template>
                        <template x-if="qty === 1">
                            <span class="text-gray-500">
                                Harga Per Satuan: <strong class="text-slate-700" x-text="formattedUnitPrice"></strong>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Selectors (Warna & Kuantitas) -->
                <div class="space-y-4 pt-1">
                    <!-- Warna / Varian Selector -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <span class="text-sm font-semibold text-slate-700 w-28 shrink-0">
                            Pilihan Varian
                        </span>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <template x-for="(color, index) in colors" :key="color">
                                <button 
                                    type="button" 
                                    @click="selectColor(color, index)"
                                    class="px-4 py-2 text-xs sm:text-sm rounded-lg transition-all cursor-pointer select-none flex items-center gap-1.5"
                                    :class="selectedColor === color ? 'border-2 border-blue-700 text-blue-700 font-bold bg-blue-50/70 shadow-2xs' : 'border border-gray-300 text-slate-700 hover:border-gray-400 bg-white'"
                                >
                                    <svg x-show="selectedColor === color" class="w-3.5 h-3.5 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="color"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Size / Ukuran Selector -->
                    <template x-if="sizes && sizes.length > 0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 border-t border-slate-100 pt-3">
                            <span class="text-sm font-semibold text-slate-700 w-28 shrink-0">
                                Pilihan Ukuran
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <template x-for="sz in sizes" :key="sz">
                                    <button 
                                        type="button" 
                                        @click="selectSize(sz)"
                                        class="px-3.5 py-1.5 text-xs sm:text-sm rounded-lg transition-all cursor-pointer select-none font-bold"
                                        :class="selectedSize === sz ? 'border-2 border-blue-700 text-blue-700 bg-blue-50/70 shadow-2xs' : 'border border-gray-300 text-slate-700 hover:border-gray-400 bg-white'"
                                    >
                                        <span x-text="sz"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>


                    <!-- Kuantitas Selector -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-semibold text-slate-700 w-28 shrink-0">Kuantitas</span>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-2xs">
                                <button 
                                    type="button" 
                                    @click="if(qty > 1) qty--" 
                                    :disabled="qty <= 1"
                                    :class="qty <= 1 ? 'opacity-40 cursor-not-allowed bg-gray-50' : 'hover:bg-blue-50 hover:text-blue-700 cursor-pointer'"
                                    class="px-3.5 py-2 text-gray-600 font-bold text-sm transition-colors select-none"
                                    aria-label="Kurangi kuantitas"
                                >
                                    -
                                </button>
                                <input 
                                    type="number" 
                                    min="1" 
                                    :max="remainingStock" 
                                    x-model.number="qty"
                                    @input="if(!qty || qty < 1) qty = 1; if(qty > remainingStock) qty = remainingStock;"
                                    class="w-14 py-1.5 text-center text-sm font-bold text-slate-900 border-x border-gray-200 bg-gray-50/50 focus:bg-white focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                >
                                <button 
                                    type="button" 
                                    @click="if(qty < remainingStock) qty++" 
                                    :disabled="qty >= remainingStock"
                                    :class="qty >= remainingStock ? 'opacity-40 cursor-not-allowed bg-gray-50' : 'hover:bg-blue-50 hover:text-blue-700 cursor-pointer'"
                                    class="px-3.5 py-2 text-gray-600 font-bold text-sm transition-colors select-none"
                                    aria-label="Tambah kuantitas"
                                >
                                    +
                                </button>
                            </div>
                            <span class="text-xs text-gray-500">
                                Tersisa <strong class="text-slate-800 font-bold" x-text="remainingStock - qty >= 0 ? remainingStock - qty : 0"></strong> buah
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Grid 2 Cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                    <!-- Left: Outline Blue Button -->
                    <button 
                        type="button" 
                        @click="addToCart()"
                        class="w-full bg-white hover:bg-blue-50 border-2 border-blue-700 text-blue-700 font-bold py-3 px-4 rounded-xl shadow-2xs transition-colors flex items-center justify-center gap-2 text-sm sm:text-base cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        + Tambah ke Keranjang
                    </button>

                    <!-- Right: Solid Blue Button -->
                    <button 
                        type="button" 
                        @click="buyNow()"
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-xl shadow-2xs transition-colors text-center text-sm sm:text-base cursor-pointer"
                    >
                        Beli Langsung
                    </button>
                </div>

                <!-- Secondary Actions (Chat, Wishlist, Share) -->
                <div class="flex items-center justify-center sm:justify-start gap-4 sm:gap-6 pt-5 mt-2 border-t border-slate-100">
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin, saya tertarik dengan produk ini: ' . route('product.detail', ['id' => $product['id']])) }}" target="_blank" class="flex items-center gap-1.5 text-slate-600 hover:text-blue-700 transition-colors font-medium text-sm cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Chat</span>
                    </a>
                    
                    <div class="w-px h-4 bg-slate-300 hidden sm:block"></div>
                    
                    <button type="button" @click="toggleWishlist()" :class="isWishlisted ? 'text-red-500' : 'text-slate-600 hover:text-red-500'" class="flex items-center gap-1.5 transition-colors font-medium text-sm cursor-pointer">
                        <svg class="w-5 h-5" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span>Wishlist</span>
                    </button>
                    
                    <div class="w-px h-4 bg-slate-300 hidden sm:block"></div>
                    
                    <button type="button" @click="shareProduct()" class="flex items-center gap-1.5 text-slate-600 hover:text-blue-700 transition-colors font-medium text-sm cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <span>Share</span>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- Tabs Section (Spesifikasi & Deskripsi) -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        
        <!-- Tabs Header -->
        <div class="flex border-b border-slate-200 bg-slate-50/50">
            <button 
                type="button"
                @click="activeTab = 'specs'" 
                :class="activeTab === 'specs' ? 'border-b-2 border-blue-700 text-blue-700 font-bold bg-white' : 'text-gray-600 hover:text-blue-700 font-semibold'" 
                class="px-6 py-3.5 text-sm sm:text-base transition-colors cursor-pointer"
            >
                Spesifikasi
            </button>
            <button 
                type="button"
                @click="activeTab = 'desc'" 
                :class="activeTab === 'desc' ? 'border-b-2 border-blue-700 text-blue-700 font-bold bg-white' : 'text-gray-600 hover:text-blue-700 font-semibold'" 
                class="px-6 py-3.5 text-sm sm:text-base transition-colors cursor-pointer"
            >
                Deskripsi
            </button>
            @if(!empty($product['size_guide_image']))
            <button 
                type="button"
                @click="activeTab = 'guide'" 
                :class="activeTab === 'guide' ? 'border-b-2 border-blue-700 text-blue-700 font-bold bg-white' : 'text-gray-600 hover:text-blue-700 font-semibold'" 
                class="px-6 py-3.5 text-sm sm:text-base transition-colors cursor-pointer"
            >
                Panduan
            </button>
            @endif
        </div>

        <!-- Content Area -->
        <div class="p-5 sm:p-6">
            
            <!-- Specs Tab Content (2-Column Table) -->
            <div x-show="activeTab === 'specs'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    @foreach($product['specs'] as $specKey => $specVal)
                        <div class="flex border-b border-slate-100 pb-2.5">
                            <span class="w-36 text-gray-500 font-medium shrink-0">{{ $specKey }}</span>
                            <span class="text-slate-800 font-semibold">{{ $specVal }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Description Tab Content -->
            <div x-show="activeTab === 'desc'" class="space-y-4 text-sm text-slate-700 leading-relaxed">
                <div class="font-medium text-slate-800 text-base leading-relaxed whitespace-pre-line">
                    {!! nl2br(e($product['description'])) !!}
                </div>
            </div>

            <!-- Guide Tab Content -->
            @if(!empty($product['size_guide_image']))
            <div x-show="activeTab === 'guide'" class="space-y-4 text-sm text-slate-700 leading-relaxed">
                <p class="font-medium text-slate-800 text-base mb-3">
                    Panduan Ukuran
                </p>
                <img src="{{ $product['size_guide_image'] }}" alt="Panduan Ukuran" class="max-w-full rounded-lg border border-slate-200">
            </div>
            @endif

        </div>

    </div>

    <!-- Reviews Section (PENILAIAN PRODUK - Professional UI) -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 shadow-xs flex flex-col" x-data="productReviews(@js($product['reviews']))">
        
        <!-- Title with thick blue left border -->
        <div class="border-l-4 border-blue-700 pl-3 mb-6 shrink-0">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight uppercase">ULASAN PEMBELI</h2>
        </div>

        @if($product['reviewCount'] > 0)

        <!-- Two Column Layout: Left Filter, Right Content -->
        <div class="flex flex-col md:flex-row gap-8">
            
            <!-- LEFT SIDEBAR: FILTER -->
            <div class="w-full md:w-1/4 shrink-0 md:sticky md:top-24 self-start pr-2">
                <div class="space-y-6">
                    <div>
                        <h3 class="font-bold text-slate-900 mb-3 text-sm">FILTER ULASAN</h3>
                        
                        <div class="space-y-4">
                            <!-- Media Filter -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-slate-800 text-sm">Media</h4>
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="filterMedia" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-600">Dengan Foto & Video</span>
                                </label>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Rating Filter -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-slate-800 text-sm">Rating</h4>
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </div>
                                <div class="space-y-2">
                                    @for($i=5; $i>=1; $i--)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" value="{{ $i }}" x-model="filterRatings" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-slate-600 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            {{ $i }}
                                        </span>
                                    </label>
                                    @endfor
                                </div>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Topic Filter -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-slate-800 text-sm">Topik Ulasan</h4>
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </div>
                                <div class="space-y-2">
                                    @foreach(['Kualitas Barang', 'Pelayanan Penjual', 'Kemasan Barang', 'Harga Barang'] as $topic)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" value="{{ $topic }}" x-model="filterTopics" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-slate-600">{{ $topic }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT CONTENT: STATS & REVIEWS -->
            <div class="w-full md:w-3/4 space-y-8">
                
                <!-- Rating Summary Box (Theme Blue) -->
                <div class="bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-xl p-5 sm:p-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-12">
                        
                        <!-- Left: Big Number -->
                        <div class="text-center shrink-0">
                            <div class="flex items-baseline justify-center gap-1">
                                <svg class="w-10 h-10 text-yellow-400 fill-current mb-1" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-5xl font-extrabold text-slate-900">{{ number_format($product['rating'], 1) }}</span>
                                <span class="text-lg font-bold text-slate-500">/ 5.0</span>
                            </div>
                            <p class="font-bold text-slate-800 mt-2 text-sm">98% pembeli merasa puas</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $product['reviewCount'] }} rating • {{ $product['reviewCount'] }} ulasan</p>
                        </div>
                        
                        <!-- Right: Progress Bars -->
                        <div class="w-full max-w-sm space-y-1.5">
                            @for($i=5; $i>=1; $i--)
                            @php 
                                $count = $product['ratingCounts'][$i] ?? 0;
                                $pct = $product['reviewCount'] > 0 ? ($count / $product['reviewCount']) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3 text-xs">
                                <div class="flex items-center gap-1 w-8 shrink-0 text-slate-600 font-bold">
                                    <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    {{ $i }}
                                </div>
                                <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="w-8 shrink-0 text-right text-slate-500">({{ $count }})</div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Review Items List -->
                <div class="max-h-[600px] overflow-y-auto pr-3 custom-scrollbar">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-slate-900 text-sm uppercase">ULASAN PILIHAN</h3>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span>Urutkan</span>
                            <select x-model="sortBy" class="border border-slate-300 rounded-lg text-sm px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="terbaru">Terbaru</option>
                                <option value="membantu">Paling Membantu</option>
                                <option value="tertinggi">Rating Tertinggi</option>
                                <option value="terendah">Rating Terendah</option>
                            </select>
                        </div>
                    </div>
                    
                    <p class="text-xs text-slate-500 mb-4" x-text="`Menampilkan ${filteredReviews.length} dari {{ $product['reviewCount'] }} ulasan`"></p>

                    <div class="space-y-6">
                        <template x-for="review in filteredReviews" :key="review.id">
                            <div class="border-b border-slate-100 pb-6 space-y-3">
                                <!-- Stars & Date -->
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <div class="flex text-yellow-400">
                                        <template x-for="i in 5">
                                            <svg class="w-3.5 h-3.5" :class="i <= review.rating ? 'fill-current' : 'text-slate-200 fill-slate-200'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        </template>
                                    </div>
                                    <span x-text="formatDate(review.created_at)"></span>
                                </div>
                                
                                <!-- User Info -->
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 font-bold flex items-center justify-center text-xs shadow-2xs uppercase" x-text="getInitial(review)"></div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-900" x-text="getUserName(review)"></h4>
                                        <p class="text-[10px] text-slate-500">Varian: Standar</p>
                                    </div>
                                </div>
                                
                                <!-- Comment -->
                                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-normal" x-text="review.comment || 'Sangat memuaskan.'"></p>
                                
                                <!-- Image -->
                                <template x-if="review.image">
                                    <div class="mt-3">
                                        <a :href="review.image" target="_blank" class="block w-20 h-20 rounded-lg overflow-hidden border border-slate-200 hover:border-blue-500 transition-colors cursor-zoom-in">
                                            <img :src="review.image" alt="Foto Ulasan" class="w-full h-full object-cover">
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="filteredReviews.length === 0">
                            <div class="text-center py-8">
                                <p class="text-sm text-slate-500">Tidak ada ulasan yang cocok dengan filter Anda.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Bottom Action Button -->
                    <template x-if="filteredReviews.length > 0">
                        <div class="pt-4">
                            <button 
                                type="button" 
                                class="border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-bold text-xs sm:text-sm py-2.5 px-6 rounded-lg transition-colors mx-auto block cursor-pointer"
                            >
                                Lihat Semua Ulasan
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        @else
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <p class="text-slate-500 font-medium">Belum ada penilaian untuk produk ini.</p>
            <p class="text-slate-400 text-xs mt-1">Jadilah yang pertama untuk memberikan ulasan!</p>
        </div>
        @endif

    </div>

    <!-- Related Products (PRODUK LAIN DARI TOKO INI) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight uppercase">
                PRODUK LAIN DARI TOKO INI
            </h2>
            <a href="{{ route('home') }}" class="text-xs font-bold text-blue-700 hover:text-blue-800 transition-colors">
                Lihat Semua Produk &gt;
            </a>
        </div>

        <!-- 6-Column Grid of Product Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
            @foreach($relatedProducts as $rel)
                <x-product-card 
                    :id="$rel['id']"
                    :title="$rel['title']"
                    :price="$rel['price']"
                    :originalPrice="$rel['originalPrice']"
                    :discount="$rel['discount']"
                    :image="$rel['image'] ?? ($rel['images'][0] ?? '')"
                    :rating="$rel['rating']"
                    :sold="$rel['sold']"
                    :variants="$rel['variants'] ?? []"
                />
            @endforeach
        </div>
    </div>

</div>

<!-- Alpine.js Product Detail Logic -->
<script>
function productDetailApp() {
    return {
        isLoggedIn: @json(session()->has('user') || auth()->check()),
        isWishlisted: @json($isWishlisted),
        images: @json($product['images']),
        activeImage: '{{ $product['images'][0] ?? $product['image'] }}',
        colors: @json($product['colors']),
        colorVariants: @json($product['colorVariants'] ?? []),
        selectedColor: '{{ $product['colors'][0] ?? 'Standar' }}',
        sizes: @json($product['sizes'] ?? []),
        selectedSize: '{{ $product['sizes'][0] ?? '' }}',
        qty: 1,
        remainingStock: {{ $product['stock'] }},
        unitPrice: parseInt(@json($product['price']).replace(/[^0-9]/g, ''), 10) || 0,
        originalUnitPrice: parseInt(@json($product['originalPrice'] ?? '').replace(/[^0-9]/g, ''), 10) || 0,
        activeTab: 'specs',
        reviewFilters: ['Semua', '5 Bintang', '4 Bintang', 'Dengan Foto', 'Dengan Komentar'],

        init() {
            if (this.selectedColor) {
                this.selectColor(this.selectedColor, 0);
            }
        },

        get totalPrice() {
            return this.unitPrice * (this.qty || 1);
        },

        get formattedTotalPrice() {
            return 'Rp ' + this.totalPrice.toLocaleString('id-ID');
        },

        get formattedOriginalTotalPrice() {
            if (!this.originalUnitPrice) return '';
            return 'Rp ' + (this.originalUnitPrice * (this.qty || 1)).toLocaleString('id-ID');
        },

        get formattedUnitPrice() {
            return 'Rp ' + this.unitPrice.toLocaleString('id-ID');
        },

        variantsList: @json($product['variantsList'] ?? []),
        selectColor(color, index) {
            this.selectedColor = color;
            
            // Check structured variants match
            let colorVars = Array.isArray(this.variantsList) ? this.variantsList.filter(v => (v.color || '').toLowerCase() === (color || '').toLowerCase()) : [];
            if (colorVars.length > 0) {
                let firstMatch = colorVars[0];
                if (firstMatch.image) this.activeImage = firstMatch.image;
                
                // Get available sizes for this color
                let availableSizes = colorVars.map(v => v.size || (Array.isArray(v.sizes) ? v.sizes[0] : v.sizes)).filter(Boolean);
                // unique sizes
                this.sizes = [...new Set(availableSizes)];
                
                if (this.sizes.length > 0) {
                    if (!this.sizes.includes(this.selectedSize)) {
                        this.selectSize(this.sizes[0]);
                    } else {
                        this.selectSize(this.selectedSize);
                    }
                } else {
                    if (firstMatch.stock !== undefined && firstMatch.stock !== null) this.remainingStock = firstMatch.stock;
                    if (firstMatch.price !== undefined && firstMatch.price !== null) {
                        this.unitPrice = parseInt(String(firstMatch.price).replace(/[^0-9]/g, ''), 10);
                    }
                }
            } else {
                if (this.colorVariants && this.colorVariants[index] && this.colorVariants[index].image) {
                    this.activeImage = this.colorVariants[index].image;
                } else if (this.images && this.images[index]) {
                    this.activeImage = this.images[index];
                }
            }
        },
        selectSize(sz) {
            this.selectedSize = sz;
            let matchVar = Array.isArray(this.variantsList) ? this.variantsList.find(v => (v.color || '').toLowerCase() === (this.selectedColor || '').toLowerCase() && (v.size === sz || (Array.isArray(v.sizes) && v.sizes.includes(sz)) || v.sizes === sz)) : null;
            
            if (matchVar) {
                if (matchVar.stock !== undefined && matchVar.stock !== null) this.remainingStock = matchVar.stock;
                if (matchVar.price !== undefined && matchVar.price !== null) {
                    this.unitPrice = parseInt(String(matchVar.price).replace(/[^0-9]/g, ''), 10);
                }
            }
        },

        selectImage(img) {
            this.activeImage = img;
            let matchVar = Array.isArray(this.variantsList) ? this.variantsList.find(v => v.image === img) : null;
            if (matchVar && matchVar.color) {
                this.selectColor(matchVar.color);
            } else if (Array.isArray(this.colorVariants)) {
                let matchCol = this.colorVariants.find(c => typeof c === 'object' && c.image === img);
                if (matchCol && matchCol.name) {
                    this.selectColor(matchCol.name);
                }
            }
        },

        get variantString() {
            let parts = [];
            if (this.selectedColor) parts.push('Warna: ' + this.selectedColor);
            if (this.selectedSize) parts.push('Ukuran: ' + this.selectedSize);
            return 'Varian: ' + (parts.join(', ') || 'Standar');
        },

        addToCart() {
            let matchVar = Array.isArray(this.variantsList) ? this.variantsList.find(v => (v.color || '').toLowerCase() === (this.selectedColor || '').toLowerCase() && (v.size === this.selectedSize || (Array.isArray(v.sizes) && v.sizes.includes(this.selectedSize)) || v.sizes === this.selectedSize)) : null;
            let variantId = matchVar ? (matchVar.id || matchVar.variant_id) : null;
            let imageId = matchVar ? (matchVar.image_id || null) : null;

            fetch("{{ route('cart.add') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: {{ $product['id'] }},
                    variant_id: variantId,
                    image_id: imageId,
                    name: @json($product['title']),
                    price: this.unitPrice,
                    image: this.activeImage,
                    variant: this.variantString,
                    color: this.selectedColor,
                    size: this.selectedSize,
                    qty: this.qty
                })
            }).then(res => res.json()).then(data => {
                // Show 3-second floating toast popup
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        title: @json($product['title']),
                        image: this.activeImage,
                        qty: this.qty
                    }
                }));

                // Update cart count badge live
                if (data.cartCount !== undefined) {
                    window.dispatchEvent(new CustomEvent('cart-count-updated', {
                        detail: { count: data.cartCount }
                    }));
                }
            }).catch(err => {
                console.error("Cart add error", err);
            });
        },

        buyNow() {
            let matchVar = Array.isArray(this.variantsList) ? this.variantsList.find(v => (v.color || '').toLowerCase() === (this.selectedColor || '').toLowerCase() && (v.size === this.selectedSize || (Array.isArray(v.sizes) && v.sizes.includes(this.selectedSize)) || v.sizes === this.selectedSize)) : null;
            let variantId = matchVar ? (matchVar.id || matchVar.variant_id) : null;
            let imageId = matchVar ? (matchVar.image_id || null) : null;

            fetch("{{ route('cart.buyNow') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: {{ $product['id'] }},
                    variant_id: variantId,
                    image_id: imageId,
                    name: @json($product['title']),
                    price: this.unitPrice,
                    image: this.activeImage,
                    variant: this.variantString,
                    color: this.selectedColor,
                    size: this.selectedSize,
                    qty: this.qty
                })
            }).then(res => res.json()).then(data => {
                window.location.href = "{{ route('checkout') }}";
            }).catch(err => {
                console.error("Buy now error", err);
            });
        },

        toggleWishlist() {
            if (!this.isLoggedIn) {
                alert('Silakan login terlebih dahulu untuk menyimpan ke Wishlist.');
                window.location.href = "{{ route('login') }}";
                return;
            }
            fetch("{{ route('wishlist.toggle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: {{ $product['id'] }}
                })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    this.isWishlisted = (data.status === 'added');
                }
            }).catch(err => {
                console.error("Wishlist error", err);
            });
        },

        shareProduct() {
            if (navigator.share) {
                navigator.share({
                    title: @json($product['title']),
                    url: window.location.href
                }).catch(err => {
                    console.error("Share error", err);
                });
            } else {
                navigator.clipboard.writeText(window.location.href); 
                alert('Tautan produk berhasil disalin!');
            }
        }
    }
}

function productReviews(reviewsData) {
    return {
        allReviews: reviewsData || [],
        filterMedia: false,
        filterRatings: [], // array of selected ratings, e.g. ["5", "4"]
        filterTopics: [], // array of selected topics
        sortBy: 'terbaru',
        
        get filteredReviews() {
            let filtered = this.allReviews.filter(review => {
                let matchMedia = true;
                if (this.filterMedia) {
                    matchMedia = !!review.image;
                }
                
                let matchRating = true;
                if (this.filterRatings.length > 0) {
                    // Alpine bindings for checkbox array are strings
                    matchRating = this.filterRatings.includes(String(review.rating));
                }

                let matchTopic = true;
                if (this.filterTopics.length > 0) {
                    const comment = (review.comment || '').toLowerCase();
                    matchTopic = this.filterTopics.some(topic => {
                        if (topic === 'Kualitas Barang') {
                            return comment.includes('kualitas') || comment.includes('bagus') || comment.includes('bahan') || comment.includes('deskripsi') || comment.includes('awet');
                        } else if (topic === 'Pelayanan Penjual') {
                            return comment.includes('pelayanan') || comment.includes('pengiriman') || comment.includes('respon') || comment.includes('ramah') || comment.includes('toko');
                        } else if (topic === 'Kemasan Barang') {
                            return comment.includes('kemasan') || comment.includes('packing') || comment.includes('rapi') || comment.includes('aman');
                        } else if (topic === 'Harga Barang') {
                            return comment.includes('harga') || comment.includes('murah') || comment.includes('mahal') || comment.includes('worth');
                        }
                        return false;
                    });
                }
                
                return matchMedia && matchRating && matchTopic;
            });

            // Sorting logic
            filtered.sort((a, b) => {
                if (this.sortBy === 'terbaru') {
                    return new Date(b.created_at || 0) - new Date(a.created_at || 0);
                } else if (this.sortBy === 'tertinggi') {
                    return b.rating - a.rating;
                } else if (this.sortBy === 'terendah') {
                    return a.rating - b.rating;
                } else if (this.sortBy === 'membantu') {
                    // Fake logic: highest rating + newest
                    if (b.rating !== a.rating) {
                        return b.rating - a.rating;
                    }
                    return new Date(b.created_at || 0) - new Date(a.created_at || 0);
                }
                return 0;
            });

            return filtered;
        },
        
        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        },
        
        getUserName(review) {
            if (review && review.user && review.user.name) {
                // Return masked name like Y***q
                let name = review.user.name;
                if (name.length <= 2) return name;
                return name.charAt(0) + '***' + name.charAt(name.length - 1);
            }
            return 'Guest';
        },
        
        getInitial(review) {
            let name = this.getUserName(review);
            return name ? name.charAt(0) : 'G';
        }
    }
}
</script>
@endsection
