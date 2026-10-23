@extends('layouts.app')

@section('content')
<div x-data="productDetailApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 sm:py-5 space-y-4">

    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-500 flex items-center gap-1.5 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-blue-700 transition-colors">Beranda</a>
        <span>&gt;</span>
        <a href="{{ route('catalog') }}" class="hover:text-blue-700 transition-colors">Kategori</a>
        <span>&gt;</span>
        <a href="{{ route('home', ['category' => $product['category']]) }}" class="hover:text-blue-700 font-semibold text-blue-700 transition-colors">{{ $product['category'] }}</a>

        <span>&gt;</span>
        <span class="text-gray-900 font-semibold truncate">{{ $product['title'] }}</span>
    </nav>

    <!-- Top Section (Grid 2 Cols) -->
    <div class="bg-white border border-slate-200 rounded-xl p-3.5 sm:p-5 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
            
            <!-- Left: Product Images -->
            <div class="lg:col-span-5 space-y-3">
                <!-- 1 Large Square Image -->
                <div class="aspect-square max-h-[400px] sm:max-h-[420px] w-full bg-gray-100 rounded-xl overflow-hidden border border-slate-200 relative group mx-auto">
                    <img 
                        :src="activeImage" 
                        alt="{{ $product['title'] }}" 
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    >
                    @php
                        $detailDiscount = null;
                        if (!empty($product['discount'])) {
                            $dVal = trim(str_ireplace(['hemat', 'diskon', 'promo', ' '], '', $product['discount']));
                            if ($dVal !== '') {
                                $detailDiscount = str_starts_with($dVal, '-') ? $dVal : '-' . $dVal;
                            }
                        }
                    @endphp
                    <span 
                        x-show="currentDiscount" 
                        x-text="currentDiscount"
                        class="absolute top-0 left-0 bg-blue-50 text-blue-700 font-bold text-xs px-2.5 py-1 rounded-br-xl shadow-2xs border-b border-r border-blue-200 z-10 leading-normal select-none"
                        style="{{ $detailDiscount ? '' : 'display: none;' }}"
                    >
                        {{ $detailDiscount }}
                    </span>
                    <span 
                        class="absolute top-2.5 bg-blue-700 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-2xs transition-all"
                        :class="currentDiscount ? 'right-2.5' : 'left-2.5'"
                    >
                        GARANSI RESMI
                    </span>
                </div>

                <!-- 4 Small Square Thumbnails Grid -->
                <div class="grid grid-cols-4 gap-2.5 max-h-[400px] sm:max-h-[420px] mx-auto">
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

                <!-- Secondary Actions Under Thumbnails (Shopee Standard) -->
                <div class="flex items-center justify-between pt-2 px-1 text-xs text-slate-500 border-t border-slate-100">
                    <!-- Share -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400">Bagikan:</span>
                        <button type="button" @click="shareProduct()" class="hover:text-blue-700 flex items-center gap-1 font-medium transition-colors cursor-pointer" title="Bagikan Link Produk">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                            <span>Salin Link</span>
                        </button>
                    </div>

                    <div class="w-px h-3.5 bg-slate-200"></div>

                    <!-- Wishlist / Favorit -->
                    <button type="button" @click="toggleWishlist($event)" :class="isWishlisted ? 'text-rose-600 font-bold' : 'text-slate-600 hover:text-rose-600'" class="flex items-center gap-1.5 font-medium transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span x-text="isWishlisted ? 'Disukai' : 'Favorit'"></span>
                    </button>

                    <div class="w-px h-3.5 bg-slate-200"></div>

                    <!-- Chat Penjual -->
                    <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Admin, saya tertarik dengan produk ini: ' . route('product.detail', ['id' => $product['id']])) }}" target="_blank" class="flex items-center gap-1.5 text-slate-600 hover:text-emerald-600 font-medium transition-colors cursor-pointer" title="Chat Admin Penjual">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Chat Penjual</span>
                    </a>
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Title & Rating -->
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 leading-snug tracking-tight">
                        {{ $product['title'] }}
                    </h1>
                    
                    @if((float) $product['rating'] > 0 || (int) str_replace(['+', 'rb', 'k'], '', $product['sold']) > 0)
                    <div class="flex items-center gap-3 text-xs text-gray-500 mt-2 pb-2.5 border-b border-slate-100">
                        @if((float) $product['rating'] > 0)
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-900 font-bold text-sm underline underline-offset-2">{{ $product['rating'] }}</span>
                            <div class="flex text-yellow-400">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                        </div>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-700 font-medium"><strong>{{ $product['reviewCount'] }}</strong> Penilaian</span>
                        @if((int) str_replace(['+', 'rb', 'k'], '', $product['sold']) > 0)
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-700 font-medium"><strong>{{ $product['sold'] }}</strong> Terjual</span>
                        @endif
                        @endif
                    </div>
                    @else
                    <div class="mt-2 pb-2.5 border-b border-slate-100"></div>
                    @endif
                </div>

                <!-- Price Box (Compact & Clean) -->
                <div class="bg-slate-50/90 p-3.5 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span 
                            class="text-xs sm:text-sm text-gray-400 line-through font-medium whitespace-nowrap" 
                            x-show="originalUnitPrice && originalUnitPrice > unitPrice" 
                            x-text="formattedOriginalTotalPrice"
                            style="{{ ($product['originalPrice'] && $product['price'] && (int)preg_replace('/[^0-9]/', '', $product['originalPrice']) > (int)preg_replace('/[^0-9]/', '', $product['price'])) ? '' : 'display: none;' }}"
                        >
                            {{ $product['originalPrice'] }}
                        </span>
                        <span class="text-xl sm:text-2xl font-extrabold text-blue-700 whitespace-nowrap" x-text="formattedTotalPrice">
                            {{ $product['price'] }}
                        </span>
                        <span 
                            x-show="currentDiscount" 
                            x-text="currentDiscount" 
                            class="bg-blue-50 text-blue-700 text-[11px] font-bold px-2 py-0.5 rounded shadow-2xs border border-blue-200 whitespace-nowrap"
                            style="{{ $detailDiscount ? '' : 'display: none;' }}"
                        >
                            {{ $detailDiscount ?? $product['discount'] }}
                        </span>
                    </div>

                    <!-- Info Harga Satuan Konsisten (Tetap Teks Bersih Saat Qty Bertambah) -->
                    <div class="text-xs text-slate-500 font-medium border-t sm:border-t-0 pt-1.5 sm:pt-0 sm:border-l border-slate-200 sm:pl-3 whitespace-nowrap">
                        <span class="text-gray-500 text-xs">
                            Harga Satuan: <strong class="text-slate-700 font-bold" x-text="formattedUnitPrice"></strong>
                        </span>
                    </div>
                </div>

                <!-- Selectors Section (Shopee Table Style Layout) -->
                <div class="space-y-3 pt-1">
                    
                    <!-- Pengiriman Info Ringkas -->
                    <div class="flex items-start gap-3 text-xs">
                        <span class="w-20 sm:w-24 shrink-0 text-slate-500 font-medium pt-0.5">Pengiriman</span>
                        <div class="flex items-center gap-2 flex-wrap text-slate-700">
                            <span class="inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded font-bold text-[11px]">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                </svg>
                                <span>Gratis Ongkir</span>
                            </span>
                            <span class="text-slate-600">Pengiriman dari Toko Resmi</span>
                        </div>
                    </div>

                    <!-- Warna / Varian Selector -->
                    <div x-show="colors && colors.length > 0" 
                         class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 p-1 rounded-lg transition-all"
                         :class="highlightColorError ? 'bg-rose-50/80 border border-rose-300 ring-2 ring-rose-200' : ''">
                        <span class="text-xs sm:text-sm font-medium text-slate-500 w-20 sm:w-24 shrink-0 flex items-center gap-1">
                            Varian
                            <span x-show="highlightColorError" class="text-rose-500 font-bold">*</span>
                        </span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-for="(color, index) in colors" :key="color">
                                <button 
                                    type="button" 
                                    @click="selectColor(color, index)"
                                    :disabled="!isColorSelectable(color)"
                                    class="h-8 px-3 text-xs rounded-md transition-all cursor-pointer select-none flex items-center gap-1 font-medium"
                                    :class="selectedColor === color 
                                        ? 'border-2 border-blue-700 text-blue-700 font-bold bg-blue-50/70 shadow-2xs ring-2 ring-blue-700/20' 
                                        : (!isColorSelectable(color) 
                                            ? 'opacity-40 border border-dashed border-gray-300 text-gray-400 bg-gray-50 cursor-not-allowed' 
                                            : 'border border-gray-300 text-slate-700 hover:border-gray-400 bg-white')"
                                >
                                    <svg x-show="selectedColor === color" class="w-3 h-3 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="color"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Size / Ukuran Selector -->
                    <template x-if="sizes && sizes.length > 0">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 p-1 rounded-lg transition-all"
                             :class="highlightSizeError ? 'bg-rose-50/80 border border-rose-300 ring-2 ring-rose-200' : ''">
                            <span class="text-xs sm:text-sm font-medium text-slate-500 w-20 sm:w-24 shrink-0 flex items-center gap-1">
                                Ukuran
                                <span x-show="highlightSizeError" class="text-rose-500 font-bold">*</span>
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <template x-for="sz in sizes" :key="sz">
                                    <button 
                                        type="button" 
                                        @click="selectSize(sz)"
                                        :disabled="!isSizeSelectable(sz)"
                                        class="h-8 min-w-[38px] px-3 text-xs rounded-md transition-all cursor-pointer select-none font-bold flex items-center justify-center"
                                        :class="selectedSize === sz 
                                            ? 'border-2 border-blue-700 text-blue-700 bg-blue-50/70 shadow-2xs ring-2 ring-blue-700/20' 
                                            : (!isSizeSelectable(sz) 
                                                ? 'opacity-40 border border-dashed border-gray-300 text-gray-400 bg-gray-50 cursor-not-allowed line-through' 
                                                : 'border border-gray-300 text-slate-700 hover:border-gray-400 bg-white')"
                                    >
                                        <span x-text="sz"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Kuantitas Selector -->
                    <div class="flex items-center gap-3 p-1">
                        <span class="text-xs sm:text-sm font-medium text-slate-500 w-20 sm:w-24 shrink-0">Kuantitas</span>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center border border-gray-300 rounded-md overflow-hidden bg-white shadow-2xs">
                                <button 
                                    type="button" 
                                    @click="decreaseQty()" 
                                    :disabled="isOutOfStock || (isFullySelected && qty <= 1)"
                                    :class="(isOutOfStock || !canChangeQty || qty <= 1) ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400' : 'hover:bg-blue-50 hover:text-blue-700 cursor-pointer text-gray-600'"
                                    class="w-8 h-8 flex items-center justify-center font-bold text-sm transition-colors select-none"
                                    aria-label="Kurangi kuantitas"
                                    :title="!canChangeQty ? 'Pilih varian dan ukuran terlebih dahulu' : ''"
                                >
                                    -
                                </button>
                                <input 
                                    type="number" 
                                    :min="isOutOfStock ? 0 : 1" 
                                    :max="remainingStock" 
                                    x-model.number="qty"
                                    :disabled="!canChangeQty"
                                    :readonly="!canChangeQty"
                                    @input="onQtyInput()"
                                    @click="if(!canChangeQty) validateSelection()"
                                    :class="!canChangeQty ? 'bg-gray-50 text-gray-400 cursor-not-allowed select-none' : 'bg-white text-slate-900 focus:bg-white'"
                                    class="w-12 h-8 text-center text-xs sm:text-sm font-bold border-x border-gray-200 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none transition-colors"
                                    :title="!canChangeQty ? 'Pilih varian dan ukuran terlebih dahulu' : ''"
                                >
                                <button 
                                    type="button" 
                                    @click="increaseQty()" 
                                    :disabled="isOutOfStock || (isFullySelected && qty >= remainingStock)"
                                    :class="(isOutOfStock || !canChangeQty || (isFullySelected && qty >= remainingStock)) ? 'opacity-40 cursor-not-allowed bg-gray-50 text-gray-400' : 'hover:bg-blue-50 hover:text-blue-700 cursor-pointer text-gray-600'"
                                    class="w-8 h-8 flex items-center justify-center font-bold text-sm transition-colors select-none"
                                    aria-label="Tambah kuantitas"
                                    :title="!canChangeQty ? 'Pilih varian dan ukuran terlebih dahulu' : ''"
                                >
                                    +
                                </button>
                            </div>
                            <span class="text-xs text-gray-500">
                                <template x-if="!isOutOfStock">
                                    <span>Tersisa <strong class="text-slate-800 font-bold" x-text="remainingStock"></strong> buah</span>
                                </template>
                                <template x-if="isOutOfStock">
                                    <span class="text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Stok Habis</span>
                                </template>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Validation Alert Box -->
                <div x-show="validationError" x-transition class="text-rose-600 font-medium text-xs bg-rose-50 border border-rose-200 px-3 py-2 rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="validationError"></span>
                </div>

                <!-- Action Buttons (Shopee Sizing & Colors - Proporsional) -->
                <div class="flex items-center gap-3 pt-2 flex-wrap">
                    <!-- Left: Shopee Style Light Blue Button (+ Tambah ke Keranjang) -->
                    <button 
                        type="button" 
                        @click="addToCart($event)"
                        :disabled="isOutOfStock"
                        :class="isOutOfStock 
                            ? 'opacity-50 cursor-not-allowed bg-gray-100 border-gray-300 text-gray-400' 
                            : 'bg-blue-50 hover:bg-blue-100/80 border border-blue-600 text-blue-700 cursor-pointer shadow-2xs active:scale-[0.99]'"
                        class="h-11 px-5 sm:px-6 font-bold rounded-lg transition-all flex items-center justify-center gap-2 text-xs sm:text-sm shrink-0"
                    >
                        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <span x-text="isOutOfStock ? 'Stok Habis' : 'Tambah ke Keranjang'"></span>
                    </button>

                    <!-- Right: Solid Blue Button (Beli Langsung) -->
                    <button 
                        type="button" 
                        @click="buyNow()"
                        :disabled="isOutOfStock"
                        :class="isOutOfStock 
                            ? 'opacity-50 cursor-not-allowed bg-gray-300 text-gray-500' 
                            : 'bg-blue-700 hover:bg-blue-800 text-white cursor-pointer shadow-xs shadow-blue-700/25 active:scale-[0.99]'"
                        class="h-11 px-8 sm:px-10 font-bold rounded-lg transition-all text-center text-xs sm:text-sm shrink-0"
                    >
                        <span x-text="isOutOfStock ? 'Stok Habis' : 'Beli Langsung'"></span>
                    </button>
                </div>

                <!-- Jaminan Keaslian & Layanan Toko (Clean Bottom Footer) -->
                <div class="flex items-center gap-4 sm:gap-6 pt-3.5 mt-2 border-t border-slate-100 text-[11px] sm:text-xs text-slate-500 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="font-medium text-slate-700">100% Original</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span class="font-medium text-slate-700">7 Hari Pengembalian</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium text-slate-700">Pengiriman Cepat</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Tabs Section (Spesifikasi & Deskripsi - Compact) -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        
        <!-- Tabs Header -->
        <div class="flex border-b border-slate-200 bg-slate-50/50">
            <button 
                type="button"
                @click="activeTab = 'specs'" 
                :class="activeTab === 'specs' ? 'border-b-2 border-blue-700 text-blue-700 font-bold bg-white' : 'text-gray-600 hover:text-blue-700 font-semibold'" 
                class="px-5 py-2.5 text-xs sm:text-sm transition-colors cursor-pointer"
            >
                Spesifikasi
            </button>
            <button 
                type="button"
                @click="activeTab = 'desc'" 
                :class="activeTab === 'desc' ? 'border-b-2 border-blue-700 text-blue-700 font-bold bg-white' : 'text-gray-600 hover:text-blue-700 font-semibold'" 
                class="px-5 py-2.5 text-xs sm:text-sm transition-colors cursor-pointer"
            >
                Deskripsi
            </button>
            @if(!empty($product['size_guide_image']))
            <button 
                type="button"
                @click="activeTab = 'guide'" 
                :class="activeTab === 'guide' ? 'border-b-2 border-blue-700 text-blue-700 font-bold bg-white' : 'text-gray-600 hover:text-blue-700 font-semibold'" 
                class="px-5 py-2.5 text-xs sm:text-sm transition-colors cursor-pointer"
            >
                Panduan
            </button>
            @endif
        </div>

        <!-- Content Area -->
        <div class="p-4 sm:p-5">
            
            <!-- Specs Tab Content (Shopee Style Layout) -->
            <div x-show="activeTab === 'specs'" class="space-y-5">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-3">Spesifikasi Produk</h3>
                    
                    <div class="space-y-2.5 text-xs sm:text-sm">
                        @foreach($product['specs'] as $specKey => $specVal)
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-4">
                                <!-- Label (Shopee style soft grey) -->
                                <span class="w-40 sm:w-52 text-slate-400 font-normal shrink-0">{{ $specKey }}</span>
                                
                                <!-- Value (Shopee style) -->
                                <div class="text-slate-800 font-normal flex-1">
                                    @if(is_array($specVal) && ($specVal['type'] ?? '') === 'breadcrumb')
                                        <div class="flex items-center flex-wrap gap-1.5 text-blue-700">
                                            @foreach($specVal['items'] as $bIdx => $bItem)
                                                @if(!empty($bItem['url']))
                                                    <a href="{{ $bItem['url'] }}" class="text-blue-700 hover:text-blue-800 hover:underline transition-colors">{{ $bItem['name'] }}</a>
                                                @else
                                                    <span class="text-blue-700">{{ $bItem['name'] }}</span>
                                                @endif
                                                @if(!$loop->last)
                                                    <span class="text-slate-400 text-xs">&gt;</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif(is_array($specVal) && ($specVal['type'] ?? '') === 'link')
                                        <a href="{{ $specVal['url'] }}" class="text-blue-700 hover:text-blue-800 hover:underline transition-colors font-medium">
                                            {{ $specVal['value'] }}
                                        </a>
                                    @else
                                        <span>{{ $specVal }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shopee Style: Deskripsi Produk Directly Below Specs -->
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-2.5">Deskripsi Produk</h3>
                    <div class="font-normal text-slate-700 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                        {!! nl2br(e($product['description'])) !!}
                    </div>
                </div>
            </div>

            <!-- Description Tab Content -->
            <div x-show="activeTab === 'desc'" class="space-y-3 text-xs sm:text-sm text-slate-700 leading-relaxed">
                <div class="font-normal text-slate-800 text-xs sm:text-sm leading-relaxed whitespace-pre-line">
                    {!! nl2br(e($product['description'])) !!}
                </div>
            </div>

            <!-- Guide Tab Content -->
            @if(!empty($product['size_guide_image']))
            <div x-show="activeTab === 'guide'" class="space-y-3 text-xs sm:text-sm text-slate-700 leading-relaxed">
                <p class="font-bold text-slate-800 text-xs sm:text-sm mb-2">
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

                <!-- Review Items List (In-Page Paginated Shopee Style) -->
                <div id="product-reviews-section">
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
                    
                    <p class="text-xs text-slate-500 mb-3" x-text="`Menampilkan ${paginatedReviews.length} dari ${filteredReviews.length} ulasan`"></p>

                    <style>
                        #reviews-scroll-list::-webkit-scrollbar {
                            width: 6px;
                        }
                        #reviews-scroll-list::-webkit-scrollbar-track {
                            background: #f8fafc;
                            border-radius: 9999px;
                        }
                        #reviews-scroll-list::-webkit-scrollbar-thumb {
                            background: #cbd5e1;
                            border-radius: 9999px;
                        }
                        #reviews-scroll-list::-webkit-scrollbar-thumb:hover {
                            background: #94a3b8;
                        }
                    </style>

                    <!-- Scrollable reviews list container (Rating summary stays still / diem aja) -->
                    <div id="reviews-scroll-list" class="space-y-6 max-h-[540px] sm:max-h-[600px] overflow-y-auto pr-2 sm:pr-3 overscroll-contain" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 #f8fafc;">
                        <template x-for="review in paginatedReviews" :key="review.id">
                            <div class="border-b border-slate-100 pb-5 space-y-2.5">
                                <!-- Top Row: Avatar + User Info + Stars + Date & Variant -->
                                <div class="flex items-start gap-3">
                                    <template x-if="review.userAvatar">
                                        <img :src="review.userAvatar" alt="Avatar" class="w-9 h-9 rounded-full object-cover shrink-0 border border-slate-200">
                                    </template>
                                    <template x-if="!review.userAvatar">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-500 font-bold flex items-center justify-center text-xs uppercase shrink-0" x-text="getInitial(review)"></div>
                                    </template>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs sm:text-sm font-medium text-slate-800 leading-tight" x-text="getUserName(review)"></h4>
                                        
                                        <!-- Stars (Consistent Yellow / Amber Rating) -->
                                        <div class="flex text-amber-400 my-1">
                                            <template x-for="i in 5">
                                                <svg class="w-3.5 h-3.5" :class="i <= review.rating ? 'fill-current' : 'text-slate-200 fill-slate-200'" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </template>
                                        </div>

                                        <!-- Date & Variant (Shopee format: YYYY-MM-DD HH:mm | Variasi: ...) -->
                                        <div class="text-[11px] sm:text-xs text-slate-400">
                                            <span x-text="formatShopeeDate(review.created_at || review.date)"></span>
                                            <span class="mx-1 text-slate-300">|</span>
                                            <span>Variasi: </span>
                                            <span x-text="review.variant ? review.variant.replace(/^(Varian(si)?:\s*)+/i, '') : 'Standar'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Body: Comment Text, Photo Thumbnails, and Thumbs Up -->
                                <div class="pl-12 space-y-3">
                                    <!-- Comment Text -->
                                    <p class="text-xs sm:text-sm text-slate-800 leading-relaxed font-normal whitespace-pre-line" x-text="review.comment || 'Sangat memuaskan.'"></p>

                                    <!-- Photo Thumbnails -->
                                    <template x-if="review.image">
                                        <div class="flex flex-wrap gap-2 pt-1">
                                            <button 
                                                type="button" 
                                                @click="openImagePreview(review)" 
                                                class="block w-18 h-18 sm:w-20 sm:h-20 rounded-md overflow-hidden border border-slate-200 hover:border-blue-500 transition-all cursor-zoom-in group relative text-left bg-slate-50"
                                                title="Klik untuk melihat foto lebih besar"
                                            >
                                                <img :src="review.image" alt="Foto Ulasan" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                                </div>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- Thumbs Up / Helpful Action Button -->
                                    <div class="pt-0.5">
                                        <button 
                                            type="button" 
                                            @click="toggleLike(review)" 
                                            class="inline-flex items-center gap-1.5 text-xs transition-colors cursor-pointer group active:scale-95"
                                            :class="review.isLiked ? 'text-blue-600 font-semibold' : 'text-slate-400 hover:text-slate-600'"
                                            title="Membantu"
                                        >
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-y-0.5" :class="review.isLiked ? 'fill-current text-blue-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                            </svg>
                                            <span x-text="review.likes || 0"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="filteredReviews.length === 0">
                            <div class="text-center py-8">
                                <p class="text-sm text-slate-500">Tidak ada ulasan yang cocok dengan filter Anda.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Consistent Blue Pagination matching user brand -->
                    <template x-if="filteredReviews.length > 0">
                        <div class="flex items-center justify-center sm:justify-end gap-6 sm:gap-7 pt-8 pb-4">
                            <!-- Prev Chevron (<) -->
                            <button 
                                type="button" 
                                @click="changePage(currentPage - 1)" 
                                :disabled="currentPage === 1"
                                class="transition-colors cursor-pointer disabled:cursor-not-allowed flex items-center justify-center p-1"
                                :class="currentPage === 1 ? 'text-slate-300' : 'text-slate-400 hover:text-blue-600'"
                                title="Halaman Sebelumnya"
                            >
                                <svg class="w-4 h-4 stroke-[1.6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            <!-- Pages and Ellipsis -->
                            <template x-for="(pageItem, idx) in visiblePages" :key="idx">
                                <div class="flex items-center justify-center">
                                    <!-- Active Page (Solid Blue Box) -->
                                    <template x-if="pageItem !== '...' && currentPage === pageItem">
                                        <button 
                                            type="button" 
                                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-[3px] bg-blue-600 text-white flex items-center justify-center text-sm font-normal shadow-2xs select-none"
                                            x-text="pageItem"
                                        ></button>
                                    </template>

                                    <!-- Inactive Page (Plain Text, No Border, No Box) -->
                                    <template x-if="pageItem !== '...' && currentPage !== pageItem">
                                        <button 
                                            type="button" 
                                            @click="changePage(pageItem)" 
                                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-sm font-normal text-slate-400 hover:text-blue-600 transition-colors cursor-pointer select-none"
                                            x-text="pageItem"
                                        ></button>
                                    </template>

                                    <!-- Ellipsis (...) -->
                                    <template x-if="pageItem === '...'">
                                        <span class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-sm font-normal text-slate-400 select-none">
                                            ...
                                        </span>
                                    </template>
                                </div>
                            </template>

                            <!-- Next Chevron (>) -->
                            <button 
                                type="button" 
                                @click="changePage(currentPage + 1)" 
                                :disabled="currentPage === totalPages"
                                class="transition-colors cursor-pointer disabled:cursor-not-allowed flex items-center justify-center p-1"
                                :class="currentPage === totalPages ? 'text-slate-300' : 'text-slate-400 hover:text-blue-600'"
                                title="Halaman Selanjutnya"
                            >
                                <svg class="w-4 h-4 stroke-[1.6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- SHOPEE STYLE: MEDIA REVIEW VIEWER MODAL (FULLSCREEN & PROMINENT CLOSE BUTTON) -->
        <div 
            x-show="previewImageUrl" 
            x-cloak 
            @keydown.escape.window="closeImagePreview()"
            class="fixed inset-0 z-[9999] bg-black/95 backdrop-blur-md flex flex-col"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <!-- TOP BAR: SHOPEE STYLE WITH HIGH VISIBILITY CLOSE BUTTON -->
            <div class="h-16 px-4 sm:px-6 bg-neutral-900/90 border-b border-white/10 flex items-center justify-between shrink-0 text-white z-20 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/10 text-yellow-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </span>
                    <div>
                        <h3 class="font-bold text-white text-sm sm:text-base leading-tight">Foto Ulasan Pembeli</h3>
                        <p class="text-[11px] text-slate-400">Klik tombol Tutup atau tekan ESC untuk keluar</p>
                    </div>
                </div>

                <!-- CLEAR & PROMINENT CLOSE BUTTON (X) -->
                <button 
                    type="button" 
                    @click="closeImagePreview()" 
                    class="group flex items-center gap-2 px-4 py-2 rounded-xl bg-white/15 hover:bg-red-600 text-white font-bold text-xs sm:text-sm transition-all duration-200 cursor-pointer border border-white/20 hover:border-red-500 shadow-lg active:scale-95"
                    title="Tutup (Esc)"
                >
                    <span class="tracking-wide">Tutup</span>
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-90 duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- MAIN VIEWER BODY (SPLIT VIEW SHOPEE CONCEPT) -->
            <div class="flex-1 flex flex-col md:flex-row overflow-hidden relative">
                <!-- LEFT / CENTER: PHOTO DISPLAY (CLICK OUTSIDE CLOSES) -->
                <div class="flex-1 flex items-center justify-center p-4 sm:p-8 relative bg-black/60 overflow-hidden" @click="closeImagePreview()">
                    <div class="relative max-w-full max-h-full flex items-center justify-center" @click.stop>
                        <img 
                            :src="previewImageUrl" 
                            alt="Foto Ulasan Pembeli" 
                            class="max-w-full max-h-[72vh] md:max-h-[82vh] object-contain rounded-xl shadow-2xl border border-white/10 select-none"
                        >
                    </div>

                    <!-- Floating Close Button for Mobile -->
                    <button 
                        type="button" 
                        @click.stop="closeImagePreview()" 
                        class="md:hidden absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-black/70 text-white border border-white/20 flex items-center justify-center cursor-pointer shadow-lg active:scale-90"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- RIGHT SIDE: SHOPEE REVIEW DETAILS PANEL -->
                <template x-if="activeReviewForMedia">
                    <div class="w-full md:w-80 lg:w-96 bg-neutral-900 border-t md:border-t-0 md:border-l border-white/10 p-5 sm:p-6 text-white flex flex-col justify-between shrink-0 overflow-y-auto max-h-[35vh] md:max-h-full custom-scrollbar">
                        <div class="space-y-4">
                            <!-- User Profile & Stars -->
                            <div class="flex items-center gap-3">
                                <template x-if="activeReviewForMedia.userAvatar">
                                    <img :src="activeReviewForMedia.userAvatar" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-white/20 shrink-0">
                                </template>
                                <template x-if="!activeReviewForMedia.userAvatar">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-sm uppercase shrink-0" x-text="getInitial(activeReviewForMedia)"></div>
                                </template>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-white" x-text="getUserName(activeReviewForMedia)"></h4>
                                        <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.2 rounded font-medium">Terverifikasi</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <div class="flex text-amber-400">
                                            <template x-for="i in 5">
                                                <svg class="w-3.5 h-3.5" :class="i <= activeReviewForMedia.rating ? 'fill-current' : 'text-slate-600 fill-slate-600'" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            </template>
                                        </div>
                                        <span class="text-xs text-slate-400 font-medium" x-text="formatShopeeDate(activeReviewForMedia.created_at || activeReviewForMedia.date)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Variant Info -->
                            <div class="bg-white/5 border border-white/10 rounded-lg p-2.5 text-xs text-slate-300">
                                <span class="text-slate-400 font-medium">Varian Produk:</span>
                                <span class="font-bold text-white ml-1" x-text="activeReviewForMedia.variant ? activeReviewForMedia.variant.replace(/^(Varian(si)?:\s*)+/i, '') : 'Standar'"></span>
                            </div>

                            <!-- Comment Text -->
                            <div class="space-y-1">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Ulasan:</p>
                                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed font-normal whitespace-pre-line" x-text="activeReviewForMedia.comment || 'Sangat memuaskan.'"></p>
                            </div>
                        </div>

                        <!-- Bottom Helper action in Shopee style -->
                        <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                            <button 
                                type="button" 
                                @click="toggleLike(activeReviewForMedia)"
                                class="flex items-center gap-1.5 transition-colors cursor-pointer group active:scale-95"
                                :class="activeReviewForMedia.isLiked ? 'text-blue-500 font-semibold' : 'text-slate-400 hover:text-white'"
                            >
                                <svg class="w-4 h-4 transition-transform group-hover:-translate-y-0.5" :class="activeReviewForMedia.isLiked ? 'fill-current text-blue-500' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                <span>Membantu (<span x-text="activeReviewForMedia.likes || 0"></span>)</span>
                            </button>
                            <button 
                                type="button" 
                                @click="closeImagePreview()" 
                                class="px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium transition-colors cursor-pointer"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
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
    const rawVariants = @json($product['variantsList'] ?? []);
    const rawColors = @json($product['colors'] ?? []);
    const rawSizes = @json($product['sizes'] ?? []);
    const totalProductStock = {{ $product['stock'] ?? 0 }};
    const basePrice = parseInt(@json($product['price']).replace(/[^0-9]/g, ''), 10) || 0;
    const baseOriginalPrice = parseInt(@json($product['originalPrice'] ?? '').replace(/[^0-9]/g, ''), 10) || 0;

    return {
        isLoggedIn: @json(session()->has('user') || auth()->check()),
        isWishlisted: @json($isWishlisted),
        images: @json($product['images']),
        defaultImage: '{{ $product['images'][0] ?? $product['image'] }}',
        activeImage: '{{ $product['images'][0] ?? $product['image'] }}',
        colors: rawColors,
        colorVariants: @json($product['colorVariants'] ?? []),
        selectedColor: '', // Belum memilih varian di awal secara default
        sizes: rawSizes,
        selectedSize: '',  // Belum memilih ukuran di awal secara default
        qty: totalProductStock > 0 ? 1 : 0,
        totalStock: totalProductStock,
        remainingStock: totalProductStock,
        baseUnitPrice: basePrice,
        unitPrice: basePrice,
        originalUnitPrice: baseOriginalPrice,
        baseOriginalUnitPrice: baseOriginalPrice,
        defaultDiscount: @json($detailDiscount ?? $product['discount'] ?? ''),
        activeTab: 'specs',
        reviewFilters: ['Semua', '5 Bintang', '4 Bintang', 'Dengan Foto', 'Dengan Komentar'],
        variantsList: rawVariants,
        validationError: '',
        highlightColorError: false,
        highlightSizeError: false,

        init() {
            // Inisialisasi awal: belum memilih varian & ukuran, stok menampilkan total produk
            this.updateState();
        },

        get hasVariants() {
            return Boolean(this.needsColor || this.needsSize || (Array.isArray(this.variantsList) && this.variantsList.length > 0));
        },

        get needsColor() {
            return Array.isArray(this.colors) && this.colors.length > 0;
        },

        get needsSize() {
            return Array.isArray(this.sizes) && this.sizes.length > 0;
        },

        get isFullySelected() {
            let colorOk = !this.needsColor || Boolean(this.selectedColor);
            let sizeOk = !this.needsSize || Boolean(this.selectedSize);
            return colorOk && sizeOk;
        },

        get canChangeQty() {
            return (!this.hasVariants || this.isFullySelected) && !this.isOutOfStock;
        },

        get isOutOfStock() {
            if (this.isFullySelected) {
                return this.remainingStock <= 0;
            }
            return this.totalStock <= 0;
        },

        get totalPrice() {
            return this.unitPrice * (this.qty || 1);
        },

        get formattedTotalPrice() {
            return 'Rp ' + this.totalPrice.toLocaleString('id-ID');
        },

        get formattedOriginalTotalPrice() {
            if (!this.originalUnitPrice || this.originalUnitPrice <= this.unitPrice) return '';
            return 'Rp ' + (this.originalUnitPrice * (this.qty || 1)).toLocaleString('id-ID');
        },

        get formattedUnitPrice() {
            return 'Rp ' + this.unitPrice.toLocaleString('id-ID');
        },

        get currentDiscount() {
            if (this.originalUnitPrice && this.unitPrice && this.originalUnitPrice > this.unitPrice) {
                let pct = Math.round(((this.originalUnitPrice - this.unitPrice) / this.originalUnitPrice) * 100);
                return pct > 0 ? `-${pct}%` : '';
            }
            if (!this.originalUnitPrice && this.defaultDiscount) {
                return this.defaultDiscount;
            }
            return '';
        },

        get variantString() {
            let parts = [];
            if (this.selectedColor) parts.push('Warna: ' + this.selectedColor);
            if (this.selectedSize) parts.push('Ukuran: ' + this.selectedSize);
            return parts.join(', ') || 'Standar';
        },

        isColorSelectable(color) {
            if (!this.selectedSize) return true;
            if (!Array.isArray(this.variantsList) || this.variantsList.length === 0) return true;
            return this.variantsList.some(v => 
                (v.color || '').toLowerCase() === (color || '').toLowerCase() && 
                (v.size === this.selectedSize || (Array.isArray(v.sizes) && v.sizes.includes(this.selectedSize)))
            );
        },

        isSizeSelectable(sz) {
            if (!this.selectedColor) return true;
            if (!Array.isArray(this.variantsList) || this.variantsList.length === 0) return true;
            return this.variantsList.some(v => 
                (v.color || '').toLowerCase() === (this.selectedColor || '').toLowerCase() && 
                (v.size === sz || (Array.isArray(v.sizes) && v.sizes.includes(sz)))
            );
        },

        selectColor(color, index) {
            this.validationError = '';
            this.highlightColorError = false;

            if (this.selectedColor === color) {
                // Klik lagi untuk batalkan pilihan (toggle off)
                this.selectedColor = '';
                if (!this.selectedSize) {
                    this.activeImage = this.defaultImage;
                }
            } else {
                this.selectedColor = color;

                // Ganti gambar aktif ke gambar varian jika ada
                let matchV = Array.isArray(this.variantsList) 
                    ? this.variantsList.find(v => (v.color || '').toLowerCase() === (color || '').toLowerCase() && v.image)
                    : null;
                if (matchV && matchV.image) {
                    this.activeImage = matchV.image;
                } else if (Array.isArray(this.colorVariants) && this.colorVariants[index] && this.colorVariants[index].image) {
                    this.activeImage = this.colorVariants[index].image;
                } else if (Array.isArray(this.images) && this.images[index]) {
                    this.activeImage = this.images[index];
                }

                // Jika ukuran yang terpilih saat ini tidak cocok dengan warna baru ini, reset ukuran
                if (this.selectedSize && !this.isSizeSelectable(this.selectedSize)) {
                    this.selectedSize = '';
                }
            }

            this.updateState();
        },

        selectSize(sz) {
            this.validationError = '';
            this.highlightSizeError = false;

            if (this.selectedSize === sz) {
                // Klik lagi untuk batalkan pilihan (toggle off)
                this.selectedSize = '';
            } else {
                this.selectedSize = sz;

                // Jika warna yang terpilih saat ini tidak cocok dengan ukuran baru ini, reset warna
                if (this.selectedColor && !this.isColorSelectable(this.selectedColor)) {
                    this.selectedColor = '';
                }
            }

            this.updateState();
        },

        updateState() {
            if (this.isFullySelected) {
                let matchVar = null;
                if (Array.isArray(this.variantsList) && this.variantsList.length > 0) {
                    matchVar = this.variantsList.find(v => {
                        let cMatch = !this.needsColor || (v.color || '').toLowerCase() === (this.selectedColor || '').toLowerCase();
                        let sMatch = !this.needsSize || (v.size === this.selectedSize || (Array.isArray(v.sizes) && v.sizes.includes(this.selectedSize)));
                        return cMatch && sMatch;
                    });
                }

                if (matchVar) {
                    if (matchVar.stock !== undefined && matchVar.stock !== null) {
                        this.remainingStock = parseInt(matchVar.stock, 10);
                    }
                    if (matchVar.price !== undefined && matchVar.price !== null) {
                        this.unitPrice = parseInt(String(matchVar.price).replace(/[^0-9]/g, ''), 10);
                    }
                    if (matchVar.original_price !== undefined && matchVar.original_price !== null && matchVar.original_price !== '') {
                        this.originalUnitPrice = parseInt(String(matchVar.original_price).replace(/[^0-9]/g, ''), 10);
                    } else {
                        this.originalUnitPrice = this.baseOriginalUnitPrice;
                    }
                    if (matchVar.image) {
                        this.activeImage = matchVar.image;
                    }
                } else {
                    this.remainingStock = 0;
                }

                if (this.remainingStock <= 0) {
                    this.qty = 0;
                } else {
                    if (!this.qty || this.qty < 1) this.qty = 1;
                    if (this.qty > this.remainingStock) this.qty = this.remainingStock;
                }
            } else if (this.selectedColor && !this.selectedSize && Array.isArray(this.variantsList) && this.variantsList.length > 0) {
                let colorVars = this.variantsList.filter(v => (v.color || '').toLowerCase() === (this.selectedColor || '').toLowerCase());
                if (colorVars.length > 0) {
                    this.remainingStock = colorVars.reduce((sum, v) => sum + (parseInt(v.stock, 10) || 0), 0);
                    this.unitPrice = this.baseUnitPrice;
                    this.originalUnitPrice = this.baseOriginalUnitPrice;
                    this.qty = (this.remainingStock > 0) ? 1 : 0;
                }
            } else if (this.selectedSize && !this.selectedColor && Array.isArray(this.variantsList) && this.variantsList.length > 0) {
                let sizeVars = this.variantsList.filter(v => v.size === this.selectedSize || (Array.isArray(v.sizes) && v.sizes.includes(this.selectedSize)));
                if (sizeVars.length > 0) {
                    this.remainingStock = sizeVars.reduce((sum, v) => sum + (parseInt(v.stock, 10) || 0), 0);
                    this.unitPrice = this.baseUnitPrice;
                    this.originalUnitPrice = this.baseOriginalUnitPrice;
                    this.qty = (this.remainingStock > 0) ? 1 : 0;
                }
            } else {
                this.remainingStock = this.totalStock;
                this.unitPrice = this.baseUnitPrice;
                this.originalUnitPrice = this.baseOriginalUnitPrice;
                this.qty = (this.remainingStock > 0) ? 1 : 0;
            }
        },

        increaseQty() {
            if (this.hasVariants && !this.isFullySelected) {
                this.validateSelection();
                return;
            }
            if (this.isOutOfStock) return;
            if (this.qty < this.remainingStock) {
                this.qty++;
            }
        },

        decreaseQty() {
            if (this.hasVariants && !this.isFullySelected) {
                this.validateSelection();
                return;
            }
            if (this.isOutOfStock) return;
            if (this.qty > 1) {
                this.qty--;
            }
        },

        onQtyInput() {
            if (this.hasVariants && !this.isFullySelected) {
                this.qty = 1;
                this.validateSelection();
                return;
            }
            if (this.isOutOfStock) {
                this.qty = 0;
                return;
            }
            if (!this.qty || this.qty < 1) {
                this.qty = 1;
            }
            if (this.qty > this.remainingStock) {
                this.qty = this.remainingStock;
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

        validateSelection() {
            this.validationError = '';
            this.highlightColorError = false;
            this.highlightSizeError = false;

            if (this.needsColor && !this.selectedColor) {
                this.validationError = 'Silakan pilih Pilihan Varian terlebih dahulu.';
                this.highlightColorError = true;
                return false;
            }

            if (this.needsSize && !this.selectedSize) {
                this.validationError = 'Silakan pilih Pilihan Ukuran terlebih dahulu.';
                this.highlightSizeError = true;
                return false;
            }

            if (this.isOutOfStock || this.remainingStock <= 0) {
                this.validationError = 'Maaf, stok untuk pilihan varian ini sedang habis.';
                return false;
            }

            if (!this.qty || this.qty < 1) {
                this.validationError = 'Kuantitas pembelian minimal 1.';
                return false;
            }

            return true;
        },

        addToCart(e) {
            if (!this.validateSelection()) {
                return;
            }

            let startEl = (e && e.currentTarget) ? e.currentTarget : null;
            if (window.flyToElement && startEl) {
                window.flyToElement(startEl, 'cart', this.activeImage);
            }

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
                        detail: { 
                            count: data.cartCount,
                            cart: data.cart,
                            product: data.product 
                        }
                    }));
                }
            }).catch(err => {
                console.error("Cart add error", err);
            });
        },

        buyNow() {
            if (!this.validateSelection()) {
                return;
            }

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
                window.location.href = data.redirectUrl || "{{ route('checkout', ['direct' => 1]) }}";
            }).catch(err => {
                console.error("Buy now error", err);
            });
        },

        toggleWishlist(e) {
            let startEl = (e && e.currentTarget) ? e.currentTarget : null;
            if (!this.isLoggedIn) {
                alert('Silakan login terlebih dahulu untuk menyimpan ke Wishlist.');
                window.location.href = "{{ route('login') }}";
                return;
            }

            if (!this.isWishlisted && window.flyToElement && startEl) {
                window.flyToElement(startEl, 'wishlist', this.activeImage);
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
                    if (data.wishlist_count !== undefined) {
                        window.dispatchEvent(new CustomEvent('wishlist-count-updated', {
                            detail: { count: data.wishlist_count }
                        }));
                    }
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
        currentPage: 1,
        perPage: 6, // 6 ulasan per halaman sesuai permintaan user
        
        // Lightbox states
        previewImageUrl: null,
        activeReviewForMedia: null,
        
        init() {
            this.$watch('filterMedia', () => { this.currentPage = 1; });
            this.$watch('filterRatings', () => { this.currentPage = 1; });
            this.$watch('filterTopics', () => { this.currentPage = 1; });
            this.$watch('sortBy', () => { this.currentPage = 1; });
        },
        
        get totalPages() {
            return Math.ceil(this.filteredReviews.length / this.perPage) || 1;
        },
        
        get paginatedReviews() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredReviews.slice(start, start + this.perPage);
        },

        get visiblePages() {
            const total = this.totalPages;
            const current = this.currentPage;

            if (total <= 6) {
                let pages = [];
                for (let i = 1; i <= total; i++) pages.push(i);
                return pages;
            }

            // Ketika total > 6 (seperti di screenshot Shopee)
            if (current <= 4) {
                return [1, 2, 3, 4, 5, '...', total];
            } else if (current >= total - 3) {
                return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
            } else {
                return [1, '...', current - 1, current, current + 1, '...', total];
            }
        },
        
        changePage(page) {
            if (page === '...' || page < 1 || page > this.totalPages) return;
            this.currentPage = page;
            const scrollEl = document.getElementById('reviews-scroll-list');
            if (scrollEl) {
                scrollEl.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        openImagePreview(reviewOrUrl) {
            if (typeof reviewOrUrl === 'object' && reviewOrUrl !== null) {
                this.activeReviewForMedia = reviewOrUrl;
                this.previewImageUrl = reviewOrUrl.image;
            } else {
                this.activeReviewForMedia = this.allReviews.find(r => r.image === reviewOrUrl) || null;
                this.previewImageUrl = reviewOrUrl;
            }
            document.body.style.overflow = 'hidden';
        },
        
        closeImagePreview() {
            this.previewImageUrl = null;
            this.activeReviewForMedia = null;
            document.body.style.overflow = '';
        },
        
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
            if (typeof dateStr === 'string' && !dateStr.includes('T') && !dateStr.includes('-')) {
                return dateStr;
            }
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return String(d.getDate()).padStart(2, '0') + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        },
        
        formatShopeeDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const mins = String(d.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${mins}`;
        },
        
        async toggleLike(review) {
            if (!review || !review.id) return;
            if (review.isLiking) return; // debounce
            review.isLiking = true;

            // Optimistic update
            const prevLiked = !!review.isLiked;
            const prevLikes = Number(review.likes || 0);

            if (prevLiked) {
                review.likes = Math.max(0, prevLikes - 1);
                review.isLiked = false;
            } else {
                review.likes = prevLikes + 1;
                review.isLiked = true;
            }

            // Sync with activeReviewForMedia if currently open
            if (this.activeReviewForMedia && this.activeReviewForMedia.id === review.id) {
                this.activeReviewForMedia.likes = review.likes;
                this.activeReviewForMedia.isLiked = review.isLiked;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/reviews/${review.id}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data && data.success) {
                        review.likes = data.likes_count;
                        review.isLiked = data.is_liked;

                        // Sync to other references in allReviews array
                        const target = this.allReviews.find(r => r.id === review.id);
                        if (target && target !== review) {
                            target.likes = data.likes_count;
                            target.isLiked = data.is_liked;
                        }
                        if (this.activeReviewForMedia && this.activeReviewForMedia.id === review.id) {
                            this.activeReviewForMedia.likes = data.likes_count;
                            this.activeReviewForMedia.isLiked = data.is_liked;
                        }
                    }
                } else {
                    // Revert if request failed
                    review.likes = prevLikes;
                    review.isLiked = prevLiked;
                    if (this.activeReviewForMedia && this.activeReviewForMedia.id === review.id) {
                        this.activeReviewForMedia.likes = prevLikes;
                        this.activeReviewForMedia.isLiked = prevLiked;
                    }
                }
            } catch (err) {
                console.error("Like error:", err);
                review.likes = prevLikes;
                review.isLiked = prevLiked;
                if (this.activeReviewForMedia && this.activeReviewForMedia.id === review.id) {
                    this.activeReviewForMedia.likes = prevLikes;
                    this.activeReviewForMedia.isLiked = prevLiked;
                }
            } finally {
                review.isLiking = false;
            }
        },
        
        getUserName(review) {
            if (!review) return 'Pembeli Setia';
            if (review.userName) return review.userName;
            if (review.user && review.user.name) return review.user.name;
            return 'Pembeli Setia';
        },
        
        getInitial(review) {
            let name = this.getUserName(review);
            return name ? name.charAt(0).toUpperCase() : 'P';
        }
    }
}
</script>
@endsection
