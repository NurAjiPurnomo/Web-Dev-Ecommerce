@extends('layouts.app')

@section('content')
<div x-data="cartApp()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    
    <!-- Main Grid: 2 Columns (Left ~65%, Right ~35%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT COLUMN: Keranjang (~65% / 8 cols) -->
        <div class="lg:col-span-8 space-y-5">
            
            <!-- Header -->
            <div class="flex items-center justify-between pb-2">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Keranjang Belanja</h1>
                <span class="text-sm font-medium text-gray-500" x-text="items.length + ' Produk dalam Keranjang'"></span>
            </div>

            <!-- Select All Box -->
            <div x-show="items.length > 0" class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        :checked="isAllSelected" 
                        @change="toggleSelectAll()" 
                        class="w-5 h-5 accent-blue-600 rounded cursor-pointer"
                    >
                    <span class="font-semibold text-gray-800 text-sm sm:text-base">
                        Pilih Semua (<span x-text="selectedCount"></span>)
                    </span>
                </label>
                <button 
                    type="button" 
                    @click="removeSelectedItems()" 
                    x-show="selectedCount > 0"
                    class="text-xs sm:text-sm font-medium text-red-500 hover:text-red-700 transition-colors cursor-pointer"
                >
                    Hapus Produk Terpilih
                </button>
            </div>

            <!-- Empty Cart State -->
            <div x-show="items.length === 0" class="bg-white border border-gray-200 rounded-xl p-8 sm:p-12 text-center shadow-sm space-y-4">
                <div class="w-16 h-16 bg-blue-50 text-blue-700 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Keranjang Belanja Anda Kosong</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto" x-text="isLoggedIn ? 'Sepertinya Anda belum menambahkan produk ke dalam keranjang. Silakan cari produk impian Anda.' : 'Anda belum masuk ke akun. Silakan masuk terlebih dahulu untuk melihat dan mengelola keranjang Anda.'"></p>
                
                <div class="pt-2">
                    <template x-if="!isLoggedIn">
                        <a href="{{ route('login') }}" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors text-xs sm:text-sm shadow-2xs">
                            Masuk ke Akun
                        </a>
                    </template>
                    <template x-if="isLoggedIn">
                        <a href="{{ route('home') }}" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-bold px-6 py-2.5 rounded-lg transition-colors text-xs sm:text-sm shadow-2xs">
                            Jelajahi Produk
                        </a>
                    </template>
                </div>
            </div>

            <!-- Item Cards Container -->
            <div class="space-y-4" x-show="items.length > 0">
                <template x-for="(item, index) in items" :key="item.id">
                    <div 
                        class="bg-white border rounded-xl p-4 sm:p-5 shadow-sm space-y-4 sm:space-y-0 sm:flex sm:items-center sm:justify-between gap-4 transition-all duration-200"
                        :class="item.selected ? 'border-gray-200' : 'border-gray-200 opacity-75 bg-gray-50/50'"
                    >
                        <!-- Left Flex Items: Checkbox + Square Image + Title & Variant -->
                        <div class="flex items-center gap-3 sm:gap-4 flex-1">
                            <input 
                                type="checkbox" 
                                x-model="item.selected" 
                                class="w-5 h-5 accent-blue-600 rounded cursor-pointer shrink-0"
                            >
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-lg overflow-hidden shrink-0 flex items-center justify-center border border-gray-100 relative">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base leading-snug truncate" x-text="item.name"></h3>
                                <p class="text-xs text-gray-500 mt-1" x-text="item.variant"></p>
                                <template x-if="item.original_price && item.original_price > item.price">
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-xs text-slate-400 line-through" x-text="formatRupiah(item.original_price)"></span>
                                        <span class="bg-blue-100 text-blue-700 text-[10px] font-extrabold px-2 py-0.5 rounded" x-text="'Hemat ' + (item.discount ? item.discount.replace('Hemat ', '') : Math.round(((item.original_price - item.price)/item.original_price)*100) + '%')"></span>
                                    </div>
                                </template>
                                <p class="text-xs font-semibold text-gray-400 mt-0.5 sm:hidden" x-text="formatRupiah(item.price) + ' / unit'"></p>
                            </div>
                        </div>

                        <!-- Right Flex Items: Price + 'X' Icon + Quantity Selector -->
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <template x-if="item.original_price && item.original_price > item.price">
                                        <div class="text-[11px] text-slate-400 line-through" x-text="formatRupiah(item.original_price * item.qty)"></div>
                                    </template>
                                    <span class="text-blue-600 font-bold text-base sm:text-lg" x-text="formatRupiah(item.price * item.qty)"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="removeItem(index)" 
                                    class="text-gray-400 hover:text-red-500 transition-colors p-1 cursor-pointer" 
                                    title="Hapus Item"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Bordered Quantity Selector (- 1 +) -->
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                                <button 
                                    type="button" 
                                    @click="decreaseQty(item)" 
                                    :disabled="item.qty <= 1"
                                    :class="item.qty <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer'"
                                    class="px-3 py-1 text-gray-600 text-sm font-semibold transition-colors"
                                >
                                    -
                                </button>
                                <span class="px-3.5 py-1 text-sm font-semibold text-gray-800 border-x border-gray-200 bg-gray-50 select-none" x-text="item.qty"></span>
                                <button 
                                    type="button" 
                                    @click="increaseQty(item)" 
                                    class="px-3 py-1 text-gray-600 hover:bg-gray-100 text-sm font-semibold transition-colors cursor-pointer"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- RIGHT COLUMN: Checkout Summary (~35% / 4 cols) -->
        <div class="lg:col-span-4">
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 sm:p-6 shadow-sm sticky top-20">
                
                <h2 class="text-lg font-bold text-gray-900 mb-5 pb-3 border-b border-gray-200">Ringkasan Checkout</h2>

                <!-- Shipping / Alamat Pengiriman -->
                <div class="mb-5">
                <!-- RajaOngkir Destination City & Weight Info -->
                <div class="mb-5 p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-900 text-xs sm:text-sm flex items-center gap-1.5">
                            <span 
                                class="w-2.5 h-2.5 rounded-full" 
                                :class="isLiveApi ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"
                            ></span>
                            <span>Lokasi Tujuan &amp; Hitung RajaOngkir</span>
                        </label>

                        <!-- Live Status Badge -->
                        <template x-if="isLiveApi">
                            <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 text-[9px] font-black px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                🟢 RAJAONGKIR LIVE API
                            </span>
                        </template>
                        <template x-if="!isLiveApi">
                            <span class="bg-amber-100 text-amber-900 border border-amber-300 text-[9px] font-black px-2 py-0.5 rounded-full flex items-center gap-1 shadow-2xs">
                                ⚡ TARIF RESMI (BACKUP)
                            </span>
                        </template>
                    </div>

                    <!-- Destination City Dropdown -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Kota / Kabupaten Tujuan Pembeli:</label>
                        <select 
                            x-model="selectedCityId" 
                            @change="fetchShippingRates()" 
                            class="w-full bg-white text-slate-800 text-xs font-semibold rounded-lg px-3 py-2 border border-slate-300 focus:border-blue-700 focus:outline-none shadow-2xs"
                        >
                            <option value="153">DKI Jakarta - Kota Jakarta Selatan (ID: 153)</option>
                            <option value="152">DKI Jakarta - Kota Jakarta Pusat (ID: 152)</option>
                            <option value="151">DKI Jakarta - Kota Jakarta Barat (ID: 151)</option>
                            <option value="154">DKI Jakarta - Kota Jakarta Timur (ID: 154)</option>
                            <option value="155">DKI Jakarta - Kota Jakarta Utara (ID: 155)</option>
                            <option value="22">Jawa Barat - Kota Bandung (ID: 22)</option>
                            <option value="54">Jawa Barat - Kota Bekasi (ID: 54)</option>
                            <option value="78">Jawa Barat - Kota Bogor (ID: 78)</option>
                            <option value="115">Jawa Barat - Kota Depok (ID: 115)</option>
                            <option value="444">Jawa Timur - Kota Surabaya (ID: 444)</option>
                            <option value="399">Jawa Tengah - Kota Semarang (ID: 399)</option>
                            <option value="501">DI Yogyakarta - Kota Yogyakarta (ID: 501)</option>
                            <option value="278">Sumatera Utara - Kota Medan (ID: 278)</option>
                            <option value="256">Sulawesi Selatan - Kota Makassar (ID: 256)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-blue-900 font-medium pt-1">
                        <span>Estimasi Total Berat Paket:</span>
                        <span class="font-black text-blue-700" x-text="(totalWeight / 1000).toFixed(1) + ' kg (' + totalWeight + ' gram)'"></span>
                    </div>

                    <!-- Status Notification Banner -->
                    <div 
                        class="p-2 text-[11px] rounded-lg border font-medium flex items-center gap-1.5"
                        :class="isLiveApi ? 'bg-emerald-50/90 border-emerald-200 text-emerald-900 font-bold' : 'bg-amber-50/90 border-amber-200 text-amber-900'"
                    >
                        <svg class="w-4 h-4 shrink-0" :class="isLiveApi ? 'text-emerald-600' : 'text-amber-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="shippingStatusMsg"></span>
                    </div>
                </div>

                <!-- Courier / Pilihan Ekspedisi Realtime RajaOngkir -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-bold text-gray-900 text-sm">Pilihan Ekspedisi (RajaOngkir API)</label>
                        <span x-show="loadingShipping" class="text-[11px] text-blue-700 font-bold animate-pulse">Memuat Tarif...</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <template x-for="courier in couriers" :key="courier.id">
                            <div 
                                @click="selectCourier(courier)"
                                class="relative p-3 bg-white rounded-xl cursor-pointer transition-all select-none border"
                                :class="selectedCourier === courier.id ? 'border-2 border-blue-700 bg-blue-50/30 shadow-xs' : 'border-slate-200 text-slate-700 hover:border-blue-300'"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-xs text-slate-900 leading-tight" x-text="courier.name"></div>
                                        <div class="text-[10px] text-slate-500 mt-0.5" x-text="courier.description || courier.etd"></div>
                                    </div>
                                    <div 
                                        x-show="selectedCourier === courier.id" 
                                        class="w-4 h-4 bg-blue-700 rounded-full flex items-center justify-center text-white shrink-0"
                                    >
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="text-xs font-black mt-2 text-blue-700" x-text="formatRupiah(courier.price)"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- KOTAK VOUCHER TOKO ONLINE -->
                <div class="mb-5 pb-5 border-b border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 011 1.732 2 2 0 01-1 1.732V17a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 01-1-1.732 2 2 0 011-1.732V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            <span>Voucher Toko Online</span>
                        </label>
                        <template x-if="totalDiscount > 0">
                            <span class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full" x-text="'Hemat ' + formatRupiah(totalDiscount)"></span>
                        </template>
                    </div>

                    <!-- Tombol Buka Modal Pilih Voucher -->
                    <button 
                        type="button" 
                        @click="openVoucherModal()" 
                        class="w-full bg-blue-50/80 border-2 border-dashed border-blue-600 hover:bg-blue-100/70 text-blue-700 font-bold py-3 px-4 rounded-xl flex items-center justify-between transition-all shadow-2xs cursor-pointer group"
                    >
                        <div class="flex items-center gap-2 text-xs sm:text-sm">
                            <svg class="w-5 h-5 text-blue-700 group-hover:scale-110 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h.01M13 11h.01M13 15h.01M17 7h.01M17 11h.01M17 15h.01"/>
                            </svg>
                            <span x-text="(appliedShippingVoucher || appliedDiscountVoucher) ? 'Ubah / Lihat Voucher Toko' : 'Pilih Voucher Toko Online'"></span>
                        </div>
                        <div class="flex items-center gap-1 text-xs font-extrabold">
                            <span x-text="(appliedShippingVoucher && appliedDiscountVoucher) ? '2 Voucher Dipilih' : (appliedShippingVoucher || appliedDiscountVoucher ? '1 Voucher Dipilih' : 'Pilih >')"></span>
                        </div>
                    </button>

                    <!-- LIST VOUCHER AKTIF YANG DITERAPKAN -->
                    <div x-show="appliedShippingVoucher || appliedDiscountVoucher" class="space-y-2 pt-1">
                        <!-- Badge Voucher Gratis Ongkir -->
                        <template x-if="appliedShippingVoucher">
                            <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900 shadow-2xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-base shrink-0">🚚</span>
                                    <div class="truncate">
                                        <span class="font-bold">Gratis Ongkir:</span>
                                        <span class="font-mono bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-extrabold text-[11px] ml-1" x-text="appliedShippingVoucher.code"></span>
                                        <span class="text-[11px] font-semibold text-emerald-700 ml-1" x-text="'(-' + formatRupiah(shippingDiscountAmount) + ')'"></span>
                                    </div>
                                </div>
                                <button type="button" @click="removeShippingVoucher()" class="text-red-600 hover:text-red-800 font-bold text-xs shrink-0 ml-2 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </template>

                        <!-- Badge Voucher Diskon Produk -->
                        <template x-if="appliedDiscountVoucher">
                            <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between text-xs text-blue-900 shadow-2xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="text-base shrink-0">🏷️</span>
                                    <div class="truncate">
                                        <span class="font-bold">Diskon Produk:</span>
                                        <span class="font-mono bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-extrabold text-[11px] ml-1" x-text="appliedDiscountVoucher.code"></span>
                                        <span class="text-[11px] font-semibold text-blue-700 ml-1" x-text="'(-' + formatRupiah(productDiscountAmount) + ')'"></span>
                                    </div>
                                </div>
                                <button type="button" @click="removeDiscountVoucher()" class="text-red-600 hover:text-red-800 font-bold text-xs shrink-0 ml-2 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Summary Section / Rincian Biaya -->
                <div class="space-y-2.5 pt-4 border-t border-gray-200 text-sm mb-4">
                    <!-- Warning if 0 selected -->
                    <div x-show="selectedCount === 0" class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 text-center font-medium">
                        Tidak ada produk yang dipilih. Centang produk untuk melihat rincian biaya.
                    </div>

                    <div x-show="selectedCount > 0" class="space-y-2.5">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Subtotal (<span x-text="totalQty"></span> produk dipilih)</span>
                            <span class="font-semibold text-gray-900" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <div class="flex items-center justify-between text-gray-600">
                            <span>Ongkos Kirim (<span x-text="getSelectedCourierName()"></span>)</span>
                            <span class="font-semibold text-gray-900" x-text="formatRupiah(shippingCost)"></span>
                        </div>

                        <!-- Potongan Gratis Ongkir -->
                        <div x-show="appliedShippingVoucher && shippingDiscountAmount > 0" class="flex items-center justify-between text-emerald-700 font-medium">
                            <span class="flex items-center gap-1">🚚 Potongan Ongkir</span>
                            <span class="font-bold" x-text="'- ' + formatRupiah(shippingDiscountAmount)"></span>
                        </div>

                        <!-- Potongan Diskon Produk -->
                        <div x-show="appliedDiscountVoucher && productDiscountAmount > 0" class="flex items-center justify-between text-blue-700 font-medium">
                            <span class="flex items-center gap-1">🏷️ Diskon Produk</span>
                            <span class="font-bold" x-text="'- ' + formatRupiah(productDiscountAmount)"></span>
                        </div>
                    </div>
                </div>

                <!-- Rincian Penjelasan (Detailed Calculation Breakdown Note) -->
                <div x-show="selectedCount > 0" class="mb-5 p-3 bg-blue-50/80 border border-blue-200 rounded-lg text-xs text-blue-900 space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-blue-800">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Penjelasan Rincian Total:</span>
                    </div>
                    <p class="text-[11.5px] text-blue-800 leading-relaxed pl-5 font-medium" x-text="getCalculationExplanation()"></p>
                </div>

                <!-- Total Section -->
                <div class="pt-4 border-t border-gray-200 mb-6 flex items-center justify-between">
                    <span class="text-base sm:text-lg font-bold text-gray-900">Total Pembayaran</span>
                    <span class="text-xl sm:text-2xl font-bold text-blue-600" x-text="formatRupiah(grandTotal)"></span>
                </div>

                <!-- Checkout Button -->
                <button 
                    type="button" 
                    :disabled="selectedCount === 0"
                    :class="selectedCount === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-md cursor-pointer'"
                    class="w-full font-bold py-3.5 px-4 rounded-xl transition-all text-center block text-base"
                    @click="processCheckout()"
                >
                    Lanjut ke Pembayaran
                </button>

            </div>
        </div>

    </div>

    <!-- Include Voucher Selection Modal Component -->
    <x-voucher-modal />

</div>

</div>

@php
    $u = session('user') ?? auth()->user();
    $uName = $u ? (is_array($u) ? ($u['name'] ?? 'Budi Santoso') : ($u->name ?? 'Budi Santoso')) : 'Budi Santoso';
    $uAddress = $u ? (is_array($u) ? ($u['address'] ?? '') : ($u->address ?? '')) : 'Jl. Jendral Sudirman No. 45, Kebayoran Baru, Jakarta Selatan, 12190';
    $uCityId = $u ? (is_array($u) ? ($u['city_id'] ?? '153') : ($u->city_id ?? '153')) : '153';
@endphp

<script>
function cartApp() {
    return {
        isLoggedIn: @json(session()->has('user') || auth()->check()),
        customerName: @json($uName),
        customerAddress: @json($uAddress),
        items: @json($cartItems ?? []),

        // RAJAONGKIR REAL-TIME API STATE
        selectedCityId: @json($uCityId),
        loadingShipping: false,
        isLiveApi: false,
        shippingStatusMsg: 'Memuat tarif ongkir...',
        selectedCourier: 'jne_reg',
        selectedCourierCost: 15000,
        selectedCourierName: 'JNE Express (REG)',
        couriers: [
            { id: 'jne_reg', code: 'jne', name: 'JNE Express (REG)', description: 'Layanan Reguler JNE', etd: '1-2 Hari', price: 15000 },
            { id: 'pos_kilat', code: 'pos', name: 'POS Indonesia (Kilat Khusus)', description: 'Layanan Kilat POS', etd: '2-3 Hari', price: 14000 },
            { id: 'tiki_reg', code: 'tiki', name: 'TIKI (REG)', description: 'Layanan Reguler TIKI', etd: '2-3 Hari', price: 16000 }
        ],

        init() {
            this.fetchShippingRates();
        },

        get totalWeight() {
            let selected = this.items.filter(item => item.selected);
            if (selected.length === 0) return 1000;
            let weight = selected.reduce((sum, item) => sum + ((item.weight || 500) * item.qty), 0);
            return Math.max(1000, weight);
        },

        async fetchShippingRates() {
            this.loadingShipping = true;
            try {
                const response = await fetch('{{ route("checkout.calculateShipping") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        destination_city_id: this.selectedCityId,
                        weight: this.totalWeight
                    })
                });
                const data = await response.json();
                if (data.source === 'api') {
                    this.isLiveApi = true;
                    this.shippingStatusMsg = '🟢 100% HARGA ASLI LIVE DARI RAJAONGKIR API';
                } else {
                    this.isLiveApi = false;
                    this.shippingStatusMsg = data.message || '⚡ Server API RajaOngkir lambat/offline. Menggunakan Tarif Resmi Ekspedisi.';
                }

                if (data.couriers && data.couriers.length > 0) {
                    this.couriers = data.couriers;
                    const match = this.couriers.find(c => c.id === this.selectedCourier);
                    if (match) {
                        this.selectedCourierCost = match.price;
                        this.selectedCourierName = match.name;
                    } else {
                        this.selectedCourier = this.couriers[0].id;
                        this.selectedCourierCost = this.couriers[0].price;
                        this.selectedCourierName = this.couriers[0].name;
                    }
                }
            } catch (err) {
                console.error("RajaOngkir fetch error", err);
                this.isLiveApi = false;
                this.shippingStatusMsg = '⚡ Menggunakan Tarif Resmi Ekspedisi (Jarak & Berat).';
            } finally {
                this.loadingShipping = false;
            }
        },

        selectCourier(courier) {
            this.selectedCourier = courier.id;
            this.selectedCourierCost = courier.price;
            this.selectedCourierName = courier.name;
        },

        getSelectedCourierName() {
            return this.selectedCourierName || 'JNE Express';
        },

        // VOUCHER SYSTEM STATE
        showVoucherModal: false,
        manualVoucherCode: '',
        modalMsg: { text: '', type: '' },
        tempShippingVoucherId: null,
        tempDiscountVoucherId: null,
        appliedShippingVoucher: null,
        appliedDiscountVoucher: null,

        shippingVouchers: [
            {
                id: 'v_ongkir_1',
                code: 'FREEONGKIR20',
                category: 'shipping',
                title: 'Gratis Ongkir s.d. Rp 20.000',
                minSpend: 50000,
                discountType: 'shipping',
                discountValue: 20000,
                description: 'Min. belanja Rp 50.000 untuk semua pilihan pengiriman',
                expiry: 'Berlaku s.d. 31 Agt 2026',
                badge: 'GRATIS ONGKIR',
                badgeBg: 'bg-emerald-100 text-emerald-800 border-emerald-200'
            },
            {
                id: 'v_ongkir_2',
                code: 'FREEONGKIR15',
                category: 'shipping',
                title: 'Gratis Ongkir s.d. Rp 15.000',
                minSpend: 30000,
                discountType: 'shipping',
                discountValue: 15000,
                description: 'Min. belanja Rp 30.000 untuk J&T / SiCepat / JNE',
                expiry: 'Berlaku s.d. 25 Agt 2026',
                badge: 'GRATIS ONGKIR',
                badgeBg: 'bg-emerald-100 text-emerald-800 border-emerald-200'
            }
        ],

        discountVouchers: [
            {
                id: 'v_diskon_1',
                code: 'DISKON50K',
                category: 'discount',
                title: 'Potongan Harga Rp 50.000',
                minSpend: 200000,
                discountType: 'fixed',
                discountValue: 50000,
                description: 'Min. belanja Rp 200.000 untuk semua produk',
                expiry: 'Berlaku s.d. 31 Agt 2026',
                badge: 'SPESIAL GAJIAN',
                badgeBg: 'bg-blue-100 text-blue-800 border-blue-200'
            },
            {
                id: 'v_diskon_2',
                code: 'DISKON10',
                category: 'discount',
                title: 'Diskon 10% (s.d. Rp 100.000)',
                minSpend: 0,
                discountType: 'percent',
                discountValue: 10,
                maxDiscount: 100000,
                description: 'Tanpa min. belanja khusus kategori Fashion & Gadget',
                expiry: 'Berlaku s.d. 20 Agt 2026',
                badge: 'DISKON PERSEN',
                badgeBg: 'bg-purple-100 text-purple-800 border-purple-200'
            },
            {
                id: 'v_diskon_3',
                code: 'CASHBACK25K',
                category: 'discount',
                title: 'Cashback Ekstra Rp 25.000',
                minSpend: 100000,
                discountType: 'fixed',
                discountValue: 25000,
                description: 'Min. belanja Rp 100.000 untuk transaksi apa saja',
                expiry: 'Berlaku s.d. 28 Agt 2026',
                badge: 'CASHBACK',
                badgeBg: 'bg-amber-100 text-amber-800 border-amber-200'
            }
        ],

        ineligibleVouchers: [
            {
                id: 'v_inelig_1',
                code: 'SUPERDEAL100K',
                title: 'Potongan Spesial Rp 100.000',
                reason: 'Minimal belanja Rp 1.000.000 belum terpenuhi',
                expiry: 'Berlaku s.d. 31 Agt 2026'
            },
            {
                id: 'v_inelig_2',
                code: 'FLASH70',
                title: 'Diskon Flash Sale 70%',
                reason: 'Kuota voucher harian telah habis',
                expiry: 'Berlaku s.d. 01 Agt 2026'
            }
        ],

        get selectedCount() {
            return this.items.filter(item => item.selected).length;
        },

        get isAllSelected() {
            return this.items.length > 0 && this.items.every(item => item.selected);
        },

        get totalQty() {
            return this.items
                .filter(item => item.selected)
                .reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.items
                .filter(item => item.selected)
                .reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        get shippingCost() {
            if (this.selectedCount === 0) return 0;
            const courier = this.couriers.find(c => c.id === this.selectedCourier);
            return courier ? courier.price : (this.selectedCourierCost || 15000);
        },

        get shippingDiscountAmount() {
            if (!this.appliedShippingVoucher || this.selectedCount === 0) return 0;
            if (!this.isVoucherEligible(this.appliedShippingVoucher)) return 0;
            return Math.min(this.appliedShippingVoucher.discountValue, this.shippingCost);
        },

        get productDiscountAmount() {
            if (!this.appliedDiscountVoucher || this.selectedCount === 0) return 0;
            if (!this.isVoucherEligible(this.appliedDiscountVoucher)) return 0;
            if (this.appliedDiscountVoucher.discountType === 'fixed') {
                return Math.min(this.appliedDiscountVoucher.discountValue, this.subtotal);
            }
            if (this.appliedDiscountVoucher.discountType === 'percent') {
                const p = Math.round((this.subtotal * this.appliedDiscountVoucher.discountValue) / 100);
                return Math.min(p, this.appliedDiscountVoucher.maxDiscount || 999999999);
            }
            return 0;
        },

        get totalDiscount() {
            return this.shippingDiscountAmount + this.productDiscountAmount;
        },

        get grandTotal() {
            if (this.selectedCount === 0) return 0;
            const total = (this.subtotal + this.shippingCost) - this.totalDiscount;
            return total < 0 ? 0 : total;
        },

        toggleSelectAll() {
            const targetState = !this.isAllSelected;
            this.items.forEach(item => item.selected = targetState);
        },

        removeSelectedItems() {
            const selectedIds = this.items.filter(item => item.selected).map(item => item.id);
            if (selectedIds.length === 0) return;

            if (confirm('Apakah Anda yakin ingin menghapus produk yang dipilih dari keranjang?')) {
                this.items = this.items.filter(item => !item.selected);
                
                fetch("{{ route('cart.removeSelected') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ product_ids: selectedIds })
                }).then(res => res.json()).then(data => {
                    window.location.reload();
                }).catch(err => console.error(err));
            }
        },

        removeItem(index) {
            const item = this.items[index];
            if (!item) return;

            if (confirm('Hapus "' + item.name + '" dari keranjang?')) {
                const targetId = item.id;
                this.items.splice(index, 1);

                fetch("{{ route('cart.remove') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ product_id: targetId })
                }).then(res => res.json()).then(data => {
                    window.location.reload();
                }).catch(err => console.error(err));
            }
        },

        increaseQty(item) {
            item.qty++;
            this.updateQtyBackend(item.id, item.qty);
        },

        decreaseQty(item) {
            if (item.qty > 1) {
                item.qty--;
                this.updateQtyBackend(item.id, item.qty);
            }
        },

        updateQtyBackend(productId, qty) {
            fetch("{{ route('cart.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: productId, qty: qty })
            }).catch(err => console.error(err));
        },

        getSelectedCourierName() {
            const courier = this.couriers.find(c => c.id === this.selectedCourier);
            return courier ? courier.name : '-';
        },

        // VOUCHER MODAL METHODS
        openVoucherModal() {
            this.modalMsg = { text: '', type: '' };
            this.manualVoucherCode = '';
            this.tempShippingVoucherId = this.appliedShippingVoucher ? this.appliedShippingVoucher.id : null;
            this.tempDiscountVoucherId = this.appliedDiscountVoucher ? this.appliedDiscountVoucher.id : null;
            this.showVoucherModal = true;
        },

        isVoucherEligible(v) {
            return (this.subtotal || 0) >= (v.minSpend || 0);
        },

        calculateTempDiscountTotal() {
            let total = 0;
            const shipV = this.shippingVouchers.find(v => v.id === this.tempShippingVoucherId);
            if (shipV && this.isVoucherEligible(shipV)) {
                total += Math.min(shipV.discountValue, this.shippingCost || 0);
            }
            const discV = this.discountVouchers.find(v => v.id === this.tempDiscountVoucherId);
            if (discV && this.isVoucherEligible(discV)) {
                if (discV.discountType === 'fixed') {
                    total += Math.min(discV.discountValue, this.subtotal || 0);
                } else if (discV.discountType === 'percent') {
                    const p = Math.round(((this.subtotal || 0) * discV.discountValue) / 100);
                    total += Math.min(p, discV.maxDiscount || 999999999);
                }
            }
            return total;
        },

        confirmVoucherSelection() {
            const shipV = this.shippingVouchers.find(v => v.id === this.tempShippingVoucherId);
            const discV = this.discountVouchers.find(v => v.id === this.tempDiscountVoucherId);
            this.appliedShippingVoucher = (shipV && this.isVoucherEligible(shipV)) ? shipV : null;
            this.appliedDiscountVoucher = (discV && this.isVoucherEligible(discV)) ? discV : null;
            this.showVoucherModal = false;
        },

        applyManualVoucherInModal() {
            const code = this.manualVoucherCode.trim().toUpperCase();
            if (!code) {
                this.modalMsg = { text: 'Masukkan kode promo terlebih dahulu.', type: 'error' };
                return;
            }
            const foundShip = this.shippingVouchers.find(v => v.code === code);
            if (foundShip) {
                if (!this.isVoucherEligible(foundShip)) {
                    this.modalMsg = { text: 'Minimal belanja untuk ' + code + ' belum terpenuhi.', type: 'error' };
                    return;
                }
                this.tempShippingVoucherId = foundShip.id;
                this.modalMsg = { text: 'Voucher ' + code + ' berhasil dipilih!', type: 'success' };
                return;
            }
            const foundDisc = this.discountVouchers.find(v => v.code === code);
            if (foundDisc) {
                if (!this.isVoucherEligible(foundDisc)) {
                    this.modalMsg = { text: 'Minimal belanja untuk ' + code + ' belum terpenuhi.', type: 'error' };
                    return;
                }
                this.tempDiscountVoucherId = foundDisc.id;
                this.modalMsg = { text: 'Voucher ' + code + ' berhasil dipilih!', type: 'success' };
                return;
            }
            const customV = {
                id: 'v_custom_' + Date.now(),
                code: code,
                category: 'discount',
                title: 'Voucher Promo ' + code,
                minSpend: 0,
                discountType: 'fixed',
                discountValue: 30000,
                description: 'Voucher promo khusus ' + code,
                expiry: 'Berlaku hari ini',
                badge: 'PROMO KHUSUS',
                badgeBg: 'bg-blue-100 text-blue-800 border-blue-200'
            };
            this.discountVouchers.unshift(customV);
            this.tempDiscountVoucherId = customV.id;
            this.modalMsg = { text: 'Voucher ' + code + ' berhasil ditemukan dan dipilih!', type: 'success' };
        },

        removeShippingVoucher() {
            this.appliedShippingVoucher = null;
            this.tempShippingVoucherId = null;
        },

        removeDiscountVoucher() {
            this.appliedDiscountVoucher = null;
            this.tempDiscountVoucherId = null;
        },

        getCalculationExplanation() {
            if (this.selectedCount === 0) return 'Belum ada produk yang dipilih.';
            let explanation = `Subtotal (${this.totalQty} barang: ${this.formatRupiah(this.subtotal)}) + Ongkir ${this.getSelectedCourierName()} (${this.formatRupiah(this.shippingCost)})`;
            if (this.shippingDiscountAmount > 0) {
                explanation += ` - Gratis Ongkir (${this.formatRupiah(this.shippingDiscountAmount)})`;
            }
            if (this.productDiscountAmount > 0) {
                explanation += ` - Diskon Produk (${this.formatRupiah(this.productDiscountAmount)})`;
            }
            explanation += ` = ${this.formatRupiah(this.grandTotal)}.`;
            return explanation;
        },

        processCheckout() {
            if (!this.isLoggedIn) {
                window.location.href = "{{ route('login') }}";
                return;
            }
            if (this.selectedCount === 0) return;
            window.location.href = "{{ route('checkout') }}";
        },

        formatRupiah(number) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endsection
